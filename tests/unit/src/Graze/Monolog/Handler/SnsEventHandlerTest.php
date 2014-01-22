<?php
namespace Graze\Monolog\Handler;

use Mockery as m;
use Graze\Monolog\Handler\SnsEventHandler;
use Graze\Monolog\Test\EventHandlerTestCase;

class SnsEventHandlerTest extends EventHandlerTestCase
{
    public function setUp()
    {
        if (!class_exists('Aws\Sns\SnsClient')) {
            $this->markTestSkipped('aws/aws-sdk-php not installed');
        }

        $this->client = $this->getMockBuilder('Aws\Sns\SnsClient')
            ->setMethods(array('formatAttributes', '__call'))
            ->disableOriginalConstructor()->getMock();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf('Graze\Monolog\Handler\SnsEventHandler', new SnsEventHandler($this->client, 'foo'));
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Monolog\Handler\HandlerInterface', new SnsEventHandler($this->client, 'foo'));
    }

    public function testGetFormatter()
    {
        $handler = new SnsEventHandler($this->client, 'foo');
        $this->assertInstanceOf('Graze\Monolog\Formatter\JsonDateAwareFormatter', $handler->getFormatter());
    }

    public function testHandle()
    {
        $record = $this->getRecord();
        $formatter = $this->getMock('Monolog\Formatter\FormatterInterface');
        $formatted = array('foo' => 1, 'bar' => 2);
        $handler = new SnsEventHandler($this->client, 'foo');
        $handler->setFormatter($formatter);

        $formatter
             ->expects($this->once())
             ->method('format')
             ->with($record)
             ->will($this->returnValue($formatted));
        $this->client
             ->expects($this->once())
             ->method('__call')
             ->with('publish', array(array(
                 'TopicArn' => 'foo',
                 'Message' => $formatted,
             )));

        $handler->handle($record);
    }
}
