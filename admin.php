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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        if(!hash_equals($_SESSION['csrf'], $_POST['csrf'])) { 
            die ("<link rel='stylesheet' href='light.css'> <h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1><h1>Fatal request - CSRF Violation <br><special style='color:red;'> Error code: (#b1)</special></h1><a id='navb' style='max-width: 10%;' href='dash.php'>Return</a>");
        }
        $userid = $_POST["id"];
        $stmt6 = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt6->execute([$userid]);
        $searchrole = $stmt6->fetchColumn(); 
        if ($searchrole !== 'admin') {
            $stmt1 = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt1->execute([$userid]);
            header("Location: admin.php");
        } else {
            die ("<link rel='stylesheet' href='light.css'> <h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1><h1>Fatal request - Forbidden Action <br><special style='color:red;'> Error code: (#b2)</special></h1><a id='navb' style='max-width: 10%;' href='dash.php'>Return</a>");
        }
        exit;
    }
    if (isset($_POST['assign'])) {
        if(!hash_equals($_SESSION['csrf'], $_POST['csrf'])) { 
            die ("<link rel='stylesheet' href='light.css'> <h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1><h1>Fatal request - CSRF Violation <br><special style='color:red;'> Error code: (#b1)</special></h1><a id='navb' style='max-width: 10%;' href='dash.php'>Return</a>");
        }
        $newname = $_POST["users"];
        $ticketid = $_POST["id"];
        $stmt3 = $pdo->prepare('UPDATE tickets SET assignee = ? WHERE id = ?');
        $stmt3->execute([$newname, $ticketid]);
        header("Location: admin.php");
        exit;
    }
    if (isset($_POST['close'])) {
        if(!hash_equals($_SESSION['csrf'], $_POST['csrf'])) { 
            die ("<link rel='stylesheet' href='light.css'> <h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1><h1>Fatal request - CSRF Violation <br><special style='color:red;'> Error code: (#b1)</special></h1><a id='navb' style='max-width: 10%;' href='dash.php'>Return</a>");
        }
        $ticketid = $_POST["id"];
        $stmt = $pdo->prepare('INSERT INTO past_tickets SELECT * FROM tickets WHERE id = ?');
        $stmt->execute([$ticketid]);
        $stmt3 = $pdo->prepare('UPDATE users SET solved = solved + 1 WHERE username = ?');
        $stmt3->execute([$name]);
        $stmt2 = $pdo->prepare('DELETE FROM tickets WHERE id = ?');
        $stmt2->execute([$ticketid]);
        header("Location: admin.php");
        exit;
    }
    if (isset($_POST['reopen'])) {
        if(!hash_equals($_SESSION['csrf'], $_POST['csrf'])) { 
            die ("<link rel='stylesheet' href='light.css'> <h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1><h1>Fatal request - CSRF Violation <br><special style='color:red;'> Error code: (#b1)</special></h1><a id='navb' style='max-width: 10%;' href='dash.php'>Return</a>");
        }
        $ticketid = $_POST["id"];
        $stmt = $pdo->prepare('INSERT INTO tickets SELECT * FROM past_tickets WHERE id = ?');
        $stmt->execute([$ticketid]);
        $stmt3 = $pdo->prepare('UPDATE users SET solved = solved - 1 WHERE username = ?');
        $stmt3->execute([$name]);
        $stmt2 = $pdo->prepare('DELETE FROM tickets WHERE id = ?');
        $stmt2->execute([$ticketid]);
        header("Location: admin.php");
        exit;
    }
    if (isset($_POST['deletecfield'])) {
        $cfieldid = $_POST["fieldid"];
        $stmt = $pdo->prepare('DELETE FROM custom_fields WHERE id = ?');
        $stmt->execute([$cfieldid]);
        header("Location: admin.php");
        exit;
    }
    if (isset($_POST['createfield'])) {
        if(!hash_equals($_SESSION['csrf'], $_POST['csrf'])) { 
            die ("<link rel='stylesheet' href='light.css'> <h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1><h1>Fatal request - CSRF Violation <br><special style='color:red;'> Error code: (#b1)</special></h1><a id='navb' style='max-width: 10%;' href='dash.php'>Return</a>");
        }
        $readyoptions = [];
        if ($_POST['ftype'] === 'dropdown') {
            $name = trim($_POST['fname']);
            $type = trim($_POST['ftype']);
            foreach ($_POST['options'] as $option) {
                $option = trim($option);
                if ($option === '') {
                    continue;
                }
                $option = htmlspecialchars($option, ENT_QUOTES, 'UTF-8');
                $readyoptions[] = $option;
            }
            if (count($readyoptions) === 0) {
                die("Dropdown must have at least one option.");
            }
            $optionsjson = json_encode($readyoptions, JSON_UNESCAPED_UNICODE);
            $stmt = $pdo->prepare(
                'INSERT INTO custom_fields (name, type, options) VALUES (?, ?, ?)'
            );
            $stmt->execute([$name, $type, $optionsjson]);
            header("Location: admin.php");
            exit;
        } else {
            $name = trim($_POST['fname']);
            $type = trim($_POST['ftype']);
            $stmt = $pdo->prepare('INSERT INTO custom_fields (name, type) VALUES (?,?)');
            $stmt->execute([$name, $type]);
            header("Location: admin.php");
            exit;
        }
    }
}

