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
    $nom = $mysqli->real_escape_string($_POST['nom']);
    $prenom = $mysqli->real_escape_string($_POST['prenom']);
    $tel = $mysqli->real_escape_string($_POST['tel']);
    $adresse = $mysqli->real_escape_string($_POST['adresse']);
    
    // Traitement du numéro de sécurité sociale depuis la session en tant que chaîne
    $patient_num_secu = $_SESSION['num_secu'];

    // Validation du numéro de téléphone
    if (!preg_match('/^(06|07|08)\d{8}$/', $tel)) {
        $error_message = "Le numéro de téléphone doit comporter 10 chiffres et commencer par 06, 07 ou 08.";
    } else {
        // Préparation de la requête d'insertion dans la table pers_prev
        $stmt = $mysqli->prepare("INSERT INTO pers_conf (nom, prenom, tel, adresse, num_secu) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            $error_message = "Erreur de préparation : " . $mysqli->error;
        } else {
            // Liaison des paramètres :
            // nom => string, prenom => string, tel => string, adresse => string, num_secu => int
            $stmt->bind_param("ssssi", $nom, $prenom, $tel, $adresse, $patient_num_secu);
            if ($stmt->execute()) {
                $success_message = "La personne à prévenir a été enregistrée avec succès.";
                // Redirection vers une page de confirmation ou une autre page appropriée après insertion réussie
                header('Location: persprev.php');
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
    <title>Formulaire Personne de confiance</title>
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
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Ajouter une Personne à Prévenir</h1>
        <?php if ($error_message): ?>
            <p class="text-red-500 text-sm mb-4"><?= htmlspecialchars($error_message) ?></p>
        <?php elseif ($success_message): ?>
            <p class="text-green-500 text-sm mb-4"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>
        <form action="" method="POST" class="space-y-4">
            <div>
                <label for="nom" class="block text-gray-700 font-medium">Nom</label>
                <input type="text" id="nom" name="nom" required class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="prenom" class="block text-gray-700 font-medium">Prénom</label>
                <input type="text" id="prenom" name="prenom" required class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="tel" class="block text-gray-700 font-medium">Téléphone</label>
                <input type="text" id="tel" name="tel" required maxlength="10" pattern="^(06|07|08)\d{8}$" title="Le numéro de téléphone doit comporter 10 chiffres et commencer par 06, 07 ou 08." class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="adresse" class="block text-gray-700 font-medium">Adresse</label>
                <input type="text" id="adresse" name="adresse" required class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition duration-200">Ajouter</button>
        </form>
    </div>
</div>
</body>
</html>