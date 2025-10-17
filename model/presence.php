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
        $sql = "SELECT Presence.idPresence, Presence.date, Presence.arrive, Presence.depart, Presence.actif, Lieu.ville, Lieu.rue, Lieu.cp FROM Presence
                INNER JOIN Lieu  ON Presence.idLieu = Lieu.idLieu WHERE Presence.idUtilisateur = :idUtilisateur ORDER BY Presence.date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idUtilisateur' => $idUtilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($idPresence) {
        $stmt = $this->db->prepare("SELECT * FROM Presence WHERE idPresence = ?");
        $stmt->execute([$idPresence]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getVendeursActifs() {
        $this->db->exec("SET time_zone = '+02:00'");
        $stmt = $this->db->query("SELECT Presence.*, Vendeur.nomFoodTruck, Utilisateur.nom, Utilisateur.prenom FROM Presence  JOIN Vendeur  ON Presence.idUtilisateur = Vendeur.idUtilisateur
                                JOIN Utilisateur ON Vendeur.idUtilisateur = Utilisateur.idUtilisateur WHERE Presence.date = CURDATE() AND Presence.arrive <= CURTIME()
                                AND Presence.depart >= CURTIME()AND Presence.actif = 1");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function desactiverPresences($idUtilisateur) {
        $stmt = $this->db->prepare("UPDATE Presence SET actif = 0 WHERE idUtilisateur = ?");
        return $stmt->execute([$idUtilisateur]);
    }

    public function changerStatut($idPresence) {
        $sql = "UPDATE Presence SET actif = NOT actif WHERE idPresence = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idPresence]);
    }

    public function modifier($idPresence, $date, $arrive, $depart, $idLieu) {
        $stmt = $this->db->prepare("UPDATE Presence SET date = ?, arrive = ?, depart = ?, idLieu = ? WHERE idPresence = ?");
        return $stmt->execute([$date, $arrive, $depart, $idLieu, $idPresence]);
    }

    public function supprimerPresence($idPresence) {
        $stmt = $this->db->prepare("DELETE FROM Presence WHERE idPresence = ?");
        return $stmt->execute([$idPresence]);
    }
}
?>
