<?php

namespace App\Controller\Front;

use App\Entity\Movie;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\CastingRepository;
use App\Repository\GenreRepository;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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
        $genres = $genreRepository->findAllOrderedByNameAscQb();

        return $this->render('front/main/home.html.twig', [
            'movies' => $movies,
            'genres' => $genres
        ]);
    }

    /**
     * Affiche un film
     * 
     * @Route("/movie/{slugger}", name="movie_show")
     */
    public function movieShow(Movie $movie = null, CastingRepository $castingRepository, Request $request ,ReviewRepository $reviewRepository)
    {
 
        if ($movie === null) {
            throw $this->createNotFoundException('Film non trouvé.');
        }

        $reviews = $reviewRepository->findAllReviewByMovie($movie);
        //$reviews = $castingRepository->findAll();
        $castings = $castingRepository->findAllByMovieJoinedToPerson($movie);

        if ($reviews === null) {
            throw $this->createNotFoundException('Aucune critique trouvé.');
        }

        $review = new Review();

        $form = $this->createForm(ReviewType::class, $review);

        $form->handlerequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $this->denyAccessUnlessGranted('ROLE_USER');
            $review->setMovie($movie);
            $entityManager = $this->getDoctrine()->getManager();             
            $entityManager->persist($review);            
            $entityManager->flush();              
                
            return $this->redirectToRoute('movie_show',['slug' => $movie->getSlugger(), 'reviews' => $reviews]);
        }
        
        return $this->render('front/main/movie_show.html.twig', [
            'movie' => $movie,
            'castings' => $castings,
            'form' => $form->createView(),
            'reviews' => $reviews,
        ]);
    }
}
