<?php
// Définir l'encodage UTF-8
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

require('../../fpdf/fpdf.php');

include('../../other/bd.php');

class PDF extends FPDF {
    private $mois_nom;
    private $annee;
    
    function __construct($mois_nom, $annee) {
        parent::__construct();
        $this->mois_nom = $mois_nom;
        $this->annee = $annee;
        
        // Définir l'encodage UTF-8 pour FPDF
        $this->SetAutoPageBreak(true, 15);
        $this->SetMargins(10, 10, 10);
    }
    
    // Fonction pour convertir UTF-8 vers l'encodage FPDF
    function utf8_decode_custom($text) {
        return iconv('UTF-8', 'windows-1252//IGNORE', $text);
    }
    
    function Header() {
        // Ajouter l'image/logo 
        $logoPath = '../../Icone/Logo.png'; // Chemin vers votre logo
        if (file_exists($logoPath)) {
            // Logo à gauche (X=10, Y=6, largeur=30)
            $this->Image($logoPath, 10, 6, 30);
        }
        
        // Titre centré avec décalage pour le logo
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'',0,1); // Ligne vide pour espacer du haut
        $this->Cell(0,10,$this->utf8_decode_custom('TIMESHEET - ' . strtoupper($this->mois_nom) . ' ' . $this->annee),0,1,'C');
        
        // Ligne de séparation
        $this->SetDrawColor(180,180,180);
        $this->Line(10, $this->GetY() + 2, 200, $this->GetY() + 2);
        $this->Ln(8);
    }
    
    function EmployeeHeader($nom, $nb_taches, $total_heure, $moyenne) {
        $this->SetFont('Arial','B',11);
        $this->SetFillColor(240,240,240);
        $this->SetDrawColor(100,100,100);
        
        // En-tête du tableau employé
        $this->Cell(60,10,'Nom Complet',1,0,'C',true);
        $this->Cell(40,10,$this->utf8_decode_custom('Nombre de Tâches'),1,0,'C',true);
        $this->Cell(40,10,'Total Heures',1,0,'C',true);
        $this->Cell(50,10,$this->utf8_decode_custom("Moyenne d'Heures/Tâche"),1,1,'C',true);

        // Données de l'employé
        $this->SetFont('Arial','',10);
        $this->SetFillColor(250,250,250);
        $this->Cell(60,10,$nom,1,0,'L',true);
        $this->Cell(40,10,$nb_taches,1,0,'C',true);
        $this->Cell(40,10,$total_heure." h",1,0,'C',true);
        $this->Cell(50,10,$moyenne." h",1,1,'C',true);
        $this->Ln(5);
    }

    function TaskTable($taches, $nom_employe) {
        $this->SetFont('Arial','B',11);
        $this->SetTextColor(60,60,60);
        $this->Cell(0,10,$this->utf8_decode_custom("Détails des Tâches - ".$nom_employe),0,1);

        $this->SetFont('Arial','B',9);
        $this->SetFillColor(220,220,220);
        $this->SetTextColor(0,0,0);
        $this->SetDrawColor(100,100,100);
        
        // En-tête du tableau des tâches
        $this->Cell(20,10,$this->utf8_decode_custom('N°'),1,0,'C',true);
        $this->Cell(30,10,$this->utf8_decode_custom('Date'),1,0,'C',true);
        $this->Cell(25,10,$this->utf8_decode_custom('Durée'),1,0,'C',true);
        $this->Cell(30,10,$this->utf8_decode_custom('Client'),1,0,'C',true);
        $this->Cell(30,10,$this->utf8_decode_custom('Tâche'),1,0,'C',true);
        $this->Cell(55,10,$this->utf8_decode_custom('Description'),1,1,'C',true);

        $this->SetFont('Arial','',9);
        
        if (empty($taches)) {
            $this->SetFillColor(255,245,245);
            $this->SetTextColor(150,50,50);
            $this->Cell(190,12,$this->utf8_decode_custom('Aucune tâche enregistrée pour ce mois'),1,1,'C',true);
            $this->SetTextColor(0,0,0);
        } else {
            $fillColor = true;
            foreach ($taches as $tache) {
                // Alternance des couleurs de fond
                if ($fillColor) {
                    $this->SetFillColor(248,248,248);
                } else {
                    $this->SetFillColor(255,255,255);
                }
                
                // Gérer le texte long pour éviter les débordements avec UTF-8
                $description = mb_strlen($tache['description_tache'], 'UTF-8') > 25 ? 
                              mb_substr($tache['description_tache'], 0, 22, 'UTF-8') . '...' : 
                              $tache['description_tache'];
                
                $client = mb_strlen($tache['client'], 'UTF-8') > 15 ? 
                         mb_substr($tache['client'], 0, 12, 'UTF-8') . '...' : 
                         $tache['client'];
                
                $tache_nom = mb_strlen($tache['tache'], 'UTF-8') > 15 ? 
                            mb_substr($tache['tache'], 0, 12, 'UTF-8') . '...' : 
                            $tache['tache'];

                $this->Cell(20,10,$tache['id_timesheet'],1,0,'C',true);
                $this->Cell(30,10,date('d/m/Y', strtotime($tache['date_tache'])),1,0,'C',true);
                $this->Cell(25,10,$tache['duree_tache']." h",1,0,'C',true);
                $this->Cell(30,10,$this->utf8_decode_custom($client),1,0,'L',true);
                $this->Cell(30,10,$this->utf8_decode_custom($tache_nom),1,0,'L',true);
                $this->Cell(55,10,$this->utf8_decode_custom($description),1,1,'L',true);
                
                // Ajouter une note si elle existe
                if (!empty($tache['note'])) {
                    $this->SetFont('Arial','I',8);
                    $this->SetFillColor(245,245,255);
                    $note_text = mb_strlen($tache['note'], 'UTF-8') > 80 ? 
                                mb_substr($tache['note'], 0, 77, 'UTF-8') . '...' : 
                                $tache['note'];
                    $this->Cell(190,8,$this->utf8_decode_custom('Note: ' . $note_text),1,1,'L',true);
                    $this->SetFont('Arial','',9);
                }
                
                $fillColor = !$fillColor;
            }
        }

        $this->Ln(10);
    }
    
    // Pied de page
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(100,100,100);
        $this->Cell(0,10,$this->utf8_decode_custom('Page '.$this->PageNo().' - S RAYs le '.date('d/m/Y')),0,0,'C');
    }
}

