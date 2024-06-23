-- Création de la table Utilisateur
CREATE TABLE Utilisateur (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255),
    prenom VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    mot_de_passe VARCHAR(255),
    numero_telephone VARCHAR(15),
    role ENUM('patient', 'medecin', 'admin') NOT NULL
);

-- Création de la table Patient
CREATE TABLE Patient (
    user_id INT PRIMARY KEY,
    adresse VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES Utilisateur(id)
);

-- Création de la table Medecin
CREATE TABLE Medecin (
    user_id INT PRIMARY KEY,
    specialite VARCHAR(100) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Utilisateur(id)
);

-- Création de la table RendezVous avec contrainte unique
CREATE TABLE RendezVous (
    rendezvous_id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE,
    heure TIME,
    patient_id INT,
    medecin_id INT,
    FOREIGN KEY (patient_id) REFERENCES Patient(user_id),
    FOREIGN KEY (medecin_id) REFERENCES Medecin(user_id),
    UNIQUE (date, heure, medecin_id)
);

-- Ajout des entrées dans la table Utilisateur
INSERT INTO Utilisateur (nom, prenom, email, mot_de_passe, numero_telephone, role) VALUES
('Admin', 'admin', 'admin@admin.com', 'Password123', '0123456789', 'admin'),
('Patient', 'User', 'patient@patient.com', 'Patient123', '0987654321', 'patient'),
('Dr. Benali', 'Cardiologue', 'dr.benali@gmail.com', 'Azert1234', '0123456789', 'medecin'),
('Dr. Ahmadi', 'Genéraliste', 'dr.ahmadi@gmail.com', 'Azerty1234', '0987654321', 'medecin'),
('Dr. Soufiane', 'AALLA', 'soufiane.aalla@gmail.com', 'Azerty1234', '0147258369', 'medecin');

-- Ajout des entrées dans la table Patient
INSERT INTO Patient (user_id, adresse) VALUES
((SELECT id FROM Utilisateur WHERE email = 'patient@patient.com'), '123 Rue de la Santé, Ville');

-- Ajout des entrées dans la table Medecin
INSERT INTO Medecin (user_id, specialite, adresse) VALUES
((SELECT id FROM Utilisateur WHERE email = 'dr.benali@gmail.com'), 'Cardiologue', '123 Rue Hassan 2, Beni Mellal'),
((SELECT id FROM Utilisateur WHERE email = 'dr.ahmadi@gmail.com'), 'Généraliste', '456 Avenue Mohamed 5, Rabat'),
((SELECT id FROM Utilisateur WHERE email = 'soufiane.aalla@gmail.com'), 'Dermatologue', '789 Boulevard des Hôpitaux, Mrirt');

-- Ajout des entrées dans la table RendezVous
INSERT INTO RendezVous (date, heure, patient_id, medecin_id) VALUES
('2024-06-10', '10:00:00', (SELECT user_id FROM Patient WHERE user_id = 
(SELECT id FROM Utilisateur WHERE email = 'patient@patient.com')), 
(SELECT user_id FROM Medecin WHERE user_id = (SELECT id FROM Utilisateur WHERE email = 'dr.benali@gmail.com')));

ALTER TABLE RendezVous
ADD COLUMN etat ENUM('non confirme', 'confirme') DEFAULT 'non confirme';
