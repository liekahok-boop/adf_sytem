<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();

echo "<h2>Test Data Frontdesk</h2>";

// 1. Cek fd_layout_gedung
echo "<h3>1. Data Gedung (fd_layout_gedung):</h3>";
$gedung = $db->fetchAll("SELECT * FROM fd_layout_gedung ORDER BY block_index");
echo "<pre>" . print_r($gedung, true) . "</pre>";

// 2. Cek fd_master_kamar
echo "<h3>2. Data Master Kamar (fd_master_kamar):</h3>";
$kamar = $db->fetchAll("SELECT * FROM fd_master_kamar ORDER BY gedung, id_posisi");
echo "<pre>" . print_r($kamar, true) . "</pre>";

// 3. Cek fd_color_config
echo "<h3>3. Data Color Config (fd_color_config):</h3>";
$colors = $db->fetchAll("SELECT color_key, hex_value FROM fd_color_config");
echo "<pre>" . print_r($colors, true) . "</pre>";

// 4. Cek fd_hk_status
echo "<h3>4. Data HK Status (fd_hk_status):</h3>";
$hk = $db->fetchAll("SELECT * FROM fd_hk_status");
echo "<pre>" . print_r($hk, true) . "</pre>";

// 5. Cek fd_inhouse
echo "<h3>5. Data In-House (fd_inhouse):</h3>";
$inhouse = $db->fetchAll("SELECT * FROM fd_inhouse");
echo "<pre>" . print_r($inhouse, true) . "</pre>";
