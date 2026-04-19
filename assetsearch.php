<?php
session_start();
include 'config.php';
include 'acmode.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (isset($_SESSION['login'])) {
    $id = $_SESSION['user_id'];
    $name = $_SESSION['name'];
    $role = $_SESSION['role'];
} else {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit'])) {
        if(!hash_equals($_SESSION['csrf'], $_POST['csrf'])) { 
            die ("<link rel='stylesheet' href='light.css'> <h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1><h1>Fatal request - CSRF Violation <br><special style='color:red;'> Error code: (#b1)</special></h1><a id='navb' style='max-width: 10%;' href='dash.php'>Return</a>");
        }
        $id = (int)$_POST['id'];
        $itemname = trim($_POST['iname']);
        $cname = trim($_POST['cname']);
        $serial = trim($_POST['snum']);
        $owner = trim($_POST['own']);
        $status = $_POST['status'];
        $notes = trim($_POST['notes']);
        $stmt = $pdo->prepare('INSERT INTO assetregister (name, serial_num, owner, notes, creator, status) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$itemname, $serial, $owner, $notes, $cname, $status]);
        header("Location: assetregister.php");
        exit;
    }
    if (isset($_POST['search'])) {
        if(!hash_equals($_SESSION['csrf'], $_POST['csrf'])) { 
            die ("<link rel='stylesheet' href='light.css'> <h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1><h1>Fatal request - CSRF Violation <br><special style='color:red;'> Error code: (#b1)</special></h1><a id='navb' style='max-width: 10%;' href='dash.php'>Return</a>");
        }
        $typesearch = $_POST['typesearch'];
        $searchasset = [];
        switch ($typesearch){
            case 'id':
                $stmt = $pdo->prepare('SELECT * FROM assetregister WHERE id = ?');
                $stmt->execute([$_POST['userinput']]);
                $searchasset = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'name':
                $stmt = $pdo->prepare('SELECT * FROM assetregister WHERE name LIKE ?');
                $stmt->execute(["%" . $_POST['userinput'] . "%"]);
                $searchasset = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'serial_num':
                $stmt = $pdo->prepare('SELECT * FROM assetregister WHERE serial_num LIKE ?');
                $stmt->execute(["%" . $_POST['userinput'] . "%"]);
                $searchasset = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'owner':
                $stmt = $pdo->prepare('SELECT * FROM assetregister WHERE owner LIKE ?');
                $stmt->execute(["%" . $_POST['userinput'] . "%"]);
                $searchasset = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'creator':
                $stmt = $pdo->prepare('SELECT * FROM assetregister WHERE creator LIKE ?');
                $stmt->execute(["%" . $_POST['userinput'] . "%"]);
                $searchasset = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'status':
                $stmt = $pdo->prepare('SELECT * FROM assetregister WHERE status LIKE ?');
                $stmt->execute(["%" . $_POST['userinput'] . "%"]);
                $searchasset = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'notes':
                $stmt = $pdo->prepare('SELECT * FROM assetregister WHERE notes LIKE ?');
                $stmt->execute(["%" . $_POST['userinput'] . "%"]);
                $searchasset = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'timestamp':
                $stmt = $pdo->prepare('SELECT * FROM assetregister WHERE `timestamp` BETWEEN ? AND ?');
                $start = $_POST['userinput'] . " 00:00:00";
                $end = $_POST['userinput'] . " 23:59:59";
                $stmt->execute([$start, $end]);
                $searchasset = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            default:
                $searchasset = ['NO RESULTS FOUND FOR'. htmlspecialchars($_POST['userinput'], ENT_QUOTES, 'UTF-8') .''];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueDesk Asset Register</title>
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
        <h1>Q<special>D</special> <ar>Asset Register</ar></h1>
    </section>
    <section id="messages">
        <h1>Queue<special>Desk</special> <ar>Asset Register</ar></h1>
        <h1 id="head2"><?php echo 'Welcome <special>' . $role . "</special> " . $name;?></special></h1>
        <div id="navbar">
            <a id="navb" href="assetregister.php">Return to QueueDesk Asset Register</a>
        </div>
        <br>
    </section>
    <br>
    <section id="messages">
        <h1>Your Asset <ar>Search Results</ar></h1>
        <table>
        <tr>
            <th>ID</th>
            <th>NAME</th>
            <th>SERIAL</th>
            <th>CREATOR</th>
            <th>OWNER</th>
            <th>TIMESTAMP</th>
            <th>MANAGE</th>
        </tr>
        <?php
        foreach ($searchasset as $asset) {
            echo("<tr>" . "<td>". htmlspecialchars($asset['id'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($asset['name'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($asset['serial_num'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($asset['creator'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($asset['owner'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($asset['timestamp'], ENT_QUOTES, 'UTF-8') . "</td><td>". "<form action='assetregister.php' method='GET'><input type='hidden' name='id' value='" . htmlspecialchars($asset['id'], ENT_QUOTES, 'UTF-8') .  "'/><input type='submit' value='See more'></form></tr>");
        }
        ?>
    </section>
    <br> 
</body>
