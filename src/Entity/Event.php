<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute as Serializer;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(['event', 'all'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Serializer\Groups(['event', 'all'])]
    private string $title;

    #[ORM\Column(nullable: false)]
    #[Serializer\Groups(['event', 'all'])]
    private \DateTime $date;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    #[Serializer\Groups(['event', 'all'])]
    private string $description;

    /**
     * @var Collection<int, Attendee>
     */
    #[ORM\ManyToMany(targetEntity: Attendee::class, mappedBy: 'events')]
    #[Serializer\Groups(['all'])]
    private Collection $attendees;


    public function __construct()
    {
        $this->attendees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Attendee>
     */
    public function getAttendees(): Collection
    {
        return $this->attendees;
    }

    public function addAttendee(Attendee $attendee): static
    {
        if (!$this->attendees->contains($attendee)) {
            $this->attendees->add($attendee);
            $attendee->addEvent($this);
        }

        return $this;
    }

    public function removeAttendee(Attendee $attendee): static
    {
        if ($this->attendees->removeElement($attendee)) {
            $attendee->removeEvent($this);
        }

        return $this;
    }
}
