<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

class Slugger{

    private $slugger;
    private $toLower;

    public function __construct(SluggerInterface $slugger, bool $toLower)
    {
        $this->slugger = $slugger;
        $this->toLower = $toLower;
    }
    
    public function sluggerMovie($movieTitle)
    {
        if($this->toLower){
            return $this->slugger->slug($movieTitle)->lower();  
        } 
    }
}