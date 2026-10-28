<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\Event;

use App\Domain\Event\EventInterface;
use App\Domain\Event\EventsRegisterer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventsRegisterer::class)]
final class EventsRegistererTest extends TestCase
{
    public function testRegisterAndFlush(): void
    {
        $event = new class implements EventInterface {
            public function getAggregateRootId(): string
            {
                return 'aggregate-id';
            }
        };

        $registerer = new EventsRegisterer();
        $registerer->register($event);

        self::assertSame([$event], $registerer->getEvents());

        $registerer->flush();

        self::assertSame([], $registerer->getEvents());
    }
}
