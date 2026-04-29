<?php

namespace App\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccessControlTest extends WebTestCase
{
    public function testDashboardPatientNecessiteAuthentification(): void
    {
        $client = static::createClient();
        
        // Tente d'accéder au dashboard patient sans être connecté
        $client->request('GET', '/patient/dashboard');
        
        // Doit rediriger vers la page de connexion
        $this->assertResponseRedirects('/login');
    }
    
    public function testDashboardMedecinNecessiteAuthentification(): void
    {
        $client = static::createClient();
        
        // Tente d'accéder au dashboard médecin sans être connecté
        $client->request('GET', '/doctor/dashboard');
        
        // Doit rediriger vers la page de connexion
        $this->assertResponseRedirects('/login');
    }
    
    public function testDashboardAdminNecessiteAuthentification(): void
    {
        $client = static::createClient();
        
        // Tente d'accéder au dashboard admin sans être connecté
        $client->request('GET', '/admin/dashboard');
        
        // Doit rediriger vers la page de connexion
        $this->assertResponseRedirects('/login');
    }
}