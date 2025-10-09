<?php
class Vue {
    public function entete() {
        echo "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <title>FoodTruck</title>
            <link rel='stylesheet' href='css/style.css'>
        </head>
        <body>
            <header>
                <h1 class='site-title'>Bienvenue sur la carte des FoodTruck</h1>
                <nav class='main-nav'>
                    <a href='index.php?action=accueil'>Accueil</a> |
                    <a href='index.php?action=connexion'>Connexion</a> |
                    <a href='index.php?action=inscription'>Inscription</a>
                </nav>
            </header>
            <main>
        ";
    }

    private function fin() {
        echo "
            </main>
        </body>
        </html>
        ";
    }

    public function accueil() {
        $this->entete();
        echo "<div class='contenu'>
                <h2 class='page-title'>Bienvenue sur le site des FoodTrucks</h2>
                <p class='center-text'>
                    <a href='index.php?action=inscription'>Inscrivez-vous</a> ou 
                    <a href='index.php?action=connexion'>Connectez-vous</a>
                </p>
              </div>";
        $this->fin();
    }

    public function erreur404() {
        $this->entete();
        echo "<div class='contenu'>
                <h2 class='error-title'>Erreur 404</h2>
                <p class='center-text'>La page que vous recherchez n'existe pas ou a été supprimée.</p>
                <p class='center-text'><a href='index.php?action=accueil'>Retour à l'accueil</a></p>
              </div>";
        $this->fin();
    }

    public function inscription() {
        $this->entete();
        echo "<div class='contenu'>
                <h2 class='page-title'>Inscription</h2>
                <form action='index.php?action=inscription' method='POST' class='form'>
                    <label for='nom'>Nom :</label>
                    <input type='text' name='nom' id='nom' required>

                    <label for='prenom'>Prénom :</label>
                    <input type='text' name='prenom' id='prenom' required>

                    <label for='email'>Email :</label>
                    <input type='email' name='email' id='email' required>

                    <label for='telephone'>Téléphone :</label>
                    <input type='text' name='telephone' id='telephone' required>

                    <label for='mdp'>Mot de passe :</label>
                    <input type='password' name='mdp' id='mdp' required>

                    <label for='mdp2'>Confirmez le mot de passe :</label>
                    <input type='password' name='mdp2' id='mdp2' required>

                    <label for='role'>Vous êtes :</label>
                    <select name='role' id='role' required onchange='toggleFields()'>
                        <option value=''>-- Sélectionnez --</option>
                        <option value='client'>Client</option>
                        <option value='vendeur'>Vendeur</option>
                    </select>

                    <div id='clientFields' class='extra-fields'>
                        <label for='localisationClient'>Localisation (ville) :</label>
                        <input type='text' name='localisationClient' id='localisationClient'>
                    </div>

                    <div id='vendeurFields' class='extra-fields'>
                        <label for='nomFoodTruck'>Nom du FoodTruck :</label>
                        <input type='text' name='nomFoodTruck' id='nomFoodTruck'>
                    </div>

                    <button type='submit' class='btn-submit'>S'inscrire</button>
                </form>

                <script>
                    function toggleFields() {
                        var role = document.getElementById('role').value;
                        document.getElementById('clientFields').style.display = role === 'client' ? 'block' : 'none';
                        document.getElementById('vendeurFields').style.display = role === 'vendeur' ? 'block' : 'none';
                    }
                </script>
              </div>";
        $this->fin();
    }

    public function connexion() {
        $this->entete();
        echo "<div class='contenu'>
                <h2 class='page-title'>Connexion</h2>
                <form method='POST' action='index.php?action=connexion' class='form'>
                    <label for='email'>Adresse email</label>
                    <input type='email' name='email' id='email' required>

                    <label for='mdp'>Mot de passe</label>
                    <input type='password' name='mdp' id='mdp' required>

                    <button type='submit' class='btn-submit'>Connexion</button>
                </form>
                <p class='center-text'>Pas encore inscrit ? <a href='index.php?action=inscription'>Inscrivez-vous ici</a></p>
              </div>";
        $this->fin();
    }

