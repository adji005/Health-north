<?php

namespace App\Controller;

use App\Entity\DossierPatient; // necessaire car on creer ou modifie son dossier patient
use App\Entity\Mutuelle; //necessaire car oncreer une mutuelle
use App\Entity\RendezVous; //necessaire car on creer un rdv
use App\Repository\ProfessionnelSanteRepository;
use Doctrine\ORM\EntityManagerInterface; //necessaire car on utilise flush et persist BDD
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**Contrôleur gérant l'espace patient Accessible que pour  les utilisateurs avec ROLE_PATIENT*/
#[IsGranted('ROLE_PATIENT')]
class PatientController extends AbstractController
{
    /**Dashboard patient Affiche les rdv a venir */
    #[Route('/patient/dashboard', name: 'patient_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        // Récupère le patient connecté via la relation User Patient
        $patient = $this->getUser()->getPatient();

        // Récupère uniquement les RDV confirmés triés
        $rdvConfirmes = $em->getRepository(RendezVous::class)->findBy([
            'patient' => $patient,
            'statut' => 'confirme'
        ], ['dateHeure' => 'ASC']);

        return $this->render('patient/dashboard.html.twig', [
            'patient' => $patient,
            'rendezVous' => $rdvConfirmes,
        ]);
    }

    /**Affiche le dossier médical du patient*/
    #[Route('/patient/dossier', name: 'patient_dossier')]
    public function dossier(): Response
    {
        $patient = $this->getUser()->getPatient();

        return $this->render('patient/dossier.html.twig', [
            'patient' => $patient,
            'dossier' => $patient->getDossier(),
        ]);
    }

    /** Modification du dossier médical , Crée le dossier s'il n'existe pas encore*/
    #[Route('/patient/dossier/modifier', name: 'patient_dossier_modifier', methods: ['POST'])]
    public function modifierDossier(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $patient = $this->getUser()->getPatient();
        $dossier = $patient->getDossier();

        // Si le patient n'a pas encore de dossier on le crée
        if (!$dossier) {
            $dossier = new DossierPatient();
            $dossier->setPatient($patient);
        }

        // Maj
        $dossier->setAntecedents($request->request->get('antecedents'));
        $dossier->setMaladiesChroniques($request->request->get('maladiesChroniques'));
        $dossier->setAllergies($request->request->get('allergies'));
        $dossier->setGroupeSanguin($request->request->get('groupeSanguin'));

        $em->persist($dossier);
        $em->flush();

        return $this->redirectToRoute('patient_dossier');
    }

    /**Liste de TOUS les rendez-vous du patient*/
    #[Route('/patient/rendez-vous', name: 'patient_rdv')]
    public function rendezVous(): Response
    {
        $patient = $this->getUser()->getPatient();

        return $this->render('patient/rendez-vous.html.twig', [
            'patient' => $patient,
            'rendezVous' => $patient->getRendezVous(),
        ]);
    }

    /**Prise de rendez-vous*/
    #[Route('/patient/rendez-vous/prendre', name: 'patient_rdv_prendre', methods: ['POST'])]
    public function prendreRdv(
        Request $request,
        EntityManagerInterface $em,
        ProfessionnelSanteRepository $medecinRepo
    ): Response {
        $patient = $this->getUser()->getPatient();

        // Récupère le médecin sélectionné
        $medecin = $medecinRepo->find($request->request->get('medecin_id'));

        if (!$medecin) {
            return $this->redirectToRoute('search');
        }

        $dateHeure = new \DateTimeImmutable($request->request->get('dateHeure'));

        // Création du rendez-vous
        $rdv = new RendezVous();
        $rdv->setPatient($patient);
        $rdv->setMedecin($medecin);
        $rdv->setDateHeure($dateHeure);
        $rdv->setMotif($request->request->get('motif'));
        $rdv->setCreatedAt(new \DateTimeImmutable());

        $em->persist($rdv);
        $em->flush();

        $this->addFlash('success', 'Rendez-vous confirmé !');
        return $this->redirectToRoute('patient_rdv');
    }

    /**Annulation d'un rendez-vous annulation impossible à moins de 24h
     */
    #[Route('/patient/rendez-vous/{id}/annuler', name: 'patient_rdv_annuler', methods: ['POST'])]
    public function annulerRdv(
        RendezVous $rdv,
        EntityManagerInterface $em
    ): Response {
        $patient = $this->getUser()->getPatient();

        // SÉCURITÉ : Vérifier que c'est bien le RDV de ce patient
        if ($rdv->getPatient() !== $patient) {
            throw $this->createAccessDeniedException();
        }

        // RÈGLE MÉTIER : Vérifier le délai de 24h
        if (!$rdv->canPatientCancel()) {
            $this->addFlash('error', 'Annulation impossible à moins de 24h du rendez-vous');
            return $this->redirectToRoute('patient_dashboard');
        }

        // Change le statut du RDV 
        $rdv->annuler();
        $em->flush();

        $this->addFlash('success', 'Rendez-vous annulé');
        return $this->redirectToRoute('patient_dashboard');
    }

    /**Modification des informations de mutuelle Crée la mutuelle si elle n'existe pas*/
    #[Route('/patient/mutuelle/modifier', name: 'patient_mutuelle_modifier', methods: ['POST'])]
    public function modifierMutuelle(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $patient = $this->getUser()->getPatient();

        // Récupère la mutuelle ou en crée une nouvelle
        $mutuelle = $patient->getMutuelle() ?: new Mutuelle();
        $mutuelle->setOrganisme($request->request->get('organisme'));
        $mutuelle->setNumeroAdherent($request->request->get('numeroAdherent'));
        $taux = $request->request->get('tauxRemboursement');
        $mutuelle->setTauxRemboursement($taux !== '' ? (float)$taux : null);
        // Si nouvelle mutuelle, lier au patient
        if (!$mutuelle->getPatient()) {
            $mutuelle->setPatient($patient);
        }

        $em->persist($mutuelle);
        $em->flush();

        $this->addFlash('success', 'Mutuelle mise à jour');
        return $this->redirectToRoute('patient_dossier');
    }
}
