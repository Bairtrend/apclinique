<?php
session_start();

$servername = 'localhost';
$username = 'sio';
$password = 'sio';
$dbname = 'cliniquelpfs';

// Activer les rapports d'erreurs pour MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = mysqli_connect($servername, $username, $password, $dbname);

if (!$mysqli) {
    die("La connexion a échoué: " . mysqli_connect_error());
}

// Hachage des mots de passe existants
$sql = "SELECT id_user, mdp FROM user";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (strlen($row['mdp']) < 20) {
            $hashedPassword = password_hash($row['mdp'], PASSWORD_DEFAULT);
            $updateSql = "UPDATE user SET mdp = '$hashedPassword' WHERE id_user = " . $row['id_user'];
            $mysqli->query($updateSql);
        }
    }
}

// Gestion de la connexion
if (isset($_POST["submitLogin"])) {
    $mail = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = $_POST['password'];
    $captcha = $_POST['captcha'];

    // Vérification du CAPTCHA
    if ($captcha !== $_SESSION['captcha_text']) {
        $error_message = "Le CAPTCHA est incorrect.";
    } else {
        // Vérification des conditions du mot de passe
        if (strlen($password) < 12 ||  // Minimum 12 caractères
            !preg_match("/[A-Z]/", $password) ||  // Au moins une majuscule
            !preg_match("/[a-z]/", $password) ||  // Au moins une minuscule
            !preg_match("/[0-9]/", $password) ||  // Au moins un chiffre
            !preg_match("/[\W]/", $password)      // Au moins un caractère spécial
        ) {
            $error_message = "Le mot de passe doit comporter au moins 12 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
        } else {
            // Si les conditions sont remplies, vérifier l'utilisateur en base de données
            $sql = "SELECT * FROM user WHERE mail = '$mail'";
            $result = $mysqli->query($sql);

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Vérification du mot de passe haché
                if (password_verify($password, $user['mdp'])) {
                    // Vérification du rôle de l'utilisateur
                    $user_id = $user['id_user'];
                    $role_sql = "SELECT id_metiers FROM user WHERE id_user = '$user_id'";
                    $role_result = $mysqli->query($role_sql);
                    $role = $role_result->fetch_assoc();

                    if ($role['id_metiers'] == 3) {
                        // L'utilisateur est une secrétaire, démarrage de la session
                        $_SESSION['user'] = $user['mail'];
                        // Redirection vers la page de pré-admission
                        header("Location: preadmission.php");
                        exit();
                    } elseif ($role['id_metiers'] == 2 || $role['id_metiers'] == 4) {
                        // L'utilisateur est un médecin ou un directeur clinique
                        $_SESSION['id_user'] = $user['id_user'];
                        $_SESSION['id_metiers'] = $role['id_metiers'];
                        header('Location: historique.php');
                        exit();
                    } else {
                        $error_message = "Accès refusé. Seuls les secrétaires, médecins et directeurs peuvent accéder à cette page.";
                    }
                } else {
                    $error_message = "Email ou mot de passe incorrect.";
                }
            } else {
                $error_message = "Aucun compte trouvé avec cet email.";
            }
        }
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
            
            <h2 class="text-2xl font-bold text-center mb-6">Connexion</h2>
            <form method="post" action="" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email :</label>
                    <input type="email" id="email" name="email" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe :</label>
                    <input type="password" id="password" name="password" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-2">Le mot de passe doit comporter au moins 12 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.</p>
                </div>

                <!-- Checkbox pour afficher/masquer le mot de passe -->
                <div class="flex items-center mt-2">
                    <input type="checkbox" id="show-password" onclick="togglePasswordVisibility()" class="h-4 w-4 text-blue-600">
                    <label for="show-password" class="ml-2 text-sm text-gray-700">Afficher le mot de passe</label>
                </div>

                <!-- CAPTCHA widget -->
                <div>
                    <label for="captcha" class="block text-sm font-medium text-gray-700">CAPTCHA :</label>
                    <img src="captcha.php" alt="CAPTCHA">
                    <input type="text" id="captcha" name="captcha" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <button type="submit" name="submitLogin" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Se connecter
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>