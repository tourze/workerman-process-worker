<?php

namespace Tourze\Workerman\ProcessWorker\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\Workerman\ProcessWorker\Contract\ProcessHandlerInterface;
use Tourze\Workerman\ProcessWorker\Event\ProcessOutputEvent;
use Tourze\Workerman\ProcessWorker\ProcessWorker;

/**
 * @internal
 */
#[CoversClass(ProcessOutputEvent::class)]
final class ProcessOutputEventTest extends AbstractEventTestCase
{
    private ProcessWorker $worker;

    private string $output;

    protected function setUp(): void
    {
        parent::setUp();

        $processHandler = $this->createMock(ProcessHandlerInterface::class);
        $this->worker = new ProcessWorker('test', $processHandler);
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
