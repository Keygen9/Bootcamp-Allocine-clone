<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service qui cause à OMDB API
 */
class OmdbApi
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function fetch(string $title): array
    {
        $response = $this->client->request(
            'GET',
            'https://www.omdbapi.com/?apiKey=83bfb8c6&t=' . $title
        );

        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        return $content;
    }

    public function fetchPoster(string $title)
    {
        $content = $this->fetch($title);

        if(array_key_exists('Poster', $content)){
            return $content['Poster'];
        }

        return null;

    }
}
