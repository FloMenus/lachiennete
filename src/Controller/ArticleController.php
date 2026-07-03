<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArticleController extends AbstractController
{
    #[Route('/annonce/{id}', name: 'app_article_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->findOneForShow($id);

        if (null === $article) {
            throw $this->createNotFoundException('Cette annonce n\'existe pas ou a été retirée.');
        }

        $ratings = $article->getReviews()->map(fn ($review) => $review->getRating())->toArray();

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'averageRating' => [] !== $ratings ? array_sum($ratings) / \count($ratings) : null,
        ]);
    }
}
