<?php

namespace Refinery29\Test\Unit\Event;

use BadMethodCallException;
use Interop\Container\ContainerInterface;
use League\Event\EventInterface;
use League\Event\ListenerInterface;
use Refinery29\Event\LazyListener;
use Refinery29\Event\Test\ContainerException;
use Refinery29\Event\Test\NotFoundException;
use stdClass;

class LazyListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsInterface()
    {
        $alias = 'foo';
        $container = $this->getContainerMock();

        $listener = new LazyListener($alias, $container);

        $this->assertInstanceOf('League\Event\ListenerInterface', $listener);
    }

    public function testGetListenerWhenActualListenerNotManagedByContainer()
    {
        $this->setExpectedException('BadMethodCallException');

        $alias = 'foo';

        $container = $this->getContainerMock();

        $container
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($alias))
            ->willThrowException(new NotFoundException('Not found'))
        ;

        $listener = new LazyListener($alias, $container);

        $listener->getListener();
    }

    public function testGetListenerWhenFetchingActualListenerImpossible()
    {
        $this->setExpectedException('BadMethodCallException');

        $alias = 'foo';

        $container = $this->getContainerMock();

        $container
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($alias))
            ->willThrowException(new ContainerException('Sorry!'))
        ;

        $listener = new LazyListener($alias, $container);

        $listener->getListener();
    }

    public function testGetListenerWhenActualListenerDoesNotImplementListenerInterface()
    {
        $this->setExpectedException('BadMethodCallException');

        $alias = 'foo';

        $container = $this->getContainerMock();

        $container
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($alias))
            ->willReturn(new stdClass())
        ;

        $listener = new LazyListener($alias, $container);

        $listener->getListener();
    }

    public function testGetListenerFetchesActualListenerFromContainer()
    {
        $alias = 'foo';

        $actualListener = $this->getListenerMock();

        $container = $this->getContainerMock();

        $container
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($alias))
            ->willReturn($actualListener)
        ;

        $listener = new LazyListener($alias, $container);

        $this->assertSame($actualListener, $listener->getListener());
    }

    public function testGetListenerFetchesActualListenerOnceOnly()
    {
        $alias = 'foo';

        $container = $this->getContainerMock();

        $container
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($alias))
            ->willReturn($this->getListenerMock())
        ;

        $listener = new LazyListener($alias, $container);

        $actualListener = $listener->getListener();

        $this->assertSame($actualListener, $listener->getListener());
    }

    public function testHandleLetsActualListenerHandleEvent()
    {
        $event = $this->getEventMock();

        $actualListener = $this->getListenerMock();

        $actualListener
            ->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($event))
        ;

        $alias = 'foo';

        $container = $this->getContainerMock();

        $container
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($alias))
            ->willReturn($actualListener)
        ;

        $listener = new LazyListener($alias, $container);

        $listener->handle($event);
    }

    public function testIsListenerWhenComparedListenerIsSameLazyListener()
    {
        $alias = 'foo';

        $container = $this->getContainerMock();

        $container
            ->expects($this->never())
            ->method($this->anything())
        ;

        $listener = new LazyListener($alias, $container);

        $this->assertTrue($listener->isListener($listener));
    }

    public function testIsListenerWhenComparedListenerIsDifferentLazyListener()
    {
        $lazyListener = $this->getLazyListenerMock();

        $alias = 'foo';

        $container = $this->getContainerMock();

        $container
            ->expects($this->never())
            ->method($this->anything())
        ;

        $listener = new LazyListener($alias, $container);

        $this->assertFalse($listener->isListener($lazyListener));
    }

    public function testIsListenerWhenComparedListenerIsActualListenerAndHasNotBeenFetchedFromContainer()
    {
        $actualListener = $this->getListenerMock();

        $alias = 'foo';

        $container = $this->getContainerMock();

        $container
            ->expects($this->never())
            ->method($this->anything())
        ;

        $listener = new LazyListener($alias, $container);

        $this->assertFalse($listener->isListener($actualListener));
    }

    public function testIsListenerWhenComparedListenerIsActualListenerAndHasBeenFetchedFromContainer()
    {
        $actualListener = $this->getListenerMock();

        $alias = 'foo';

        $container = $this->getContainerMock();

        $container
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($alias))
            ->willReturn($actualListener)
        ;

        $listener = new LazyListener($alias, $container);

        $listener->getListener();

        $this->assertTrue($listener->isListener($actualListener));
    }

    public function testFromAlias()
    {
        $alias = 'foo';

        $actualListener = $this->getListenerMock();

        $container = $this->getContainerMock();

        $container
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($alias))
            ->willReturn($actualListener)
        ;

        $listener = LazyListener::fromAlias($alias, $container);

        $this->assertSame($actualListener, $listener->getListener());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    private function getContainerMock()
    {
        return $this->getMockBuilder('Interop\Container\ContainerInterface')->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EventInterface
     */
    private function getEventMock()
    {
        return $this->getMockBuilder('League\Event\EventInterface')->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LazyListener
     */
    private function getLazyListenerMock()
    {
        return $this->getMockBuilder('Refinery29\Event\LazyListener')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ListenerInterface
     */
    private function getListenerMock()
    {
        return $this->getMockBuilder('League\Event\ListenerInterface')->getMock();
    }
}
