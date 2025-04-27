<?php

namespace Tourze\Workerman\ProcessWorker\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tourze\Workerman\ProcessWorker\Event\ProcessExitEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessOutputEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessStartEvent;
use Tourze\Workerman\ProcessWorker\ProcessWorker;
use Tourze\Workerman\ProcessWorker\Tests\Mock\MockEventLoop;
use Tourze\Workerman\ProcessWorker\Tests\Mock\MockProcessHandler;
use Workerman\Worker;

/**
 * ProcessWorker 功能测试
 */
class ProcessWorkerFunctionalTest extends TestCase
{
    /**
     * 保存原始的 Worker::$globalEvent
     */
    private $originalGlobalEvent;
    
    /**
     * 测试设置
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // 保存原始事件循环
        $this->originalGlobalEvent = Worker::$globalEvent;
    }
    
    /**
     * 测试清理
     */
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
        // 创建模拟输出数据
        $outputLines = ['Line 1', 'Line 2', 'Line 3'];
        
        // 创建 mock 对象
        $processHandler = new MockProcessHandler('test command', $outputLines);
        $eventLoop = new MockEventLoop();
        $eventDispatcher = new EventDispatcher();
        
        // 设置全局事件循环为我们的模拟实现
        Worker::$globalEvent = $eventLoop;
        
        // 捕获输出
        $capturedOutput = [];
        
        // 创建 ProcessWorker 实例
        $worker = new ProcessWorker('test command', $processHandler, $eventDispatcher);
        $worker->onProcessOutput = function ($worker, $output) use (&$capturedOutput) {
            $capturedOutput[] = $output;
        };
        
        // 手动触发 onWorkerStart
        $reflectionClass = new \ReflectionClass($worker);
        $method = $reflectionClass->getMethod('onWorkerStart');
        $method->setAccessible(true);
        $method->invoke($worker);
        
        // 模拟事件循环触发读取事件
        $stream = $processHandler->getMockResource();
        $eventLoop->triggerReadable($stream);
        
        // 验证是否捕获了输出
        $this->assertNotEmpty($capturedOutput);
        
        // 读取内存流，验证内容是否匹配
        rewind($stream);
        $content = stream_get_contents($stream);
        $this->assertEquals(implode(PHP_EOL, $outputLines), $content);
    }
    
    /**
     * 测试进程退出处理
     */
    public function testProcessExit(): void
    {
        // 创建 mock 对象
        $processHandler = new MockProcessHandler('test command');
        $eventLoop = new MockEventLoop();
        $eventDispatcher = new EventDispatcher();
        
        // 设置全局事件循环为我们的模拟实现
        Worker::$globalEvent = $eventLoop;
        
        // 标记进程退出事件是否被调用
        $exitCalled = false;
        
        // 创建 ProcessWorker 实例
        $worker = new ProcessWorker('test command', $processHandler, $eventDispatcher);
        $worker->onProcessExit = function ($worker) use (&$exitCalled) {
            $exitCalled = true;
        };
        
        // 手动触发 onWorkerStart
        $reflectionClass = new \ReflectionClass($worker);
        $method = $reflectionClass->getMethod('onWorkerStart');
        $method->setAccessible(true);
        $method->invoke($worker);
        
        // 模拟进程退出
        $processHandler->simulateExit();
        
        // 模拟事件循环触发读取事件
        $stream = $processHandler->getMockResource();
        $eventLoop->triggerReadable($stream);
        
        // 验证退出事件是否被调用
        $this->assertTrue($exitCalled, '进程退出事件应该被调用');
    }
    
    /**
     * 测试自定义事件监听器
     */
    public function testCustomEventListeners(): void
    {
        // 创建 mock 对象
        $processHandler = new MockProcessHandler('test command', ['test output']);
        $eventLoop = new MockEventLoop();
        $eventDispatcher = new EventDispatcher();
        
        // 设置全局事件循环为我们的模拟实现
        Worker::$globalEvent = $eventLoop;
        
        // 模拟调用记录
        $calls = [];
        
        // 创建 ProcessWorker 实例
        $worker = new ProcessWorker('test command', $processHandler, $eventDispatcher);
        
        // 添加自定义事件监听器
        $worker->addListener(ProcessStartEvent::NAME, function () use (&$calls) {
            $calls[] = 'start';
        });
        
        $worker->addListener(ProcessOutputEvent::NAME, function () use (&$calls) {
            $calls[] = 'output';
        });
        
        $worker->addListener(ProcessExitEvent::NAME, function () use (&$calls) {
            $calls[] = 'exit';
        });
        
        // 手动触发 onWorkerStart
        $reflectionClass = new \ReflectionClass($worker);
        $method = $reflectionClass->getMethod('onWorkerStart');
        $method->setAccessible(true);
        $method->invoke($worker);
        
        // 验证启动事件是否被调用
        $this->assertEquals(['start'], $calls);
        
        // 模拟事件循环触发读取事件
        $stream = $processHandler->getMockResource();
        $eventLoop->triggerReadable($stream);
        
        // 验证输出事件是否被调用
        $this->assertEquals(['start', 'output'], $calls);
        
        // 模拟进程退出
        $processHandler->simulateExit();
        $eventLoop->triggerReadable($stream);
        
        // 验证退出事件是否被调用
        $this->assertEquals(['start', 'output', 'exit'], $calls);
    }
}
