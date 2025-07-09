<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Publication;
use App\Entity\User;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommentController extends AbstractController
{
    #[Route('/comment', name: 'create_comment', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        // Récupérer les données JSON de la requête
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }
        
        // Vérifier que les champs requis sont présents
        if (!isset($data['user_id']) || !isset($data['publication_id']) || !isset($data['content'])) {
            return new JsonResponse(['error' => 'user_id, publication_id and content are required'], Response::HTTP_BAD_REQUEST);
        }
        
        // Rechercher l'utilisateur
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->find($data['user_id']);
        
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Rechercher la publication
        $publicationRepository = $entityManager->getRepository(Publication::class);
        $publication = $publicationRepository->find($data['publication_id']);
        
        if (!$publication) {
            return new JsonResponse(['error' => 'Publication not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Créer un nouveau commentaire
        $comment = new Comment();
        $comment->setUser($user);
        $comment->setPublication($publication);
        $comment->setContent($data['content']);
        $comment->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('Europe/Paris')));
        
        // Valider l'entité
        $errors = $validator->validate($comment);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            // Sauvegarder en base de données
            $entityManager->persist($comment);
            $entityManager->flush();
            
            return new JsonResponse([
                'message' => 'Comment created successfully',
                'comment' => [
                    'id' => $comment->getId(),
                    'content' => $comment->getContent(),
                    'created_at' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
                    'user' => [
                        'id' => $comment->getUser()->getId(),
                        'email' => $comment->getUser()->getEmail(),
                        'name' => $comment->getUser()->getName()
                    ],
                    'publication' => [
                        'id' => $comment->getPublication()->getId(),
                        'content' => substr($comment->getPublication()->getContent(), 0, 50) . '...' // Aperçu du contenu
                    ]
                ]
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Comment creation failed: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
