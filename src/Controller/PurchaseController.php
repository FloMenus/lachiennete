<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Purchase;
use App\Entity\ShippingAddress;
use App\Entity\User;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_USER')]
final class PurchaseController extends AbstractController
{
    #[Route('/checkout/{id}', name: 'app_checkout', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function checkout(
        Article $article,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        MailerInterface $mailer,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($redirect = $this->guardPurchasable($article, $user)) {
            return $redirect;
        }

        $shippingAddresses = $user->getAddresses()
            ->filter(fn ($address) => $address instanceof ShippingAddress)
            ->getValues();

        $errors = [];

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('checkout_'.$article->getId(), $request->getPayload()->getString('_token'))) {
                throw $this->createAccessDeniedException();
            }

            $deliveryAddress = $this->resolveDeliveryAddress($request, $user, $shippingAddresses, $validator, $errors);

            if ([] === $errors && null !== $deliveryAddress) {
                $cardDigits = preg_replace('/\D/', '', $request->getPayload()->getString('card_number'));

                $purchase = new Purchase();
                $purchase->setCustomer($user)
                    ->setArticle($article)
                    ->setArticleTitle($article->getTitle())
                    ->setPrice($article->getPrice())
                    ->setPaymentNote(sprintf('Paiement de démonstration — carte •••• %s', '' !== $cardDigits ? substr($cardDigits, -4) : '0000'))
                    ->setDeliveryAddress(sprintf(
                        '%s, %s %s — %s',
                        $deliveryAddress->getStreet(),
                        $deliveryAddress->getPostalCode(),
                        $deliveryAddress->getCity(),
                        $deliveryAddress->getCountry(),
                    ));

                $article->decrementQuantity();

                $em->persist($purchase);
                $em->flush();

                $mailer->send(
                    (new TemplatedEmail())
                        ->from(new Address('no-reply@la-chiennete.onion', 'LA_CHIENNETÉ'))
                        ->to(new Address($user->getEmail(), $user->getFirstname().' '.$user->getLastname()))
                        ->subject(sprintf('Commande #%05d confirmée — %s', $purchase->getId(), $purchase->getArticleTitle()))
                        ->htmlTemplate('emails/purchase_confirmation.html.twig')
                        ->context(['purchase' => $purchase])
                );

                $this->addFlash('success', 'Commande confirmée. Aucun paiement réel n\'a été effectué, personne n\'a été débité.');

                return $this->redirectToRoute('app_purchase_confirmation', ['id' => $purchase->getId()]);
            }
        }

        return $this->render('purchase/checkout.html.twig', [
            'article' => $article,
            'shippingAddresses' => $shippingAddresses,
            'errors' => $errors,
            'previous' => $request->isMethod('POST') ? $request->getPayload()->all() : [],
        ]);
    }

    #[Route('/purchases/{id}/confirmation', name: 'app_purchase_confirmation', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function confirmation(Purchase $purchase): Response
    {
        if ($purchase->getCustomer() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('purchase/confirmation.html.twig', [
            'purchase' => $purchase,
        ]);
    }

    #[Route('/purchases', name: 'app_purchases', methods: ['GET'])]
    public function index(PurchaseRepository $purchaseRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('purchase/index.html.twig', [
            'purchases' => $purchaseRepository->findByCustomer($user),
        ]);
    }

    private function guardPurchasable(Article $article, User $user): ?Response
    {
        $redirect = $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);

        if ($article->isSold()) {
            $this->addFlash('error', 'Trop tard : cet article est en rupture de stock.');

            return $redirect;
        }

        if (null === $article->getPrice()) {
            $this->addFlash('error', 'Cet article est sur devis : contactez le vendeur pour négocier.');

            return $redirect;
        }

        if ($article->getSeller() === $user) {
            $this->addFlash('error', 'Vous ne pouvez pas acheter votre propre annonce.');

            return $redirect;
        }

        return null;
    }

    /**
     * @param ShippingAddress[] $shippingAddresses
     * @param string[]          $errors
     */
    private function resolveDeliveryAddress(
        Request $request,
        User $user,
        array $shippingAddresses,
        ValidatorInterface $validator,
        array &$errors,
    ): ?ShippingAddress {
        $payload = $request->getPayload();
        $addressId = $payload->getString('address_id');

        if ('new' !== $addressId && '' !== $addressId) {
            foreach ($shippingAddresses as $address) {
                if ($address->getId() === (int) $addressId) {
                    return $address;
                }
            }

            $errors[] = 'Adresse de livraison invalide.';

            return null;
        }

        $address = new ShippingAddress();
        $address->setLabel(trim($payload->getString('new_label')) ?: 'Livraison')
            ->setStreet(trim($payload->getString('new_street')))
            ->setPostalCode(trim($payload->getString('new_postal_code')))
            ->setCity(trim($payload->getString('new_city')))
            ->setCountry(trim($payload->getString('new_country')) ?: 'France');

        $violations = $validator->validate($address);

        if (\count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            return null;
        }

        // L'adresse rejoint le carnet du client (persistée en cascade depuis User).
        $user->addAddress($address);

        return $address;
    }
}
