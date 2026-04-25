<?php

namespace App\Controller;

use App\Entity\Mutuelle;
use App\Entity\DossierPatient;
use App\Entity\RendezVous;
use App\Repository\RendezVousRepository;
use App\Repository\ProfessionnelSanteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_PATIENT')]
class PatientController extends AbstractController
{
    #[Route('/patient/dashboard', name: 'patient_dashboard')]
    public function dashboard(): Response
    {
        $patient = $this->getUser()->getPatient();

        return $this->render('patient/dashboard.html.twig', [
            'patient' => $patient,
            'rendezVous' => $patient->getRendezVous(),
        ]);
    }

    #[Route('/patient/dossier', name: 'patient_dossier')]
    public function dossier(): Response
    {
        $patient = $this->getUser()->getPatient();

        return $this->render('patient/dossier.html.twig', [
            'patient' => $patient,
            'dossier' => $patient->getDossier(),
        ]);
    }

    #[Route('/patient/dossier/modifier', name: 'patient_dossier_modifier', methods: ['POST'])]
    public function modifierDossier(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $patient = $this->getUser()->getPatient();
        $dossier = $patient->getDossier();

        if (!$dossier) {
            $dossier = new DossierPatient();
            $dossier->setPatient($patient);
        }

        $dossier->setAntecedents($request->request->get('antecedents'));
        $dossier->setMaladiesChroniques($request->request->get('maladiesChroniques'));
        $dossier->setAllergies($request->request->get('allergies'));
        $dossier->setGroupeSanguin($request->request->get('groupeSanguin'));

        $em->persist($dossier);
        $em->flush();

        return $this->redirectToRoute('patient_dossier');
    }

    #[Route('/patient/rendez-vous', name: 'patient_rdv')]
    public function rendezVous(): Response
    {
        $patient = $this->getUser()->getPatient();

        return $this->render('patient/rendez-vous.html.twig', [
            'patient' => $patient,
            'rendezVous' => $patient->getRendezVous(),
        ]);
    }

    #[Route('/patient/rendez-vous/prendre', name: 'patient_rdv_prendre', methods: ['POST'])]
    public function prendreRdv(
        Request $request,
        EntityManagerInterface $em,
        ProfessionnelSanteRepository $medecinRepo
    ): Response {
        $patient = $this->getUser()->getPatient();

        $medecin = $medecinRepo->find($request->request->get('medecin_id'));

        if (!$medecin) {
            return $this->redirectToRoute('search');
        }

        $dateHeure = new \DateTimeImmutable($request->request->get('dateHeure'));

        // Vérifier si le créneau est déjà pris
        $existingRdv = $em->getRepository(RendezVous::class)->findOneBy([
            'medecin' => $medecin,
            'dateHeure' => $dateHeure,
            'statut' => 'confirme'
        ]);

        if ($existingRdv) {
            $this->addFlash('error', 'Ce créneau est déjà pris, veuillez choisir une autre date');
            return $this->redirectToRoute('pro_fiche', ['id' => $medecin->getId()]);
        }
        $rdv = new RendezVous();
        $rdv->setPatient($patient);
        $rdv->setMedecin($medecin);
        $rdv->setDateHeure(new \DateTimeImmutable($request->request->get('dateHeure')));
        $rdv->setMotif($request->request->get('motif'));
        $rdv->setCreatedAt(new \DateTimeImmutable());

        $em->persist($rdv);
        $em->flush();

        $this->addFlash('success', 'Rendez-vous confirmé !');
        return $this->redirectToRoute('patient_rdv');
    }

    #[Route('/patient/rendez-vous/{id}/annuler', name: 'patient_rdv_annuler', methods: ['POST'])]
    public function annulerRdv(
        RendezVous $rdv,
        EntityManagerInterface $em
    ): Response {
        $patient = $this->getUser()->getPatient();

        if ($rdv->getPatient() !== $patient) {
            throw $this->createAccessDeniedException();
        }

        if (!$rdv->canPatientCancel()) {
            $this->addFlash('error', 'Annulation impossible à moins de 24h du rendez-vous');
            return $this->redirectToRoute('patient_rdv');
        }

        $rdv->annuler();
        $em->flush();

        $this->addFlash('success', 'Rendez-vous annulé');
        return $this->redirectToRoute('patient_rdv');
    }
    #[Route('/patient/mutuelle/modifier', name: 'patient_mutuelle_modifier', methods: ['POST'])]
    public function modifierMutuelle(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $patient = $this->getUser()->getPatient();

        // Récupérer ou créer la mutuelle
        $mutuelle = $patient->getMutuelles()->first() ?: new \App\Entity\Mutuelle();
        $mutuelle->setOrganisme($request->request->get('organisme'));
        $mutuelle->setNumeroAdherent($request->request->get('numeroAdherent'));
        $mutuelle->setTauxRemboursement($request->request->get('tauxRemboursement'));

        if (!$mutuelle->getPatient()) {
            $mutuelle->setPatient($patient);
        }

        $em->persist($mutuelle);
        $em->flush();

        $this->addFlash('success', 'Mutuelle mise à jour');
        return $this->redirectToRoute('patient_dossier');
    }
}
