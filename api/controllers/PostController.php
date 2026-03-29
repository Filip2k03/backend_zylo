<?php
class PostController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Handles Standard Posts, Dumps, and Flashes
    public function createPost($userId, $data) {
        $postType = isset($data->post_type) ? $data->post_type : 'standard';
        $caption = isset($data->caption) ? $data->caption : '';
        $expiresAt = null;

        if ($postType === 'flash') {
            // Flashes expire exactly 24 hours from now
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        }

        $this->db->beginTransaction();

        try {
            // 1. Insert Core Post
            $postQuery = "INSERT INTO posts (user_id, post_type, caption, expires_at) VALUES (:user_id, :post_type, :caption, :expires_at)";
            $stmt = $this->db->prepare($postQuery);
            $stmt->execute([
                ':user_id' => $userId,
                ':post_type' => $postType,
                ':caption' => $caption,
                ':expires_at' => $expiresAt
            ]);
            
            $postId = $this->db->lastInsertId();

            // 2. Insert Multiple Media Files (For Photo Dumps)
            if(!empty($data->media) && is_array($data->media)) {
                $mediaQuery = "INSERT INTO post_media (post_id, media_url, media_order) VALUES (?, ?, ?)";
                $mediaStmt = $this->db->prepare($mediaQuery);
                
                foreach($data->media as $index => $mediaUrl) {
                    $mediaStmt->execute([$postId, $mediaUrl, $index + 1]);
                }
            }

            $this->db->commit();
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Post published!", "post_id" => $postId]);

        } catch(Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create post."]);
        }
    }
}
?>