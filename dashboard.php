<?php
session_start();
include 'config.php';

// Vérifier si l'organisateur est connecté
if (!isset($_SESSION['organisateur_id'])) {
    header("Location: login_organisateur.php");
    exit;
}

// Afficher un message de session
if (isset($_SESSION['message'])) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 20px auto; max-width: 1200px; border-radius: 5px; text-align: center;'>
            {$_SESSION['message']}
          </div>";
    unset($_SESSION['message']); // Supprimer le message après l'affichage
}

// Récupérer les événements de l'organisateur connecté
$organisateur_id = $_SESSION['organisateur_id'];

try {
    // Récupérer les événements
    $stmt = $pdo->prepare("SELECT * FROM evenements WHERE organisateur_id = ? ORDER BY date_evenement DESC");
    $stmt->execute([$organisateur_id]);
    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque événement, récupérer les participants
    foreach ($evenements as &$event) {
        $stmt = $pdo->prepare("SELECT * FROM participants WHERE evenement_id = ?");
        $stmt->execute([$event['id']]);
        $event['participants'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Organisateur</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Style global */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
        }

        /* Header */
        header {
            background-color: #007BFF;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 600;
        }

        /* Navigation */
        nav {
            margin-top: 15px;
        }

        nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #ffdd57;
        }

        /* Main section */
        main {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 20px;
            color: #007BFF;
        }

        /* Liste des événements */
        .event-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .event-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: calc(33% - 20px);
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .event-card h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #007BFF;
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

        /* Bouton Supprimer */
        .btn-supprimer {
            padding: 10px 20px;
            background-color: #ff4d4d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-supprimer:hover {
            background-color: #cc0000;
        }

        /* Tableau récapitulatif des participants */
        .recap-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .recap-table th,
        .recap-table td {
            padding: 12px;
            text-align: left;
        }

        .recap-table th {
            background-color: #007BFF;
            color: white;
            font-weight: 600;
        }

        .recap-table tr {
            background-color: #fff;
            transition: background-color 0.3s ease;
        }

        .recap-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .recap-table tr:hover {
            background-color: #f1f1f1;
        }

        .recap-table td {
            border-bottom: 1px solid #ddd;
        }

        /* Alerte s'il n'y a pas d'événements */
        p {
            text-align: center;
            font-size: 1.2rem;
            color: #555;
        }

        /* Footer */
        footer {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: center;
            margin-top: 30px;
        }

        footer p {
            margin: 0;
            font-size: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
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
<header>
    <h1>Tableau de bord</h1>
    <nav>
        <a href="creer_evenement.php">Ajouter un événement</a>
        <a href="index.php">Se déconnecter</a>
    </nav>
</header>

<main>
    <h2>Vos événements</h2>

    <?php if (!empty($evenements)): ?>
        <div class="event-list">
            <?php foreach ($evenements as $event): ?>
                <div class="event-card">
                    <h3><?php echo htmlspecialchars($event['titre']); ?></h3>
                    <p><strong>Description :</strong> <?php echo htmlspecialchars($event['description']); ?></p>
                    <p><strong>Lieu :</strong> <?php echo htmlspecialchars($event['lieu']); ?></p>
                    <p><strong>Date :</strong> <?php echo htmlspecialchars($event['date_evenement']); ?></p>
                    <p><strong>Places disponibles :</strong> <?php echo htmlspecialchars($event['places_disponibles']); ?></p>
                    <?php if (!empty($event['image'])): ?>
                        <img src="assets/images/<?php echo htmlspecialchars($event['image']); ?>" alt="Image de l'événement">
                    <?php endif; ?>
                    <!-- Bouton Supprimer -->
                    <form action="supprimer_evenement.php" method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="evenement_id" value="<?php echo $event['id']; ?>">
                        <button type="submit" class="btn-supprimer">Supprimer</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Tableau récapitulatif des participants -->
        <h2>Participants inscrits par événement</h2>
        <table class="recap-table">
            <thead>
                <tr>
                    <th>Événement</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Date d'inscription</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evenements as $event): ?>
                    <?php if (!empty($event['participants'])): ?>
                        <?php foreach ($event['participants'] as $participant): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['titre']); ?></td>
                                <td><?php echo htmlspecialchars($participant['nom']); ?></td>
                                <td><?php echo htmlspecialchars($participant['prenom']); ?></td>
                                <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                <td><?php echo htmlspecialchars($participant['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Vous n'avez créé aucun événement pour le moment.</p>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2025 - Gestion des Événements</p>
</footer>
</body>
</html>