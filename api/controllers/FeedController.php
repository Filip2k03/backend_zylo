<?php

class FeedController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Generates the chronological feed for the user
    public function getMainFeed($userId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;

        try {
            // Select posts, ignoring expired "Flashes"
            // Joins users to get author info, and aggregates total hypes directly in the query
            $query = "
                SELECT 
                    p.id AS post_id, p.post_type, p.caption, p.created_at, p.expires_at,
                    u.id AS author_id, u.username, u.profile_pic_url, u.vibe_emoji,
                    COALESCE(SUM(h.hype_count), 0) AS total_hypes,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS total_comments
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN hypes h ON p.id = h.post_id
                WHERE (p.expires_at IS NULL OR p.expires_at > NOW())
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();

            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // If it's a 'dump' post, we need to fetch the attached media
            foreach ($posts as &$post) {
                if ($post['post_type'] === 'dump' || $post['post_type'] === 'standard') {
                    $mediaQuery = "SELECT media_url, media_order FROM post_media WHERE post_id = :post_id ORDER BY media_order ASC";
                    $mediaStmt = $this->db->prepare($mediaQuery);
                    $mediaStmt->execute([':post_id' => $post['post_id']]);
                    $post['media'] = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $post['media'] = [];
                }
            }

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "page" => $page,
                "data" => $posts
            ]);

        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    }
}
?>