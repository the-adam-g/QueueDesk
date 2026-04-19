<?php
session_start();
include "config.php";
include 'cmode.php';
if (isset($_SESSION['login'])) {
    $id = $_SESSION['user_id'];
    $name = $_SESSION['name'];
    $role = $_SESSION['role'];
} else {
    header('Location: index.php');
    exit();
}
if ($role !== "admin"){
    header('Location: index.php');
    exit();
}
if(!hash_equals($_SESSION['csrf'], $_POST['csrf'])) { 
    die ("<link rel='stylesheet' href='light.css'> <h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1><h1>Fatal request - CSRF Violation <br><special style='color:red;'> Error code: (#b1)</special></h1><a id='navb' style='max-width: 10%;' href='dash.php'>Return</a>");
}

if(isset($_POST['target'])) {
    $target = $_POST['target'];
    $data = $_POST['data'];
    $allowedtables = ['tickets' => 'tickets', 'past_tickets' => 'past_tickets'];
    if (!in_array($data, $allowedtables, true)) {
        echo("<h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1>");
        echo("<h1>Fatal request - Unauthorised Table Access <br><special style='color:red;'> Error code: (#a1)</special></h1>");
        echo "<a id='navb' style='max-width: 10%;' href='dash.php'>Return</a>";
        exit();
    }
    $output = fopen('php://output', 'w');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="export.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    $table = $allowedtables[$data];
    $stmt = $pdo->prepare("SELECT * FROM " . $table . " WHERE assignee = ?");
    $stmt->execute([$target]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    fputcsv($output, array_keys($results[0]));
    foreach ($results as $row) {
        fputcsv($output, $row);
    }
} else {
    $data = $_POST['data'];
}
fclose($output);
exit();