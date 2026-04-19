<?php
/* Imported from Librebook */
function getmode($id, $pdo) {
    $stmt = $pdo->prepare('SELECT theme FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $preferredmode = $stmt->fetchColumn();
    return $preferredmode;
}

function echostyle($preferredmode) {
    $stylesheet = '';
    switch ($preferredmode) {
        case 'dark':
            $stylesheet = 'dark.css';
            break;
        default:
            $stylesheet = 'light.css';
    }
    echo '<link rel="stylesheet" href="' . $stylesheet . '">';
}

if (isset($_SESSION['user_id'])) {
    $id = $_SESSION['user_id'];
    $preferredmode = getmode($id, $pdo);
} else {
    $preferredmode = 'light';
}

echostyle($preferredmode);
?>