// === Récupération des paramètres ===
$moisActuel = isset($_GET['mois']) ? intval($_GET['mois']) : intval(date('m'));
$anneeActuelle = isset($_GET['annee']) ? intval($_GET['annee']) : intval(date('Y'));
$filtreEmploye = isset($_GET['employe']) ? $_GET['employe'] : 'tous';

// Vérification de la validité du mois
if ($moisActuel < 1) {
    $moisActuel = 12;
    $anneeActuelle--;
} elseif ($moisActuel > 12) {
    $moisActuel = 1;
    $anneeActuelle++;
}

// Date du premier et dernier jour du mois
$premierJourDuMois = $anneeActuelle . '-' . str_pad($moisActuel, 2, '0', STR_PAD_LEFT) . '-01';
$dernierJourDuMois = date('Y-m-t', strtotime($premierJourDuMois));

// Noms des mois
$nomsMois = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

// === Récupération des données de la base ===
try {
    // Assurer l'encodage UTF-8 pour les requêtes MySQL
    $bdd->exec("SET NAMES utf8");
    
    // Récupérer tous les employés
    $requeteEmployes = $bdd->query('SELECT matricule_emp, nom_emp, prenom_emp FROM employer_login ORDER BY nom_emp, prenom_emp');
    
    $employesInfos = [];
    
    // Initialiser les données pour tous les employés
    while ($employe = $requeteEmployes->fetch(PDO::FETCH_ASSOC)) {
        $matricule = $employe['matricule_emp'];
        $nomComplet = trim($employe['nom_emp'] . ' ' . $employe['prenom_emp']);
        
        $employesInfos[$matricule] = [
            'nom_complet' => $nomComplet,
            'total_heures' => 0,
            'nb_taches' => 0,
            'taches' => []
        ];
    }
    
    // Construire la requête pour récupérer les tâches avec filtrage optionnel
    $sql = '
        SELECT t.*, e.nom_emp, e.prenom_emp
        FROM timesheet t
        JOIN employer_login e ON e.matricule_emp = t.matricule_emp
        WHERE t.date_tache BETWEEN :debut AND :fin
    ';
    
    $params = [
        'debut' => $premierJourDuMois,
        'fin' => $dernierJourDuMois
    ];
    
    // Ajouter le filtre par employé si nécessaire
    if ($filtreEmploye !== 'tous') {
        $sql .= ' AND t.matricule_emp = :matricule';
        $params['matricule'] = $filtreEmploye;
    }
    
    $sql .= ' ORDER BY e.matricule_emp, t.id_timesheet DESC';
    
    $requeteTaches = $bdd->prepare($sql);
    $requeteTaches->execute($params);
    
    // Compléter les informations des employés avec leurs tâches
    while ($tache = $requeteTaches->fetch(PDO::FETCH_ASSOC)) {
        $matricule = $tache['matricule_emp'];
        
        if (isset($employesInfos[$matricule])) {
            $employesInfos[$matricule]['taches'][] = $tache;
            $employesInfos[$matricule]['total_heures'] += intval($tache['duree_tache']);
            $employesInfos[$matricule]['nb_taches']++;
        }
    }
    
    // Filtrer les employés à afficher SI un filtre spécifique est appliqué
    if ($filtreEmploye !== 'tous') {
        $employesInfosFiltres = [];
        if (isset($employesInfos[$filtreEmploye])) {
            $employesInfosFiltres[$filtreEmploye] = $employesInfos[$filtreEmploye];
        }
        $employesInfos = $employesInfosFiltres;
    }
    
} catch (Exception $e) {
    die('Erreur lors de la récupération des données : ' . $e->getMessage());
}

