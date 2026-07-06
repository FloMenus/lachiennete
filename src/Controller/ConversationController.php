<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageFormType;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Security\ConversationVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('ROLE_USER')]
final class ConversationController extends AbstractController
{
    public static function topic(Conversation $conversation): string
    {
        return '/conversations/' . $conversation->getId();
    }

    #[Route('/article/{id}/contact', name: 'app_conversation_start', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function start(Article $article, Request $request, ConversationRepository $conversations, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('contact_' . $article->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($article->getSeller() === $user) {
            $this->addFlash('error', 'Vous ne pouvez pas contacter votre propre annonce.');

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        $conversation = $conversations->findOneBy(['article' => $article, 'buyer' => $user]);

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setArticle($article);
            $conversation->setBuyer($user);
            $em->persist($conversation);
            $em->flush();
        }

        return $this->redirectToRoute('app_conversation_show', ['id' => $conversation->getId()]);
    }

    #[Route('/conversations', name: 'app_conversations', methods: ['GET'])]
    public function index(ConversationRepository $conversations, MessageRepository $messages): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('conversation/index.html.twig', [
            'conversations' => $conversations->findForUser($user),
            'unreadCounts' => $messages->countUnreadByConversation($user),
        ]);
    }

    #[Route('/conversations/{id}', name: 'app_conversation_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(ConversationVoter::VIEW, subject: 'conversation')]
    public function show(Conversation $conversation, MessageRepository $messages): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $messages->markConversationAsRead($conversation, $user);

        return $this->render('conversation/show.html.twig', [
            'conversation' => $conversation,
            'form' => $this->createForm(MessageFormType::class),
        ]);
    }

    #[Route('/conversations/{id}/message', name: 'app_conversation_send', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(ConversationVoter::VIEW, subject: 'conversation')]
    public function send(Conversation $conversation, Request $request, EntityManagerInterface $em, HubInterface $hub): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $message = new Message();
        $form = $this->createForm(MessageFormType::class, $message);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('conversation/show.html.twig', [
                'conversation' => $conversation,
                'form' => $form,
            ], new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY));
        }

        $message->setConversation($conversation);
        $message->setAuthor($user);
        $em->persist($message);
        $em->flush();

        try {
            $hub->publish(new Update(
                self::topic($conversation),
                $this->renderView('conversation/_message.stream.html.twig', ['message' => $message]),
                private: true,
            ));
        } catch (\Throwable) {}

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render('conversation/send.stream.html.twig', [
                'message' => $message,
                'conversation' => $conversation,
                'form' => $this->createForm(MessageFormType::class),
            ]);
        }

        return $this->redirectToRoute('app_conversation_show', ['id' => $conversation->getId()]);
    }
}
