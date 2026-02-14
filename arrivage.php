<?php
/**
 * PAGE D'ARRIVAGE DE MATÉRIEL
 * Permet d'ajouter du nouveau matériel dans l'inventaire
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

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $connexion_ok) {
    $id_type = $_POST['id_type_materiel'] ?? '';
    $id_marque = $_POST['id_marque_materiel'] ?? '';
    $modele = trim($_POST['modele'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    
    // Validation
    $erreurs = [];
    
    if (empty($id_type)) {
        $erreurs[] = "Le type de matériel est obligatoire";
    }
    
    if (empty($id_marque)) {
        $erreurs[] = "La marque est obligatoire";
    }
    
    if (empty($serial_number)) {
        $erreurs[] = "Le numéro de série est obligatoire";
    }
    
    if (empty($nom)) {
        $erreurs[] = "Le nom du matériel est obligatoire";
    }
    
    // Vérifier si le numéro de série existe déjà
    if (empty($erreurs) && !empty($serial_number)) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM (
                SELECT serial_number FROM materiel_dispo WHERE serial_number = ?
                UNION ALL
                SELECT serial_number FROM materiel_affecté WHERE serial_number = ?
            ) as combined
        ");
        $stmt->execute([$serial_number, $serial_number]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $erreurs[] = "Ce numéro de série existe déjà dans la base de données";
        }
    }
    
    if (empty($erreurs)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO materiel_dispo (id_type_materiel, id_marque_materiel, modele, serial_number, nom)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$id_type, $id_marque, $modele, $serial_number, $nom]);
            
            // Mettre à jour la quantité disponible du type
            $stmt = $pdo->prepare("
                UPDATE type_materiel 
                SET quantite_dispo = quantite_dispo + 1 
                WHERE id_type_materiel = ?
            ");
            $stmt->execute([$id_type]);
            
            $message = "✅ Matériel ajouté avec succès ! (ID: " . $pdo->lastInsertId() . ")";
            $message_type = 'success';
            
            // Réinitialiser le formulaire
            $_POST = [];
            
        } catch(PDOException $e) {
            $message = "❌ Erreur lors de l'ajout : " . $e->getMessage();
            $message_type = 'error';
        }
    } else {
        $message = "❌ Erreurs de validation :<br>" . implode("<br>", $erreurs);
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
    $types_materiel = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    $marques = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arrivage Matériel</title>
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
            max-width: 800px;
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
        
        .header-buttons {
            display: flex;
            gap: 10px;
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
        
        .btn-config {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease;
            display: inline-block;
        }
        
        .btn-config:hover {
            transform: translateY(-2px);
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
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
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95em;
        }
        
        .required {
            color: #e53e3e;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group small {
            display: block;
            color: #718096;
            margin-top: 5px;
            font-size: 0.85em;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .stat-box .label {
            color: #718096;
            font-size: 0.85em;
            margin-bottom: 5px;
        }
        
        .stat-box .value {
            color: #2d3748;
            font-size: 1.5em;
            font-weight: 700;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .header-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-back, .btn-config {
                width: 100%;
                text-align: center;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
        }
        
        .alert-info {
            background: #bee3f8;
            border-left: 4px solid #3182ce;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            color: #2c5282;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📦 Arrivage de Matériel</h1>
            <div class="header-buttons">
                <a href="index.php" class="btn-back">← Dashboard</a>
                <a href="config.php" class="btn-config">⚙️ Configuration</a>
            </div>
        </div>
        
        <?php if (!$connexion_ok): ?>
            <div class="card">
                <div class="message error">
                    <strong>⚠️ Erreur de connexion à la base de données</strong><br>
                    <?= htmlspecialchars($erreur_connexion ?? 'Erreur inconnue') ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Statistiques rapides -->
            <div class="stats">
                <div class="stat-box">
                    <div class="label">Types de matériel</div>
                    <div class="value"><?= count($types_materiel) ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Marques disponibles</div>
                    <div class="value"><?= count($marques) ?></div>
                </div>
            </div>
            
            <!-- Formulaire -->
            <div class="card">
                <?php if (!empty($message)): ?>
                    <div class="message <?= $message_type ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>
                
                <div class="alert-info">
                    ℹ️ Remplissez ce formulaire pour enregistrer l'arrivée d'un nouveau matériel dans l'inventaire.
                </div>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_type_materiel">Type de matériel <span class="required">*</span></label>
                            <select name="id_type_materiel" id="id_type_materiel" required>
                                <option value="">-- Sélectionner un type --</option>
                                <?php foreach ($types_materiel as $type): ?>
                                    <option value="<?= $type['id_type_materiel'] ?>" 
                                            <?= (isset($_POST['id_type_materiel']) && $_POST['id_type_materiel'] == $type['id_type_materiel']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type['nom']) ?> (Stock: <?= $type['quantite_dispo'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_marque_materiel">Marque <span class="required">*</span></label>
                            <select name="id_marque_materiel" id="id_marque_materiel" required>
                                <option value="">-- Sélectionner une marque --</option>
                                <?php foreach ($marques as $marque): ?>
                                    <option value="<?= $marque['id_marque_materiel'] ?>"
                                            <?= (isset($_POST['id_marque_materiel']) && $_POST['id_marque_materiel'] == $marque['id_marque_materiel']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($marque['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="nom">Nom du matériel <span class="required">*</span></label>
                        <input type="text" 
                               name="nom" 
                               id="nom" 
                               required
                               placeholder="Ex: Dell Latitude 5520 - i5-11500H"
                               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                        <small>Nom complet et descriptif du matériel</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="modele">Modèle</label>
                            <input type="text" 
                                   name="modele" 
                                   id="modele"
                                   placeholder="Ex: Latitude 5520"
                                   value="<?= htmlspecialchars($_POST['modele'] ?? '') ?>">
                            <small>Référence du modèle</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="serial_number">Numéro de série <span class="required">*</span></label>
                            <input type="text" 
                                   name="serial_number" 
                                   id="serial_number" 
                                   required
                                   placeholder="Ex: SN123456789"
                                   value="<?= htmlspecialchars($_POST['serial_number'] ?? '') ?>">
                            <small>Numéro unique du matériel</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        ✅ Enregistrer l'arrivage
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto-complétion du nom basé sur le type et la marque
        document.getElementById('id_type_materiel').addEventListener('change', updateNom);
        document.getElementById('id_marque_materiel').addEventListener('change', updateNom);
        document.getElementById('modele').addEventListener('input', updateNom);
        
        function updateNom() {
            const typeSelect = document.getElementById('id_type_materiel');
            const marqueSelect = document.getElementById('id_marque_materiel');
            const modeleInput = document.getElementById('modele');
            const nomInput = document.getElementById('nom');
            
            // Ne pas écraser si l'utilisateur a déjà saisi quelque chose
            if (nomInput.value.trim() !== '') return;
            
            const typeText = typeSelect.options[typeSelect.selectedIndex]?.text.split(' (')[0] || '';
            const marqueText = marqueSelect.options[marqueSelect.selectedIndex]?.text || '';
            const modeleText = modeleInput.value.trim();
            
            let nomSuggestion = '';
            if (marqueText && typeText) {
                nomSuggestion = marqueText;
                if (modeleText) {
                    nomSuggestion += ' ' + modeleText;
                }
                nomSuggestion += ' - ' + typeText;
            }
            
            if (nomSuggestion) {
                nomInput.value = nomSuggestion;
            }
        }
    </script>
</body>
</html>
