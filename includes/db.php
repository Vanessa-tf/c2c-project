<?php
// db.php - Keep this file simple and reliable
function getPDO() {
    static $pdo = null;
    
    // Return existing connection if already established
    if ($pdo !== null) {
        return $pdo;
    }
    
    $host = 'localhost';
    $dbname = 'smallstreet';
    $username = 'root';
    $password = '';
    
    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

// Test connection function (optional - for debugging)
function testConnection() {
    try {
        $pdo = getPDO();
        return true;
    } catch(Exception $e) {
        return false;
    }
}
?>