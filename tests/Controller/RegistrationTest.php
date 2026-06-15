<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationTest extends WebTestCase
{
    public function testInscriptionPatientReussie(): void
    {
        $client = static::createClient();
        
        // Données d'inscription
        $donnees = [
            'nom' => 'Testeur',
            'prenom' => 'Jean',
            'email' => 'test' . time() . '@example.com',
            'password' => 'Motdepasse123',
            'dateDeNaissance' => '1990-01-01',
            'telephone' => '0612345678',
            'adresse' => '10 rue Test, Paris'
        ];
        
        // Envoie la requête d'inscription à l'API
        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($donnees)
        );
        
        // Vérifie que la réponse est un succès (200 ou 201)
        $this->assertResponseIsSuccessful();
        
        // Vérifie que la réponse est en JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }
    
    public function testInscriptionAvecMotDePasseFaible(): void
    {
        $client = static::createClient();
        
        // Mot de passe faible(pas de majuscule)
        $donnees = [
            'nom' => 'Testeur',
            'prenom' => 'Marie',
            'email' => 'test' . time() . '@example.com',
            'password' => 'faible123',
            'dateDeNaissance' => '1995-05-15',
            'telephone' => '0687654321',
            'adresse' => '20 avenue Test, Lyon'
        ];
        
        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($donnees)
        );
        
        // Doit retourner une erreur (400)
        $this->assertResponseStatusCodeSame(400);
    }
}
