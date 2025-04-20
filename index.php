<?php
include 'config.php';

// Initialiser les événements
$evenements = [];

// Traitement de la recherche
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['search'])) {
    $search_term = trim($_GET['search']);
    $stmt = $pdo->prepare("SELECT * FROM evenements WHERE titre LIKE ? ORDER BY date_evenement ASC");
    $stmt->execute(['%' . $search_term . '%']);
    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Si aucune recherche, récupérer tous les événements
    $stmt = $pdo->prepare("SELECT * FROM evenements ORDER BY date_evenement ASC");
    $stmt->execute();
    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer les témoignages existants
$stmt = $pdo->prepare("SELECT * FROM temoignages");
$stmt->execute();
$temoignages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Gestion des Événements</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Style global */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            display: flex;
        }

        /* Menu latéral */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }

        .sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #1abc9c;
            text-align: center;
        }

        .sidebar nav a {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            text-align: center;
            margin-bottom: 10px;
        }

        .sidebar nav a:hover {
            background-color: #1abc9c;
            border-radius: 5px;
        }

        /* Contenu principal */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
        }

        /* Header */
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Titres des sections */
        h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
            color: #2c3e50;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Liste des événements */
        .event-list, .testimonials-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .event-card, .testimonial-card {
            background: white;
            border: 1px solid #e0e0e0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 300px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .event-card:hover, .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .event-card h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #2c3e50;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .event-card p {
            font-size: 1rem;
            margin-bottom: 10px;
            color: #555;
        }

        .event-card img {
            width: 100%;
            height: auto;
            margin-top: 10px;
            border-radius: 8px;
        }

        /* Boutons */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 10px;
            background-color: #1abc9c;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #16a085;
        }

        /* Footer */
        footer {
            margin-top: 40px;
            text-align: center;
            padding: 15px;
            background-color: #2c3e50;
            color: white;
        }

        footer p {
            margin: 0;
            font-size: 1rem;
        }

        /* Styles pour la carte */
        #map {
            height: 600px; /* Hauteur fixe */
            width: calc(100% - 20px); /* Largeur relative avec un peu de marge */
            margin-top: 40px;
            margin-left: 20px; /* Pas de marge à gauche pour aligner avec le contenu */
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 10px;
            }

            .main-content {
                margin-left: 0;
            }

            .event-card, .testimonial-card {
                width: calc(50% - 20px);
            }
        }

        @media (max-width: 480px) {
            .event-card, .testimonial-card {
                width: 100%;
            }

            header h1 {
                font-size: 2rem;
            }

            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
    <!-- Intégration de l'API Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBBxvIEp-N_L7atLQjwrtCxmYkyM57jrbI&callback=initMap" async defer></script>
    <script>
 
        function initMap() {
            // Obtenir la position de l'utilisateur
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const userLat = position.coords.latitude;
                        const userLng = position.coords.longitude;

                        // Créer la carte centrée sur la position de l'utilisateur
                        const map = new google.maps.Map(document.getElementById('map'), {
                            center: { lat: userLat, lng: userLng },
                            zoom: 12
                        });

                        // Ajouter un marqueur pour la position de l'utilisateur
                        new google.maps.Marker({
                            position: { lat: userLat, lng: userLng },
                            map: map,
                            title: "Votre position"
                        });

                        // Filtrer les événements à proximité (rayon de 50 km)
                        const events = <?php echo json_encode($evenements); ?>;
                        events.forEach(event => {
                            if (event.latitude && event.longitude) {
                                const eventLat = parseFloat(event.latitude);
                                const eventLng = parseFloat(event.longitude);
                                const distance = calculateDistance(userLat, userLng, eventLat, eventLng);

                                if (distance <= 50) { // Rayon de 50 km
                                    const marker = new google.maps.Marker({
                                        position: { lat: eventLat, lng: eventLng },
                                        map: map,
                                        title: event.titre
                                    });

                                    // InfoWindow pour afficher les détails de l'événement
                                    const infowindow = new google.maps.InfoWindow({
                                        content: `
                                            <h3>${event.titre}</h3>
                                            <p><strong>Lieu :</strong> ${event.lieu}</p>
                                            <p><strong>Date :</strong> ${event.date_evenement}</p>
                                            <a href="inscription.php?event_id=${event.id}" class="btn">S'inscrire</a>
                                        `
                                    });

                                    // Ouvrir l'InfoWindow lors du clic sur le marqueur
                                    marker.addListener('click', () => {
                                        infowindow.open(map, marker);
                                    });
                                }
                            }
                        });
                    },
                    (error) => {
                        console.error("Erreur de géolocalisation : ", error);
                        alert("Impossible de récupérer votre position. Veuillez activer la géolocalisation.");
                    }
                );
            } else {
                alert("La géolocalisation n'est pas supportée par votre navigateur.");
            }
        }
    </script>
</head>
<body>
    <!-- Menu latéral -->
    <div class="sidebar">
        <h2>Menu</h2>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="signup_organisateur.php">Espace Organisateur</a>
            <a href="ajouter_temoin.php">Ajouter un témoignage</a>
            <a href="liste_evenements.php">Liste des événements</a>
          
        </nav>
    </div>

    <!-- Contenu principal -->
    <div class="main-content">
        <header>
            <h1>Gestion des Événements</h1>
        </header>

        <main>
            <h2>Rechercher un événement</h2>
            <form action="index.php" method="GET" style="text-align: center; margin-bottom: 30px;">
                <input type="text" name="search" placeholder="Rechercher un événement..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 5px;">
                <button type="submit" style="padding: 10px 20px; background: #1abc9c; color: white; border: none; border-radius: 5px; cursor: pointer;">Rechercher</button>
            </form>

            <h2>Événements</h2>
            <?php if (count($evenements) > 0): ?>
                <div class="event-list">
                    <?php foreach ($evenements as $event): ?>
                        <div class="event-card">
                            <h3><?php echo htmlspecialchars($event['titre']); ?></h3>
                            <p><strong>Lieu :</strong> <?php echo htmlspecialchars($event['lieu']); ?></p>
                            <p><strong>Date :</strong> <?php echo htmlspecialchars($event['date_evenement']); ?></p>
                            <p><?php echo htmlspecialchars($event['description']); ?></p>
                            <?php if (!empty($event['image'])): ?>
                                <img src="assets/images/<?php echo htmlspecialchars($event['image']); ?>" alt="Image de l'événement" style="max-width: 100%; border-radius: 8px; margin-top: 10px;">
                            <?php endif; ?>
                            <a href="inscription.php?event_id=<?php echo $event['id']; ?>" class="btn">S'inscrire</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center;">Aucun événement trouvé.</p>
            <?php endif; ?>

            <h2>Témoignages</h2>
            <?php if (count($temoignages) > 0): ?>
                <div class="testimonials-list">
                    <?php foreach ($temoignages as $temoignage): ?>
                        <div class="testimonial-card">
                            <h4><?php echo htmlspecialchars($temoignage['nom_event']); ?></h4>
                            <p><strong>De :</strong> <?php echo htmlspecialchars($temoignage['nom_utilisateur']); ?></p>
                            <p>"<?php echo htmlspecialchars($temoignage['message']); ?>"</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center;">Aucun témoignage disponible.</p>
            <?php endif; ?>

            <!-- Carte Google Maps pour les événements à proximité -->
            <h2>Événements à proximité</h2>
            <div id="map"></div>
        </main>

        <footer>
            <p>&copy; 2025 - Gestion des Événements</p>
        </footer>
    </div>
</body>
</html>