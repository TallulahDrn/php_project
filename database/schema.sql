-- Création des tables pour le modèle conceptuel de données

-- Table : Personne
CREATE TABLE Personne (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom CHAR(50),
    prenom CHAR(50),
    email CHAR(50),
    mot_de_passe CHAR(50),
    medecin BOOLEAN,
    telephone INT
);

-- Table : RDV
CREATE TABLE RDV (
    id INT AUTO_INCREMENT PRIMARY KEY,
    heure TIME,
    duree TIME,
    date DATE
);

-- Table : Etablissement
CREATE TABLE Etablissement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adresse CHAR(50)
);

-- Table : Medecin
CREATE TABLE Medecin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_postal INT
);

-- Table : Specialite
CREATE TABLE Specialite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_specialite CHAR(50)
);

-- Relation : prend (entre Personne et RDV)
CREATE TABLE prend (
    personne_id INT,
    rdv_id INT,
    PRIMARY KEY (personne_id, rdv_id),
    FOREIGN KEY (personne_id) REFERENCES Personne(id),
    FOREIGN KEY (rdv_id) REFERENCES RDV(id)
);

-- Relation : donne (entre Medecin et RDV)
CREATE TABLE donne (
    medecin_id INT,
    rdv_id INT,
    PRIMARY KEY (medecin_id, rdv_id),
    FOREIGN KEY (medecin_id) REFERENCES Medecin(id),
    FOREIGN KEY (rdv_id) REFERENCES RDV(id)
);

-- Relation : situe (entre RDV et Etablissement)
CREATE TABLE situe (
    rdv_id INT,
    etablissement_id INT,
    PRIMARY KEY (rdv_id, etablissement_id),
    FOREIGN KEY (rdv_id) REFERENCES RDV(id),
    FOREIGN KEY (etablissement_id) REFERENCES Etablissement(id)
);

-- Relation : appartient (entre Medecin et Personne)
CREATE TABLE appartient (
    medecin_id INT,
    personne_id INT,
    PRIMARY KEY (medecin_id, personne_id),
    FOREIGN KEY (medecin_id) REFERENCES Medecin(id),
    FOREIGN KEY (personne_id) REFERENCES Personne(id)
);

-- Relation : possede (entre Medecin et Specialite)
CREATE TABLE possede (
    medecin_id INT,
    specialite_id INT,
    PRIMARY KEY (medecin_id, specialite_id),
    FOREIGN KEY (medecin_id) REFERENCES Medecin(id),
    FOREIGN KEY (specialite_id) REFERENCES Specialite(id)
);
