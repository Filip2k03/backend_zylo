<?php
require_once __DIR__ . '/../utils/ZodiacHelper.php';

class AuthController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($data) {
        // 1. Basic validation
        if(empty($data->first_name) || empty($data->username) || empty($data->email) || empty($data->password) || empty($data->dob)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "All fields are required."]);
            return;
        }

        // 2. Validate Age (Must be 16+)
        $dobDate = new DateTime($data->dob);
        $today = new DateTime('today');
        $age = $dobDate->diff($today)->y;

        if($age < 16) {
            http_response_code(403); // Forbidden
            echo json_encode(["status" => "error", "message" => "You must be at least 16 years old to join Zylo."]);
            return;
        }

        // 3. Calculate Zodiac
        $zodiacData = ZodiacHelper::getZodiacSign($data->dob);

        // 4. Hash the password securely
        $hashed_password = password_hash($data->password, PASSWORD_BCRYPT);

        // 5. Insert into Database
        try {
            $query = "INSERT INTO users (first_name, last_name, username, email, password_hash, dob, zodiac_sign, zodiac_element) 
                      VALUES (:first_name, :last_name, :username, :email, :password_hash, :dob, :zodiac_sign, :zodiac_element)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":first_name", $data->first_name);
            $stmt->bindParam(":last_name", $data->last_name);
            $stmt->bindParam(":username", $data->username);
            $stmt->bindParam(":email", $data->email);
            $stmt->bindParam(":password_hash", $hashed_password);
            $stmt->bindParam(":dob", $data->dob);
            $stmt->bindParam(":zodiac_sign", $zodiacData['sign']);
            $stmt->bindParam(":zodiac_element", $zodiacData['element']);

            if($stmt->execute()) {
                http_response_code(201); // Created
                echo json_encode([
                    "status" => "success", 
                    "message" => "Registration successful! Welcome to Zylo.",
                    "zodiac_sign" => $zodiacData['sign'],
                    "zodiac_element" => $zodiacData['element']
                ]);
            }
        } catch(PDOException $e) {
            // Handle duplicate emails or usernames
            if ($e->getCode() == 23000) {
                http_response_code(409); // Conflict
                echo json_encode(["status" => "error", "message" => "Username or Email already exists."]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Internal Server Error."]);
            }
        }
    }
}
?>