<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const ANIMAUX = 'category-animaux';
    public const SERVICES = 'category-services';
    public const DOCUMENTS = 'category-documents';
    public const CARRIERES = 'category-carrieres';
    public const OCCULTE = 'category-occulte';
    public const TRANSPORT = 'category-transport';

    public function load(ObjectManager $manager): void
    {
        $categories = [
            self::ANIMAUX => [
                'Animaux exotiques',
                'animaux-exotiques',
                'Compagnons à poils, à écailles ou à poche ventrale, livrés à domicile.',
            ],
            self::SERVICES => [
                'Services premium',
                'services-premium',
                'Des prestations sur mesure, rapides, discrètes et légalement ambiguës.',
            ],
            self::DOCUMENTS => [
                'Documents & certificats',
                'documents-certificats',
                'Diplômes, permis et certificats imprimés sur du vrai papier avec de vraies lettres.',
            ],
            self::CARRIERES => [
                'Carrières & pouvoir',
                'carrieres-pouvoir',
                'Accédez au sommet sans effort : sport, politique, prestige.',
            ],
            self::OCCULTE => [
                'Occulte & surnaturel',
                'occulte-surnaturel',
                'Artefacts et créatures venus d\'ailleurs. Âme non requise à l\'achat.',
            ],
            self::TRANSPORT => [
                'Transport & voyages',
                'transport-voyages',
                'Des moyens de transport que personne d\'autre ne peut vous proposer.',
            ],
        ];

        foreach ($categories as $reference => [$name, $slug, $description]) {
            $category = new Category();
            $category->setName($name)
                ->setSlug($slug)
                ->setDescription($description);

            $manager->persist($category);
            $this->addReference($reference, $category);
        }

        $manager->flush();
    }
}
