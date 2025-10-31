<?php

namespace Tourze\Workerman\ProcessWorker\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\Workerman\ProcessWorker\Contract\ProcessHandlerInterface;
use Tourze\Workerman\ProcessWorker\Event\ProcessExitEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessOutputEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessStartEvent;
use Tourze\Workerman\ProcessWorker\ProcessWorker;

/**
 * ProcessWorker 单元测试
 *
 * @internal
 */
#[CoversClass(ProcessWorker::class)]
final class ProcessWorkerTest extends TestCase
{
    /**
     * 测试构造函数
     */
    public function testConstructor(): void
    {
        $runCommand = 'php -r "echo \"test\";"';
        $worker = new ProcessWorker($runCommand);

        $this->assertSame($runCommand, $worker->getRunCommand());
        $this->assertInstanceOf(ProcessHandlerInterface::class, $worker->getProcessHandler());
        $this->assertInstanceOf(EventDispatcherInterface::class, $worker->getEventDispatcher());
    }

    /**
     * 测试依赖注入
     */
    public function testDependencyInjection(): void
    {
        // 创建 mock 对象
        $processHandler = $this->createMock(ProcessHandlerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        // 创建 ProcessWorker 实例，注入 mock 对象
        $worker = new ProcessWorker('test', $processHandler, $eventDispatcher);

        // 验证 getXXX 方法返回的是我们注入的 mock 对象
        $this->assertSame($processHandler, $worker->getProcessHandler());
        $this->assertSame($eventDispatcher, $worker->getEventDispatcher());
    }

    /**
     * 测试事件监听器
     */
    public function testEventListeners(): void
    {
        $worker = new ProcessWorker('test');

        // 记录已触发的事件
        $triggeredEvents = [];

        // 添加自定义监听器
        $worker->addListener(ProcessStartEvent::class, function () use (&$triggeredEvents): void {
            $triggeredEvents[] = ProcessStartEvent::NAME;
        });

        $worker->addListener(ProcessOutputEvent::class, function () use (&$triggeredEvents): void {
            $triggeredEvents[] = ProcessOutputEvent::NAME;
        });

        $worker->addListener(ProcessExitEvent::class, function () use (&$triggeredEvents): void {
            $triggeredEvents[] = ProcessExitEvent::NAME;
        });

        // 手动触发事件
        $worker->getEventDispatcher()->dispatch(new ProcessStartEvent($worker));
        $worker->getEventDispatcher()->dispatch(new ProcessOutputEvent($worker, 'test output'));
        $worker->getEventDispatcher()->dispatch(new ProcessExitEvent($worker));

        // 验证事件是否被触发
        $this->assertEquals([
            ProcessStartEvent::NAME,
            ProcessOutputEvent::NAME,
            ProcessExitEvent::NAME,
        ], $triggeredEvents);
    }

    /**
     * 测试传统回调方式
     */
    public function testLegacyCallbacks(): void
    {
        $worker = new ProcessWorker('test');

        // 记录回调调用
        $callbackCalls = [];

        // 设置传统回调
        $worker->onProcessStart = function () use (&$callbackCalls): void {
            $callbackCalls[] = 'start';
        };

        $worker->onProcessOutput = function ($worker, $output) use (&$callbackCalls): void {
            $callbackCalls[] = 'output: ' . $output;
        };

        $worker->onProcessExit = function () use (&$callbackCalls): void {
            $callbackCalls[] = 'exit';
        };

        // 手动触发事件
        $worker->getEventDispatcher()->dispatch(new ProcessStartEvent($worker));
        $worker->getEventDispatcher()->dispatch(new ProcessOutputEvent($worker, 'test'));
        $worker->getEventDispatcher()->dispatch(new ProcessExitEvent($worker));

        // 验证回调是否被调用
        $this->assertEquals([
            'start',
            'output: test',
            'exit',
        ], $callbackCalls);
    }

    /**
     * 测试 getRunCommand 方法
     */
    public function testGetRunCommand(): void
    {
        $runCommand = 'ping localhost';
        $worker = new ProcessWorker($runCommand);

        $this->assertSame($runCommand, $worker->getRunCommand());
    }

    /**
     * 测试 addListener 方法
     */
    public function testAddListener(): void
    {
        $worker = new ProcessWorker('test');
        $eventDispatcher = $worker->getEventDispatcher();

        $eventName = \stdClass::class;
        $called = false;
        $listener = function () use (&$called): void {
            $called = true;
        };

        // 测试添加监听器
        $worker->addListener($eventName, $listener);

        // 手动触发事件验证监听器是否被添加
        $eventDispatcher->dispatch(new \stdClass());

        $this->assertTrue($called, 'addListener 方法应该成功添加事件监听器');
    }
}
