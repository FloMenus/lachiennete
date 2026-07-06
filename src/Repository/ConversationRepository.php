<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findForUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->addSelect('a', 'b', 's')
            ->join('c.article', 'a')
            ->join('c.buyer', 'b')
            ->join('a.seller', 's')
            ->leftJoin('c.messages', 'm')
            ->addSelect('COALESCE(MAX(m.sentAt), c.createdAt) AS HIDDEN lastActivityAt')
            ->andWhere('c.buyer = :user OR a.seller = :user')
            ->setParameter('user', $user)
            ->groupBy('c.id')
            ->addGroupBy('a.id')
            ->addGroupBy('b.id')
            ->addGroupBy('s.id')
            ->orderBy('lastActivityAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
