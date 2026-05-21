<?php
require("config.php");

$region = isset($_POST['region']) && $_POST['region'] != '' ? $_POST['region'] : null;
$dep    = isset($_POST['dep'])    && $_POST['dep']    != '' ? $_POST['dep']    : null;
$com    = isset($_POST['com'])    && $_POST['com']    != '' ? $_POST['com']    : null;

$sql = "SELECT nom_etablissement, s.nom_region, nom_commune, s.nom_departement, x_wgs84, y_wgs84, r.id_region
        FROM site_industriel s
        JOIN region r ON r.nom_region = s.nom_region
        WHERE x_wgs84 IS NOT NULL AND y_wgs84 IS NOT NULL";

if ($region !== null) {
    $sql .= " AND r.id_region = '$region'";
}

if ($com !== null) {
    $nom_commune = strtoupper($com);
    $sql .= " AND nom_commune LIKE '%$nom_commune%'";
}

// CORRECTION : WHERE manquait dans la sous-requête département
if ($dep !== null) {
    $reqDep = $db->query("SELECT UPPER(nom_departement) FROM departement WHERE id_departement = '$dep'");
    $nom_dep = $reqDep->fetchColumn();
    if ($nom_dep !== false && $nom_dep !== null) {
        $sql .= " AND UPPER(s.nom_departement) = '$nom_dep'";
    }
}

// CORRECTION : espace avant LIMIT pour éviter la collision avec le WHERE précédent
$sql .= " LIMIT 5000";

$req = $db->query($sql);
$f = [];
while ($l = $req->fetch()) {
    $f[] = [
        "type" => "Feature",
        "geometry" => [
            "type" => "Point",
            "coordinates" => [$l['x_wgs84'], $l['y_wgs84']]
        ],
        "properties" => [
            "nom"        => $l['nom_etablissement'],
            "region"     => $l['nom_region'],
            "departement"=> $l['nom_departement'],
            "commune"    => $l['nom_commune']
        ]
    ];
}

echo json_encode(["type" => "FeatureCollection", "features" => $f]);
?>