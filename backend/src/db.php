<?php
function getPDO() {
    // Mac XAMPP/MAMP database settings
    $host = 'localhost';
    $dbname = 'markmaster2'; // Your database name
    $username = 'root';
    $password = ''; // Default XAMPP password is empty
    
    // Try different connection methods for Mac
    $connections = [
        // Standard TCP connection
        [
            'dsn' => "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4",
            'type' => 'TCP Port 3306'
        ],
        // MAMP default port
        [
            'dsn' => "mysql:host=$host;port=8889;dbname=$dbname;charset=utf8mb4", 
            'type' => 'MAMP Port 8889'
        ],
        // Socket connection (common on Mac)
        [
            'dsn' => "mysql:unix_socket=/tmp/mysql.sock;dbname=$dbname;charset=utf8mb4",
            'type' => 'Unix Socket /tmp/mysql.sock'
        ],
        // XAMPP socket
        [
            'dsn' => "mysql:unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname=$dbname;charset=utf8mb4",
            'type' => 'XAMPP Socket'
        ]
    ];
    
    $lastError = '';
    
    foreach ($connections as $conn) {
        try {
            $pdo = new PDO($conn['dsn'], $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Test the connection
            $pdo->query("SELECT 1");
            
            error_log("Database connected successfully using: " . $conn['type']);
            return $pdo;
        } catch (PDOException $e) {
            $lastError = $e->getMessage();
            error_log("Failed to connect using " . $conn['type'] . ": " . $e->getMessage());
            continue;
        }
    }
    
    throw new PDOException("All connection attempts failed. Last error: " . $lastError);
}

// Test function to verify database connection
function testDatabaseConnection() {
    try {
        $pdo = getPDO();
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        return [
            'success' => true,
            'message' => 'Database connected successfully',
            'user_count' => $result['count']
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Database connection failed: ' . $e->getMessage(),
            'suggestions' => [
                'Make sure MySQL/MariaDB is running in XAMPP',
                'Check if database "markmaster2" exists',
                'Verify MySQL is running on port 3306 or 8889',
                'Check XAMPP control panel for MySQL status'
            ]
        ];
    }
}
?>