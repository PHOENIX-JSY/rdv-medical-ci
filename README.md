# RDV-MEDICAL CI

Plateforme web de prise de rendez-vous médicaux à distance dans plusieurs établissements de santé en Côte d'Ivoire.
Projet « TD Projet Web » — MIAGE 2e année. **Aucun framework** : HTML/CSS/JavaScript natifs, PHP natif, SQLite.

> Les établissements, services et créneaux sont des **données de démonstration fictives**.

## Fonctionnalités (MVP)
1. Inscription et connexion du patient (téléphone + mot de passe)
2. Recherche d'établissements par ville, service et nom
3. Réservation d'un créneau avec confirmation immédiate et référence (`RDV-XXXXXX`)
4. Espace patient : rendez-vous à venir, historique, annulation

## Prérequis
- PHP 8.1 ou plus, avec les extensions `pdo_sqlite` et `sqlite3` (`php -m | grep -i sqlite`)
- Optionnel : Node.js 18+ pour le linter JavaScript (ESLint)

## Installation et lancement
```bash
git clone <url-du-depot> rdv-medical-ci
cd rdv-medical-ci
cp .env.example .env          # configuration (valeurs par défaut utilisables telles quelles)
php database/init.php         # crée la base SQLite, les établissements de démo et les créneaux
php -S localhost:8000 -t public
```
Ouvrir http://localhost:8000.

- Réinitialiser la base : `php database/init.php --reset`
- Régénérer les créneaux des 14 prochains jours (à relancer de temps en temps) : `php database/init.php`

## Structure du projet
```
rdv-medical-ci/
├── public/              # seul dossier exposé par le serveur web
│   ├── *.html           # pages (accueil, établissement, connexion, inscription, mes rendez-vous, confirmation)
│   ├── css/style.css    # charte graphique (bleu #1261A0, turquoise #35B7A4)
│   ├── js/              # modules JavaScript (app.js = fonctions communes, un fichier par page)
│   └── api/             # points d'entrée de l'API JSON (PHP)
├── src/bootstrap.php    # configuration, connexion PDO, sessions, helpers
├── database/            # schema.sql, seed.sql, init.php (le fichier .sqlite n'est pas versionné)
├── tests/smoke.sh       # tests de bout en bout de l'API
├── docs/                # documentation (API, onboarding)
├── .env.example         # variables d'environnement
├── eslint.config.js, .editorconfig, package.json
└── README.md
```

## API (JSON)
| Méthode | Route | Rôle | Connexion |
|---|---|---|---|
| POST | `api/register.php` | Créer un compte | non |
| POST | `api/login.php` / `api/logout.php` | Connexion / déconnexion | non |
| GET | `api/me.php` | Patient connecté (ou `null`) | non |
| GET | `api/filtres.php` | Villes et services pour les filtres | non |
| GET | `api/etablissements.php?ville=&service=&q=` | Recherche | non |
| GET | `api/etablissement.php?id=` | Fiche et services | non |
| GET | `api/creneaux.php?service_id=&date=` | Dates et créneaux libres | non |
| POST | `api/reserver.php` | Réserver (`creneau_id`, `motif`) | oui |
| GET | `api/mes_rdv.php[?reference=]` | Mes rendez-vous | oui |
| POST | `api/annuler.php` | Annuler (`rdv_id`) | oui |

## Qualité et sécurité
- Mots de passe hachés (`password_hash`), requêtes SQL préparées (PDO), sessions HttpOnly + SameSite
- Les POST exigent `Content-Type: application/json` (protection CSRF de base)
- Affichage via `textContent` (pas d'injection HTML) ; collecte minimale : nom, téléphone, motif
- Double réservation impossible : index unique partiel sur `rendez_vous(creneau_id) WHERE statut = 'confirme'`

## Tests et linter
```bash
npm test            # ou : bash tests/smoke.sh  (26 vérifications, nécessite php, curl, jq)
npm install         # une fois, pour ESLint
npm run lint        # ESLint (JS) + php -l (syntaxe PHP)
```

## Décisions d'architecture
ADR-001 (PHP natif + front natif) et ADR-002 (SQLite) : voir le document ADR du projet.
