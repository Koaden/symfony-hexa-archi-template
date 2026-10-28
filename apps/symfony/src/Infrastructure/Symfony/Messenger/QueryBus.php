<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Messenger;

use App\Application\MessageBus\QueryBus as QueryBusInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class QueryBus implements QueryBusInterface
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(object $input): mixed
    {
        try {
            return $this->handle($input);
        } catch (HandlerFailedException $exception) {
            throw self::unwrap($exception);
        }
    }

    private static function unwrap(HandlerFailedException $exception): \Throwable
    {
        $nested = $exception->getWrappedExceptions();
        $first = reset($nested);

        return false === $first ? $exception : $first;
    }
}
