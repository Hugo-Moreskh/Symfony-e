<?php

namespace App\Controller;

use App\Entity\Publication;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route('/', name: 'app')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les publications avec leurs utilisateurs et commentaires
        $publicationRepository = $entityManager->getRepository(Publication::class);
        $publications = $publicationRepository->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.comments', 'c')
            ->leftJoin('c.user', 'cu')
            ->addSelect('u', 'c', 'cu')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('app/home.html.twig', [
            'publications' => $publications,
            'user' => $this->getUser()
        ]);
    }
}
