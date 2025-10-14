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
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}
?>
</body>
</html>

