<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProfessionnelSanteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        UserRepository $userRepo,
        ProfessionnelSanteRepository $medecinRepo
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'totalPatients' => count($userRepo->findAll()) - count($medecinRepo->findAll()) - 1, // -1 pour l'admin
            'medecinEnAttente' => $medecinRepo->findBy(['statut' => 'en_attente']),
            'totalMedecins' => count($medecinRepo->findBy(['statut' => 'valide'])),
        ]);
    }

    #[Route('/admin/medecins/attente', name: 'admin_medecins_attente')]
    public function medecinEnAttente(
        ProfessionnelSanteRepository $medecinRepo
    ): Response {
        return $this->render('admin/medecins.html.twig', [
            'medecins' => $medecinRepo->findBy(['statut' => 'en_attente']),
        ]);
    }

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

    #[Route('/admin/medecin/{id}/refuser', name: 'admin_medecin_refuser', methods: ['POST'])]
    public function refuserMedecin(
        \App\Entity\ProfessionnelSante $medecin,
        EntityManagerInterface $em
    ): Response {
        $medecin->setStatut('refuse');
        $medecin->getUser()->setIsActive(false);
        $em->flush();

        $this->addFlash('error', 'Médecin refusé');
        return $this->redirectToRoute('admin_medecins_attente');
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function users(UserRepository $userRepo): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepo->findAll(),
        ]);
    }

    #[Route('/admin/user/{id}/toggle', name: 'admin_user_toggle', methods: ['POST'])]
    public function toggleUser(
        User $user,
        EntityManagerInterface $em
    ): Response {
        $user->setIsActive(!$user->isActive());
        $em->flush();

        $this->addFlash('success', 'Compte mis à jour');
        return $this->redirectToRoute('admin_users');
    }
}