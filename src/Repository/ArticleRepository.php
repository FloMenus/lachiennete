<?php

namespace App\Repository;

use App\Entity\Article;
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
}
