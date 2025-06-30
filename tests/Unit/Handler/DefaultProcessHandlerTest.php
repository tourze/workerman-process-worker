<?php

namespace Tourze\Workerman\ProcessWorker\Tests\Unit\Handler;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\ProcessWorker\Handler\DefaultProcessHandler;

class DefaultProcessHandlerTest extends TestCase
{
    private DefaultProcessHandler $handler;
    private string $command;

    protected function setUp(): void
    {
        $this->command = 'echo "test"';
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
        
        // After stopping, the resource should be closed
        $this->assertFalse(is_resource($process) && !feof($process));
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
        
        // Close the resource
        fclose($process);
        
        // Should return false for closed resource
        $this->assertFalse($this->handler->isRunning($process));
    }

    public function testStopWithClosedResource(): void
    {
        $process = $this->handler->start();
        
        // Close the resource first
        fclose($process);
        
        // Should not throw exception when stopping already closed resource
        $this->handler->stop($process);
        
        // Verify no exceptions were thrown
        $this->assertTrue(true);
    }
}