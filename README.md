# 📊 Dashboard Matériel

Dashboard de gestion de matériel avec connexion MySQL - Interface moderne et responsive pour suivre votre inventaire en temps réel.

## 🚀 Fonctionnalités

- **Statistiques en temps réel** : Total matériel, disponible, affecté, utilisateurs, types
- **Gestion des utilisateurs** : Suivi des affectations de matériel
- **Derniers matériels** : Affichage des derniers matériels disponibles et affectés
- **Répartition par type** : Graphique en barres par type de matériel
- **Répartition par marque** : Graphique en barres par marque
- **Design moderne** : Interface responsive avec dégradés et animations
- **Numéros de série** : Traçabilité complète avec serial numbers

## ⚙️ Installation

### Prérequis
- Serveur web (Apache/Nginx)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Laragon, XAMPP, WAMP ou équivalent

### Étape 1 : Configuration de la base de données

1. Ouvrez **phpMyAdmin** : http://localhost/phpmyadmin
2. Cliquez sur l'onglet **"SQL"**
3. Copiez et collez le contenu du fichier `setup_database.sql` pour créer les tables
4. Exécutez ensuite le fichier `insert_sample_data.sql` pour ajouter des données d'exemple
5. Cliquez sur **"Exécuter"**

**OU** via la ligne de commande :
```bash
mysql -u root -p@Dmin_password db_gestion_materiel < setup_database.sql
mysql -u root -p@Dmin_password db_gestion_materiel < insert_sample_data.sql
```

### Étape 2 : Configuration de la connexion

Éditez les paramètres de connexion dans `index.php` (lignes 7-10) :

```php
$db_host = 'localhost';          // Hôte de la base de données
$db_name = 'db_gestion_materiel'; // Nom de la base de données
$db_user = 'root';                // Utilisateur MySQL
$db_pass = '@Dmin_password';      // Mot de passe MySQL
```

### Étape 3 : Accès au dashboard

Ouvrez votre navigateur et accédez à :
```
http://localhost/materiel/
```

## 📁 Structure de la base de données

### Table : `users`
Gestion des utilisateurs et des droits d'administration

| Colonne      | Type         | Description                    |
|--------------|--------------|--------------------------------|
| id_user      | INT          | Identifiant unique             |
| nom          | VARCHAR(255) | Nom de l'utilisateur           |
| prenom       | VARCHAR(255) | Prénom de l'utilisateur        |
| depot        | VARCHAR(255) | Dépôt/Site de l'utilisateur    |
| is_admin_n1  | BOOLEAN      | Administrateur niveau 1        |
| is_admin_n2  | BOOLEAN      | Administrateur niveau 2        |

### Table : `type_materiel`
Types de matériel disponibles

| Colonne          | Type         | Description                    |
|------------------|--------------|--------------------------------|
| id_type_materiel | INT          | Identifiant unique             |
| nom              | VARCHAR(255) | Nom du type de matériel        |
| quantite_dispo   | INT          | Quantité disponible            |

### Table : `marque_materiel`
Marques de matériel

| Colonne            | Type         | Description                    |
|--------------------|--------------|--------------------------------|
| id_marque_materiel | INT          | Identifiant unique             |
| nom                | VARCHAR(255) | Nom de la marque               |

### Table : `materiel_dispo`
Matériel disponible (non affecté)

| Colonne            | Type         | Description                    |
|--------------------|--------------|--------------------------------|
| id_materiel_dispo  | INT          | Identifiant unique             |
| id_type_materiel   | INT          | Référence au type              |
| id_marque_materiel | INT          | Référence à la marque          |
| modele             | VARCHAR(255) | Modèle du matériel             |
| serial_number      | VARCHAR(255) | Numéro de série (unique)       |
| nom                | VARCHAR(255) | Nom complet du matériel        |

### Table : `materiel_affecté`
Matériel affecté à des utilisateurs

| Colonne              | Type         | Description                    |
|----------------------|--------------|--------------------------------|
| id_materiel_affecté  | INT          | Identifiant unique             |
| id_type_materiel     | INT          | Référence au type              |
| id_marque_materiel   | INT          | Référence à la marque          |
| modele               | VARCHAR(255) | Modèle du matériel             |
| serial_number        | VARCHAR(255) | Numéro de série (unique)       |
| nom                  | VARCHAR(255) | Nom complet du matériel        |
| id_user              | INT          | Référence à l'utilisateur      |

## 🎨 Personnalisation

