# Health North - Plateforme de prise de rendez-vous médicaux

## Description

Health North est une application web de gestion de rendez-vous médicaux développée avec Symfony 6.4. Elle permet aux patients de rechercher des médecins et de prendre rendez-vous en ligne, tout en offrant aux professionnels de santé une interface pour gérer leur planning et leurs consultations.

## Fonctionnalités

### Pour les patients
- Inscription et connexion 
- Recherche de médecins par ville, spécialité et secteur
- Prise de rendez-vous en ligne
- Gestion du dossier médical personnel
-  Suivi des rendez-vous (confirmés, terminés, annulés)
- Gestion de la mutuelle

### Pour les médecins
- Inscription avec validation par administrateur
- Creation et Gestion des disponibilités
- Consultation du planning
- Création de comptes-rendus de consultation
- Upload d'ordonnances (PDF)
- Accès aux dossiers patients

### Pour les administrateurs
-  Validation des comptes médecins
- Gestion des utilisateurs
- Activation/désactivation de comptes

## Technologies utilisées

- **Backend** : Symfony 6.4, PHP 8.2
- **Base de données** : MySQL 8.0
- **Frontend** : Twig, Bootstrap 5, JavaScript
- **Sécurité** : Symfony Security, password hashing, CSRF protection
- **Tests** : PHPUnit

## Installation

### Prérequis

- PHP 8.2 ou supérieur
- Composer
- MySQL 8.0 ou supérieur
- Symfony CLI (optionnel)

### Étapes d'installation

1. **Cloner le projet**
```bash
git clone https://github.com/adji005/Health-north.git
cd Health-north
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer la base de données**

Créez un fichier `.env.local` et configurez votre connexion :
```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/health_north?serverVersion=8.0"
```

4. **Créer la base de données**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:create
```

5. **Lancer le serveur**
```bash
symfony serve
# OU
php -S localhost:8000 -t public
```

6. **Accéder à l'application**

[text](http://localhost:8000)

## Tests

### Lancer tous les tests
```bash
php bin/phpunit
```

### Tests disponibles
- **Tests d'entité** : Validation des getters/setters
- **Tests de sécurité** : Vérification des accès protégés
- **Tests fonctionnels** : Inscription, validation des données

## Sécurité

- **Mots de passe** : Hashage avec bcrypt
- **Validation** : Regexxpour les mots de passe (8+ caractères, 1 majuscule, 1 chiffre)
- **Protection des routes** : Annotations `#[IsGranted()]`
- **Vérification de propriété** : Les utilisateurs ne peuvent modifier que leurs propres données

## Rôles des uutilisateurs

- **ROLE_PATIENT** : Accès patient
- **ROLE_DOCTOR** : Accès médecin 
- **ROLE_ADMIN** : Accès administrateur 

## Déploiement

Pour déployer en production :

1. Configurer `.env` en mode production
2. Vider le cache : `php bin/console cache:clear --env=prod`
3. Optimiser l'autoloader : `composer dump-autoload --optimize`
4. Configurer un serveur web (Apache/Nginx)
