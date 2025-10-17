<?php
class Lieu {
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

    public function ajouterLieu($cp, $ville, $rue) {
        $stmt = $this->db->prepare("INSERT INTO Lieu (cp, ville, rue)VALUES (?, ?, ?)");
        $stmt->execute([$cp, $ville, $rue]);
        return $this->db->lastInsertId();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM Lieu ORDER BY ville, rue");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($idLieu) {
        $stmt = $this->db->prepare("SELECT * FROM Lieu WHERE idLieu = ?");
        $stmt->execute([$idLieu]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function supprimerLieu($idLieu) {
        $stmt = $this->db->prepare("DELETE FROM Lieu WHERE idLieu = ?");
        return $stmt->execute([$idLieu]);
    }

    public function modifierLieu($idLieu, $cp, $ville, $rue) {
        $stmt = $this->db->prepare("UPDATE Lieu SET cp = ?, ville = ?, rue = ? WHERE idLieu = ?");
        return $stmt->execute([$cp, $ville, $rue, $idLieu]);
    }

    public function dernierLieuAjouter() {
        $stmt = $this->db->query("SELECT * FROM Lieu ORDER BY idLieu DESC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
