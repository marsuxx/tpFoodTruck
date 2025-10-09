<?php
$password = 'amelia2005'; // le mot de passe que tu veux pour l'admin
$hash = password_hash($password, PASSWORD_BCRYPT);
echo $hash;
?>