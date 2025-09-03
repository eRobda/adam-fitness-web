<?php
session_start();

// Kontrola přihlášení
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

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
    die("Chyba připojení k databázi: " . $e->getMessage());
}

// Funkce pro odhlášení
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Zpracování odhlášení
if (isset($_GET['logout'])) {
    logout();
}

// Funkce pro získání statistik návštěv
function getVisitStats($pdo, $days = 30) {
    $stmt = $pdo->prepare("
        SELECT 
            DATE(visit_date) as date,
            COUNT(*) as visits,
            COUNT(DISTINCT visitor_ip) as unique_visitors
        FROM page_visits 
        WHERE visit_date >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
        GROUP BY DATE(visit_date)
        ORDER BY date ASC
    ");
    $stmt->execute([$days]);
    return $stmt->fetchAll();
}

// Funkce pro získání celkových statistik
function getTotalStats($pdo) {
    $stats = [];
    
    // Celkový počet návštěv
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM page_visits");
    $stats['total_visits'] = $stmt->fetch()['total'];
    
    // Unikátní návštěvníci
    $stmt = $pdo->query("SELECT COUNT(DISTINCT visitor_ip) as `unique` FROM page_visits");
    $stats['unique_visitors'] = $stmt->fetch()['unique'];
    
    // Návštěvy dnes
    $stmt = $pdo->query("SELECT COUNT(*) as today FROM page_visits WHERE DATE(visit_date) = CURRENT_DATE");
    $stats['today_visits'] = $stmt->fetch()['today'];
    
    // Návštěvy tento měsíc
    $stmt = $pdo->query("SELECT COUNT(*) as month FROM page_visits WHERE YEAR(visit_date) = YEAR(CURRENT_DATE) AND MONTH(visit_date) = MONTH(CURRENT_DATE)");
    $stats['month_visits'] = $stmt->fetch()['month'];
    
    return $stats;
}

// Funkce pro získání kontaktních formulářů
function getContactSubmissions($pdo, $limit = 50) {
    $stmt = $pdo->prepare("
        SELECT * FROM contact_submissions 
        ORDER BY submission_date DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Funkce pro označení formuláře jako přečtený
function markAsRead($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ?");
    return $stmt->execute([$id]);
}

// Zpracování akcí s formuláři
if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] === 'mark_read' && isset($_POST['id'])) {
        markAsRead($pdo, $_POST['id']);
    }
}

