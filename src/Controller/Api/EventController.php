<?php

namespace App\Controller\Api;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/api/events', name: 'app_api_event', methods: ['GET'])]
    public function eventsAsJson(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->json($events);
    }
}
