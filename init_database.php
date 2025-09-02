<?php
// Inicializace databáze pro Adam Preis dashboard
// Tento soubor spusťte pouze jednou pro vytvoření tabulek

$db_config = [
    'host' => '92.113.22.82',
    'dbname' => 'u498377835_adampreis',
    'username' => 'u498377835_adampreis',
    'password' => 'AdamPosilko67.'
];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "<h2>Připojení k databázi úspěšné!</h2>";
    
    // Vytvoření tabulek
    $tables = [
        // Tabulka pro uživatele (admin)
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE
        )",
        
        // Tabulka pro návštěvy webu
        "CREATE TABLE IF NOT EXISTS page_visits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_name VARCHAR(100) NOT NULL,
            visitor_ip VARCHAR(45),
            user_agent TEXT,
            referrer VARCHAR(500),
            visit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            session_id VARCHAR(100),
            country VARCHAR(100),
            city VARCHAR(100)
        )",
        
        // Tabulka pro odpovědi z kontaktního formuláře
        "CREATE TABLE IF NOT EXISTS contact_submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            service VARCHAR(50) NOT NULL,
            message TEXT,
            submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_read BOOLEAN DEFAULT FALSE,
            status ENUM('new', 'contacted', 'completed', 'archived') DEFAULT 'new'
        )",
        
        // Tabulka pro statistiky denních návštěv
        "CREATE TABLE IF NOT EXISTS daily_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            visit_date DATE UNIQUE NOT NULL,
            total_visits INT DEFAULT 0,
            unique_visitors INT DEFAULT 0,
            page_views INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Tabulka pro měsíční statistiky
        "        CREATE TABLE IF NOT EXISTS monthly_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            `year_month` VARCHAR(7) UNIQUE NOT NULL,
            total_visits INT DEFAULT 0,
            unique_visitors INT DEFAULT 0,
            page_views INT DEFAULT 0,
            contact_submissions INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )"
    ];
    
    echo "<h3>Vytváření tabulek...</h3>";
    
    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Tabulka vytvořena úspěšně</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ Tabulka již existuje nebo chyba: " . $e->getMessage() . "</p>";
        }
    }
    
    // Vytvoření indexů pro lepší výkon
    echo "<h3>Vytváření indexů...</h3>";
    
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_page_visits_date ON page_visits(visit_date)",
        "CREATE INDEX IF NOT EXISTS idx_page_visits_page ON page_visits(page_name)",
        "CREATE INDEX IF NOT EXISTS idx_contact_submissions_date ON contact_submissions(submission_date)",
        "CREATE INDEX IF NOT EXISTS idx_contact_submissions_status ON contact_submissions(status)",
        "CREATE INDEX IF NOT EXISTS idx_daily_stats_date ON daily_stats(visit_date)",
        "CREATE INDEX IF NOT EXISTS idx_monthly_stats_month ON monthly_stats(year_month)"
    ];
    
    foreach ($indexes as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Index vytvořen úspěšně</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ Index již existuje nebo chyba: " . $e->getMessage() . "</p>";
        }
    }
    
    // Vložení výchozího admin uživatele
    echo "<h3>Vytváření admin uživatele...</h3>";
    
    try {
        // Kontrola, zda už admin uživatel existuje
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
        $stmt->execute();
        
        if (!$stmt->fetch()) {
            // Vytvoření nového admin uživatele
            $adminPassword = 'admin123';
            $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password_hash, email) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute(['admin', $passwordHash, 'admin@adampreis.cz']);
            
            echo "<p style='color: green;'>✓ Admin uživatel vytvořen úspěšně</p>";
            echo "<p><strong>Přihlašovací údaje:</strong></p>";
            echo "<p>Uživatelské jméno: <strong>admin</strong></p>";
            echo "<p>Heslo: <strong>admin123</strong></p>";
        } else {
            echo "<p style='color: blue;'>ℹ Admin uživatel již existuje</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Chyba při vytváření admin uživatele: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>Inicializace databáze dokončena!</h3>";
    echo "<p><a href='dashboard.php' style='color: #f97316; text-decoration: none;'>→ Přejít na Dashboard</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Chyba připojení k databázi!</h2>";
    echo "<p>Chyba: " . $e->getMessage() . "</p>";
    echo "<p>Zkontrolujte prosím:</p>";
    echo "<ul>";
    echo "<li>Připojení k internetu</li>";
    echo "<li>Správnost databázových údajů</li>";
    echo "<li>Dostupnost databázového serveru</li>";
    echo "</ul>";
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicializace databáze - Adam Preis</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #111827;
            color: #f9fafb;
            padding: 2rem;
            line-height: 1.6;
        }
        h2, h3 {
            color: #f97316;
        }
        p {
            margin: 0.5rem 0;
        }
        a {
            color: #f97316;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>🚀 Inicializace databáze pro Adam Preis Dashboard</h1>
    <p>Tento soubor vytvoří všechny potřebné tabulky a výchozího admin uživatele.</p>
    <hr style="border: 1px solid #374151; margin: 2rem 0;">
</body>
</html>
