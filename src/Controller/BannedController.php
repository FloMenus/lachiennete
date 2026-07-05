<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_BANNI')]
final class BannedController extends AbstractController
{
    #[Route('/banned', name: 'app_banned', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('banned.html.twig', [], new Response(null, 403));
    }
}
