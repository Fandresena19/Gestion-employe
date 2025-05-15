<?php
include('../other/head.php');
$employe = $_SESSION['Matricule_emp'];

$employes = $bdd->query('select * from employer_login WHERE matricule_emp ="'.$employe.'"');
$matricule = $bdd->query('SELECT matricule FROM employer_login WHERE matricule_emp ='.$employe) ->fetch(PDO::FETCH_ASSOC);

$date = new DateTime('now', new DateTimeZone('GMT+3'));

/// Récupérer le dernier matricule
$result = $bdd->query("SELECT id_conge FROM conge LIMIT 1");

// Vérifier si un matricule existe déjà
$last_id = ($result->rowCount() > 0) ? intval($result->fetch()['id_conge']) + 1 : 1;


// Afficher le message de succès s'il existe
if (isset($_SESSION['success_message'])) {
  echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
  unset($_SESSION['success_message']); // Supprimer le message après affichage
}

// Afficher le message d'erreur s'il existe
if (isset($_SESSION['error_message'])) {
  echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
  unset($_SESSION['error_message']); // Supprimer le message après affichage
}
?>

<?php

// Récupérer les données de l'utilisateur connecté
$matricule_emp = $_SESSION['Matricule_emp'];

// Calculer le quota de congé annuel et le reste disponible
$quotaCongeAnnuel = 30;
$congePrise = 0;

// Récupérer tous les congés validés de l'employé pour l'année en cours
$sql = "SELECT * FROM conge 
        WHERE matricule_emp = :matricule_emp 
        AND statut_conge = 'Validé' 
        AND YEAR(date_debut) = :annee_en_cours";
$stmt = $bdd->prepare($sql);
$stmt->execute([
  'matricule_emp' => $matricule_emp,
  'annee_en_cours' => date('Y')
]);

// Calculer le total des jours de congé déjà pris
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $dateDebut = new DateTime($row['date_debut']);
  $dateFin = new DateTime($row['date_fin']);
  $interval = date_diff($dateDebut, $dateFin);

  $dureeAbsenceJ = $interval->days;
  $dureeAbsenceH = $interval->h;

  // Convertir heures en jours
  $dureeAbsenceEnJours = $dureeAbsenceJ + ($dureeAbsenceH / 24);

  // Mettre à jour le cumul des congés pris
  $congePrise += $dureeAbsenceEnJours;
}

// Calculer le reste de congé disponible
$resteConge = $quotaCongeAnnuel - $congePrise;

// Fonction pour vérifier si la demande est valide
function verifierDemandeConge($dateDebut, $dateFin, $resteConge)
{
  // Convertir les dates en objets DateTime
  $debut = new DateTime($dateDebut);
  $fin = new DateTime($dateFin);

  // Calculer l'intervalle
  $interval = date_diff($debut, $fin);

  // Calculer la durée de l'absence
  $dureeAbsenceJ = $interval->days;
  $dureeAbsenceH = $interval->h;

  // Convertir en jours
  $dureeAbsenceEnJours = $dureeAbsenceJ + ($dureeAbsenceH / 24);

  // Vérifier si la durée demandée est supérieure au reste disponible
  if ($dureeAbsenceEnJours > $resteConge) {
    return [
      'valide' => false,
      'message' => "Erreur: Vous demandez $dureeAbsenceEnJours jours, mais il ne vous reste que $resteConge jours de congé disponibles.",
      'duree_demandee' => $dureeAbsenceEnJours
    ];
  }

  return [
    'valide' => true,
    'message' => "Demande valide. Durée: $dureeAbsenceEnJours jours sur $resteConge jours disponibles.",
    'duree_demandee' => $dureeAbsenceEnJours
  ];
}

?>


<div class="annotation">
  <h2>Ajout Congé</h2>
</div>

<div class="contenu">
  <header>
    <h4>Ajouter congé</h4>

    <div class="bouton">
      <a href="./mes_conge.php"><button>Retour</button></a>
    </div>
  </header><br>

  <form action="../traitement/add_conge.php" method="post">
    <div class="form-group">
      <label>L'interessé</label>
      <input type="hidden" name="emp" class="form-control" value="<?php echo $employe ?>" readonly>
      <input type="text" class="form-control" value="<?= $matricule['matricule'] ?>" readonly>
    </div>

    <div class="form-group">
      <label for="">Congé N°</label>
      <input name="ref" type="text" class="form-control" required>
    </div>

    <div class="form-group">
      <label>Motif de la demande</label>
      <textarea name="motif" class="form-control" required> </textarea>
    </div>

    <div class="form-group">
      <label>Date de la demande</label>
      <input type="datetime-local" name="dateDem" class="form-control" value="<?php echo $date->format('Y-m-d\TH:i'); ?>" min="<?= date('Y-m-d\TH:i'); ?>" readonly />
    </div>

    <div class="form-group">
      <label>Date debut</label>
      <input type="datetime-local" name="dateD" class="form-control" id="date_debut" required>
    </div>
    <div class="form-group">
      <label>Date fin</label>
      <input type="datetime-local" name="dateF" class="form-control" id="date_fin" required>
    </div><br>

    <div class="form-group">
      <button type="submit" class="bouton_confirmer">Demander congé</button>
    </div>
  </form>
</div>


<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');

    form.addEventListener('submit', function(e) {
      const dateDebut = document.getElementById('date_debut').value;
      const dateFin = document.getElementById('date_fin').value;

      if (!dateDebut || !dateFin) {
        return true; // Laissez la validation HTML5 gérer ce cas
      }

      // Vérification côté client avant envoi du formulaire
      const debutDate = new Date(dateDebut);
      const finDate = new Date(dateFin);

      if (debutDate >= finDate) {
        alert("La date de début doit être antérieure à la date de fin.");
        e.preventDefault();
        return false;
      }

      // Le reste de la validation sera géré côté serveur
      return true;
    });
  });
</script>