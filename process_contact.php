<?php
// Zpracování kontaktního formuláře
require_once 'track_visit.php';

// Databázové připojení
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
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Chyba připojení k databázi']);
    exit;
}

// Funkce pro validaci emailu
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Funkce pro sanitizaci vstupu
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Zpracování POST požadavku
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        // Získání a validace vstupních dat
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $service = sanitizeInput($_POST['service'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');
        
        // Validace povinných polí
        if (empty($name)) {
            throw new Exception('Jméno je povinné');
        }
        
        if (empty($email)) {
            throw new Exception('Email je povinný');
        }
        
        if (!isValidEmail($email)) {
            throw new Exception('Neplatný formát emailu');
        }
        
        if (empty($service)) {
            throw new Exception('Výběr služby je povinný');
        }
        
        if (empty($message)) {
            throw new Exception('Zpráva je povinná');
        }
        
        // Vložení do databáze
        $stmt = $pdo->prepare("
            INSERT INTO contact_submissions (name, email, phone, service, message) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$name, $email, $phone, $service, $message]);
        
        // Sledování návštěvy (kontaktní formulář)
        trackVisit($pdo, 'contact_form');
        
        // Aktualizace statistik
        updateDailyStats($pdo);
        updateMonthlyStats($pdo);
        
        $response['success'] = true;
        $response['message'] = 'Děkujeme za vaši zprávu! Budeme vás kontaktovat co nejdříve.';
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    // Vrácení JSON odpovědi
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Pokud není POST požadavek, přesměrovat na hlavní stránku
header('Location: index.php');
exit;
?>
