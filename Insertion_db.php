<?php
$connexion = new PDO(
    'pgsql:host=10.11.159.10;dbname=2026_SAE_SIG',
    'admindbetu',
    'admindbetu'
);
$connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ─── Récupérer le tableau JSON envoyé par le JS ───────────────────────────────
$stationsJson = isset($_POST['stations']) ? $_POST['stations'] : null;

if (!$stationsJson) {
    echo json_encode(["status" => "error", "message" => "Aucune donnée reçue"]);
    exit;
}

$stations = json_decode($stationsJson, true);

if (!$stations || !is_array($stations)) {
    echo json_encode(["status" => "error", "message" => "JSON invalide"]);
    exit;
}

// ─── Charger une fois le mapping nom_particule → id_particule ─────────────────
$stmtP = $connexion->prepare("SELECT id_particule, nom_particule FROM particule");
$stmtP->execute();
$rows = $stmtP->fetchAll(PDO::FETCH_ASSOC);
$particules = array_column($rows, 'id_particule', 'nom_particule');
// ─── Requête insertion réutilisée ─────────────────────────────────────────────
$sqlStation = "
    SELECT a.id_station
    FROM station_a a
    INNER JOIN station_c c ON a.id_station = c.id_station
    ORDER BY c.geom <-> ST_SetSRID(ST_MakePoint(:lon, :lat), 4326)
    LIMIT 1
";
$stmtStation = $connexion->prepare($sqlStation);

$sqlMesurer = "
    INSERT INTO mesure_particule (id_station, id_particule, valeur_mesure_particule, date_mesure_particule)
    VALUES (:id_station, :id_particule, :valeur, :date)
    ON CONFLICT DO NOTHING
";
$stmtM = $connexion->prepare($sqlMesurer);

// ─── Boucle sur chaque station reçue ─────────────────────────────────────────
$totalInseres = 0;
$erreurs = [];

foreach ($stations as $station) {
    $lat  = isset($station['lat'])  ? (float)$station['lat']  : null;
    $lon  = isset($station['lon'])  ? (float)$station['lon']  : null;
    $date = isset($station['date']) ? $station['date']         : null;

    if (!$lat || !$lon) continue;

    // Trouver la station BDD la plus proche
    $stmtStation->execute([':lon' => $lon, ':lat' => $lat]);
    $row = $stmtStation->fetch(PDO::FETCH_ASSOC);
	

    if (!$row) {
        $erreurs[] = "Pas de station trouvée pour lat=$lat lon=$lon";
        continue;
    }

    $id_station = $row['id_station'];

    // Mapping JS → nom_particule BDD
    $mesures = [
        'PM2.5' => isset($station['pm25']) ? $station['pm25'] : null,
        'PM10'  => isset($station['pm10']) ? $station['pm10'] : null,
        'O3'    => isset($station['o3'])   ? $station['o3']   : null,
        'NO2'   => isset($station['no2'])  ? $station['no2']  : null,
        'SO2'   => isset($station['so2'])  ? $station['so2']  : null,
    ];

    foreach ($mesures as $nomParticule => $valeur) {
        if ($valeur === null || !isset($particules[$nomParticule])) continue;

        $stmtM->execute([
            ':id_station'   => $id_station,
            ':id_particule' => $particules[$nomParticule],
            ':valeur'       => $valeur,
            ':date'         => $date,
        ]);
        $totalInseres++;
    }
}

echo json_encode([
    "status"  => "success",
    "inseres" => $totalInseres,
    "erreurs" => $erreurs,
    "message" => "$totalInseres mesures insérées pour " . count($stations) . " stations"
]);