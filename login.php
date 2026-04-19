<?php
include 'config.php';
session_start();

if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
    $_SESSION['lasttry'] = time();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['attempts'] >= MAX_ATTEMPTS && (time() - $_SESSION['lasttry'] < LOCK_TIME)) {
        die ("<link rel='stylesheet' href='light.css'> <h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1><h1>Fatal request - Exceeded Max Login Attempts or Took Too Long To Login<br><special style='color:red;'> Error code: (#c1)</special></h1><a id='navb' style='max-width: 10%;' href='index.php'>Return</a>");
    }
    if (isset($_POST['register'])) { 
        $name = trim($_POST['name']);
        $pass = trim($_POST['pass']);
        $auth = trim($_POST['auth']);
        $admin = trim($_POST['admin']);
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $pass = htmlspecialchars($pass, ENT_QUOTES, 'UTF-8');
        $auth = htmlspecialchars($auth, ENT_QUOTES, 'UTF-8');
        $admin = htmlspecialchars($admin, ENT_QUOTES, 'UTF-8');
        if (ALLOW_REGISTRATION !== true) {
            echo "Admin registration is disabled.";
            exit();
        } else {
            if (password_verify($auth, ADMIN_UNIVERSAL)) {
                if (password_verify($admin, ADMIN_PASS)) {
                    $role = 'admin';
                    $hashed = password_hash($pass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
                    $stmt->execute([$name, $hashed, $role]);
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
                    $stmt->execute([$name]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['login'] = true;
                    $_SESSION['name'] = $name;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $role;
                    $_SESSION['csrf'] = bin2hex(random_bytes(32));
                    header('Location: dash.php');
                    exit();
                } else {
                    $role = 'technician';
                    $hashed = password_hash($pass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
                    $stmt->execute([$name, $hashed, $role]);
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
                    $stmt->execute([$name]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['login'] = true;
                    $_SESSION['name'] = $name;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $role;
                    $_SESSION['csrf'] = bin2hex(random_bytes(32));
                    header('Location: dash.php');
                    exit();
                }
            } else {
                echo "Admin authorisation password was wrong.";
                exit(); 
            }
        }
    }
    if (isset($_POST['login'])) {
        $name = trim($_POST['name']);
        $pass = trim($_POST['pass']);
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$name]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['name'] = $name;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: dash.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to QueueDesk as an Staff</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="light.css">
    <meta property="og:title" content="QueueDesk">
    <meta property="og:description" content="Login to QueueDesk as Staff">
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
        <h1><special>Login to QueueDesk as Staff.</special></h1>
         <form action="" method="post">
            <label>Your name: </label><input type="text" name="name" required>
            <label>Your password: </label><input type="password" name="pass" required>
            <input type="submit" id="login" value="login" name="login">
        </form>
    </section>
    <br>
    <section id="messages">
        <h1><special>Register to QueueDesk.</special></h1>
         <form action="" method="post">
            <label>Your name: </label><input type="text" name="name" required>
            <label>Your password: </label><input type="password" name="pass" required>
            <label>Authorisation password: </label><input type="password" name="auth" required>
            <label>Admin registration password: </label><input type="password" name="admin">
            <input type="submit" id="register" value="Register" name="register">
        </form>
    </section>
</body>
