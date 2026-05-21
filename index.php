<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qualité de l'air – Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f7ff;
        }

        /* NAVBAR */
        .navbar { background-color: #38b6ff; }
        .navbar-brand, .nav-link { color: white !important; font-weight: 600; }
        .nav-link:hover { text-decoration: underline; }

        /* HERO */
        .hero {
            background: linear-gradient(135deg, #38b6ff, #0077cc);
            color: white;
            padding: 70px 20px;
            text-align: center;
        }
        .hero h1 { font-size: 2.2rem; font-weight: 700; }
        .hero p { font-size: 1.05rem; max-width: 650px; margin: 16px auto 28px; opacity: 0.93; }
        .hero .btn-light { font-weight: 600; color: #0077cc; padding: 12px 32px; border-radius: 8px; }

        /* STAT CARDS */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 28px 16px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            border-top: 4px solid #38b6ff;
        }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #38b6ff; }
        .stat-card .label { font-size: 0.88rem; color: #666; margin-top: 4px; }

        /* SECTION */
        .section-title { font-weight: 700; font-size: 1.4rem; color: #1a1a2e; margin-bottom: 6px; }
        .section-sub { color: #777; font-size: 0.9rem; margin-bottom: 20px; }

        /* ACCORDION */
        .accordion-button { font-weight: 600; color: #1a1a2e; background-color: #fff; }
        .accordion-button:not(.collapsed) { background-color: #e8f4ff; color: #0077cc; box-shadow: none; }
        .accordion-item {
            border: 1px solid #cce3f8;
            border-radius: 10px !important;
            margin-bottom: 10px;
            overflow: hidden;
        }
        .accordion-body { color: #444; font-size: 0.93rem; line-height: 1.75; }

        /* IQA dot */
        .iqa-badge {
            display: inline-block;
            width: 16px; height: 16px;
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
        }

        /* FOOTER */
        footer {
            background-color: #38b6ff;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 70px;
            font-size: 0.88rem;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">Qualité de l'air</a>
        <button class="navbar-toggler border-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon" style="filter:invert(1)"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto gap-2">
                <li class="nav-item"><a class="nav-link" href="#">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="carte4.php">Carte interactive</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<div class="hero">
    <h1>Application cartographique de la qualité de l'air</h1>
    <p>
        Visualisez en temps réel et en données historiques la qualité de l'air sur le territoire français.
        Explorez les polluants, les conditions météo et les zones à risque sur une carte interactive.
    </p>
    <a href="carte4.php" class="btn btn-light">Accéder à la carte →</a>
</div>

<!-- ACCORDEONS -->
<div class="container my-5">
    <div class="row g-4">

        <!-- OBJECTIFS -->
        <div class="col-lg-6">
            <p class="section-title">Objectifs du projet</p>
            <p class="section-sub">À quoi sert cette application ?</p>

            <div class="accordion" id="accordionObjectifs">

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#obj1">
                            Contexte du projet
                        </button>
                    </h2>
                    <div id="obj1" class="accordion-collapse collapse show" data-bs-parent="#accordionObjectifs">
                        <div class="accordion-body">
                            Ce projet a été réalisé dans le cadre des SAE <strong>5.VCOD.1</strong> et <strong>6.VCOD.1</strong>
                            à l'IUT de Carcassonne (BUT Science des Données). L'objectif est de construire une application
                            cartographique permettant d'analyser la qualité de l'air en France métropolitaine,
                            en croisant plusieurs sources de données environnementales.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#obj2">
                            Visualisation sur la carte
                        </button>
                    </h2>
                    <div id="obj2" class="accordion-collapse collapse" data-bs-parent="#accordionObjectifs">
                        <div class="accordion-body">
                            La carte interactive affiche les stations de mesure sous forme de cercles colorés selon l'IQA.
                            On peut également afficher les <strong>parcs, forêts, sites industriels</strong> et
                            <strong>incendies</strong> pour comprendre les facteurs influençant la pollution locale.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#obj3">
                            Filtrage des données
                        </button>
                    </h2>
                    <div id="obj3" class="accordion-collapse collapse" data-bs-parent="#accordionObjectifs">
                        <div class="accordion-body">
                            L'utilisateur peut filtrer les résultats selon :
                            <ul class="mt-2">
                                <li>Le <strong>polluant</strong> : O3, NO2, PM10, PM2.5, SO2</li>
                                <li>La <strong>période</strong> : date de début et de fin</li>
                                <li>La <strong>zone géographique</strong> : région → département → station → commune</li>
                            </ul>
                            Les résultats se mettent à jour automatiquement sur la carte.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#obj4">
                            Statistiques et analyses
                        </button>
                    </h2>
                    <div id="obj4" class="accordion-collapse collapse" data-bs-parent="#accordionObjectifs">
                        <div class="accordion-body">
                            Des graphiques permettent de suivre l'évolution des concentrations de polluants dans le temps,
                            de comparer les moyennes par station et d'identifier les périodes à risque.
                            Ces analyses s'appuient sur les données historiques stockées dans la base PostgreSQL/PostGIS.
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- DONNÉES -->
        <div class="col-lg-6">
            <p class="section-title">Qualité de l'air & Données</p>
            <p class="section-sub">Sources et informations utilisées</p>

            <div class="accordion" id="accordionDonnees">

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#don1">
                            Source : API WAQI
                        </button>
                    </h2>
                    <div id="don1" class="accordion-collapse collapse show" data-bs-parent="#accordionDonnees">
                        <div class="accordion-body">
                            Les données temps réel proviennent de l'API
                            <a href="https://waqi.info" target="_blank"><strong>WAQI (World Air Quality Index)</strong></a>.
                            Elle agrège les mesures de milliers de stations dans le monde et fournit pour chaque station :
                            l'IQA, les concentrations de polluants et les données météo (température, humidité, vent).
							On a également des données prevenant de l'IGN pour tout ce qui concerne les communes et régions. 
							Pour les forêt, Espaces et sites industriels, météo, les données proviennent de Data.gouv
							et enfin les polluant sont issues de ATMO.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#don2">
                            Base de données PostgreSQL / PostGIS
                        </button>
                    </h2>
                    <div id="don2" class="accordion-collapse collapse" data-bs-parent="#accordionDonnees">
                        <div class="accordion-body">
                            Toutes les données sont stockées dans une base <strong>PostgreSQL</strong> avec l'extension
                            <strong>PostGIS</strong>. Les géométries (communes, régions, départements, stations) sont
                            stockées au format <code>geometry(Point/Polygon, 4326)</code> en projection WGS84.
                            Cela permet de faire des requêtes spatiales comme trouver la station la plus proche d'un point.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#don3">
                            Les polluants mesurés
                        </button>
                    </h2>
                    <div id="don3" class="accordion-collapse collapse" data-bs-parent="#accordionDonnees">
                        <div class="accordion-body">
                            <table class="table table-sm table-bordered mt-1">
                                <thead class="table-primary">
                                    <tr><th>Polluant</th><th>Nom complet</th><th>Source principale</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>O3</strong></td><td>Ozone</td><td>Réaction photochimique UV</td></tr>
                                    <tr><td><strong>NO2</strong></td><td>Dioxyde d'azote</td><td>Transports, industrie</td></tr>
                                    <tr><td><strong>PM2.5</strong></td><td>Particules &lt;2,5µm</td><td>Combustion, trafic</td></tr>
                                    <tr><td><strong>PM10</strong></td><td>Particules &lt;10µm</td><td>Poussières, chantiers</td></tr>
                                    <tr><td><strong>SO2</strong></td><td>Dioxyde de soufre</td><td>Charbon, pétrole</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#don4">
                            Comprendre les couleurs IQA
                        </button>
                    </h2>
                    <div id="don4" class="accordion-collapse collapse" data-bs-parent="#accordionDonnees">
                        <div class="accordion-body">
                            Les cercles sur la carte sont colorés selon l'IQA :
                            <ul class="list-unstyled mt-2">
                                <li><span class="iqa-badge" style="background:#00e400"></span><strong>0–50</strong> : Bon — aucune restriction</li>
                                <li><span class="iqa-badge" style="background:#cccc00"></span><strong>51–100</strong> : Moyen — personnes sensibles vigilantes</li>
                                <li><span class="iqa-badge" style="background:#ff7e00"></span><strong>101–150</strong> : Dégradé — limitez les efforts dehors</li>
                                <li><span class="iqa-badge" style="background:#ff0000"></span><strong>151–200</strong> : Mauvais — évitez les activités extérieures</li>
                                <li><span class="iqa-badge" style="background:#8f3f97"></span><strong>201–300</strong> : Très mauvais — restez à l'intérieur</li>
                                <li><span class="iqa-badge" style="background:#7e0023"></span><strong>300+</strong> : Dangereux — fermez les fenêtres</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- FOOTER -->
<footer>
    <p>IUT de Carcassonne – BUT Science des Données · SAE SIG 2025–2026</p>
    <p style="opacity:0.8; font-size:0.8rem">Données : API WAQI · Carte : Leaflet & OpenStreetMap</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
