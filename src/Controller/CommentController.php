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
    ): Response {
        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('user_login_form');
        }
        
        // Récupérer les données du formulaire
        $publicationId = $request->request->get('publication_id');
        $content = $request->request->get('content');
        
        if (!$publicationId || !$content) {
            $this->addFlash('error', 'Tous les champs sont requis.');
            return $this->redirectToRoute('app');
        }
        
        // Rechercher la publication
        $publicationRepository = $entityManager->getRepository(Publication::class);
        $publication = $publicationRepository->find($publicationId);
        
        if (!$publication) {
            $this->addFlash('error', 'Publication non trouvée.');
            return $this->redirectToRoute('app');
        }
        
        // Créer un nouveau commentaire
        $comment = new Comment();
        $comment->setUser($user);
        $comment->setPublication($publication);
        $comment->setContent($content);
        $comment->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('Europe/Paris')));
        
        // Valider l'entité
        $errors = $validator->validate($comment);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            return $this->redirectToRoute('app');
        }
        
        try {
            // Sauvegarder en base de données
            $entityManager->persist($comment);
            $entityManager->flush();
            
            $this->addFlash('success', 'Commentaire ajouté avec succès !');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création du commentaire : ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('app');
    }
}
