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
        $vue = new Vue();
        $vue->accueil();
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

					echo "<p style='color:green'>Inscription réussie ! ID=$idUtilisateur</p>";
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
					header("Location: index.php?action=vendeur");
					exit();
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

		$lieux = $this->lieu->getAll();

		if (empty($lieux)) {
			header("Location: index.php?action=ajouterLieu");
			exit;
		}

		$presences = $this->presence->getByVendeur($vendeur['idUtilisateur']);
		$this->vue->pageVendeur($estValide, $presences, $lieux);
	}


	public function ajouterPresence() {
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

			if (!$date || !$arrive || !$depart) {
				echo "<p style='color:red'>Tous les champs obligatoires doivent être remplis.</p>";
				return;
			}

			try {
				$this->presence->ajouterPresence($idUtilisateur, $date, $arrive, $depart, $coordLat, $coordLong, $idLieu);
				header("Location: index.php?action=vendeur");
				exit;
			} catch (Exception $e) {
				echo "<p style='color:red'>Erreur lors de l’ajout de la présence : ".$e->getMessage()."</p>";
			}
		} else {
			echo "<p style='color:red'>Méthode invalide.</p>";
		}
	}


	public function pageClient() {
		if (!isset($_SESSION['connexion']) || $_SESSION['connexion']['role'] !== 'client') {
			echo "<p style='color:red;text-align:center;'>Accès refusé.</p>";
			return;
		}

		$presenceModel = new Presence();
		$vendeursActifs = $presenceModel->getVendeursActifs();

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
		$this->vue = new Vue();
        $this->lieu = new Lieu();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cp = $_POST['cp'];
            $ville = $_POST['ville'];
            $rue = $_POST['rue'];
            $this->lieu->ajouterLieu($cp, $ville, $rue);
            header("Location: index.php?action=listeLieux");
            exit;
        } else {
            $this->vue->ajouterLieu();
        }
    }
}
?>