    public function pageAdmin($vendeurs) {
        $this->entete();
        echo "<div class='admin-container'>
                <h2 class='page-title'>Espace Administrateur</h2>
                <p class='center-text'>Validation des vendeurs</p>";

        if (empty($vendeurs)) {
            echo "<p class='center-text no-vendeur'>Aucun vendeur en attente.</p>";
        } 
        else {
            echo "<table class='admin-table'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>";
            foreach ($vendeurs as $v) {
                $idV = $v['idV'] ?? $v['idUtilisateur'];
                echo "<tr>
                        <td>$idV</td>
                        <td>{$v['nom']}</td>
                        <td>{$v['prenom']}</td>
                        <td>{$v['email']}</td>
                        <td class='status {$v['statut']}'>{$v['statut']}</td>
                        <td class='actions'>";
                if ($v['statut'] === 'en_attente') {
                    echo "<a class='btn validate' href='index.php?action=validerVendeur&idV=$idV'>Valider</a>
                          <a class='btn refuse' href='index.php?action=refuserVendeur&idV=$idV'>Refuser</a>";
                } 
                else {
                    echo ucfirst($v['statut']);
                }
                echo "</td></tr>";
            }
            echo "</tbody></table>";
        }

        echo "</div>";
        $this->fin();
    }

    public function ajouterLieu() {
        $this->entete();
        echo "<div class='contenu'>
                <h2>Ajouter un lieu</h2>
                <form method='POST' action='index.php?action=ajouterLieu' class='form'>
                    <label for='cp'>Code postal :</label>
                    <input type='text' name='cp' id='cp' required>

                    <label for='ville'>Ville :</label>
                    <input type='text' name='ville' id='ville' required>

                    <label for='rue'>Rue :</label>
                    <input type='text' name='rue' id='rue' required>

                    <button type='submit' class='btn-submit'>Ajouter le lieu</button>
                </form>
            </div>";
        $this->fin();
    }

    public function pageVendeur($estValide, $presences) {
        $this->entete();

        echo "<div class='vendeur-container'>";
        echo "<h2>Espace Vendeur</h2>";

        if (!$estValide) {
            echo "<p class='alerte'>Votre compte est en attente de validation par un administrateur.</p>";
        } 
        else {
            echo "
            <section class='form-section'>
                <h3>Indiquez votre position actuelle :</h3>
                <form method='POST' action='index.php?action=ajouterPresence' class='form-vendeur'>
                    <label for='date'>Date :</label>
                    <input type='date' name='date' id='date' required>

                    <label for='arrive'>Heure d'arrivée :</label>
                    <input type='time' name='arrive' id='arrive' required>

                    <label for='depart'>Heure de départ :</label>
                    <input type='time' name='depart' id='depart' required>

                    <label for='coordLat'>Latitude :</label>
                    <input type='text' name='coordLat' id='coordLat' placeholder='Ex: 48.8566'>

                    <label for='coordLong'>Longitude :</label>
                    <input type='text' name='coordLong' id='coordLong' placeholder='Ex: 2.3522'>

                    <button type='submit'>Partager ma position</button>
                </form>
            </section>";

            echo "<section class='table-section'>";
            echo "<h3>Vos présences enregistrées :</h3>";

            if (empty($presences)) {
                echo "<p class='no-data'>Aucune présence enregistrée.</p>";
            } else {
                echo "<table class='table-vendeur'>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Heure d'arrivée</th>
                                <th>Heure de départ</th>
                                <th>Actif</th>
                            </tr>
                        </thead>
                        <tbody>";
                foreach ($presences as $p) {
                    echo "<tr>
                            <td>{$p['date']}</td>
                            <td>{$p['arrive']}</td>
                            <td>{$p['depart']}</td>
                            <td>" . ($p['actif'] ? "Oui" : "Non") . "</td>
                        </tr>";
                }
                echo "</tbody></table>";
            }
            echo "</section>";
        }

        echo "</div>";
        $this->fin();
    }

    public function pageClient($vendeursActifs) {
        $this->entete();

        echo "<div class='client-container'>";
        echo "<h2>Carte des FoodTrucks</h2>";

        if (empty($vendeursActifs)) {
            echo "<p class='no-data'>Aucun vendeur actuellement en activité.</p>";
        } 
        else {
            echo "<div id='map' style='width:80%;height:500px;margin:auto;'></div>";

            echo "<script>
                function initMap() {
                    var map = new google.maps.Map(document.getElementById('map'), {
                        zoom: 12,
                        center: {lat: 48.8566, lng: 2.3522} // Paris par défaut
                    });
            ";

            foreach ($vendeursActifs as $v) {
                echo "
                    new google.maps.Marker({
                        position: {lat: {$v['coordLat']}, lng: {$v['coordLong']}},
                        map: map,
                        title: '{$v['nomFoodTruck']}'
                    });
                ";
            }

            echo "
                }
            </script>
            <script async defer src='https://maps.googleapis.com/maps/api/js?key=TON_API_KEY&callback=initMap'></script>";
        }
        echo "</div>";
        $this->fin();
    }
}
?>
