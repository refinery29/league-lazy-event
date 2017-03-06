<?php

/*
 * Copyright (c) 2016 Refinery29, Inc.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Refinery29\Event;

use BadMethodCallException;
use League\Event\CallbackListener;
use League\Event\EventInterface;
use League\Event\ListenerInterface;
use Psr\Container;

class LazyListener implements ListenerInterface
{
    /**
     * @var string
     */
    private $alias;

    /**
     * @var Container\ContainerInterface
     */
    private $container;

    /**
     * @var ListenerInterface
     */
    private $listener;

    /**
     * @param string                       $alias
     * @param Container\ContainerInterface $container
     */
    public function __construct($alias, Container\ContainerInterface $container)
    {
        $this->alias = $alias;
        $this->container = $container;
    }

    /**
     * @throws BadMethodCallException
     *
     * @return ListenerInterface
     */
    public function getListener()
    {
        if ($this->listener === null) {
            try {
                $listener = $this->container->get($this->alias);
            } catch (Container\ContainerExceptionInterface $exception) {
                throw new \BadMethodCallException(sprintf(
                    'Unable to fetch a service for alias "%s" from the container',
                    $this->alias
                ));
            }

            $this->listener = $this->ensureListener($listener);
        }

        return $this->listener;
    }

    /**
     * @param ListenerInterface|callable $listener
     *
     * @throws \BadMethodCallException
     *
     * @return ListenerInterface
     */
    private function ensureListener($listener)
    {
        if ($listener instanceof ListenerInterface) {
            return $listener;
        }

        if (is_callable($listener)) {
            return CallbackListener::fromCallable($listener);
        }

        throw new \BadMethodCallException('Fetched listener neither implements ListenerInterface nor is a callable');
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

        if ($this->listener instanceof CallbackListener) {
            return $this->listener->isListener($listener);
        }

        if ($this->listener !== null) {
            return $this->listener === $listener;
        }

        return false;
    }

    /**
     * @param string                       $alias
     * @param Container\ContainerInterface $container
     *
     * @return static
     */
    public static function fromAlias($alias, Container\ContainerInterface $container)
    {
        return new static($alias, $container);
    }
}
