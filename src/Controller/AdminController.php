<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository, UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['createdAt' => 'DESC']);
        $bannedCount = count(array_filter($users, fn(User $u) => in_array('ROLE_BANNI', $u->getRoles())));

        return $this->render('admin/index.html.twig', [
            'articleCount' => $articleRepository->count(),
            'userCount' => count($users),
            'bannedCount' => $bannedCount,
        ]);
    }

    #[Route('/articles', name: 'app_admin_articles', methods: ['GET'])]
    public function articles(ArticleRepository $articleRepository): Response
    {
        return $this->render('admin/articles.html.twig', [
            'articles' => $articleRepository->findAllForListing(),
        ]);
    }

    #[Route('/articles/{id}/delete', name: 'app_admin_article_delete', methods: ['POST'])]
    public function deleteArticle(Article $article, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_article_'.$article->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($article);
        $em->flush();

        $this->addFlash('success', 'Annonce supprimée.');

        return $this->redirectToRoute('app_admin_articles');
    }

    #[Route('/users', name: 'app_admin_users', methods: ['GET'])]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/users/{id}/ban', name: 'app_admin_user_ban', methods: ['POST'])]
    public function banUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('ban_user_'.$user->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Impossible de se bannir soi-même.');

            return $this->redirectToRoute('app_admin_users');
        }

        $isBanned = in_array('ROLE_BANNI', $user->getRoles());

        if ($isBanned) {
            $user->setRoles(['ROLE_CLIENT']);
            $this->addFlash('success', sprintf('%s %s a été débanni.', $user->getFirstname(), $user->getLastname()));
        } else {
            $user->setRoles(['ROLE_BANNI']);
            $this->addFlash('success', sprintf('%s %s a été banni.', $user->getFirstname(), $user->getLastname()));
        }

        $em->flush();

        return $this->redirectToRoute('app_admin_users');
    }
}
