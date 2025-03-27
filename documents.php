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
$username = 'sio';
$password = 'sio';
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
    if (isset($_POST['cancel_preadmission'])) {
        // Supprimer les données du patient de la base de données
        $stmt = $mysqli->prepare("DELETE FROM patient WHERE num_secu = ?");
        $stmt->bind_param("i", $num_secu);
        if ($stmt->execute()) {
            // Détruire les données de session et démarrer une nouvelle session
            session_unset();
            session_destroy();
            session_start();

            // Redirection vers preadmission.php
            header('Location: preadmission.php');
            exit;
        } else {
            $error_message = "Erreur lors de la suppression des données : " . $stmt->error;
        }
        $stmt->close();
    } else {
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
                if (!$is_minor) {
                    $livret_famille = null;
                    $stmt->bind_param("ssssi", $carte_identite, $carte_vitale, $carte_mutuelle, $livret_famille, $num_secu);
                } else {
                    $stmt->bind_param("ssssi", $carte_identite, $carte_vitale, $carte_mutuelle, $livret_famille, $num_secu);
                }

                if ($stmt->execute()) {
                    // Option de génération de fiche de rendez-vous
                    if (isset($_POST['generate_pdf']) && $_POST['generate_pdf'] == 'yes') {
                        generatePDF($num_secu, $mysqli);
                        exit;  // Arrêter l'exécution après la génération du PDF
                    }

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
}

// Fonction pour générer le PDF
function generatePDF($num_secu, $mysqli) {
    require('lib/fpdf/fpdf.php');

    // Récupérer les informations du patient et du médecin
    $query = "
        SELECT p.num_secu, p.nom_de_naissance, p.prenom, p.date_preadmission, s.libelle_service, m.nom_med, m.prenom_med
        FROM patient p
        LEFT JOIN services s ON p.num_serv = s.num_serv
        LEFT JOIN medecin m ON p.id_med = m.id_med
        WHERE p.num_secu = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $num_secu);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    $stmt->close();

    if ($patient) {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Fiche de Rendez-vous', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Ln(10);

        // Informations du patient
        $pdf->Cell(50, 10, 'Nom:', 0, 0);
        $pdf->Cell(0, 10, $patient['nom_de_naissance'], 0, 1);
        $pdf->Cell(50, 10, 'Prénom:', 0, 0);
        $pdf->Cell(0, 10, $patient['prenom'], 0, 1);
        $pdf->Cell(50, 10, 'Numéro de Sécurité Sociale:', 0, 0);
        $pdf->Cell(0, 10, $patient['num_secu'], 0, 1);
        $pdf->Cell(50, 10, 'Date de Pré-admission:', 0, 0);
        $pdf->Cell(0, 10, $patient['date_preadmission'], 0, 1);
        $pdf->Cell(50, 10, 'Service:', 0, 0);
        $pdf->Cell(0, 10, $patient['libelle_service'], 0, 1);
        $pdf->Cell(50, 10, 'Médecin:', 0, 0);
        $pdf->Cell(0, 10, $patient['nom_med'] . ' ' . $patient['prenom_med'], 0, 1);

        // Téléchargement du PDF
        $pdf->Output('D', 'Fiche_Rendez_vous.pdf');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<header class="bg-green-600 text-white py-4 shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
        <!-- Logo et Nom de la clinique -->
        <div class="flex items-center space-x-3">
            <img src="images/logo.png" alt="Logo Clinique" class="w-12 h-12 rounded-full">
            <div>
                <h1 class="text-3xl font-bold">Clinique Santé</h1>
                <p class="text-sm">Votre bien-être, notre priorité</p>
            </div>
        </div>

        <!-- Déconnexion et Annuler la préadmission -->
        <div class="space-x-4">
            <a href="index.php" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 transition">Déconnexion</a>
            <form action="documents.php" method="POST" class="inline">
                <input type="hidden" name="cancel_preadmission" value="yes">
                <button type="submit" class="bg-gray-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700 transition">Annuler la préadmission</button>
            </form>
        </div>

        <!-- Menu Mobile -->
        <div class="md:hidden">
            <button id="mobile-menu-button" class="text-white focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                </svg>
            </button>
        </div>
    </div>

    <script>
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
</header>

<div class="bg-white p-8 rounded shadow-lg max-w-md w-full mx-auto mt-10">
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
        <label class="block">
            <input type="checkbox" name="generate_pdf" value="yes">
            Générer une fiche de rendez-vous en PDF
        </label>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Envoyer</button>
    </form>
</div>

</body>
</html>