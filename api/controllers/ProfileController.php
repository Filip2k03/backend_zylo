<?php
class ProfileController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getProfile($profileId) {
        try {
            $query = "SELECT id, username, first_name, last_name, zodiac_sign, zodiac_element, 
                             profile_pic_url, theme_song_url, vibe_emoji, vibe_text, vibe_expires_at 
                      FROM users WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $profileId);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Check if Vibe has expired
                if ($user['vibe_expires_at'] !== null && strtotime($user['vibe_expires_at']) < time()) {
                    $user['vibe_emoji'] = null;
                    $user['vibe_text'] = null;
                }

                http_response_code(200);
                echo json_encode(["status" => "success", "profile" => $user]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Profile not found."]);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Database error."]);
        }
    }
}
?>