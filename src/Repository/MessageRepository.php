<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function markConversationAsRead(Conversation $conversation, User $reader): void
    {
        $this->getEntityManager()->createQuery(
            'UPDATE App\Entity\Message m
             SET m.isRead = true
             WHERE m.conversation = :conversation
               AND m.author != :reader
               AND m.isRead = false'
        )
            ->setParameter('conversation', $conversation)
            ->setParameter('reader', $reader)
            ->execute();
    }

    public function countUnreadByConversation(User $user): array
    {
        $rows = $this->getEntityManager()->createQuery(
            'SELECT IDENTITY(m.conversation) AS id, COUNT(m.id) AS nb
             FROM App\Entity\Message m
             JOIN m.conversation c
             JOIN c.article a
             WHERE m.isRead = false
               AND m.author != :user
               AND (c.buyer = :user OR a.seller = :user)
             GROUP BY m.conversation'
        )
            ->setParameter('user', $user)
            ->getArrayResult();

        return array_column($rows, 'nb', 'id');
    }
}
