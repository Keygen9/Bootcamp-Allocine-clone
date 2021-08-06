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
     * Test movie show
     */
    public function testMovieShow(): void
    {
        // Crée un client HTTP
        $client = static::createClient();
        // Envoie une requête vers l'url '/'
        $crawler = $client->request('GET', '/');

        // Sélectionner le premier lien de la liste des films
        $selectedLink = $crawler->filter('.movie-main p a');
        // On mémorise le texte du lien
        $textLink = $selectedLink->text();
        // On récupère le lien
        $link = $selectedLink->link();

        // Cliquer dessus
        $client->click($link);

        // Est-ce que la réponse a un statut 2xx
        $this->assertResponseIsSuccessful();
        // Le texte correspond
        $this->assertSelectorTextSame('h1#header-margin', $textLink);
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
