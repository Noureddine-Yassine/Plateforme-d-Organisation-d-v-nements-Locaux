<?php
include 'config.php';

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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Événements - Gestion des Événements</title>
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
        .event-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .event-card {
            background: white;
            border: 1px solid #e0e0e0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 300px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .event-card:hover {
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

            .event-card {
                width: calc(50% - 20px);
            }
        }

        @media (max-width: 480px) {
            .event-card {
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
            <h1>Liste des Événements</h1>
        </header>

        <main>
            <h2>Rechercher un événement</h2>
            <form action="liste_evenements.php" method="GET" style="text-align: center; margin-bottom: 30px;">
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
        </main>

        <footer>
            <p>&copy; 2025 - Gestion des Événements</p>
        </footer>
    </div>
</body>
</html>