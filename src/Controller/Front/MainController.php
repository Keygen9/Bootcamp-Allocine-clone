<?php

namespace App\Controller\Front;

use App\Entity\Movie;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\MovieRepository;
use App\Repository\CastingRepository;
use App\Repository\GenreRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    /**
     * Liste des films
     * 
     * @Route("/", name="home")
     */
    public function home(MovieRepository $movieRepository, GenreRepository $genreRepository): Response
    {
        // Les films par ordre alphabétique sur le titre
        $movies = $movieRepository->findAllOrderedByTitleAscDql();

        // Tous les genres
        $genres = $genreRepository->findBy([], ['name' => 'ASC']);

        // Accès aux paramètres de services depuis un contrôleur
        // @link https://symfony.com/doc/current/configuration.html#accessing-configuration-parameters
        // dump($this->getParameter('kernel.project_dir'));
        // dump($this->getParameter('app.message_generator_is_random'));

        return $this->render('front/main/home.html.twig', [
            'movies' => $movies,
            'genres' => $genres,
        ]);
    }

    /**
     * Affiche un film selon son slug
     * 
     * @Route("/movie/{slugger}", name="movie_show")
     */
    public function movieShow(Movie $movie = null, CastingRepository $castingRepository): Response
    {
    
        // Film non trouvé
        if ($movie === null) {
            throw $this->createNotFoundException('Film non trouvé.');
        }

        // Pour classer les castings, on utilise notre requête custom
        // qu'on oublie pas d'envoyer à la vue
        $castings = $castingRepository->findAllByMovieJoinedToPerson($movie);

        return $this->render('front/main/movie_show.html.twig', [
            'movie' => $movie,
            'castings' => $castings,
        ]);
    }

    /**
     * Ajout d'une critique sur un film
     * 
     * @Route("/movie/{id}/add/review", name="movie_add_review", methods={"GET", "POST"})
     */
    public function movieAddReview(Movie $movie = null, Request $request): Response
    {
        // Film non trouvé
        if ($movie === null) {
            throw $this->createNotFoundException('Film non trouvé.');
        }

        // Nouvelle critique
        $review = new Review();

        // Création du form, associé à l'entité $review
        $form = $this->createForm(ReviewType::class, $review);

        // Prendre en charge la requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Si le form était sur la page qui affiche le film
            // on aurait pu protéger l'accès à ajout du form dans le code, via
            // $this->denyAccessUnlessGranted('ROLE_USER');
            // OU BIEN
            // utiliser l'option "methods" de l'ACL
            // @link https://symfony.com/doc/current/security/access_control.html
            // - { path: ^/movie/show/\d+, roles: ROLE_USER, methods: POST }

            // Relation review <> movie
            $review->setMovie($movie);

            // On sauve la Review
            $em = $this->getDoctrine()->getManager();
            $em->persist($review);
            $em->flush();

            return $this->redirectToRoute('movie_show', ['id' => $movie->getId()]);
        }

        // Affiche le form
        return $this->render('front/main/movie_add_review.html.twig', [
            'form' => $form->createView(),
            'movie' => $movie,
        ]);
    }
}
