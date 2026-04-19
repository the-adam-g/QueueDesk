
<?php
session_start();
include "config.php";
include 'cmode.php';
if (isset($_GET['id'])) {
  $searchid = $_GET['id'];
  if (isset($_SESSION['login'])) {
      $id = $_SESSION['user_id'];
      $name = $_SESSION['name'];
      $role = $_SESSION['role'];
      if ($role !== "admin"){
        if ((int)$id !== (int)$_GET['id']) {
          echo $role . '<br>';
          echo $id . '<br>';
          echo $_GET['id'] . '<br>';
          echo $name . '<br>';
          echo $role . '<br>';
          header('Location: visual.php?id=' . $id);
          exit();
        }
      }
  } else {
      header('Location: index.php');
      exit();
  }
} else {
  header('Location: index.php');
  exit();
}
$stmt1 = $pdo->prepare("SELECT username from users WHERE id = ?");
$stmt1->execute([$searchid]);
$searchname = $stmt1->fetchColumn();
$stmt = $pdo->prepare(" SELECT YEARWEEK(timestamp, 1) AS week, COUNT(*) AS total FROM past_tickets WHERE assignee = ? GROUP BY YEARWEEK(timestamp, 1) ORDER BY week ASC");
$stmt->execute([$searchname]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($role === 'admin') {
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      if (isset($_POST['users'])) {
        $searchname = $_POST['users'];
        $stmt = $pdo->prepare(" SELECT YEARWEEK(timestamp, 1) AS week, COUNT(*) AS total FROM past_tickets WHERE assignee = ? GROUP BY YEARWEEK(timestamp, 1) ORDER BY week ASC");
        $stmt->execute([$searchname]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }
  }
}
$labels = [];
$data = [];
foreach ($results as $row) {
    $labels[] = $row['week'];
    $data[] = $row['total'];
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
    <section id="messages">
        <h1><special>QueueDesk admin panel.</special></h1>
        <h1 id="head2"><?php echo 'Welcome <special>' . $role . "</special> " . $name;?></special></h1>
        <div id="navbar">
            <a id="navb" href="dash.php">Home</a>
            <a id="navb" href="assetregister.php">Asset Register</a>
            <a <?php if ($role !== "admin") { echo 'style="visibility: hidden;"'; } ?> id="navb" href="admin.php">Admin Panel</a>
            <a id="navb" href="logout.php">Logout</a>
         </div>
        <?php
        if ($role === "admin") {
          $stmt2 = $pdo->prepare('SELECT * FROM users');
          $stmt2->execute();
          $allusers = $stmt2->fetchAll(PDO::FETCH_ASSOC);
          $selections = '';
          foreach ($allusers as $auser) { 
              $selections = $selections . '<option value=' . $auser['username'] . '>' . $auser['username'] . '</option>'; 
          }
          echo "<form action='' method='POST'><select name='users' id='users' onchange='this.form.submit()'><option value='' disabled selected>Select a user</option>" . $selections . "</select></form>";
        }
        ?>
        <h1>Tickets Solved Per Week (<?php echo htmlspecialchars($searchname, ENT_QUOTES, 'UTF-8'); ?>)</h1>
        <canvas id="myChart" width="200" height="100"></canvas>
        <script src="chart.umd.min.js"></script>
        <script>
          if (Chart && Array.isArray(Chart.registerables)) {
            Chart.register(...Chart.registerables);
          }

          const rawLabels = <?php echo json_encode($labels); ?>;
          const labels = rawLabels.map(week => {
              week = week.toString();
              const year = week.substring(0, 4);
              const weekNum = week.substring(4);
              return weekNum.padStart(2, '0') + "/" + year;
          });
          const data = <?php echo json_encode($data); ?>;

          const ctx = document.getElementById('myChart').getContext('2d');
          new Chart(ctx, {
            type: 'line',
            data: {
              labels: labels,
              datasets: [{
                label: 'Tickets Solved',
                data: data,
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.3,
                pointBackgroundColor: 'rgb(75, 192, 192)',
                pointRadius: 5
              }]
            },
            options: {
              responsive: true,
              plugins: {
                title: {
                  display: true,
                  text: 'Tickets Solved Per Week'
                }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  title: { display: true, text: 'Tickets' }
                },
                x: {
                  title: { display: true, text: 'Week (YYYYWW)' }
                }
              }
            }
          });
        </script>
  </section>
</body>
</html>