if (isset($_GET['sticketid'])) {
    $sticket = (int)$_GET['sticketid'];
    if ($sticket < 1) {
        $params = $_GET;
        $params['sticketid'] = 1;
        header("Location: admin.php?" . http_build_query($params));
        exit;
    }
} else {
    $sticket = null;
}
if (isset($_GET['opage'])) {
    $opage = (int)$_GET['opage'];
    if ($opage < 1) {
        $params = $_GET;
        $params['opage'] = 1;
        header("Location: admin.php?" . http_build_query($params));
        exit;
    }
} else {
    $opage = 1;
}
if (isset($_GET['cpage'])) {
    $cpage = (int)$_GET['cpage'];
    if ($cpage < 1) {
        $params = $_GET;
        $params['cpage'] = 1;
        header("Location: admin.php?" . http_build_query($params));
        exit;
    }
} else {
    $cpage = 1;
}
if (isset($_GET['mpage'])) {
    $mpage = (int)$_GET['mpage'];
    if ($mpage < 1) {
        $params = $_GET;
        $params['mpage'] = 1;
        header("Location: admin.php?" . http_build_query($params));
        exit;
    }
} else {
    $mpage = 1;
}
function paging($key, $value, $anchor = null) {
    $params = $_GET;
    $params[$key] = $value;
    $url = 'admin.php?' . http_build_query($params);
    if ($anchor) {
        $url .= '#' . $anchor;
    }
    return $url;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueDesk Admin Panel</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta property="og:title" content="QueueDesk">
    <meta property="og:description" content="Admin Panel">
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
    <section class="messages">
        <h1>Queue<special>Desk Admin Panel</special></h1>
        <h1 id="head2"><?php echo 'Welcome <special>' . $role . "</special> " . $name;?></special></h1>
        <div id="navbar">
            <a id="navb" href="dash.php">Home</a>
            <?php echo '<a id="navb" href="visual.php?id=' . $id . '">Visual</a>'; ?>
            <a id="navb" href="assetregister.php">Asset Register</a>
            <a id="navb" href="logout.php">Logout</a>
        </div>
        <p>Summary</p>
        <div id="boxes">
            <div id="ibox">
                <h1 onclick="window.location='#ct';">Tickets closed:</h1>
                <?php 
                $countstmt = $pdo->prepare("SELECT COUNT(*) FROM past_tickets");
                $countstmt->execute();
                $count = $countstmt->fetchColumn();
                echo "<h1>" . $count . "</h1>";
                ?>
            </div>
            <div <?php $countstmt = $pdo->prepare("SELECT COUNT(*) FROM tickets"); $countstmt->execute(); $count = $countstmt->fetchColumn(); if ($count > 0) { echo 'id="rbox"'; } else { echo 'id="gbox"'; }?>  onclick="window.location='#ot';">
                <h1>Tickets Open:</h1>
                <?php 
                echo "<h1>" . $count . "</h1>";
                ?>
            </div>
            <div id="ibox">
                <h1>Most closed:</h1>
                <?php 
                $countstmt = $pdo->prepare("SELECT username, solved FROM users ORDER BY solved DESC LIMIT 1");
                $countstmt->execute();
                $row = $countstmt->fetch(PDO::FETCH_ASSOC);
                echo "<h1>" . $row['username'] . ": " . $row['solved'] . "</h1>";
                ?>
            </div>
        </div>
        <p>Admins:</p>
        <table>
        <tr>
            <th>ID</th>
            <th>NAME</th>
            <th>TIMESTAMP</th>
            <th>CURRENT TICKETS</th>
            <th>CLOSED TICKETS</th>
        </tr>
        <?php
        $stmt = $pdo->prepare('SELECT * FROM users WHERE role = "admin"');
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) {
            echo("<tr>" . "<td>". $user['id'] . "</td><td>". htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') . "</td><td>". $user['timestamp'] . "</td><td>". htmlspecialchars($user['tickets'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($user['solved'], ENT_QUOTES, 'UTF-8') . "</td></tr>");
        }
        ?>
        </table>
        <p>Technicians:</p>
        <table>
        <tr>
            <th>ID</th>
            <th>NAME</th>
            <th>TIMESTAMP</th>
            <th>CURRENT TICKETS</th>
            <th>CLOSED TICKETS</th>
            <th>MANAGE</th>
        </tr>
        <?php
        $stmt = $pdo->prepare('SELECT * FROM users WHERE role = "technician"');
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) {
            echo("<tr>" . "<td>". $user['id'] . "</td><td>". htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') . "</td><td>". $user['timestamp'] . "</td><td>". htmlspecialchars($user['tickets'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($user['solved'], ENT_QUOTES, 'UTF-8') . "</td><td>". "<form action='' method='POST'><input type='hidden' name='id' value='" . (int)$user['id'] .  "'/><input type='hidden' name='csrf' value=" . $_SESSION['csrf'] . "><input type='submit' name='delete' value='Delete user'></form></tr>");
        }
        ?>
        </table>
    </section>
    <br> 
    <section class="messages">
        <h1><special>Custom</special> Field Creation Form</h1>
        <form action="" method="post">
            <h1><special>Field Name</special></h1>
            <input type="text" name="fname" required>
            <h1><special>Type</special></h1>
            <label class="radio"><input type="radio" name="ftype" value="text" required>Text</label>
            <label class="radio"><input type="radio" name="ftype" value="textarea">Textarea</label>
            <label class="radio"><input type="radio" id="dropdown-radio" name="ftype" value="dropdown">Dropdown</label>
            <?php echo "<input type='hidden' name='csrf' value='" . $_SESSION['csrf'] . "'>";?>
            <div id="optionscontainer" style="display:none;">
                <div id="options-list">
                    <input type="text" name="options[]" placeholder="Option 1">
                </div>
                <button type="button" id="addoption">+ Add option</button>
            </div>
            <h1><special>Create</special></h1>
            <input type="submit" name="createfield" id="createfield" value="Create Question Field">
        </form>
        <script>
            const dropdownRadio = document.getElementById('dropdown-radio');
            const optionsBox = document.getElementById('optionscontainer');
            const addBtn = document.getElementById('addoption');
            const list = document.getElementById('options-list');
            dropdownRadio.addEventListener('change', () => {
                optionsBox.style.display = 'block';
            });
            document.querySelectorAll('input[name="ftype"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    if (!dropdownRadio.checked) {
                        optionsBox.style.display = 'none';
                    }
                });
            });
            addBtn.addEventListener('click', () => {
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'options[]';
                input.placeholder = `Option ${list.children.length + 1}`;
                list.appendChild(input);
                console.log(list)
            });
        </script>
        <br>
        <h1>Current Custom Fields</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>NAME</th>
                <th>TYPE</th>
                <th>OPTIONS</th>
                <th>DELETE</th>
            </tr>
        <?php
        $stmt7 = $pdo->prepare("SELECT * FROM custom_fields");
        $stmt7->execute();
        $cfields = $stmt7->fetchAll(PDO::FETCH_ASSOC);
        foreach($cfields as $cfield) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($cfield['id'], ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($cfield['name'], ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($cfield['type'], ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($cfield['options'], ENT_QUOTES, 'UTF-8') . "</td><td>". "<form action='' method='POST'><input type='hidden' name='csrf' value=" . $_SESSION['csrf'] . "><input type='hidden' name='fieldid' value='" . (int)$cfield['id'] .  "'/><input type='submit' name='deletecfield' value='Delete field'></form>";
            echo "</tr>";
        }
        ?>
        </table>
    </section>
    <br>
    <section class="messages" id='ot'>
        <h1><special>Open</special> Tickets</h1>
        <div id="navbar">
            <a id="navb" href="<?php echo paging('opage', 1, 'ot');?>">First page</a>
            <a id="navb" href="<?php echo paging('opage', ($opage - 1), 'ot');?>">Prior page</a>
            <a id="navb"href="<?php echo paging('opage', ($opage + 1), 'ot');?>">Next page</a>
        </div>
        <p><?php echo "Page: <special>" . $opage;?></special></p>
        <?php
        $stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
        $stmt->execute([$sticket]);
        $individual = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($individual) {
            $customdata = json_decode($individual['custom'], true);
            if ($customdata && is_array($customdata)) {
                echo "<h1><special>More</special> Details</h1>";
                echo "<p>ID: " . htmlspecialchars($individual['id'], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p>Creator: " . htmlspecialchars($individual['creator'], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p>Email: " . htmlspecialchars($individual['email'], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p>Summary: " . htmlspecialchars($individual['summary'], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p>Details: " . htmlspecialchars($individual['details'], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<table>";
                echo "<tr>";
                echo "<th>Header</th>";
                echo "<th>Data</th>";
                echo "</tr>";
                foreach ($customdata as $key => $value) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No custom fields for this ticket.</p>";
            }
        } else {
            echo "<p>Select a ticket to see more information.</p>";
        }
        ?>
    <br>
    <table>
            <tr>
                <th>ID</th>
                <th>TIMESTAMP</th>
                <th>CREATOR</th>
                <th>EMAIL</th>
                <th>SUMMARY</th>
                <th>CUSTOM FIELDS</th>
                <th>URGENCY</th>
                <th>MANAGE</th>
            </tr>
            <?php
            $ooffset = ($opage - 1) * OFFSET;
            $stmt3 = $pdo->prepare('SELECT * FROM tickets ORDER BY timestamp DESC limit :rlimit OFFSET :offset');
            $stmt3->bindValue(':rlimit', LIMIT, PDO::PARAM_INT);
            $stmt3->bindValue(':offset', $ooffset, PDO::PARAM_INT);
            $stmt3->execute();
            $tickets = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            foreach ($tickets as $ticket) {
                echo("<tr>" . "<td>". $ticket['id'] . "</td><td>". $ticket['timestamp'] . "</td><td>". htmlspecialchars($ticket['creator'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['email'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['summary'], ENT_QUOTES, 'UTF-8') . "</td><td> <a id='navb' href=" . paging('sticketid', $ticket['id'], 'ot') . ">See More</a> </td><td>". htmlspecialchars($ticket['urgency'], ENT_QUOTES, 'UTF-8') . "</td><td>". "<form action='' method='POST'><input type='hidden' name='csrf' value=" . $_SESSION['csrf'] . "><input type='hidden' name='id' value='" . (int)$ticket['id'] .  "'/><input type='submit' name='close' value='Close ticket'></form></td></tr>");
            }
            ?>
        </table>
    </section>
    <br> 
    <section class="messages" id="ct">
        <h1><special>Closed</special> Tickets</h1>
        <div id="navbar">            
            <a id="navb" href="<?php echo paging('cpage', 1, 'ct');?>">First page</a>
            <a id="navb" href="<?php echo paging('cpage', ($cpage - 1), 'ct');?>">Prior page</a>
            <a id="navb"href="<?php echo paging('cpage', ($cpage + 1), 'ct');?>">Next page</a> </div>
        <p><?php echo "Page: <special>" . $cpage;?></special></p>
        <table>
            <tr>
                <th>ID</th>
                <th>TIMESTAMP</th>
                <th>CREATOR</th>
                <th>EMAIL</th>
                <th>TYPE</th>
                <th>SUMMARY</th>
                <th>URGENCY</th>
                <th>MANAGE</th>
            </tr>
            <?php
            $coffset = ($cpage - 1) * OFFSET;
            $stmt3 = $pdo->prepare('SELECT * FROM past_tickets ORDER BY timestamp DESC limit :rlimit OFFSET :offset');
            $stmt3->bindValue(':rlimit', LIMIT, PDO::PARAM_INT);
            $stmt3->bindValue(':offset', $coffset, PDO::PARAM_INT);
            $stmt3->execute();
            $tickets = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            foreach ($tickets as $ticket) {
                echo("<tr>" . "<td>". $ticket['id'] . "</td><td>". $ticket['timestamp'] . "</td><td>". htmlspecialchars($ticket['creator'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['email'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['type'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['summary'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['urgency'], ENT_QUOTES, 'UTF-8') . "</td><td>". "<form action='' method='POST'><input type='hidden' name='csrf' value=" . $_SESSION['csrf'] . "><input type='hidden' name='id' value='" . (int)$ticket['id'] .  "'/><input type='submit' name='reopen' value='Reopen ticket'></form></tr>");
            }
            ?>
        </table>
    </section>
    <br>
    <section class="messages" id='mt'>
        <h1><special>Migrate</special> Tickets</h1>
        <div id="navbar">
            <a id="navb" href="<?php echo paging('mpage', 1, 'mt');?>">First page</a>
            <a id="navb" href="<?php echo paging('mpage', ($mpage - 1), 'mt');?>">Prior page</a>
            <a id="navb"href="<?php echo paging('mpage', ($mpage + 1), 'mt');?>">Next page</a>
        </div>
        <p><?php echo "Page: <special>" . $mpage;?></special></p>
        <table>
        <tr>
            <th>ID</th>
            <th>TIMESTAMP</th>
            <th>CREATOR</th>
            <th>ASSIGNEE</th>
            <th>TYPE</th>
            <th>Summary</th>
            <th>URGENCY</th>
            <th>REASSIGN</th>
        </tr>
        <?php
        $stmt2 = $pdo->prepare('SELECT * FROM users');
        $stmt2->execute();
        $allusers = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $selections = '';
        foreach ($allusers as $auser) { 
            $selections = $selections . '<option value=' . htmlspecialchars($auser['username'], ENT_QUOTES, 'UTF-8') . '>' . htmlspecialchars($auser['username'], ENT_QUOTES, 'UTF-8') . '</option>'; 
        }
        $ooffset = ($mpage - 1) * OFFSET;
        $stmt = $pdo->prepare('SELECT * FROM tickets ORDER BY timestamp DESC limit :rlimit OFFSET :offset');
        $stmt->bindValue(':rlimit', LIMIT, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $ooffset, PDO::PARAM_INT);
        $stmt->execute();
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tickets as $ticket) {
            echo("<tr>" . "<td>". $ticket['id'] . "</td><td>". $ticket['timestamp'] . "</td><td>". htmlspecialchars($ticket['creator'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['assignee'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['type'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['summary'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($ticket['urgency'], ENT_QUOTES, 'UTF-8') . "</td><td>". "<form action='' method='POST'><select name='users' id='users'>" . $selections . "</select><input type='hidden' name='csrf' value=" . $_SESSION['csrf'] . "><input type='hidden' name='id' value='" . (int)$ticket['id'] .  "'/><input type='submit' name='assign' value='Reassign'></form></tr>");
        }
        ?>
        </table>
    </section>
    <br>
    <section class="messages" id='et'>
        <h1><special>Export</special> Your Data As <special>.CSV</special></h1>
        <?php
        $stmt4 = $pdo->prepare('SELECT username FROM users');
        $stmt4->execute();
        $allusers = $stmt4->fetchAll(PDO::FETCH_ASSOC);
        $userselections = '';
        foreach ($allusers as $tuser) { 
            $userselections = $userselections . '<option value="' . htmlspecialchars($tuser['username'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($tuser['username'], ENT_QUOTES, 'UTF-8') . '</option>';
        }
        echo "<form action='export.php' method='POST'>";        
        echo "<select name='target' id='target'>" . $userselections . "</select>";
        $stmt5 = $pdo->prepare('SHOW TABLES');
        $stmt5->execute();
        $tables = $stmt5->fetchAll(PDO::FETCH_ASSOC);
        $tableselections = '';
        foreach ($tables as $table) { 
            $tablename = array_values($table)[0];
            $tableselections = $tableselections . '<option value="' . htmlspecialchars($tablename, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($tablename, ENT_QUOTES, 'UTF-8') . '</option>';
        }
        echo "<select name='data' id='data'>" . $tableselections . "</select>";
        echo "<input type='hidden' name='csrf' value=" . $_SESSION['csrf'] . ">";
        echo "<input type='submit' name='export' value='Export data'></input>";
        echo "</form>";
        ?>
    </section>
    <br> 
</body>
