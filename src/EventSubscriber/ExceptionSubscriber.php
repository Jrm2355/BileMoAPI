<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exeption = $event->getThrowable();

        if ($exeption instanceof HttpException) {
            $data = [
                'status' => $exeption->getStatusCode(),
                'message' => $exeption->getMessage()
            ];

            $event->setResponse( new JsonResponse($data));
        } else {
            $data = [
                'status' => 500, // le status n'existe pas car ce n'est pas une exeption HTTP, donc on met 500 par défaut.
                'message' => $exeption->getMessage()
            ];

            $event->setResponse( new JsonResponse($data));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.exeption' => 'onKernelExeption',
        ];
    }
}
