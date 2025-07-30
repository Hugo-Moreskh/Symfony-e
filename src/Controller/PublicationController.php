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
    ): Response {
        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('user_login_form');
        }
        
        // Récupérer le contenu du formulaire
        $content = $request->request->get('content');
        
        if (!$content) {
            $this->addFlash('error', 'Le contenu est requis.');
            return $this->redirectToRoute('app');
        }
        
        // Créer une nouvelle publication
        $publication = new Publication();
        $publication->setUser($user);
        $publication->setContent($content);
        $publication->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
        
        // Gérer l'upload de média
        $mediaFile = $request->files->get('media');
        if ($mediaFile) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
            
            if (in_array($mediaFile->getMimeType(), $allowedTypes)) {
                $originalFilename = pathinfo($mediaFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$mediaFile->guessExtension();

                try {
                    $uploadsDirectory = $this->getParameter('kernel.project_dir').'/public/uploads';
                    if (!is_dir($uploadsDirectory)) {
                        mkdir($uploadsDirectory, 0755, true);
                    }
                    $mediaFile->move($uploadsDirectory, $newFilename);
                    $publication->setMediaPath($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                }
            } else {
                $this->addFlash('error', 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF, WebP, MP4 ou WebM.');
                return $this->redirectToRoute('app');
            }
        }
        
        // Valider l'entité
        $errors = $validator->validate($publication);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            return $this->redirectToRoute('app');
        }
        
        try {
            // Sauvegarder en base de données
            $entityManager->persist($publication);
            $entityManager->flush();
            
            $this->addFlash('success', 'Publication créée avec succès !');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('app');
    }
}
