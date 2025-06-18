<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirection si déjà connecté
if (isset($_SESSION['user'])) {
    redirect_based_on_role($_SESSION['user']['role']);
}

// Récupérer les scrutins actifs
$scrutins_actifs = [];
try {
    $stmt = $db->query("SELECT s.*, 
                       (SELECT COUNT(*) FROM candidats c WHERE c.scrutin_id = s.id) as nb_candidats
                       FROM scrutins s 
                       WHERE s.date_debut <= NOW() AND s.date_fin >= NOW()
                       ORDER BY s.date_fin ASC
                       LIMIT 3");
    $scrutins_actifs = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des scrutins: " . $e->getMessage();
}

// Récupérer les résultats récents
$resultats_recents = [];
try {
    $stmt = $db->query("SELECT s.*, 
                       (SELECT COUNT(*) FROM votes v WHERE v.scrutin_id = s.id) as participation,
                       (SELECT c.prenom FROM candidats c 
                        JOIN votes v ON v.candidat_id = c.id 
                        WHERE v.scrutin_id = s.id 
                        GROUP BY c.id 
                        ORDER BY COUNT(*) DESC 
                        LIMIT 1) as gagnant_prenom,
                       (SELECT c.nom FROM candidats c 
                        JOIN votes v ON v.candidat_id = c.id 
                        WHERE v.scrutin_id = s.id 
                        GROUP BY c.id 
                        ORDER BY COUNT(*) DESC 
                        LIMIT 1) as gagnant_nom
                       FROM scrutins s 
                       WHERE s.date_fin < NOW() AND s.afficher_resultats = 1
                       ORDER BY s.date_fin DESC
                       LIMIT 3");
    $resultats_recents = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des résultats: " . $e->getMessage();
}

require_once 'includes/header.php';
?>

<div class="container">
    <!-- Hero Section -->
    <section class="hero-section text-center py-5 bg-light rounded-3 mb-5">
        <h1 class="display-4 fw-bold">Bienvenue sur la plateforme de vote électronique</h1>
        <p class="lead">Participez aux élections en ligne de manière sécurisée et transparente</p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
            <a href="login.php" class="btn btn-primary btn-lg px-4 gap-3">Se connecter</a>
            <a href="inscription.php" class="btn btn-outline-secondary btn-lg px-4">S'inscrire</a>
        </div>
    </section>

    <!-- Scrutins en cours -->
    <section class="mb-5">
        <h2 class="mb-4 border-bottom pb-2">Scrutins en cours</h2>
        
        <?php if (!empty($scrutins_actifs)): ?>
            <div class="row g-4">
                <?php foreach ($scrutins_actifs as $scrutin): ?>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0"><?= htmlspecialchars($scrutin['titre']) ?></h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?= htmlspecialchars(truncate($scrutin['description'], 100)) ?></p>
                                <ul class="list-group list-group-flush mb-3">
                                    <li class="list-group-item">
                                        <strong>Fin:</strong> <?= date('d/m/Y H:i', strtotime($scrutin['date_fin'])) ?>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Candidats:</strong> <?= $scrutin['nb_candidats'] ?>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Type:</strong> <?= ucfirst($scrutin['type_vote']) ?>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="login.php" class="btn btn-primary w-100">Participer</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Aucun scrutin en cours actuellement.
            </div>
        <?php endif; ?>
    </section>

    <!-- Résultats récents -->
    <section class="mb-5">
        <h2 class="mb-4 border-bottom pb-2">Résultats récents</h2>
        
        <?php if (!empty($resultats_recents)): ?>
            <div class="row g-4">
                <?php foreach ($resultats_recents as $resultat): ?>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0"><?= htmlspecialchars($resultat['titre']) ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-trophy fa-2x text-warning"></i>
                                    </div>
                                    <h4 class="mt-3"><?= htmlspecialchars($resultat['gagnant_prenom'] . ' ' . $resultat['gagnant_nom']) ?></h4>
                                    <p class="text-muted">Vainqueur</p>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <strong>Participation:</strong> <?= $resultat['participation'] ?> votes
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Clôturé le:</strong> <?= date('d/m/Y', strtotime($resultat['date_fin'])) ?>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="login.php" class="btn btn-outline-success w-100">Voir les détails</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Aucun résultat disponible pour le moment.
            </div>
        <?php endif; ?>
    </section>

    <!-- Fonctionnalités -->
    <section class="mb-5">
        <h2 class="mb-4 border-bottom pb-2">Pourquoi voter avec nous ?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3">
                            <i class="fas fa-lock fa-2x text-primary"></i>
                        </div>
                        <h4>Sécurisé</h4>
                        <p class="card-text">Système de vote crypté garantissant l'intégrité et la confidentialité de votre vote.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3">
                            <i class="fas fa-mobile-alt fa-2x text-primary"></i>
                        </div>
                        <h4>Accessible</h4>
                        <p class="card-text">Votez depuis n'importe quel appareil, à tout moment pendant la période de vote.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3">
                            <i class="fas fa-chart-bar fa-2x text-primary"></i>
                        </div>
                        <h4>Transparent</h4>
                        <p class="card-text">Résultats disponibles immédiatement après la clôture du scrutin (selon configuration).</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php 
require_once 'includes/footer.php';