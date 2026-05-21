<?php session_start(); ?>
<?php include 'data_storymap.php'; ?>
<?php include('../page_admin/menu.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Story Map – Évolution qualité de l'air</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<link rel="stylesheet" href="../css/CSS.css">
<link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css"/>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="../css/css_story_map.css">
<style>

</style>
</head>

<body>

<div id="layout">

    <!-- ================= FILTRES (gauche) ================= -->
    <div id="filtre">
        <h3>Critères de sélection</h3>

        <div id="filtresHistorique">
            <form id="filtres">
                <label>Région</label>
                <select name="region" id="region">
                    <option value="">-- Toutes les régions --</option>
                    <?php foreach($regions as $r): ?>
                    <option value="<?= $r['id_region'] ?>"><?= $r['nom_region'] ?></option>
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
        </div>

        <!-- ================= STORY MAP PANEL ================= -->
        <div id="story-panel">
            <h3>Story Map</h3>

            <label>Polluant principal</label>
            <select id="story-polluant">
                <option value="">-- Tous les polluants --</option>
                <?php foreach($particules as $p): ?>
                <option value="<?= htmlspecialchars($p['nom_particule']) ?>">
                    <?= htmlspecialchars($p['nom_particule']) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <label>Date début</label>
            <input type="date" id="story-date-debut">

            <label>Date fin</label>
            <input type="date" id="story-date-fin">

            <button id="btn-charger-story">Charger la Story Map</button>

            <div id="story-loading"> Chargement des données...</div>

            <div id="story-controls">
                <div id="story-semaine-label">Semaine : —</div>
                <input type="range" id="story-slider" min="0" value="0">
                <div class="story-nav-btns">
                    <button id="btn-prev">back</button>
                    <button id="btn-playpause"> Play</button>
                    <button id="btn-next">next</button>
                </div>
                <div style="margin-top:6px;">
                    <label style="font-size:11px;font-weight:bold;display:block;margin-bottom:2px;">Vitesse</label>
                    <select id="story-vitesse" style="width:100%;padding:3px;border-radius:5px;border:1px solid #ccc;font-size:11px;">
                        <option value="2000">Lente (2s)</option>
                        <option value="1000" selected>Normale (1s)</option>
                        <option value="500">Rapide (0.5s)</option>
                        <option value="200">Très rapide (0.2s)</option>
                    </select>
                </div>
                <div id="story-progress"></div>
            </div>
        </div>
    </div>

    <!-- ================= CARTE (centre) ================= -->
    <div id="map"></div>

    <!-- ================= PANNEAU DROIT : graphique + récit ================= -->
    <div id="story-right-panel">
        <div id="story-right-header">
            <span>Évolution de l'IQA</span>
            <button onclick="fermerPanneauDroit()">✕</button>
        </div>

        <!-- Texte narratif -->
        <div id="story-narrative-box">
            <div id="story-narrative-icon"></div>
            <div id="story-narrative-titre">Chargement de l'histoire...</div>
            <div id="story-narrative-texte"></div>
        </div>

        <!-- Stats rapides -->
        <div id="story-stats">
            <div class="story-stat-card">
                <div class="stat-val" id="stat-iqa-val">—</div>
                <div class="stat-lbl">IQA moyen</div>
            </div>
            <div class="story-stat-card">
                <div class="stat-val" id="stat-stations-val">—</div>
                <div class="stat-lbl">Stations</div>
            </div>
            <div class="story-stat-card">
                <div class="stat-val" id="stat-iqa-min">—</div>
                <div class="stat-lbl">IQA min</div>
            </div>
            <div class="story-stat-card">
                <div class="stat-val" id="stat-iqa-max">—</div>
                <div class="stat-lbl">IQA max</div>
            </div>
        </div>

        <!-- Graphique ligne -->
        <div id="story-chart-wrap">
            <div id="story-chart-titre">Évolution semaine par semaine</div>
            <div id="story-chart-container">
                <canvas id="story-chart"></canvas>
            </div>
        </div>
    </div>

</div>

<footer class="footer">
    <p>IUT de Carcassonne – BUT Science des Données</p>
</footer>

<script src="../js/ajax1.js"></script>
<script src="../js/ajax_temps_reel.js"></script>

<script>
var meteo_journalier = <?= json_encode($meteo_journalier) ?>;
var parcs    = <?= json_encode($parcs) ?>;
var forets   = <?= json_encode($forets) ?>;
var sites    = <?= json_encode($sites) ?>;
var incendies = <?= json_encode($incendies) ?>;
var particules = <?= json_encode($particules) ?>;

// ================= CARTE =================
var map = L.map('map').setView([46.8, 2.5], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

// ================= ICONES =================
var thermBleu = L.icon({ iconUrl: '../images/thermometre_bleu.png', iconSize: [25,25] });
var thermRouge = L.icon({ iconUrl: '../images/thermometre_rouge.png', iconSize: [25,25] });
var eau    = L.icon({ iconUrl: '../images/eau.png', iconSize: [25,25] });
var cactus = L.icon({ iconUrl: '../images/cactus.jpg', iconSize: [25,25] });
var iconeSite = L.icon({ iconUrl: '../images/site_industriel.png', iconSize: [30,30], iconAnchor: [15,30] });
var iconeFeu  = L.icon({ iconUrl: '../images/incendie.png', iconSize: [30,30], iconAnchor: [15,30] });

// ================= LAYERS =================
var tempLayer    = L.layerGroup();
var ventLayer    = L.layerGroup();
var humidLayer   = L.layerGroup();
var parcLayer    = L.layerGroup();
var foretLayer   = L.layerGroup();
var siteLayer    = L.layerGroup();
var feuLayer     = L.layerGroup();
var stationLayer = L.layerGroup();
var Station      = L.layerGroup();
Station.addLayer(stationLayer);
var storyLayer = L.layerGroup().addTo(map);

// ================= DONNEES CARTE =================
meteo_journalier.forEach(p => {
    L.marker([p.lat, p.lon], { icon: p.temperature < 10 ? thermBleu : thermRouge })
     .bindPopup('Température: ' + p.temperature + '°C<br>Vent: ' + p.force_vent + '<br>Humidité: ' + p.humidite + '%')
     .addTo(tempLayer);
    L.circle([p.lat, p.lon], { radius: p.force_vent * 400, color: 'blue', fillOpacity: 0.3 }).addTo(ventLayer);
    L.marker([p.lat, p.lon], { icon: p.humidite < 79 ? cactus : eau })
     .bindPopup('Humidité: ' + p.humidite + '%').addTo(humidLayer);
});
parcs.forEach(p => {
    L.geoJSON(JSON.parse(p.geom_geojson), { style: {color:"#32CD32", weight:2, fillOpacity:0.4} }).addTo(parcLayer);
});
forets.forEach(f => {
    L.geoJSON(JSON.parse(f.geom_geojson), { style: {color:"#0b6623", weight:2, fillOpacity:0.4} }).addTo(foretLayer);
});
sites.forEach(s => {
    L.marker([s.y_wgs84, s.x_wgs84], { icon: iconeSite })
     .bindPopup('<b>' + s.nom_etablissement + '</b><br>Région: ' + s.nom_region).addTo(siteLayer);
});
incendies.forEach(i => {
    L.marker([i.lat, i.lon], { icon: iconeFeu })
     .bindPopup('<b>Commune:</b> ' + i.nom_commune + '<br><b>Date:</b> ' + i.date_incendie).addTo(feuLayer);
});

// ================= CONTROLE DES COUCHES =================
L.control.layers(null, {
    "Stations":          Station,
    "Parcs":             parcLayer,
    "Forêts":            foretLayer,
    "Sites industriels": siteLayer,
    "Incendies":         feuLayer,
    "Température":       tempLayer,
    "Vent":              ventLayer,
    "Humidité":          humidLayer,
    "Story Map":         storyLayer,
}, {collapsed: false}).addTo(map);

// ================= LEGENDE IQA =================
var legend = L.control({position: 'bottomleft'});
legend.onAdd = function(map) {
    var div = L.DomUtil.create('div', 'legende-iqa');
    div.innerHTML =
        '<h4>Indice de qualité de l\'air</h4>' +
        '<div class="legende-item"><div class="legende-couleur" style="background:#00e400;"></div><span>0-50 : Bon</span></div>' +
        '<div class="legende-item"><div class="legende-couleur" style="background:#ffff00;"></div><span>51-100 : Moyen</span></div>' +
        '<div class="legende-item"><div class="legende-couleur" style="background:#ff7e00;"></div><span>101-150 : Dégradé</span></div>' +
        '<div class="legende-item"><div class="legende-couleur" style="background:#ff0000;"></div><span>151-200 : Mauvais</span></div>' +
        '<div class="legende-item"><div class="legende-couleur" style="background:#8f3f97;"></div><span>201-300 : Très mauvais</span></div>' +
        '<div class="legende-item"><div class="legende-couleur" style="background:#7e0023;"></div><span>300+ : Dangereux</span></div>';
    return div;
};
legend.addTo(map);

Station.addTo(map);
chargerStationsTempsReel();

// ================= STORY MAP =================
var storySemaines = [];
var storyIndex    = 0;
var storyTimer    = null;
var storyPlaying  = false;
var storyChart    = null;

// Textes narratifs selon l'IQA moyen
function getNarratif(iqaMoyen, nbStations, semDeb, semFin) {
    var date = "du " + formatDate(semDeb) + " au " + formatDate(semFin);
    if (iqaMoyen <= 50) {
        return {
            
            titre: "Air excellent",
            texte: "La semaine " + date + " affiche une qualité de l'air exceptionnelle (IQA moyen : " + iqaMoyen + "). "
                 + "Sur " + nbStations + " station(s) surveillée(s), les concentrations de polluants restent très basses. "
                 + "Conditions idéales pour les activités en plein air."
        };
    } else if (iqaMoyen <= 100) {
        return {
            
            titre: "Qualité de l'air acceptable",
            texte: "Semaine " + date + " — IQA moyen de " + iqaMoyen + " sur " + nbStations + " station(s). "
                 + "La qualité de l'air est satisfaisante. Les personnes très sensibles peuvent ressentir de légères irritations."
        };
    } else if (iqaMoyen <= 150) {
        return {
            icon: "⚠️",
            titre: "Air dégradé — vigilance",
            texte: "Alerte modérée semaine " + date + " (IQA moyen : " + iqaMoyen + "). "
                 + nbStations + " station(s) enregistrent des niveaux préoccupants. "
                 + "Les personnes asthmatiques ou cardiaques doivent limiter les efforts prolongés en extérieur."
        };
    } else if (iqaMoyen <= 200) {
        return {
            
            titre: "Mauvaise qualité de l'air",
            texte: "Semaine " + date + " : qualité de l'air mauvaise (IQA moyen : " + iqaMoyen + ") sur " + nbStations + " station(s). "
                 + "Toute la population peut ressentir des effets sur la santé. "
                 + "Réduire les activités physiques intenses en extérieur est recommandé."
        };
    } else if (iqaMoyen <= 300) {
        return {
           
            titre: "Très mauvaise qualité de l'air",
            texte: "Pollution très élevée semaine " + date + " — IQA moyen de " + iqaMoyen + " sur " + nbStations + " station(s). "
                 + "Des alertes officielles peuvent être déclenchées. "
                 + "Il est fortement conseillé de rester à l'intérieur et d'aérer aux heures creuses."
        };
    } else {
        return {
            
            titre: "Danger — air dangereux",
            texte: "Niveau d'urgence atteint semaine " + date + " (IQA moyen : " + iqaMoyen + "). "
                 + "Les " + nbStations + " station(s) enregistrent des pics de pollution extrêmes. "
                 + "Restez à l'intérieur, portez un masque si vous sortez. Consultez les autorités sanitaires."
        };
    }
}

// Couleur IQA
function getCouleurIQA(iqa) {
    if (iqa <= 50)  return "#00e400";
    if (iqa <= 100) return "#ffff00";
    if (iqa <= 150) return "#ff7e00";
    if (iqa <= 200) return "#ff0000";
    if (iqa <= 300) return "#8f3f97";
    return "#7e0023";
}

// Charger les données
document.getElementById('btn-charger-story').addEventListener('click', function() {
    var debut    = document.getElementById('story-date-debut').value;
    var fin      = document.getElementById('story-date-fin').value;
    var polluant = document.getElementById('story-polluant').value;

    if (!debut || !fin) { alert('Veuillez saisir une date de début et une date de fin.'); return; }
    if (new Date(debut) > new Date(fin)) { alert('La date de début doit être avant la date de fin.'); return; }

    stopAnimation();
    document.getElementById('story-loading').style.display  = 'block';
    document.getElementById('story-controls').style.display = 'none';
    storyLayer.clearLayers();
    Station.removeFrom(map);

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
            document.getElementById('story-loading').style.display = 'none';
            if (xhr.status == 200) {
                var data = JSON.parse(xhr.responseText);
                if (!data || data.error) { alert('Erreur : ' + (data.error || 'inconnue')); return; }
                if (data.length === 0)   { alert('Aucune donnée pour cette période.'); return; }

                storySemaines = data;
                storyIndex    = 0;

                var slider = document.getElementById('story-slider');
                slider.min   = 0;
                slider.max   = storySemaines.length - 1;
                slider.value = 0;

                document.getElementById('story-controls').style.display = 'block';

                // Ouvrir le panneau droit
                document.getElementById('story-right-panel').classList.add('visible');
                setTimeout(function() { map.invalidateSize(); }, 50);

                // Construire le graphique avec toutes les semaines
                construireGraphique();

                afficherSemaine(0);
                startAnimation();
            } else {
                alert('Erreur réseau : ' + xhr.status);
            }
        }
    };
    xhr.open('POST', 'ajax_storymap.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('date_debut=' + encodeURIComponent(debut) + '&date_fin=' + encodeURIComponent(fin) + '&polluant=' + encodeURIComponent(polluant));
});

// ================= GRAPHIQUE =================
function construireGraphique() {
    // Calculer IQA moyen par semaine
    var labels = [];
    var valeurs = [];

    storySemaines.forEach(function(sem) {
        var iqas = sem.geojson.features.map(f => f.properties.iqa).filter(v => v !== null && v !== undefined);
        var moy = iqas.length > 0 ? Math.round(iqas.reduce((a, b) => a + b, 0) / iqas.length) : 0;
        labels.push(formatDateCourt(sem.semaine_debut));
        valeurs.push(moy);
    });

    // Couleurs des points selon IQA
    var pointColors = valeurs.map(v => getCouleurIQA(v));

    if (storyChart) {
        storyChart.destroy();
        storyChart = null;
    }

    var ctx = document.getElementById('story-chart').getContext('2d');
    storyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'IQA moyen',
                data: valeurs,
                borderColor: '#38b6ff',
                backgroundColor: 'rgba(56,182,255,0.1)',
                borderWidth: 2,
                pointBackgroundColor: pointColors,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return 'IQA : ' + ctx.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.06)' },
                    ticks: { font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 10 },
                        maxRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 8
                    }
                }
            }
        }
    });
}

