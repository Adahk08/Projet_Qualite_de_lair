<?php
$db = new PDO('pgsql:host=10.11.159.10;dbname=2026_SAE_SIG', 'admindbetu', 'admindbetu');

$date_deb = !empty($_POST['date_deb']) ? $_POST['date_deb'] : null;
$date_fin = !empty($_POST['date_fin']) ? $_POST['date_fin'] : null;
$region   = !empty($_POST['region'])  ? $_POST['region']   : null; // ← string, pas int
$dep      = !empty($_POST['dep'])     ? $_POST['dep']      : null;
$com      = !empty($_POST['com'])     ? $_POST['com']      : null;
$station  = !empty($_POST['station']) ? (int) $_POST['station']  : null;
$polluants = !empty($_POST['polluant']) ? $_POST['polluant'] : [];

$sql = "
SELECT DISTINCT ON (sa.id_station)
    sa.id_station,
    sa.nom_station,
    d.id_departement_a,
    par.id_particule,
    r.id_region_a,
    par.nom_particule,
    m.valeur_mesure_particule,
    m.date_mesure_particule,
    q.valeur_min,
    q.valeur_max,
    ROUND(
        ((m.valeur_mesure_particule - q.valeur_min) 
        / NULLIF(q.valeur_max - q.valeur_min, 0)) * 100
    ) AS iqa,
    i.couleur_indicateur,
    c.nom_commune AS commune,
    ST_AsGeoJSON(sc.geom) AS geom
FROM station_a sa
JOIN station_c sc   ON sa.id_station = sc.id_station
JOIN mesure_particule m ON sa.id_station = m.id_station
JOIN particule par   ON m.id_particule = par.id_particule
JOIN commune_a c ON CAST(sa.id_commune AS VARCHAR) = c.id_commune    
JOIN departement_a d ON c.id_departement_a = d.id_departement_a
JOIN region_a r      ON r.id_region_a = d.id_region_a
JOIN qualifier q 
    ON q.id_particule = par.id_particule
    AND m.valeur_mesure_particule >= q.valeur_min
    AND m.valeur_mesure_particule <= q.valeur_max
JOIN indicateur i ON i.id_indicateur = q.id_indicateur
WHERE m.valeur_mesure_particule IS NOT NULL
";

// Dates
if ($date_fin !== null) {
    $sql .= " AND DATE(m.date_mesure_particule) = '$date_fin'";
} elseif ($date_deb !== null) {
    $sql .= " AND DATE(m.date_mesure_particule) >= '$date_deb'";
}

// Région — compare directement sans CAST
if ($region !== null) {
    $sql .= " AND r.id_region_a = '$region'";
}

// Département
if ($dep !== null) {
    $sql .= " AND d.id_departement_a = '$dep'";
}

// Commune
if ($com !== null && $com !== '') {
    $com_esc = $db->quote('%' . $com . '%');
    $sql .= " AND c.nom_commune LIKE $com_esc";
}

// Station
if ($station !== null) {
    $sql .= " AND sa.id_station = '$station'";
}

// Polluants cochés
if (!empty($polluants)) {
    $placeholders = implode(',', array_map([$db, 'quote'], $polluants));
    $sql .= " AND par.nom_particule IN ($placeholders)";
}

// OBLIGATOIRE avec DISTINCT ON
$sql .= " ORDER BY sa.id_station, m.date_mesure_particule DESC";
$sql .= " LIMIT 2000";

$req = $db->query($sql);
if ($req === false) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur SQL", "detail" => $db->errorInfo()]);
    exit;
}

$features = [];
while ($l = $req->fetch(PDO::FETCH_ASSOC)) {
    $features[] = [
        "type" => "Feature",
        "geometry" => json_decode($l['geom'], true),
        "properties" => [
            "id_station"   => $l['id_station'],
            "id_particule" => $l['id_particule'],
            "nom"          => $l['nom_station'],
            "polluant"     => $l['nom_particule'],
            "valeur"       => (float)$l['valeur_mesure_particule'],
            "iqa"          => (int)$l['iqa'],
            "commune"      => $l['commune'],
            "date"         => $l['date_mesure_particule'],
            "couleur_indicateur" => $l['couleur_indicateur']
        ]
    ];
}

echo json_encode([
    "type" => "FeatureCollection",
    "features" => $features
]);