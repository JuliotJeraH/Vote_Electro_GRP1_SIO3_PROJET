<?php
session_start();
require_once 'config/database.php'; // Assurez-vous que $db est défini
require_once 'includes/functions.php';

// Journalisation de la déconnexion
if (isset($_SESSION['user'])) {
    log_action($db, $_SESSION['user']['id'], 'deconnexion', "Déconnexion de l'utilisateur");
}

// Destruction complète de la session
$_SESSION = array();

// Suppression du cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destruction de la session
session_destroy();

// Redirection vers la page de login avec un message
header('Location: login.php');
exit;
?>