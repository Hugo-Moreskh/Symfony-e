<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PublicationController extends AbstractController
{
    #[Route('/publication', name: 'create_publication', methods: ['POST'])]
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
        if (!isset($data['user_id']) || !isset($data['content'])) {
            return new JsonResponse(['error' => 'user_id and content are required'], Response::HTTP_BAD_REQUEST);
        }
        
        // Rechercher l'utilisateur
        $userRepository = $entityManager->getRepository(User::class);
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Créer une nouvelle publication
        $publication = new Publication();
        $publication->setUser($user);
        $publication->setContent($data['content']);
        $publication->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'))); // Ajout de \ devant DateTimeZone
        
        // Ajouter le media_path si fourni
        if (isset($data['media_path'])) {
            $publication->setMediaPath($data['media_path']);
        }
        
        // Valider l'entité
        $errors = $validator->validate($publication);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            // Sauvegarder en base de données
            $entityManager->persist($publication);
            $entityManager->flush();
            
            return new JsonResponse([
                'message' => 'Publication created successfully',
                'publication' => [
                    'id' => $publication->getId(),
                    'content' => $publication->getContent(),
                    'media_path' => $publication->getMediaPath(),
                    'created_at' => $publication->getCreatedAt()->format('Y-m-d H:i:s'),
                    'user' => [
                        'id' => $publication->getUser()->getId(),
                        'email' => $publication->getUser()->getEmail(),
                        'name' => $publication->getUser()->getName()
                    ]
                ]
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Publication creation failed: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
