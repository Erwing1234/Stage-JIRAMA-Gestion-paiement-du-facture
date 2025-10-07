<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: *");

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=jirama;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && md5($password) === $user['motdepasse']) {
        echo json_encode([
            "success" => true,
            "user" => [
                "id" => $user['id'],
                "email" => $user['email']
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Email ou mot de passe incorrect."
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur serveur : " . $e->getMessage()
    ]);
}