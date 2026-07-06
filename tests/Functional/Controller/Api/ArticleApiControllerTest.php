<?php

namespace App\Tests\Functional\Controller\Api;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArticleApiControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->entityManager->getConnection()->executeStatement(
            'TRUNCATE app_user, category RESTART IDENTITY CASCADE'
        );
    }

    public function testArticlesEndpointReturnsArticlesAsJson(): void
    {
        $article = $this->createArticle('Table basse en chêne', '120.00');

        $this->client->request('GET', '/api/v1/articles');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertSame($article->getId(), $data[0]['id']);
        $this->assertSame('Table basse en chêne', $data[0]['title']);
        $this->assertSame('120.00', $data[0]['price']);
        $this->assertSame('Meubles', $data[0]['category']['name']);
        $this->assertSame('Jean', $data[0]['seller']['firstname']);
        $this->assertArrayNotHasKey('password', $data[0]['seller'], 'Le vendeur ne doit pas exposer son mot de passe.');
    }

    public function testArticlesEndpointReturnsEmptyListWhenNoArticles(): void
    {
        $this->client->request('GET', '/api/v1/articles');

        $this->assertResponseIsSuccessful();
        $this->assertSame('[]', $this->client->getResponse()->getContent());
    }

    private function createArticle(string $title, string $price): Article
    {
        $seller = new User();
        $seller->setEmail('vendeur@example.com');
        $seller->setPassword('hashed-password');
        $seller->setFirstname('Jean');
        $seller->setLastname('Dupont');

        $category = new Category();
        $category->setName('Meubles');
        $category->setSlug('meubles');

        $article = new Article();
        $article->setSeller($seller);
        $article->setCategory($category);
        $article->setTitle($title);
        $article->setDescription('Une belle table basse en chêne massif.');
        $article->setPrice($price);

        $this->entityManager->persist($seller);
        $this->entityManager->persist($category);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $article;
    }
}
