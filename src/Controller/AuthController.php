<?php

namespace App\Controller;

use App\Entity\Patient;  // nécessaire car je creer un patient 
use App\Entity\User; // nécessaire car je creer un user
use App\Entity\ProfessionnelSante; // nécessaire car je creer un medecin
use Doctrine\ORM\EntityManagerInterface;  // $em pour persist() et flush()
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse; // return $this->json()
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**Contrôleur gérant la connexion et l'inscription des user */

class AuthController extends AbstractController
{

#[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
public function apiLogin(
    Request $request,
    \Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface $JWTManager,
    \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $passwordHasher,
    \App\Repository\UserRepository $userRepo
): JsonResponse {
    $data = json_decode($request->getContent(), true);
    
    $user = $userRepo->findOneBy(['email' => $data['email'] ?? '']);
    
    if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'] ?? '')) {
        return $this->json(['error' => 'Identifiants invalides'], 401);
    }
    
    if (!$user->isActive()) {
        return $this->json(['error' => 'Compte inactif'], 403);
    }
    
    $token = $JWTManager->create($user);
    
    return $this->json(['token' => $token]);
}
    /**Page de connexion qui affiche le form de Login */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // RECUP la derniere erreur de connexion
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupère le dernier email saisi pour pré-remplir le formulaire
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }

    /** Déconnexion géré par symfony dans security yamll*/
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void {}

    /**Page d'accueil publique*/
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        // Si connecté, rediriger vers le dashboard approprié Pour pas créer de conflit
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_dashboard');
            }
            if ($this->isGranted('ROLE_DOCTOR')) {
                return $this->redirectToRoute('doctor_dashboard');
            }
            if ($this->isGranted('ROLE_PATIENT')) {
                return $this->redirectToRoute('patient_dashboard');
            }
        }

        return $this->render('home/index.html.twig');
    }
    /**Affiche le formulaire d'inscription patient*/

    #[Route('/register', name: 'app_register')]
    public function registerForm(): Response
    {
        return $this->render('auth/register.html.twig');
    }

    /**Affiche le formulaire d'inscription médecin*/
    #[Route('/register/doctor', name: 'app_register_doctor')]
    public function registerDoctorForm(): Response
    {
        return $this->render('auth/register_doctor.html.twig');
    }

    /**API : Inscription d'un patient, Crée un compte user (ROLE_PATIENT) + entité Patient
     * @param Request $request Requête HTTP contenant les données JSON
     * @param UserPasswordHasherInterface $passwordHasher Service de hashage des mots de passe
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return JsonResponse Réponse JSON (succès ou erreur)
     */
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): JsonResponse {
        // decodage des données JSON
        $data = json_decode($request->getContent(), true);

        // condition1 Sécurité du mot de passe min 8 caractères, 1 majuscule, 1 chiffre
        if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $data['password'])) {
            return $this->json([
                'error' => 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule et 1 chiffre'
            ], 400);
        }

        // condition 2 Vérification de la présence de tous les champs obligatoires
        if (!isset($data['email'], $data['password'], $data['nom'], $data['prenom'], $data['dateDeNaissance'], $data['telephone'], $data['adresse'])) {
            return $this->json(['error' => 'Champs manquants'], 400);
        }

        // condition 3 Vérification unicité de l'email
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'Email déjà utilisé'], 400);
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_PATIENT']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setIsActive(true); // Patient actif immédiatement
        $user->setCreatedAt(new \DateTimeImmutable());

        // Création du profil patient
        $patient = new Patient();
        $patient->setNom($data['nom']);
        $patient->setPrenom($data['prenom']);
        $patient->setDateDeNaissance(new \DateTime($data['dateDeNaissance']));
        $patient->setTelephone($data['telephone']);
        $patient->setAdresse($data['adresse']);
        $patient->setUser($user); // Relation OneToOne avec User genre un 

        // Sauvegarde en base de données
        $em->persist($user);
        $em->persist($patient);
        $em->flush();

        $this->addFlash('success', 'Compte créer vous serez bientot redirigé');
        return $this->json([
            'message' => 'Compte créé avec succès',
            'email' => $data['email'],
            'password' => $data['password'] // Nécessaire pour l'auto-login JavaScript
        ], 201);
    }

    /** CREATION API API Inscription d'un médecin, Crée un compte User (ROLE_DOCTOR, inactif car doit etre validé par l'admin + entité ProfessionnelSante
     * @param Request $request Requête HTTP contenant les données JSON
     * @param UserPasswordHasherInterface $passwordHasher Service de hashage des mots de passe
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return JsonResponse Réponse JSON (succès ou erreur)
     */
    #[Route('/api/register/doctor', name: 'api_register_doctor', methods: ['POST'])]
    public function registerDoctor(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): JsonResponse {
        // Décodage des données JSONNNN
        $data = json_decode($request->getContent(), true);

        // codition 1 similaire a celle du register 
        if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $data['password'])) {
            return $this->json([
                'error' => 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule et 1 chiffre'
            ], 400);
        }

        // condition 2
        if (!isset($data['email'], $data['password'], $data['nom'], $data['prenom'], $data['specialite'], $data['numeroRPPS'], $data['telephone'], $data['adresse'], $data['ville'], $data['codePostal'], $data['secteur'])) {
            return $this->json(['error' => 'Champs manquants'], 400);
        }

        // condition 3
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'Email déjà utilisé'], 400);
        }

        // Création de l'utilisateur (inactif)
        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_DOCTOR']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setIsActive(false); // Bloqué jusqu'à validation admin
        $user->setCreatedAt(new \DateTimeImmutable());

        // Création du profil médecin
        $medecin = new ProfessionnelSante();
        $medecin->setNom($data['nom']);
        $medecin->setPrenom($data['prenom']);
        $medecin->setSpecialite($data['specialite']);
        $medecin->setNumeroRPPS($data['numeroRPPS']);
        $medecin->setTelephone($data['telephone']);
        $medecin->setAdresse($data['adresse']);
        $medecin->setVille($data['ville']);
        $medecin->setCodePostal($data['codePostal']);
        $medecin->setSecteur($data['secteur']);
        $medecin->setConventionneSecu($data['conventionneSecu'] ?? false); // Checkbox optionnelle
        $medecin->setStatut('en_attente'); // Statut en attente de validation
        $medecin->setUser($user); // Relation OneToOne avec User

        // Sauvegarde en base de données
        $em->persist($user);
        $em->persist($medecin);
        $em->flush();

        return $this->json([
            'message' => 'Demande envoyée, en attente de validation',
        ], 201);
    }
}
