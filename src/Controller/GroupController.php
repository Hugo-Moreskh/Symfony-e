<?php

namespace App\Controller;

use App\Entity\GroupsTable;
use App\Entity\GroupMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    #[Route('/groups', name: 'groups')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('user_login_form');
        }

        $groups = $entityManager->getRepository(GroupsTable::class)
            ->findUserGroups($user->getId());

        return $this->render('group.html.twig', [
            'groups' => $groups
        ]);
    }

    #[Route('/groups/create', name: 'create_group', methods: ['POST'])]
    public function createGroup(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        $group = new GroupsTable();
        $group->setName($request->request->get('name'));
        $group->setDescription($request->request->get('description'));
        $group->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($group);
        $entityManager->flush();

        // Ajouter le créateur comme membre
        $member = new GroupMember();
        $member->setGroupId($group->getId());
        $member->setUserId($user->getId());
        $member->setIsAdmin(true);
        $member->setJoinedAt(new \DateTimeImmutable());

        $entityManager->persist($member);
        $entityManager->flush();

        return $this->redirectToRoute('groups');
    }

    #[Route('/groups/{id}/join', name: 'join_group')]
    public function joinGroup(GroupsTable $group, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $member = new GroupMember();
        $member->setGroupId($group->getId());
        $member->setUserId($user->getId());
        $member->setJoinedAt(new \DateTimeImmutable());

        $entityManager->persist($member);
        $entityManager->flush();

        return $this->redirectToRoute('groups');
    }
}