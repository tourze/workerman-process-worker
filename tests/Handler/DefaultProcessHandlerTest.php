<?php

namespace Tourze\Workerman\ProcessWorker\Tests\Handler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tourze\Workerman\ProcessWorker\Handler\DefaultProcessHandler;

/**
 * @internal
 */
#[CoversClass(DefaultProcessHandler::class)]
final class DefaultProcessHandlerTest extends TestCase
{
    private DefaultProcessHandler $handler;

    private string $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = 'php -r "echo \"test\";"';
        $this->handler = new DefaultProcessHandler($this->command);
    }

    public function testConstruction(): void
    {
        $this->assertInstanceOf(DefaultProcessHandler::class, $this->handler);
    }

    public function testGetCommand(): void
    {
        $this->assertSame($this->command, $this->handler->getCommand());
    }

    public function testStart(): void
    {
        $process = $this->handler->start();

        $this->assertIsResource($process);

        // Clean up
        $this->handler->stop($process);
    }

    public function testStop(): void
    {
        $process = $this->handler->start();

        $this->assertIsResource($process);

        $this->handler->stop($process);

        // Validate stop executed without throwing exception
        $this->assertTrue(true, 'Stop method executed successfully');
    }

    public function testIsRunning(): void
    {
        $process = $this->handler->start();

        // Should be running initially
        $this->assertTrue($this->handler->isRunning($process));

        // Read to EOF
        while (!feof($process)) {
            fread($process, 1024);
        }

        // Should not be running after EOF
        $this->assertFalse($this->handler->isRunning($process));

        // Clean up
        $this->handler->stop($process);
    }

    public function testIsRunningWithClosedResource(): void
    {
        $process = $this->handler->start();

        // Close the resource with pclose
        pclose($process);

        // Should return false for closed resource
        $this->assertFalse($this->handler->isRunning($process));
    }

    public function testStopWithClosedResource(): void
    {
        $process = $this->handler->start();

        // Close the resource first with pclose
        pclose($process);

        // Should not throw exception when stopping already closed resource
        $this->handler->stop($process);

        // Test passes if no exception is thrown
        $this->expectNotToPerformAssertions();
    }
}
