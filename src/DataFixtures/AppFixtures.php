<?php

namespace App\DataFixtures;

use App\Story\CreateEventsWithAttendees;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        CreateEventsWithAttendees::load();
    }
}
