<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**UserChecker vérifie l'état du compte utilisateur lors de la connexion et bloque les comptes inactifs (médecins non validés, comptes désactivés par admin)*/
class UserChecker implements UserCheckerInterface
{
    /**Vérifie l'utilisateur avt l'authentification*/
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Bloque les comptes inactifs 
        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte n\'est pas validé par l\'administrateur.'
            );
        }
    }

   
    public function checkPostAuth(UserInterface $user): void
    {
        // Rien à vérifier après connexion
    }
}