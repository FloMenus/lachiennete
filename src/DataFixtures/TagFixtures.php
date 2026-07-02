<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture
{
    public const TAGS = [
        'tag-animaux' => 'Animaux',
        'tag-livraison-rapide' => 'Livraison rapide',
        'tag-garantie' => 'Garantie',
        'tag-exotique' => 'Exotique',
        'tag-express' => 'Express',
        'tag-certifie' => 'Certifié',
        'tag-discret' => 'Discret',
        'tag-occulte' => 'Occulte',
        'tag-vip' => 'VIP',
        'tag-politique' => 'Politique',
        'tag-luxe' => 'Luxe',
        'tag-bien-etre' => 'Bien-être',
        'tag-sur-mesure' => 'Sur mesure',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::TAGS as $reference => $name) {
            $tag = new Tag();
            $tag->setName($name);

            $manager->persist($tag);
            $this->addReference($reference, $tag);
        }

        $manager->flush();
    }
}
