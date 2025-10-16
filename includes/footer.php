</main>
<footer>
    <p>&copy; <?= date('Y') ?> FTD Dörnigheim 06 e.V.</p>
</footer>
<?php
if ($hesk_settings['debug']) {
    if (isset($_GET['debug'])) {
        echo "<pre>Debug (base64):\n";
        $decoded = base64_decode($_GET['debug']);
        print_r(json_decode($decoded, true));
        echo "</pre>";
    }
    echo "<pre>\$_SESSION:\n";
    print_r($_SESSION);
    echo "</pre>";
    echo "<pre>Übermittelte Formulardaten:\n";
    print_r($_POST);
    echo "</pre>";
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    echo "Aufruf von $referer\n";
    echo '<p>Pfad: ' . htmlspecialchars(__DIR__, ENT_QUOTES, 'UTF-8') . '</p>';
}
?>
</body>
</html>