// Získání dat pro dashboard
$totalStats = getTotalStats($pdo);
$visitStats = getVisitStats($pdo, 30);
$contactSubmissions = getContactSubmissions($pdo);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Adam Preis</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#ed7a1a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="AP Dashboard">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="AP Dashboard">
    
    <!-- iOS Status Bar Color Fix -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTgwIiBoZWlnaHQ9IjE4MCIgdmlld0JveD0iMCAwIDE4MCAxODAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxODAiIGhlaWdodD0iMTgwIiByeD0iMjIiIGZpbGw9IiNlZDdhMWEiLz4KPHN2ZyB4PSI0NSIgeT0iNDUiIHdpZHRoPSI5MCIgaGVpZ2h0PSI5MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJ3aGl0ZSI+CjxwYXRoIGQ9Ik0yMC41NyAxNC4wN0wxOC4xMiAxMS42MkwyMC41NyA5LjE3TDE4LjEyIDYuNzJMMTUuNjcgOS4xN0wxMy4yMiA2LjcyTDEwLjc3IDkuMTdMMy4yMiAxNi43MkwyLjUgMTcuNDRMMy4yMiAxOC4xNkwxMC43NyAyNS43MUwxMy4yMiAyMy4yNkwxNS42NyAyNS43MUwxOC4xMiAyMy4yNkwyMC41NyAyNS43MUwyMi4wMiAyNC4yNkwxOS41NyAyMS44MUwyMi4wMiAxOS4zNkwyMC41NyAxNy45MUwyMi4wMiAxNi40NkwyMC41NyAxNC4wN1oiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K">
    <link rel="apple-touch-icon" sizes="152x152" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUyIiBoZWlnaHQ9IjE1MiIgdmlld0JveD0iMCAwIDE1MiAxNTIiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxNTIiIGhlaWdodD0iMTUyIiByeD0iMTkiIGZpbGw9IiNlZDdhMWEiLz4KPHN2ZyB4PSIzOCIgeT0iMzgiIHdpZHRoPSI3NiIgaGVpZ2h0PSI3NiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJ3aGl0ZSI+CjxwYXRoIGQ9Ik0yMC41NyAxNC4wN0wxOC4xMiAxMS42MkwyMC41NyA5LjE3TDE4LjEyIDYuNzJMMTUuNjcgOS4xN0wxMy4yMiA2LjcyTDEwLjc3IDkuMTdMMy4yMiAxNi43MkwyLjUgMTcuNDRMMy4yMiAxOC4xNkwxMC43NyAyNS43MUwxMy4yMiAyMy4yNkwxNS42NyAyNS43MUwxOC4xMiAyMy4yNkwyMC41NyAyNS43MUwyMi4wMiAyNC4yNkwxOS41NyAyMS44MUwyMi4wMiAxOS4zNkwyMC41NyAxNy45MUwyMi4wMiAxNi40NkwxMC43NyA5LjE3TDMuMjIgMTYuNzJMMi41IDE3LjQ0TDMuMjIgMTguMTZMMTAuNzcgMjUuNzFMMTMuMjIgMjMuMjZMMTUuNjcgMjUuNzFMMTguMTIgMjMuMjZMMjAuNTcgMjUuNzFMMjIuMDIgMjQuMjZMMTkuNTcgMjEuODFMMjIuMDIgMTkuMzZMMjAuNTcgMTcuOTFMMjIuMDIgMTYuNDZMMjAuNTcgMTQuMDdaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4KPC9zdmc+Cg==">
    <link rel="apple-touch-icon" sizes="180x180" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTgwIiBoZWlnaHQ9IjE4MCIgdmlld0JveD0iMCAwIDE4MCAxODAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0aD0iMTgwIiBoZWlnaHQ9IjE4MCIgcng9IjIyIiBmaWxsPSIjZWQ3YTFhIi8+CjxzdmcgeD0iNDUiIHk9IjQ1IiB3aWR0aD0iOTAiIGhlaWdodD0iOTAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0id2hpdGUiPgo8cGF0aCBkPSJNMjAuNTcgMTQuMDdMMTguMTIgMTEuNjJMMjAuNTcgOS4xN0wxOC4xMiA2LjcyTDE1LjY3IDkuMTdMMTMuMjIgNi43MkwxMC43NyA5LjE3TDMuMjIgMTYuNzJMMi41IDE3LjQ0TDMuMjIgMTguMTZMMTAuNzcgMjUuNzFMMTMuMjIgMjMuMjZMMTUuNjcgMjUuNzFMMTguMTIgMjMuMjZMMjAuNTcgMjUuNzFMMjIuMDIgMjQuMjZMMTkuNTcgMjEuODFMMjIuMDIgMTkuMzZMMjAuNTcgMTcuOTFMMjIuMDIgMTYuNDZMMjAuNTcgMTQuMDdaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4KPC9zdmc+Cg==">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                        'space': ['Space Grotesk', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fef7ee',
                            100: '#fdedd6',
                            200: '#fad7ac',
                            300: '#f6bb77',
                            400: '#f1943a',
                            500: '#ed7514',
                            600: '#de5a0a',
                            700: '#b8440c',
                            800: '#933612',
                            900: '#762e12',
                        }
                    },
                    letterSpacing: {
                        'tighter': '-0.05em',
                        'tight': '-0.025em',
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #111827;
            overflow-x: hidden;
        }

        .display-font { font-family: 'Space Grotesk', sans-serif; }
        .text-gradient { background: linear-gradient(135deg, #f1943a 0%, #ed7514 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .glass-effect { 
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.1); 
            border: 1px solid rgba(255, 255, 255, 0.2); 
            transition: all 0.3s ease-in-out;
        }
        
        .card-hover { 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-style: preserve-3d;
            perspective: 1000px;
        }
        
        .card-hover:hover {
            transform: translateY(-8px) rotateX(5deg) rotateY(5deg);
            box-shadow: 
                0 30px 60px -12px rgba(0, 0, 0, 0.6),
                0 0 30px rgba(237, 117, 20, 0.2);
        }
        
        .card-hover::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(237, 117, 20, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: inherit;
        }
        
        .card-hover:hover::before {
            opacity: 1;
        }
        
        /* Animations */
        
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }
        
        .stats-number {
            display: inline-block;
            animation: fadeInScale 0.8s ease-out forwards;
        }
        
        @keyframes fadeInScale {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        /* Premium Button Effects */
        .btn-premium {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .btn-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-premium:hover::before {
            left: 100%;
        }
        
        .btn-premium:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 20px 40px rgba(237, 117, 20, 0.3);
        }
        
        /* Loading Animation */
        .loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, #ed7514, #f1943a);
            z-index: 9999;
            transition: width 0.3s ease;
        }
        
        /* iOS Status Bar Fix */
        @supports (-webkit-touch-callout: none) {
            body {
                padding-top: env(safe-area-inset-top);
                padding-bottom: env(safe-area-inset-bottom);
            }
        }
        
        /* iOS PWA Status Bar */
        @media screen and (display-mode: standalone) {
            body {
                padding-top: env(safe-area-inset-top);
            }
        }
    </style>
