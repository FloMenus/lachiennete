<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @return int[] ids des articles déjà notés par cet auteur
     */
    public function findReviewedArticleIds(User $author): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.article) AS articleId')
            ->andWhere('r.author = :author')
            ->setParameter('author', $author)
            ->getQuery()
            ->getScalarResult();

        return array_map('intval', array_column($rows, 'articleId'));
    }
}
