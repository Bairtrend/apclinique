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

// Ajout d'un service
if (isset($_POST['add_service'])) {
    $libelle = $mysqli->real_escape_string($_POST['libelle']);
    $mysqli->query("INSERT INTO services (libelle_service) VALUES ('$libelle')");
    header("Location: admin_services.php");
    exit();
}

// Modification d'un service
if (isset($_POST['edit_service'])) {
    $id = intval($_POST['service_id']);
    $libelle = $mysqli->real_escape_string($_POST['libelle']);
    $mysqli->query("UPDATE services SET libelle_service = '$libelle' WHERE num_serv = $id");
    header("Location: admin_services.php");
    exit();
}

// Suppression d'un service
if (isset($_POST['delete_service'])) {
    $id = intval($_POST['service_id']);
    $mysqli->query("DELETE FROM services WHERE num_serv = $id");
    header("Location: admin_services.php");
    exit();
}

// Récupération des services
$services = $mysqli->query("SELECT * FROM services");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-3xl mx-auto bg-white p-6 shadow-lg rounded-lg">
        <h2 class="text-2xl font-bold mb-4">Gestion des Services</h2>

        <!-- Formulaire d'ajout -->
        <form method="POST" class="mb-4 flex gap-2">
            <input type="text" name="libelle" required placeholder="Nom du service" class="border p-2 w-full rounded">
            <button type="submit" name="add_service" class="bg-green-500 text-white px-4 py-2 rounded">Ajouter</button>
        </form>

        <!-- Liste des services -->
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Nom du service</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($service = $services->fetch_assoc()): ?>
                <tr>
                    <td class="border p-2 text-center"><?php echo $service['num_serv']; ?></td>
                    <td class="border p-2 text-center"><?php echo htmlspecialchars($service['libelle_service']); ?></td>
                    <td class="border p-2 text-center flex justify-center gap-2">
                        <!-- Modifier -->
                        <form method="POST" class="inline-block">
                            <input type="hidden" name="service_id" value="<?php echo $service['num_serv']; ?>">
                            <input type="text" name="libelle" value="<?php echo htmlspecialchars($service['libelle_service']); ?>" class="border p-1 rounded">
                            <button type="submit" name="edit_service" class="bg-blue-500 text-white px-3 py-1 rounded">Modifier</button>
                        </form>
                        
                        <!-- Supprimer -->
                        <form method="POST" class="inline-block">
                            <input type="hidden" name="service_id" value="<?php echo $service['num_serv']; ?>">
                            <button type="submit" name="delete_service" class="bg-red-500 text-white px-3 py-1 rounded" onclick="return confirm('Êtes-vous sûr ?');">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
