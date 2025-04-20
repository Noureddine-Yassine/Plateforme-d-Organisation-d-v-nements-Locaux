<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM organisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $message = "Cet email est déjà utilisé.";
    } else {
        // Insérer dans la base de données
        $stmt = $pdo->prepare("INSERT INTO organisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)");
        if ($stmt->execute([$nom, $email, $mot_de_passe])) {
            header("Location: login_organisateur.php");
            exit;
        } else {
            $message = "Une erreur s'est produite lors de l'inscription.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Organisateur</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            box-sizing: border-box;
        }

        h1 {
            color: #333;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Error message style */
        .error {
            color: red;
            font-size: 1rem;
            text-align: center;
            margin-bottom: 15px;
        }

        /* Form Container */
        form {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        form label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
            text-align: left;
        }

        form input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            background-color: #f9f9f9;
            transition: border 0.3s ease;
        }

        form input:focus {
            border-color: #007bff;
            outline: none;
        }

        form button {
            width: 100%;
            padding: 14px;
            background-color: #007bff;
            color: #fff;
            font-size: 1.2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #0056b3;
        }

        /* Link style */
        p {
            font-size: 1rem;
            color: #555;
            margin-top: 20px;
        }

        a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div>
        <h1>Inscription - Espace Organisateur</h1>
        <?php if (!empty($message)): ?>
            <p class="error"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" required>

            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>

            <button type="submit">S'inscrire</button>
        </form>
        <p>Déjà inscrit ? <a href="login_organisateur.php">Connectez-vous ici</a>.</p>
    </div>
</body>
</html>
