<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$dbName = 'patisserie';

$mysqli = new mysqli($host, $user, $password);
$mysqli->set_charset('utf8mb4');
if ($mysqli->connect_error) {
    die('Erreur de connexion : ' . $mysqli->connect_error);
}

$mysqli->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$mysqli->select_db($dbName);

$mysqli->query(
    "CREATE TABLE IF NOT EXISTS `magasin` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `nom` VARCHAR(100) NOT NULL,
        `adresse` VARCHAR(255) NOT NULL,
        `ville` VARCHAR(100) NOT NULL,
        `code_postal` VARCHAR(20) NOT NULL,
        `telephone` VARCHAR(50) DEFAULT NULL,
        `email` VARCHAR(150) DEFAULT NULL,
        `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$mysqli->query(
    "CREATE TABLE IF NOT EXISTS `client` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `nom` VARCHAR(100) NOT NULL,
        `prenom` VARCHAR(100) NOT NULL,
        `email` VARCHAR(150) NOT NULL UNIQUE,
        `telephone` VARCHAR(50) DEFAULT NULL,
        `adresse` VARCHAR(255) NOT NULL,
        `ville` VARCHAR(100) NOT NULL,
        `code_postal` VARCHAR(20) NOT NULL,
        `date_inscription` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `est_actif` TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

function clean($value)
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

$section = isset($_GET['section']) && in_array($_GET['section'], ['magasin', 'client']) ? $_GET['section'] : 'magasin';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_magasin'])) {
        $nom = clean($_POST['nom']);
        $adresse = clean($_POST['adresse']);
        $ville = clean($_POST['ville']);
        $code_postal = clean($_POST['code_postal']);
        $telephone = clean($_POST['telephone']);
        $email = clean($_POST['email']);

        if (!empty($_POST['id'])) {
            $stmt = $mysqli->prepare('UPDATE magasin SET nom = ?, adresse = ?, ville = ?, code_postal = ?, telephone = ?, email = ? WHERE id = ?');
            $stmt->bind_param('ssssssi', $nom, $adresse, $ville, $code_postal, $telephone, $email, $_POST['id']);
            $stmt->execute();
            $message = 'Magasin mis à jour avec succès.';
        } else {
            $stmt = $mysqli->prepare('INSERT INTO magasin (nom, adresse, ville, code_postal, telephone, email) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssss', $nom, $adresse, $ville, $code_postal, $telephone, $email);
            $stmt->execute();
            $message = 'Magasin ajouté avec succès.';
        }
    }

    if (isset($_POST['save_client'])) {
        $nom = clean($_POST['nom']);
        $prenom = clean($_POST['prenom']);
        $email = clean($_POST['email']);
        $telephone = clean($_POST['telephone']);
        $adresse = clean($_POST['adresse']);
        $ville = clean($_POST['ville']);
        $code_postal = clean($_POST['code_postal']);
        $est_actif = isset($_POST['est_actif']) ? 1 : 0;

        if (!empty($_POST['id'])) {
            $stmt = $mysqli->prepare('UPDATE client SET nom = ?, prenom = ?, email = ?, telephone = ?, adresse = ?, ville = ?, code_postal = ?, est_actif = ? WHERE id = ?');
            $stmt->bind_param('ssssssiii', $nom, $prenom, $email, $telephone, $adresse, $ville, $code_postal, $est_actif, $_POST['id']);
            $stmt->execute();
            $message = 'Client mis à jour avec succès.';
        } else {
            $stmt = $mysqli->prepare('INSERT INTO client (nom, prenom, email, telephone, adresse, ville, code_postal, est_actif) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssssssi', $nom, $prenom, $email, $telephone, $adresse, $ville, $code_postal, $est_actif);
            $stmt->execute();
            $message = 'Client ajouté avec succès.';
        }
    }
}

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete_magasin' && isset($_GET['id'])) {
        $stmt = $mysqli->prepare('DELETE FROM magasin WHERE id = ?');
        $stmt->bind_param('i', $_GET['id']);
        $stmt->execute();
        $message = 'Magasin supprimé.';
    }
    if ($_GET['action'] === 'delete_client' && isset($_GET['id'])) {
        $stmt = $mysqli->prepare('DELETE FROM client WHERE id = ?');
        $stmt->bind_param('i', $_GET['id']);
        $stmt->execute();
        $message = 'Client supprimé.';
    }
}

$editMagasin = null;
$editClient = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit_magasin' && isset($_GET['id'])) {
    $stmt = $mysqli->prepare('SELECT * FROM magasin WHERE id = ?');
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $editMagasin = $stmt->get_result()->fetch_assoc();
    $section = 'magasin';
}
if (isset($_GET['action']) && $_GET['action'] === 'edit_client' && isset($_GET['id'])) {
    $stmt = $mysqli->prepare('SELECT * FROM client WHERE id = ?');
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $editClient = $stmt->get_result()->fetch_assoc();
    $section = 'client';
}

$magasins = $mysqli->query('SELECT * FROM magasin ORDER BY id DESC')->fetch_all(MYSQLI_ASSOC);
$clients = $mysqli->query('SELECT * FROM client ORDER BY id DESC')->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Pâtisserie - CRUD</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background: #f7f1f6;
            color: #333;
        }
        header {
            background: linear-gradient(90deg, #ff7aa2, #ffb6c1);
            padding: 20px;
            text-align: center;
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .tabs {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .tab {
            padding: 12px 20px;
            border-radius: 999px;
            background: #fff;
            text-decoration: none;
            color: #333;
            border: 1px solid #ddd;
            transition: 0.2s;
        }
        .tab.active,
        .tab:hover {
            background: #ff4d88;
            color: white;
            border-color: #ff4d88;
        }
        .message {
            text-align: center;
            margin-bottom: 20px;
            padding: 14px 18px;
            background: #e9ffe7;
            border: 1px solid #b7ffb1;
            color: #216c24;
            border-radius: 12px;
        }
        .grid {
            display: grid;
            gap: 30px;
            grid-template-columns: 1fr 1.4fr;
        }
        .card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 12px 25px rgba(0,0,0,0.08);
        }
        .card h2 {
            margin-bottom: 18px;
            font-size: 24px;
            color: #444;
        }
        form {
            display: grid;
            gap: 16px;
        }
        label {
            font-weight: 600;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 14px;
            font-size: 15px;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .btn,
        .btn-small {
            display: inline-block;
            border: none;
            border-radius: 999px;
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn {
            background: #ff4d88;
            color: white;
        }
        .btn:hover {
            background: #e63e77;
        }
        .btn-small {
            background: #fff;
            color: #333;
            border: 1px solid #ddd;
        }
        .btn-small:hover {
            background: #ff4d88;
            color: white;
            border-color: #ff4d88;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid #f0f0f0;
            text-align: left;
        }
        th {
            font-weight: 700;
            color: #555;
        }
        td:last-child {
            white-space: nowrap;
        }
        .label {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            background: #ffebf0;
            color: #d32f6b;
            font-size: 12px;
            font-weight: 700;
        }
        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<header>
    <h1>Interface CRUD - Pâtisserie</h1>
    <p>Gérez les magasins et les clients de la base de données <strong>patisserie</strong>.</p>
</header>
<div class="container">
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="tabs">
        <a class="tab <?php echo $section === 'magasin' ? 'active' : ''; ?>" href="?section=magasin">Magasins</a>
        <a class="tab <?php echo $section === 'client' ? 'active' : ''; ?>" href="?section=client">Clients</a>
    </div>

    <?php if ($section === 'magasin'): ?>
        <div class="grid">
            <div class="card">
                <h2><?php echo $editMagasin ? 'Modifier un magasin' : 'Ajouter un magasin'; ?></h2>
                <form method="post" action="?section=magasin">
                    <?php if ($editMagasin): ?>
                        <input type="hidden" name="id" value="<?php echo $editMagasin['id']; ?>">
                    <?php endif; ?>
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required value="<?php echo $editMagasin['nom'] ?? ''; ?>">
                    <label for="adresse">Adresse</label>
                    <textarea id="adresse" name="adresse" required><?php echo $editMagasin['adresse'] ?? ''; ?></textarea>
                    <label for="ville">Ville</label>
                    <input type="text" id="ville" name="ville" required value="<?php echo $editMagasin['ville'] ?? ''; ?>">
                    <label for="code_postal">Code postal</label>
                    <input type="text" id="code_postal" name="code_postal" required value="<?php echo $editMagasin['code_postal'] ?? ''; ?>">
                    <label for="telephone">Téléphone</label>
                    <input type="text" id="telephone" name="telephone" value="<?php echo $editMagasin['telephone'] ?? ''; ?>">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo $editMagasin['email'] ?? ''; ?>">
                    <button type="submit" name="save_magasin" class="btn"><?php echo $editMagasin ? 'Mettre à jour' : 'Ajouter'; ?></button>
                </form>
            </div>
            <div class="card">
                <h2>Liste des magasins</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Ville</th>
                            <th>Téléphone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($magasins as $magasin): ?>
                            <tr>
                                <td><?php echo $magasin['id']; ?></td>
                                <td><?php echo $magasin['nom']; ?></td>
                                <td><?php echo $magasin['ville']; ?></td>
                                <td><?php echo $magasin['telephone']; ?></td>
                                <td>
                                    <a class="btn-small" href="?section=magasin&action=edit_magasin&id=<?php echo $magasin['id']; ?>">Modifier</a>
                                    <a class="btn-small" href="?section=magasin&action=delete_magasin&id=<?php echo $magasin['id']; ?>" onclick="return confirm('Supprimer ce magasin ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="grid">
            <div class="card">
                <h2><?php echo $editClient ? 'Modifier un client' : 'Ajouter un client'; ?></h2>
                <form method="post" action="?section=client">
                    <?php if ($editClient): ?>
                        <input type="hidden" name="id" value="<?php echo $editClient['id']; ?>">
                    <?php endif; ?>
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required value="<?php echo $editClient['nom'] ?? ''; ?>">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required value="<?php echo $editClient['prenom'] ?? ''; ?>">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo $editClient['email'] ?? ''; ?>">
                    <label for="telephone">Téléphone</label>
                    <input type="text" id="telephone" name="telephone" value="<?php echo $editClient['telephone'] ?? ''; ?>">
                    <label for="adresse">Adresse</label>
                    <textarea id="adresse" name="adresse" required><?php echo $editClient['adresse'] ?? ''; ?></textarea>
                    <label for="ville">Ville</label>
                    <input type="text" id="ville" name="ville" required value="<?php echo $editClient['ville'] ?? ''; ?>">
                    <label for="code_postal">Code postal</label>
                    <input type="text" id="code_postal" name="code_postal" required value="<?php echo $editClient['code_postal'] ?? ''; ?>">
                    <label>
                        <input type="checkbox" name="est_actif" <?php echo isset($editClient['est_actif']) && $editClient['est_actif'] ? 'checked' : ''; ?>> Client actif
                    </label>
                    <button type="submit" name="save_client" class="btn"><?php echo $editClient ? 'Mettre à jour' : 'Ajouter'; ?></button>
                </form>
            </div>
            <div class="card">
                <h2>Liste des clients</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Ville</th>
                            <th>Actif</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php echo $client['id']; ?></td>
                                <td><?php echo $client['nom'] . ' ' . $client['prenom']; ?></td>
                                <td><?php echo $client['email']; ?></td>
                                <td><?php echo $client['ville']; ?></td>
                                <td><span class="label"><?php echo $client['est_actif'] ? 'Oui' : 'Non'; ?></span></td>
                                <td>
                                    <a class="btn-small" href="?section=client&action=edit_client&id=<?php echo $client['id']; ?>">Modifier</a>
                                    <a class="btn-small" href="?section=client&action=delete_client&id=<?php echo $client['id']; ?>" onclick="return confirm('Supprimer ce client ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
