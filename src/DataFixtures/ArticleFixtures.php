<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    private const ARTICLES = [
        [
            'Pack Macaques Premium',
            'Besoin d\'une armée de macaques entraînés pour perturber une réunion ? Ce pack inclut 3 macaques motivés, discrets et livrés en 48h. Satisfaction garantie ou on les reprend.',
            '149.99',
            'Macaques.jpeg',
            CategoryFixtures::ANIMAUX,
            ['tag-animaux', 'tag-livraison-rapide', 'tag-garantie'],
            UserFixtures::SELLER_1,
            null,
        ],
        [
            'Alligator de Compagnie',
            'Tu veux impressionner tes voisins ? Adopte Gator, notre alligator semi-domestiqué de 2 mètres. Parfait pour surveiller ta piscine ou faire fuir les indésirables.',
            '899.00',
            'alligator.jpeg',
            CategoryFixtures::ANIMAUX,
            ['tag-animaux', 'tag-exotique'],
            UserFixtures::SELLER_1,
            null,
        ],
        [
            'Avocat Express',
            'Un avocat disponible 24h/24, pas de rendez-vous, pas de facture surprise. Il plaide, tu gagnes. Spécialité : faire acquitter n\'importe quoi.',
            '299.50',
            'avocat.jpeg',
            CategoryFixtures::SERVICES,
            ['tag-express', 'tag-certifie'],
            UserFixtures::SELLER_2,
            null,
        ],
        [
            'Billet de 50 (x10)',
            'Lot de 10 billets de 50€ d\'une qualité tellement parfaite que même ta grand-mère ne verra pas la différence. Usage strictement décoratif, bien sûr.',
            '75.00',
            'billet-50.jpeg',
            CategoryFixtures::DOCUMENTS,
            ['tag-discret'],
            UserFixtures::SELLER_2,
            null,
        ],
        [
            'Démon à Acheter',
            'Tu as toujours rêvé de posséder ton propre démon ? C\'est le moment. Livré en cage renforcée, il obéit à son nouveau maître après 3 jours d\'apprivoisement. Âme non requise à l\'achat.',
            '666.66',
            'demon.jpeg',
            CategoryFixtures::OCCULTE,
            ['tag-occulte', 'tag-garantie'],
            UserFixtures::SELLER_3,
            'Paiement en âme accepté',
        ],
        [
            'Diplômes Tous Niveaux',
            'Bachelier, Licence, Master ou Doctorat — choisis ton niveau ! Nos diplômes sont imprimés sur du vrai papier avec de vraies lettres. Encadrement livré offert.',
            '199.00',
            'diplomes.jpeg',
            CategoryFixtures::DOCUMENTS,
            ['tag-certifie', 'tag-express'],
            UserFixtures::SELLER_2,
            null,
        ],
        [
            'Faux Vaccin Certifié',
            'Tu veux le carnet sans l\'aiguille ? Notre faux vaccin est livré avec un certificat d\'immunité pour une liste de 47 maladies dont certaines n\'existent pas encore.',
            '49.99',
            'faux-vaccin.jpeg',
            CategoryFixtures::DOCUMENTS,
            ['tag-certifie', 'tag-discret'],
            UserFixtures::SELLER_2,
            null,
        ],
        [
            'Fruit du Démon',
            'Le légendaire fruit du démon. Mange-le et tu gagneras un pouvoir surnaturel (résultats non garantis, peut causer des illusions de grandeur et une addiction aux snaps).',
            '399.00',
            'fruit-du-demon.jpeg',
            CategoryFixtures::OCCULTE,
            ['tag-occulte', 'tag-exotique'],
            UserFixtures::SELLER_3,
            null,
        ],
        [
            'Devenir Joueur au Barça',
            'Intègre l\'effectif du FC Barcelona dès la semaine prochaine. Contrat 3 ans, vestiaire partagé avec les stars, numéro de maillot au choix. Aucun niveau requis, on s\'occupe du reste.',
            '1200.00',
            'joueur-barca.jpeg',
            CategoryFixtures::CARRIERES,
            ['tag-vip', 'tag-garantie'],
            UserFixtures::SELLER_3,
            null,
        ],
        [
            'Kangourou Coursier',
            'Marre de DHL ? Notre kangourou livre tes colis en sautant par-dessus les embouteillages. Capacité de la poche : jusqu\'à 5kg. Délai : fonction de la météo.',
            '89.00',
            'kagourou.jpeg',
            CategoryFixtures::ANIMAUX,
            ['tag-animaux', 'tag-livraison-rapide'],
            UserFixtures::SELLER_1,
            null,
        ],
        [
            'Lama Thérapeutique',
            'Un lama certifié pour tes séances de développement personnel. Il t\'écoute, il crache si tu mens, et il est moins cher qu\'un psy. 1h de session incluse.',
            '120.00',
            'lama.jpeg',
            CategoryFixtures::ANIMAUX,
            ['tag-animaux', 'tag-bien-etre'],
            UserFixtures::SELLER_1,
            null,
        ],
        [
            'Devenir Ministre du Mali',
            'Accède directement à un portefeuille ministériel au Mali. Ministère au choix selon disponibilité, bureau officiel, voiture avec chauffeur et accès aux réunions gouvernementales. Prise de poste sous 72h.',
            '750.00',
            'ministre-mali.jpeg',
            CategoryFixtures::CARRIERES,
            ['tag-politique', 'tag-vip', 'tag-express'],
            UserFixtures::SELLER_3,
            null,
        ],
        [
            'Permis Avion Toutes Catégories',
            'Pilote de ligne, hélico, jet privé ou planeur — obtiens ton permis en 3 jours sans formation. Valable dans 12 pays (liste disponible sur demande).',
            '350.00',
            'permis-avion.jpeg',
            CategoryFixtures::DOCUMENTS,
            ['tag-certifie', 'tag-express'],
            UserFixtures::SELLER_2,
            null,
        ],
        [
            'Devenir Président de République',
            'Accède au pouvoir suprême sans passer par les élections. Palais présidentiel, garde rapprochée et accès aux codes nucléaires inclus. Mandat garanti 5 ans, renouvelable selon humeur.',
            '2500.00',
            'president.jpeg',
            CategoryFixtures::CARRIERES,
            ['tag-politique', 'tag-vip', 'tag-luxe'],
            UserFixtures::SELLER_3,
            null,
        ],
        [
            'Train Privé Intercités',
            'Réserve un train entier pour toi et tes amis. Pas de contrôleur, pas de retard annoncé, pas de voisin qui mange des chips. Trajet à définir selon disponibilité.',
            '3999.00',
            'train.jpeg',
            CategoryFixtures::TRANSPORT,
            ['tag-luxe', 'tag-vip'],
            UserFixtures::SELLER_1,
            null,
        ],
        [
            'Service Bagarre Uber',
            'Tu as besoin qu\'une altercation se passe à un endroit précis à une heure précise ? Notre service de bagarre Uber livre deux combattants professionnels en moins de 10 minutes.',
            '199.00',
            'uber-bagarre.jpeg',
            CategoryFixtures::SERVICES,
            ['tag-express', 'tag-discret'],
            UserFixtures::SELLER_2,
            null,
        ],
        [
            'Pack Vengeance Complète',
            'Ex qui t\'a trahi ? Associé qui t\'a volé ? Notre pack vengeance clé en main inclut 5 plans coordonnés, discrets et légalement ambigus. Devis sur mesure disponible.',
            null,
            'vengeance.jpeg',
            CategoryFixtures::SERVICES,
            ['tag-discret', 'tag-sur-mesure'],
            UserFixtures::SELLER_2,
            'Devis sur mesure',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ARTICLES as $index => [$title, $description, $price, $filename, $categoryRef, $tagRefs, $sellerRef, $alternativePayment]) {
            $article = new Article();
            $article->setTitle($title)
                ->setDescription($description)
                ->setPrice($price)
                ->setAlternativePayment($alternativePayment)
                ->setSeller($this->getReference($sellerRef, User::class))
                ->setCategory($this->getReference($categoryRef, Category::class));

            foreach ($tagRefs as $tagRef) {
                $article->addTag($this->getReference($tagRef, Tag::class));
            }

            $image = new Image();
            $image->setFilename($filename)
                ->setAlt($title)
                ->setPosition(0);
            $article->addImage($image);

            $manager->persist($article);
            $this->addReference('article-'.$index, $article);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
            TagFixtures::class,
        ];
    }
}
