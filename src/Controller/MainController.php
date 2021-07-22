<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\CastingRepository;
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
    public function home(MovieRepository $movieRepository): Response
    {
        // Les films par ordre alphabétique sur le titre
        $movies = $movieRepository->findAllOrderedByTitleAscDql();

        return $this->render('main/home.html.twig', [
            'movies' => $movies,
        ]);
    }

    /**
     * Affiche un film
     * 
     * @Route("/movie/{id<\d+>}", name="movie_show")
     */
    public function movieShow(Movie $movie = null, CastingRepository $castingRepository, Request $request ,ReviewRepository $reviewRepository ,$id)
    {
 
        if ($movie === null) {
            throw $this->createNotFoundException('Film non trouvé.');
        }

        $reviews = $reviewRepository->findAllReviewByMovie($movie);
        //$reviews = $castingRepository->findAll();
        $castings = $castingRepository->findAllByMovieJoinedToPerson($movie->getId());

        if ($reviews === null) {
            throw $this->createNotFoundException('Aucune critique trouvé.');
        }

        $review = new Review();

        $form = $this->createForm(ReviewType::class, $review);

        $form->handlerequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $review->setMovie($movie);
            $entityManager = $this->getDoctrine()->getManager();             
            $entityManager->persist($review);            
            $entityManager->flush();              
                
            return $this->redirectToRoute('movie_show',['id' => $id, 'reviews' => $reviews]);
        }
        
        return $this->render('main/movie_show.html.twig', [
            'movie' => $movie,
            'castings' => $castings,
            'form' => $form->createView(),
            'reviews' => $reviews,
        ]);
    }
}
