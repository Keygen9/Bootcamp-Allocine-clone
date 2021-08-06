<?php

namespace App\Controller\Back;

use App\Entity\Movie;
use App\EventListener\MovieSlug;
use App\Form\MovieType;
use App\Repository\MovieRepository;
use App\Repository\CastingRepository;
use App\Service\Slugger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\History;

class MovieController extends AbstractController
{
    /**
     * Lister les films
     *
     * @Route("/back/movie/browse", name="back_movie_browse", methods={"GET"})
     */
    public function browse(MovieRepository $movieRepository): Response
    {
        $movies = $movieRepository->findAllOrderedByTitleAscQb();

        return $this->render('back/movie/browse.html.twig', [
            'movies' => $movies,
        ]);
    }

    /**
     * Afficher un film
     *
     * @Route("/back/movie/read/{id}", name="back_movie_read", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function read(Movie $movie = null, CastingRepository $castingRepository): Response
    {
        // 404 ?
        if ($movie === null) {
            throw $this->createNotFoundException('Film non trouvé.');
        }

        // Jointure sur les personnes
        $castings = $castingRepository->findAllByMovieJoinedToPerson($movie);

        return $this->render('back/movie/read.html.twig', [
            'movie' => $movie,
            'castings' => $castings,
        ]);
    }

    /**
     * Ajouter un film
     *
     * @Route("/back/movie/add", name="back_movie_add", methods={"GET", "POST"})
     */
    public function add(Request $request, Slugger $slugger): Response
    {
        $movie = new Movie();

        $form = $this->createForm(MovieType::class, $movie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($movie);
            $em->flush();

            return $this->redirectToRoute('back_movie_read', ['id' => $movie->getId()]);
        }

        // Affiche le form
        return $this->render('back/movie/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Editer un film
     * 
     * @Route("/back/movie/edit/{id<\d+>}", name="back_movie_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Movie $movie): Response
    {
        // 404 ?
        if ($movie === null) {
            throw $this->createNotFoundException('Film non trouvé.');
        }

            $form = $this->createForm(MovieType::class, $movie);

            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                // Pas de persist() pour un edit
                $em->flush();
    
                return $this->redirectToRoute('back_movie_read', ['id' => $movie->getId()]);
            }
        
        // Affiche le form
        return $this->render('back/movie/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Supprimer un film
     * @todo en GET à convertir en POST ou mieux en DELETE
     * 
     * @Route("/back/movie/delete/{id<\d+>}", name="back_movie_delete", methods={"GET"})
     */
    public function delete(Movie $movie = null, EntityManagerInterface $entityManager): Response
    {
        // 404 ?
        // Conditions Yoda
        // @link https://fr.wikipedia.org/wiki/Condition_Yoda
        if (null === $movie) {
            throw $this->createNotFoundException("Le film n'existe pas.");
        }

        $entityManager->remove($movie);
        $entityManager->flush();
        //$this->addFlash('success', 'Le film a été supprimé.');

        return $this->redirectToRoute('back_movie_browse');
    }
}
