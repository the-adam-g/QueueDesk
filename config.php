<?php

$db_host = '';
$db_name = '';
$db_user = '';
$db_password = '';

//These are the default passwords and we recommend that you change these
define('ADMIN_UNIVERSAL', password_hash('Super_Admin258$!', PASSWORD_DEFAULT)); //This one goes into the authorisation password field
define('ADMIN_PASS', password_hash('274Auth__Admin$!', PASSWORD_DEFAULT)); //This one goes into the admin registration field to register a user as an admin rather than technician
define('ALLOW_REGISTRATION', true);
define('MAX_ATTEMPTS', 5);
define('LOCK_TIME', 300); // Defines how long users have to login before their sessions locks
define('OFFSET', 10); //how large the offset is in the pages of tickets in admin panel
define('LIMIT', 10); //how many tickets are loaded in the pages

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
