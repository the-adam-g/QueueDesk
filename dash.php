<?php
session_start();
include 'config.php';
include 'cmode.php';

if (isset($_SESSION['login'])) {
    $id = $_SESSION['user_id'];
    $name = $_SESSION['name'];
    $role = $_SESSION['role'];
} else {
    header('Location: index.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueDesk dashboard</title>
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
        <h1>Queue<special>Desk Dashboard</special></h1>
        <h1 id="head2"><?php echo 'Welcome <special>' . $role . "</special> " . $name;?></special></h1>
        <div id="navbar">
            <a id="navb" href="dash.php">Home</a>
            <a id="navb" href="opentickets.php">Open Tickets</a>
            <a id="navb" href="archive.php">Closed Tickets</a>
            <a id="navb" href="assetregister.php">Asset Register</a>
            <a <?php if ($role !== "admin") { echo 'style="visibility: hidden;"'; } ?> id="navb" href="admin.php">Admin Panel</a>
            <a id="navb" href="settings.php">Settings</a>
            <a id="navb" href="logout.php">Logout</a>
        </div>
        <br>
        <div id="boxes">
            <div id="ibox" onclick="location.href = 'archive.php';">
                <h1>Tickets closed:</h1>
                <?php 
                $countstmt = $pdo->prepare("SELECT COUNT(*) FROM past_tickets WHERE assignee = ?");
                $countstmt->execute([$name]);
                $count = $countstmt->fetchColumn();
                echo "<h1>" . $count . "</h1>";
                ?>
            </div>
            <div <?php $countstmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE assignee = ?"); $countstmt->execute([$name]); $count = $countstmt->fetchColumn(); if ($count > 0) { echo 'id="rbox"'; } else { echo 'id="gbox"'; }?>  onclick="location.href = 'opentickets.php';">
                <h1>Tickets Open:</h1>
                <?php 
                echo "<h1>" . $count . "</h1>";
                ?>
            </div>
            <div id="ibox" onclick="location.href = 'assetregister.php';">
                <h1>Assets registered:</h1>
                <?php 
                $countstmt = $pdo->prepare("SELECT COUNT(*) FROM assetregister WHERE creator = ?");
                $countstmt->execute([$name]);
                $count = $countstmt->fetchColumn();
                echo "<h1>" . $count . "</h1>";
                ?>
            </div>
        </div>
    </section>
    <br> 
</body>