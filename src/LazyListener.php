<?php

namespace Refinery29\Event;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use League\Event\EventInterface;
use League\Event\ListenerInterface;

class LazyListener implements ListenerInterface
{
    /**
     * @var string
     */
    private $alias;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ListenerInterface
     */
    private $listener;

    /**
     * @param string             $alias
     * @param ContainerInterface $container
     */
    public function __construct($alias, ContainerInterface $container)
    {
        $this->alias = $alias;
        $this->container = $container;
    }

    /**
     * @return ListenerInterface
     */
    public function getListener()
    {
        if ($this->listener === null) {
            try {
                $listener = $this->container->get($this->alias);
            } catch (ContainerException $exception) {
                throw new \BadMethodCallException(sprintf(
                    'Unable to fetch a service for alias "%s" from the container',
                    $this->alias
                ));
            }

            if (!$listener instanceof ListenerInterface) {
                throw new \BadMethodCallException('Fetched listener does not implement ListenerInterface');
            }

            $this->listener = $listener;
        }

        return $this->listener;
    }

    public function handle(EventInterface $event)
    {
        $this->getListener()->handle($event);
    }

    public function isListener($listener)
    {
        if ($listener instanceof LazyListener) {
            return $this === $listener;
        }

        if ($this->listener !== null) {
            return $this->listener === $listener;
        }

        return false;
    }

    /**
     * @param string             $alias
     * @param ContainerInterface $container
     *
     * @return static
     */
    public static function fromAlias($alias, ContainerInterface $container)
    {
        return new static($alias, $container);
    }
}
