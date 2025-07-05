<?php

namespace App\EventListener;

use App\Exception\FormValidationException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionEventListener
{
    private const JSON_RESPONSE = 'json';

    private ExceptionEvent $event;

    public function __construct(
        private readonly string $environment,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $this->event = $event;
        $exception = $this->event->getThrowable();
        $message = $exception->getMessage();
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        switch (true) {
            case $exception instanceof ForeignKeyConstraintViolationException:
                $message = "Can't delete a resource : The Resource has a relationship";
                $statusCode = Response::HTTP_BAD_REQUEST;
                break;
            case $exception instanceof FormValidationException:
                $this->event->setResponse($exception->getResponse());
                return;
            case $exception instanceof HttpException:
                $statusCode = $exception->getStatusCode();
                break;
            default:
        }

        $this->setResponse($message, $statusCode);
    }

    private function setResponse(string $message, int $statusCode): void
    {
        if ('dev' !== $this->environment && Response::HTTP_INTERNAL_SERVER_ERROR === $statusCode) {
            $message = "An error occurred while processing your request";
        }

        $request = $this->event->getRequest();

        $uri = $request->getPathInfo();
        $responseType = null;
        $headers = [];

        if (preg_match("/^\/(v\d*\/)?api\/.+/", $uri)) {
            $responseType = self::JSON_RESPONSE;
        }

        if (self::JSON_RESPONSE === $responseType) {
            $error = [
                'message' => $message,
                'code' => $statusCode,
            ];
            $response = new JsonResponse($error, $statusCode, $headers);
            $this->event->setResponse($response);

            return;
        }

        if ('dev' !== $this->environment) {
            $session = $this->event->getRequest()->getSession();
            $session->getFlashBag()->add('error', $message);
            $this->event->setResponse(
                new RedirectResponse("/")
            );
        }
    }
}
