<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_event = trim($_POST['nom_event']);
    $nom_utilisateur = trim($_POST['nom_utilisateur']);
    $message = trim($_POST['message']);

    // Validation simple
    if (!empty($nom_event) && !empty($nom_utilisateur) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO temoignages (nom_event, nom_utilisateur, message) VALUES (?, ?, ?)");
        $stmt->execute([$nom_event, $nom_utilisateur, $message]);

        // Rediriger après l'ajout
        header('Location: index.php');
        exit;
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}

// Récupérer la liste des événements pour le menu déroulant
$stmt = $pdo->prepare("SELECT titre FROM evenements ORDER BY date_evenement ASC");
$stmt->execute();
$evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un témoignage</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        main {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
        }

        input, textarea, select, button {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            background-color: #0056b3;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #003d80;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <main>
        <h1>Ajouter un témoignage</h1>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="ajouter_temoin.php" method="POST">
            <label for="nom_event">Nom de l'événement :</label>
            <select name="nom_event" id="nom_event" required>
                <option value="">-- Sélectionnez un événement --</option>
                <?php foreach ($evenements as $event): ?>
                    <option value="<?php echo htmlspecialchars($event['titre']); ?>">
                        <?php echo htmlspecialchars($event['titre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="nom_utilisateur">Votre nom :</label>
            <input type="text" id="nom_utilisateur" name="nom_utilisateur" required>

            <label for="message">Votre témoignage :</label>
            <textarea id="message" name="message" rows="5" required></textarea>

            <button type="submit">Envoyer</button>
        </form>
    </main>
</body>
</html>
