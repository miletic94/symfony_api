<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class ProductsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/api/products');

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
        $response = static::createClient()->request('GET', '/api/products?page=2');

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
        static::createClient()->request('POST', '/api/products', [
            'headers' => ['Content-Type' => 'application/ld+json'],
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
        $client = static::createClient();

        $client->request('PATCH', 'api/products/1', [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
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
}
