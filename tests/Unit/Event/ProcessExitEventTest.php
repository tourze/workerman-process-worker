<?php

namespace Tourze\Workerman\ProcessWorker\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\ProcessWorker\Event\ProcessExitEvent;
use Tourze\Workerman\ProcessWorker\ProcessWorker;

class ProcessExitEventTest extends TestCase
{
    private ProcessWorker $worker;

    protected function setUp(): void
    {
        $this->worker = $this->createMock(ProcessWorker::class);
    }

    public function testConstruction(): void
    {
        $event = new ProcessExitEvent($this->worker);

        $this->assertInstanceOf(ProcessExitEvent::class, $event);
    }

    public function testGetWorker(): void
    {
        $event = new ProcessExitEvent($this->worker);

        $this->assertSame($this->worker, $event->getWorker());
    }

    public function testEventName(): void
    {
        $this->assertSame('process.exit', ProcessExitEvent::NAME);
    }
}