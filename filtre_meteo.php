<?php
require("config.php");

$date_deb = isset($_POST['date_deb']) ? $_POST['date_deb'] : null;
$date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : null;
$sql = "
SELECT lon, lat, temperature, force_vent, humidite,date_heure_mesure
    FROM meteo_journalier
	where 1=1
";


if ($date_deb != '') {
    $sql .= " AND date_heure_mesure >= '$date_deb'";
   
}

if ($date_fin != '') {
    $sql .= "AND date_heure_mesure <= '$date_fin'";
    
}

$sql .= " ORDER BY RANDOM() LIMIT 2000";

$req = $db->prepare($sql);
$req->execute();

$f = [];
while ($l = $req->fetch(PDO::FETCH_ASSOC)) {
    $f[] = [
        "type" => "Feature",
        "geometry" => [
            "type" => "Point",
            "coordinates" => [$l['lon'], $l['lat']]
        ],
        "properties" => [
            "temperature" => $l['temperature'],
            "humidite" => $l['humidite'],
            "force_vent" => $l['force_vent']
        ]
    ];
}

echo json_encode([
    "type" => "FeatureCollection",
    "features" => $f
]);
