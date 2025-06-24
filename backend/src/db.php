<?php
function getPDO() {
    // Production environment variables (Railway, Heroku, PlanetScale, etc.)
    $prod_host = $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? $_ENV['DATABASE_HOST'] ?? null;
    $prod_port = $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? $_ENV['DATABASE_PORT'] ?? '3306';
    $prod_dbname = $_ENV['DB_NAME'] ?? $_ENV['MYSQLDATABASE'] ?? $_ENV['DATABASE_NAME'] ?? null;
    $prod_username = $_ENV['DB_USER'] ?? $_ENV['MYSQLUSER'] ?? $_ENV['DATABASE_USER'] ?? null;
    $prod_password = $_ENV['DB_PASS'] ?? $_ENV['MYSQLPASSWORD'] ?? $_ENV['DATABASE_PASSWORD'] ?? null;
    
    // Check if we're in production (environment variables are set)
    if ($prod_host && $prod_dbname && $prod_username !== null) {
        try {
            $dsn = "mysql:host=$prod_host;port=$prod_port;dbname=$prod_dbname;charset=utf8mb4";
            $pdo = new PDO($dsn, $prod_username, $prod_password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // For some cloud providers
            ]);
            
            // Test the connection
            $pdo->query("SELECT 1");
            error_log("Database connected successfully using: Production Environment");
            return $pdo;
        } catch (PDOException $e) {
            error_log("Production database connection failed: " . $e->getMessage());
            throw new PDOException("Database connection failed. Please check if your database service is running.");
        }
    }
    
    // Local development settings (XAMPP/MAMP)
    $host = 'localhost';
    $dbname = 'markmaster2'; // Your local database name
    $username = 'root';
    $password = ''; // Default XAMPP password is empty
    
    // Try different connection methods for Mac local development
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
    
    error_log("Database connection failed: SQLSTATE[HY000] [2002] No such file or directory");
    throw new PDOException("Database connection failed. Please check if XAMPP MySQL is running.");
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
            'user_count' => $result['count'],
            'environment' => isset($_ENV['DB_HOST']) ? 'Production' : 'Local Development'
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

// Helper function to check if we're in production
function isProduction() {
    return isset($_ENV['DB_HOST']) || isset($_ENV['MYSQLHOST']) || isset($_ENV['DATABASE_HOST']);
}
?>