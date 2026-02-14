CREATE TABLE users
(
	id_user INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nom VARCHAR(255),
    prenom VARCHAR(255),
    depot VARCHAR(255),
    is_admin_n1 BOOLEAN DEFAULT 0,
    is_admin_n2 BOOLEAN DEFAULT 0
);

CREATE TABLE type_materiel (
    id_type_materiel INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    quantite_dispo INT NOT NULL
);


CREATE TABLE marque_materiel (
    id_marque_materiel INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nom VARCHAR(255) NOT NULL
);


CREATE TABLE materiel_dispo (
    id_materiel_dispo INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_type_materiel INT NOT NULL,
    id_marque_materiel INT NOT NULL,
    modele VARCHAR(255),
    serial_number VARCHAR(255) NOT NULL UNIQUE,
    nom VARCHAR(255) NOT NULL
);

CREATE TABLE materiel_affecté (
    id_materiel_affecté INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_type_materiel INT NOT NULL,
    id_marque_materiel INT NOT NULL,
    modele VARCHAR(255),
    serial_number VARCHAR(255) NOT NULL UNIQUE,
    nom VARCHAR(255) NOT NULL,
    id_user INT
);