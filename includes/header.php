<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>FTD Hallenbuch</title>
  <link rel="stylesheet" href="assets/style.css">
  <meta charset="utf-8">
  <title>FTD Hallenbuch</title>
  <style>
    .logout {
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 0.9em;
      text-align: right;
      z-index: 1000;
    }
  </style>
</head>
<body>
<?php
if (isset($_SESSION['user'])) {
    echo "<div style='text-align:right;'>👤 Angemeldet als <strong>" . htmlspecialchars($_SESSION['user']) . "</strong> ";
    echo "<a href='logout.php'>Logout</a><BR>";
    echo "<div style='text-align:right;'><a href='admin/admin_menu.php'>Admin-Menü</a></div><BR><BR>";
    if (isset($_SESSION['entry_count']) && $_SESSION['entry_count'] >= 3) {
        echo "<form method='post' style='display:inline;'>
                <button type='submit' name='reset_session' style='font-size:0.8em;'>🔄 Eintragszähler zurücksetzen</button>
              </form>";
    }
    echo "</div>";
} else {
    echo "<div style='text-align:right;'><a href='login.php'>🔐 Admin-Login</a></div>";
} 
if (isset($_POST['reset_session'])) {
    unset($_SESSION['entry_count']);
    unset($_SESSION['eintrags_reset']); // falls du eine Zeitsteuerung nutzt
    echo "<div style='text-align:right;'><p>✅ Eintragszähler wurde zurückgesetzt.</p></div>";
}
?>
<header>
    <h1>FTD Hallenbuch</h1>
</header>
<main>

