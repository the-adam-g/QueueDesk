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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticketid = $_POST["id"];
    $stmt = $pdo->prepare('INSERT INTO past_tickets SELECT * FROM tickets WHERE id = ?');
    $stmt->execute([$ticketid]);
    $stmt2 = $pdo->prepare('DELETE FROM tickets WHERE id = ?');
    $stmt2->execute([$ticketid]);
    $stmt3 = $pdo->prepare('UPDATE users SET tickets = tickets - 1 WHERE username = ?');
    $stmt3->execute([$name]);
    $stmt4 = $pdo->prepare('UPDATE users SET solved = solved + 1 WHERE username = ?');
    $stmt4->execute([$name]);
    echo $ticketid;
    header("Location: opentickets.php");
    exit;
}
$selectedticket = null;
if (isset($_GET['ticket'])) {
    $ticketid = $_GET['ticket'];
    if (filter_var($ticketid, FILTER_VALIDATE_INT)) {
        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND assignee = ?");
        $stmt->execute([$ticketid, $name]);
        $selectedticket = $stmt->fetch(PDO::FETCH_ASSOC);
    }
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
        <h1>Queue<special>Desk Open Tickets</special></h1>
        <h1 id="head2"><?php echo 'Welcome <special>' . $role . "</special> " . $name;?></special></h1>
        <div id="navbar">
            <a id="navb" href="dash.php">Home</a>
            <a id="navb" href="archive.php">Closed Tickets</a>
            <a id="navb" href="assetregister.php">Asset Register</a>
            <a <?php if ($role !== "admin") { echo 'style="visibility: hidden;"'; } ?> id="navb" href="admin.php">Admin Panel</a>
            <a id="navb" href="logout.php">Logout</a>
        </div>
        <div id="container">
            <div id="left">
                <p>Tickets:</p>
                <table>
                <tr>
                    <th>ID</th>
                    <th>CREATOR</th>
                    <th>SUMMARY</th>
                    <th>URGENCY</th>
                    <th>TIMESTAMP</th>
                </tr>
                <?php
                $stmt = $pdo->prepare('SELECT * FROM tickets WHERE assignee = ? ORDER BY urgency ASC');
                $stmt->execute([$name]);
                $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($tickets as $ticket) {
                    switch ($ticket['urgency']) {
                        case 1:
                            $urgency = "Emergency";
                            break;
                        case 2:
                            $urgency = "Immediate";
                            break;
                        case 3:
                            $urgency = "Urgent";
                            break;
                        case 4:
                            $urgency = "Non Urgent";
                            break;
                        case 5:
                            $urgency = "Not Major";
                            break;
                        case 6:
                            $urgency = "Other / Not sure";
                            break;
                        default:
                            $urgency = "Other / Not sure";
                            break;
                    }
                    echo("<tr onclick=\"window.location='opentickets.php?ticket=" . (int)$ticket['id'] . "'\">" . "<td>". $ticket['id'] . "</td><td>". htmlspecialchars($ticket['creator'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['summary'], ENT_QUOTES, 'UTF-8') . "</td><td>". $urgency . "</td><td>". htmlspecialchars($ticket['timestamp'], ENT_QUOTES, 'UTF-8') . "</td></tr>");
                }
                ?>
                </table>
            </div>
            <div id="right">
            <?php 
            if ($selectedticket) {
                switch ($selectedticket['urgency']) {
                    case 1:
                        $urgency = "Emergency";
                        break;
                    case 2:
                        $urgency = "Immediate";
                        break;
                    case 3:
                        $urgency = "Urgent";
                        break;
                    case 4:
                        $urgency = "Non Urgent";
                        break;
                    case 5:
                        $urgency = "Not Major";
                        break;
                    case 6:
                        $urgency = "Other / Not sure";
                        break;
                    default:
                        $urgency = "Other / Not sure";
                        break;
                }
            ?>
                <h1>Ticket #<?= $selectedticket['id'] ?></h1>
                <p><special>Creator:</special> <?php echo htmlspecialchars($selectedticket['creator'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><special>Urgency:</special> <?php echo $urgency ?></p>
                <p><special>Type:</special> <?php echo htmlspecialchars($selectedticket['type'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><special>Summary:</special> <?php echo htmlspecialchars($selectedticket['summary'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><special>Details:</special> <?php echo htmlspecialchars($selectedticket['details'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><special>Custom Fields:</special></p>
                <?php
                $custom = json_decode($selectedticket['custom'], true);
                if ($custom) {
                    foreach ($custom as $key => $value) {
                        echo "<p><special> &emsp;" . htmlspecialchars($key) . ":</special> " . htmlspecialchars($value) . "</p>";
                    }
                }
                ?>
                <p><special>Timestamp:</special> <?php echo htmlspecialchars($selectedticket['timestamp'], ENT_QUOTES, 'UTF-8') ?></p>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo (int)$selectedticket['id'] ?>">
                    <input type="submit" value="Close ticket">
                </form>
            <?php 
            } else {
                echo "<p>Select a <special>ticket</special> to get started.</p>";
            }
            ?>
            </div>
        </div>
    </section>
    <br> 
</body>