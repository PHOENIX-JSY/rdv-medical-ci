# Parcours d'onboarding

Objectif : qu'un nouveau patient réussisse sa première réservation en moins de 2 minutes.

| Étape | Écran | Ce que voit l'utilisateur | Action attendue |
|---|---|---|---|
| 1. Découverte | Accueil | Bandeau « Prenez votre rendez-vous médical en ligne », recherche, section « Comment ça marche ? » en 3 étapes | Rechercher ou cliquer « Voir détails » |
| 2. Création de compte | Inscription | 3 champs seulement : nom, téléphone, mot de passe. Mention de collecte minimale | Créer son compte |
| 3. Bienvenue | Accueil (`?bienvenue=1`) | Message « Bienvenue <nom> ! Étape suivante : recherchez un établissement » | Filtrer par ville et service |
| 4. Choix | Fiche établissement | Infos pratiques, service, date, heure | Choisir un créneau et un motif |
| 5. Confirmation | Confirmation | Référence `RDV-XXXXXX`, date, lieu, consignes | Noter la référence |
| 6. Suivi | Mes rendez-vous | À venir / historique, bouton Annuler | Gérer ses rendez-vous |

Un visiteur non connecté peut tout explorer ; la connexion n'est demandée qu'à la validation, puis il revient à sa réservation (`?next=`).
