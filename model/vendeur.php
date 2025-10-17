<?php
class Vendeur extends Utilisateur {
    private $idV;
    private $idUtilisateur;
    private $nomFoodTruck;
    private $statut;
    private $validePar;
    private $dateValidation;

    public function __construct($idUtilisateur) {
        parent::__construct();
        $this->idUtilisateur = $idUtilisateur;
        $this->loadVendeurInfo();
    }

    private function infoVendeur() {
        $stmt = $this->db->prepare("SELECT * FROM Vendeur WHERE idUtilisateur=?");
        $stmt->execute([$this->idUtilisateur]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->idUtilisateur = $data['idUtilisateur'];
            $this->nomFoodTruck = $data['nomFoodTruck'];
            $this->statut = $data['statut'];
            $this->validePar = $data['validePar'];
            $this->dateValidation = $data['dateValidation'];
        }
    }

    public function estValide() {
        return $this->statut === 'valide';
    }

    public function changerStatut($statut, $idUtilisateur) {
        $stmt = $this->db->prepare("UPDATE Vendeur SET statut=?, validePar=?, dateValidation=NOW() WHERE idUtilisateur=?");
        $stmt->execute([$statut, $idAdmin, $this->idUtilisateur]);

        $this->statut = $statut;
        $this->validePar = $idUtilisateur;
        $this->dateValidation = date('d-m-Y H:i:s');
    }

    public function getNomFoodTruck() {
        return $this->nomFoodTruck;
    }

    public function setNomFoodTruck($nom) {
        $stmt = $this->db->prepare("UPDATE Vendeur SET nomFoodTruck=? WHERE idUtilisateur=?");
        $stmt->execute([$nom, $this->idUtilisateur]);
        $this->nomFoodTruck = $nom;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function getValidePar() {
        return $this->validePar;
    }

    public function getDateValidation() {
        return $this->dateValidation;
    }

    public function getIdUtilisateur() {
        return $this->idUtilisateur;
    }
}
