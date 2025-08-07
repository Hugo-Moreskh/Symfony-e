<?php

namespace App\Repository;

use App\Entity\Messages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Messages::class);
    }

    public function findConversations(int $userId)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.sender', 'sender')
            ->leftJoin('m.recipient', 'recipient')
            ->where('sender.id = :userId OR recipient.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.send_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findConversationMessages(int $userId, int $otherUserId)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->leftJoin('m.recipient', 'r')
            ->where('
                (s.id = :userId AND r.id = :otherUserId)
                OR (s.id = :otherUserId AND r.id = :userId)
            ')
            ->setParameter('userId', $userId)
            ->setParameter('otherUserId', $otherUserId)
            ->orderBy('m.send_at', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
