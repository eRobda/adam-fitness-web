<?php
session_start();

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

// Funkce pro přihlášení
function login($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && $password === $user['password']) {
        // Aktualizace posledního přihlášení
        $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}

// Zpracování přihlášení
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Nesprávné přihlašovací údaje';
    }
}

// Pokud je uživatel přihlášen, přesměrovat na dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Adam Preis</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#ed7a1a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="AP Dashboard">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="AP Dashboard">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTgwIiBoZWlnaHQ9IjE4MCIgdmlld0JveD0iMCAwIDE4MCAxODAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxODAiIGhlaWdodD0iMTgwIiByeD0iMjIiIGZpbGw9IiNlZDdhMWEiLz4KPHN2ZyB4PSI0NSIgeT0iNDUiIHdpZHRoPSI5MCIgaGVpZ2h0PSI5MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJ3aGl0ZSI+CjxwYXRoIGQ9Ik0yMC41NyAxNC4wN0wxOC4xMiAxMS42MkwyMC41NyA5LjE3TDE4LjEyIDYuNzJMMTUuNjcgOS4xN0wxMy4yMiA2LjcyTDEwLjc3IDkuMTdMMy4yMiAxNi43MkwyLjUgMTcuNDRMMy4yMiAxOC4xNkwxMC43NyAyNS43MUwxMy4yMiAyMy4yNkwxNS42NyAyNS43MUwxOC4xMiAyMy4yNkwyMC41NyAyNS43MUwyMi4wMiAyNC4yNkwxOS41NyAyMS44MUwyMi4wMiAxOS4zNkwyMC41NyAxNy45MUwyMi4wMiAxNi40NkwyMC41NyAxNC4wN1oiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K">
    <link rel="apple-touch-icon" sizes="152x152" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUyIiBoZWlnaHQ9IjE1MiIgdmlld0JveD0iMCAwIDE1MiAxNTIiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxNTIiIGhlaWdodD0iMTUyIiByeD0iMTkiIGZpbGw9IiNlZDdhMWEiLz4KPHN2ZyB4PSIzOCIgeT0iMzgiIHdpZHRoPSI3NiIgaGVpZ2h0PSI3NiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJ3aGl0ZSI+CjxwYXRoIGQ9Ik0yMC41NyAxNC4wN0wxOC4xMiAxMS42MkwyMC41NyA5LjE3TDE4LjEyIDYuNzJMMTUuNjcgOS4xN0wxMy4yMiA2LjcyTDEwLjc3IDkuMTdMMy4yMiAxNi43MkwyLjUgMTcuNDRMMy4yMiAxOC4xNkwxMC43NyAyNS43MUwxMy4yMiAyMy4yNkwxNS42NyAyNS43MUwxOC4xMiAyMy4yNkwyMC41NyAyNS43MUwyMi4wMiAyNC4yNkwxOS41NyAyMS44MUwyMi4wMiAxOS4zNkwyMC41NyAxNy45MUwyMi4wMiAxNi40NkwxMC43NyA5LjE3TDMuMjIgMTYuNzJMMi41IDE3LjQ0TDMuMjIgMTguMTZMMTAuNzcgMjUuNzFMMTMuMjIgMjMuMjZMMTUuNjcgMjUuNzFMMTguMTIgMjMuMjZMMjAuNTcgMjUuNzFMMjIuMDIgMjQuMjZMMTkuNTcgMjEuODFMMjIuMDIgMTkuMzZMMjAuNTcgMTcuOTFMMjIuMDIgMTYuNDZMMjAuNTcgMTQuMDdaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4KPC9zdmc+Cg==">
    <link rel="apple-touch-icon" sizes="180x180" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTgwIiBoZWlnaHQ9IjE4MCIgdmlld0JveD0iMCAwIDE4MCAxODAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxODAiIGhlaWdodD0iMTgwIiByeD0iMjIiIGZpbGw9IiNlZDdhMWEiLz4KPHN2ZyB4PSI0NSIgeT0iNDUiIHdpZHRoPSI5MCIgaGVpZ2h0PSI5MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJ3aGl0ZSI+CjxwYXRoIGQ9Ik0yMC41NyAxNC4wN0wxOC4xMiAxMS42MkwyMC41NyA5LjE3TDE4LjEyIDYuNzJMMTUuNjcgOS4xN0wxMy4yMiA2LjcyTDEwLjc3IDkuMTdMMy4yMiAxNi43MkwyLjUgMTcuNDRMMy4yMiAxOC4xNkwxMC43NyAyNS43MUwxMy4yMiAyMy4yNkwxNS42NyAyNS43MUwxOC4xMiAyMy4yNkwyMC41NyAyNS43MUwyMi4wMiAyNC4yNkwxOS41NyAyMS44MUwyMi4wMiAxOS4zNkwyMC41NyAxNy45MUwyMi4wMiAxNi40NkwyMC41NyAxNC4wN1oiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fef7ee',
                            100: '#fdedd6',
                            200: '#fad7ac',
                            300: '#f6bb77',
                            400: '#f1953d',
                            500: '#ed7a1a',
                            600: '#de5f0f',
                            700: '#b8480f',
                            800: '#933a13',
                            900: '#773214',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#111827] font-['Inter'] overflow-x-hidden">


    <!-- Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo a nadpis -->
            <div class="text-center animate-on-scroll">
                <div class="mx-auto h-20 w-20 md:h-24 md:w-24 bg-gradient-to-br from-primary-500 to-primary-600 rounded-3xl flex items-center justify-center mb-6 shadow-2xl transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-dumbbell text-white text-3xl md:text-4xl"></i>
                </div>
                <h1 class="font-['Space_Grotesk'] text-4xl md:text-5xl font-black mb-3 tracking-tight text-white">
                    ADAM <span class="text-gradient">PREIS</span>
                </h1>
                <p class="text-xl text-white/70 font-medium">Admin Dashboard</p>
                                 <div id="loading-dots" class="mt-4 flex items-center justify-center space-x-2 hidden">
                     <div class="w-2 h-2 bg-primary-500 rounded-full animate-pulse"></div>
                     <div class="w-2 h-2 bg-primary-500 rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                     <div class="w-2 h-2 bg-primary-500 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
                 </div>
            </div>
            
            <!-- Login Form -->
                         <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-2xl border border-white/10 hover:border-white/20 transition-all duration-500">
                <?php if (isset($error)): ?>
                    <div class="bg-gradient-to-r from-red-500/20 to-red-600/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-2xl mb-6 animate-on-scroll">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="font-medium"><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="login">
                    
                                         <!-- Username Field -->
                     <div class="animate-on-scroll" style="animation-delay: 0.1s;">
                         <label for="username" class="block text-sm font-semibold text-white/90 mb-3">
                             <i class="fas fa-user mr-2 text-primary-400"></i>
                             Uživatelské jméno
                         </label>
                                                   <input type="text" id="username" name="username" required
                                 class="w-full px-4 py-4 bg-white/10 border border-white/20 text-white rounded-2xl transition-all duration-300 placeholder-white/40"
                                 placeholder="Zadejte uživatelské jméno">
                     </div>
                     
                     <!-- Password Field -->
                     <div class="animate-on-scroll" style="animation-delay: 0.2s;">
                         <label for="password" class="block text-sm font-semibold text-white/90 mb-3">
                             <i class="fas fa-lock mr-2 text-primary-400"></i>
                             Heslo
                         </label>
                                                   <input type="password" id="password" name="password" required
                                 class="w-full px-4 py-4 bg-white/10 border border-white/20 text-white rounded-2xl transition-all duration-300 placeholder-white/40"
                                 placeholder="Zadejte heslo">
                     </div>
                    
                    <!-- Login Button -->
                    <div class="animate-on-scroll" style="animation-delay: 0.3s;">
                                                 <button type="submit" 
                                 class="w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white py-4 px-6 rounded-2xl font-bold text-lg transition-all duration-300 shadow-2xl btn-premium relative overflow-hidden group">
                            <span class="relative z-10 flex items-center justify-center">
                                <i class="fas fa-sign-in-alt mr-3"></i>
                                Přihlásit se
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-primary-400 to-primary-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </button>
                    </div>
                </form>
                
                
            </div>

            <!-- Footer -->
            <div class="text-center animate-on-scroll" style="animation-delay: 0.5s;">
                <p class="text-white/40 text-sm">
                    <i class="fas fa-shield-alt mr-2"></i>
                    Bezpečné přihlášení do administrace
                </p>
            </div>
        </div>
    </div>

    <style>
        /* Custom CSS */
        .glass-effect {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #f1953d, #ed7a1a, #de5f0f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .btn-premium {
            position: relative;
            overflow: hidden;
        }
        
        .btn-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-premium:hover::before {
            left: 100%;
        }
        
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 640px) {
            .glass-effect {
                margin: 0 1rem;
            }
        }
        
        /* Remove default focus outline */
        input:focus {
            outline: none !important;
        }
        
        /* Custom focus styles */
        input:focus {
            box-shadow: 0 0 0 2px rgba(237, 122, 26, 0.5) !important;
            border-color: rgba(237, 122, 26, 0.5) !important;
        }
        
        /* Override browser autofill styles */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #3a3d4f inset !important;
            -webkit-text-fill-color: white !important;
            background-color: #3a3d4f !important;
            transition: background-color 5000s ease-in-out 0s;
        }
        
        /* Firefox autofill override */
        input:-moz-autofill {
            background-color: #3a3d4f !important;
            color: white !important;
        }
    </style>

    <script>
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationDelay = entry.target.dataset.delay || '0s';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });

        // Show loading dots when form is submitted
        document.querySelector('form').addEventListener('submit', function() {
            const loadingDots = document.getElementById('loading-dots');
            const submitButton = document.querySelector('button[type="submit"]');
            
            // Show loading dots
            loadingDots.classList.remove('hidden');
            
            // Disable submit button and change text
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="relative z-10 flex items-center justify-center"><i class="fas fa-spinner fa-spin mr-3"></i>Přihlašuji...</span>';
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
