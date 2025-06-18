<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user'])) {
    redirect_based_on_role($_SESSION['user']['role']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = $_POST['matricule'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE matricule = ?");
        $stmt->execute([$matricule]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            redirect_based_on_role($user['role']);
        } else {
            $error = "Matricule ou mot de passe incorrect";
        }
    } catch (PDOException $e) {
        $error = "Erreur de connexion: " . $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title text-center">Connexion</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="matricule" class="form-label">Matricule</label>
                        <input type="text" class="form-control" id="matricule" name="matricule" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <small>Vous n'avez pas encore de compte? <a href="inscription.php">S'inscrire ici</a></small>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>