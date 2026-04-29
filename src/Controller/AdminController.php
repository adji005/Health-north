<?php

namespace App\Controller;

use App\Entity\User;  // Entité User pour gérer les comptes utilisateurs
use App\Repository\ProfessionnelSanteRepository; //recup les medecin en bdd
use App\Repository\UserRepository; // Repository pour récupérer les utilisateurs en BDD
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**Contrôleur de l'espace administrateur gere la validtion des médecin et les accès a lapp*/
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        UserRepository $userRepo,
        ProfessionnelSanteRepository $medecinRepo
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'totalPatients' => count($userRepo->findAll()) - count($medecinRepo->findAll()) - 1, //  compte le nbr de membres -1 pour l'admin
            'medecinEnAttente' => $medecinRepo->findBy(['statut' => 'en_attente']),
            'totalMedecins' => count($medecinRepo->findBy(['statut' => 'valide'])),
        ]);
    }

    /**Liste des médecins en attente de validation*/
    #[Route('/admin/medecins/attente', name: 'admin_medecins_attente')]
    public function medecinEnAttente(
        ProfessionnelSanteRepository $medecinRepo
    ): Response {
        return $this->render('admin/medecins.html.twig', [
            'medecins' => $medecinRepo->findBy(['statut' => 'en_attente']),
        ]);
    }

    /**Validation d'un médecin Change le statut en "valide" et active le compte utilisateur*/
    #[Route('/admin/medecin/{id}/valider', name: 'admin_medecin_valider', methods: ['POST'])]
    public function validerMedecin(
        \App\Entity\ProfessionnelSante $medecin,
        EntityManagerInterface $em
    ): Response {
        $medecin->setStatut('valide');
        $medecin->getUser()->setIsActive(true); 
        $em->flush();

        $this->addFlash('success', 'Médecin validé avec succès');
        return $this->redirectToRoute('admin_medecins_attente');
    }

    /**Refus d'un médecin desactive le compte*/
    #[Route('/admin/medecin/{id}/refuser', name: 'admin_medecin_refuser', methods: ['POST'])]
    public function refuserMedecin(
        \App\Entity\ProfessionnelSante $medecin,
        EntityManagerInterface $em
    ): Response {
        $medecin->setStatut('refuse');
        $medecin->getUser()->setIsActive(false); // Bloque la connexion
        $em->flush();

        $this->addFlash('error', 'Médecin refusé');
        return $this->redirectToRoute('admin_medecins_attente');
    }

    /** Liste de tous les utilisateurs */
    #[Route('/admin/users', name: 'admin_users')]
    public function users(UserRepository $userRepo): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepo->findAll(),
        ]);
    }

    /**activer/Désactiver un compte utilisateur*/
    #[Route('/admin/user/{id}/toggle', name: 'admin_user_toggle', methods: ['POST'])]
    public function toggleUser(
        User $user,
        EntityManagerInterface $em
    ): Response {
        // Inverse l'état actuel
        $user->setIsActive(!$user->isActive());
        $em->flush();

        $this->addFlash('success', 'Compte mis à jour');
        return $this->redirectToRoute('admin_users');
    }
}