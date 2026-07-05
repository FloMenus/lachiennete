<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Image;
use App\Entity\User;
use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_PRESTATAIRE')]
#[Route('/my-articles')]
final class SellerController extends AbstractController
{
    #[Route('', name: 'app_seller_articles', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('seller/index.html.twig', [
            'articles' => $articleRepository->findBySeller($user),
        ]);
    }

    #[Route('/new', name: 'app_seller_article_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/articles')] string $uploadsDir,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $article = new Article();
        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setSeller($user);

            $this->handleImageUploads($form->get('imageFiles')->getData(), $article, $uploadsDir, $slugger);

            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Annonce publiée. Elle est désormais visible.');

            return $this->redirectToRoute('app_seller_articles');
        }

        return $this->render('seller/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/edit', name: 'app_seller_article_edit', methods: ['GET', 'POST'])]
    public function edit(
        Article $article,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/articles')] string $uploadsDir,
    ): Response {
        $this->denyAccessUnlessGranted('ARTICLE_EDIT', $article);

        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUploads($form->get('imageFiles')->getData(), $article, $uploadsDir, $slugger);

            $em->flush();

            $this->addFlash('success', 'Annonce mise à jour.');

            return $this->redirectToRoute('app_seller_article_edit', ['id' => $article->getId()]);
        }

        return $this->render('seller/edit.html.twig', [
            'form' => $form,
            'article' => $article,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_seller_article_delete', methods: ['POST'])]
    public function delete(
        Article $article,
        Request $request,
        EntityManagerInterface $em,
        #[Autowire('%kernel.project_dir%/public/uploads/articles')] string $uploadsDir,
    ): Response {
        $this->denyAccessUnlessGranted('ARTICLE_EDIT', $article);

        if (!$this->isCsrfTokenValid('delete_article_'.$article->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        foreach ($article->getImages() as $image) {
            $this->removeImageFile($image, $uploadsDir);
        }

        $em->remove($article);
        $em->flush();

        $this->addFlash('success', 'Annonce supprimée.');

        return $this->redirectToRoute('app_seller_articles');
    }

    #[Route('/{articleId}/images/{imageId}/delete', name: 'app_seller_image_delete', methods: ['POST'])]
    public function deleteImage(
        int $articleId,
        int $imageId,
        Request $request,
        EntityManagerInterface $em,
        ArticleRepository $articleRepository,
        ImageRepository $imageRepository,
        #[Autowire('%kernel.project_dir%/public/uploads/articles')] string $uploadsDir,
    ): Response {
        $article = $articleRepository->find($articleId);
        $image = $imageRepository->find($imageId);

        if (null === $article || null === $image || $image->getArticle() !== $article) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('ARTICLE_EDIT', $article);

        if (!$this->isCsrfTokenValid('delete_image_'.$image->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->removeImageFile($image, $uploadsDir);
        $em->remove($image);
        $em->flush();

        return $this->redirectToRoute('app_seller_article_edit', ['id' => $article->getId()]);
    }

    private function handleImageUploads(array $files, Article $article, string $uploadsDir, SluggerInterface $slugger): void
    {
        $position = $article->getImages()->count();

        foreach ($files as $file) {
            $filename = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                .'-'.uniqid()
                .'.'.$file->guessExtension();

            $file->move($uploadsDir, $filename);

            $image = new Image();
            $image->setFilename($filename)->setPosition($position++);
            $article->addImage($image);
        }
    }

    private function removeImageFile(Image $image, string $uploadsDir): void
    {
        $path = $uploadsDir.'/'.$image->getFilename();
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
