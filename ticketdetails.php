<?php
session_start();
include "config.php";
$ticket = [];
if (isset($_SESSION['veruac'])) {
    $uac = $_SESSION['veruac'];
    $stmt = $pdo->prepare('SELECT * FROM tickets WHERE uac = ? LIMIT 1');
    $stmt->execute([$uac]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your QueueDesk Ticket</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="light.css">
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
        <?php
        echo '
        <table id="hidden">
        <tr>
        <th>ID</th>
        <th>TIMESTAMP</th>
        <th>ASSIGNEE</th>
        <th>EMAIL</th>
        <th>TYPE</th>
        <th>SUMMARY</th>
        <th>URGENCY</th>
        </tr>
        ';
        echo("<tr>" . "<td>". $ticket['id'] . "</td><td>". htmlspecialchars($ticket['timestamp'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['assignee'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['email'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['type'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['summary'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['urgency'], ENT_QUOTES, 'UTF-8') . "</td>");      
        echo '</table>';
        echo "<h1>Additional Details</h1>";
        echo "<p>" . htmlspecialchars($ticket['summary'], ENT_QUOTES, 'UTF-8')  . "</p>";
        ?>
    </section>
    <br> 
</body>