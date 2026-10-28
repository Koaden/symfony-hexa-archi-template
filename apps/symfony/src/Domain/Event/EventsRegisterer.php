<?php

declare(strict_types=1);

namespace App\Domain\Event;

final class EventsRegisterer
{
    /** @var list<EventInterface> */
    private array $events = [];

    public function register(EventInterface ...$events): void
    {
        array_push($this->events, ...$events);
    }

    /** @return list<EventInterface> */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function flush(): void
    {
        $this->events = [];
    }
}
