<?php

namespace App\Twig;

use App\Entity\User;
use App\Service\CurrencyConverter;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Filtre `price` : formate un prix stocké en EUR dans la devise de l'utilisateur connecté
 * (ou dans la devise passée en argument), converti via CurrencyConverter.
 *
 * Usage : {{ article.price|price }} ou {{ purchase.price|price(purchase.customer.currency) }}
 */
class PriceExtension extends AbstractExtension
{
    public function __construct(
        private readonly CurrencyConverter $converter,
        private readonly Security $security,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('price', $this->formatPrice(...)),
        ];
    }

    public function formatPrice(string|float|null $price, ?string $currency = null): string
    {
        if (null === $price || '' === $price) {
            return '—';
        }

        if (null === $currency) {
            $user = $this->security->getUser();
            $currency = $user instanceof User ? $user->getCurrency() : 'EUR';
        }

        ['amount' => $amount, 'currency' => $resolved] = $this->converter->convert((float) $price, $currency);
        $config = CurrencyConverter::CURRENCIES[$resolved];

        return number_format($amount, $config['decimals'], ',', ' ').' '.$config['symbol'];
    }
}
