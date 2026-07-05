<?php

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 5)]
final class BannedUserListener
{
    private const ALLOWED_ROUTES = ['app_banned', 'app_logout'];

    public function __construct(
        private readonly Security $security,
        private readonly RouterInterface $router,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (null === $route || str_starts_with($route, '_') || in_array($route, self::ALLOWED_ROUTES, true)) {
            return;
        }

        $user = $this->security->getUser();

        if (null === $user || !in_array('ROLE_BANNI', $user->getRoles(), true)) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->router->generate('app_banned')));
    }
}
