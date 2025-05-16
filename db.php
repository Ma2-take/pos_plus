<?php
try {
    $pdo = new PDO("mysql:host=mysql;dbname=yse_register;port=3306;charset=utf8", 'docker', 'docker');
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("接続に失敗しました: " . $e->getMessage());
}
?>
