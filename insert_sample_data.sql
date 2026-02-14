-- Données d'exemple pour tester le Dashboard Matériel
-- Exécutez ce script après avoir créé les tables avec setup_database.sql

USE gestion_materiel;

-- Insertion d'utilisateurs
INSERT INTO users (nom, prenom, depot, is_admin_n1, is_admin_n2) VALUES
('Dupont', 'Jean', 'Paris', 0, 0),
('Martin', 'Sophie', 'Lyon', 1, 0),
('Bernard', 'Pierre', 'Marseille', 0, 0),
('Dubois', 'Marie', 'Paris', 1, 1),
('Moreau', 'Luc', 'Toulouse', 0, 0),
('Laurent', 'Emma', 'Nice', 0, 0),
('Simon', 'Thomas', 'Lyon', 0, 0);

-- Insertion des types de matériel
INSERT INTO type_materiel (nom, quantite_dispo) VALUES
('Ordinateur Portable', 15),
('Écran', 25),
('Clavier', 30),
('Souris', 35),
('Webcam', 12),
('Casque Audio', 20),
('Imprimante', 8),
('Scanner', 5),
('Tablette', 10),
('Smartphone', 18),
('Routeur', 6),
('Switch', 4);

-- Insertion des marques
INSERT INTO marque_materiel (nom) VALUES
('Dell'),
('HP'),
('Lenovo'),
('Apple'),
('Samsung'),
('Logitech'),
('Microsoft'),
('Asus'),
('Acer'),
('Canon'),
('Epson'),
('TP-Link'),
('Cisco');

-- Insertion de matériel disponible
INSERT INTO materiel_dispo (id_type_materiel, id_marque_materiel, modele, serial_number, nom) VALUES
(1, 1, 'Latitude 5520', 'SN001DEL2024', 'Dell Latitude 5520 - i5-11500H'),
(1, 2, 'EliteBook 840 G8', 'SN002HP2024', 'HP EliteBook 840 G8 - i7-1165G7'),
(1, 3, 'ThinkPad X1', 'SN003LEN2024', 'Lenovo ThinkPad X1 Carbon Gen 9'),
(2, 1, 'P2422H', 'SN004DEL2024', 'Dell P2422H 24" Full HD'),
(2, 5, 'S27A600', 'SN005SAM2024', 'Samsung 27" 4K Professional'),
(2, 8, 'VG279Q', 'SN006ASU2024', 'Asus VG279Q 27" Gaming'),
(3, 6, 'MX Keys', 'SN007LOG2024', 'Logitech MX Keys Wireless'),
(3, 7, 'Surface Keyboard', 'SN008MIC2024', 'Microsoft Surface Keyboard'),
(4, 6, 'MX Master 3', 'SN009LOG2024', 'Logitech MX Master 3 Souris'),
(4, 6, 'MX Anywhere 3', 'SN010LOG2024', 'Logitech MX Anywhere 3'),
(5, 6, 'C920 HD Pro', 'SN011LOG2024', 'Logitech C920 HD Pro Webcam'),
(5, 6, 'Brio 4K', 'SN012LOG2024', 'Logitech Brio 4K Ultra HD'),
(6, 6, 'Zone Wireless', 'SN013LOG2024', 'Logitech Zone Wireless Headset'),
(6, 7, 'Surface Headphones', 'SN014MIC2024', 'Microsoft Surface Headphones 2'),
(7, 2, 'LaserJet Pro M404', 'SN015HP2024', 'HP LaserJet Pro M404dn'),
(7, 10, 'i-SENSYS LBP623', 'SN016CAN2024', 'Canon i-SENSYS LBP623Cdw'),
(8, 10, 'CanoScan LiDE 400', 'SN017CAN2024', 'Canon CanoScan LiDE 400'),
(9, 4, 'iPad Pro 11"', 'SN018APP2024', 'Apple iPad Pro 11" 256GB'),
(9, 5, 'Galaxy Tab S8', 'SN019SAM2024', 'Samsung Galaxy Tab S8 Ultra'),
(10, 4, 'iPhone 13 Pro', 'SN020APP2024', 'Apple iPhone 13 Pro 256GB'),
(10, 5, 'Galaxy S22', 'SN021SAM2024', 'Samsung Galaxy S22 Ultra'),
(11, 12, 'Archer AX6000', 'SN022TPL2024', 'TP-Link Archer AX6000 WiFi 6'),
(12, 13, 'SG350-28P', 'SN023CIS2024', 'Cisco SG350-28P Switch 28 ports');

