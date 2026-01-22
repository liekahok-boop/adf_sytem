<?php
// Test logo detection
echo "<h2>Logo Path Test</h2>";

$uploadsDir = dirname(__DIR__) . '/narayana/uploads/logos/';
echo "<p>Uploads Dir: " . $uploadsDir . "</p>";
echo "<p>Dir Exists: " . (is_dir($uploadsDir) ? 'Yes' : 'No') . "</p>";

$logos = glob($uploadsDir . 'hotel_logo_*.png');
echo "<p>Found " . count($logos) . " logo files:</p>";
echo "<ul>";
foreach ($logos as $logo) {
    echo "<li>" . basename($logo) . "</li>";
}
echo "</ul>";

if (!empty($logos)) {
    $logoFile = basename(end($logos));
    echo "<h3>Selected Logo: $logoFile</h3>";
    echo '<img src="uploads/logos/' . $logoFile . '" style="max-width: 200px; border: 2px solid #ccc;">';
}
?>
