<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'last_username' => $authUtils->getLastUsername(),
            'error' => $authUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony will intercept this
    }
}
