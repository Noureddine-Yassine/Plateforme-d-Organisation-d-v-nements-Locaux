<?php

// Vérifier si un utilisateur est connecté
function estConnecteUtilisateur() {
    return isset($_SESSION['utilisateur_id']);
}

// Vérifier si un organisateur est connecté
function estConnecteOrganisateur() {
    return isset($_SESSION['organisateur_id']);
}

// Rediriger vers une page donnée
function redirection($url) {
    header("Location: $url");
    exit;
}

// Récupérer les événements
function recupererEvenements($pdo) {
    $stmt = $pdo->query("SELECT * FROM evenements ORDER BY date_evenement ASC");
    return $stmt->fetchAll();
}

// Récupérer les détails d'un événement
function recupererEvenementParId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM evenements WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Vérifier si un événement est complet
function estComplet($evenement) {
    return $evenement['places_disponibles'] <= 0;
}

// Réduire le nombre de places disponibles pour un événement
function reduirePlaces($pdo, $evenement_id) {
    $stmt = $pdo->prepare("UPDATE evenements SET places_disponibles = places_disponibles - 1 WHERE id = ?");
    $stmt->execute([$evenement_id]);
}

?>
