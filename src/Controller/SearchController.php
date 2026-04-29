<?php

namespace App\Controller;

use App\Entity\ProfessionnelSante;
use App\Repository\ProfessionnelSanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**Contrôleur de recherche de médecins */
class SearchController extends AbstractController
{
    /**recherche de médecins avec filtres (ville, spécialité, secteur) Affiche uniquement les médecins validés avec disponibilité libre*/
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(
        Request $request,
        ProfessionnelSanteRepository $medecinRepo
    ): Response {
        // Récupération des paramètres du formulaire dans l'URL
        $ville = $request->query->get('ville');
        $specialite = $request->query->get('specialite');
        $secteur = $request->query->get('secteur');

        // Requête SQL pour récupérer les médecins validés
        $qb = $medecinRepo->createQueryBuilder('m')
            ->where('m.statut = :statut')
            ->setParameter('statut', 'valide');

        // Filtre par ville (recherche partielle)
        if ($ville) {
            $qb->andWhere('m.ville LIKE :ville')
                ->setParameter('ville', '%' . $ville . '%');
        }

        // Filtre par spécialité (recherche partielle)
        if ($specialite) {
            $qb->andWhere('m.specialite LIKE :specialite')
                ->setParameter('specialite', '%' . $specialite . '%');
        }

        // Filtre par secteur
        if ($secteur) {
            $qb->andWhere('m.secteur = :secteur')
                ->setParameter('secteur', $secteur);
        }

        // Exécution de la requête
        $medecins = $qb->getQuery()->getResult();

        // Filtrer uniquement les médecins avec au moins une disponibilité LIBRE
        $medecinsAvecDispos = [];

        foreach ($medecins as $medecin) {
            $toutesLesDispos = $medecin->getDisponibilites();
            $aDispoLibre = false;

            foreach ($toutesLesDispos as $dispo) {
                $rdvExiste = false;

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
                    $aDispoLibre = true;
                    break;
                }
            }

            if ($aDispoLibre) {
                $medecinsAvecDispos[] = $medecin;
            }
        }

        return $this->render('search/results.html.twig', [
            'medecins' => $medecinsAvecDispos,
            'ville' => $ville,
            'specialite' => $specialite,
        ]);
    }

    #[Route('/pro/{id}', name: 'pro_fiche', methods: ['GET'])]
    public function fiche(ProfessionnelSante $medecin): Response
    {
        // Filtrer uniquement les disponibilités libres (sans RDV confirmé ou terminé)
        $toutesLesDispos = $medecin->getDisponibilites();
        $dispoLibres = [];

        foreach ($toutesLesDispos as $dispo) {
            $rdvExiste = false;

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

        return $this->render('search/fiche.html.twig', [
            'medecin' => $medecin,
            'disponibilites' => $dispoLibres,
        ]);
    }
}