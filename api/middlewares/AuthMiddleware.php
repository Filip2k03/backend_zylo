<?php
require_once __DIR__ . '/../utils/JwtHandler.php';

class LoginController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login($data) {
        // Support login with either Username OR Email
        if(empty($data->login_id) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Username/Email and password are required."]);
            return;
        }

        try {
            $query = "SELECT id, username, password_hash, zodiac_sign, zodiac_element FROM users WHERE email = :id OR username = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $data->login_id);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($data->password, $user['password_hash'])) {
                $jwt = new JwtHandler();
                // Token expires in 30 days
                $token = $jwt->encode([
                    "user_id" => $user['id'], 
                    "username" => $user['username'], 
                    "exp" => time() + (86400 * 30) 
                ]);

                http_response_code(200);
                echo json_encode([
                    "status" => "success", 
                    "token" => $token, 
                    "user" => [
                        "id" => $user['id'],
                        "username" => $user['username'],
                        "zodiac_sign" => $user['zodiac_sign'],
                        "zodiac_element" => $user['zodiac_element']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["status" => "error", "message" => "Invalid credentials."]);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Database error."]);
        }
    }
}
?>