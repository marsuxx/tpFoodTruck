<?php
class Controleur {
	private $vue;
    private $presence;
    private $lieu;

    public function __construct() {
        $this->vue = new Vue();
        $this->presence = new Presence();
        $this->lieu = new Lieu();
    }

    public function accueil() {
		if (isset($_SESSION['connexion'])) {
			$role = $_SESSION['connexion']['role'];

			switch ($role) {
				case 'admin':
					header("Location: index.php?action=admin");
					exit;

				case 'vendeur':
					header("Location: index.php?action=vendeur");
					exit;

				case 'client':
					header("Location: index.php?action=client");
					exit;

				default:
					$vue = new Vue();
					$vue->accueil();
					break;
			}
		} 
		else {
			$vue = new Vue();
			$vue->accueil();
		}
	}

    public function erreur404() {
        $vue = new Vue();
        $vue->erreur404();
    }

    public function inscription() {
		$vue = new Vue();
		$clientModel = new Utilisateur();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (!empty($_POST['mdp']) && $_POST['mdp'] === ($_POST['mdp2'] ?? '')) {

				try {
					$idUtilisateur = $clientModel->ajouterUtilisateur(
						$_POST["nom"],
						$_POST["prenom"],
						$_POST["email"],
						$_POST["telephone"],
						$_POST["mdp"],
						$_POST["role"],
						$_POST["localisationClient"] ?? null,
						$_POST["nomFoodTruck"] ?? null
					);

					echo "<p style='color:green'>Inscription réussie !</p>";
					$this->accueil();

				} 
				catch (PDOException $e) {
					echo "<p style='color:red'>Erreur BDD : ".$e->getMessage()."</p>";
					$vue->inscription();
				}
			} 
			else {
				echo "<p style='color:red'>Les mots de passe ne correspondent pas.</p>";
				$vue->inscription();
			}
		} 
		else {
			$vue->inscription();
		}
	}

	private function deconnexionAuto() {
		session_start();

		if(isset($_SESSION['page_loaded'])) {
			$_SESSION = [];
			session_destroy();

			header("Location: index.php?controller=Utilisateur&action=login");
			exit();
		} 
		else {
			$_SESSION['page_loaded'] = true;
		}
	}

    public function connexion() {
		$vue = new Vue();
		$clientModel = new Utilisateur();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$email = $_POST["email"];
			$mdp   = $_POST["mdp"];

			$client = $clientModel->getByEmail($email);

			if ($client && password_verify($mdp, $client["mdp"])) {
				$_SESSION["connexion"] = $client;

				if ($client["role"] === "admin") {
					header("Location: index.php?action=admin");
					exit();
				} 
				elseif ($client["role"] === "vendeur") {
					$_SESSION["connexion"] = $client;
					$lieux = $this->lieu->getAll();

					if (empty($lieux)) {
						header("Location: index.php?action=ajouterLieu");
						exit;
					} 
					else {
						$this->pageVendeur();
					}
				}

				elseif ($client["role"] === "client") {
					header("Location: index.php?action=client");
					exit();
				} 
				else {
					header("Location: index.php?action=accueil");
					exit();
				}
			} else {
				echo "<p style='color:red;text-align:center;'>Email ou mot de passe incorrect.</p>";
				$vue->connexion();
			}
		} else {
			$vue->connexion();
		}
	}

	public function deconnexion() {
        session_unset();
        session_destroy();

        echo "<script>
                alert('Vous avez été déconnecté.');
                window.location.href = 'index.php?action=accueil';
              </script>";
        exit;
    }

	public function validerVendeur($idV) {
		if (empty($_SESSION['connexion']) || $_SESSION['connexion']['role'] !== 'admin') {
			echo "<p style='color:red;text-align:center;'>Accès refusé.</p>";
			return;
		}

		$userModel = new Utilisateur();
		$userModel->validerVendeur($idV, $_SESSION['connexion']['idUtilisateur']);
		header("Location: index.php?action=admin");
		exit;
	}

	public function refuserVendeur($idV) {
		if (empty($_SESSION['connexion']) || $_SESSION['connexion']['role'] !== 'admin') {
			echo "<p style='color:red;text-align:center;'>Accès refusé.</p>";
			return;
		}

		$userModel = new Utilisateur();
		$userModel->refuserVendeur($idV, $_SESSION['connexion']['idUtilisateur']);
		header("Location: index.php?action=admin");
		exit;
	}

	public function pageAdmin() {
		if (!isset($_SESSION['connexion']) || $_SESSION['connexion']['role'] !== 'admin') {
			echo "<p style='color:red;text-align:center;'>Accès refusé. Vous devez être administrateur.</p>";
			return;
		}

		$utilisateurModel = new Utilisateur();
		$vendeurs = $utilisateurModel->getAllVendeur();

		$vue = new Vue();
		$vue->pageAdmin($vendeurs);
	}

	public function pageVendeur() {
		if (!isset($_SESSION['connexion']) || $_SESSION['connexion']['role'] !== 'vendeur') {
			echo "<p style='color:red;text-align:center;'>Accès refusé.</p>";
			return;
		}

		$vendeur = $_SESSION['connexion'];
		$utilisateurModel = new Utilisateur();

		$estValide = $utilisateurModel->estValider($vendeur['idUtilisateur']);

		$dernierLieu = $this->lieu->dernierLieuAjouter($vendeur['idUtilisateur']);
		$idLieu = $_SESSION['dernierLieuAjoute'] ?? ($dernierLieu['idLieu'] ?? null);

		$presences = $this->presence->getByVendeur($vendeur['idUtilisateur']);
		$lieux = $this->lieu->getAll();

		$this->vue->pageVendeur($estValide, $presences, $lieux);
		unset($_SESSION['dernierLieuAjoute']);
	}

	public function changerStatutPresence($idPresence) {
		if (!isset($_SESSION['connexion']) || $_SESSION['connexion']['role'] !== 'vendeur') {
			echo "<p style='color:red;text-align:center;'>Accès refusé.</p>";
			return;
		}

		try {
			$this->presence->changerStatut($idPresence);
			header("Location: index.php?action=vendeur");
			exit;
		} 
		catch (Exception $e) {
			echo "<p style='color:red;text-align:center;'>Erreur : " . $e->getMessage() . "</p>";
		}
	}

	public function ajouterPresence() {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			echo "<p style='color:red'>Méthode invalide.</p>";
			return;
		}

		if (!isset($_SESSION['connexion'])) {
			echo "<p style='color:red'>Vous devez être connecté.</p>";
			return;
		}

		$idUtilisateur = $_SESSION['connexion']['idUtilisateur'];
		$date = $_POST['date'] ?? null;
		$arrive = $_POST['arrive'] ?? null;
		$depart = $_POST['depart'] ?? null;
		$coordLat = $_POST['coordLat'] ?? null;
		$coordLong = $_POST['coordLong'] ?? null;
		$idLieu = $_POST['idLieu'] ?? null;

		if (!$date || !$arrive || !$depart || !$idLieu) {
			echo "<p style='color:red'>Tous les champs obligatoires doivent être remplis, y compris le lieu.</p>";
			return;
		}

		try {
			$this->presence->ajouterPresence($idUtilisateur, $date, $arrive, $depart, $coordLat, $coordLong, $idLieu);
			header("Location: index.php?action=vendeur");
			exit;
		} 
		catch (Exception $e) {
			echo "<p style='color:red'>Erreur lors de l’ajout de la présence : ".$e->getMessage()."</p>";
		}
	}

	public function formulairePresence() {
		if (!isset($_SESSION['connexion']) || $_SESSION['connexion']['role'] !== 'vendeur') {
			echo "<p style='color:red;text-align:center;'>Accès refusé.</p>";
			return;
		}

		$lieux = $this->lieu->getAll();
		$this->vue->formulairePresence($lieux);
	}

	public function formulaireModifierPresence() {
		if (!isset($_SESSION['connexion']) || $_SESSION['connexion']['role'] !== 'vendeur') {
			echo "<p style='color:red;text-align:center;'>Accès refusé.</p>";
			return;
		}

		$id = $_GET['id'] ?? null;
		if (!$id) {
			$this->erreur404();
			return;
		}

		$presence = $this->presence->getById($id);
		if (!$presence) {
			$this->erreur404();
			return;
		}
		$lieux = $this->lieu->getAll();
		$this->vue->formulaireModifierPresence($presence, $lieux);
	}

	public function modifierPresence() {
		if (!isset($_SESSION['connexion']) || $_SESSION['connexion']['role'] !== 'vendeur') {
			echo "<p style='color:red'>Accès refusé.</p>";
			return;
		}

		$id = $_GET['id'] ?? null;
		if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->erreur404();
			return;
		}

		$date = $_POST['date'] ?? null;
		$arrive = $_POST['arrive'] ?? null;
		$depart = $_POST['depart'] ?? null;
		$idLieu = $_POST['idLieu'] ?? null;

		if (!$date || !$arrive || !$depart || !$idLieu) {
			echo "<p style='color:red'>Tous les champs sont obligatoires.</p>";
			return;
		}

		$this->presence->modifier($id, $date, $arrive, $depart, $idLieu);
		header("Location: index.php?action=vendeur");
		exit;
	}

	public function supprimerPresence() {
		if (!isset($_SESSION['connexion']) || $_SESSION['connexion']['role'] !== 'vendeur') {
			echo "<p style='color:red'>Accès refusé.</p>";
			return;
		}

		$id = $_GET['id'] ?? null;
		if (!$id) {
			echo "<p style='color:red'>ID manquant.</p>";
			return;
		}

		$this->presence->supprimerPresence($id);
		header("Location: index.php?action=vendeur");
		exit;
	}

	public function pageClient() {
		if (!isset($_SESSION['connexion']) || $_SESSION['connexion']['role'] !== 'client') {
			echo "<p style='color:red;text-align:center;'>Accès refusé.</p>";
			return;
		}

		$presenceModel = new Presence();
		$vendeursActifs = $presenceModel->getVendeursActifs();
		//echo var_dump($vendeursActifs);
		$vue = new Vue();
		$vue->pageClient($vendeursActifs);
	}

	public function listeLieux() {
		$this->vue = new Vue();
        $this->lieu = new Lieu();

        $lieux = $this->lieu->getAll();
        $this->vue->pageLieux($lieux);
    }

    public function ajouterLieu() {
		if (!isset($_SESSION['connexion']) || $_SESSION['connexion']['role'] !== 'vendeur') {
			echo "<p style='color:red'>Accès refusé.</p>";
			return;
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$cp = $_POST['cp'];
			$ville = $_POST['ville'];
			$rue = $_POST['rue'];
			$idVendeur = $_SESSION['connexion']['idUtilisateur'];

			$idLieu = $this->lieu->ajouterLieu($cp, $ville, $rue, $idUtilisateur);
			$_SESSION['dernierLieuAjoute'] = $idLieu;
			header("Location: index.php?action=formulairePresence");
			exit;
		} 
		else {
			$this->vue->ajouterLieu();
			exit;
		}
	}
}
?>