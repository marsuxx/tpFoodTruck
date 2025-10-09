<?php
class Presence {
    private $db;

    public function __construct($pdo = null) {
        if ($pdo) {
            $this->db = $pdo;
        } else {
            $config = parse_ini_file("config.ini");
            $this->db = new PDO(
                "mysql:host=".$config['host'].";dbname=".$config['db'].";charset=utf8",
                $config['user'], $config['pass']
            );
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public function ajouterPresence($idUtilisateur, $date, $arrive, $depart, $coordLat = null, $coordLong = null, $idLieu = null) {
        $stmt = $this->db->prepare("INSERT INTO Presence (date, arrive, depart, actif, coordLat, coordLong, idUtilisateur, idLieu)
                                    VALUES (?, ?, ?, 1, ?, ?, ?, ?)");
        return $stmt->execute([$date, $arrive, $depart, $coordLat, $coordLong, $idUtilisateur, $idLieu]);
    }

    public function getByVendeur($idUtilisateur) {
        $stmt = $this->db->prepare("SELECT * FROM Presence WHERE idUtilisateur = ? ORDER BY date DESC, arrive DESC");
        $stmt->execute([$idUtilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVendeursActifs() {
        $stmt = $this->db->query("SELECT Presence.*, Vendeur.nomFoodTruck, Utilisateur.nom, Utilisateur.prenom FROM Presence  JOIN Vendeur  ON Presence.idUtilisateur = Vendeur.idUtilisateur
                                JOIN Utilisateur ON Vendeur.idUtilisateur = Utilisateur.idUtilisateur WHERE Presence.date = CURDATE() AND Presence.arrive <= CURTIME()
                                AND Presence.depart >= CURTIME()AND Presence.actif = 1");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function desactiverPresences($idUtilisateur) {
        $stmt = $this->db->prepare("UPDATE Presence SET actif = 0 WHERE idUtilisateur = ?");
        return $stmt->execute([$idUtilisateur]);
    }
}
?>
