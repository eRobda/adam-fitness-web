<?php
// Sledování návštěv na webu
function trackVisit($pdo, $pageName = 'main') {
    try {
        // Získání IP adresy návštěvníka
        $visitorIP = $_SERVER['HTTP_CLIENT_IP'] ?? 
                     $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
                     $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Získání User Agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Získání referrer
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        
        // Generování session ID
        if (!isset($_COOKIE['session_id'])) {
            $sessionId = uniqid('sess_', true);
            setcookie('session_id', $sessionId, time() + 3600 * 24, '/'); // 24 hodin
        } else {
            $sessionId = $_COOKIE['session_id'];
        }
        
        // Vložení návštěvy do databáze
        $stmt = $pdo->prepare("
            INSERT INTO page_visits (page_name, visitor_ip, user_agent, referrer, session_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$pageName, $visitorIP, $userAgent, $referrer, $sessionId]);
        
        return true;
    } catch (Exception $e) {
        // Logování chyby (v produkci by se mělo logovat do souboru)
        error_log("Chyba při sledování návštěvy: " . $e->getMessage());
        return false;
    }
}

// Funkce pro aktualizaci denních statistik
function updateDailyStats($pdo) {
    try {
        $today = date('Y-m-d');
        
        // Kontrola, zda už existují statistiky pro dnešek
        $stmt = $pdo->prepare("SELECT id FROM daily_stats WHERE visit_date = ?");
        $stmt->execute([$today]);
        
        if ($stmt->fetch()) {
            // Aktualizace existujících statistik
            $stmt = $pdo->prepare("
                UPDATE daily_stats SET 
                    total_visits = (SELECT COUNT(*) FROM page_visits WHERE DATE(visit_date) = ?),
                    unique_visitors = (SELECT COUNT(DISTINCT visitor_ip) FROM page_visits WHERE DATE(visit_date) = ?),
                    page_views = (SELECT COUNT(*) FROM page_visits WHERE DATE(visit_date) = ?),
                    updated_at = CURRENT_TIMESTAMP
                WHERE visit_date = ?
            ");
            $stmt->execute([$today, $today, $today, $today]);
        } else {
            // Vytvoření nových statistik
            $stmt = $pdo->prepare("
                INSERT INTO daily_stats (visit_date, total_visits, unique_visitors, page_views)
                SELECT 
                    DATE(visit_date) as visit_date,
                    COUNT(*) as total_visits,
                    COUNT(DISTINCT visitor_ip) as unique_visitors,
                    COUNT(*) as page_views
                FROM page_visits 
                WHERE DATE(visit_date) = ?
                GROUP BY DATE(visit_date)
            ");
            $stmt->execute([$today]);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Chyba při aktualizaci denních statistik: " . $e->getMessage());
        return false;
    }
}

// Funkce pro aktualizaci měsíčních statistik
function updateMonthlyStats($pdo) {
    try {
        $currentMonth = date('Y-m');
        
        // Kontrola, zda už existují statistiky pro tento měsíc
        $stmt = $pdo->prepare("SELECT id FROM monthly_stats WHERE year_month = ?");
        $stmt->execute([$currentMonth]);
        
        if ($stmt->fetch()) {
            // Aktualizace existujících statistik
            $stmt = $pdo->prepare("
                UPDATE monthly_stats SET 
                    total_visits = (SELECT COUNT(*) FROM page_visits WHERE DATE_FORMAT(visit_date, '%Y-%m') = ?),
                    unique_visitors = (SELECT COUNT(DISTINCT visitor_ip) FROM page_visits WHERE DATE_FORMAT(visit_date, '%Y-%m') = ?),
                    page_views = (SELECT COUNT(*) FROM page_visits WHERE DATE_FORMAT(visit_date, '%Y-%m') = ?),
                    contact_submissions = (SELECT COUNT(*) FROM contact_submissions WHERE DATE_FORMAT(submission_date, '%Y-%m') = ?),
                    updated_at = CURRENT_TIMESTAMP
                WHERE year_month = ?
            ");
            $stmt->execute([$currentMonth, $currentMonth, $currentMonth, $currentMonth, $currentMonth]);
        } else {
            // Vytvoření nových statistik
            $stmt = $pdo->prepare("
                INSERT INTO monthly_stats (year_month, total_visits, unique_visitors, page_views, contact_submissions)
                SELECT 
                    DATE_FORMAT(visit_date, '%Y-%m') as year_month,
                    COUNT(*) as total_visits,
                    COUNT(DISTINCT visitor_ip) as unique_visitors,
                    COUNT(*) as page_views,
                    (SELECT COUNT(*) FROM contact_submissions WHERE DATE_FORMAT(submission_date, '%Y-%m') = ?) as contact_submissions
                FROM page_visits 
                WHERE DATE_FORMAT(visit_date, '%Y-%m') = ?
                GROUP BY DATE_FORMAT(visit_date, '%Y-%m')
            ");
            $stmt->execute([$currentMonth, $currentMonth]);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Chyba při aktualizaci měsíčních statistik: " . $e->getMessage());
        return false;
    }
}
?>
