<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['cname']);
    $email = trim($_POST['email']);
    $type = $_POST['type'];
    $urgency = $_POST['urgency'];
    $sum = trim($_POST['summary']);
    $ad = trim($_POST['details']);
    $stmt0 = $pdo->prepare('SELECT username, tickets from users ORDER BY tickets ASC LIMIT 1');
    $stmt0->execute();
    $ticketcounts = $stmt0->fetch(PDO::FETCH_ASSOC);
    $stmt4 = $pdo->prepare("SELECT * FROM custom_fields");
    $stmt4->execute();
    $fields = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    $cdata = [];
    $count = 0;
    foreach ($fields as $field) {
        $count = $count + 1;
        if (isset($_POST[$count])) {
            $cdata[$field['name']] = $_POST[$count];
        } elseif (isset($_POST[$field['name']])) { 
            $cdata[$field['name']] = $_POST[$field['name']];
        }
    }
    $cjson = json_encode($cdata, JSON_UNESCAPED_UNICODE);
    try {
        $check = true;
        do {
            $uac = bin2hex(random_bytes(16));
            $stmt3 = $pdo->prepare('SELECT COUNT(*) FROM tickets WHERE uac = ?');
            $stmt3->execute([$uac]);
            $count = (int)$stmt3->fetchColumn();
        } while ($count !== 0);
        $stmt = $pdo->prepare('INSERT INTO tickets (creator, assignee, type, details, email, urgency, uac, custom, summary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $ticketcounts['username'], $type, $ad, $email, $urgency, $uac, $cjson, $sum]);
        $stmt2 = $pdo->prepare('UPDATE users SET tickets = tickets + 1 WHERE username = ?');
        $stmt2->execute([$ticketcounts['username']]);
        header("Location: thankyou.php?uac=" . $uac);
    } catch (PDOException $e) {
        echo 'Ticket creation failed';
        echo "Database error: " . $e->getMessage();
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create a QueueDesk ticket</title>
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
        <h1><special>Create a QueueDesk ticket.</special></h1>
         <form action="" method="post">
            <label>Your name: </label><input type="text" name="cname" required>
            <label>Your email: </label><input type="email" name="email" required>
            <label>Type of issue: </label>
            <select id="type" name="type"> 
                <option value="Application / Software">Application / Software</option>
                <option value="Operating System">Operating System</option>
                <option value="User Permissions">User Permissions</option>
                <option value="Hardware">Hardware</option>
                <option value="Network / Internet">Network / Internet</option>
                <option value="Other / Not sure">Other / Not sure</option>
            </select>
            <label>Urgency: </label>
            <select id="urgency" name="urgency"> 
                <option value="1">Emergency</option>
                <option value="2">Immediate</option>
                <option value="3">Urgent</option>
                <option value="4">Non Urgent</option>
                <option value="5">Not Major</option>
                <option value="6">Other / Not sure</option>
            </select> 
            <?php
            $stmt4 = $pdo->prepare("SELECT * FROM custom_fields");
            $stmt4->execute();
            $fields = $stmt4->fetchAll(PDO::FETCH_ASSOC);
            $count = 0;
            foreach ($fields as $field) {
                $count = $count + 1;
                if ($field['type'] === 'text') {
                    echo "<label>" . $field['name'] . "</label><input type='text' name=" . $count . " required>";
                } elseif ($field['type'] === 'dropdown') {
                    echo "<label>" . $field['name'] . "</label><select name=" . $count . " required>";
                    $options = json_decode($field['options'], true);
                    if (is_array($options)) {
                        foreach ($options as $option) {
                            echo '<option value="' . htmlspecialchars($option, ENT_QUOTES, 'UTF-8') . '">"'. htmlspecialchars($option, ENT_QUOTES, 'UTF-8') . '"</option>"';
                        }
                    }
                    echo "</select>";
                } elseif ($field['type'] === 'textarea') {
                    echo "<label>" . $field['name'] . ": </label><textarea name='" . $count . "' rows='5' required></textarea>";
                } elseif ($field['type'] === 'password') {
                    echo "<label>" . $field['name'] . ": </label><input type='password' name=" . $count . " required>";
                }
            }
            ?>
            <label>Short summary of issue: </label><textarea name="summary" rows="1" required></textarea>
            <label>Additional details: </label><textarea name="details" rows="5"></textarea>
            <input type="submit" id="createticket" value="Create Ticket">
        </form>
    </section>
    <br> 
</body>
