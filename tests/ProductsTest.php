<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private const API_TOKEN = "00ec07a73df0ee5178f7f8c003c70a6fc1a8b27e72031b3c3986271a4c9967dc601fdda8ca67e5b07cc92c77f63c4655dec3df47780f05dbb542c159";

    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail('info@test.com');
        $user->setPassword('some_password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $apiToken = new ApiToken();
        $apiToken->setToken(self::API_TOKEN);
        $apiToken->setUser($user);
        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();
    }

    public function testGetCollection(): void
    {
        $response = $this->client->request('GET', '/api/products', [
            'headers' => ['x-api-token' => self::API_TOKEN]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $this->assertJsonContains([
            "@context" => "/api/contexts/Product",
            "@id" => "/api/products",
            "@type" => "hydra:Collection",
            "hydra:totalItems" => 100,
            "hydra:view" => [
                "@id" => "/api/products?page=1",
                "@type" => "hydra:PartialCollectionView",
                "hydra:first" => "/api/products?page=1",
                "hydra:last" => "/api/products?page=10",
                "hydra:next" => "/api/products?page=2"
            ]
        ]);

        $this->assertCount(10, $response->toArray()['hydra:member']);
    }

    public function testPagination(): void
    {
        $response = $this->client->request('GET', '/api/products?page=2', [
            'headers' => ['x-api-token' => self::API_TOKEN]
        ]);

        $this->assertJsonContains([
            "hydra:view" => [
                "@id" => "/api/products?page=2",
                "@type" => "hydra:PartialCollectionView",
                "hydra:first" => "/api/products?page=1",
                "hydra:last" => "/api/products?page=10",
                "hydra:previous" => "/api/products?page=1",
                "hydra:next" => "/api/products?page=3"
            ]
        ]);

        $this->assertCount(10, $response->toArray()['hydra:member']);
    }

    public function testCreateProduct(): void
    {
        $this->client->request('POST', '/api/products', [
            'headers' => [
                'x-api-token' => self::API_TOKEN,
                'Content-Type' => 'application/ld+json'
            ],
            'json' => [
                'mpn' => '1234',
                'name' => 'A Test Product',
                'description' => 'A Test Description',
                'listedDate' => '1994-06-10T00:00:00+00:00',
                'manufacturer' => '/api/manufacturers/1' // It's better to retrieve manufacturer
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);

        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $this->assertJsonContains([
            'mpn' => '1234',
            'name' => 'A Test Product',
            'description' => 'A Test Description',
            'listedDate' => '1994-06-10T00:00:00+00:00',
            'manufacturer' => [
                '@id' => '/api/manufacturers/1',
                '@type' => 'Manufacturer',
                'name' => 'Luettgen LLC',
            ]
        ]);
    }

    public function testUpdateProduct(): void
    {
        $this->client->request('PATCH', 'api/products/1', [
            'headers' => [
                'x-api-token' => self::API_TOKEN,
                'Content-Type' => 'application/merge-patch+json'
            ],
            'json' => [
                'description' => 'An updated description'
            ]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertJsonContains([
            '@id' => '/api/products/1',
            'description' => 'An updated description'
        ]);
    }

    public function testInvalidToken(): void
    {
        $this->client->request('GET', '/api/products', [
            'headers' => ['x-api-token' => 'fake-token']
        ]);

        $this->assertResponseStatusCodeSame(401);

        $this->assertJsonContains([
            'message' => 'Invalid credentials.',
        ]);
    }
}
