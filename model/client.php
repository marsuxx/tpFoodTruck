<?php
class Client extends Utilisateur {
    private $idUtilisateur;
    private $localisationClient;

    public function __construct($idUtilisateur = null) {
        parent::__construct(); 
        $this->idUtilisateur = $idUtilisateur;

        if ($idUtilisateur) {
            $this->loadClientInfo();
        }
    }

    private function infoClient() {
        $stmt = $this->db->prepare("SELECT localisationClient FROM Client WHERE idUtilisateur=?");
        $stmt->execute([$this->idUtilisateur]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->localisationClient = $data['localisationClient'];
        }
    }

    public function getLocalisation() {
        return $this->localisationClient;
    }

    public function setLocalisation($localisation) {
        $stmt = $this->db->prepare("UPDATE Client SET localisationClient=? WHERE idUtilisateur=?");
        $stmt->execute([$localisation, $this->idUtilisateur]);
        $this->localisationClient = $localisation;
    }

    public function ajouter($data) {
        return $this->register(
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['tel'],
            $data['motdepasse'],
            $data['role'],
            $data['localisationClient'] ?? null,
            $data['nomFoodTruck'] ?? null
        );
    }
}
?>