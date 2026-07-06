<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Review;
use App\Entity\User;
use App\Form\ReviewFormType;
use App\Repository\PurchaseRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ReviewController extends AbstractController
{
    #[Route('/articles/{id}/review', name: 'app_review_new', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function new(
        Article $article,
        Request $request,
        EntityManagerInterface $em,
        PurchaseRepository $purchaseRepository,
        ReviewRepository $reviewRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$purchaseRepository->hasPurchased($user, $article)) {
            $this->addFlash('error', 'Seuls les acheteurs peuvent laisser un avis sur cette annonce.');

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        if (null !== $reviewRepository->findOneBy(['author' => $user, 'article' => $article])) {
            $this->addFlash('error', 'Vous avez déjà laissé un avis sur cette annonce.');

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        $review = new Review();
        $review->setAuthor($user)
            ->setArticle($article)
            ->setRating(5);

        $form = $this->createForm(ReviewFormType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($review);
            $em->flush();

            $this->addFlash('success', 'Avis enregistré. Votre opinion est désormais publique et archivée.');

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        return $this->render('review/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }
}
