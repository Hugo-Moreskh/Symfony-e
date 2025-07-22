<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\LoginFormAuthenticator; // ton authenticator
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register_form', methods: ['GET'])]
    public function showRegisterForm(): Response
    {
        return $this->render('register/register.html.twig', [
            'error' => null
        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserAuthenticatorInterface $userAuthenticator,       // ajouté
        LoginFormAuthenticator $authenticator                 // ajouté
    ): Response {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $name = $request->request->get('name');

        if (!$email || !$password) {
            return $this->render('register/register.html.twig', [
                'error' => 'Email et mot de passe requis.',
            ]);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        if ($name) {
            $user->setName($name);
        }

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }

            return $this->render('register/register.html.twig', [
                'error' => implode(', ', $messages)
            ]);
        }

        try {
            $entityManager->persist($user);
            $entityManager->flush();

            // Connexion automatique puis redirection vers la route 'app' ("/")
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request,
            );

        } catch (\Exception $e) {
            return $this->render('register/register.html.twig', [
                'error' => 'Erreur lors de l\'inscription : ' . $e->getMessage()
            ]);
        }
    }
}
