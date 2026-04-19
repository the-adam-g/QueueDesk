<?php
session_start();
include "config.php";
$uac = $_GET['uac'];
if (!$uac || !preg_match('/^[a-f0-9]{32}$/', $uac)) {
    header("Location: index.php");
    exit;
}
$myticket = "/myticket.php?uac=" . urlencode($uac);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueDesk dashboard</title>
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
    <style>
    img {
        margin-left: auto;
        margin-right: auto;
    }
    #underlineme {
        position: relative;
        display: inline-block;
    }
    #underlineme::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -6px;
        width: 100%;
        height: 2px;
        background-color: #4F46E5;
        transform: scaleX(0);
        transform-origin: left;
        animation: underline-in 0.6s ease forwards;
    }
    @keyframes underline-in {
        to {
            transform: scaleX(1);
        }
    }
    </style>
</head>
<body>
    <section id="head">
        <h1>Queue<special>Desk</special></h1>
    </section>
    <section id="messages">
        <h1>Your Queue<special>Desk</special> ticket has been successfully created!</h1>
        <p id="underlineme">Thank you for using Queue<special>Desk</special></p>
        <p>Queue<special>Desk</special> advises that you do not share your UAC and is not responsible for any repurcussions of a shared UAC.</p>
        <?php 
        $userurl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . $myticket;
        echo "<h1>Your Unique Access Code is: " . htmlspecialchars($uac, ENT_QUOTES, 'UTF-8');
        echo "<h1>You can view the progress of your ticket at the following URL: </h1>";
        echo "<h1><a href='". htmlspecialchars($userurl, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($userurl, ENT_QUOTES, 'UTF-8') . "</a></h1>";
        ?>
        <h1>QR code to your ticket details:</h1>
        <div id="qrcode"></div>
        <script src="qrcode.min.js"></script>
        <script>
            new QRCode(document.getElementById("qrcode"),  "<?php echo $userurl;?>");
        </script>
    </section>
    <br> 
</body>
