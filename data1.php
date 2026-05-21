<?php
$connexion = new PDO(
    'pgsql:host=10.11.159.10;dbname=zaza', 'admindbetu', 'admindbetu'
);

$meteo_journalier = $connexion->query("
    SELECT lon, lat, temperature, force_vent, humidite
    FROM meteo_journalier
	ORDER BY RANDOM()
    LIMIT 500
")->fetchAll(PDO::FETCH_ASSOC);


/* ================= REGIONS ================= */


/* ================= PARTICULES ================= */
$particules = $connexion->query("
SELECT id_particule, nom_particule FROM particule ORDER BY id_particule
")->fetchAll(PDO::FETCH_ASSOC);

/* ================= STATIONS ================= */

$req = $connexion->query("
SELECT 
    sa.id_station,
    sa.nom_station, 
    p.nom_particule, 
    m.valeur_mesure,
    m.date_jjmmaahh,
    q.valeur_min,
    q.valeur_max,
    ROUND(
        ((m.valeur_mesure - q.valeur_min) / (q.valeur_max - q.valeur_min)) * 100
    ) AS iqa,
    i.couleur_indicateur,
    ST_AsGeoJSON(sc.point) AS geom
FROM station_a sa
JOIN station_c sc ON sa.id_station = sc.id_station
JOIN mesurer m ON sa.id_station = m.id_station
JOIN particule p ON m.id_particule = p.id_particule
JOIN qualifier q ON q.id_particule = p.id_particule
JOIN indicateur i ON i.id_idicateur = q.id_idicateur
WHERE m.valeur_mesure IS NOT NULL
  AND m.valeur_mesure BETWEEN q.valeur_min AND q.valeur_max
limit 5000
");

$featuresStations = [];
while ($l = $req->fetch(PDO::FETCH_ASSOC)) {
    $featuresStations[] = [
        "type" => "Feature",
        "geometry" => json_decode($l['geom'], true),
        "properties" => [
		"id_station" => $l['id_station'],
		"nom" => $l['nom_station'],
		"polluant" => $l['nom_particule'],
		"valeur" => (float)$l['valeur_mesure'],
		"iqa" => (int)$l['iqa'],
		"couleur" => $l['couleur_indicateur'],
		"date" => $l['date_jjmmaahh']
		]

    ];
}

$geojsonStations = ["type"=>"FeatureCollection","features"=>$featuresStations];


/* ================= PARCS ================= */
$parcs = $connexion->query("
SELECT geom_geojson FROM parc_reserve WHERE geom_geojson IS NOT NULL ORDER BY RANDOM() LIMIT 500
")->fetchAll(PDO::FETCH_ASSOC);

/* ================= FORETS ================= */
$forets = $connexion->query("
    SELECT geom_geojson FROM forets WHERE geom_geojson IS NOT NULL ORDER BY RANDOM() LIMIT 5000
")->fetchAll(PDO::FETCH_ASSOC);

/* ================= SITES ================= */
$sites = $connexion->query("
SELECT nom_etablissement, nom_region, x_wgs84, y_wgs84
FROM site_industriel
WHERE x_wgs84 IS NOT NULL AND y_wgs84 IS NOT NULL
ORDER BY RANDOM()
LIMIT 1000
")->fetchAll(PDO::FETCH_ASSOC);

/* ================= INCENDIES ================= */
$incendies = $connexion->query("
SELECT i.nom_commune, i.etendue_incendie, i.date_incendie,
       ST_X(ST_Centroid(c.geom_geom)) lon,
       ST_Y(ST_Centroid(c.geom_geom)) lat
FROM incendie_a i
JOIN commune_a_1 a ON i.nom_commune=a.nom_commune
JOIN commune_c_1 c ON a.id_commune=c.id_commune_1
")->fetchAll(PDO::FETCH_ASSOC);



/* ================= STATISTIQUES ================= */
/* ================= STATISTIQUES ================= */
$correlations=[]; $evolutions=[]; $tops=[]; $moyennes=[];

foreach($particules as $p){
    $id=$p['id_particule'];
    
    // Remplacer corrélations par moyennes
    $moyennes[$id]=$connexion->query("
    SELECT AVG(valeur_mesure) moyenne
    FROM mesurer 
    WHERE id_particule=$id AND valeur_mesure IS NOT NULL
    ")->fetch(PDO::FETCH_ASSOC);
    
    $evolutions[$id]=$connexion->query("
    SELECT DATE(date_jjmmaahh) jour, AVG(valeur_mesure) v
    FROM mesurer WHERE id_particule=$id
    GROUP BY jour ORDER BY jour
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $tops[$id]=$connexion->query("
    SELECT c.nom_commune, AVG(m.valeur_mesure) v
    FROM mesurer m
    JOIN station_a s ON m.id_station=s.id_station
    JOIN commune_a_1 c ON s.id_commune=c.id_commune
    WHERE m.id_particule=$id
    GROUP BY c.nom_commune
    ORDER BY v DESC LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
}