<?php

namespace Tourze\Workerman\ProcessWorker\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\Workerman\ProcessWorker\Exception\ProcessException;

/**
 * ProcessException 测试
 *
 * @internal
 */
#[CoversClass(ProcessException::class)]
final class ProcessExceptionTest extends AbstractExceptionTestCase
{
    /**
     * 测试异常构造
     */
    public function testConstruction(): void
    {
        $message = 'Process failed to start';
        $code = 500;
        $previous = new \Exception('Previous exception');

        $exception = new ProcessException($message, $code, $previous);

        $this->assertInstanceOf(ProcessException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * 测试默认构造
     */
    public function testDefaultConstruction(): void
    {
        $exception = new ProcessException();

        $this->assertInstanceOf(ProcessException::class, $exception);
        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * 测试异常抛出
     */
    public function testThrowException(): void
    {
        $this->expectException(ProcessException::class);
        $this->expectExceptionMessage('Test process error');
        $this->expectExceptionCode(123);

        throw new ProcessException('Test process error', 123);
    }

    /**
     * 测试异常链
     */
    public function testExceptionChaining(): void
    {
        $rootCause = new \RuntimeException('Root cause');
        $processException = new ProcessException('Process failed', 0, $rootCause);

        $this->assertInstanceOf(\RuntimeException::class, $processException->getPrevious());
        $this->assertEquals('Root cause', $processException->getPrevious()->getMessage());
    }
}
