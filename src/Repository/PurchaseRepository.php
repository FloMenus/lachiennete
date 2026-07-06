<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Purchase;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Purchase>
 */
class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

    /**
     * @return Purchase[]
     */
    public function findByCustomer(User $customer): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('article', 'images')
            ->leftJoin('p.article', 'article')
            ->leftJoin('article.images', 'images')
            ->andWhere('p.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('p.purchasedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function hasPurchased(User $customer, Article $article): bool
    {
        return $this->count(['customer' => $customer, 'article' => $article]) > 0;
    }
}
