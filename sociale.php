<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier que le numéro de sécurité sociale est disponible dans la session
if (!isset($_SESSION['num_secu'])) {
    die("Aucun patient n'a été enregistré. Veuillez enregistrer un patient d'abord.");
}

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

// Traitement du formulaire lors de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et sécurisation des données du formulaire
    $organisme_secu = $mysqli->real_escape_string($_POST['organisme_secu']);
    $pat_ass = ($_POST['assure'] === 'oui') ? 1 : 0;
    $pat_ald = ($_POST['ald'] === 'oui') ? 1 : 0;
    $mutuelle = $mysqli->real_escape_string($_POST['mutuelle']);
    $num_adherent = $_POST['num_adherent'];
    $id_chambre = (int)$_POST['id_chambre'];
    
    // Traitement du numéro de sécurité sociale depuis la session en tant que chaîne
    $patient_num_secu = $_SESSION['num_secu'];

    // Vérification du numéro d'adhérent
    if (!preg_match('/^\d{11}$/', $num_adherent)) {
        $error_message = "Le numéro d'adhérent doit contenir exactement 11 chiffres.";
    } else {
        // Préparation de la requête d'insertion dans la table secu_soc
        $stmt = $mysqli->prepare("INSERT INTO secu_soc (ogr_sec_soc, pat_ass, pat_ald, nom_ass, num_adr, num_secu, id_chambre) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $error_message = "Erreur de préparation : " . $mysqli->error;
        } else {
            // Liaison des paramètres :
            // ogr_sec_soc => string, pat_ass => int, pat_ald => int, nom_ass => string,
            // num_adr => int, num_secu => string, id_chambre => int
            $stmt->bind_param("siisisi", $organisme_secu, $pat_ass, $pat_ald, $mutuelle, $num_adherent, $patient_num_secu, $id_chambre);
            if ($stmt->execute()) {
                $success_message = "Les informations sociales ont été enregistrées avec succès.";
                // Redirection vers persconf.php après insertion réussie
                header('Location: persconf.php');
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
    <title>Informations Sociales</title>
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
            <a href="preadmission.php" class="bg-gray-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700 transition">Annuler la préadmission</a>
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

<div class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-6">
        <?php if ($error_message): ?>
            <p class="text-red-500 text-sm mb-4"><?= htmlspecialchars($error_message) ?></p>
        <?php elseif ($success_message): ?>
            <p class="text-green-500 text-sm mb-4"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>

        <h2 class="text-2xl font-bold text-center mb-6">Formulaire Social du Patient</h2>
        <form method="POST" action="" class="space-y-4">
            <div>
                <label for="organisme_secu" class="block text-sm font-medium text-gray-700">Organisme de Sécurité Sociale :</label>
                <input type="text" id="organisme_secu" name="organisme_secu" required 
                       class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="assure" class="block text-sm font-medium text-gray-700">Le patient est-il assuré ?</label>
                <select id="assure" name="assure" required 
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>Choisissez</option>
                    <option value="oui">Oui</option>
                    <option value="non">Non</option>
                </select>
            </div>
            <div>
                <label for="ald" class="block text-sm font-medium text-gray-700">Le patient est-il en ALD ?</label>
                <select id="ald" name="ald" required 
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>Choisissez</option>
                    <option value="oui">Oui</option>
                    <option value="non">Non</option>
                </select>
            </div>
            <div>
                <label for="mutuelle" class="block text-sm font-medium text-gray-700">Nom de la mutuelle :</label>
                <input type="text" id="mutuelle" name="mutuelle" required 
                       class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="num_adherent" class="block text-sm font-medium text-gray-700">Numéro d'adhérent :</label>
                <input type="text" id="num_adherent" name="num_adherent" required minlength="11" maxlength="11"
                       class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="id_chambre" class="block text-sm font-medium text-gray-700">Type de chambre :</label>
                <select id="id_chambre" name="id_chambre" required 
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>Choisissez</option>
                    <?php
                    // Récupération des chambres disponibles
                    $sql = "SELECT id_chambre, libelle FROM chambre";
                    $result = $mysqli->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id_chambre'] . "'>" . htmlspecialchars($row['libelle']) . "</option>";
                        }
                    } else {
                        echo "<option disabled>Aucun type de chambre disponible</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="text-center">
                <button type="submit" name="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Soumettre
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>