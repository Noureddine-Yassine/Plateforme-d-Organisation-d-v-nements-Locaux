<?php
include 'config.php';

// Récupérer l'ID de l'événement depuis l'URL
$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    die("ID de l'événement non spécifié.");
}

// Récupérer les détails de l'événement
$stmt = $pdo->prepare("SELECT * FROM evenements WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Événement non trouvé.");
}

// Gérer le formulaire d'inscription
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);

    // Validation des champs
    if (empty($nom) || empty($prenom) || empty($email)) {
        $error_message = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "L'adresse email n'est pas valide.";
    } elseif ($event['places_disponibles'] <= 0) {
        $error_message = "Désolé, il n'y a plus de places disponibles pour cet événement.";
    } else {
        // Ajouter le participant dans la base de données
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO participants (nom, prenom, email, evenement_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $event_id]);

            // Réduire le nombre de places disponibles
            $stmt = $pdo->prepare("UPDATE evenements SET places_disponibles = places_disponibles - 1 WHERE id = ?");
            $stmt->execute([$event_id]);

            $pdo->commit();

            $success_message = "Inscription réussie ! Merci de vous être inscrit.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = "Une erreur est survenue : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - <?php echo htmlspecialchars($event['titre']); ?></title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
        }

        header h1 {
            font-size: 2rem;
            color: #333;
        }

        nav {
            margin-top: 10px;
        }

        nav a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        main {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        main p.success {
            color: #28a745;
            font-weight: bold;
        }

        main p.error {
            color: #dc3545;
            font-weight: bold;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        form div {
            text-align: left;
        }

        form label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        form input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            font-weight: bold;
        }

        form button:hover {
            background-color: #0056b3;
        }

        footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9rem;
            color: #555;
        }

        .btn-return {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }

        .btn-return:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <header>
        <h1>Inscription à l'événement : <?php echo htmlspecialchars($event['titre']); ?></h1>
        <nav>
            <a href="index.php">Accueil</a>
        </nav>
    </header>

    <main>
        <?php if ($success_message): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
            <a href="index.php" class="btn-return">Retour à l'accueil</a>
        <?php elseif ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php else: ?>
            <form action="" method="POST">
                <div>
                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div>
                    <label for="prenom">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
                <div>
                    <label for="email">Email :</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit">S'inscrire</button>
            </form>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 - Gestion des Événements</p>
    </footer>
</body>
</html>
