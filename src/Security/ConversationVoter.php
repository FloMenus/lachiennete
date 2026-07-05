<?php

namespace App\Security;

use App\Entity\Conversation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ConversationVoter extends Voter
{
    public const VIEW = 'CONVERSATION_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof Conversation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Conversation $conversation */
        $conversation = $subject;

        return $conversation->getBuyer() === $user
            || $conversation->getArticle()?->getSeller() === $user;
    }
}
