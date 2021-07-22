<?php

namespace App\Controller;

use App\Entity\Movie;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur en mode "sandbox" (bac à sable, on joue ;))
 * 
 * On va appliquer BREAD
 * Browse => liste les enregistrements
 * Read => lit un enregistrement
 * Edit => met à jour
 * Add => ajoute
 * Delete => supprime
 */
class TestController extends AbstractController
{
    /**
     * Test création entité
     * 
     * Add
     * 
     * @Route("/test/add", name="test_add")
     */
    public function add(): Response
    {
        // On crée une entité PHP
        $movie = new Movie();

        // On renseigne l'entité
        $movie->setTitle('99 francs');
        // Date courante pour createdAt
        $movie->setCreatedAt(new DateTime());

        dump($movie);

        // 1. On demande au Manager de *se préparer à* "persister" l'entité
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($movie);

        // 2. Exécute les requêtes SQL nécessaires (ici, INSERT INTO)
        $entityManager->flush();

        dump($movie);

        return new Response('Film ajouté : '.$movie->getId(). '</body>');
        // PS : on ajoute le </body> pour afficher la Toolbar
    }

    /**
     * Browse Movie
     * 
     * @Route("/test/browse", name="test_browse")
     */
    public function browse()
    {
        // Pour accéder aux données de la table movie
        // on passe par le Repository de l'entité Movie
        // PS : Movie::class => 'App\Entity\Movie'
        $movieRepository = $this->getDoctrine()->getRepository(Movie::class);

        // On utilise les méthodes d'accès fournies par ce Repository
        $movies = $movieRepository->findAll();

        dump($movies);

        return new Response('Liste des films</body>');
    }

    /**
     * Read Movie
     * 
     * @Route("/test/read/{id<\d+>}", name="test_read")
     */
    public function read($id)
    {
        // Pour accéder aux données de la table movie
        // on passe par le Repository de l'entité Movie
        // PS : Movie::class => 'App\Entity\Movie'
        $movieRepository = $this->getDoctrine()->getRepository(Movie::class);

        // On utilise les méthodes d'accès fournies par ce Repository
        $movie = $movieRepository->find($id);

        dump($movie);

        return new Response('Film n°'.$id.'</body>');
    }

    /**
     * Edit Movie
     * 
     * @Route("/test/edit/{id<\d+>}")
     */
    public function edit($id)
    {
        // On va chercher le film, on le modifie, on le sauvegarde
        $movieRepository = $this->getDoctrine()->getRepository(Movie::class);
        $movie = $movieRepository->find($id);

        // Mise à jour
        $movie->setUpdatedAt(new DateTime());

        // Sauvegarde
        // 1. On récupère le Manager de Doctrine
        $entityManager = $this->getDoctrine()->getManager();
        // 2. Exécute les requêtes SQL nécessaires (ici, UPDATE)
        $entityManager->flush();

        return $this->redirectToRoute('test_read', ['id' => $id]);
    }
}
