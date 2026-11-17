-- Données de démonstration (établissements fictifs)
INSERT INTO etablissements (id, nom, ville, commune, adresse, telephone, infos) VALUES
 (1, 'Centre Hospitalier Les Lagunes', 'Abidjan', 'Cocody', 'Boulevard des Martyrs, Cocody', '+22527000001', 'Présentez-vous 15 minutes avant l''heure du rendez-vous avec une pièce d''identité.'),
 (2, 'Clinique Sainte-Espérance', 'Abidjan', 'Yopougon', 'Rue du Marché, Yopougon', '+22527000002', 'Apportez votre carnet de santé et vos anciens résultats d''examen.'),
 (3, 'Hôpital Régional de Bouaké', 'Bouaké', 'Centre', 'Avenue Houphouët-Boigny, Bouaké', '+22531000003', 'Accueil du lundi au vendredi, de 7h30 à 17h.'),
 (4, 'Centre de Santé Akwaba', 'Yamoussoukro', 'Centre', 'Quartier Habitat, Yamoussoukro', '+22530000004', 'Consultations sur rendez-vous uniquement.'),
 (5, 'Clinique Baie du Sud', 'San-Pédro', 'Bardot', 'Route de la Plage, San-Pédro', '+22534000005', 'Présentez-vous avec votre pièce d''identité et votre référence de rendez-vous.');

INSERT INTO services (etablissement_id, nom) VALUES
 (1, 'Médecine générale'), (1, 'Cardiologie'), (1, 'Pédiatrie'), (1, 'Ophtalmologie'),
 (2, 'Médecine générale'), (2, 'Gynécologie'), (2, 'Pédiatrie'),
 (3, 'Médecine générale'), (3, 'Dentaire'), (3, 'Dermatologie'),
 (4, 'Médecine générale'), (4, 'Pédiatrie'),
 (5, 'Médecine générale'), (5, 'Gynécologie'), (5, 'Cardiologie');
