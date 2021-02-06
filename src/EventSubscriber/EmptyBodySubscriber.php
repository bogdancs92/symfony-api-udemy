<?php


namespace App\EventSubscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EmptyBodySubscriber implements EventSubscriberInterface
{
    const ERROR_EMPTY_BODY = "The body of the POST/PUT method cannot be empty";
    const ERROR_EMPTY_BODY_CODE = 400;
    public static function getSubscribedEvents()
    {
        /*return [
            KernelEvents::EXCEPTION => ["handleEmptyBody",EventPriorities::PRE_DESERIALIZE]

        ];
        */
        return [
            KernelEvents::EXCEPTION => ['handleEmptyBody', EventPriorities::POST_DESERIALIZE]
        ];
    }

    public function handleEmptyBody2(ViewEvent $viewEvent)
    {
        $method = $viewEvent->getRequest()->getMethod();
        var_dump($method);
        die();

        if (!in_array($method, [Request::METHOD_PUT, Request::METHOD_POST])) {
            return;
        }
        $data = $viewEvent->getRequest()->get('data');
        var_dump($data);
        die();
    }

    public function handleEmptyBody(ExceptionEvent $event)
    {
        $method = $event->getRequest()->getMethod();
        $data = $event->getRequest()->get('data');

        if (!in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        if (null === $data) {
            $event->setResponse(new JsonResponse(["Error" => self::ERROR_EMPTY_BODY, "Error Code" => self::ERROR_EMPTY_BODY_CODE], Response::HTTP_BAD_REQUEST));
        }

    }
}