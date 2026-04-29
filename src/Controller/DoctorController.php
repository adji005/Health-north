<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\Disponibilite;
use App\Entity\Ordonnance;
use App\Repository\RendezVousRepository; //Recup les rdv en bdd
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** Contrôleur de l'espace médecin Gère le planning, les consultations et les disponibilités*/
#[IsGranted('ROLE_DOCTOR')]
class DoctorController extends AbstractController
{
    /**Dashboard médecin  affiche les RDV du jour + les disponibilités libree*/
    #[Route('/doctor/dashboard', name: 'doctor_dashboard')]
    public function dashboard(): Response
    {
        $user = $this->getUser();
        $medecin = $user->getMedecin();

        // Récupérer toutes les disponibilités
        $toutesLesDispos = $medecin->getDisponibilites();

        // Filtrer uniquement celles qui n'ont PAS de RDV
        $dispoLibres = [];
        foreach ($toutesLesDispos as $dispo) {
            $rdvExiste = false;

            // Vérifier si un RDV existe à cette date/heure
            foreach ($medecin->getRendezVous() as $rdv) {
                if ($rdv->getStatut() === 'confirme' || $rdv->getStatut() === 'termine') {
                    $dateDispo = $dispo->getDate();
                    $heureDebut = $dispo->getHeureDebut();
                    $dateRdv = $rdv->getDateHeure();

                    if (
                        $dateDispo->format('Y-m-d') === $dateRdv->format('Y-m-d') &&
                        $heureDebut->format('H:i') === $dateRdv->format('H:i')
                    ) {
                        $rdvExiste = true;
                        break;
                    }
                }
            }

            if (!$rdvExiste) {
                $dispoLibres[] = $dispo;
            }
        }

        return $this->render('doctor/dashboard.html.twig', [
            'medecin' => $medecin,
            'rendezVous' => $medecin->getRendezVous(),
            'disponibilites' => $dispoLibres,
        ]);
    }

    /**Ajout d'une disponibilité */
    #[Route('/doctor/disponibilite/ajouter', name: 'doctor_dispo_ajouter', methods: ['POST'])]
    public function ajouterDisponibilite(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $medecin = $user->getMedecin();

        // Création du créneau
        $dispo = new Disponibilite();
        $dispo->setDate(new \DateTime($request->request->get('date')));
        $dispo->setHeureDebut(new \DateTime($request->request->get('heureDebut')));
        $dispo->setHeureFin(new \DateTime($request->request->get('heureFin')));
        $dispo->setMedecin($medecin);

        $em->persist($dispo);
        $em->flush();

        $this->addFlash('success', 'Disponibilité ajoutée !');
        return $this->redirectToRoute('doctor_dashboard');
    }

    /**Suppression d'une disponibilité + annulation du RDV associé si existant*/
    #[Route('/doctor/disponibilite/{id}/supprimer', name: 'doctor_dispo_supprimer', methods: ['POST'])]
    public function supprimerDisponibilite(
        Disponibilite $dispo,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $medecin = $user->getMedecin();

        // Vérifier que cette dispo appartient bien à ce médecin
        if ($dispo->getMedecin() !== $medecin) {
            throw $this->createAccessDeniedException();
        }

        // Chercher un RDV correspondant à cette disponibilité
        $dateDispo = $dispo->getDate();
        $heureDebut = $dispo->getHeureDebut();

        foreach ($medecin->getRendezVous() as $rdv) {
            if ($rdv->getStatut() === 'confirme' || $rdv->getStatut() === 'termine') {
                $dateRdv = $rdv->getDateHeure();

                if (
                    $dateDispo->format('Y-m-d') === $dateRdv->format('Y-m-d') &&
                    $heureDebut->format('H:i') === $dateRdv->format('H:i')
                ) {
                    // RDV trouvé → l'annuler
                    $rdv->annuler();
                    break;
                }
            }
        }

        $em->remove($dispo);
        $em->flush();

        $this->addFlash('success', 'Disponibilité supprimée');
        return $this->redirectToRoute('doctor_dashboard');
    }

