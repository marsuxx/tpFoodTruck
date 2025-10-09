<?php
	session_start();

	// Test de connexion à la base
	$config = parse_ini_file('config.ini');

	$host = $config['host'];
	$user = $config['user'];
	$pass = $config['pass'];
	$db = $config['db'];

	try {
		$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//echo "Connexion réussie !<br>";
	} catch (PDOException $e) {
		die("Erreur de connexion : " . $e->getMessage());
	}

	// Chargement des fichiers MVC
	require("controler/controleur.php");
	require("view/vue.php");
	require("model/utilisateur.php");
	require("model/client.php");
	require("model/vendeur.php");
	require("model/presence.php");
	require("model/lieu.php");

	$controleur = new Controleur();
	$action = $_GET["action"] ?? "accueil";
	// Routes
	if(isset($_GET["action"])) {
		switch($_GET["action"]) {
			case "accueil":
				$controleur->accueil();
				break;

			case "connexion":
				$controleur->connexion();
				break;

			case "inscription":
				$controleur->inscription();
				break;

			case 'admin': 
				$controleur->pageAdmin(); 
				break;

			case 'validerVendeur':
				$controleur->validerVendeur($_GET['idV']);
				break;

			case 'refuserVendeur':
				$controleur->refuserVendeur($_GET['idV']);
				break;

			case 'vendeur': 
				$controleur->pageVendeur(); 
				break;

			case "ajouterLieu":
				$controleur->ajouterLieu();
				break;

			case "listeLieux":
				$controleur->listeLieux();
				break;
			
			case "ajouterPresence":
				if ($_SERVER['REQUEST_METHOD'] === 'POST') {
					$controleur->ajouterPresence();
				}
				break;

			case 'client': 
				$controleur->pageClient(); 
				break;

			case "deconnexion":
				(new controleur)->deconnexion();
				break;
			
			default:
				if(method_exists($controleur, 'erreur404')) {
					$controleur->erreur404();
				} else {
					$controleur->accueil();
				}
				break;
		}
	}
	else {
		$controleur->accueil();
	}
?>