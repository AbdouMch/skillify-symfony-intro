<?php

namespace App\Story;

use App\Factory\AttendeeFactory;
use App\Factory\EventFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class CreateEventsWithAttendees extends Story
{
    public function build(): void
    {
        $firstAttendee = AttendeeFactory::createOne();
        $secondAttendee = AttendeeFactory::createOne();

        EventFactory::createOne(static fn () => ['attendees' => [$firstAttendee]]);
        EventFactory::createOne(static fn () => ['attendees' => [$firstAttendee]]);
        EventFactory::createOne(static fn () => ['attendees' => [$firstAttendee, $secondAttendee]]);
        EventFactory::createOne(static fn () => ['attendees' => [$firstAttendee, $secondAttendee]]);

        EventFactory::createMany(10);
    }
}
