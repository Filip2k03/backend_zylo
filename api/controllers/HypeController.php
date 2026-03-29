<?php

class HypeController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addHype($data) {
        // Validate required input
        if(empty($data->post_id) || empty($data->user_id)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "post_id and user_id are required."]);
            return;
        }

        $postId = (int)$data->post_id;
        $userId = (int)$data->user_id;
        
        // Allows React Native to send the total hypes generated from holding the button
        // e.g., if user held for 15 hypes, $data->count will be 15
        $hypesToAdd = isset($data->count) ? (int)$data->count : 1;

        try {
            // Check if this user has already hyped this post
            $checkQuery = "SELECT hype_count FROM hypes WHERE post_id = :post_id AND user_id = :user_id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(":post_id", $postId);
            $checkStmt->bindParam(":user_id", $userId);
            $checkStmt->execute();

            $existingHype = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingHype) {
                // User already hyped, let's increment their hype count
                $currentCount = (int)$existingHype['hype_count'];
                
                if ($currentCount >= 50) {
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => "Maximum hype limit (50) reached for this post."]);
                    return;
                }

                // Ensure it never goes above 50
                $newCount = min(50, $currentCount + $hypesToAdd);

                $updateQuery = "UPDATE hypes SET hype_count = :new_count WHERE post_id = :post_id AND user_id = :user_id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(":new_count", $newCount);
                $updateStmt->bindParam(":post_id", $postId);
                $updateStmt->bindParam(":user_id", $userId);
                $updateStmt->execute();

                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Hype updated!", "current_hypes" => $newCount]);

            } else {
                // First time user is hyping this post
                $initialCount = min(50, $hypesToAdd);

                $insertQuery = "INSERT INTO hypes (post_id, user_id, hype_count) VALUES (:post_id, :user_id, :hype_count)";
                $insertStmt = $this->db->prepare($insertQuery);
                $insertStmt->bindParam(":post_id", $postId);
                $insertStmt->bindParam(":user_id", $userId);
                $insertStmt->bindParam(":hype_count", $initialCount);
                $insertStmt->execute();

                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Hype added!", "current_hypes" => $initialCount]);
            }

        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    }
}
?>