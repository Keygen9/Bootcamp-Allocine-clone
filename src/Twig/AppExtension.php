<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('convert', [$this, 'convertToHoursMins']),
        ];
    }

    public function convertToHoursMins($time, $format = '%2dh %02dmin')
    {
        if ($time < 1) {
            return;
        }

        if ($time < 60) {
            $format = '%02dmin';
            return sprintf($format, $time);
        }

        if ($time % 60 == 0) {
            $hours = floor($time / 60);
            $format = '%2dh';
            return sprintf($format, $hours);
        }

        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return sprintf($format, $hours, $minutes);
    }
}
