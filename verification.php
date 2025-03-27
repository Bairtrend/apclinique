<?php
session_start();
$mysqli = new mysqli('localhost', 'root', 'sio2024', 'cliniquelpfs');

if (isset($_POST['submitVerification'])) {
    $userCode = $_POST['verification_code'];

    // Vérifier si le code entré correspond à celui généré
    if ($userCode == $_SESSION['verification_code']) {
        // Authentification réussie
        unset($_SESSION['verification_code']);  // Supprimer le code de la session
        $_SESSION['user'] = $_SESSION['user_id'];  // Utilisateur authentifié

        // Redirection vers la page de pré-admission
        header("Location: preadmission.php");
        exit();
    } else {
        $error_message = "Code de vérification incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification à deux facteurs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-sm bg-white rounded-lg shadow-md p-6">
        <?php
        if (isset($error_message)) {
            echo "<p class='text-red-500 text-sm mb-4'>$error_message</p>";
        }
        ?>
        
        <h2 class="text-2xl font-bold text-center mb-6">Vérification à deux facteurs</h2>
        <form method="post" action="" class="space-y-4">
            <div>
                <label for="verification_code" class="block text-sm font-medium text-gray-700">Code de vérification :</label>
                <input type="text" id="verification_code" name="verification_code" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <button type="submit" name="submitVerification" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Vérifier
                </button>
            </div>
        </form>
    </div>
</body>
</html>