    /**Annulation d'un RDV par le médecin*/
    #[Route('/doctor/rendez-vous/{id}/annuler', name: 'doctor_rdv_annuler', methods: ['POST'])]
    public function annulerRdv(
        \App\Entity\RendezVous $rdv,
        EntityManagerInterface $em
    ): Response {

        $user = $this->getUser();
        $medecin = $user->getMedecin();

        // vérifier que c'est un RDV de ce médecin
        if ($rdv->getMedecin() !== $medecin) {
            throw $this->createAccessDeniedException();
        }

        $rdv->annuler();
        $em->flush();

        $this->addFlash('success', 'Rendez-vous annulé');
        return $this->redirectToRoute('doctor_dashboard');
    }

    /**Affichage du dossier patient pour un RDV donné*/
    #[Route('/doctor/patient/{id}/dossier', name: 'doctor_patient_dossier')]
    public function voirDossier(
        \App\Entity\Patient $patient,
        Request $request,
        RendezVousRepository $rdvRepo,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $medecin = $user->getMedecin();

        // Récupérer le RDV spécifique 
        $rdvId = $request->query->get('rdv_id');

        if ($rdvId) {
            $rdv = $rdvRepo->find($rdvId);

            // Vérifications de sécurité
            if (!$rdv || $rdv->getMedecin() !== $medecin || $rdv->getPatient() !== $patient) {
                throw $this->createAccessDeniedException();
            }
        } else {

            $rdv = $rdvRepo->findOneBy([
                'patient' => $patient,
                'medecin' => $medecin,
            ]);

            if (!$rdv) {
                throw $this->createAccessDeniedException('Vous n\'avez pas de RDV avec ce patient.');
            }
        }

        // Récupérer la consultation liée au rdv
        $consultation = $em->getRepository(Consultation::class)
            ->findOneBy(['rendezVous' => $rdv]);

        return $this->render('doctor/dossier.html.twig', [
            'patient' => $patient,
            'rdv' => $rdv,
            'consultation' => $consultation,
        ]);
    }

    /** + upload d'ordonnance + Termine le RDV (change statut en 'termine')*/
    #[Route('/doctor/consultation/creer/{id}', name: 'doctor_consultation_creer', methods: ['POST'])]
    public function creerConsultation(
        \App\Entity\RendezVous $rdv,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $medecin = $user->getMedecin();

        $consultation = new Consultation();
        $consultation->setRendezVous($rdv);
        $consultation->setPatient($rdv->getPatient());
        $consultation->setMedecin($medecin);
        $consultation->setCreatedAt(new \DateTimeImmutable());
        $consultation->setNotes($request->request->get('notes'));

        // Marquer le RDV comme terminé
        $rdv->setStatut('termine');

        $em->persist($consultation);
        $em->flush(); // Flush pour avoir l'ID de la consultation

        // Gestion de l'upload d'ordonnance (fichier PDF)
        $ordonnanceFile = $request->files->get('ordonnance');
        if ($ordonnanceFile) {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/ordonnances';

            // Créer le dossier s'il n'existe pas
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }

            // Nommage du fichier consultation_ID.pdf
            $filename = 'consultation_' . $consultation->getId() . '.pdf';
            $ordonnanceFile->move($uploadsDir, $filename);

            // Créer l'ordonnance
            $ordonnance = new Ordonnance();
            $ordonnance->setConsultation($consultation);
            $ordonnance->setDateCreation(new \DateTimeImmutable());
            $ordonnance->setContenu('uploads/ordonnances/' . $filename);

            $em->persist($ordonnance);
            $em->flush();
        }

        $this->addFlash('success', 'Consultation terminée');
        return $this->redirectToRoute('doctor_dashboard');
    }
}