</head>
<body class="font-inter">
    <!-- Loading Bar -->
    <div class="loading-bar" id="loadingBar"></div>
    
         <!-- Navigation -->
     <nav class="fixed top-0 w-full glass-effect z-50 border-b border-white/20">
         <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
             <div class="flex justify-between items-center h-16 md:h-20">
                 <div class="flex items-center space-x-2 md:space-x-3">
                     <div class="w-8 h-8 md:w-12 md:h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl md:rounded-2xl flex items-center justify-center shadow-2xl">
                         <i class="fas fa-dumbbell text-white text-sm md:text-xl"></i>
                     </div>
                     <div class="flex flex-col md:flex-row md:items-center md:space-x-3">
                         <span class="display-font text-lg md:text-2xl font-bold text-white tracking-tight">ADAM PREIS</span>
                         <span class="hidden md:inline text-primary-400 font-medium text-xs md:text-sm bg-primary-500/20 px-2 md:px-3 py-1 rounded-full">Dashboard</span>
                     </div>
                 </div>
                 <div class="flex items-center space-x-2 md:space-x-6">
                     <div class="hidden md:flex items-center space-x-3">
                         <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                         <span class="text-white/80 text-sm">Online</span>
                     </div>
                     <span class="hidden lg:inline text-white/80">Přihlášen jako: <span class="text-primary-400 font-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></span></span>
                     <a href="?logout=1" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-3 md:px-6 py-2 md:py-3 rounded-xl md:rounded-2xl transition-all duration-300 transform hover:scale-105 shadow-2xl btn-premium text-xs md:text-sm">
                         <i class="fas fa-sign-out-alt mr-1 md:mr-2"></i><span class="hidden sm:inline">Odhlásit</span><span class="sm:hidden">Logout</span>
                     </a>
                 </div>
             </div>
         </div>
     </nav>

         <div class="pt-20 md:pt-32 pb-6 md:pb-8">
         <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
             <!-- Welcome Header -->
             <div class="text-center mb-8 md:mb-12 animate-on-scroll">
                 <h1 class="display-font text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-black mb-4 md:mb-6 tracking-tight text-white">
                     Vítejte v <span class="text-gradient">Dashboardu</span>
                 </h1>
                 <p class="text-base sm:text-lg md:text-xl text-white/70 max-w-2xl md:max-w-3xl mx-auto">Profesionální přehled vašich webových statistik a kontaktních formulářů</p>
             </div>

                         <!-- Statistiky -->
             <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-8 md:mb-12 animate-on-scroll">
                 <div class="glass-effect rounded-2xl md:rounded-3xl p-4 md:p-8 card-hover relative overflow-hidden">
                     <div class="absolute top-0 right-0 w-16 md:w-32 h-16 md:h-32 bg-blue-500/10 rounded-full blur-2xl md:blur-3xl"></div>
                     <div class="relative z-10">
                         <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 md:mb-6 text-center md:text-left">
                             <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl md:rounded-2xl flex items-center justify-center shadow-2xl mx-auto md:mx-0 mb-3 md:mb-0">
                                 <i class="fas fa-eye text-white text-lg md:text-2xl"></i>
                             </div>
                             <div class="md:text-right">
                                 <div class="text-2xl md:text-3xl font-bold text-white stats-number"><?php echo number_format($totalStats['total_visits']); ?></div>
                                 <div class="text-blue-400 text-xs md:text-sm font-medium">Celkem</div>
                             </div>
                         </div>
                         <h3 class="text-white font-semibold text-sm md:text-lg mb-1 md:mb-2 text-center md:text-left">Celkem návštěv</h3>
                         <p class="text-white/60 text-xs md:text-sm text-center md:text-left hidden md:block">Všechny návštěvy vašeho webu</p>
                     </div>
                 </div>
                 
                 <div class="glass-effect rounded-2xl md:rounded-3xl p-4 md:p-8 card-hover relative overflow-hidden">
                     <div class="absolute top-0 right-0 w-16 md:w-32 h-16 md:h-32 bg-green-500/10 rounded-full blur-2xl md:blur-3xl"></div>
                     <div class="relative z-10">
                         <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 md:mb-6 text-center md:text-left">
                             <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-xl md:rounded-2xl flex items-center justify-center shadow-2xl mx-auto md:mx-0 mb-3 md:mb-0">
                                 <i class="fas fa-users text-white text-lg md:text-2xl"></i>
                             </div>
                             <div class="md:text-right">
                                 <div class="text-2xl md:text-3xl font-bold text-white stats-number"><?php echo number_format($totalStats['unique_visitors']); ?></div>
                                 <div class="text-green-400 text-xs md:text-sm font-medium">Unikátní</div>
                             </div>
                         </div>
                         <h3 class="text-white font-semibold text-sm md:text-lg mb-1 md:mb-2 text-center md:text-left">Unikátní návštěvníci</h3>
                         <p class="text-white/60 text-xs md:text-sm text-center md:text-left hidden md:block">Rozdílné IP adresy</p>
                     </div>
                 </div>
                 
                 <div class="glass-effect rounded-2xl md:rounded-3xl p-4 md:p-8 card-hover relative overflow-hidden">
                     <div class="absolute top-0 right-0 w-16 md:w-32 h-16 md:h-32 bg-primary-500/10 rounded-full blur-2xl md:blur-3xl"></div>
                     <div class="relative z-10">
                         <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 md:mb-6 text-center md:text-left">
                             <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl md:rounded-2xl flex items-center justify-center shadow-2xl mx-auto md:mx-0 mb-3 md:mb-0">
                                 <i class="fas fa-calendar-day text-white text-lg md:text-2xl"></i>
                             </div>
                             <div class="md:text-right">
                                 <div class="text-2xl md:text-3xl font-bold text-white stats-number"><?php echo number_format($totalStats['today_visits']); ?></div>
                                 <div class="text-primary-400 text-xs md:text-sm font-medium">Dnes</div>
                             </div>
                         </div>
                         <h3 class="text-white font-semibold text-sm md:text-lg mb-1 md:mb-2 text-center md:text-left">Návštěvy dnes</h3>
                         <p class="text-white/60 text-xs md:text-sm text-center md:text-left hidden md:block">Aktuální denní statistiky</p>
                     </div>
                 </div>
                 
                 <div class="glass-effect rounded-2xl md:rounded-3xl p-4 md:p-8 card-hover relative overflow-hidden">
                     <div class="absolute top-0 right-0 w-16 md:w-32 h-16 md:h-32 bg-purple-500/10 rounded-full blur-2xl md:blur-3xl"></div>
                     <div class="relative z-10">
                         <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 md:mb-6 text-center md:text-left">
                             <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl md:rounded-2xl flex items-center justify-center shadow-2xl mx-auto md:mx-0 mb-3 md:mb-0">
                                 <i class="fas fa-calendar-alt text-white text-lg md:text-2xl"></i>
                             </div>
                             <div class="md:text-right">
                                 <div class="text-2xl md:text-3xl font-bold text-white stats-number"><?php echo number_format($totalStats['month_visits']); ?></div>
                                 <div class="text-purple-400 text-xs md:text-sm font-medium">Měsíc</div>
                             </div>
                         </div>
                         <h3 class="text-white font-semibold text-sm md:text-lg mb-1 md:mb-2 text-center md:text-left">Tento měsíc</h3>
                         <p class="text-white/60 text-xs md:text-sm text-center md:text-left hidden md:block">Měsíční přehled návštěv</p>
                     </div>
                 </div>
             </div>

                         <!-- Grafy a statistiky -->
             <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-8 mb-8 md:mb-12">
                 <!-- Graf návštěvnosti -->
                 <div class="glass-effect rounded-2xl md:rounded-3xl p-4 md:p-8 card-hover animate-on-scroll">
                     <div class="flex items-center justify-between mb-4 md:mb-8">
                         <h3 class="display-font text-lg md:text-2xl font-bold text-white tracking-tight">Návštěvnost (30 dní)</h3>
                         <div class="flex space-x-2">
                             <div class="w-2 h-2 md:w-3 md:h-3 bg-primary-500 rounded-full"></div>
                             <div class="w-2 h-2 md:w-3 md:h-3 bg-blue-500 rounded-full"></div>
                         </div>
                     </div>
                     <div class="h-64 md:h-80 relative">
                         <canvas id="visitsChart"></canvas>
                     </div>
                 </div>
                 
                 <!-- Statistiky stránek -->
                 <div class="glass-effect rounded-2xl md:rounded-3xl p-4 md:p-8 card-hover animate-on-scroll">
                     <h3 class="display-font text-lg md:text-2xl font-bold text-white tracking-tight mb-4 md:mb-8">Nejnavštěvovanější stránky</h3>
                     <div class="space-y-3 md:space-y-6">
                        <?php
                        $pageStats = $pdo->query("
                            SELECT page_name, COUNT(*) as visits 
                            FROM page_visits 
                            GROUP BY page_name 
                            ORDER BY visits DESC 
                            LIMIT 5
                        ")->fetchAll();
                        
                                                 foreach ($pageStats as $index => $page): ?>
                             <div class="flex items-center justify-between group hover:bg-white/5 p-3 md:p-4 rounded-xl md:rounded-2xl transition-all duration-300">
                                 <div class="flex items-center space-x-2 md:space-x-4">
                                     <div class="w-8 h-8 md:w-10 md:h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg md:rounded-xl flex items-center justify-center text-white font-bold text-xs md:text-sm">
                                         <?php echo $index + 1; ?>
                                     </div>
                                     <span class="text-white font-medium text-sm md:text-base"><?php echo htmlspecialchars($page['page_name']); ?></span>
                                 </div>
                                 <div class="flex items-center space-x-2 md:space-x-3">
                                     <div class="w-16 md:w-24 bg-gray-700 rounded-full h-2">
                                         <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-2 rounded-full" style="width: <?php echo min(100, ($page['visits'] / max(array_column($pageStats, 'visits'))) * 100); ?>%"></div>
                                     </div>
                                     <span class="text-primary-400 font-bold text-sm md:text-lg"><?php echo number_format($page['visits']); ?></span>
                                 </div>
                             </div>
                         <?php endforeach; ?>
                    </div>
                </div>
            </div>

                         <!-- Kontaktní formuláře -->
             <div class="glass-effect rounded-2xl md:rounded-3xl p-4 md:p-8 card-hover animate-on-scroll">
                 <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4 md:mb-8 space-y-3 md:space-y-0">
                     <h3 class="display-font text-lg md:text-2xl font-bold text-white tracking-tight">Kontaktní formuláře</h3>
                     <div class="flex items-center space-x-3 md:space-x-4">
                         <span class="bg-gradient-to-r from-primary-500 to-primary-600 text-white px-3 md:px-4 py-2 rounded-full text-xs md:text-sm font-semibold shadow-lg">
                             <?php echo count($contactSubmissions); ?> nových
                         </span>
                         <div class="w-2 h-2 md:w-3 md:h-3 bg-green-400 rounded-full animate-pulse"></div>
                     </div>
                 </div>
                 
                 <!-- Card View for All Devices -->
                 <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                     <?php foreach ($contactSubmissions as $submission): ?>
                         <div class="glass-effect rounded-2xl p-4 md:p-6 border border-white/10 hover:border-white/20 transition-all duration-300">
                             <!-- Header -->
                             <div class="flex items-center justify-between mb-4 md:mb-6">
                                 <div class="flex items-center space-x-3 md:space-x-4">
                                     <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white font-bold text-sm md:text-base">
                                         <?php echo strtoupper(substr($submission['name'], 0, 1)); ?>
                                     </div>
                                     <div>
                                         <div class="text-white font-semibold text-sm md:text-base"><?php echo htmlspecialchars($submission['name']); ?></div>
                                         <div class="text-white/60 text-xs md:text-sm"><?php echo date('d.m.Y H:i', strtotime($submission['submission_date'])); ?></div>
                                     </div>
                                 </div>
                                 <div class="text-right">
                                     <?php if ($submission['is_read']): ?>
                                         <span class="bg-green-500/20 text-green-400 px-2 md:px-3 py-1 md:py-2 rounded-full text-xs md:text-sm font-medium border border-green-500/30">
                                             ✓ Přečteno
                                         </span>
                                     <?php else: ?>
                                         <span class="bg-red-500/20 text-red-400 px-2 md:px-3 py-1 md:py-2 rounded-full text-xs md:text-sm font-medium border border-red-500/30 animate-pulse">
                                             ! Nové
                                         </span>
                                     <?php endif; ?>
                                 </div>
                             </div>

                             <!-- Contact Info -->
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-3 mb-4 md:mb-6">
                                 <div class="flex items-center space-x-2 md:space-x-3">
                                     <i class="fas fa-envelope text-primary-400 text-sm md:text-base w-4 md:w-5"></i>
                                     <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>" class="text-primary-400 text-sm md:text-base hover:text-primary-300 transition-colors duration-300">
                                         <?php echo htmlspecialchars($submission['email']); ?>
                                     </a>
                                 </div>
                                 <?php if ($submission['phone']): ?>
                                     <div class="flex items-center space-x-2 md:space-x-3">
                                         <i class="fas fa-phone text-primary-400 text-sm md:text-base w-4 md:w-5"></i>
                                         <a href="tel:<?php echo htmlspecialchars($submission['phone']); ?>" class="text-white/70 text-sm md:text-base hover:text-primary-400 transition-colors duration-300">
                                             <?php echo htmlspecialchars($submission['phone']); ?>
                                         </a>
                                     </div>
                                 <?php endif; ?>
                                 <div class="flex items-center space-x-2 md:space-x-3 md:col-span-2">
                                     <i class="fas fa-tag text-primary-400 text-sm md:text-base w-4 md:w-5"></i>
                                     <span class="bg-gradient-to-r from-primary-500/20 to-primary-600/20 text-primary-400 px-2 md:px-3 py-1 md:py-2 rounded-full text-xs md:text-sm font-medium border border-primary-500/30">
                                         <?php echo htmlspecialchars($submission['service']); ?>
                                     </span>
                                 </div>
                             </div>

                             <!-- Message -->
                             <div class="mb-4 md:mb-6">
                                 <div class="text-white/80 text-sm md:text-base leading-relaxed"><?php echo htmlspecialchars($submission['message']); ?></div>
                             </div>

                             <!-- Action Button -->
                             <?php if (!$submission['is_read']): ?>
                                 <form method="POST" class="text-center">
                                     <input type="hidden" name="action" value="mark_read">
                                     <input type="hidden" name="id" value="<?php echo $submission['id']; ?>">
                                     <button type="submit" class="bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white px-4 md:px-6 py-2 md:py-3 rounded-xl text-sm md:text-base font-medium transition-all duration-300 transform hover:scale-105 shadow-lg btn-premium w-full">
                                         <i class="fas fa-check mr-2"></i>Označit jako přečtené
                                     </button>
                                 </form>
                             <?php endif; ?>
                         </div>
                     <?php endforeach; ?>
                 </div>
            </div>
        </div>
    </div>

    <script>
        // Graf návštěvnosti
        const ctx = document.getElementById('visitsChart').getContext('2d');
        const visitsData = <?php echo json_encode($visitStats); ?>;
        
        const labels = visitsData.map(item => item.date);
        const visits = visitsData.map(item => item.visits);
        const uniqueVisitors = visitsData.map(item => item.unique_visitors);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Návštěvy',
                    data: visits,
                    borderColor: '#ed7514',
                    backgroundColor: 'rgba(237, 117, 22, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointBackgroundColor: '#ed7514',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }, {
                    label: 'Unikátní návštěvníci',
                    data: uniqueVisitors,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#ffffff',
                            font: {
                                size: 14,
                                weight: '600'
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)',
                            borderColor: 'rgba(255, 255, 255, 0.2)'
                        }
                    },
                                         y: {
                         beginAtZero: true,
                         ticks: {
                             color: '#9ca3af',
                             font: {
                                 size: 12
                             },
                             stepSize: 1,
                             callback: function(value) {
                                 return Math.round(value);
                             }
                         },
                         grid: {
                             color: 'rgba(255, 255, 255, 0.1)',
                             borderColor: 'rgba(255, 255, 255, 0.2)'
                         }
                     }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Loading Bar
        function initLoadingBar() {
            const loadingBar = document.getElementById('loadingBar');
            if (!loadingBar) return;
            
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(interval);
                    setTimeout(() => {
                        loadingBar.style.opacity = '0';
                        setTimeout(() => loadingBar.remove(), 300);
                    }, 500);
                }
                loadingBar.style.width = progress + '%';
            }, 100);
        }

        // Scroll Animations
        function initScrollAnimations() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        }

        // Stats Counter Animation
        function animateStats() {
            const statsNumbers = document.querySelectorAll('.stats-number');
            
            statsNumbers.forEach(stat => {
                const target = parseInt(stat.textContent.replace(/,/g, ''));
                const increment = target / 7;
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    stat.textContent = Math.floor(current).toLocaleString();
                }, 100);
            });
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', () => {
            initLoadingBar();
            initScrollAnimations();
            
            // Trigger stats animation when stats section is visible
            const statsSection = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4');
            if (statsSection) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            setTimeout(() => {
                                animateStats();
                            }, 300);
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.3
                });
                observer.observe(statsSection);
            }
        });

        // PWA Installation Logic
        let deferredPrompt;
        let installButton = null;

        // Create install button
        function createInstallButton() {
            if (installButton) return;
            
            installButton = document.createElement('button');
            installButton.className = 'fixed bottom-4 right-4 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white p-3 rounded-full shadow-2xl z-50 transition-all duration-300 transform hover:scale-110';
            installButton.innerHTML = '<i class="fas fa-download text-lg"></i>';
            installButton.title = 'Nainstalovat aplikaci';
            
            installButton.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        console.log('Uživatel souhlasil s instalací');
                    } else {
                        console.log('Uživatel odmítl instalaci');
                    }
                    deferredPrompt = null;
                    installButton.style.display = 'none';
                }
            });
            
            document.body.appendChild(installButton);
        }

        // Listen for beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            createInstallButton();
        });

        // Listen for appinstalled event
        window.addEventListener('appinstalled', () => {
            console.log('Aplikace byla úspěšně nainstalována');
            if (installButton) {
                installButton.style.display = 'none';
            }
        });

        // Check if app is already installed
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
            console.log('Aplikace je již nainstalována');
        }
    </script>
</body>
</html>
