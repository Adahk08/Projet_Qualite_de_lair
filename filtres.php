<?php
require('config.php');

$polluants = isset($_POST['polluant']) ? $_POST['polluant'] : [];
$date_deb  = $_POST['date_deb'] ?? '';
$date_fin  = $_POST['date_fin'] ?? '';
$region    = $_POST['region'] ?? '';
$dep       = $_POST['dep'] ?? '';
$station   = $_POST['station'] ?? '';

$features = [];

if (!empty($polluants)) {
    foreach ($polluants as $pol) {
        // Préparation de la requête SQL
        $sql = "
        SELECT 
            sa.nom_station, 
            p.nom_particule, 
            m.valeur_mesure,
            ST_AsGeoJSON(sc.point) AS geom,
            p.id_particule,
            i.couleur_indicateur,
            r.nom_region,
            d.nom_departement,
            c.nom_commune
        FROM station_a sa
        JOIN station_c sc ON sa.id_station = sc.id_station
        JOIN mesurer m ON sa.id_station = m.id_station
        JOIN particule p ON m.id_particule = p.id_particule
        LEFT JOIN qualifier q ON q.id_particule = p.id_particule
        LEFT JOIN indicateur i ON i.id_idicateur = q.id_idicateur
        JOIN commune_a_1 c ON sa.id_commune = c.id_commune
        JOIN departement d ON c.id_departement = d.id_departement
        JOIN region r ON d.id_region = r.id_region
        WHERE m.valeur_mesure IS NOT NULL 
          AND m.valeur_mesure >= q.valeur_min 
          AND m.valeur_mesure <= q.valeur_max
          AND p.nom_particule = ?
        LIMIT 1000";
        
        $sth = $db->prepare($sql);
        if (!$sth) {
            die("Erreur de préparation de la requête SQL");
        }

        $sth->execute([$pol]);  // Exécuter la requête pour ce polluant spécifique

        // Collecte des résultats pour GeoJSON
        while ($ligne = $sth->fetch(PDO::FETCH_ASSOC)) {
            $features[] = [
                "type" => "Feature",
                "geometry" => json_decode($ligne['geom'], true),
                "properties" => [
                    "nom" => $ligne['nom_station'],
                    "polluant" => $ligne['nom_particule'],
                    "valeur" => (float)$ligne['valeur_mesure'],
                    "couleur_indicateur" => $ligne['couleur_indicateur'],
                    "region" => $ligne['nom_region'],
                    "departement" => $ligne['nom_departement'],
                    "commune" => $ligne['nom_commune']
                ]
            ];
        }
    }
}

// Vérification si des résultats ont été trouvés avant de renvoyer la réponse
if (!empty($features)) {
    echo json_encode([
        "type" => "FeatureCollection",
        "features" => $features
    ]);
} else {
    echo json_encode(["error" => "Aucune donnée trouvée pour les polluants sélectionnés."]);
}
?>
