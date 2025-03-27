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
} catch (Exception $e) {
    die($e->getMessage());
}

// Récupérer les préadmissions filtrées par service et mois
$selected_service = isset($_GET['service']) ? $_GET['service'] : '';
$selected_month = isset($_GET['month']) ? $_GET['month'] : '';

$query = "SELECT p.*, m.nom_med, m.prenom_med FROM patient p LEFT JOIN medecin m ON p.id_med = m.id_med";
$conditions = [];

if ($selected_service != '') {
    $conditions[] = "p.num_serv = " . intval($selected_service);
}

if ($selected_month != '') {
    $conditions[] = "MONTH(p.date_preadmission) = " . intval($selected_month);
}

if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

try {
    $result = $mysqli->query($query);
    if (!$result) {
        throw new Exception("Erreur lors de la récupération des préadmissions: " . $mysqli->error);
    }
    $preadmissions = [];
    while ($row = $result->fetch_assoc()) {
        $preadmissions[] = $row;
    }
} catch (Exception $e) {
    die($e->getMessage());
}

// Mois en français
$mois = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
    7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Preadmissions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <!-- Inclusion du header en haut de la page -->
    <?php include 'header.php'; ?>

    <!-- Contenu de la page -->
    <div class="container mx-auto py-8 px-4">
        <h1 class="text-3xl font-bold mb-6 text-center">Historique des Preadmissions</h1>
        
        <!-- Formulaire de filtrage par service et mois -->
        <form method="GET" action="historique.php" class="mb-8">
            <div class="flex flex-wrap justify-center space-y-4 md:space-y-0 md:space-x-4 mb-4">
                <div class="flex items-center">
                    <label for="service" class="mr-2 text-sm font-medium text-gray-700">Filtrer par service :</label>
                    <select name="service" id="service" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous les services</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?= $service['num_serv'] ?>" <?= $service['num_serv'] == $selected_service ? 'selected' : '' ?>>
                                <?= htmlspecialchars($service['libelle_service']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center">
                    <label for="month" class="mr-2 text-sm font-medium text-gray-700">Filtrer par mois :</label>
                    <select name="month" id="month" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous les mois</option>
                        <?php foreach ($mois as $m => $nom): ?>
                            <option value="<?= $m ?>" <?= $m == $selected_month ? 'selected' : '' ?>>
                                <?= $nom ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">Filtrer</button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numéro Sécurité Sociale</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sexe</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom de Naissance</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom d'Épouse</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prénom</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date de Naissance</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adresse</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code Postal</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ville</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date de Preadmission</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Heure de Preadmission</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Médecin</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($preadmissions)): ?>
                        <tr>
                            <td colspan="15" class="py-4 px-6 text-center text-sm text-gray-500">Aucune préadmission trouvée.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($preadmissions as $preadmission): ?>
                            <tr>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['num_secu']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['sexe']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['nom_de_naissance']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['nom_depouse']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['prenom']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['date_de_naissance']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['adresse']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['cp']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['ville']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['email']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['tel']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['date_preadmission']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['heure_preadmission']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['num_serv']) ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?= htmlspecialchars($preadmission['nom_med'] . ' ' . $preadmission['prenom_med']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>