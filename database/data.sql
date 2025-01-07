-- Activer l'extension pgcrypto pour le hachage des mots de passe

CREATE EXTENSION IF NOT EXISTS pgcrypto;


--Insertion de 2 patients dans la bdd

INSERT INTO personne (nom, prenom, email, mot_de_passe, medecin, telephone) 
VALUES ('Lefevre', 'John', 'lefevre@mail.com', crypt('Password123', gen_salt('bf')), 'f', '07.11.11.11.11'),
('Maisonneuve', 'Amelie', 'amelie@mail.com', crypt('Password456', gen_salt('bf')), 'f', '07.22.22.22.22');


--Insertion de 2 médecins dans la bdd

INSERT INTO personne (nom, prenom, email, mot_de_passe, medecin, telephone) 
VALUES ('Roux', 'Alice', 'alice.roux@mail.com', crypt('123Password', gen_salt('bf')), 't', '07.33.33.33.33'),
('Maboul', 'Jim', 'maboul@mail.com', crypt('456Password', gen_salt('bf')),'t', '07.44.44.44.44');

INSERT INTO medecin (code_postal, id_personne)
SELECT 44466, id
FROM personne WHERE email = 'alice.roux@mail.com'
UNION ALL
SELECT 55577, id
FROM personne
WHERE email = 'maboul@mail.com';


--Insertion de 5 établissements dans la base de données

INSERT INTO Etablissement (adresse)
VALUES ('Hopital Saint Clair'),
('Clinique Bellevue'),
('Hopital Chubert'),
('Clinique des Ursulines'),
('Clinique Océane');


--Insertion de 2 spécialités

INSERT INTO Specialite(nom_specialite)
VALUES ('Dentiste'),('Ophtalmo');


--Ajout du lien entre les médecins rentrés et leur spécialités

INSERT INTO possede (id_medecin, id_specialite)
SELECT m.id, s.id
FROM medecin m
JOIN personne p ON m.id_personne = p.id
JOIN specialite s 
ON p.email = 'alice.roux@mail.com' AND s.nom_specialite = 'Dentiste'

UNION ALL

SELECT m.id, s.id
FROM medecin m
JOIN personne p ON m.id_personne = p.id
JOIN specialite s 
ON p.email = 'maboul@mail.com' AND s.nom_specialite = 'Ophtalmo';


--Insertion de quelques RDVs

INSERT INTO Rdv (heure, duree, date, id_personne, id_medecin, id_etablissement)

-- Premier RDV
SELECT TIME '10:00:00', TIME '00:45:00', DATE '2025-01-10', p.id, m.id, e1.id
FROM personne p
JOIN medecin m ON m.id_personne = (SELECT id FROM personne WHERE email = 'maboul@mail.com')
JOIN etablissement e1 ON e1.adresse = 'Clinique Bellevue'
WHERE p.email = 'lefevre@mail.com'

UNION ALL

-- Deuxième RDV
SELECT TIME '14:00:00', TIME '00:30:00', DATE '2025-01-11', p.id, m.id, e2.id
FROM personne p
JOIN medecin m ON m.id_personne = (SELECT id FROM personne WHERE email = 'maboul@mail.com')
JOIN etablissement e2 ON e2.adresse = 'Hopital Chubert'
WHERE p.email = 'amelie@mail.com'

UNION ALL

-- Troisième RDV
SELECT TIME '16:00:00', TIME '01:00:00',  DATE '2025-01-07', p.id, m.id, e2.id
FROM personne p
JOIN medecin m ON m.id_personne = (SELECT id FROM personne WHERE email = 'alice.roux@mail.com')
JOIN etablissement e2 ON e2.adresse = 'Clinique Océane'
WHERE p.email = 'amelie@mail.com';
