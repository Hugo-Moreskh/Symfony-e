<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'user_login_form', methods: ['GET'])]
        public function showLoginForm(): Response
        {
            return $this->render('login/login.html.twig', [
                'error' => null
            ]);
        }


    #[Route('/login', name: 'user_login', methods: ['POST'])]
        public function login(
            Request $request,
            EntityManagerInterface $entityManager,
            UserPasswordHasherInterface $passwordHasher
        ): Response {
            // Récupérer les données du formulaire (pas JSON)
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            if (!$email || !$password) {
                return $this->render('login/login.html.twig', [
                    'error' => 'Email et mot de passe requis.'
                ]);
            }

            // Rechercher l'utilisateur par email
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
                return $this->render('login/login.html.twig', [
                    'error' => 'Identifiants invalides.'
                ]);
            }

            // Connexion réussie : redirection vers une page protégée ou message
            return $this->redirectToRoute('app');
            
        }

}