// Mettre en surbrillance le point courant sur le graphique
function surbrillanceGraphique(index) {
    if (!storyChart) return;
    var valeurs = storyChart.data.datasets[0].data;
    var pointColors = valeurs.map(function(v, i) {
        return i === index ? '#ff7043' : getCouleurIQA(v);
    });
    var pointRadius = valeurs.map(function(v, i) {
        return i === index ? 8 : 5;
    });
    storyChart.data.datasets[0].pointBackgroundColor = pointColors;
    storyChart.data.datasets[0].pointRadius = pointRadius;
    storyChart.update('none');
}

// ================= AFFICHER UNE SEMAINE =================
function afficherSemaine(index) {
    storyLayer.clearLayers();
    storyIndex = index;
    var sem = storySemaines[index];

    // Semaine label
    document.getElementById('story-semaine-label').textContent =
        'Semaine du ' + formatDate(sem.semaine_debut) + ' au ' + formatDate(sem.semaine_fin);
    document.getElementById('story-slider').value = index;
    document.getElementById('story-progress').textContent =
        'Semaine ' + (index + 1) + ' / ' + storySemaines.length +
        ' — ' + sem.geojson.features.length + ' station(s)';
    document.getElementById('btn-prev').disabled = (index === 0);
    document.getElementById('btn-next').disabled = (index === storySemaines.length - 1);

    // Calculer stats
    var iqas = sem.geojson.features.map(f => f.properties.iqa).filter(v => v != null);
    var iqaMoyen = iqas.length > 0 ? Math.round(iqas.reduce((a, b) => a + b, 0) / iqas.length) : 0;
    var iqaMin   = iqas.length > 0 ? Math.min(...iqas) : 0;
    var iqaMax   = iqas.length > 0 ? Math.max(...iqas) : 0;

    // Stats cards
    document.getElementById('stat-iqa-val').textContent = iqaMoyen;
    document.getElementById('stat-iqa-val').style.color = getCouleurIQA(iqaMoyen);
    document.getElementById('stat-stations-val').textContent = sem.geojson.features.length;
    document.getElementById('stat-iqa-min').textContent = iqaMin;
    document.getElementById('stat-iqa-min').style.color = getCouleurIQA(iqaMin);
    document.getElementById('stat-iqa-max').textContent = iqaMax;
    document.getElementById('stat-iqa-max').style.color = getCouleurIQA(iqaMax);

    // Narratif
    var narr = getNarratif(iqaMoyen, sem.geojson.features.length, sem.semaine_debut, sem.semaine_fin);
    document.getElementById('story-narrative-icon').textContent  = narr.icon;
    document.getElementById('story-narrative-titre').textContent = narr.titre;
    document.getElementById('story-narrative-texte').textContent = narr.texte;

    // Couleur de la bordure gauche selon IQA
    document.getElementById('story-narrative-box').style.borderLeftColor = getCouleurIQA(iqaMoyen);

    // Surbrillance graphique
    surbrillanceGraphique(index);

    // Marqueurs carte
    sem.geojson.features.forEach(function(feature) {
        var coords  = feature.geometry.coordinates;
        var p       = feature.properties;
        var couleur = p.couleur || '#888888';
        var textColor = (couleur === '#ffff00' || couleur === '#FFFF00') ? '#333' : 'white';

        var iqaIcon = L.divIcon({
            className: '',
            html: '<div style="background:' + couleur + ';color:' + textColor + ';width:34px;height:34px;border-radius:50%;border:2px solid white;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:12px;box-shadow:0 1px 4px rgba(0,0,0,0.4);">' + p.iqa + '</div>',
            iconSize: [34,34], iconAnchor: [17,17], popupAnchor: [0,-20]
        });

        L.marker([coords[1], coords[0]], { icon: iqaIcon })
         .bindPopup(
            '<b>' + p.nom + '</b><hr style="margin:4px 0">' +
            '<b>Polluant :</b> ' + p.polluant + '<br>' +
            '<b>Valeur moyenne :</b> ' + p.valeur + '<br>' +
            '<b>IQA :</b> <span style="background:' + couleur + ';color:' + textColor + ';padding:2px 7px;border-radius:4px;font-weight:bold;">' + p.iqa + '</span><br>' +
            '<small style="color:#888">Semaine du ' + formatDate(sem.semaine_debut) + '</small>'
         )
         .addTo(storyLayer);
    });
}

