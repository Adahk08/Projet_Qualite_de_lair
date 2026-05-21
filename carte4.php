<?php session_start(); ?>
<?php include 'data1.php'; ?>
<?php include 'get_region.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Carte Qualité de l'air – Temps réel</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<link rel="stylesheet" href="../css/CSS.css">

<link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css"/>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ================= NAVBAR ================= */
.navbar {
    background-color: #38b6ff;
    color: white;
    padding: 12px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-link {
    color: white;
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
	
}
.nav-left{
	display: flex;
    gap: 20px;
    align-items: center;
	
}

.nav-link:hover {
    text-decoration: underline;
}

.btn-login {
    background-color: white;
    color: #38b6ff;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
}

.btn-login:hover {
    background-color: #e6f4ff;
}

/* ================= FOOTER ================= */
.footer {
    background-color: #38b6ff;
    color: white;
    text-align: center;
    padding: 15px;
    margin-top: 20px;
}

.iqa-label {
    color: white;
    font-weight: bold;
    font-size: 14px;
    background: none;
    border: none;
    box-shadow: none;
}




/* ================= LEGENDE IQA ================= */
.legende-iqa {
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    width: 220px;
    font-family: Arial, sans-serif;
}

.legende-iqa h4 {
    margin-bottom: 12px;
    font-size: 15px;
    text-align: center;
}

.legende-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.legende-couleur {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    margin-right: 10px;
    flex-shrink: 0;
}

</style>
</head>

<body>

<?php include('../page_admin/menu.php'); ?>

<div id="layout">
<!-- FILTRES -->
<div id="filtre">
    <h3>Critères de sélection</h3>
    
    
    <!-- Filtres historiques -->
    <div id="filtresHistorique">
        <form id="filtres">
            <label>Polluants</label><br>
            <input type="checkbox" name="polluant[]" value="O3"> O3<br>
            <input type="checkbox" name="polluant[]" value="NO2"> NO2<br>
            <input type="checkbox" name="polluant[]" value="PM10"> PM10<br>
            <input type="checkbox" name="polluant[]" value="PM2.5"> PM2.5<br>
            <input type="checkbox" name="polluant[]" value="SO2"> SO2<br>
                    
            <label>Date du :</label>
            <input type="date" name="date_deb">
            
            <label>Au :</label>
            <input type="date" name="date_fin">
            
            <label>Région</label>
            <select name="region" id="region">
                <option value="">-- Toutes les régions --</option>
                <?php foreach($regions as $r): ?>
                <option value="<?= $r['id_region_a'] ?>"><?= $r['nom_region'] ?></option>
                <?php endforeach; ?>
            </select>
            
            <label>Département</label>
            <select name="dep" id="dep">
                <option value="">-- Sélectionnez un département --</option>
            </select>
            
            <label>Station</label>
            <select name="station" id="station">
                <option value="">-- Sélectionnez une station --</option>
            </select>
            
            <label>Commune</label>
            <input type="text" name="com" placeholder="Nom de la commune">
            
            <button type="submit">Afficher</button>
        </form>
	<button type="button" id="data" onclick="chargerStationsDansDb()" style="margin-top: 10px;">
    Télécharger les données du jour
</button>
</div>
	
</div>

<!-- CARTE -->
<div id="map"></div>

</div>

<!-- STATISTIQUES -->

</div>

<footer class="footer">
    <p>IUT de Carcassonne – BUT Science des Données</p>
</footer>
<!-- SCRIPTS AJAX -->
<script src="../js/ajax1.js"></script>
<script src="../js/ajax_temps_reel.js"></script>
<div class="container">

<script>
// ================= DONNÉES PHP =================
var meteo_journalier = <?= json_encode($meteo_journalier) ?>;
var parcs = <?= json_encode($parcs) ?>;
var forets = <?= json_encode($forets) ?>;
var sites = <?= json_encode($sites) ?>;
var incendies = <?= json_encode($incendies) ?>;
var particules = <?= json_encode($particules) ?>;
var evolutions = <?= json_encode($evolutions) ?>;
var tops = <?= json_encode($tops) ?>;
var moyennes = <?= json_encode($moyennes) ?>;



// ================= CARTE =================
var map = L.map('map').setView([46.8, 2.5], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

// ================= ICONES =================
var thermBleu = L.icon({
    iconUrl: '../images/thermometre_bleu.png',
    iconSize: [25, 25]
});

var thermRouge = L.icon({
    iconUrl: '../images/thermometre_rouge.png',
    iconSize: [25, 25]
});

var eau = L.icon({
    iconUrl: '../images/eau.png',
    iconSize: [25, 25]
});

var cactus = L.icon({
    iconUrl: '../images/cactus.jpg',
    iconSize: [25, 25]
});

var iconeSite = L.icon({
    iconUrl: '../images/site_industriel.png',
    iconSize: [30, 30],
    iconAnchor: [15, 30]
});

var iconeFeu = L.icon({
    iconUrl: '../images/incendie.png',
    iconSize: [30, 30],
    iconAnchor: [15, 30]
});

// ================= LAYERS =================
var tempLayer = L.layerGroup();
var ventLayer = L.layerGroup();
var humidLayer = L.layerGroup();
var parcLayer = L.layerGroup();
var foretLayer = L.layerGroup();
var siteLayer = L.layerGroup();
var feuLayer = L.layerGroup();
var stationLayer = L.layerGroup(); // Layer pour les stations
var Station = L.layerGroup();
Station.addLayer(stationLayer);

// ================= METEO =================
meteo_journalier.forEach(p => {
    L.marker([p.lat, p.lon], {
        icon: p.temperature < 10 ? thermBleu : thermRouge
    })
    .bindPopup('Température: ' + p.temperature + '°C<br> Vent: ' + p.force_vent + '<br>Humidité: ' + p.humidite + '%')
    .addTo(tempLayer);

    L.circle([p.lat, p.lon], {
        radius: p.force_vent * 400,
        color: 'blue',
        fillOpacity: 0.3
    }).addTo(ventLayer);
    
    L.marker([p.lat, p.lon], {
        icon: p.humidite < 79.0304201027689461 ? cactus : eau
    })
    .bindPopup('Humidité: ' + p.humidite + '%')
    .addTo(humidLayer);
});

// ================= PARCS & FORETS =================
parcs.forEach(p => {
    L.geoJSON(JSON.parse(p.geom_geojson), {
        style: {color: "#32CD32", weight: 2, fillOpacity: 0.4}
    }).addTo(parcLayer);
});

forets.forEach(f => {
    L.geoJSON(JSON.parse(f.geom_geojson), {
        style: {color: "#0b6623", weight: 2, fillOpacity: 0.4}
    }).addTo(foretLayer);
});

// ================= SITES INDUSTRIELS =================
sites.forEach(s => {
    L.marker([s.y_wgs84, s.x_wgs84], {
        icon: iconeSite
    })
    .bindPopup('<b>' + s.nom_etablissement + '</b><br>Région: ' + s.nom_region)
    .addTo(siteLayer);
});

// ================= INCENDIES =================
incendies.forEach(i => {
    L.marker([i.lat, i.lon], {
        icon: iconeFeu
    })
    .bindPopup('<b>Commune:</b> ' + i.nom_commune + '<br><b>Date:</b> ' + i.date_incendie)
    .addTo(feuLayer);
});

// ================= CONTROLE DES COUCHES =================
L.control.layers(null, {
    "Stations": Station,
    "Parcs": parcLayer,
    "Forêts": foretLayer,
    "Sites industriels": siteLayer,
    "Incendies": feuLayer,
    "Température": tempLayer,
    "Vent": ventLayer,
    "Humidité": humidLayer,
}, {collapsed: false}).addTo(map);

// ================= LEGENDE IQA LEAFLET =================
var legend = L.control({position: 'bottomleft'});

legend.onAdd = function (map) {
    var div = L.DomUtil.create('div', 'legende-iqa');

    div.innerHTML =
        '<h4>Indice de qualité de l\'air</h4>' +
        '<div class="legende-item">' +
            '<div class="legende-couleur" style="background:#00e400;"></div>' +
            '<span>0-50 : Bon</span>' +
        '</div>' +
        '<div class="legende-item">' +
            '<div class="legende-couleur" style="background:#ffff00;"></div>' +
            '<span>51-100 : Moyen</span>' +
        '</div>' +
        '<div class="legende-item">' +
            '<div class="legende-couleur" style="background:#ff7e00;"></div>' +
            '<span>101-150 : Dégradé</span>' +
        '</div>' +
        '<div class="legende-item">' +
            '<div class="legende-couleur" style="background:#ff0000;"></div>' +
            '<span>151-200 : Mauvais</span>' +
        '</div>' +
        '<div class="legende-item">' +
            '<div class="legende-couleur" style="background:#8f3f97;"></div>' +
            '<span>201-300 : Très mauvais</span>' +
        '</div>' +
        '<div class="legende-item">' +
            '<div class="legende-couleur" style="background:#7e0023;"></div>' +
            '<span>300+ : Dangereux</span>' +
        '</div>';

    return div;
};


legend.addTo(map);






// ================= AJOUTER STATION LAYER AU DEMARRAGE =================
Station.addTo(map);

// ================= CHARGER TEMPS REEL AU DEMARRAGE =================
chargerStationsTempsReel();
</script>

</div>

</body>
</html>