<?php
// Set standard API headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests for React Native / Web CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include configurations and controllers
require_once 'config/Database.php';
require_once 'controllers/AuthController.php';

// Initialize Database
$database = new Database();
$db = $database->getConnection();

// Get the requested endpoint from the .htaccess rewrite
$endpoint = isset($_GET['endpoint']) ? rtrim($_GET['endpoint'], '/') : '';
$requestMethod = $_SERVER["REQUEST_METHOD"];

// Get JSON data sent from the frontend (React Native)
$data = json_decode(file_get_contents("php://input"));

// ==========================================
// 🚀 DYNAMIC ROUTER
// ==========================================
switch ($endpoint) {
    
    // Route: POST /api/users/register
    case 'users/register':
        if ($requestMethod === 'POST') {
            $authController = new AuthController($db);
            $authController->register($data);
        } else {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
        }
        break;

    // Route: POST /api/users/login (Placeholder for later)
    case 'users/login':
        if ($requestMethod === 'POST') {
            echo json_encode(["status" => "success", "message" => "Login endpoint reached!"]);
        }
        break;

    // 404 Not Found fallback
    default:
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Endpoint '$endpoint' not found."]);
        break;
}
?>