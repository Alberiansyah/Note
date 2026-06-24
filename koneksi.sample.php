<?php
// =============================================
// Copy this file to koneksi/koneksi.php
// and adjust your database credentials
// =============================================

$host = "localhost";
$dbname = "redNotes";
$user   = "root";
$pass   = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Database connection failed.");
}
