<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityController extends AbstractController
{
    /**
     * @Route("/api/login", name="api_login", methods={"POST"})
     */
    public function login(): Response
    {
        $user = $this->getUser();

        return $this->json([
            // The getUserIdentifier() method was introduced in Symfony 5.3.
            // In previous versions it was called getUsername()
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ]);
    }
}
