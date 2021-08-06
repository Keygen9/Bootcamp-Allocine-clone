<?php

namespace App\Tests\Controller\Back;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Teste les accès pour le ROLE_MANAGER
 */
class MovieControllerManagerTest extends WebTestCase
{
    /**
     * Browse
     */
    public function testBrowse(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('manager@manager.com');

        // simulate $testUser being logged in
        $client->loginUser($testUser);
        
        $crawler = $client->request('GET', '/back/movie/browse');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Read
     */
    public function testRead(): void
    {
        $client = static::createClient();
        
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('manager@manager.com');

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/back/movie/read/1');

        $this->assertResponseIsSuccessful();
    }

    /**
     * add...
     */
    public function testAdd(): void
    {
        $client = static::createClient();
    
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('manager@manager.com');

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/back/movie/add');

        $this->assertResponseStatusCodeSame(403);
    }
}
