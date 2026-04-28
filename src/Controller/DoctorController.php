<?php

namespace App\Controller;


use App\Entity\Ordonnance;
use App\Entity\Consultation;
use App\Entity\Disponibilite;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_DOCTOR')]
class DoctorController extends AbstractController
{
    #[Route('/doctor/dashboard', name: 'doctor_dashboard')]
    public function dashboard(RendezVousRepository $rdvRepo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $medecin = $user->getMedecin();

        return $this->render('doctor/dashboard.html.twig', [
            'medecin' => $medecin,
            'rendezVous' => $medecin->getRendezVous(),
        ]);
    }

    #[Route('/doctor/disponibilite/ajouter', name: 'doctor_dispo_ajouter', methods: ['POST'])]
    public function ajouterDisponibilite(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $medecin = $user->getMedecin();

        $dispo = new Disponibilite();
        $dispo->setDate(new \DateTime($request->request->get('date')));
        $dispo->setHeureDebut(new \DateTime($request->request->get('heureDebut')));
        $dispo->setHeureFin(new \DateTime($request->request->get('heureFin')));
        $dispo->setMedecin($medecin);

        $em->persist($dispo);
        $em->flush();

         $this->addFlash('success', 'Disponibilité ajouté !');
        return $this->redirectToRoute('doctor_dashboard');
    }

    #[Route('/doctor/disponibilite/{id}/supprimer', name: 'doctor_dispo_supprimer', methods: ['POST'])]
    public function supprimerDisponibilite(
        Disponibilite $dispo,
        EntityManagerInterface $em
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $medecin = $user->getMedecin();

        if ($dispo->getMedecin() !== $medecin) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($dispo);
        $em->flush();

        return $this->redirectToRoute('doctor_dashboard');
    }

    #[Route('/doctor/rendez-vous/{id}/annuler', name: 'doctor_rdv_annuler', methods: ['POST'])]
    public function annulerRdv(
        \App\Entity\RendezVous $rdv,
        EntityManagerInterface $em
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $medecin = $user->getMedecin();

        if ($rdv->getMedecin() !== $medecin) {
            throw $this->createAccessDeniedException();
        }

        $rdv->annuler();
        $em->flush();

        $this->addFlash('success', 'Rendez-vous annulé');
        return $this->redirectToRoute('doctor_dashboard');
    }

 #[Route('/doctor/patient/{id}/dossier', name: 'doctor_patient_dossier')]
public function voirDossier(
    \App\Entity\Patient $patient,
    Request $request,
    \App\Repository\RendezVousRepository $rdvRepo,
    EntityManagerInterface $em
): Response {
    /** @var \App\Entity\User $user */
    $user = $this->getUser();
    $medecin = $user->getMedecin();

    // Récupérer l'ID du RDV depuis l'URL
    $rdvId = $request->query->get('rdv_id');
    
    if ($rdvId) {
        // Récupérer le RDV spécifique
        $rdv = $rdvRepo->find($rdvId);
        
        if (!$rdv || $rdv->getMedecin() !== $medecin || $rdv->getPatient() !== $patient) {
            throw $this->createAccessDeniedException();
        }
    } else {
        // Fallback : chercher n'importe quel RDV avec ce patient
        $rdv = $rdvRepo->findOneBy([
            'patient' => $patient,
            'medecin' => $medecin,
        ]);

        if (!$rdv) {
            throw $this->createAccessDeniedException('Vous n\'avez pas eu de RDV avec ce patient.');
        }
    }

    // Récupérer la consultation liée à CE rendez-vous
    $consultation = $em->getRepository(\App\Entity\Consultation::class)
        ->findOneBy(['rendezVous' => $rdv]);

    return $this->render('doctor/dossier.html.twig', [
        'patient' => $patient,
        'rdv' => $rdv,
        'consultation' => $consultation,
    ]);
}



#[Route('/doctor/consultation/creer/{id}', name: 'doctor_consultation_creer', methods: ['POST'])]
public function creerConsultation(
    \App\Entity\RendezVous $rdv,
    Request $request,
    EntityManagerInterface $em
): Response {
    /** @var \App\Entity\User $user */
    $user = $this->getUser();
    $medecin = $user->getMedecin();

    // Récupérer ou créer la consultation
    $consultation = $em->getRepository(\App\Entity\Consultation::class)
        ->findOneBy(['rendezVous' => $rdv]);

    if (!$consultation) {
        $consultation = new \App\Entity\Consultation();
        $consultation->setRendezVous($rdv);
        $consultation->setPatient($rdv->getPatient());
        $consultation->setMedecin($medecin);
        $consultation->setCreatedAt(new \DateTimeImmutable());
    }

    $consultation->setNotes($request->request->get('notes'));
    $rdv->setStatut('termine');

    $em->persist($consultation);
    $em->flush(); // Important : flush pour avoir l'ID de la consultation

    // Gérer l'upload de l'ordonnance
    $ordonnanceFile = $request->files->get('ordonnance');
    if ($ordonnanceFile) {
        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/ordonnances';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        // Nom du fichier : consultation_ID.pdf
        $filename = 'consultation_' . $consultation->getId() . '.pdf';
        $ordonnanceFile->move($uploadsDir, $filename);

        // Créer ou mettre à jour l'ordonnance
        $ordonnance = $consultation->getOrdonnance();
        if (!$ordonnance) {
            $ordonnance = new \App\Entity\Ordonnance();
            $ordonnance->setConsultation($consultation);
            $ordonnance->setDateCreation(new \DateTimeImmutable());
        }
        
        $ordonnance->setContenu('uploads/ordonnances/' . $filename);
        
        $em->persist($ordonnance);
        $em->flush();
    }

    $this->addFlash('success', 'Consultation terminée');
    return $this->redirectToRoute('doctor_dashboard');
}
}
