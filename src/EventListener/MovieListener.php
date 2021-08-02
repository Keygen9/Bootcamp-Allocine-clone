<?php

namespace App\EventListener;

use App\Entity\Movie;
use App\Service\Slugger;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class MovieListener
{
    private $slug;

    public function __construct(Slugger $slugger)
    {
        $this->slug = $slugger;
    }

    // the entity listener methods receive two arguments:
    // the entity instance and the lifecycle event
    public function preUpdate(Movie $movie, LifecycleEventArgs $event): void
    {
        $moviez = $event->getObject();
        $title = $moviez->getTitle();
        $slugz = $this->slug->sluggerMovie($title);

        $moviez->setSlugger($slugz);
    }

    public function prePersist(Movie $movie, LifecycleEventArgs $event): void
    {
        $moviez = $event->getObject();
        $title = $moviez->getTitle();
        $slugz = $this->slug->sluggerMovie($title);

        $moviez->setSlugger($slugz);
    }
}
