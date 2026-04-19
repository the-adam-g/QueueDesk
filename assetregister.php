<?php
session_start();
include 'config.php';
include 'acmode.php';

if (isset($_SESSION['login'])) {
    $id = $_SESSION['user_id'];
    $name = $_SESSION['name'];
    $role = $_SESSION['role'];
} else {
    header('Location: index.php');
    exit();
}

function paging($key, $value) {
    $params = $_GET;
    $params[$key] = $value;
    return 'assetregister.php?' . http_build_query($params);
}
function destroypage($page) {
    $params = $_GET;
    unset($params[$page]);
    $url =  'assetregister.php?' . http_build_query($params);
    header("Location: $url");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        if(!hash_equals($_SESSION['csrf'], $_POST['csrf'])) { 
            die ("<link rel='stylesheet' href='light.css'> <h1>Queue<special>Desk</special> <special style='color:red;'>Error Handler</special></h1><h1>Fatal request - CSRF Violation <br><special style='color:red;'> Error code: (#b1)</special></h1><a id='navb' style='max-width: 10%;' href='dash.php'>Return</a>");
        }
        $itemname = trim($_POST['iname']);
        $cname = trim($name);
        $serial = trim($_POST['snum']);
        $owner = trim($_POST['own']);
        $status = $_POST['status'];
        $notes = trim($_POST['notes']);
        $stmt = $pdo->prepare('INSERT INTO assetregister (name, serial_num, owner, notes, creator, status) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$itemname, $serial, $owner, $notes, $cname, $status]);
        header("Location: assetregister.php");
        exit;
    }
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
        $stmt = $pdo->prepare('UPDATE assetregister SET name=?, serial_num=?, owner=?, notes=?, creator=?, status=? WHERE id = ?');
        $stmt->execute([$itemname, $serial, $owner, $notes, $cname, $status, $id]);
        header("Location: assetregister.php");
        exit;
    }
    if (isset($_POST['limit'])) {
        if ($_POST['limit'] === 'none') {
            $params = $_GET;
            unset($params['limit']);
            unset($limit);
            $url =  'assetregister.php?' . http_build_query($params);
            header("Location: $url");
            exit();
        } else{
            $url = paging('limit', (int)$_POST['limit']);
            header("Location: $url");
            exit();
        }

    }
    if (isset($_POST['id'])) {
        $url = paging('id', (int)$_POST['id']);
        header("Location: $url");
        exit();

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
            <a id="navb" href="dash.php">Return to QueueDesk Core</a>
        </div>
        <br>
        <script src="JsBarcode.all.min.js"></script>
        <script src="html2pdf.js"></script>
        <div id="content">
        <?php
        if (isset($_GET['id'])) {
            $userurl = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            echo '<h1>Asset <ar>#'. htmlspecialchars($_GET['id']) .'</ar></h1>';
            $stmt = $pdo->prepare('SELECT * FROM assetregister WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            $asset = $stmt->fetch(PDO::FETCH_ASSOC);
            echo '
            <div id="container">
            <div id="left">
            <p>Current Owner: '. htmlspecialchars($asset['owner']) .'</p>
            <p>Item Name: '. htmlspecialchars($asset['name']) .'</p>
            <p>Asset Registrant: '. htmlspecialchars($asset['creator']) .'</p>
            <p>Serial Number: '. htmlspecialchars($asset['serial_num']) .'</p>
            <p>Notes: '. htmlspecialchars($asset['notes']) .'</p>
            <p>Date Created: '. htmlspecialchars($asset['timestamp']) .'</p>
            ';
            if ($asset['status'] === 'Available') {
                echo '<p style="background-color: #2fc240;" id="pbox">Status: '. htmlspecialchars($asset['status']) .'</p>';
            } elseif ($asset['status'] === 'In-Use'){
                echo '<p style="background-color: #f43c3c;" id="pbox">Status: '. htmlspecialchars($asset['status']) .'</p>';
            } else {
                echo '<p style="background-color: #fd9827;"id="pbox">Status: Unknown - "' . htmlspecialchars($asset['status']) .'"</p>';
            }
            echo '
            <svg id="barcode"></svg>
            ';
            ?>
            <script>JsBarcode("#barcode", "<?php echo htmlspecialchars($asset['serial_num']); ?>");</script> 
            </div> 
            <div id="right"> 
                <div id="container">
                    <div id="left" style="border: none !important;">
                        <h1>QR code to your asset:</h1> 
                        <div id="qrcode"></div> 
                    </div>
                    <div id="right">
                        <h1>Data matrix for your asset:</h1> 
                        <div id="datamatrix"></div> 
                    </div>
                </div>
                <script src="datamatrix.js"></script> 
                <script>
                    const assetData = <?php echo json_encode($asset); ?>;
                    dataMatrix(JSON.stringify(assetData), {
                        width: 32,
                        height: 32,
                    })
                    .then(canvas => {
                        document.getElementById("datamatrix").appendChild(canvas);
                    });             
                </script> 
                <script src="qrcode.min.js"></script> 
                <script>new QRCode(document.getElementById("qrcode"), "<?php echo htmlspecialchars($userurl);?>");</script> 
            </div> 
            </div>
            </div>
            <div id="navbar">
                <a id="navb" onclick="createPDF()">Download as PDF</a>
                <a id="navb" onclick="assetregister.php">Close Asset Details</a> 
            </div>
            <br>
            <script>
                function createPDF() {
                    const element = document.getElementById('content');
                    element.style.marginTop = "0";
                    element.style.paddingTop = "0";
                    const opt = {
                        margin: 5,
                        filename: 'asset-<?php echo $asset['id'] ?? "export"; ?>.pdf',
                        image: { type: 'png', quality: 1 },
                        html2canvas: { 
                            scale: 2,
                            scrollY: 0 
                        },
                        jsPDF: { 
                            unit: 'mm', 
                            format: 'a4', 
                            orientation: 'landscape'
                        }
                    };
                    html2pdf().set(opt).from(element).save();
                }
                </script>
            <br>
            <hr>
            <?php
            echo '
            <h1>Edit your <ar>Asset</h1>
            <form action="" method="post">
            <div id="navbar" style="margin: auto;">
                <p style="margin-left: 15px; margin-right: 15px;">Item name: </p><input type="text" name="iname" value="' . htmlspecialchars($asset['name'])  . '" required><p style="margin-left: 15px; margin-right: 15px;">Registrant: </p><input type="text" name="cname" value="' . htmlspecialchars($asset['creator'])  . '" required><p style="margin-left: 15px; margin-right: 15px;">Serial number: </p><input type="text" name="snum" value="' . htmlspecialchars($asset['serial_num'])  . '" required><p style="margin-left: 15px; margin-right: 15px;">Owner: </p><input type="text" name="own" value="' . htmlspecialchars($asset['owner'])  . '" required>
            </div>
            <div id="navbar" style="margin: auto;">
                <p style="margin-right: 15px;">Status: </p><select name="status" id="status"><option value="Available" ' . ($asset['status'] === 'Available' ? 'selected' : '') . '>Available</option><option value="In-Use" ' . ($asset['status'] === 'In-Use' ? 'selected' : '') . '>In-Use</option></select><p style="margin-left: 15px; margin-right: 15px;">Additional notes: </p><textarea style="resize: none;" name="notes" rows="1" cols="75">' . htmlspecialchars($asset['notes'])  . '</textarea>
            </div>
            <input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">
            <input type="hidden" name="id" value="' . (int)$asset['id'] .  '"/>
            <input type="submit" id="edit" name="edit" value="Edit Asset">
            </form>
            ';
        }
        ?>
    </section>
    <br>
    <section id="messages">
        <h1>Search for an <ar>Asset</ar></h1>
        <form action="assetsearch.php" method="post">
            <p>Search by...</p>
            <select name="typesearch" id="typesearch" required>
                <option value="id">ID</option>
                <option value="name">Item name</option>
                <option value="owner">Owner name</option>
                <option value="creator">Asset creator</option>
                <option value="serial_num">Serial number</option>
                <option value="status">Status</option>
                <option value="notes">Notes</option>
                <option value="timestamp">Timestamp (YYYY-MM-DDDD)</option>
            </select>
            <p style="margin-right: 15px;">Value to search with: </p><input type="text" name="userinput">
            <input type="submit" id="search" name="search" value="Search For Asset">
            <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">
        </form>
        <br>
        <hr>
        <h1>Register a new <ar>Asset</ar></h1>
        <br>
        <form action="" method="post">
            <div id="navbar" style="margin: auto;">
                <p style="margin-left: 15px; margin-right: 15px;">Item name: </p><input type="text" name="iname" required><p style="margin-left: 15px; margin-right: 15px;">Serial number: </p><input type="text" name="snum" required><p style="margin-left: 15px; margin-right: 15px;">Owner: </p><input type="text" name="own" required>
            </div>
            <div id="navbar" style="margin: auto;">
                <p style="margin-right: 15px;">Status: </p><select name="status" id="status"><option value="Available">Available</option><option value="In-Use">In-Use</option></select><p style="margin-left: 15px; margin-right: 15px;">Additional notes: </p><textarea style="resize: none;" name="notes" rows="1" cols="75"></textarea>
            </div>
            <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">
            <input type="submit" id="create" name="create" value="Create Asset">
        </form>
    </section>
    <br>
    <section id="messages">
        <h1>Your <ar>Assets</ar>:</h1>
        <p>Loading limit:</p>
        <form action="" method="post">
            <select name='limit' id='limit' onchange='this.form.submit()'>
                <?php
                if (isset($_GET['limit'])) {
                    $limit = (int)$_GET['limit'];
                    echo "<option value='" . $limit . "' hidden selected disabled>" . $limit . "</option>";
                    echo "<option value='none' >Clear limit</option>";
                }
                ?>
                <option value='0' >0</option>
                <option value='10' >10</option>
                <option value='25' >25</option>
                <option value='50' >50</option>
                <option value='100' >100</option>
                <option value='200' >200</option>
            </select>
        </form>
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
        if (isset($_GET['limit'])) {
            $limits = [0, 10, 25, 50, 100, 200];
            $limit = (int)$_GET['limit'];
            if (in_array($limit, $limits)) {
                if ($limit === 0) {
                    $stmt = $pdo->prepare('SELECT * FROM assetregister ORDER BY `timestamp` DESC');
                } else{
                    $stmt = $pdo->prepare('SELECT * FROM assetregister ORDER BY `timestamp` DESC LIMIT ' . $limit);
                }
                } else {
                $stmt = $pdo->prepare('SELECT * FROM assetregister ORDER BY `timestamp` DESC LIMIT 10');
            }
        } else {
            $stmt = $pdo->prepare('SELECT * FROM assetregister ORDER BY `timestamp` DESC LIMIT 10');
        }
        $stmt->execute();
        $assets = $stmt->fetchAll();
        foreach ($assets as $asset) {
            echo("<tr>" . "<td>". htmlspecialchars($asset['id'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($asset['name'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($asset['serial_num'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($asset['creator'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($asset['owner'], ENT_QUOTES, 'UTF-8') . "</td><td>". htmlspecialchars($asset['timestamp'], ENT_QUOTES, 'UTF-8') . "</td><td>". "<form action='' method='post'><input type='hidden' name='id' value='" . htmlspecialchars($asset['id'], ENT_QUOTES, 'UTF-8') .  "'/><input type='submit' value='See more'></form></tr>");
        }
        ?>
    </section>
    <br> 
</body>
