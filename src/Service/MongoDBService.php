<?php

namespace App\Service;

use MongoDB\Client;
use MongoDB\Collection;

class MongoDBService
{
    private Client $client;
    private string $database;

    public function __construct()
    {
        // Connexion sera établie dans Docker
        $this->client = new Client($_ENV['MONGODB_URL'] ?? 'mongodb://localhost:27017');
        $this->database = $_ENV['MONGODB_DB'] ?? 'restaurant_mongo';
    }

    public function getCollection(string $collectionName): Collection
    {
        return $this->client->selectCollection($this->database, $collectionName);
    }

    // Exemples pour votre projet restaurant
    public function getMenusCollection(): Collection
    {
        return $this->getCollection('menus');
    }

    public function getReservationsCollection(): Collection
    {
        return $this->getCollection('reservations');
    }

    public function getReviewsCollection(): Collection
    {
        return $this->getCollection('reviews');
    }
}
