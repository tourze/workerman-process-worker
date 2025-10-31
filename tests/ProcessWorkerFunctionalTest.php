<?php

namespace Tourze\Workerman\ProcessWorker\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\Workerman\ProcessWorker\Event\ProcessExitEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessOutputEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessStartEvent;
use Tourze\Workerman\ProcessWorker\ProcessWorker;
use Workerman\Events\EventInterface;
use Workerman\Worker;

/**
 * ProcessWorker 功能测试
 *
 * @internal
 */
#[CoversClass(ProcessWorker::class)]
final class ProcessWorkerFunctionalTest extends TestCase
{
    /**
     * 保存原始的 Worker::$globalEvent
     *
     * @var EventInterface|null
     */
    private $originalGlobalEvent;

    protected function setUp(): void
    {
        parent::setUp();
        // 保存原始事件循环
        $this->originalGlobalEvent = Worker::$globalEvent;
    }

    protected function tearDown(): void
    {
        // 恢复原始事件循环
        Worker::$globalEvent = $this->originalGlobalEvent;
        parent::tearDown();
    }

    /**
     * 测试进程输出处理
     */
    public function testProcessOutput(): void
    {
        // 创建事件调度器
        $eventDispatcher = new EventDispatcher();

        // 捕获输出
        $capturedOutput = [];

        // 创建 ProcessWorker 实例
        $worker = new ProcessWorker('echo "test output"', null, $eventDispatcher);

        // 设置输出回调
        $worker->onProcessOutput = function ($worker, $output) use (&$capturedOutput): void {
            $capturedOutput[] = $output;
        };

        // 添加事件监听器来捕获输出事件
        $outputEventCaptured = false;
        $eventDispatcher->addListener(ProcessOutputEvent::class, function (ProcessOutputEvent $event) use (&$outputEventCaptured): void {
            $outputEventCaptured = true;
            $this->assertNotEmpty($event->getOutput());
        });

        // 验证事件调度器已设置
        $this->assertInstanceOf(EventDispatcherInterface::class, $worker->getEventDispatcher());

        // 由于实际进程执行需要事件循环，我们仅验证回调和事件注册是否正确
        $this->assertNotNull($worker->onProcessOutput);
        $this->assertTrue($eventDispatcher->hasListeners(ProcessOutputEvent::class));
    }

    /**
     * 测试进程退出处理
     */
    public function testProcessExit(): void
    {
        // 创建事件调度器
        $eventDispatcher = new EventDispatcher();

        // 标记进程退出事件是否被调用
        $exitCalled = false;

        // 创建 ProcessWorker 实例
        $worker = new ProcessWorker('echo "test"', null, $eventDispatcher);

        // 设置退出回调
        $worker->onProcessExit = function ($worker) use (&$exitCalled): void {
            $exitCalled = true;
        };

        // 添加事件监听器来捕获退出事件
        $exitEventCaptured = false;
        $eventDispatcher->addListener(ProcessExitEvent::class, function (ProcessExitEvent $event) use (&$exitEventCaptured): void {
            $exitEventCaptured = true;
            $this->assertInstanceOf(ProcessWorker::class, $event->getWorker());
        });

        // 验证事件调度器已设置
        $this->assertInstanceOf(EventDispatcherInterface::class, $worker->getEventDispatcher());

        // 验证回调和事件监听器已正确注册
        $this->assertNotNull($worker->onProcessExit);
        $this->assertTrue($eventDispatcher->hasListeners(ProcessExitEvent::class));
    }

    /**
     * 测试自定义事件监听器
     */
    public function testCustomEventListeners(): void
    {
        // 创建事件调度器
        $eventDispatcher = new EventDispatcher();

        // 模拟调用记录
        $calls = [];

        // 创建 ProcessWorker 实例
        $worker = new ProcessWorker('echo "test"', null, $eventDispatcher);

        // 添加自定义事件监听器
        $worker->addListener(ProcessStartEvent::class, function (ProcessStartEvent $event) use (&$calls): void {
            $calls[] = 'start';
            $this->assertInstanceOf(ProcessWorker::class, $event->getWorker());
        });

        $worker->addListener(ProcessOutputEvent::class, function (ProcessOutputEvent $event) use (&$calls): void {
            $calls[] = 'output';
            $this->assertInstanceOf(ProcessWorker::class, $event->getWorker());
            $this->assertIsString($event->getOutput());
        });

        $worker->addListener(ProcessExitEvent::class, function (ProcessExitEvent $event) use (&$calls): void {
            $calls[] = 'exit';
            $this->assertInstanceOf(ProcessWorker::class, $event->getWorker());
        });

        // 验证所有事件监听器已注册
        $this->assertTrue($eventDispatcher->hasListeners(ProcessStartEvent::class));
        $this->assertTrue($eventDispatcher->hasListeners(ProcessOutputEvent::class));
        $this->assertTrue($eventDispatcher->hasListeners(ProcessExitEvent::class));

        // 手动触发事件以验证监听器
        $eventDispatcher->dispatch(new ProcessStartEvent($worker));
        $this->assertEquals(['start'], $calls);

        $eventDispatcher->dispatch(new ProcessOutputEvent($worker, 'test output'));
        $this->assertEquals(['start', 'output'], $calls);

        $eventDispatcher->dispatch(new ProcessExitEvent($worker));
        $this->assertEquals(['start', 'output', 'exit'], $calls);
    }

    /**
     * 测试 addListener 方法
     */
    public function testAddListener(): void
    {
        $worker = new ProcessWorker('test');
        $eventDispatcher = $worker->getEventDispatcher();

        $eventName = \stdClass::class;
        $eventData = [];
        $listener = function ($event) use (&$eventData): void {
            $eventData['triggered'] = true;
            $eventData['event'] = $event;
        };

        // 测试添加自定义监听器
        $worker->addListener($eventName, $listener, 10);

        // 创建测试事件对象
        $testEvent = new \stdClass();
        $testEvent->testProperty = 'test_value';

        // 手动触发事件
        $eventDispatcher->dispatch($testEvent);

        // 验证监听器是否被正确调用
        $this->assertTrue($eventData['triggered'], 'addListener 应该成功添加事件监听器');
        $this->assertSame($testEvent, $eventData['event'], '监听器应该接收到正确的事件对象');
    }
}
