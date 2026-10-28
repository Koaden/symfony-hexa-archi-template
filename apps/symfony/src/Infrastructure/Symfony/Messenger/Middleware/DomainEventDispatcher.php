<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Messenger\Middleware;

use App\Application\MessageBus\EventBus;
use App\Domain\Event\EventsRegisterer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class DomainEventDispatcher implements MiddlewareInterface
{
    public function __construct(
        private readonly EventBus $eventBus,
        private readonly EventsRegisterer $eventsRegisterer,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $envelope = $stack->next()->handle($envelope, $stack);

        if (!$envelope->last(HandledStamp::class) instanceof HandledStamp) {
            return $envelope;
        }

        $this->entityManager->clear();

        foreach ($this->eventsRegisterer->getEvents() as $event) {
            $this->eventBus->dispatch($event);
        }

        $this->eventsRegisterer->flush();

        return $envelope;
    }
}
