<?php
class Utilisateur {
    private $db;

    public function __construct($pdo = null) {
        if ($pdo) {
            $this->db = $pdo;
        } else {
            $config = parse_ini_file("config.ini");
            $this->db = new PDO(
                "mysql:host=".$config['host'].";dbname=".$config['db'].";charset=utf8",
                $config['user'],
                $config['pass']
            );
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public function ajouterUtilisateur($nom, $prenom, $email, $telephone, $mdp, $role, $localisationClient = null, $nomFoodTruck = null) {
        $hashed = password_hash($mdp, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("INSERT INTO Utilisateur (nom, prenom, email, telephone, mdp, role) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $telephone, $hashed, $role]);
        $idUtilisateur = $this->db->lastInsertId();

        if ($role === 'client') {
            $stmt2 = $this->db->prepare("INSERT INTO Client (idUtilisateur, localisationClient) 
                                         VALUES (?, ?)");
            $stmt2->execute([$idUtilisateur, $localisationClient]);
        } 
        elseif ($role === 'vendeur') {
            $stmt2 = $this->db->prepare("INSERT INTO Vendeur (idUtilisateur, nomFoodTruck, statut, validePar) 
                                         VALUES (?, ?, 'en_attente', NULL)");
            $stmt2->execute([$idUtilisateur, $nomFoodTruck]);
        }

        return $idUtilisateur;
    }

    public function getAllVendeur() {
        $sql = "SELECT 
                    Vendeur.idUtilisateur,
                    Utilisateur.email,
                    Utilisateur.nom,
                    Utilisateur.prenom,
                    Vendeur.nomFoodTruck,
                    Vendeur.statut,
                    Vendeur.validePar,
                    Vendeur.dateValidation
                FROM Vendeur
                INNER JOIN Utilisateur ON Vendeur.idUtilisateur = Utilisateur.idUtilisateur";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function validerVendeur($idUtilisateurVendeur, $idAdmin) {
        $stmt = $this->db->prepare("UPDATE Vendeur 
                                    SET statut='valide', validePar=?, dateValidation=NOW() 
                                    WHERE idUtilisateur=?");
        return $stmt->execute([$idAdmin, $idUtilisateurVendeur]);
    }

    public function refuserVendeur($idUtilisateurVendeur, $idAdmin) {
        $stmt = $this->db->prepare("UPDATE Vendeur 
                                    SET statut='refuse', validePar=?, dateValidation=NOW() 
                                    WHERE idUtilisateur=?");
        return $stmt->execute([$idAdmin, $idUtilisateurVendeur]);
    }

    public function estValider($idUtilisateurVendeur) {
        $stmt = $this->db->prepare("SELECT statut FROM Vendeur WHERE idUtilisateur=?");
        $stmt->execute([$idUtilisateurVendeur]);
        return $stmt->fetchColumn() === 'valide';
    }

    public function getByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM Utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
