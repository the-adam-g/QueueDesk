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
    if (isset($_POST['delete'])) {
        if(!hash_equals($_SESSION['csrf'], $_POST['csrf'])) { 
            die ("<link rel='stylesheet' href='light.css'> <h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1><h1>Fatal request - CSRF Violation <br><special style='color:red;'> Error code: (#b1)</special></h1><a id='navb' style='max-width: 10%;' href='dash.php'>Return</a>");
        }
        try {
            $stmt = $pdo->prepare("SELECT id FROM tickets WHERE assignee = ?");
            $stmt->execute([$name]);
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($tickets as $ticket) {
               $stmtlookup = $pdo->prepare("SELECT username FROM users WHERE username != ? ORDER BY tickets ASC LIMIT 1");
                $stmtlookup->execute([$name]);
                $tech = $stmtlookup->fetch(PDO::FETCH_ASSOC);
                if ($tech) {
                    $reassignee = $tech['username'];
                    $stmt1 = $pdo->prepare("UPDATE tickets SET assignee = ? WHERE id = ?");
                    $stmt1->execute([$reassignee, $ticket['id']]);
                    $stmt2 = $pdo->prepare("UPDATE users SET tickets = tickets + 1 WHERE username = ?");
                    $stmt2->execute([$reassignee]);
                }
            }
            $stmt2 = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt2->execute([$id]);
            session_destroy();
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            echo "Error deleting account: " . $e->getMessage();
        }
    }
    if (isset($_POST['theme'])) {
        $theme = $_POST['themechoice'];
        $themes = ["light", "dark"];
        if (in_array($theme, $themes)) {
            $stmt = $pdo->prepare("UPDATE users SET theme = ? WHERE id = ?");
            $stmt->execute([$theme, $id]);
        }
        header("Location: settings.php");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueDesk past tickets</title>
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
        <h1>Queue<special>Desk Settings</special></h1>
        <h1 id="head2"><?php echo 'Hello <special>' . $role . "</special> " . $name;?></special></h1>
        <div id="navbar">
            <a id="navb" href="dash.php">Home</a>
            <a id="navb" href="assetregister.php">Asset Register</a>
            <a <?php if ($role !== "admin") { echo 'style="visibility: hidden;"'; } ?> id="navb" href="admin.php">Admin Panel</a>
            <a id="navb" href="logout.php">Logout</a>
         </div>
    </section>
    <br> 
    <section class="messages">
        <h1>Delete account</h1>
            <p>Warning! This action is irreversible.</p>
            <p>Your ticket history is retained unless deleted by your database administrator.</p>
            <p>Your tickets will be automatically reassigned</p>
            <form action="" method="POST">
                <?php echo "<input type='hidden' name='csrf' value='" . $_SESSION['csrf'] . "'>";?>
                <input type="submit" name="delete" value="Delete my account">
            </form>
        <h1>Theme</h1>
        <label>Select your theme: </label>
            <form action="" method="POST">
                <select id="type" name="themechoice"> 
                    <option value="light">Light mode</option>
                    <option value="dark">Dark mode</option>
                </select>
                <input type="submit" name="theme" value="Change theme">
            </form>
    </section> 
</body>