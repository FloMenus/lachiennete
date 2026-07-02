<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    private const REVIEWS = [
        [
            [5, 'Les trois macaques sont arrivés en 47h, très professionnels. La réunion budget de lundi a été annulée en 12 minutes. Je recommande.'],
            [4, 'Efficaces mais bruyants. L\'un d\'eux a volé mon téléphone : il me manque une étoile et un téléphone.'],
            [5, 'La satisfaction est vraiment garantie : le SAV est venu récupérer le macaque qui refusait de travailler.'],
        ],
        [
            [5, 'Gator surveille la piscine depuis un mois, plus aucun voisin ne vient sans invitation. Très dissuasif.'],
            [2, '« Semi-domestiqué » est un terme généreux. Il a mangé le salon de jardin. Déçu.'],
            [4, 'Livraison impeccable en camion adapté, il répond déjà à son nom. Prévoyez beaucoup de poulet.'],
        ],
        [
            [5, 'Appelé à 3h du matin, il plaidait à 9h. Relaxe totale. Incroyable.'],
            [4, 'Très réactif. Je crois qu\'il dort dans son costume, mais ça fait partie du charme.'],
            [5, 'Il a fait acquitter mon lapin pour la pelouse du voisin. Rien à redire.'],
            [3, 'Compétent, mais il plaide aussi pendant les repas de famille. C\'est épuisant.'],
        ],
        [
            [5, 'Qualité décorative exceptionnelle, même ma grand-mère n\'a rien remarqué. Le lot rend très bien encadré au-dessus de la cheminée.'],
            [1, 'Le distributeur de la gare n\'a pas apprécié la décoration. Service client injoignable depuis.'],
        ],
        [
            [5, 'Apprivoisé en 3 jours comme promis. Il fait la vaisselle et terrorise le chat. Très satisfait.'],
            [4, 'Obéissant, mais il chauffe beaucoup la chambre d\'amis. Prévoir une bonne ventilation.'],
            [5, 'Le paiement en âme est très pratique quand on est à découvert. Transaction fluide.'],
            [2, 'La cage renforcée était fêlée à la livraison. Le démon est adorable, mais le carrelage a fondu.'],
            [5, 'Il murmure des vérités anciennes à 4h du matin, mais sinon c\'est un excellent compagnon.'],
        ],
        [
            [5, 'Master en astrophysique reçu en 5 jours, l\'encadrement offert est en très beau bois. Embauché le mois suivant.'],
            [4, 'Le doctorat fait très vrai, il y a même une faute d\'orthographe, comme sur les vrais.'],
            [3, 'Bien, mais mon nom est écrit avec deux R. Le SAV propose un deuxième doctorat en dédommagement.'],
        ],
        [
            [4, 'Le certificat couvre 47 maladies dont la lycanthropie. On n\'est jamais trop prudent.'],
            [5, 'Carnet impeccable, tampon très officiel. Aucune aiguille, comme promis.'],
        ],
        [
            [3, 'Mangé il y a deux semaines. Toujours aucun pouvoir, mais je ne sais plus nager. C\'est louche.'],
            [5, 'Goût atroce, exactement comme dans le manga. Je sens un pouvoir monter. Ou une indigestion.'],
            [4, 'Emballage soigné, fruit spiralé magnifique. Résultats surnaturels en attente, mais je reste confiant.'],
            [1, 'Addiction aux snaps confirmée. J\'en suis à mon troisième fruit. Fuyez.'],
        ],
        [
            [5, 'Présenté au vestiaire mardi, titulaire samedi. Personne n\'a posé de question. Le maillot flotte un peu.'],
            [4, 'Contrat signé rapidement. Petit bémol : on m\'a mis gardien alors que j\'avais demandé le numéro 10.'],
            [2, 'L\'intégration est réelle, mais « aucun niveau requis » veut vraiment dire aucun niveau : on a perdu 8-0 et c\'est ma faute.'],
        ],
        [
            [5, 'Mon colis est arrivé avant la notification d\'expédition. Il a sauté par-dessus le périph, je l\'ai vu de mes yeux.'],
            [4, 'Rapide et écologique. Le colis sent un peu la poche ventrale, mais rien de grave.'],
            [3, 'Délai fonction de la météo, c\'est vrai : il pleuvait, il a boudé sous un abribus pendant deux heures.'],
            [5, 'Livraison en 22 minutes, il a même sonné avec sa queue. DHL peut trembler.'],
        ],
        [
            [5, 'Il a craché sur mon ex qui passait par là. Meilleure séance de thérapie de ma vie.'],
            [5, 'Il écoute sans juger, contrairement à mon ancien psy. Et il sent bon le foin.'],
            [4, 'Séance efficace, mais il a craché quand j\'ai dit que j\'allais bien. Il avait raison.'],
            [3, 'Bon thérapeute, mais très strict sur l\'horaire : il part exactement à la 60e minute, même en pleine phrase.'],
            [5, 'Moins cher qu\'un psy et il tond la pelouse pendant les silences. Parfait.'],
        ],
        [
            [4, 'Prise de poste en 71h, bureau climatisé, chauffeur ponctuel. Seul le portefeuille de l\'Artisanat était disponible, mais je m\'y fais.'],
            [5, 'Les réunions gouvernementales sont très instructives. Personne n\'a vérifié mon CV.'],
            [2, 'Remanié au bout de deux semaines, aucun remboursement prévu au contrat. Lisez les petites lignes.'],
        ],
        [
            [5, 'Permis reçu en 3 jours. Le planeur, c\'est facile : il n\'y a même pas de moteur.'],
            [3, 'Valable dans 12 pays, mais apparemment pas celui où j\'habite. Vérifiez la liste avant.'],
        ],
        [
            [5, 'Le palais est spacieux, la garde rapprochée très polie. Les codes nucléaires étaient dans une enveloppe kraft, sympa.'],
            [4, 'Mandat conforme, mais « renouvelable selon humeur » dépend de l\'humeur du vendeur, pas de la vôtre. Attention.'],
            [5, 'Accès au pouvoir suprême en 10 jours ouvrés. Ma première allocution télévisée s\'est très bien passée.'],
            [1, 'Coup d\'État au bout d\'un mois. Le SAV m\'a proposé un bon d\'achat sur le pack Ministre. Moyen.'],
        ],
        [
            [5, 'Train entier réservé pour mon anniversaire, zéro voisin qui mange des chips. Le rêve.'],
            [4, '« Pas de retard annoncé », en effet : le train est parti avec 3h de retard, sans aucune annonce.'],
            [5, 'Pas de contrôleur, wagon-bar à volonté. Trajet Paris-Marseille inoubliable.'],
        ],
        [
            [5, 'Altercation livrée en 8 minutes devant la boulangerie, comme demandé. Chorégraphie très crédible.'],
            [4, 'Professionnels et ponctuels. Ils ont même ramassé les chaises après.'],
            [3, 'Bonne bagarre, mais un des combattants a été livré chez le voisin. Logistique à revoir.'],
        ],
        [
            [5, 'Devis rapide, 5 plans coordonnés exécutés au millimètre. Mon ex-associé a déménagé de région. Très pro.'],
            [4, 'Légalement ambigu mais moralement satisfaisant. Un seul plan sur les cinq a échoué (le pigeon voyageur).'],
            [5, 'Discrétion totale : je n\'ai moi-même pas compris ce qui s\'était passé.'],
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        $clientCount = count(UserFixtures::CLIENTS);

        foreach (self::REVIEWS as $articleIndex => $reviews) {
            $article = $this->getReference('article-'.$articleIndex, Article::class);

            foreach ($reviews as $reviewIndex => [$rating, $comment]) {
                $authorRef = UserFixtures::CLIENTS[($articleIndex * 3 + $reviewIndex) % $clientCount];

                $review = new Review();
                $review->setArticle($article)
                    ->setAuthor($this->getReference($authorRef, User::class))
                    ->setRating($rating)
                    ->setComment($comment);

                $manager->persist($review);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ArticleFixtures::class,
        ];
    }
}
