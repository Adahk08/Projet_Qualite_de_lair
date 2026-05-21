
<?php
$connexion = new PDO(
    'pgsql:host=10.11.159.10;dbname=2026_SAE_SIG', 'admindbetu', 'admindbetu'
);

$regions = $connexion->query("
SELECT id_region_a, nom_region FROM region_a ORDER BY nom_region
")->fetchAll(PDO::FETCH_ASSOC);

$communes = $connexion->query("
SELECT id_commune, nom_commune FROM commune_a Order by nom_commune")->fetchAll(PDO::FETCH_ASSOC);
