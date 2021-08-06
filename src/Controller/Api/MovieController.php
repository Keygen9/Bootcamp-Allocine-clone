<?php

namespace App\Controller\Api;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MovieController extends AbstractController
{
    /**
     * @Route("/api/movies", name="api_movies_get", methods="GET")
     */
    public function index(MovieRepository $mr): Response
    {
        $movies = $mr->findAll();

        // on demande à symfo de serialize sous forme de JSON et d'envoyer
        return $this->json([$movies], 200, [], ['groups' => 'movies_get']);
    }

    /**
     * Create a new movie
     * 
     * @Route("/api/movies", name="api_movies_post", methods="POST")
     */
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        // On récupère le contenu de la requête (du JSON)
        $jsonContent = $request->getContent();

        // On désérialise le JSON vers une entité Movie
        // @see https://symfony.com/doc/current/components/serializer.html#deserializing-an-object
        $movie = $serializer->deserialize($jsonContent, Movie::class, 'json');

        // On valide l'entité avec le service Validator
        $errors = $validator->validate($movie);

        if (count($errors) > 0) {
            // Ici on "cast" (= convertit) notre variable en chaine
            $errorsString = (string) $errors;

            return new Response($errorsString);
        }

        // On persist, on flush
        $entityManager->persist($movie);
        $entityManager->flush();

        // REST nous demande un statut 201 et un header Location: url
        return $this->json(
            $movie,
            // C'est cool d'utiliser les constantes de classe !
            // => ça aide à la lecture du code et au fait de penser objet
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('api_movies_get_item', ['id' => $movie->getId()])],['groups' => 'movies_get']
        );
    }

    /**
     * @Route("/api/movies/{id<\d+>}", name="api_movies_put_item", methods={"PUT", "PATCH"})
     */
    public function itemEdit(Movie $movie, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager, Request $request): Response
    {

        // Film non trouvé
        if ($movie === null) {
            return new JsonResponse(["message" => "Film non trouvé"], Response::HTTP_NOT_FOUND);
        }

        // Récupère les données du POST
        $data = $request->getContent();

        // Création de la question via les données de la requete
        $movie = $serializer->deserialize($data, Movie::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $movie]);

        // On valide l'entité
        $errors = $validator->validate($movie);

        // Affichage des erreurs
        if (count($errors) > 0) {

            // On va créer un joli tableau d'erreurs
            $newErrors = [];

            foreach ($errors as $error) {
                $newErrors[$error->getPropertyPath()][] = $error->getMessage();
            }

            return new JsonResponse(["errors" => $newErrors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Enregistrement en BDD
        $entityManager->flush();

        //return new JsonResponse(["message" => "Film modifié"], Response::HTTP_NO_CONTENT);
        return new JsonResponse(["message" => "Film modifié"], Response::HTTP_OK);
    }

    /**
     * Delete a movie
     * 
     * @Route("/api/movies/{id<\d+>}", name="api_movies_delete", methods="DELETE")
     */
    public function delete(Movie $movie = null, EntityManagerInterface $em)
    {
        if (null === $movie) {

            $error = 'Ce film n\'existe pas';
            return $this->json(['error' => $error], Response::HTTP_NOT_FOUND);
        }

        $em->remove($movie);
        $em->flush();

        $remove = "Le film a bien été supprimé.";

        return $this->json(['remove' => $remove], Response::HTTP_OK);
    }
}
