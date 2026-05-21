<?php
$db = new PDO('pgsql:host=10.11.159.10;dbname=2026_SAE_SIG', 'admindbetu', 'admindbetu');

$dep_id = $_GET['dep'] ?? '';

if (!$dep_id) {
    echo json_encode([]);
    exit;
}

$req = $db->prepare("
   SELECT DISTINCT sa.id_station, sa.nom_station
    FROM station_a sa
    JOIN commune_a c
	ON CAST(sa.id_commune AS VARCHAR) = c.id_commune
	 JOIN departement_a d ON c.id_departement_a = d.id_departement_a
    WHERE d.id_departement_a =?
    ORDER BY sa.nom_station ASC
	
");
$req->execute([$dep_id]);
$stations = $req->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($stations);
