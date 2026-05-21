<?php
$connexion = new PDO(
    'pgsql:host=10.11.159.10;dbname=2026_SAE_SIG', 'admindbetu', 'admindbetu'
);

header('Content-Type: application/json');

$date_debut = $_POST['date_debut'] ?? null;
$date_fin   = $_POST['date_fin']   ?? null;
$polluant   = $_POST['polluant']   ?? null;

if (!$date_debut || !$date_fin) {
    echo json_encode(['error' => 'Dates manquantes']);
    exit;
}

// Construire la liste des semaines
$semaines = [];
$current  = new DateTime($date_debut);
$end      = new DateTime($date_fin);
while ($current <= $end) {
    $semaines[] = $current->format('Y-m-d');
    $current->modify('+1 week');
}

$polluantClause = $polluant ? "AND p.nom_particule = :polluant" : '';

$result = [];
foreach ($semaines as $sem_debut) {
    $semDt  = new DateTime($sem_debut);
    $semFin = clone $semDt;
    $semFin->modify('+6 days');
    $sem_fin_str = $semFin->format('Y-m-d');

    $sql = "
        SELECT
            sa.id_station,
            sa.nom_station,
            p.nom_particule,
            AVG(m.valeur_mesure_particule) AS valeur_mesure,
            q.valeur_min,
            q.valeur_max,
            ROUND(
                ((AVG(m.valeur_mesure_particule) - q.valeur_min) / NULLIF(q.valeur_max - q.valeur_min, 0)) * 100
            ) AS iqa,
            i.couleur_indicateur,
            ST_AsGeoJSON(sc.geom) AS geom
        FROM station_a sa
        JOIN station_c sc ON sa.id_station = sc.id_station
        JOIN mesure_particule m ON sa.id_station = m.id_station
        JOIN particule p ON m.id_particule = p.id_particule
        JOIN qualifier q ON q.id_particule = p.id_particule
        JOIN indicateur i ON i.id_indicateur = q.id_indicateur
        WHERE m.valeur_mesure_particule IS NOT NULL
          AND m.valeur_mesure_particule BETWEEN q.valeur_min AND q.valeur_max
          AND DATE(m.date_mesure_particule) BETWEEN :debut AND :fin
          $polluantClause
        GROUP BY sa.id_station, sa.nom_station, p.nom_particule,
                 q.valeur_min, q.valeur_max, i.couleur_indicateur, sc.geom
        LIMIT 2000
    ";

    $stmt = $connexion->prepare($sql);
    $stmt->bindParam(':debut', $sem_debut);
    $stmt->bindParam(':fin',   $sem_fin_str);
    if ($polluant) $stmt->bindParam(':polluant', $polluant);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $features = [];
    foreach ($rows as $l) {
        $features[] = [
            "type"     => "Feature",
            "geometry" => json_decode($l['geom'], true),
            "properties" => [
                "id_station" => $l['id_station'],
                "nom"        => $l['nom_station'],
                "polluant"   => $l['nom_particule'],
                "valeur"     => round((float)$l['valeur_mesure'], 2),
                "iqa"        => (int)$l['iqa'],
                "couleur"    => $l['couleur_indicateur'],
            ]
        ];
    }

    $result[] = [
        'semaine_debut' => $sem_debut,
        'semaine_fin'   => $sem_fin_str,
        'geojson'       => ['type' => 'FeatureCollection', 'features' => $features]
    ];
}

echo json_encode($result);
