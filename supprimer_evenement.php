<?php
session_start();
include 'config.php';

// Vérifier si l'organisateur est connecté
if (!isset($_SESSION['organisateur_id'])) {
    header("Location: login_organisateur.php");
    exit;
}

// Vérifier si l'ID de l'événement est passé en POST
if (isset($_POST['evenement_id'])) {
    $evenement_id = $_POST['evenement_id'];
    $organisateur_id = $_SESSION['organisateur_id'];

    try {
        // Commencer une transaction
        $pdo->beginTransaction();

        // Supprimer les participants associés à l'événement
        $stmt = $pdo->prepare("DELETE FROM participants WHERE evenement_id = ?");
        $stmt->execute([$evenement_id]);

        // Supprimer l'événement
        $stmt = $pdo->prepare("DELETE FROM evenements WHERE id = ? AND organisateur_id = ?");
        $stmt->execute([$evenement_id, $organisateur_id]);

        // Valider la transaction
        $pdo->commit();

        // Rediriger vers le tableau de bord avec un message de succès
        $_SESSION['message'] = "L'événement a été supprimé avec succès.";
        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        die("Erreur lors de la suppression de l'événement : " . $e->getMessage());
    }
} else {
    // Rediriger si l'ID de l'événement n'est pas fourni
    header("Location: dashboard.php");
    exit;
}
?>