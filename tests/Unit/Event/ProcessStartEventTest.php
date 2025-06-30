<?php

namespace Tourze\Workerman\ProcessWorker\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\ProcessWorker\Event\ProcessStartEvent;
use Tourze\Workerman\ProcessWorker\ProcessWorker;

class ProcessStartEventTest extends TestCase
{
    private ProcessWorker $worker;

    protected function setUp(): void
    {
        $this->worker = $this->createMock(ProcessWorker::class);
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