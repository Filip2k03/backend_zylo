<?php
class Database {
    private $host = "localhost";
    private $db_name = "zylo";
    private $username = "root"; // Change to your database username
    private $password = "Stephan2k03";     // Change to your database password
    public $conn;

    // Get the database connection
    public function getConnection() {
        $this->conn = null;
        try {
            // Using PDO for maximum security against SQL injection
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo json_encode(["status" => "error", "message" => "Database connection error: " . $exception->getMessage()]);
            exit;
        }
        return $this->conn;
    }
}
?>