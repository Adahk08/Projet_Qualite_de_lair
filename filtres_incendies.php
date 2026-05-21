<?php
require("config.php");

$region = isset($_POST['region']) && $_POST['region'] != '' ? $_POST['region'] : null;
$dep    = isset($_POST['dep'])    && $_POST['dep']    != '' ? $_POST['dep']    : null;
$com    = isset($_POST['com'])    && $_POST['com']    != '' ? $_POST['com']    : null;

$sql = "SELECT i.nom_commune, i.etendue_incendie, i.date_incendie,
               d.nom_departement, r.nom_region,
               ST_X(ST_Centroid(c.geom_geom)) AS lon,
               ST_Y(ST_Centroid(c.geom_geom)) AS lat
        FROM incendie_a i
        JOIN commune_a_1 a ON i.nom_commune = a.nom_commune
        JOIN commune_c_1 c ON a.id_commune  = c.id_commune_1
        JOIN departement d ON a.id_departement = d.id_departement
        JOIN region r      ON d.id_region      = r.id_region
        WHERE 1=1";

if ($region !== null) {
    $sql .= " AND r.id_region = '$region'";
}

if ($dep !== null) {
    $sql .= " AND d.id_departement = '$dep'";
}

// CORRECTION : LIKE au lieu de = pour la commune
if ($com !== null) {
    $sql .= " AND i.nom_commune LIKE '%$com%'";
}

$req = $db->query($sql);
$f = [];
while ($l = $req->fetch()) {
    $f[] = [
        "type" => "Feature",
        "geometry" => [
            "type"        => "Point",
            "coordinates" => [$l['lon'], $l['lat']]
        ],
        "properties" => [
            "etendue"     => $l['etendue_incendie'],
            "commune"     => $l['nom_commune'],
            "departement" => $l['nom_departement'],
            "region"      => $l['nom_region'],
            "date"        => $l['date_incendie']
        ]
    ];
}

echo json_encode(["type" => "FeatureCollection", "features" => $f]);
?>