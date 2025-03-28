<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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

// Récupération des utilisateurs hors admin
$users = $mysqli->query("SELECT user.id_user, user.mail, metiers.nom_metier, services.libelle_service FROM user INNER JOIN metiers ON user.id_metiers = metiers.id_metier INNER JOIN services ON user.id_service = services.num_serv WHERE metiers.nom_metier != 'Admin'");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Utilisateurs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 shadow-lg rounded-lg">
        <h2 class="text-2xl font-bold mb-4">Liste des Utilisateurs</h2>
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Email</th>
                    <th class="border p-2">Métier</th>
                    <th class="border p-2">Service</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td class="border p-2 text-center"><?php echo $user['id_user']; ?></td>
                    <td class="border p-2 text-center"><?php echo htmlspecialchars($user['mail']); ?></td>
                    <td class="border p-2 text-center"><?php echo htmlspecialchars($user['nom_metier']); ?></td>
                    <td class="border p-2 text-center"><?php echo htmlspecialchars($user['libelle_service']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>