<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=jirama;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo json_encode(["success" => true, "message" => "Connexion réussie"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}