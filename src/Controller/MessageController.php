<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Entity\User;
use App\Entity\Friendship;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    #[Route('/messages', name: 'messages')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('user_login_form');
        }

        $conversations = $entityManager->getRepository(Messages::class)
            ->findConversations($user->getId());

        return $this->render('messages/message.html.twig', [
            'conversations' => $conversations
        ]);
    }

    #[Route('/messages/{id}', name: 'conversation')]
    public function conversation(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $otherUser = $entityManager->getRepository(User::class)->find($id);

        if (!$user || !$otherUser) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $friendship = $entityManager->getRepository(Friendship::class)->findOneBy([
            'user' => $user,
            'friend' => $otherUser
        ]) ?? $entityManager->getRepository(Friendship::class)->findOneBy([
            'user' => $otherUser,
            'friend' => $user
        ]);

        if (!$friendship) {
            throw $this->createNotFoundException('Relation d\'amitié non trouvée');
        }

        if (!$friendship->getIdRoom()) {
            $friendship->setIdRoom(uniqid('room_'));
            $entityManager->flush();
        }

        $messages = $entityManager->getRepository(Messages::class)
            ->findConversationMessages($user->getId(), $otherUser->getId());

        $conversations = $entityManager->getRepository(Messages::class)
            ->findConversations($user->getId());

        return $this->render('messages/message.html.twig', [
            'otherUser' => $otherUser,
            'messages' => $messages,
            'conversations' => $conversations,
            'roomId' => $friendship->getIdRoom()
        ]);
    }

    #[Route('/messages/send', name: 'send_message', methods: ['POST'])]
    public function sendMessage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $content = $request->request->get('content');
        $recipientId = $request->request->get('recipient_id');
        $recipient = $entityManager->getRepository(User::class)->find($recipientId);

        if (!$recipient || !$content) {
            return new Response('Données invalides', 400);
        }

        $message = new Messages();
        $message->setSender($user);
        $message->setRecipient($recipient);
        $message->setContent($content);
        $message->setSendAt(new \DateTimeImmutable());

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('conversation', ['id' => $recipientId]);
    }
}
