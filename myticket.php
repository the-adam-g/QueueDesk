<?php
session_start();
include "config.php";
include 'cmode.php';
$uac = $_GET['uac'];
if (!$uac || !preg_match('/^[a-f0-9]{32}$/', $uac)) {
    header("Location: index.php");
    exit();
}
$uac = htmlspecialchars($uac, ENT_QUOTES, 'UTF-8');
$ticket = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify'])) {
        $stmt = $pdo->prepare('SELECT email FROM tickets WHERE uac = ? LIMIT 1');
        $stmt->execute([$uac]);
        $realemail = $stmt->fetchColumn();
        if ($realemail === $_POST['email']) {
            $_SESSION['veruac'] = $uac;
            header("Location: ticketdetails.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your QueueDesk Ticket</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta property="og:title" content="QueueDesk">
    <meta property="og:description" content="Create a QueueDesk ticket">
    <meta property="og:image" content="idkyet">
    <meta property="og:url" content="http://librebook.co.uk/QueueDesk/">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Golos+Text:wght@400..900&display=swap" rel="stylesheet">
</head>
<body>
    <section id="head">
        <h1>Queue<special>Desk</special></h1>
    </section>
    <section id="messages">
        <h1>Your Queue<special>Desk</special> ticket status:</h1>
        <p>Please enter the email you used to access your ticket status.</p>
        <form action="" method="post">
            <label>Your email: </label><input type="text" name="email" required>
            <input type="submit" id="verify" value="Verify" name="verify">
        </form>
    </section>
    <br> 
</body>
