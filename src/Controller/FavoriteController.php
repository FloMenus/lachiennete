<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('ROLE_CLIENT')]
final class FavoriteController extends AbstractController
{
    #[Route('/favoris', name: 'app_favorites', methods: ['GET'])]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('favorites/index.html.twig', [
            'favorites' => $user->getFavorites(),
        ]);
    }

    #[Route('/article/{id}/favorite', name: 'app_article_favorite', methods: ['POST'])]
    public function toggle(Article $article, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('favorite_'.$article->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();
        $isFavorite = $user->getFavorites()->contains($article);

        if ($isFavorite) {
            $user->removeFavorite($article);
        } else {
            $user->addFavorite($article);
        }

        $em->flush();
        $isFavorite = !$isFavorite;

        if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render('partials/_favorite_btn.stream.html.twig', [
                'article' => $article,
                'isFavorite' => $isFavorite,
            ]);
        }

        return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
    }
}
