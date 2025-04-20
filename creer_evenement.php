<?php
session_start();
include 'config.php';

// Vérifier si l'organisateur est connecté
if (!isset($_SESSION['organisateur_id'])) {
    header("Location: login_organisateur.php");
    exit;
}

// Initialisation des variables
$message = "";

// Liste des fichiers dans le dossier "assets/images"
$images = array_diff(scandir('assets/images'), array('..', '.')); // On ignore les entrées '.' et '..'

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données du formulaire
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $lieu = trim($_POST['lieu'] ?? '');
    $date_evenement = $_POST['date_evenement'] ?? null;
    $places_disponibles = intval($_POST['places_disponibles'] ?? 0);
    $organisateur_id = $_SESSION['organisateur_id'];
    $image_name = $_POST['image'] ?? null; // On récupère le nom de l'image choisie
    $latitude = $_POST['latitude'] ?? null; // Nouveau champ pour la latitude
    $longitude = $_POST['longitude'] ?? null; // Nouveau champ pour la longitude

    // Vérification des champs obligatoires
    if (empty($titre) || empty($description) || empty($lieu) || empty($date_evenement) || $places_disponibles <= 0 || empty($image_name) || empty($latitude) || empty($longitude)) {
        $message = "Tous les champs sont obligatoires, y compris la sélection d'une image et d'un lieu sur la carte.";
    } else {
        // Insertion dans la base de données si aucune erreur
        if (empty($message)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO evenements (titre, description, lieu, date_evenement, places_disponibles, image, organisateur_id, latitude, longitude) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titre, $description, $lieu, $date_evenement, $places_disponibles, $image_name, $organisateur_id, $latitude, $longitude]);

                $message = "Événement créé avec succès.";
            } catch (PDOException $e) {
                $message = "Erreur lors de l'insertion dans la base de données : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Événement</title>
    <style>
        /* Styles généraux */
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        header {
            background: #007BFF;
            color: #fff;
            padding: 1rem 0;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            margin: 0;
            font-size: 2rem;
        }

        nav {
            margin-top: 10px;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #ffdd57;
        }

        main {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .message {
            background: #e0ffe0;
            color: #007B00;
            padding: 10px;
            border: 1px solid #007B00;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        form div {
            margin-bottom: 15px;
        }

        form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }

        form input, form textarea, form button {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        form input:focus, form textarea:focus {
            border-color: #007BFF;
            outline: none;
        }

        form textarea {
            resize: vertical;
            min-height: 100px;
        }

        form button {
            background: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            padding: 12px;
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        form button:hover {
            background: #0056b3;
        }

        #map {
            height: 400px;
            width: 100%;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        footer {
            text-align: center;
            padding: 10px 0;
            background: #333;
            color: #fff;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        /* Styles pour les petits écrans */
        @media (max-width: 768px) {
            main {
                padding: 15px;
            }

            header h1 {
                font-size: 1.5rem;
            }

            form input, form textarea, form button {
                font-size: 0.9rem;
            }
        }
    </style>
    <!-- Intégration de l'API Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBBxvIEp-N_L7atLQjwrtCxmYkyM57jrbI&libraries=places"></script>
    <script>
        function initMap() {
            const map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 48.8566, lng: 2.3522 }, // Centre sur Paris par défaut
                zoom: 13
            });

            const input = document.getElementById('lieu');
            const autocomplete = new google.maps.places.Autocomplete(input);

            autocomplete.bindTo('bounds', map);

            const marker = new google.maps.Marker({
                map: map,
                draggable: true, // Permet de déplacer le marqueur
                anchorPoint: new google.maps.Point(0, -29)
            });

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place.geometry) {
                    window.alert("Aucun détail disponible pour l'entrée: '" + place.name + "'");
                    return;
                }

                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }

                marker.setPosition(place.geometry.location);
                marker.setVisible(true);

                // Mettre à jour les champs latitude et longitude
                document.getElementById('latitude').value = place.geometry.location.lat();
                document.getElementById('longitude').value = place.geometry.location.lng();
            });

            // Mettre à jour les coordonnées lorsque le marqueur est déplacé
            marker.addListener('dragend', function() {
                const position = marker.getPosition();
                document.getElementById('latitude').value = position.lat();
                document.getElementById('longitude').value = position.lng();
            });
        }

        window.onload = initMap;
    </script>
</head>
<body>

<header>
    <h1>Créer un nouvel événement</h1>
    <nav>
        <a href="dashboard.php">Retour au tableau de bord</a>
        <a href="index.php">Se déconnecter</a>
    </nav>
</header>

<main>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <div>
            <label for="titre">Titre de l'événement :</label>
            <input type="text" id="titre" name="titre" required>
        </div>
        <div>
            <label for="description">Description :</label>
            <textarea id="description" name="description" required></textarea>
        </div>
        <div>
            <label for="lieu">Lieu :</label>
            <input type="text" id="lieu" name="lieu" required>
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">
        </div>
        <div id="map"></div>
        <div>
            <label for="date_evenement">Date de l'événement :</label>
            <input type="date" id="date_evenement" name="date_evenement" required>
        </div>
        <div>
            <label for="places_disponibles">Nombre de places disponibles :</label>
            <input type="number" id="places_disponibles" name="places_disponibles" required>
        </div>
        <div>
            <label for="image">Choisir une image de l'événement :</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        <button type="submit">Créer l'événement</button>
    </form>
</main>

<footer>
    <p>&copy; 2025 - Gestion des Événements</p>
</footer>

</body>
</html>