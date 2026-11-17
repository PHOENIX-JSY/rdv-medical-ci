PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS patients (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  nom          TEXT NOT NULL,
  telephone    TEXT NOT NULL UNIQUE,
  mot_de_passe TEXT NOT NULL,
  created_at   TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS etablissements (
  id        INTEGER PRIMARY KEY AUTOINCREMENT,
  nom       TEXT NOT NULL,
  ville     TEXT NOT NULL,
  commune   TEXT,
  adresse   TEXT NOT NULL,
  telephone TEXT,
  infos     TEXT
);

CREATE TABLE IF NOT EXISTS services (
  id               INTEGER PRIMARY KEY AUTOINCREMENT,
  etablissement_id INTEGER NOT NULL REFERENCES etablissements(id) ON DELETE CASCADE,
  nom              TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS creneaux (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  service_id INTEGER NOT NULL REFERENCES services(id) ON DELETE CASCADE,
  debut      TEXT NOT NULL,            -- format 'YYYY-MM-DD HH:MM'
  UNIQUE (service_id, debut)
);

CREATE TABLE IF NOT EXISTS rendez_vous (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  reference  TEXT NOT NULL UNIQUE,
  patient_id INTEGER NOT NULL REFERENCES patients(id) ON DELETE CASCADE,
  creneau_id INTEGER NOT NULL REFERENCES creneaux(id),
  motif      TEXT NOT NULL,
  statut     TEXT NOT NULL DEFAULT 'confirme' CHECK (statut IN ('confirme', 'annule')),
  created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

-- Un créneau ne peut avoir qu'un seul rendez-vous confirmé (anti double réservation)
CREATE UNIQUE INDEX IF NOT EXISTS idx_rdv_creneau_confirme
  ON rendez_vous (creneau_id) WHERE statut = 'confirme';
CREATE INDEX IF NOT EXISTS idx_creneaux_service_debut ON creneaux (service_id, debut);
