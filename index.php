<?php
/**
 * DASHBOARD MATÉRIEL
 * Tableau de bord pour la gestion du matériel
 */

// Configuration de la base de données
$db_host = 'localhost';
$db_name = 'gestion_materiel';
$db_user = 'root';
$db_pass = '@Dmin_password';

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion_ok = true;
} catch(PDOException $e) {
    $connexion_ok = false;
    $erreur_connexion = $e->getMessage();
}

// Récupération des statistiques
$stats = [
    'total_materiel' => 0,
    'materiel_disponible' => 0,
    'materiel_affecte' => 0,
    'total_users' => 0,
    'total_types' => 0
];

$derniers_materiels_dispo = [];
$derniers_materiels_affecte = [];
$materiels_par_type = [];
$materiels_par_marque = [];

if ($connexion_ok) {
    try {
        // Total du matériel disponible
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM materiel_dispo");
        $stats['materiel_disponible'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Total du matériel affecté
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM materiel_affecté");
        $stats['materiel_affecte'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Total du matériel
        $stats['total_materiel'] = $stats['materiel_disponible'] + $stats['materiel_affecte'];
        
        // Nombre d'utilisateurs
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Nombre de types de matériel
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM type_materiel");
        $stats['total_types'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Derniers matériels disponibles ajoutés
        $stmt = $pdo->query("
            SELECT md.*, tm.nom as type_nom, mm.nom as marque_nom
            FROM materiel_dispo md
            LEFT JOIN type_materiel tm ON md.id_type_materiel = tm.id_type_materiel
            LEFT JOIN marque_materiel mm ON md.id_marque_materiel = mm.id_marque_materiel
            ORDER BY md.id_materiel_dispo DESC
            LIMIT 5
        ");
        $derniers_materiels_dispo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Derniers matériels affectés
        $stmt = $pdo->query("
            SELECT ma.*, tm.nom as type_nom, mm.nom as marque_nom, 
                   u.nom as user_nom, u.prenom as user_prenom
            FROM materiel_affecté ma
            LEFT JOIN type_materiel tm ON ma.id_type_materiel = tm.id_type_materiel
            LEFT JOIN marque_materiel mm ON ma.id_marque_materiel = mm.id_marque_materiel
            LEFT JOIN users u ON ma.id_user = u.id_user
            ORDER BY ma.id_materiel_affecté DESC
            LIMIT 5
        ");
        $derniers_materiels_affecte = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Matériels par type (disponible + affecté)
        $stmt = $pdo->query("
            SELECT MIN(tm.id_type_materiel) as id_type_materiel, tm.nom as type_nom, 
                   (SELECT COUNT(*) FROM materiel_dispo WHERE id_type_materiel = MIN(tm.id_type_materiel)) +
                   (SELECT COUNT(*) FROM materiel_affecté WHERE id_type_materiel = MIN(tm.id_type_materiel)) as nombre
            FROM type_materiel tm
            GROUP BY tm.nom
            ORDER BY nombre DESC
        ");
        $materiels_par_type = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Matériels par marque (disponible + affecté)
        $stmt = $pdo->query("
            SELECT MIN(mm.id_marque_materiel) as id_marque_materiel, mm.nom as marque_nom,
                   (SELECT COUNT(*) FROM materiel_dispo WHERE id_marque_materiel = MIN(mm.id_marque_materiel)) +
                   (SELECT COUNT(*) FROM materiel_affecté WHERE id_marque_materiel = MIN(mm.id_marque_materiel)) as nombre
            FROM marque_materiel mm
            GROUP BY mm.nom
            ORDER BY nombre DESC
        ");
        $materiels_par_marque = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        $erreur_requete = $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Matériel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }
        
        .header-content h1 {
            color: #2d3748;
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .header-content p {
            color: #718096;
            font-size: 1em;
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
        }
        
        .btn-action {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1em;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
        }
        
        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
        }
        
        .btn-action:active {
            transform: translateY(-1px);
        }
        
        .btn-action.secondary {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            box-shadow: 0 4px 15px rgba(237, 137, 54, 0.3);
        }
        
        .btn-action.secondary:hover {
            box-shadow: 0 8px 25px rgba(237, 137, 54, 0.4);
        }
        
        .alert {
            background: #fed7d7;
            border-left: 4px solid #f56565;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #742a2a;
        }
        
        .alert.info {
            background: #bee3f8;
            border-left-color: #3182ce;
            color: #2c5282;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stat-card.blue .icon { background: #ebf8ff; color: #3182ce; }
        .stat-card.green .icon { background: #f0fff4; color: #38a169; }
        .stat-card.red .icon { background: #fff5f5; color: #e53e3e; }
        .stat-card.orange .icon { background: #fffaf0; color: #dd6b20; }
        .stat-card.purple .icon { background: #faf5ff; color: #805ad5; }
        
        .stat-card h3 {
            color: #718096;
            font-size: 0.9em;
            font-weight: 500;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .value {
            color: #2d3748;
            font-size: 2em;
            font-weight: 700;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .card h2 {
            color: #2d3748;
            font-size: 1.3em;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: #f7fafc;
            color: #4a5568;
            font-weight: 600;
            text-align: left;
            padding: 12px;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
        }
        
        table tr:hover {
            background: #f7fafc;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
        }
        
        .badge.disponible { background: #c6f6d5; color: #22543d; }
        .badge.panne { background: #fed7d7; color: #742a2a; }
        .badge.maintenance { background: #feebc8; color: #7c2d12; }
        
        .chart-bar {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .chart-bar .label {
            width: 120px;
            font-size: 0.9em;
            color: #4a5568;
            font-weight: 500;
        }
        
        .chart-bar .bar-container {
            flex: 1;
            height: 30px;
            background: #e2e8f0;
            border-radius: 15px;
            overflow: hidden;
            margin: 0 10px;
        }
        
        .chart-bar .bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            transition: width 0.5s ease;
        }
        
        .chart-bar .value {
            width: 50px;
            text-align: right;
            font-weight: 600;
            color: #2d3748;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #a0aec0;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .header-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <h1>📊 Dashboard Matériel</h1>
                <p>Gestion et suivi du matériel en temps réel</p>
            </div>
            <div class="header-actions">
                <a href="arrivage.php" class="btn-action">
                    <span>📦</span>
                    <span>Nouvel Arrivage</span>
                </a>
                <a href="config.php" class="btn-action secondary">
                    <span>⚙️</span>
                    <span>Configuration</span>
                </a>
            </div>
        </div>
        
        <?php if (!$connexion_ok): ?>
            <div class="alert">
                <strong>⚠️ Erreur de connexion à la base de données</strong><br>
                <?= htmlspecialchars($erreur_connexion ?? 'Erreur inconnue') ?><br><br>
                <strong>Instructions :</strong><br>
                1. Vérifiez que MySQL est démarré<br>
                2. Vérifiez les paramètres de connexion dans index.php<br>
                3. Exécutez le fichier setup_database.sql dans phpMyAdmin ou via la ligne de commande<br>
            </div>
        <?php elseif ($stats['total_materiel'] == 0): ?>
            <div class="alert info">
                <strong>ℹ️ Aucun matériel dans la base de données</strong><br>
                La connexion à la base de données fonctionne, mais aucun matériel n'a été ajouté.<br>
                Ajoutez des données pour voir les statistiques s'afficher.
            </div>
        <?php endif; ?>
        
        <!-- Statistiques -->
        <?php if ($connexion_ok && $stats['total_materiel'] > 0): ?>
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="icon">📦</div>
                    <h3>Total Matériel</h3>
                    <div class="value"><?= number_format($stats['total_materiel']) ?></div>
                </div>
                
                <div class="stat-card green">
                    <div class="icon">✅</div>
                    <h3>Disponible</h3>
                    <div class="value"><?= number_format($stats['materiel_disponible']) ?></div>
                </div>
                
                <div class="stat-card orange">
                    <div class="icon">👤</div>
                    <h3>Affecté</h3>
                    <div class="value"><?= number_format($stats['materiel_affecte']) ?></div>
                </div>
                
                <div class="stat-card purple">
                    <div class="icon">👥</div>
                    <h3>Utilisateurs</h3>
                    <div class="value"><?= number_format($stats['total_users']) ?></div>
                </div>
                
                <div class="stat-card red">
                    <div class="icon">🏷️</div>
                    <h3>Types de Matériel</h3>
                    <div class="value"><?= number_format($stats['total_types']) ?></div>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div class="content-grid">
                <!-- Derniers matériels disponibles -->
                <div class="card">
                    <h2>✅ Derniers Matériels Disponibles</h2>
                    <?php if (count($derniers_materiels_dispo) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Marque</th>
                                        <th>Modèle</th>
                                        <th>SN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($derniers_materiels_dispo as $materiel): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($materiel['nom']) ?></td>
                                            <td><?= htmlspecialchars($materiel['type_nom']) ?></td>
                                            <td><?= htmlspecialchars($materiel['marque_nom']) ?></td>
                                            <td><?= htmlspecialchars($materiel['modele'] ?? '-') ?></td>
                                            <td><small><?= htmlspecialchars(substr($materiel['serial_number'], 0, 10)) ?>...</small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">Aucun matériel disponible</div>
                    <?php endif; ?>
                </div>
                
                <!-- Derniers matériels affectés -->
                <div class="card">
                    <h2>👤 Derniers Matériels Affectés</h2>
                    <?php if (count($derniers_materiels_affecte) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Marque</th>
                                        <th>Utilisateur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($derniers_materiels_affecte as $materiel): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($materiel['nom']) ?></td>
                                            <td><?= htmlspecialchars($materiel['type_nom']) ?></td>
                                            <td><?= htmlspecialchars($materiel['marque_nom']) ?></td>
                                            <td><?= htmlspecialchars(($materiel['user_prenom'] ?? '') . ' ' . ($materiel['user_nom'] ?? '')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">Aucun matériel affecté</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Graphiques -->
            <div class="content-grid" style="margin-top: 20px;">
                <!-- Matériel par type -->
                <div class="card">
                    <h2>🏷️ Répartition par Type</h2>
                    <?php if (count($materiels_par_type) > 0): ?>
                        <?php 
                        $max_nombre = max(array_column($materiels_par_type, 'nombre'));
                        foreach ($materiels_par_type as $item): 
                            if ($item['nombre'] > 0):
                                $pourcentage = ($item['nombre'] / $max_nombre) * 100;
                        ?>
                            <div class="chart-bar">
                                <div class="label"><?= htmlspecialchars($item['type_nom']) ?></div>
                                <div class="bar-container">
                                    <div class="bar" style="width: <?= $pourcentage ?>%"></div>
                                </div>
                                <div class="value"><?= $item['nombre'] ?></div>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    <?php else: ?>
                        <div class="no-data">Aucun type trouvé</div>
                    <?php endif; ?>
                </div>
                
                <!-- Matériel par marque -->
                <div class="card">
                    <h2>🔖 Répartition par Marque</h2>
                    <?php if (count($materiels_par_marque) > 0): ?>
                        <?php 
                        $max_nombre = max(array_column($materiels_par_marque, 'nombre'));
                        foreach ($materiels_par_marque as $item): 
                            if ($item['nombre'] > 0):
                                $pourcentage = ($item['nombre'] / $max_nombre) * 100;
                        ?>
                            <div class="chart-bar">
                                <div class="label"><?= htmlspecialchars($item['marque_nom']) ?></div>
                                <div class="bar-container">
                                    <div class="bar" style="width: <?= $pourcentage ?>%"></div>
                                </div>
                                <div class="value"><?= $item['nombre'] ?></div>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    <?php else: ?>
                        <div class="no-data">Aucune marque trouvée</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>