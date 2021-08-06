<?php

namespace App\Tests\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    /**
     * Test de la home
     */
    public function testHome(): void
    {
        // Crée un client HTTP
        $client = static::createClient();
        // Envoie une requête vers l'url '/'
        $crawler = $client->request('GET', '/');

        // Est-ce que la réponse a un statut 2xx
        $this->assertResponseIsSuccessful();
        // Est-ce que je suis bien sur la page d'accueil
        $this->assertSelectorTextContains('h1', 'Tous les films');
    }

    /**
     * L'anonyme n'a pas accès à l'écriture d'une Review
     * et se trouve redirigé
     */
    public function testReviewAddFailure()
    {
        // Crée un client HTTP
        $client = static::createClient();
        // Envoie une requête vers l'url '/'
        $crawler = $client->request('GET', '/movie/1/add/review');
        // Si form dans la page show :
        // $crawler = $client->request('POST', '/movie/rambo-2');

        $this->assertResponseRedirects();
    }
}