// === Création du PDF ===
$pdf = new PDF($nomsMois[$moisActuel], $anneeActuelle);
$pdf->AddPage();

if (empty($employesInfos)) {
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,10,$pdf->utf8_decode_custom('Aucun employé trouvé dans la base de données.'),0,1,'C');
} else {
    $compteurEmployes = 0;
    $totalEmployes = count($employesInfos);
    
    foreach ($employesInfos as $matricule => $employe) {
        $compteurEmployes++;
        
        $moyenne = $employe['nb_taches'] > 0 ? 
                  round($employe['total_heures'] / $employe['nb_taches'], 2) : 0;
        
        $pdf->EmployeeHeader(
            $employe['nom_complet'], 
            $employe['nb_taches'], 
            $employe['total_heures'], 
            $moyenne
        );
        
        $pdf->TaskTable($employe['taches'], $employe['nom_complet']);
        
        // Ajouter une nouvelle page si ce n'est pas le dernier employé
        if ($compteurEmployes < $totalEmployes) {
            $pdf->AddPage();
        }
    }
    
    // Ajouter une page de résumé si tous les employés sont affichés
    if ($filtreEmploye === 'tous' && $totalEmployes > 1) {
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,$pdf->utf8_decode_custom('RÉSUMÉ GÉNÉRAL'),0,1,'C');
        $pdf->Ln(5);
        
        $totalHeuresGlobal = 0;
        $totalTachesGlobal = 0;
        $employesAvecTaches = 0;
        
        $pdf->SetFont('Arial','B',10);
        $pdf->SetFillColor(220,220,220);
        $pdf->Cell(80,10,$pdf->utf8_decode_custom('Employé'),1,0,'C',true);
        $pdf->Cell(30,10,$pdf->utf8_decode_custom('Tâches'),1,0,'C',true);
        $pdf->Cell(30,10,$pdf->utf8_decode_custom('Heures'),1,0,'C',true);
        $pdf->Cell(50,10,$pdf->utf8_decode_custom('Moyenne'),1,1,'C',true);
        
        $pdf->SetFont('Arial','',9);
        foreach ($employesInfos as $employe) {
            $moyenne = $employe['nb_taches'] > 0 ? 
                      round($employe['total_heures'] / $employe['nb_taches'], 2) : 0;
            
            $fillColor = $employe['nb_taches'] > 0 ? [248,255,248] : [255,248,248];
            $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
            
            $nomAffiche = mb_strlen($employe['nom_complet'], 'UTF-8') > 35 ? 
                         mb_substr($employe['nom_complet'], 0, 32, 'UTF-8') . '...' : 
                         $employe['nom_complet'];
                         
            $pdf->Cell(80,8,$pdf->utf8_decode_custom($nomAffiche),1,0,'L',true);
            $pdf->Cell(30,8,$employe['nb_taches'],1,0,'C',true);
            $pdf->Cell(30,8,$employe['total_heures'].' h',1,0,'C',true);
            $pdf->Cell(50,8,$moyenne.' h',1,1,'C',true);
            
            $totalHeuresGlobal += $employe['total_heures'];
            $totalTachesGlobal += $employe['nb_taches'];
            if ($employe['nb_taches'] > 0) $employesAvecTaches++;
        }
        
        // Ligne de total
        $pdf->SetFont('Arial','B',10);
        $pdf->SetFillColor(200,200,200);
        $pdf->Cell(80,10,$pdf->utf8_decode_custom('TOTAL'),1,0,'C',true);
        $pdf->Cell(30,10,$totalTachesGlobal,1,0,'C',true);
        $pdf->Cell(30,10,$totalHeuresGlobal.' h',1,0,'C',true);
        $moyenneGlobale = $totalTachesGlobal > 0 ? round($totalHeuresGlobal / $totalTachesGlobal, 2) : 0;
        $pdf->Cell(50,10,$moyenneGlobale.' h',1,1,'C',true);
    }
}

// Générer le nom du fichier automatiquement
$nomFichier = 'timesheet_' . $nomsMois[$moisActuel] . '_' . $anneeActuelle;

if ($filtreEmploye !== 'tous') {
    // Si un employé spécifique est filtré, ajouter son nom
    if (isset($employesInfos[$filtreEmploye])) {
        $nomEmploye = $employesInfos[$filtreEmploye]['nom_complet'];
        // Nettoyer le nom pour le nom de fichier (enlever caractères spéciaux)
        $nomEmploye = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nomEmploye);
        $nomEmploye = preg_replace('/_+/', '_', $nomEmploye); // Remplacer multiples underscores
        $nomEmploye = trim($nomEmploye, '_'); // Enlever underscores en début/fin
        $nomFichier .= '_' . $nomEmploye;
    }
} else {
    // Si tous les employés, ajouter des informations sur le nombre d'employés
    $nomFichier .= '_tous_employes_(' . count($employesInfos) . ')';
}

$nomFichier .= '.pdf';

$pdf->Output('D', $nomFichier);
?>