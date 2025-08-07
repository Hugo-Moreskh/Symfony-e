<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Friendship;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FriendController extends AbstractController
{
   #[Route('/friends', name: 'friends_list')]
public function list(Request $request, EntityManagerInterface $entityManager): Response
{
    /** @var User $user */
    $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('user_login_form');
    }

    // Récupérer le terme de recherche
    $searchTerm = $request->query->get('search');

    // Rechercher les utilisateurs si un terme de recherche est présent
    $searchResults = [];
    if ($searchTerm) {
        $searchResults = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.email LIKE :search OR u.name LIKE :search')
            ->andWhere('u.id != :currentUser')
            ->setParameter('search', '%'.$searchTerm.'%')
            ->setParameter('currentUser', $user->getId())
            ->getQuery()
            ->getResult();
    }

    // ✅ Récupérer la liste des amis (relations acceptées où le user est l’émetteur OU le destinataire)
    $friendships = $entityManager->getRepository(Friendship::class)
        ->createQueryBuilder('f')
        ->leftJoin('f.friend', 'friend')
        ->leftJoin('f.user', 'userSender')
        ->where('f.accepted_at IS NOT NULL')
        ->andWhere('f.user = :me OR f.friend = :me')
        ->setParameter('me', $user)
        ->select('f', 'friend', 'userSender')
        ->getQuery()
        ->getResult();

    // ✅ Récupérer les demandes d’amitié reçues et non acceptées
    $pendingRequests = $entityManager->getRepository(Friendship::class)
        ->createQueryBuilder('f')
        ->leftJoin('f.user', 'requestSender')
        ->where('f.friend = :me')
        ->andWhere('f.accepted_at IS NULL')
        ->setParameter('me', $user)
        ->select('f', 'requestSender')
        ->getQuery()
        ->getResult();

    return $this->render('friend/friend.html.twig', [
        'friends' => $friendships,
        'searchResults' => $searchResults,
        'pendingRequests' => $pendingRequests,
        'searchTerm' => $searchTerm
    ]);
}


    #[Route('/friends/accept/{id}', name: 'accept_friend')]
    public function acceptFriend(Friendship $friendship, EntityManagerInterface $entityManager): Response
    {
        $friendship->setAcceptedAt(new \DateTimeImmutable());
        $entityManager->flush();

        return $this->redirectToRoute('friends_list');
    }

    #[Route('/friends/remove/{id}', name: 'remove_friend')]
    public function removeFriend(Friendship $friendship, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($friendship);
        $entityManager->flush();

        return $this->redirectToRoute('friends_list');
    }
#[Route('/friends/add/{id}', name: 'add_friend')]
public function addFriend(User $userToAdd, EntityManagerInterface $entityManager): Response
{
    /** @var User $user */
    $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('user_login_form');
    }

    // Vérifier si une demande existe déjà (dans les deux sens)
    $existingFriendship = $entityManager->getRepository(Friendship::class)->findOneBy([
        'user' => $user,
        'friend' => $userToAdd
    ]);

    $reverseFriendship = $entityManager->getRepository(Friendship::class)->findOneBy([
        'user' => $userToAdd,
        'friend' => $user
    ]);

    if (!$existingFriendship && !$reverseFriendship) {
        $friendship = new Friendship();
        $friendship->setUser($user);
        $friendship->setFriend($userToAdd);
        $friendship->setCreatedAt(new \DateTimeImmutable());
        // Générer un id_room unique
        $friendship->setIdRoom('room_' . uniqid());

        $entityManager->persist($friendship);
        $entityManager->flush();

        $this->addFlash('success', 'Demande d\'ami envoyée !');
    } else {
        $this->addFlash('error', 'Une demande d\'ami existe déjà avec cet utilisateur.');
    }

    return $this->redirectToRoute('friends_list');
}
#[Route('/friends/{id}/message', name: 'friend_message')]
public function message(User $friend, EntityManagerInterface $entityManager): Response
{
    /** @var User $user */
    $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('user_login_form');
    }

    // Vérifier si ils sont amis
    $friendship = $entityManager->getRepository(Friendship::class)
        ->createQueryBuilder('f')
        ->where('(f.user = :user AND f.friend = :friend) OR (f.user = :friend AND f.friend = :user)')
        ->andWhere('f.accepted_at IS NOT NULL')
        ->setParameter('user', $user)
        ->setParameter('friend', $friend)
        ->getQuery()
        ->getOneOrNullResult();

    if (!$friendship) {
        $this->addFlash('error', 'Vous devez être amis pour échanger des messages');
        return $this->redirectToRoute('friends_list');
    }

    // ✅ Récupérer tous les messages entre les deux utilisateurs
    $messages = $entityManager->getRepository(\App\Entity\Messages::class)->createQueryBuilder('m')
        ->where('(m.sender = :user AND m.recipient = :friend) OR (m.sender = :friend AND m.recipient = :user)')
        ->setParameter('user', $user)
        ->setParameter('friend', $friend)
        ->orderBy('m.send_at', 'ASC')
        ->getQuery()
        ->getResult();

    // ✅ On génère un roomId unique pour Socket.IO
    $roomId = $friendship->getIdRoom();

    return $this->render('messages/message.html.twig', [
        'friend' => $friend,
        'messages' => $messages,
        'roomId' => $roomId,
    ]);
}


}