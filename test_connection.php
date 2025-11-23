<?php
$host = 'srv1277.hstgr.io';  // or try 'localhost'
$db   = 'u778631151_oznew';
$user = 'u778631151_liveoz';
$pass = 'Liveoz58liveoz@3';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
  echo "✅ Connected successfully to $db on $host";
} catch (PDOException $e) {
  echo "❌ " . $e->getMessage();
}
?>
