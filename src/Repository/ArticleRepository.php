<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return Article[]
     */
    public function findAllForListing(): array
    {
        return $this->createQueryBuilder('a')
            ->addSelect('category', 'seller', 'images', 'tags')
            ->leftJoin('a.category', 'category')
            ->leftJoin('a.seller', 'seller')
            ->leftJoin('a.images', 'images')
            ->leftJoin('a.tags', 'tags')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[]
     */
    public function findBySeller(User $seller): array
    {
        return $this->createQueryBuilder('a')
            ->addSelect('category', 'images', 'tags')
            ->leftJoin('a.category', 'category')
            ->leftJoin('a.images', 'images')
            ->leftJoin('a.tags', 'tags')
            ->andWhere('a.seller = :seller')
            ->setParameter('seller', $seller)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneForShow(int $id): ?Article
    {
        return $this->createQueryBuilder('a')
            ->addSelect('category', 'seller', 'images', 'tags')
            ->leftJoin('a.category', 'category')
            ->leftJoin('a.seller', 'seller')
            ->leftJoin('a.images', 'images')
            ->leftJoin('a.tags', 'tags')
            ->andWhere('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
