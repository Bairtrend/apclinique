<?php
session_start();

$servername = 'localhost';
$username = 'sio';
$password = 'sio';
$dbname = 'cliniquelpfs';

// Activer le mode d'erreur pour MySQL
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Établir une connexion à la base de données MySQL
$mysqli = mysqli_connect($servername, $username, $password, $dbname);

// Vérifie si la connexion a réussi
if (!$mysqli) {
    die("La connexion a échoué: " . mysqli_connect_error());
}

if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['id_metier'])) {
    $mail = $_POST['email'];
    $mdp = $_POST['password'];
    $id_metier = $_POST['id_metier']; // Récupère l'ID du métier

    // Vérifier si le mot de passe satisfait les critères
    if (strlen($mdp) >= 12 && preg_match('/[a-z]/', $mdp) && preg_match('/[A-Z]/', $mdp) && preg_match('/[0-9]/', $mdp) && preg_match('/[^a-zA-Z0-9]/', $mdp)) {
        // Hasher le mot de passe
        $hashed_password = password_hash($mdp, PASSWORD_DEFAULT);

        // Insérer les données dans la base de données
        $sql = "INSERT INTO `user` (`mail`, `mdp`, `id_metiers`) VALUES ('$mail', '$hashed_password', '$id_metier')";
        $stmt = $mysqli->prepare($sql);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            echo "Erreur lors de l'insertion dans la base de données : " . $stmt->error;
        }
    } else {
        echo "<p class='ml-80 text-red-500 font-bold fixed text-center '>Le mot de passe doit contenir au moins 16 caractères, y compris des minuscules, des majuscules, des chiffres et des symboles.</p>";
    }
}
?>





<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://hcaptcha.com/1/api.js" async defer></script>

    <script>
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("password");
            var checkbox = document.getElementById("show-password");
            if (checkbox.checked) {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
    </script>
</head>
<body class="bg-gray-100">

    <!-- Inclusion du header en haut de la page -->
    <?php include 'header.php'; ?>

    <!-- Contenu de la page -->
    <div class="flex items-center justify-center min-h-screen">
        <div class="w-full max-w-sm bg-white rounded-lg shadow-md p-6">
            <?php
            // Affichage des erreurs ou des messages d'information
            if (isset($error_message)) {
                echo "<p class='text-red-500 text-sm mb-4'>$error_message</p>";
            }
            ?>
            
            <h2 class="text-2xl font-bold text-center mb-6">Inscription</h2>
            <form method="post" action="" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email :</label>
                    <input type="email" id="email" name="email" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe :</label>
                    <input type="password" id="password" name="password" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-2">Le mot de passe doit comporter au moins 16 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.</p>
                    
                    <!-- Ajout de la case à cocher pour afficher/masquer le mot de passe -->
                    <div class="mt-2">
                        <input type="checkbox" id="show-password" onclick="togglePasswordVisibility()">
                        <label for="show-password" class="text-sm text-gray-700">Afficher le mot de passe</label>
                    </div>
                </div>

                <!-- Ajout de la liste déroulante pour les métiers -->
                <div>
                    <label for="metier" class="block text-sm font-medium text-gray-700">Métier :</label>
                    <select id="metier" name="id_metier" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" disabled selected>Choisissez un métier</option>
                        <option value="1">Admin</option>
                        <option value="2">Médecin</option>
                        <option value="3">Secrétaire</option>       
                        <option value="4">Directeur clinique</option>     
                    </select>
                </div>

                <div>
                    <button type="submit" name="submitLogin" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        S'inscrire
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
