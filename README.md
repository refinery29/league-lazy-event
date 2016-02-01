# league-lazy-event

[![Build Status](https://travis-ci.org/refinery29/league-lazy-event.svg?branch=master)](https://travis-ci.org/refinery29/league-lazy-event)
[![Code Climate](https://codeclimate.com/github/refinery29/league-lazy-event/badges/gpa.svg)](https://codeclimate.com/github/refinery29/league-lazy-event)
[![Test Coverage](https://codeclimate.com/github/refinery29/league-lazy-event/badges/coverage.svg)](https://codeclimate.com/github/refinery29/league-lazy-event/coverage)

This repository provides a `LazyListener` for use with [`league/event`](http://github.com/thephpleague/event), which 
allows for lazy fetching of an actual listener from the composed container.

## Installation

Run

```
$ composer require refinery29/league-lazy-event
```

## Usage

Register your actual listener as a service with the container:

```php
use League\Container;

$container = new Container();

$container->share(ExpensiveListener::class, function () {
    /*
     * here, some heavy lifting occurs that creates the actual listener,
     * which should implement the ListenerInterface
     */
    return $listener;
});
```

Then register a `LazyListener`, composing the alias and the container:

```php
use League\Event\Emitter;
use Refinery29\Event\LazyListener;

$emitter->addListener(ContentChangedEvent::class, LazyListener::fromAlias(
    ExpensiveListener::class,
    $container
));
```

Trigger your events as needed!

```php
$emitter->emit(ContentChangedEvent::class, new ContentChangedEvent(
    $url, 
    new DateTimeImmutable()
);
```

:+1: Listeners are only ever fetched from the container when the event is handled.

## Contributing

Please have a look at [CONTRIBUTING.md](CONTRIBUTING.md).

## Code of Conduct

Please have a look at [CONDUCT.md](CONDUCT.md).

## License

This package is licensed using the MIT License.
