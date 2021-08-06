<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\User;
use App\Entity\Genre;
use App\Entity\Movie;
use App\Entity\Person;
use App\Entity\Casting;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\DataFixtures\Provider\MovieDbProvider;
use App\Service\Slugger;
use Doctrine\DBAL\Connection;

class AppFixtures extends Fixture
{
    private $slugger;
    private $dbal;

    public function __construct(Slugger $slugger, Connection $dbal)
    {
        $this->slugger = $slugger;
        $this->dbal = $dbal;
    }

    private function truncate()
    {
        // On passen mode SQL ! On cause avec MySQL
        // Désactivation des contraintes FK
        $this->dbal->executeQuery('SET foreign_key_checks = 0');
        // On tronque
        $this->dbal->executeQuery('TRUNCATE TABLE casting');
        $this->dbal->executeQuery('TRUNCATE TABLE department');
        $this->dbal->executeQuery('TRUNCATE TABLE genre');
        $this->dbal->executeQuery('TRUNCATE TABLE job');
        $this->dbal->executeQuery('TRUNCATE TABLE movie');
        $this->dbal->executeQuery('TRUNCATE TABLE movie_genre');
        $this->dbal->executeQuery('TRUNCATE TABLE person');
        $this->dbal->executeQuery('TRUNCATE TABLE review');
        $this->dbal->executeQuery('TRUNCATE TABLE team');
        $this->dbal->executeQuery('TRUNCATE TABLE user');
        // etc.
    }

    public function load(ObjectManager $manager)
    {
        $this->truncate();
        // Créons une instance de Faker
        // (avec use Faker;)
        $faker = Faker\Factory::create('fr_FR');

        // Si on veut toujours les mêmes données
        $faker->seed('BABAR');

        // Notre fournisseur de données, ajouté à Faker
        $faker->addProvider(new MovieDbProvider());

        // 3 users "en dur" : USER, MANAGER, ADMIN
        $user = new User();
        $user->setEmail('user@user.com');
        // user
        $user->setPassword('$2y$13$h.eZWrS5PJya7zNMNsKcXe8LUSVBtN2PBy8WHxmdHgAFjHG/rW.dG');
        $user->setRoles(['ROLE_USER']);

        $manager->persist($user);

        $admin = new User();
        $admin->setEmail('admin@admin.com');
        // user via "bin/console security:hash-password"
        $admin->setPassword('$2y$13$VibctPTsjQG5iGN/BokaFuJa9oZDpt88a9s6TJ/vdd26EfcmUyAym');
        $admin->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);

        $managers = new User();
        $managers->setEmail('manager@manager.com');
        // user via "bin/console security:hash-password"
        $managers->setPassword('$2y$13$npbaA2iXBRt4SDOn.fMQuOmKUrbjbg0E7B1uDqPkdVEhIA3fVlCma');
        $managers->setRoles(['ROLE_MANAGER']);

        $manager->persist($managers);

        // 20 genres
        // créés avant les films car à associer depuis chaque film

        // Liste des genres
        $genresList = [];

        for ($i = 1; $i <= 20; $i++) {

            // Nouveau genre
            $genre = new Genre();
            $genre->setName($faker->unique()->movieGenre());

            // On stocke le genre pour association ultérieur avec film
            $genresList[] = $genre; // array_push($genresList, $genre);

            // Doctrine Persist
            $manager->persist($genre);
        }

        // dump($genresList);

        // 20 films
        
        $moviesList = [];
       
        for ($i = 1; $i <= 20; $i++) {


            $movie = new Movie();
            $movie->setTitle($faker->unique()->movieTitle());
            $movie->setDuration($faker->numberBetween(15, 360));
            $movie->setPoster($faker->imageUrl(300, 400));
            $movie->setRating($faker->numberBetween(1, 5));
            $movie->setReleaseDate($faker->dateTimeBetween('-50 years'));
            $movie->setSlugger($this->slugger->sluggerMovie($movie->getTitle()));

            // Association de 1 à 3 genres au hasard
            for ($r = 1; $r <= mt_rand(1, 3); $r++) {
                // https://www.php.net/manual/fr/function.array-rand.php
                // On va chercher un index au hasard (array_rand)
                // dans le taleau des genres
                $movie->addGenre($genresList[array_rand($genresList)]);
            }

            // On stocke...
            $moviesList[] = $movie;

            $manager->persist($movie);
        }

        // 20 personnes

        $personsList = [];

        for ($i = 1; $i <= 20; $i++) {

            $person = new Person();
            $person->setFirstname($faker->firstName());
            $person->setLastname($faker->lastName());

            $personsList[] = $person;

            $manager->persist($person);
        }

        // Les castings
        for ($i = 1; $i < 100; $i++) {
            $casting = new Casting();
            $casting->setRole($faker->firstName());
            $casting->setCreditOrder(mt_rand(1, 10));

            // On va chercher un film au hasard dans la liste des films créée au-dessus
            // Variante avec mt_rand et count
            $randomMovie = $moviesList[mt_rand(0, count($moviesList) - 1)];
            $casting->setMovie($randomMovie);

            // On va chercher une personne au hasard dans la liste des personnes créée au-dessus
            // Variante avec array_rand()
            $randomPerson = $personsList[array_rand($personsList)];
            $casting->setPerson($randomPerson);

            // On persist
            $manager->persist($casting);
        }

        $manager->flush();
    }
}
