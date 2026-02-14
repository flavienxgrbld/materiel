<?php
/**
 * PAGE DE CONFIGURATION
 * Gestion des types de matériel et des marques
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

$message = '';
$message_type = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $connexion_ok) {
    $action = $_POST['action'] ?? '';
    
    try {
        // GESTION DES TYPES DE MATÉRIEL
        if ($action === 'add_type') {
            $nom = trim($_POST['nom_type'] ?? '');
            $quantite = intval($_POST['quantite_dispo'] ?? 0);
            
            if (empty($nom)) {
                throw new Exception("Le nom du type est obligatoire");
            }
            
            $stmt = $pdo->prepare("INSERT INTO type_materiel (nom, quantite_dispo) VALUES (?, ?)");
            $stmt->execute([$nom, $quantite]);
            
            $message = "✅ Type de matériel ajouté avec succès";
            $message_type = 'success';
            
        } elseif ($action === 'edit_type') {
            $id = intval($_POST['id_type'] ?? 0);
            $nom = trim($_POST['nom_type'] ?? '');
            $quantite = intval($_POST['quantite_dispo'] ?? 0);
            
            if (empty($nom)) {
                throw new Exception("Le nom du type est obligatoire");
            }
            
            $stmt = $pdo->prepare("UPDATE type_materiel SET nom = ?, quantite_dispo = ? WHERE id_type_materiel = ?");
            $stmt->execute([$nom, $quantite, $id]);
            
            $message = "✅ Type de matériel modifié avec succès";
            $message_type = 'success';
            
        } elseif ($action === 'delete_type') {
            $id = intval($_POST['id_type'] ?? 0);
            
            // Vérifier si le type est utilisé
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM materiel_dispo WHERE id_type_materiel = ?");
            $stmt->execute([$id]);
            $count_dispo = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM materiel_affecté WHERE id_type_materiel = ?");
            $stmt->execute([$id]);
            $count_affecte = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($count_dispo > 0 || $count_affecte > 0) {
                throw new Exception("Impossible de supprimer ce type car il est utilisé par " . ($count_dispo + $count_affecte) . " matériel(s)");
            }
            
            $stmt = $pdo->prepare("DELETE FROM type_materiel WHERE id_type_materiel = ?");
            $stmt->execute([$id]);
            
            $message = "✅ Type de matériel supprimé avec succès";
            $message_type = 'success';
        }
        
        // GESTION DES MARQUES
        elseif ($action === 'add_marque') {
            $nom = trim($_POST['nom_marque'] ?? '');
            
            if (empty($nom)) {
                throw new Exception("Le nom de la marque est obligatoire");
            }
            
            $stmt = $pdo->prepare("INSERT INTO marque_materiel (nom) VALUES (?)");
            $stmt->execute([$nom]);
            
            $message = "✅ Marque ajoutée avec succès";
            $message_type = 'success';
            
        } elseif ($action === 'edit_marque') {
            $id = intval($_POST['id_marque'] ?? 0);
            $nom = trim($_POST['nom_marque'] ?? '');
            
            if (empty($nom)) {
                throw new Exception("Le nom de la marque est obligatoire");
            }
            
            $stmt = $pdo->prepare("UPDATE marque_materiel SET nom = ? WHERE id_marque_materiel = ?");
            $stmt->execute([$nom, $id]);
            
            $message = "✅ Marque modifiée avec succès";
            $message_type = 'success';
            
        } elseif ($action === 'delete_marque') {
            $id = intval($_POST['id_marque'] ?? 0);
            
            // Vérifier si la marque est utilisée
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM materiel_dispo WHERE id_marque_materiel = ?");
            $stmt->execute([$id]);
            $count_dispo = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM materiel_affecté WHERE id_marque_materiel = ?");
            $stmt->execute([$id]);
            $count_affecte = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($count_dispo > 0 || $count_affecte > 0) {
                throw new Exception("Impossible de supprimer cette marque car elle est utilisée par " . ($count_dispo + $count_affecte) . " matériel(s)");
            }
            
            $stmt = $pdo->prepare("DELETE FROM marque_materiel WHERE id_marque_materiel = ?");
            $stmt->execute([$id]);
            
            $message = "✅ Marque supprimée avec succès";
            $message_type = 'success';
        }
        
    } catch(Exception $e) {
        $message = "❌ Erreur : " . $e->getMessage();
        $message_type = 'error';
    }
}

// Récupérer les types de matériel (dédupliqués)
$types_materiel = [];
if ($connexion_ok) {
    $stmt = $pdo->query("
        SELECT MIN(id_type_materiel) as id_type_materiel, nom, MAX(quantite_dispo) as quantite_dispo 
        FROM type_materiel 
        GROUP BY nom 
        ORDER BY nom ASC
    ");
    $types_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ajouter le compte d'utilisation pour chaque type
    foreach ($types_results as $type) {
        $id = $type['id_type_materiel'];
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM (
                SELECT id_materiel_dispo FROM materiel_dispo WHERE id_type_materiel = ?
                UNION ALL
                SELECT id_materiel_affecté as id_materiel_dispo FROM materiel_affecté WHERE id_type_materiel = ?
            ) as combined
        ");
        $stmt->execute([$id, $id]);
        $type['count_usage'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $types_materiel[] = $type;
    }
}

// Récupérer les marques (dédupliquées)
$marques = [];
if ($connexion_ok) {
    $stmt = $pdo->query("
        SELECT MIN(id_marque_materiel) as id_marque_materiel, nom
        FROM marque_materiel
        GROUP BY nom
        ORDER BY nom ASC
    ");
    $marques_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ajouter le compte d'utilisation pour chaque marque
    foreach ($marques_results as $marque) {
        $id = $marque['id_marque_materiel'];
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM (
                SELECT id_materiel_dispo FROM materiel_dispo WHERE id_marque_materiel = ?
                UNION ALL
                SELECT id_materiel_affecté as id_materiel_dispo FROM materiel_affecté WHERE id_marque_materiel = ?
            ) as combined
        ");
        $stmt->execute([$id, $id]);
        $marque['count_usage'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $marques[] = $marque;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration - Matériel</title>
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
            max-width: 1200px;
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
        }
        
        .header h1 {
            color: #2d3748;
            font-size: 2em;
        }
        
        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease;
            display: inline-block;
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
        }
        
        .message {
            background: white;
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            font-weight: 500;
        }
        
        .message.success {
            background: #c6f6d5;
            border-left: 4px solid #38a169;
            color: #22543d;
        }
        
        .message.error {
            background: #fed7d7;
            border-left: 4px solid #e53e3e;
            color: #742a2a;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .card h2 {
            color: #2d3748;
            font-size: 1.5em;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .add-form {
            background: #f7fafc;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9em;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1em;
            font-family: 'Inter', sans-serif;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 120px;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95em;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: #48bb78;
            color: white;
        }
        
        .btn-danger {
            background: #f56565;
            color: white;
        }
        
        .btn-edit {
            background: #4299e1;
            color: white;
            padding: 6px 12px;
            font-size: 0.85em;
        }
        
        .btn-delete {
            background: #f56565;
            color: white;
            padding: 6px 12px;
            font-size: 0.85em;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
        }
        
        table tr:hover {
            background: #f7fafc;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .badge.used {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .edit-row {
            background: #ebf8ff !important;
        }
        
        .edit-row input {
            padding: 6px 10px;
            border: 2px solid #4299e1;
            border-radius: 4px;
            width: 100%;
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            color: #a0aec0;
        }
        
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>⚙️ Configuration</h1>
            <a href="index.php" class="btn-back">← Retour au Dashboard</a>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$connexion_ok): ?>
            <div class="card">
                <div class="message error">
                    <strong>⚠️ Erreur de connexion à la base de données</strong><br>
                    <?= htmlspecialchars($erreur_connexion ?? 'Erreur inconnue') ?>
                </div>
            </div>
        <?php else: ?>
            
            <!-- Grid principale -->
            <div class="grid">
                
                <!-- TYPES DE MATÉRIEL -->
                <div class="card">
                    <h2>🏷️ Types de Matériel</h2>
                    
                    <!-- Formulaire d'ajout -->
                    <div class="add-form">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_type">
                            <div class="form-group">
                                <label>Nom du type</label>
                                <div class="form-row">
                                    <input type="text" name="nom_type" placeholder="Ex: Ordinateur Portable" required>
                                    <button type="submit" class="btn btn-primary">Ajouter</button>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Quantité initiale</label>
                                <input type="number" name="quantite_dispo" value="0" min="0" style="width: 100px;">
                            </div>
                        </form>
                    </div>
                    
                    <!-- Liste des types -->
                    <div class="table-container">
                        <?php if (count($types_materiel) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th style="width: 100px;">Stock</th>
                                        <th style="width: 100px;">Utilisé</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="types-tbody">
                                    <?php foreach ($types_materiel as $type): ?>
                                        <tr data-id="<?= $type['id_type_materiel'] ?>">
                                            <td class="name-cell"><?= htmlspecialchars($type['nom']) ?></td>
                                            <td class="qty-cell"><?= $type['quantite_dispo'] ?></td>
                                            <td>
                                                <?php if ($type['count_usage'] > 0): ?>
                                                    <span class="badge used"><?= $type['count_usage'] ?></span>
                                                <?php else: ?>
                                                    <span class="badge">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="actions-cell">
                                                <div class="actions">
                                                    <button class="btn btn-edit" onclick="editType(<?= $type['id_type_materiel'] ?>, '<?= htmlspecialchars($type['nom'], ENT_QUOTES) ?>', <?= $type['quantite_dispo'] ?>)">
                                                        ✏️
                                                    </button>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce type ?');">
                                                        <input type="hidden" name="action" value="delete_type">
                                                        <input type="hidden" name="id_type" value="<?= $type['id_type_materiel'] ?>">
                                                        <button type="submit" class="btn btn-delete" <?= $type['count_usage'] > 0 ? 'disabled title="Type utilisé"' : '' ?>>
                                                            🗑️
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-data">Aucun type de matériel</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- MARQUES -->
                <div class="card">
                    <h2>🔖 Marques</h2>
                    
                    <!-- Formulaire d'ajout -->
                    <div class="add-form">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_marque">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Nom de la marque</label>
                                <div class="form-row">
                                    <input type="text" name="nom_marque" placeholder="Ex: Dell, HP, Lenovo..." required>
                                    <button type="submit" class="btn btn-primary">Ajouter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Liste des marques -->
                    <div class="table-container">
                        <?php if (count($marques) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th style="width: 100px;">Utilisé</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="marques-tbody">
                                    <?php foreach ($marques as $marque): ?>
                                        <tr data-id="<?= $marque['id_marque_materiel'] ?>">
                                            <td class="name-cell"><?= htmlspecialchars($marque['nom']) ?></td>
                                            <td>
                                                <?php if ($marque['count_usage'] > 0): ?>
                                                    <span class="badge used"><?= $marque['count_usage'] ?></span>
                                                <?php else: ?>
                                                    <span class="badge">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="actions-cell">
                                                <div class="actions">
                                                    <button class="btn btn-edit" onclick="editMarque(<?= $marque['id_marque_materiel'] ?>, '<?= htmlspecialchars($marque['nom'], ENT_QUOTES) ?>')">
                                                        ✏️
                                                    </button>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette marque ?');">
                                                        <input type="hidden" name="action" value="delete_marque">
                                                        <input type="hidden" name="id_marque" value="<?= $marque['id_marque_materiel'] ?>">
                                                        <button type="submit" class="btn btn-delete" <?= $marque['count_usage'] > 0 ? 'disabled title="Marque utilisée"' : '' ?>>
                                                            🗑️
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-data">Aucune marque</div>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
            
        <?php endif; ?>
    </div>
    
    <script>
        // Édition en ligne des types
        function editType(id, nom, quantite) {
            const row = document.querySelector(`#types-tbody tr[data-id="${id}"]`);
            const nameCell = row.querySelector('.name-cell');
            const qtyCell = row.querySelector('.qty-cell');
            const actionsCell = row.querySelector('.actions-cell');
            
            row.classList.add('edit-row');
            
            nameCell.innerHTML = `<input type="text" value="${nom}" id="edit-type-nom-${id}">`;
            qtyCell.innerHTML = `<input type="number" value="${quantite}" id="edit-type-qty-${id}" style="width: 80px;">`;
            actionsCell.innerHTML = `
                <div class="actions">
                    <button class="btn btn-success" onclick="saveType(${id})">💾</button>
                    <button class="btn btn-danger" onclick="location.reload()">✖️</button>
                </div>
            `;
        }
        
        function saveType(id) {
            const nom = document.getElementById(`edit-type-nom-${id}`).value;
            const quantite = document.getElementById(`edit-type-qty-${id}`).value;
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="edit_type">
                <input type="hidden" name="id_type" value="${id}">
                <input type="hidden" name="nom_type" value="${nom}">
                <input type="hidden" name="quantite_dispo" value="${quantite}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        // Édition en ligne des marques
        function editMarque(id, nom) {
            const row = document.querySelector(`#marques-tbody tr[data-id="${id}"]`);
            const nameCell = row.querySelector('.name-cell');
            const actionsCell = row.querySelector('.actions-cell');
            
            row.classList.add('edit-row');
            
            nameCell.innerHTML = `<input type="text" value="${nom}" id="edit-marque-nom-${id}">`;
            actionsCell.innerHTML = `
                <div class="actions">
                    <button class="btn btn-success" onclick="saveMarque(${id})">💾</button>
                    <button class="btn btn-danger" onclick="location.reload()">✖️</button>
                </div>
            `;
        }
        
        function saveMarque(id) {
            const nom = document.getElementById(`edit-marque-nom-${id}`).value;
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="edit_marque">
                <input type="hidden" name="id_marque" value="${id}">
                <input type="hidden" name="nom_marque" value="${nom}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
