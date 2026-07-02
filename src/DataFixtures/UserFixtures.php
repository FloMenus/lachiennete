<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN = 'user-admin';
    public const CLIENT = 'user-client-1';
    public const SELLER_1 = 'user-seller-1';
    public const SELLER_2 = 'user-seller-2';
    public const SELLER_3 = 'user-seller-3';

    public const CLIENTS = [
        self::CLIENT,
        'user-client-2',
        'user-client-3',
        'user-client-4',
        'user-client-5',
        'user-client-6',
        'user-client-7',
        'user-client-8',
        'user-client-9',
    ];

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            self::ADMIN => ['admin@snapdeals.fr', 'Sophie', 'Lambert', ['ROLE_ADMIN']],
            self::CLIENT => ['julien.moreau@gmail.com', 'Julien', 'Moreau', ['ROLE_CLIENT']],
            'user-client-2' => ['emma.petit@gmail.com', 'Emma', 'Petit', ['ROLE_CLIENT']],
            'user-client-3' => ['lucas.bernard@outlook.fr', 'Lucas', 'Bernard', ['ROLE_CLIENT']],
            'user-client-4' => ['chloe.durand@gmail.com', 'Chloé', 'Durand', ['ROLE_CLIENT']],
            'user-client-5' => ['hugo.fontaine@yahoo.fr', 'Hugo', 'Fontaine', ['ROLE_CLIENT']],
            'user-client-6' => ['lea.marchand@gmail.com', 'Léa', 'Marchand', ['ROLE_CLIENT']],
            'user-client-7' => ['antoine.girard@hotmail.fr', 'Antoine', 'Girard', ['ROLE_CLIENT']],
            'user-client-8' => ['manon.chevalier@gmail.com', 'Manon', 'Chevalier', ['ROLE_CLIENT']],
            'user-client-9' => ['maxime.dupont@orange.fr', 'Maxime', 'Dupont', ['ROLE_CLIENT']],
            self::SELLER_1 => ['thomas.lefevre@gmail.com', 'Thomas', 'Lefèvre', ['ROLE_PRESTATAIRE']],
            self::SELLER_2 => ['camille.roussel@outlook.fr', 'Camille', 'Roussel', ['ROLE_PRESTATAIRE']],
            self::SELLER_3 => ['nicolas.garnier@hotmail.fr', 'Nicolas', 'Garnier', ['ROLE_PRESTATAIRE']],
        ];

        foreach ($users as $reference => [$email, $firstname, $lastname, $roles]) {
            $user = new User();
            $user->setEmail($email)
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setRoles($roles)
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'));

            $manager->persist($user);
            $this->addReference($reference, $user);
        }

        $manager->flush();
    }
}