-- Insertion de matériel affecté
INSERT INTO materiel_affecté (id_type_materiel, id_marque_materiel, modele, serial_number, nom, id_user) VALUES
(1, 1, 'XPS 15', 'SN101DEL2024', 'Dell XPS 15 - i7-11800H', 1),
(1, 4, 'MacBook Pro 14"', 'SN102APP2024', 'Apple MacBook Pro 14" M1 Pro', 2),
(1, 3, 'ThinkPad P15', 'SN103LEN2024', 'Lenovo ThinkPad P15 Gen 2', 3),
(1, 2, 'ZBook Studio G8', 'SN104HP2024', 'HP ZBook Studio G8', 4),
(2, 1, 'U2720Q', 'SN105DEL2024', 'Dell UltraSharp U2720Q 27" 4K', 1),
(2, 2, 'E243', 'SN106HP2024', 'HP E243 24" Monitor', 5),
(3, 6, 'K780', 'SN107LOG2024', 'Logitech K780 Multi-Device', 1),
(3, 7, 'Ergonomic', 'SN108MIC2024', 'Microsoft Ergonomic Keyboard', 2),
(4, 6, 'MX Master 2S', 'SN109LOG2024', 'Logitech MX Master 2S', 1),
(4, 7, 'Arc Mouse', 'SN110MIC2024', 'Microsoft Arc Mouse', 2),
(5, 6, 'C922 Pro', 'SN111LOG2024', 'Logitech C922 Pro Stream', 3),
(6, 6, 'H800', 'SN112LOG2024', 'Logitech H800 Wireless', 3),
(6, 4, 'AirPods Pro', 'SN113APP2024', 'Apple AirPods Pro', 4),
(9, 4, 'iPad Air', 'SN114APP2024', 'Apple iPad Air 64GB', 5),
(9, 5, 'Galaxy Tab S7', 'SN115SAM2024', 'Samsung Galaxy Tab S7', 6),
(10, 4, 'iPhone 12', 'SN116APP2024', 'Apple iPhone 12 128GB', 7),
(10, 5, 'Galaxy S21', 'SN117SAM2024', 'Samsung Galaxy S21', 2);

-- Affichage des statistiques
SELECT '=== STATISTIQUES ===' as '';
SELECT 
    (SELECT COUNT(*) FROM materiel_dispo) as 'Matériel Disponible',
    (SELECT COUNT(*) FROM materiel_affecté) as 'Matériel Affecté',
    (SELECT COUNT(*) FROM materiel_dispo) + (SELECT COUNT(*) FROM materiel_affecté) as 'Total Matériel',
    (SELECT COUNT(*) FROM users) as 'Utilisateurs',
    (SELECT COUNT(*) FROM type_materiel) as 'Types',
    (SELECT COUNT(*) FROM marque_materiel) as 'Marques';

-- Affichage par type
SELECT '=== RÉPARTITION PAR TYPE ===' as '';
SELECT 
    tm.nom as 'Type',
    (SELECT COUNT(*) FROM materiel_dispo WHERE id_type_materiel = tm.id_type_materiel) as 'Disponible',
    (SELECT COUNT(*) FROM materiel_affecté WHERE id_type_materiel = tm.id_type_materiel) as 'Affecté',
    (SELECT COUNT(*) FROM materiel_dispo WHERE id_type_materiel = tm.id_type_materiel) +
    (SELECT COUNT(*) FROM materiel_affecté WHERE id_type_materiel = tm.id_type_materiel) as 'Total'
FROM type_materiel tm
ORDER BY Total DESC;

-- Affichage par marque
SELECT '=== RÉPARTITION PAR MARQUE ===' as '';
SELECT 
    mm.nom as 'Marque',
    (SELECT COUNT(*) FROM materiel_dispo WHERE id_marque_materiel = mm.id_marque_materiel) as 'Disponible',
    (SELECT COUNT(*) FROM materiel_affecté WHERE id_marque_materiel = mm.id_marque_materiel) as 'Affecté',
    (SELECT COUNT(*) FROM materiel_dispo WHERE id_marque_materiel = mm.id_marque_materiel) +
    (SELECT COUNT(*) FROM materiel_affecté WHERE id_marque_materiel = mm.id_marque_materiel) as 'Total'
FROM marque_materiel mm
ORDER BY Total DESC;

-- Affichage des utilisateurs avec matériel
SELECT '=== UTILISATEURS AVEC MATÉRIEL ===' as '';
SELECT 
    u.nom,
    u.prenom,
    u.depot,
    COUNT(ma.id_materiel_affecté) as 'Nb Matériels',
    CASE 
        WHEN u.is_admin_n2 = 1 THEN 'Admin N2'
        WHEN u.is_admin_n1 = 1 THEN 'Admin N1'
        ELSE 'Utilisateur'
    END as 'Rôle'
FROM users u
LEFT JOIN materiel_affecté ma ON u.id_user = ma.id_user
GROUP BY u.id_user
ORDER BY COUNT(ma.id_materiel_affecté) DESC;
