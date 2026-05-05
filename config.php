<?php
// ============================================================
// GRD - Database Configuration
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Change for production
define('DB_PASS', '');             // Change for production
define('DB_NAME', 'grd_website');

// Email settings
define('SMTP_FROM', 'noreply@gururocdrillingtool.com');
define('SMTP_TO',   'info@gururocdrillingtool.com');  // Receives enquiries
define('SITE_NAME', 'GURUROCDRILLINGTOOL');

// Create connection
function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Auto-create tables if they don't exist
function initDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) return false;
    $conn->set_charset('utf8mb4');

    // Create database
    $conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db(DB_NAME);

    // Products table
    $conn->query("CREATE TABLE IF NOT EXISTS `products` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `category` VARCHAR(100) DEFAULT 'General',
        `description` TEXT,
        `specifications` JSON,
        `image` VARCHAR(255),
        `is_active` TINYINT(1) DEFAULT 1,
        `sort_order` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Enquiries table
    $conn->query("CREATE TABLE IF NOT EXISTS `enquiries` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(50),
        `company` VARCHAR(255),
        `product_interest` VARCHAR(255),
        `message` TEXT,
        `is_read` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Seed sample products if empty
    $count = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
    if ($count == 0) {
        $samples = [
            ['DTH Drill Bits', 'DTH', 'High-performance Down-the-Hole drill bits engineered for maximum penetration in rock formations.', '{"Diameter":"76mm - 200mm","Thread":"SD/BR/QL Series","Steel Grade":"High Alloy Steel","Application":"Rock Drilling / Mining"}'],
            ['DTH Hammers', 'DTH', 'Robust DTH hammers delivering superior energy transfer for efficient deep hole drilling.', '{"Working Pressure":"10-35 bar","Hole Size":"76mm - 254mm","Thread Type":"SD/QL","Weight":"8kg - 48kg"}'],
            ['Rock Drill Bits', 'Rock Drilling', 'Precision-engineered rock drill bits for top hammer drilling applications.', '{"Type":"Button / Cross / X-type","Diameter":"32mm - 127mm","Thread":"R22/R25/R28/R32","Button Grade":"Carbide"}'],
            ['DTH Accessories', 'Accessories', 'Complete range of DTH accessories including adapters, centralizers and drill pipes.', '{"Material":"Alloy Steel","Surface":"Heat Treated","Compatibility":"All DTH Systems","Finish":"Phosphated"}'],
            ['Mining Tools', 'Mining', 'Heavy-duty mining tools designed for underground and open-pit mining operations.', '{"Application":"Underground / Open Pit","Standard":"IS/ISO Certified","Hardness":"HRC 52-58","Life":"3x Standard Tools"}'],
            ['Cutting Tools', 'Drill Bits & Cutting Tools', 'Precision cutting tools for core drilling and exploration applications.', '{"Type":"PDC / Roller Cone / Diamond","Size":"2" - 14"","Connection":"API/Custom","Application":"Core Drilling"}'],
        ];
        foreach ($samples as $s) {
            $stmt = $conn->prepare("INSERT INTO products (name, category, description, specifications) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $s[0], $s[1], $s[2], $s[3]);
            $stmt->execute();
        }
    }
    $conn->close();
    return true;
}

// Run init
initDB();

// CORS and JSON header helper
function jsonResponse($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
?>
