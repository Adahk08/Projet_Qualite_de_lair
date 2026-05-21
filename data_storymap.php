<?php
$connexion = new PDO(
    'pgsql:host=10.11.159.10;dbname=zaza', 'admindbetu', 'admindbetu'
);

/* ================= DONNÉES PAGE ================= */
$meteo_journalier = $connexion->query("
    SELECT lon, lat, temperature, force_vent, humidite
    FROM meteo_journalier ORDER BY RANDOM() LIMIT 500
")->fetchAll(PDO::FETCH_ASSOC);

$regions = $connexion->query("
    SELECT id_region, nom_region FROM region ORDER BY nom_region
")->fetchAll(PDO::FETCH_ASSOC);

$particules = $connexion->query("
    SELECT id_particule, nom_particule FROM particule ORDER BY id_particule
")->fetchAll(PDO::FETCH_ASSOC);

$parcs = $connexion->query("
    SELECT geom_geojson FROM parc_reserve WHERE geom_geojson IS NOT NULL ORDER BY RANDOM() LIMIT 500
")->fetchAll(PDO::FETCH_ASSOC);

$forets = $connexion->query("
    SELECT geom_geojson FROM forets WHERE geom_geojson IS NOT NULL ORDER BY RANDOM() LIMIT 5000
")->fetchAll(PDO::FETCH_ASSOC);

$sites = $connexion->query("
    SELECT nom_etablissement, nom_region, x_wgs84, y_wgs84
    FROM site_industriel WHERE x_wgs84 IS NOT NULL AND y_wgs84 IS NOT NULL
    ORDER BY RANDOM() LIMIT 1000
")->fetchAll(PDO::FETCH_ASSOC);

$incendies = $connexion->query("
    SELECT i.nom_commune, i.etendue_incendie, i.date_incendie,
           ST_X(ST_Centroid(c.geom_geom)) lon,
           ST_Y(ST_Centroid(c.geom_geom)) lat
    FROM incendie_a i
    JOIN commune_a_1 a ON i.nom_commune = a.nom_commune
    JOIN commune_c_1 c ON a.id_commune = c.id_commune_1
")->fetchAll(PDO::FETCH_ASSOC);

