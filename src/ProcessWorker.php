<?php

namespace Tourze\Workerman\ProcessWorker;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\Workerman\ProcessWorker\Contract\ProcessHandlerInterface;
use Tourze\Workerman\ProcessWorker\Event\ProcessExitEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessOutputEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessStartEvent;
use Tourze\Workerman\ProcessWorker\Handler\DefaultProcessHandler;
use Workerman\Worker;

class ProcessWorker extends Worker
{
    /**
     * 进程启动回调
     */
    public mixed $onProcessStart = null;

    /**
     * 进程输出回调
     */
    public mixed $onProcessOutput = null;

    /**
     * 进程退出回调
     */
    public mixed $onProcessExit = null;

    /**
     * 进程资源
     */
    private mixed $processResource = null;

    /**
     * 事件调度器
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @param string $runCommand 要运行的命令
     * @param ProcessHandlerInterface|null $processHandler 进程处理器
     * @param EventDispatcherInterface|null $eventDispatcher 事件调度器
     */
    public function __construct(
        private readonly string $runCommand,
        private readonly ?ProcessHandlerInterface $processHandler = null,
        ?EventDispatcherInterface $eventDispatcher = null
    )
    {
        parent::__construct();
        $this->reloadable = true;
        $this->count = 1;
        $this->onWorkerStart = $this->onWorkerStart(...);
        $this->eventDispatcher = $eventDispatcher ?? new EventDispatcher();

        // 注册默认的事件监听器（如果提供了回调函数）
        $this->registerDefaultListeners();
    }

    /**
     * 注册默认的事件监听器
     */
    private function registerDefaultListeners(): void
    {
        // 在构造时注册监听器，以兼容原有的回调方式
        $this->eventDispatcher->addListener(ProcessStartEvent::NAME, function (ProcessStartEvent $event) {
            if ($this->onProcessStart !== null) {
                call_user_func($this->onProcessStart, $this);
            }
        });

        $this->eventDispatcher->addListener(ProcessOutputEvent::NAME, function (ProcessOutputEvent $event) {
            if ($this->onProcessOutput !== null) {
                call_user_func($this->onProcessOutput, $this, $event->getOutput());
            }
        });

        $this->eventDispatcher->addListener(ProcessExitEvent::NAME, function (ProcessExitEvent $event) {
            if ($this->onProcessExit !== null) {
                call_user_func($this->onProcessExit, $this);
            }
        });
    }

    /**
     * 获取进程处理器
     *
     * @return ProcessHandlerInterface
     */
    public function getProcessHandler(): ProcessHandlerInterface
    {
        return $this->processHandler ?? new DefaultProcessHandler($this->runCommand);
    }

    /**
     * 获取事件调度器
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * Worker启动时的回调
     *
     * @return void
     */
    private function onWorkerStart(): void
    {
        $processHandler = $this->getProcessHandler();
        $eventLoop = Worker::getEventLoop();

        // 分发进程启动事件
        $this->eventDispatcher->dispatch(new ProcessStartEvent($this), ProcessStartEvent::NAME);

        // 启动进程
        $this->processResource = $processHandler->start();

        // 创建读事件
        $eventLoop->onReadable($this->processResource, function ($pipe) use ($processHandler) {
            // 获取事件循环
            $eventLoop = Worker::getEventLoop();

            // 读取进程输出
            $output = fread($pipe, 8192);

            if ($output === false || !$processHandler->isRunning($pipe)) {
                // 关闭连接和进程
                $processHandler->stop($pipe);
                $eventLoop->offReadable($pipe);

                // 如果进程都退出了，那我们Worker也要退出
                $this->stop();

                // 分发进程退出事件
                $this->eventDispatcher->dispatch(new ProcessExitEvent($this), ProcessExitEvent::NAME);
            } else {
                // 分发进程输出事件
                $this->eventDispatcher->dispatch(new ProcessOutputEvent($this, $output), ProcessOutputEvent::NAME);
            }
        });
    }

    /**
     * 添加事件监听器
     *
     * @param string $eventName 事件名称
     * @param callable $listener 监听器
     * @param int $priority 优先级
     * @return void
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * 获取运行的命令
     *
     * @return string
     */
    public function getRunCommand(): string
    {
        return $this->runCommand;
    }
}