### Modifier les couleurs du dégradé
Dans `index.php`, ligne 90 :
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Ajouter des types et marques
Les types et marques sont gérés via les tables `type_materiel` et `marque_materiel`.
Ajoutez-les d'abord avant d'ajouter du matériel.

### Modifier le nombre de matériels affichés
Ligne 61 dans `index.php` (pour les matériels disponibles) :
```php
$stmt = $pdo->query("... LIMIT 5");
```
Changez `LIMIT 5` pour afficher plus ou moins d'éléments.

## 🔧 Gestion des données

### Ajouter un utilisateur

Via phpMyAdmin ou MySQL :
```sql
INSERT INTO users (nom, prenom, depot, is_admin_n1, is_admin_n2) VALUES
('Nouveau', 'Utilisateur', 'Paris', 0, 0);
```

### Ajouter un type de matériel
```sql
INSERT INTO type_materiel (nom, quantite_dispo) VALUES
('Nouveau Type', 10);
```

### Ajouter une marque
```sql
INSERT INTO marque_materiel (nom) VALUES
('Nouvelle Marque');
```

### Ajouter du matériel disponible
```sql
INSERT INTO materiel_dispo (id_type_materiel, id_marque_materiel, modele, serial_number, nom) VALUES
(1, 1, 'Modèle XYZ', 'SN123456', 'Nom complet du matériel');
```

### Affecter du matériel à un utilisateur

1. D'abord, trouvez l'ID de l'utilisateur :
```sql
SELECT id_user, nom, prenom FROM users;
```

2. Déplacez le matériel de `materiel_dispo` vers `materiel_affecté` :
```sql
-- Insérer dans materiel_affecté
INSERT INTO materiel_affecté (id_type_materiel, id_marque_materiel, modele, serial_number, nom, id_user)
SELECT id_type_materiel, id_marque_materiel, modele, serial_number, nom, 1 -- ID de l'utilisateur
FROM materiel_dispo
WHERE id_materiel_dispo = 1; -- ID du matériel à affecter

-- Supprimer de materiel_dispo
DELETE FROM materiel_dispo WHERE id_materiel_dispo = 1;
```

### Libérer du matériel (retour en disponible)
```sql
-- Insérer dans materiel_dispo
INSERT INTO materiel_dispo (id_type_materiel, id_marque_materiel, modele, serial_number, nom)
SELECT id_type_materiel, id_marque_materiel, modele, serial_number, nom
FROM materiel_affecté
WHERE id_materiel_affecté = 1; -- ID du matériel à libérer

-- Supprimer de materiel_affecté
DELETE FROM materiel_affecté WHERE id_materiel_affecté = 1;
```

### Supprimer du matériel
```sql
-- Supprimer du matériel disponible
DELETE FROM materiel_dispo WHERE id_materiel_dispo = 1;

-- Supprimer du matériel affecté
DELETE FROM materiel_affecté WHERE id_materiel_affecté = 1;
```

## 🐛 Dépannage

### Erreur de connexion MySQL
✅ Vérifiez que MySQL est démarré  
✅ Vérifiez les identifiants dans `index.php`  
✅ Vérifiez que la base de données existe  

### Page blanche
✅ Activez l'affichage des erreurs PHP  
✅ Vérifiez les logs Apache/PHP  
✅ Vérifiez la syntaxe PHP  

### Aucune donnée affichée
✅ Vérifiez que la table `materiel` contient des données  
✅ Exécutez le script `setup_database.sql`  
✅ Vérifiez les requêtes SQL dans le code  

## 📈 Évolutions possibles

- [ ] Formulaire d'ajout de matériel via l'interface
- [ ] Formulaire d'affectation/libération de matériel
- [ ] Système d'authentification des utilisateurs
- [ ] Historique des affectations
- [ ] Export en PDF/Excel des inventaires
- [ ] Notifications pour les retours de matériel
- [ ] Recherche et filtres avancés (par type, marque, utilisateur)
- [ ] Upload de photos/documents pour chaque matériel
- [ ] Gestion des garanties et maintenances
- [ ] Dashboard multi-dépôts
- [ ] API REST pour intégrations externes
- [ ] Scan de codes-barres pour les serial numbers
- [ ] Alertes de stock bas par type de matériel
- [ ] Signature électronique lors de l'affectation

## 📝 Licence

Projet libre d'utilisation pour un usage personnel ou commercial.

## 👤 Support

Pour toute question ou problème, vérifiez d'abord la section Dépannage ci-dessus.

---

**Dernière mise à jour** : Février 2026  
**Version** : 1.0.0
