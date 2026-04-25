<?php

namespace App\Controller;

use App\Entity\ProfessionnelSante;
use App\Repository\ProfessionnelSanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(
        Request $request,
        ProfessionnelSanteRepository $medecinRepo
    ): Response {
        $ville = $request->query->get('ville');
        $specialite = $request->query->get('specialite');
        $secteur = $request->query->get('secteur');

        $qb = $medecinRepo->createQueryBuilder('m')
            ->where('m.statut = :statut')
            ->setParameter('statut', 'valide');

        if ($ville) {
            $qb->andWhere('m.ville LIKE :ville')
               ->setParameter('ville', '%' . $ville . '%');
        }

        if ($specialite) {
            $qb->andWhere('m.specialite LIKE :specialite')
               ->setParameter('specialite', '%' . $specialite . '%');
        }

        if ($secteur) {
            $qb->andWhere('m.secteur = :secteur')
               ->setParameter('secteur', $secteur);
        }

        $medecins = $qb->getQuery()->getResult();

        return $this->render('search/results.html.twig', [
            'medecins' => $medecins,
            'ville' => $ville,
            'specialite' => $specialite,
        ]);
    }

    #[Route('/pro/{id}', name: 'pro_fiche', methods: ['GET'])]
    public function fiche(ProfessionnelSante $medecin): Response
    {
        return $this->render('search/fiche.html.twig', [
            'medecin' => $medecin,
        ]);
    }
}