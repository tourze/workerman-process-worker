<?php

namespace Tourze\Workerman\ProcessWorker\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\Workerman\ProcessWorker\Contract\ProcessHandlerInterface;
use Tourze\Workerman\ProcessWorker\Event\ProcessStartEvent;
use Tourze\Workerman\ProcessWorker\ProcessWorker;

/**
 * @internal
 */
#[CoversClass(ProcessStartEvent::class)]
final class ProcessStartEventTest extends AbstractEventTestCase
{
    private ProcessWorker $worker;

    protected function setUp(): void
    {
        parent::setUp();

        $processHandler = $this->createMock(ProcessHandlerInterface::class);
        $this->worker = new ProcessWorker('test', $processHandler);
    }

    public function testConstruction(): void
    {
        $event = new ProcessStartEvent($this->worker);

        $this->assertInstanceOf(ProcessStartEvent::class, $event);
    }

    public function testGetWorker(): void
    {
        $event = new ProcessStartEvent($this->worker);

        $this->assertSame($this->worker, $event->getWorker());
    }

    public function testEventName(): void
    {
        $this->assertSame('process.start', ProcessStartEvent::NAME);
    }
}
