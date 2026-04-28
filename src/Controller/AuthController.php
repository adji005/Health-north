<?php

namespace App\Controller;


use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use App\Entity\Patient;
use App\Entity\User;
use App\Entity\ProfessionnelSante;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void {}

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('home/index.html.twig');
    }
    #[Route('/register', name: 'app_register')]
    public function registerForm(): Response
    {
        return $this->render('auth/register.html.twig');
    }

    #[Route('/register/doctor', name: 'app_register_doctor')]
    public function registerDoctorForm(): Response
    {
        return $this->render('auth/register_doctor.html.twig');
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
public function register(
    Request $request,
    UserPasswordHasherInterface $passwordHasher,
    EntityManagerInterface $em
): JsonResponse {
    $data = json_decode($request->getContent(), true);

    if (!isset($data['email'], $data['password'], $data['nom'], $data['prenom'], $data['dateDeNaissance'], $data['telephone'], $data['adresse'])) {
        return $this->json(['error' => 'Champs manquants'], 400);
    }

    $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
    if ($existingUser) {
        $this->addFlash('error', 'Compte existant déja');

        return $this->json(['error' => 'Email déjà utilisé'], 400);
    }

    $user = new User();
    $user->setEmail($data['email']);
    $user->setRoles(['ROLE_PATIENT']);
    $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
    $user->setIsActive(true);
    $user->setCreatedAt(new \DateTimeImmutable());

    $patient = new Patient();
    $patient->setNom($data['nom']);
    $patient->setPrenom($data['prenom']);
    $patient->setDateDeNaissance(new \DateTime($data['dateDeNaissance']));
    $patient->setTelephone($data['telephone']);
    $patient->setAdresse($data['adresse']);
    $patient->setUser($user);

    $em->persist($user);
    $em->persist($patient);
    $em->flush();

    
    $this->addFlash('success', 'Compte créer vous serez bientot redirigé');

    return $this->json([
        'message' => 'Compte créé avec succès',
        'email' => $data['email'],
        'password' => $data['password']
    ], 201);
}

    #[Route('/api/register/doctor', name: 'api_register_doctor', methods: ['POST'])]
    public function registerDoctor(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'], $data['nom'], $data['prenom'], $data['specialite'], $data['numeroRPPS'], $data['telephone'], $data['adresse'], $data['ville'], $data['codePostal'], $data['secteur'])) {
            return $this->json(['error' => 'Champs manquants'], 400);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'Email déjà utilisé'], 400);
            
    $this->addFlash('success', 'Consultation terminée');
    return $this->redirectToRoute('doctor_dashboard');
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_DOCTOR']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setIsActive(false);
        $user->setCreatedAt(new \DateTimeImmutable());

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
        $medecin->setConventionneSecu($data['conventionneSecu'] ?? false);
        $medecin->setStatut('en_attente');
        $medecin->setUser($user);

        $em->persist($user);
        $em->persist($medecin);
        $em->flush();

        $this->addFlash('success', 'Demande envoyé !');


        return $this->json([
            'message' => 'Demande envoyée, en attente de validation',
        ], 201);
    

    }

}