// ================= ANIMATION =================
function startAnimation() {
    if (storyPlaying) return;
    storyPlaying = true;
    var btn = document.getElementById('btn-playpause');
    btn.textContent = 'Pause';
    btn.classList.add('pause');

    var vitesse = parseInt(document.getElementById('story-vitesse').value);
    storyTimer = setInterval(function() {
        if (storyIndex < storySemaines.length - 1) {
            afficherSemaine(storyIndex + 1);
        } else {
            stopAnimation();
        }
    }, vitesse);
}

function stopAnimation() {
    if (storyTimer) { clearInterval(storyTimer); storyTimer = null; }
    storyPlaying = false;
    var btn = document.getElementById('btn-playpause');
    if (btn) { btn.textContent = 'Play'; btn.classList.remove('pause'); }
}

function fermerPanneauDroit() {
    document.getElementById('story-right-panel').classList.remove('visible');
    stopAnimation();
    storyLayer.clearLayers();
    Station.addTo(map);
    setTimeout(function() { map.invalidateSize(); }, 50);
}

document.getElementById('btn-playpause').addEventListener('click', function() {
    if (storyPlaying) { stopAnimation(); }
    else { if (storyIndex >= storySemaines.length - 1) afficherSemaine(0); startAnimation(); }
});
document.getElementById('story-slider').addEventListener('input', function() {
    stopAnimation(); afficherSemaine(parseInt(this.value));
});
document.getElementById('btn-prev').addEventListener('click', function() {
    stopAnimation(); if (storyIndex > 0) afficherSemaine(storyIndex - 1);
});
document.getElementById('btn-next').addEventListener('click', function() {
    stopAnimation(); if (storyIndex < storySemaines.length - 1) afficherSemaine(storyIndex + 1);
});
document.getElementById('story-vitesse').addEventListener('change', function() {
    if (storyPlaying) { stopAnimation(); startAnimation(); }
});

// ================= UTILITAIRES =================
function formatDate(str) {
    if (!str) return '';
    var p = str.split('-');
    return p[2] + '/' + p[1] + '/' + p[0];
}
function formatDateCourt(str) {
    if (!str) return '';
    var p = str.split('-');
    return p[2] + '/' + p[1];
}
</script>

</body>
</html>