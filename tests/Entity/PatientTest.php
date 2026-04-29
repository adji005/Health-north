<?php

namespace App\Tests\Entity;

use App\Entity\Patient;
use PHPUnit\Framework\TestCase;

class PatientTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $patient = new Patient();
        
        $patient->setNom('Dupont');
        $patient->setPrenom('Jean');
        $patient->setTelephone('0612345678');
        $patient->setAdresse('10 rue de la Paix, Paris');
        
        $this->assertEquals('Dupont', $patient->getNom());
        $this->assertEquals('Jean', $patient->getPrenom());
        $this->assertEquals('0612345678', $patient->getTelephone());
        $this->assertEquals('10 rue de la Paix, Paris', $patient->getAdresse());
    }
    
    public function testDateDeNaissance(): void
    {
        $patient = new Patient();
        $date = new \DateTime('1990-05-15');
        
        $patient->setDateDeNaissance($date);
        
        $this->assertEquals($date, $patient->getDateDeNaissance());
        $this->assertEquals('1990-05-15', $patient->getDateDeNaissance()->format('Y-m-d'));
    }
}