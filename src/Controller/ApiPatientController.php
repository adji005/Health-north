<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Repository\ProfessionnelSanteRepository;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/patient')]
#[IsGranted('ROLE_PATIENT')]
class ApiPatientController extends AbstractController
{
    /** GET /api/patient/rdv — RDV à venir (confirmés) */
    #[Route('/rdv', name: 'api_patient_rdv', methods: ['GET'])]
    public function getRdv(EntityManagerInterface $em): JsonResponse
    {
        $patient = $this->getUser()->getPatient();

        $rdvs = $em->getRepository(RendezVous::class)->findBy(
            ['patient' => $patient, 'statut' => 'confirme'],
            ['dateHeure' => 'ASC']
        );

        return $this->json(array_map(fn($rdv) => [
            'id'        => $rdv->getId(),
            'dateHeure' => $rdv->getDateHeure()->format('Y-m-d H:i'),
            'statut'    => $rdv->getStatut(),
            'motif'     => $rdv->getMotif(),
            'medecin'   => [
                'id'         => $rdv->getMedecin()->getId(),
                'nom'        => $rdv->getMedecin()->getNom(),
                'prenom'     => $rdv->getMedecin()->getPrenom(),
                'specialite' => $rdv->getMedecin()->getSpecialite(),
            ],
            'canCancel' => $rdv->canPatientCancel(),
        ], $rdvs));
    }

    /** GET /api/patient/rdv/passes — RDV passés (terminés + annulés) */
    #[Route('/rdv/passes', name: 'api_patient_rdv_passes', methods: ['GET'])]
    public function getRdvPasses(EntityManagerInterface $em): JsonResponse
    {
        $patient = $this->getUser()->getPatient();

        $rdvs = $em->getRepository(RendezVous::class)->findBy(
            ['patient' => $patient],
            ['dateHeure' => 'DESC']
        );

        $passes = array_filter($rdvs, fn($rdv) => in_array($rdv->getStatut(), ['termine', 'annule']));

        return $this->json(array_values(array_map(fn($rdv) => [
            'id'        => $rdv->getId(),
            'dateHeure' => $rdv->getDateHeure()->format('Y-m-d H:i'),
            'statut'    => $rdv->getStatut(),
            'motif'     => $rdv->getMotif(),
            'medecin'   => [
                'id'         => $rdv->getMedecin()->getId(),
                'nom'        => $rdv->getMedecin()->getNom(),
                'prenom'     => $rdv->getMedecin()->getPrenom(),
                'specialite' => $rdv->getMedecin()->getSpecialite(),
            ],
            'ordonnance' => $rdv->getConsultation()?->getOrdonnance() ? [
                'id'     => $rdv->getConsultation()->getOrdonnance()->getId(),
                'fichier' => $rdv->getConsultation()->getOrdonnance()->getContenu(),
                'date'   => $rdv->getConsultation()->getOrdonnance()->getDateCreation()->format('Y-m-d'),
            ] : null,
        ], $passes)));
    }

    /** POST /api/patient/rdv/annuler/{id} — Annuler un RDV */
    #[Route('/rdv/annuler/{id}', name: 'api_patient_rdv_annuler', methods: ['POST'])]
    public function annulerRdv(RendezVous $rdv, EntityManagerInterface $em): JsonResponse
    {
        $patient = $this->getUser()->getPatient();

        if ($rdv->getPatient() !== $patient) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        if (!$rdv->canPatientCancel()) {
            return $this->json(['error' => 'Annulation impossible à moins de 24h'], 400);
        }

        $rdv->annuler();
        $em->flush();

        return $this->json(['message' => 'Rendez-vous annulé']);
    }

    /** GET /api/patient/medecins — Liste des médecins validés avec dispos libres */
    #[Route('/medecins', name: 'api_patient_medecins', methods: ['GET'])]
    public function getMedecins(ProfessionnelSanteRepository $medecinRepo): JsonResponse
    {
        $medecins = $medecinRepo->findBy(['statut' => 'valide']);

        $result = [];
        foreach ($medecins as $medecin) {
            $dispoLibres = [];
            foreach ($medecin->getDisponibilites() as $dispo) {
                $rdvExiste = false;
                foreach ($medecin->getRendezVous() as $rdv) {
                    if (in_array($rdv->getStatut(), ['confirme', 'termine'])) {
                        if (
                            $dispo->getDate()->format('Y-m-d') === $rdv->getDateHeure()->format('Y-m-d') &&
                            $dispo->getHeureDebut()->format('H:i') === $rdv->getDateHeure()->format('H:i')
                        ) {
                            $rdvExiste = true;
                            break;
                        }
                    }
                }
                if (!$rdvExiste) {
                    $dispoLibres[] = [
                        'date'       => $dispo->getDate()->format('Y-m-d'),
                        'heureDebut' => $dispo->getHeureDebut()->format('H:i'),
                        'heureFin'   => $dispo->getHeureFin()->format('H:i'),
                    ];
                }
            }

            if (!empty($dispoLibres)) {
                $result[] = [
                    'id'         => $medecin->getId(),
                    'nom'        => $medecin->getNom(),
                    'prenom'     => $medecin->getPrenom(),
                    'specialite' => $medecin->getSpecialite(),
                    'ville'      => $medecin->getVille(),
                    'secteur'    => $medecin->getSecteur(),
                    'disponibilites' => $dispoLibres,
                ];
            }
        }

        return $this->json($result);
    }

    /** POST /api/patient/rdv/prendre — Prendre un RDV */
    #[Route('/rdv/prendre', name: 'api_patient_rdv_prendre', methods: ['POST'])]
    public function prendreRdv(
        Request $request,
        EntityManagerInterface $em,
        ProfessionnelSanteRepository $medecinRepo
    ): JsonResponse {
        $patient = $this->getUser()->getPatient();
        $data = json_decode($request->getContent(), true);

        if (!isset($data['medecin_id'], $data['dateHeure'])) {
            return $this->json(['error' => 'Champs manquants'], 400);
        }

        $medecin = $medecinRepo->find($data['medecin_id']);
        if (!$medecin) {
            return $this->json(['error' => 'Médecin introuvable'], 404);
        }

        $rdv = new RendezVous();
        $rdv->setPatient($patient);
        $rdv->setMedecin($medecin);
        $rdv->setDateHeure(new \DateTimeImmutable($data['dateHeure']));
        $rdv->setMotif($data['motif'] ?? null);
        $rdv->setCreatedAt(new \DateTimeImmutable());

        $em->persist($rdv);
        $em->flush();

        return $this->json(['message' => 'Rendez-vous confirmé', 'id' => $rdv->getId()], 201);
    }

    /** GET /api/patient/profil — Infos du patient connecté */
    #[Route('/profil', name: 'api_patient_profil', methods: ['GET'])]
    public function getProfil(): JsonResponse
    {
        $patient = $this->getUser()->getPatient();

        return $this->json([
            'nom'            => $patient->getNom(),
            'prenom'         => $patient->getPrenom(),
            'email'          => $this->getUser()->getEmail(),
            'telephone'      => $patient->getTelephone(),
            'dateDeNaissance' => $patient->getDateDeNaissance()->format('Y-m-d'),
        ]);
    }
}