<?php

namespace Tourze\Workerman\ProcessWorker\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\ProcessWorker\Event\ProcessOutputEvent;
use Tourze\Workerman\ProcessWorker\ProcessWorker;

class ProcessOutputEventTest extends TestCase
{
    private ProcessWorker $worker;
    private string $output;

    protected function setUp(): void
    {
        $this->worker = $this->createMock(ProcessWorker::class);
        $this->output = 'Test output content';
    }

    public function testConstruction(): void
    {
        $event = new ProcessOutputEvent($this->worker, $this->output);

        $this->assertInstanceOf(ProcessOutputEvent::class, $event);
    }

    public function testGetWorker(): void
    {
        $event = new ProcessOutputEvent($this->worker, $this->output);

        $this->assertSame($this->worker, $event->getWorker());
    }

    public function testGetOutput(): void
    {
        $event = new ProcessOutputEvent($this->worker, $this->output);

        $this->assertSame($this->output, $event->getOutput());
    }

    public function testEventName(): void
    {
        $this->assertSame('process.output', ProcessOutputEvent::NAME);
    }

    public function testWithEmptyOutput(): void
    {
        $emptyOutput = '';
        $event = new ProcessOutputEvent($this->worker, $emptyOutput);

        $this->assertSame($emptyOutput, $event->getOutput());
    }

    public function testWithMultilineOutput(): void
    {
        $multilineOutput = "Line 1\nLine 2\nLine 3";
        $event = new ProcessOutputEvent($this->worker, $multilineOutput);

        $this->assertSame($multilineOutput, $event->getOutput());
    }
}