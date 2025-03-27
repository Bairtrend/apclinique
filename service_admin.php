<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier que le numéro de sécurité sociale est disponible dans la session
if (!isset($_SESSION['num_secu'])) {
    die("Aucun patient n'a été enregistré. Veuillez enregistrer un patient d'abord.");
}

$num_secu = $_SESSION['num_secu'];

// Connexion à la base de données
$servername = 'localhost';
$username = 'root';
$password = 'sio2024';
$dbname = 'cliniquelpfs';

$mysqli = mysqli_connect($servername, $username, $password, $dbname);
if (!$mysqli) {
    die("La connexion a échoué: " . mysqli_connect_error());
}

$error_message = null;
$success_message = null;

// Fonction pour vérifier si le patient est mineur
function isMinor($num_secu, $mysqli) {
    $query = "SELECT date_de_naissance FROM patient WHERE num_secu = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $num_secu);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $date_de_naissance = $row['date_de_naissance'];
        $birth_year = intval(explode("-", $date_de_naissance)[0]);
        $current_year = intval(date("Y"));
        return ($current_year - $birth_year) < 18;
    }
    return false;
}

// Vérification si le patient est mineur
$is_minor = isMinor($num_secu, $mysqli);

// Traitement du formulaire lors de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification des fichiers téléchargés
    $required_files = ['carte_identite', 'carte_vitale', 'carte_mutuelle'];
    if ($is_minor) {
        $required_files[] = 'livret_famille';
    }

    foreach ($required_files as $file) {
        if (!isset($_FILES[$file]) || $_FILES[$file]['error'] != UPLOAD_ERR_OK) {
            if ($is_minor && $file == 'livret_famille') {
                $error_message = "Veuillez envoyer le livret de famille car le patient est mineur.";
            } else {
                $error_message = "Tous les fichiers obligatoires doivent être téléchargés.";
            }
            break;
        }
    }

    if (!$error_message) {
        $carte_identite = file_get_contents($_FILES['carte_identite']['tmp_name']);
        $carte_vitale = file_get_contents($_FILES['carte_vitale']['tmp_name']);
        $carte_mutuelle = file_get_contents($_FILES['carte_mutuelle']['tmp_name']);
        $livret_famille = $is_minor ? file_get_contents($_FILES['livret_famille']['tmp_name']) : null;

        // Préparation de la requête d'insertion dans la table document
        $stmt = $mysqli->prepare("INSERT INTO document (carte_identite, carte_vitale, carte_mut, livret_fam, num_secu) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            $error_message = "Erreur de préparation : " . $mysqli->error;
        } else {
            // Si le livret de famille est null, utilisez NULL pour la colonne livret_fam
            if (!$is_minor) {
                $stmt->bind_param("ssssi", $carte_identite, $carte_vitale, $carte_mutuelle, $livret_famille, $num_secu);
                $stmt->send_long_data(3, null); // Envoyer une valeur NULL pour livret_fam
            } else {
                $stmt->bind_param("ssssi", $carte_identite, $carte_vitale, $carte_mutuelle, $livret_famille, $num_secu);
            }

            if ($stmt->execute()) {
                // Détruire les données de session et démarrer une nouvelle session
                session_unset();
                session_destroy();
                session_start();
                
                // Redirection vers preadmission.php
                header('Location: preadmission.php');
                exit;
            } else {
                $error_message = "Erreur lors de l'exécution : " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow-lg max-w-md w-full">
        <h2 class="text-xl font-bold mb-4">Téléversement de documents</h2>
        <?php if ($error_message): ?>
            <p class="text-red-500 text-sm mb-4"><?= htmlspecialchars($error_message) ?></p>
        <?php elseif ($success_message): ?>
            <p class="text-green-500 text-sm mb-4"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>
        <form action="documents.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <label class="block">Carte d'identité (obligatoire)
                <input type="file" name="carte_identite" class="block w-full border p-2 rounded" required>
            </label>
            <label class="block">Carte Vitale (obligatoire)
                <input type="file" name="carte_vitale" class="block w-full border p-2 rounded" required>
            </label>
            <label class="block">Carte Mutuelle (obligatoire)
                <input type="file" name="carte_mutuelle" class="block w-full border p-2 rounded" required>
            </label>
            <label class="block">Livret de Famille <?php if ($is_minor): ?>(obligatoire)<?php else: ?>(optionnel)<?php endif; ?>
                <input type="file" name="livret_famille" class="block w-full border p-2 rounded" <?php if ($is_minor): ?>required<?php endif; ?>>
            </label>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Envoyer</button>
        </form>
    </div>
</body>
</html>