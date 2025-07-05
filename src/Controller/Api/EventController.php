<?php

namespace App\Controller\Api;

use App\Exception\FormValidationException;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/event')]
final class EventController extends AbstractController
{
    #[Route('', name: 'app_api_all_events', methods: ['GET'])]
    public function getEventsList(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->json($events);
    }

    #[Route('/{id}', name: 'app_api_event_by_id', methods: ['GET'])]
    public function getEvent(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);

        return $this->json($event);
    }

    #[Route('', name: 'app_api_new_event', methods: ['POST'])]
    public function newEvent(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EventType::class);

        $data = json_decode($request->getContent(), true);

        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $em->persist($event);
            $em->flush();

            return $this->json($event);
        }

        throw new FormValidationException($form);
    }
}
