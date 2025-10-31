<?php

namespace Tourze\Workerman\ProcessWorker\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\Workerman\ProcessWorker\Contract\ProcessHandlerInterface;
use Tourze\Workerman\ProcessWorker\Event\ProcessExitEvent;
use Tourze\Workerman\ProcessWorker\ProcessWorker;

/**
 * @internal
 */
#[CoversClass(ProcessExitEvent::class)]
final class ProcessExitEventTest extends AbstractEventTestCase
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
