<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./bootstrap4/css/bootstrap.min.css">
  <link rel="stylesheet" href="./bootstrap4/boxicons-2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="./css/login.css">
  <!-- <link rel="stylesheet" href="./css/teste.css"> -->
  <title>Se connecter</title>
</head>

<body>


  <div class="container-fluid">
    <div class="contenu-entreprise">
      <img src="./Icone/Logo.png" alt="Logo S RAYs"><br>
      <h3 align="center">
        Cabinet d'audit 
        <span>S</span> RAYs
      </h3>
    </div>
    
    <div class="row well" id="Contenu_login">
      <form action="login.php" method="post">
        <?php if (isset($_GET['error'])) { ?>
          <p id="error"><?php echo $_GET['error']; ?></p>
        <?php } ?>
        <h2>Se connecter</h2>

        <div class="form-group" id="Forme">
          <label for="Email">Email</label>
          <input type="Email" name="mail" class="form-control" placeholder="example@gmail.com" id="Email" required>
        </div>

        
        <div id="show">
          <div class="form-group" id="Show">
            <label for="mdp">Mot de passe</label>
            <span><input type="password" name="mdp" class="form-control" placeholder="Mot de passe" id="mdp" required />
              <i class="bx bx-hide" id="icone"></i></span><br>
          </div>
        </div>

        <table>
          <tr>
            <td>
              <div class="form-group" id="Forme">
                <input type="radio" name="user" value="resp" id="resp" />
                <label for="resp">Responsable</label>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="form-group" id="Forme">
                <input type="radio" name="user" value="emp" id="Emp" />
                <label for="Emp">Employé</label>
              </div>
            </td>
          </tr>
        </table>


        <div id="login_btn">
          <button type="Submit">Connecte</button>
        </div>

      </form>
    </div>
  </div>

  <script src="./js/Password.js"></script>
</body>

</html>