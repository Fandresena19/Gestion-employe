<?php
session_start();



// Initialisation des variables
$message = '';
$messageType = '';

// Traitement du formulaire de connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  try {
    // Connexion à la base de données
    $bdd = include("./traitement/db.php");

    $nom_utilisateur = $_POST['nom_utilisateur'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if (empty($nom_utilisateur) || empty($mot_de_passe)) {
      $message = "Veuillez remplir tous les champs.";
      $messageType = "error";
    } else {
      // Recherche de l'utilisateur dans la base de données
      $stmt = $bdd->prepare("SELECT id_utilisateur, nom_utilisateur, mot_de_passe FROM utilisateur WHERE nom_utilisateur = ?");
      $stmt->execute([$nom_utilisateur]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      // Vérification des identifiants
      if (!$user) {
        // L'utilisateur n'existe pas
        $message = "Information incorrecte.";
        $messageType = "error";
      } elseif ($mot_de_passe == $user['mot_de_passe']) {
        // Connexion réussie - Comparaison directe des mots de passe
        $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
        $_SESSION['nom_utilisateur'] = $user['nom_utilisateur'];

        // Redirection vers le tableau de bord
        header("Location: ./vue/dashboard.php");
        exit();
      } elseif (!password_verify($mot_de_passe, $user['mot_de_passe'])) {
        // Le mot de passe est incorrect avec password_verify
        $message = "Information incorrecte).";
        $messageType = "error";
      } else {
        // Connexion réussie - Création de la session
        $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
        $_SESSION['nom_utilisateur'] = $user['nom_utilisateur'];

        // Redirection vers le tableau de bord
        header("Location: ./vue/dashboard.php");
        exit();
      }
    }
  } catch (PDOException $e) {
    $message = "Erreur de connexion à la base de données: " . $e->getMessage();
    $messageType = "error";
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion - Gestion Stock</title>
  <link href="../bootstrap4/boxicons-2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../Css/style.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f7fa;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-container {
      background-color: #ffffff;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
      padding: 30px;
    }

    .login-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .login-header img {
      width: 80px;
      margin-bottom: 15px;
    }

    .login-header h2 {
      color: #0A2558;
      margin: 0;
      font-size: 24px;
      font-family: Maiandra GD !important;
    }

    .contenu-entreprise h2 span {
      font-size: 30px
    }

    .login-form .form-group {
      margin-bottom: 20px;
    }

    .login-form label {
      display: block;
      font-weight: 500;
      margin-bottom: 8px;
      color: #555;
    }

    .login-form input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      box-sizing: border-box;
    }

    .login-form .btn-login {
      background: #0A2558;
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      width: 100%;
      transition: background 0.3s;
    }

    .login-form .btn-login:hover {
      background: #1D3C78;
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="login-header">
      <div class="contenu-entreprise">
        <img src="./icone/Logo.png" alt="Logo S RAYs"><br>
        <h2>
          <span>S</span> RAYs
         Gestion de stock</h2>
      </div>
    </div>

    <?php if (!empty($message)): ?>
      <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <form class="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <div class="form-group">
        <label for="nom_utilisateur">Nom d'utilisateur</label>
        <input type="text" id="nom_utilisateur" name="nom_utilisateur" required>
      </div>

      <div class="form-group">
        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
      </div>

      <div class="form-group">
        <button type="submit" class="btn-login">Se connecter</button>
      </div>
    </form>
  </div>
</body>

</html>