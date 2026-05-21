<?php
$db = new PDO('pgsql:host=10.11.159.10;dbname=2026_SAE_SIG', 'admindbetu', 'admindbetu');

$region_id = $_GET['region'] ?? '';

if (!$region_id) {
    echo json_encode([]);
    exit;
}

$req = $db->prepare("
     SELECT DISTINCT d.id_departement_a, d.nom_departement
    FROM departement_a d
    JOIN commune_a c ON c.id_departement_a = d.id_departement_a
    JOIN station_a sa 
		ON CAST(sa.id_commune AS VARCHAR) = c.id_commune  
    JOIN mesure_particule m ON sa.id_station = m.id_station
	JOIN region_a r ON r.id_region_a = d.id_region_a 
    WHERE r.id_region_a =?
    ORDER BY d.nom_departement ASC
	
");
$req->execute([$region_id]);
$departements = $req->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($departements);
