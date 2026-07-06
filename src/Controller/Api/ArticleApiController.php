<?php

namespace App\Controller\Api;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1', name: 'api_v1_')]
class ArticleApiController extends AbstractController
{
    #[Route('/articles', name: 'articles', methods: ['GET'])]
    public function articles(ArticleRepository $articleRepository): JsonResponse
    {
        $articles = $articleRepository->findAllForListing();

        return $this->json($articles, JsonResponse::HTTP_OK, [], ['groups' => 'article:list']);
    }
}
