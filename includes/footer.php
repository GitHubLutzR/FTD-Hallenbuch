</main>
<footer>
    <p>&copy; <?= date('Y') ?> FTD Dörnigheim 06 e.V.</p>
</footer>
<?php
if ($hesk_settings['debug']) {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}
?>
</body>
</html>

