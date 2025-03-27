<?php
session_start();

// Activer les rapports d'erreurs pour MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Connexion à la base de données
$servername = 'localhost';
$username = 'sio';
$password = 'sio';
$dbname = 'cliniquelpfs';

try {
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    if ($mysqli->connect_error) {
        throw new Exception("La connexion a échoué: " . $mysqli->connect_error);
    }
} catch (Exception $e) {
    die($e->getMessage());
}

// Récupérer les services disponibles
try {
    $services_result = $mysqli->query("SELECT num_serv, libelle_service FROM services");
    if (!$services_result) {
        throw new Exception("Erreur lors de la récupération des services: " . $mysqli->error);
    }
    $services = [];
    while ($row = $services_result->fetch_assoc()) {
        $services[] = $row;
    }

    // Récupérer les médecins disponibles
    $medecins_result = $mysqli->query("SELECT id_med, nom_med, prenom_med FROM medecin");
    if (!$medecins_result) {
        throw new Exception("Erreur lors de la récupération des médecins: " . $mysqli->error);
    }
    $medecins = [];
    while ($row = $medecins_result->fetch_assoc()) {
        $medecins[] = $row;
    }
} catch (Exception $e) {
    die($e->getMessage());
}

// Initialisation
$error_message = null;
$success_message = null;
$current_date = date('Y-m-d');

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num_secu = $_POST['num_secu'];
    $sexe = $_POST['sexe'];
    $nom_de_naissance = $_POST['nom_de_naissance'];
    $nom_depouse = $_POST['nom_depouse'] ?? '';
    $prenom = $_POST['prenom'];
    $date_de_naissance = $_POST['date_de_naissance'];
    $adresse = $_POST['adresse'];
    $cp = $_POST['cp'];
    $ville = $_POST['ville'];
    $email = $_POST['email'];
    $tel = $_POST['tel'];
    $date_preadmission = $_POST['date_preadmission'];
    $heure_preadmission = $_POST['heure_preadmission'];
    $num_serv = $_POST['num_serv'];
    $id_med = $_POST['id_med'];

    // Vérifications
    if (!preg_match('/^\d{15}$/', $num_secu)) {
        $error_message = "Le numéro de sécurité sociale doit comporter exactement 15 chiffres.";
    } elseif ($sexe === 'homme' && $num_secu[0] != '1') {
        $error_message = "Le numéro de sécurité sociale pour un homme doit commencer par 1.";
    } elseif ($sexe === 'femme' && $num_secu[0] != '2') {
        $error_message = "Le numéro de sécurité sociale pour une femme doit commencer par 2.";
    } elseif (new DateTime($date_de_naissance) > new DateTime($current_date)) {
        $error_message = "La date de naissance ne peut pas être dans le futur.";
    } elseif (!preg_match('/^(06|07)\d{8}$/', $tel)) {
        $error_message = "Le numéro de téléphone doit comporter 10 chiffres et commencer par 06 ou 07.";
    } elseif (new DateTime($date_preadmission) <= new DateTime($current_date)) {
        $error_message = "La date de pré-admission doit être dans le futur.";
    } else {
        // Insertion dans la base
        try {
            $stmt = $mysqli->prepare("INSERT INTO patient (num_secu, sexe, nom_de_naissance, nom_depouse, prenom, date_de_naissance, adresse, cp, ville, email, tel, date_preadmission, heure_preadmission, num_serv, id_med)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                throw new Exception("Erreur de préparation de la requête: " . $mysqli->error);
            }

            $stmt->bind_param('sssssssssssssis', $num_secu, $sexe, $nom_de_naissance, $nom_depouse, $prenom, $date_de_naissance, $adresse, $cp, $ville, $email, $tel, $date_preadmission, $heure_preadmission, $num_serv, $id_med);

            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de l'enregistrement: " . $stmt->error);
            }

            $_SESSION['num_secu'] = $num_secu;
            $success_message = "Le patient a bien été enregistré.";
            // Redirection vers la page social.php après l'enregistrement
            header('Location: sociale.php');
            exit();
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enregistrer un patient</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        form {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
        input, select {
            width: 100%;
            margin: 5px 0;
            padding: 8px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }
        p {
            text-align: center;
            font-weight: bold;
        }
        p.success { color: green; }
        p.error { color: red; }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var dateField = document.getElementsByName("date_preadmission")[0];
            var today = new Date().toISOString().split('T')[0];
            dateField.setAttribute('min', today);
        });
    </script>
</head>
<body>

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

<?php
if ($error_message) {
    echo "<p class='error'>$error_message</p>";
} elseif ($success_message) {
    echo "<p class='success'>$success_message</p>";
}
?>

<form method="POST" action="">
    <label>Numéro Sécurité Sociale :</label>
    <input type="text" name="num_secu" maxlength="15" required pattern="\d{15}" title="Le numéro de sécurité sociale doit comporter exactement 15 chiffres.">

    <label>Sexe :</label>
    <select name="sexe" required>
        <option value="homme">Homme</option>
        <option value="femme">Femme</option>
    </select>

    <label>Nom de naissance :</label>
    <input type="text" name="nom_de_naissance" required>

    <label>Nom d'épouse (facultatif) :</label>
    <input type="text" name="nom_depouse">

    <label>Prénom :</label>
    <input type="text" name="prenom" required>

    <label>Date de naissance :</label>
    <input type="date" name="date_de_naissance" required>

    <label>Adresse :</label>
    <input type="text" name="adresse" required>

    <label>Code postal :</label>
    <input type="text" name="cp" required>

    <label>Ville :</label>
    <input type="text" name="ville" required>

    <label>Email :</label>
    <input type="email" name="email" required>

    <label>Téléphone :</label>
    <input type="text" name="tel" maxlength="10" required pattern="\d{10}" title="Le numéro de téléphone doit comporter exactement 10 chiffres.">

    <label>Date de pré-admission :</label>
    <input type="date" name="date_preadmission" required>

    <label>Heure de pré-admission :</label>
    <input type="time" name="heure_preadmission" required>

    <label>Service :</label>
    <select name="num_serv" required>
        <?php foreach ($services as $service): ?>
            <option value="<?= $service['num_serv'] ?>"><?= htmlspecialchars($service['libelle_service']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Médecin :</label>
    <select name="id_med" required>
        <?php foreach ($medecins as $medecin): ?>
            <option value="<?= $medecin['id_med'] ?>"><?= htmlspecialchars($medecin['nom_med'] . ' ' . $medecin['prenom_med']) ?></option>
        <?php endforeach; ?>
    </select>

    <input type="submit" value="Enregistrer">
</form>

</body>
</html>