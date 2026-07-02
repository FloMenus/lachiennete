<?php

namespace App\DataFixtures;

use App\Entity\BillingAddress;
use App\Entity\ShippingAddress;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AddressFixtures extends Fixture implements DependentFixtureInterface
{
    private const ADDRESSES = [
        UserFixtures::ADMIN => ['12 rue de la République', '69002', 'Lyon', null, null, null],
        UserFixtures::CLIENT => ['8 avenue Jean Jaurès', '75019', 'Paris', 'Interphone 4821, 3e étage droite', null, null],
        'user-client-2' => ['27 rue Nationale', '59000', 'Lille', null, null, null],
        'user-client-3' => ['5 rue des Teinturiers', '84000', 'Avignon', 'Portail vert, sonner deux fois', null, null],
        'user-client-4' => ['18 quai des Chartrons', '33000', 'Bordeaux', null, null, null],
        'user-client-5' => ['42 rue de Siam', '29200', 'Brest', 'Laisser le colis chez la gardienne en cas d\'absence', null, null],
        'user-client-6' => ['9 rue d\'Austerlitz', '67000', 'Strasbourg', null, null, null],
        'user-client-7' => ['31 cours Julien', '13006', 'Marseille', null, null, null],
        'user-client-8' => ['6 rue de la Verrerie', '21000', 'Dijon', 'Bâtiment B, boîte aux lettres 12', null, null],
        'user-client-9' => ['15 rue Saint-Melaine', '35000', 'Rennes', null, null, null],
        UserFixtures::SELLER_1 => ['25 rue des Carmes', '44000', 'Nantes', null, 'Lefèvre Négoce SARL', 'FR32123456789'],
        UserFixtures::SELLER_2 => ['14 boulevard Gambetta', '34000', 'Montpellier', null, 'Roussel Services', 'FR47987654321'],
        UserFixtures::SELLER_3 => ['3 impasse des Lilas', '31400', 'Toulouse', 'Accès par la cour intérieure', 'Garnier Distribution', 'FR18456789123'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ADDRESSES as $userRef => [$street, $postalCode, $city, $instructions, $company, $vatNumber]) {
            $user = $this->getReference($userRef, User::class);

            $shipping = new ShippingAddress();
            $shipping->setLabel('Domicile')
                ->setStreet($street)
                ->setPostalCode($postalCode)
                ->setCity($city)
                ->setCountry('France')
                ->setIsDefault(true);
            $shipping->setRecipientName($user->getFirstname().' '.$user->getLastname())
                ->setDeliveryInstructions($instructions);
            $user->addAddress($shipping);
            $manager->persist($shipping);

            $billing = new BillingAddress();
            $billing->setLabel('Facturation')
                ->setStreet($street)
                ->setPostalCode($postalCode)
                ->setCity($city)
                ->setCountry('France')
                ->setIsDefault(true);
            $billing->setCompanyName($company)
                ->setVatNumber($vatNumber);
            $user->addAddress($billing);
            $manager->persist($billing);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
