-- Création des tables pour le modèle conceptuel de données

-- Table : Personne
CREATE TABLE Personne (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mot_de_passe VARCHAR(150) NOT NULL,
    medecin BOOLEAN NOT NULL,
    telephone VARCHAR(14) NOT NULL --les 10 chiffres et les points
);

-- Table : Etablissement
CREATE TABLE Etablissement (
    id SERIAL PRIMARY KEY,
    adresse CHAR(50) NOT NULL
);


-- Table : Medecin
CREATE TABLE Medecin (
    id SERIAL PRIMARY KEY,
    code_postal INT NOT NULL,
    id_personne INT NOT NULL,
    FOREIGN KEY (id_personne) REFERENCES Personne(id)
);

-- Table : Specialite
CREATE TABLE Specialite (
    id SERIAL PRIMARY KEY,
    nom_specialite VARCHAR(50) NOT NULL
);


-- Table : rendez vous
CREATE TABLE Rdv (
    id SERIAL PRIMARY KEY,
    heure TIME NOT NULL,
    duree TIME NOT NULL,
    date DATE NOT NULL,
    id_personne INT,
    id_medecin INT NOT NULL,
    id_etablissement INT NOT NULL,
    FOREIGN KEY (id_personne) REFERENCES Personne(id),
    FOREIGN KEY (id_medecin) REFERENCES Medecin(id),
    FOREIGN KEY (id_etablissement) REFERENCES Etablissement(id)
);


-- Relation : possede (entre Medecin et Specialite)
CREATE TABLE possede (
    id_medecin INT NOT NULL,
    id_specialite INT NOT NULL,
    PRIMARY KEY (id_medecin, id_specialite),
    FOREIGN KEY (id_medecin) REFERENCES Medecin(id),
    FOREIGN KEY (id_specialite) REFERENCES Specialite(id)
);