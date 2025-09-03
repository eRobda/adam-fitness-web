<?php
// Sledování návštěv
require_once 'track_visit.php';

// Databázové připojení pro sledování
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
    
    // Sledování návštěvy hlavní stránky
    trackVisit($pdo, 'main_page');
    
    // Aktualizace statistik
    updateDailyStats($pdo);
    updateMonthlyStats($pdo);
    
} catch (Exception $e) {
    // Tichá chyba - nechceme přerušit zobrazení stránky
    error_log("Chyba při sledování návštěvy: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privátní Trenér - Zdokonalte sami sebe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            background: rgba(0, 0, 0, 0.3); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            transition: all 0.3s ease-in-out;
        }
        
        /* Fallback for devices that don't support backdrop-filter */
        @supports not (backdrop-filter: blur(20px)) {
            .glass-effect {
                background: rgba(0, 0, 0, 0.8);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
        }
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        
        /* Professional Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(237, 117, 20, 0.3); }
            50% { box-shadow: 0 0 40px rgba(237, 117, 20, 0.6); }
        }
        
        @keyframes slideInUp {
            from { transform: translateY(100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes slideInLeft {
            from { transform: translateX(-100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideInRight {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes fadeInScale {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes textReveal {
            from { clip-path: inset(0 100% 0 0); }
            to { clip-path: inset(0 0 0 0); }
        }
        
        @keyframes particleFloat {
            0% { transform: translateY(0px) translateX(0px); opacity: 1; }
            100% { transform: translateY(-100vh) translateX(100px); opacity: 0; }
        }
        
        /* Enhanced Card Hover Effects */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-style: preserve-3d;
            perspective: 1000px;
        }
        
        .card-hover:hover {
            transform: translateY(-12px) rotateX(5deg) rotateY(5deg);
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
        
        /* Floating Elements */
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-delay-1 {
            animation-delay: 1s;
        }
        
        .floating-delay-2 {
            animation-delay: 2s;
        }
        
        /* Particle System */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(237, 117, 20, 0.6);
            border-radius: 50%;
            animation: particleFloat 8s linear infinite;
        }
        
        /* Enhanced Button Effects */
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
        
        /* Text Reveal Animation */
        .text-reveal {
            overflow: hidden;
        }
        
        .text-reveal span {
            display: inline-block;
            animation: textReveal 1s ease-out forwards;
        }
        
        /* Gradient Background Animation */
        .gradient-bg {
            background: linear-gradient(-45deg, #111827, #1f2937, #374151, #111827);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
                 /* Parallax Effect - Desktop only */
         .parallax {
             transform: translateZ(0);
             will-change: transform;
         }
         
         /* Disable parallax on mobile devices */
         @media (max-width: 768px) {
             .parallax {
                 transform: none !important;
                 will-change: auto;
             }
         }
        
        /* Mobile menu styles */
        .mobile-menu { 
            transform: translateX(-100%);
            transition: all 0.3s ease-in-out;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 40;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            display: block;
            overflow: hidden;
        }
        
        /* Fallback for mobile menu on devices without backdrop-filter support */
        @supports not (backdrop-filter: blur(20px)) {
            .mobile-menu {
                background: rgba(17, 24, 39, 0.98);
                border-top: 1px solid rgba(255, 255, 255, 0.15);
            }
        }
        .mobile-menu.open { 
            transform: translateX(0);
            visibility: visible;
            opacity: 1;
        }
        
        /* Hide menu completely when closed */
        .mobile-menu:not(.open) {
            visibility: hidden;
            opacity: 0;
            pointer-events: none;
        }
        
        /* Ensure mobile menu is visible on mobile */
        @media (max-width: 768px) {
            .mobile-menu {
                display: block !important;
            }
        }
        
        /* Additional hiding for closed menu */
        .mobile-menu:not(.open) {
            clip-path: inset(0 100% 0 0);
        }
        
        .mobile-menu.open {
            clip-path: inset(0 0 0 0);
        }
        
        /* Touch-friendly button sizes */
        @media (max-width: 768px) {
            .touch-friendly { min-height: 48px; min-width: 48px; }
        }
        
        /* Optimize background image for mobile */
        @media (max-width: 768px) {
            .hero-bg { background-position: center 30% !important; }
        }
        
        /* Fallback for backdrop-blur on older devices */
        @supports not (backdrop-filter: blur(4px)) {
            .backdrop-blur-sm {
                background: rgba(255, 255, 255, 0.3) !important;
            }
        }
        
        /* Scroll-triggered animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Enhanced Hero Section */
        .hero-content {
            animation: fadeInScale 1.5s ease-out;
        }
        
        .hero-title span {
            display: inline-block;
            animation: slideInUp 1s ease-out forwards;
        }
        
        .hero-title span:nth-child(2) {
            animation-delay: 0.3s;
        }
        
        .hero-subtitle {
            animation: slideInUp 1s ease-out 0.6s both;
        }
        
        .hero-buttons {
            animation: slideInUp 1s ease-out 0.9s both;
        }
        
        /* Stats Animation */
        .stats-number {
            display: inline-block;
            animation: fadeInScale 0.8s ease-out forwards;
        }
        
        .stats-number:nth-child(1) { animation-delay: 0.2s; }
        .stats-number:nth-child(2) { animation-delay: 0.4s; }
        .stats-number:nth-child(3) { animation-delay: 0.6s; }
        
        /* Service Cards Enhanced */
        .service-card {
            position: relative;
            overflow: hidden;
        }
        
        .service-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(237, 117, 20, 0.05) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .service-card:hover::after {
            opacity: 1;
        }
        
        /* Testimonial Cards */
        .testimonial-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .testimonial-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        }
        
        /* Contact Form Enhanced */
        .contact-input {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .contact-input:focus {
            border-color: #ed7514;
            box-shadow: 0 0 20px rgba(237, 117, 20, 0.2);
            transform: translateY(-2px);
        }
        
        /* Footer Enhanced */
        .footer-link {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .footer-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #ed7514, #f1943a);
            transition: width 0.3s ease;
        }
        
        .footer-link:hover::after {
            width: 100%;
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
    </style>
</head>
<body class="font-inter">
    <!-- Loading Bar -->
    <div class="loading-bar" id="loadingBar"></div>
    
    <!-- Navigation -->
    <nav class="fixed top-0 w-full glass-effect z-50" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-dumbbell text-white text-lg"></i>
                    </div>
                                         <span class="display-font text-xl font-bold text-white tracking-tight">ADAM PREIS</span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#home" class="text-white/80 hover:text-white transition-colors duration-300 font-medium">Domů</a>
                    <a href="#about" class="text-white/80 hover:text-white transition-colors duration-300 font-medium">O mně</a>
                    <a href="#services" class="text-white/80 hover:text-white transition-colors duration-300 font-medium">Služby</a>
                    <a href="#contact" class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-2 rounded-full transition-all duration-300 font-medium">Kontakt</a>
                </div>
                <div class="md:hidden">
                    <button id="mobileMenuBtn" class="text-white hover:text-primary-300 touch-friendly p-2">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="mobile-menu md:hidden">
            <div class="px-4 py-6 space-y-4">
                <a href="#home" class="block text-white/80 hover:text-primary-400 transition-colors duration-300 font-medium py-3 text-lg">Domů</a>
                <a href="#about" class="block text-white/80 hover:text-primary-400 transition-colors duration-300 font-medium py-3 text-lg">O mně</a>
                <a href="#services" class="block text-white/80 hover:text-primary-400 transition-colors duration-300 font-medium py-3 text-lg">Služby</a>
                <a href="#contact" class="block bg-primary-500 text-white px-6 py-3 rounded-full transition-all duration-300 font-medium text-lg">Kontakt</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="bg-cover bg-no-repeat min-h-screen flex items-end justify-center relative pb-32 hero-bg parallax" style="background-image: url('IMG_9594.png'); background-position: center 40%;">
        <!-- Particle System -->
        <div class="particles" id="particles"></div>
        
        <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-black/40 to-black/60"></div>
        
        <!-- Floating Elements -->
        <div class="absolute top-20 left-10 w-20 h-20 bg-primary-500/20 rounded-full blur-xl floating"></div>
        <div class="absolute top-40 right-20 w-16 h-16 bg-primary-400/20 rounded-full blur-lg floating-delay-1"></div>
        <div class="absolute bottom-40 left-20 w-24 h-24 bg-primary-300/20 rounded-full blur-xl floating-delay-2"></div>
        
        <div class="relative z-10 text-center text-white px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto hero-content">
            <h1 class="display-font text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-black mb-8 tracking-tight leading-tight hero-title">
                <span class="text-gradient text-reveal">ZDOKONALTE</span><br>
                <span class="text-white text-reveal">SAMI SEBE</span>
            </h1>
            <p class="text-lg sm:text-xl lg:text-2xl xl:text-3xl mb-12 text-gray-200 max-w-4xl mx-auto font-light leading-relaxed hero-subtitle">
                Osobní trenér s <span class="text-primary-300 font-semibold">individuálním přístupem</span>. Dosáhněte svých fitness cílů s profesionálním vedením.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center items-center hero-buttons">
                <a href="#contact" class="group bg-primary-500 hover:bg-primary-600 text-white px-6 sm:px-10 py-4 sm:py-5 rounded-2xl font-bold text-base sm:text-lg transition-all duration-300 transform hover:scale-105 flex items-center space-x-3 shadow-2xl touch-friendly w-full sm:w-auto justify-center btn-premium">
                    <i class="fas fa-calendar-check text-lg sm:text-xl"></i>
                    <span>Rezervovat konzultaci</span>
                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform duration-300"></i>
                </a>
                <a href="#about" class="glass-effect text-white px-6 sm:px-10 py-4 sm:py-5 rounded-2xl font-bold text-base sm:text-lg transition-all duration-300 transform hover:scale-105 flex items-center space-x-3 touch-friendly w-full sm:w-auto justify-center btn-premium">
                    <i class="fas fa-play text-lg sm:text-xl"></i>
                    <span>Zjistit více</span>
                </a>
            </div>
        </div>
        <div class="absolute bottom-8 left-0 right-0 flex justify-center animate-bounce">
            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center touch-friendly floating">
                <i class="fas fa-chevron-down text-white text-xl"></i>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 sm:py-24 bg-gradient-to-br from-gray-900 to-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 sm:mb-20 animate-on-scroll">
                                 <span class="inline-block bg-primary-500/20 text-primary-400 px-4 py-2 rounded-full text-sm font-semibold mb-6">
                     O MNĚ
                 </span>
                <h2 class="display-font text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-black mb-6 tracking-tight text-white">
                    PROFESIONÁLNÍ<br><span class="text-gradient">PŘÍSTUP</span>
                </h2>
                <p class="text-lg sm:text-xl text-gray-300 max-w-3xl mx-auto">K vašemu fitness cíli s individuálním přístupem a výsledky</p>
            </div>
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div class="space-y-8 sm:space-y-10 animate-on-scroll">
                    <div>
                        <h3 class="display-font text-2xl sm:text-3xl font-bold text-white mb-6 tracking-tight">Certifikovaný osobní trenér</h3>
                        <p class="text-base sm:text-lg text-gray-300 leading-relaxed mb-6">
                            Ahoj, jmenuji se <span class="text-primary-400 font-semibold">Adam</span> a věnuji se fitness několik let. Věnoval jsem se převážně <span class="text-primary-400 font-semibold">silovému trojboji</span>, ale nyní přecházím spíše ke <span class="text-primary-400 font-semibold">kulturistice</span>. Nicméně mě zajímá fitness celkově.
                        </p>
                        
                        <div class="bg-gray-800/50 rounded-2xl p-6 border border-gray-700/50 backdrop-blur-sm">
                            <h4 class="text-primary-400 font-semibold text-lg mb-4 flex items-center">
                                <i class="fas fa-trophy text-primary-400 mr-3"></i>
                                Moje úspěchy
                            </h4>
                            <p class="text-base sm:text-lg text-gray-300 leading-relaxed mb-4">
                                K mým největším úspěchům patří <span class="text-primary-400 font-semibold">druhé místo na Mistrovství České Republiky v benchi</span>, které se mi podařilo vybojovat <span class="text-primary-400 font-semibold">třikrát</span>. Mám za sebou přes <span class="text-primary-400 font-semibold">10 závodů</span> a také nějaké úspěchy.
                            </p>
                        </div>
                        
                        <div class="bg-gray-800/50 rounded-2xl p-6 border border-gray-700/50 backdrop-blur-sm mt-6">
                            <h4 class="text-primary-400 font-semibold text-lg mb-4 flex items-center">
                                <i class="fas fa-heart text-primary-400 mr-3"></i>
                                Proč jsem trenérem
                            </h4>
                            <p class="text-base sm:text-lg text-gray-300 leading-relaxed">
                                Vždy jsem chtěl pomáhat ostatním v dosažení jejich cílů a předat jim své znalosti, proto jsem se stal <span class="text-primary-400 font-semibold">fitness trenérem</span>. Sám jsem ale pod vedením profesionálního trenéra.
                            </p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4 sm:gap-8">
                        <div class="text-center">
                            <div class="display-font text-3xl sm:text-4xl font-black text-primary-400 mb-3 stats-number">30+</div>
                            <div class="text-gray-300 font-medium text-sm sm:text-base">Spokojených klientů</div>
                        </div>
                        <div class="text-center">
                            <div class="display-font text-3xl sm:text-4xl font-black text-primary-400 mb-3 stats-number">6+</div>
                            <div class="text-gray-300 font-medium text-sm sm:text-base">Let zkušeností</div>
                        </div>
                        <div class="text-center">
                            <div class="display-font text-3xl sm:text-4xl font-black text-primary-400 mb-3 stats-number">100%</div>
                            <div class="text-gray-300 font-medium text-sm sm:text-base">Individuální přístup</div>
                        </div>
                    </div>


                </div>
                <div class="space-y-6">
                    <div class="bg-gradient-to-br from-gray-800 to-gray-700 rounded-3xl relative overflow-hidden border border-gray-600 animate-on-scroll">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/10 to-transparent"></div>
                        <div class="relative w-full h-full">
                            <img src="IMG_9528.JPG" alt="Osobní trenér" class="w-full h-full object-cover rounded-3xl">
                        </div>
                    </div>
                    
                    <div class="bg-gray-800 rounded-3xl p-6 sm:p-8 shadow-xl border border-gray-700 card-hover animate-on-scroll">
                        <h4 class="display-font text-lg sm:text-xl font-bold text-white mb-6 tracking-tight">Odbornost</h4>
                        <ul class="space-y-4">
                            <li class="flex items-center space-x-4">
                                <div class="w-8 h-8 bg-primary-500/20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-certificate text-primary-400 text-sm"></i>
                                </div>
                                <span class="text-gray-200 font-medium text-sm sm:text-base">Osobní trenér</span>
                            </li>
                            <li class="flex items-center space-x-4">
                                <div class="w-8 h-8 bg-primary-500/20 rounded-full flex items-center justify-center floating-delay-1">
                                    <i class="fas fa-certificate text-primary-400 text-sm"></i>
                                </div>
                                <span class="text-gray-200 font-medium text-sm sm:text-base">Výživový poradce</span>
                            </li>
                            <li class="flex items-center space-x-4">
                                <div class="w-8 h-8 bg-primary-500/20 rounded-full flex items-center justify-center floating-delay-2">
                                    <i class="fas fa-certificate text-primary-400 text-sm"></i>
                                </div>
                                <span class="text-gray-200 font-medium text-sm sm:text-base">Silový trénink</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-16 sm:py-24 bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 sm:mb-20 animate-on-scroll">
                                 <span class="inline-block bg-primary-500/20 text-primary-400 px-4 py-2 rounded-full text-sm font-semibold mb-6">
                     SLUŽBY
                 </span>
                <h2 class="display-font text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-black mb-6 tracking-tight text-white">
                    MOJE <span class="text-gradient">SLUŽBY</span>
                </h2>
                <p class="text-lg sm:text-xl text-gray-300 max-w-3xl mx-auto">Komplexní fitness řešení pro každého s profesionálním přístupem</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <div class="bg-gray-800 rounded-3xl shadow-2xl p-6 sm:p-10 card-hover border border-gray-700 service-card animate-on-scroll flex flex-col">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mb-6 sm:mb-8">
                        <i class="fas fa-dumbbell text-white text-2xl sm:text-3xl"></i>
                    </div>
                    <h3 class="display-font text-xl sm:text-2xl font-bold text-white mb-6 tracking-tight">Osobní trénink</h3>
                    <p class="text-gray-300 mb-4 leading-relaxed text-sm sm:text-base flex-grow">Individuální trénink pod mým vedením, kde se zaměříme na tvé cíle. Zkontrolujeme tvoji techniku a zoptimalizujeme trénink přímo pro tebe.</p>
                    <div class="bg-primary-500/20 rounded-2xl p-4 mb-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary-400">300,-</div>
                            <div class="text-sm text-gray-300">Kč</div>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Individuální přístup</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Kontrola techniky</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Fitness centrum</span>
                        </li>
                    </ul>
                </div>
                
                <div class="bg-gray-800 rounded-3xl shadow-2xl p-6 sm:p-10 card-hover border border-gray-700 service-card animate-on-scroll flex flex-col">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mb-6 sm:mb-8">
                        <i class="fas fa-clipboard-list text-white text-2xl sm:text-3xl"></i>
                    </div>
                    <h3 class="display-font text-xl sm:text-2xl font-bold text-white mb-6 tracking-tight">Tréninkový plán</h3>
                    <p class="text-gray-300 mb-4 leading-relaxed text-sm sm:text-base flex-grow">Tréninkový plán bude vytvořený přímo pro tebe, podle tvých potřeb a cílů. Bude nastavený tak, aby tě především bavil a dovedl k nejlepším výsledkům.</p>
                    <div class="bg-primary-500/20 rounded-2xl p-4 mb-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary-400">1000,-</div>
                            <div class="text-sm text-gray-300">Kč</div>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Na míru</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Individuální délka</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Zábavný trénink</span>
                        </li>
                    </ul>
                </div>
                
                <div class="bg-gray-800 rounded-3xl shadow-2xl p-6 sm:p-10 card-hover border border-gray-700 service-card animate-on-scroll flex flex-col">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mb-6 sm:mb-8">
                        <i class="fas fa-apple-alt text-white text-2xl sm:text-3xl"></i>
                    </div>
                    <h3 class="display-font text-xl sm:text-2xl font-bold text-white mb-6 tracking-tight">Jídelní plán</h3>
                    <p class="text-gray-300 mb-4 leading-relaxed text-sm sm:text-base flex-grow">Sestavím ti jídelní plán na míru, přizpůsobený tobě. Zaměřím se na tvůj cíl a přidám doporučení k suplementaci.</p>
                    <div class="bg-primary-500/20 rounded-2xl p-4 mb-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary-400">1000,-</div>
                            <div class="text-sm text-gray-300">Kč</div>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Na míru</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Týdenní úpravy</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Suplementace</span>
                        </li>
                    </ul>
                </div>
                
                <div class="bg-gray-800 rounded-3xl shadow-2xl p-6 sm:p-10 card-hover border border-gray-700 service-card animate-on-scroll flex flex-col">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mb-6 sm:mb-8">
                        <i class="fas fa-users text-white text-2xl sm:text-3xl"></i>
                    </div>
                    <h3 class="display-font text-xl sm:text-2xl font-bold text-white mb-6 tracking-tight">Coaching</h3>
                    <p class="text-gray-300 mb-4 leading-relaxed text-sm sm:text-base flex-grow">Spolupráce, ve které ti sestavím tréninkový plán a jídelníček přizpůsobený tvému cíli. Každotýdenní kontroly a konzultace.</p>
                    <div class="bg-primary-500/20 rounded-2xl p-4 mb-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary-400">1500,-</div>
                            <div class="text-sm text-gray-300">Kč</div>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Kompletní plán</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">24/7 podpora</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Týdenní kontroly</span>
                        </li>
                    </ul>
                </div>
                
                <div class="bg-gray-800 rounded-3xl shadow-2xl p-6 sm:p-10 card-hover border border-gray-700 service-card animate-on-scroll flex flex-col">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mb-6 sm:mb-8">
                        <i class="fas fa-trophy text-white text-2xl sm:text-3xl"></i>
                    </div>
                    <h3 class="display-font text-xl sm:text-2xl font-bold text-white mb-6 tracking-tight">Příprava na závody</h3>
                    <p class="text-gray-300 mb-4 leading-relaxed text-sm sm:text-base flex-grow">Připravím tě na závody v silovém trojboji. Příprava zahrnuje tréninkový plán, jídelníček i osobní tréninky.</p>
                    <div class="bg-primary-500/20 rounded-2xl p-4 mb-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary-400">1500,-</div>
                            <div class="text-sm text-gray-300">Kč</div>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Silový trojboj</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Kompletní příprava</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">24/7 k dispozici</span>
                        </li>
                    </ul>
                </div>
                
                <div class="bg-gray-800 rounded-3xl shadow-2xl p-6 sm:p-10 card-hover border border-gray-700 service-card animate-on-scroll flex flex-col">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mb-6 sm:mb-8">
                        <i class="fas fa-comments text-white text-2xl sm:text-3xl"></i>
                    </div>
                    <h3 class="display-font text-xl sm:text-2xl font-bold text-white mb-6 tracking-tight">Konzultace</h3>
                    <p class="text-gray-300 mb-4 leading-relaxed text-sm sm:text-base flex-grow">Potřebuješ poradit ať už ve výživě nebo tréninku? Můžeme se setkat a nebo vše vyřešit online.</p>
                    <div class="bg-primary-500/20 rounded-2xl p-4 mb-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary-400">250,-</div>
                            <div class="text-sm text-gray-300">Kč</div>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Výživa i trénink</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Osobně i online</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-primary-500/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-primary-400 text-xs"></i>
                            </div>
                            <span class="text-gray-200 font-medium text-sm sm:text-base">Individuální čas</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-16 sm:py-24 bg-gradient-to-br from-gray-800 to-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 sm:mb-20 animate-on-scroll">
                                 <span class="inline-block bg-primary-500/20 text-primary-400 px-4 py-2 rounded-full text-sm font-semibold mb-6">
                     REFERENCE
                 </span>
                <h2 class="display-font text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-black mb-6 tracking-tight text-white">
                    CO ŘÍKAJÍ <span class="text-gradient">KLIENTI</span>
                </h2>
                <p class="text-lg sm:text-xl text-gray-300 max-w-3xl mx-auto">Skutečné výsledky a spokojenost mých klientů</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <div class="bg-gray-800 rounded-3xl shadow-2xl p-6 sm:p-10 card-hover border border-gray-700 testimonial-card animate-on-scroll">
                    <div class="mb-6 sm:mb-8">
                        <div class="flex text-primary-400 mb-4">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="text-gray-300 italic text-base sm:text-lg leading-relaxed">"Díky osobnímu přístupu jsem dosáhl svých cílů rychleji, než jsem čekal. Profesionální a motivující přístup!"</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mr-4">
                            <i class="fas fa-user text-white text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h4 class="display-font font-bold text-white text-base sm:text-lg">Petr Novák</h4>
                            <span class="text-gray-400 font-medium text-sm sm:text-base">Klient 2 roky</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-800 rounded-3xl shadow-2xl p-6 sm:p-10 card-hover border border-gray-700 testimonial-card animate-on-scroll">
                    <div class="mb-6 sm:mb-8">
                        <div class="flex text-primary-400 mb-4">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="text-gray-300 italic text-base sm:text-lg leading-relaxed">"Konečně jsem našla trenéra, který rozumí mým potřebám. Výsledky jsou viditelné už po prvním měsíci!"</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mr-4">
                            <i class="fas fa-user text-white text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h4 class="display-font font-bold text-white text-base sm:text-lg">Marie Svobodová</h4>
                            <span class="text-gray-400 font-medium text-sm sm:text-base">Klientka 1 rok</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-800 rounded-3xl shadow-2xl p-6 sm:p-10 card-hover border border-gray-700 testimonial-card animate-on-scroll">
                    <div class="mb-6 sm:mb-8">
                        <div class="flex text-primary-400 mb-4">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="text-gray-300 italic text-base sm:text-lg leading-relaxed">"Online koučování mi perfektně vyhovuje. Flexibilní rozvrh a stejně kvalitní výsledky jako při osobních trénincích."</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mr-4">
                            <i class="fas fa-user text-white text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h4 class="display-font font-bold text-white text-base sm:text-lg">Jan Dvořák</h4>
                            <span class="text-gray-400 font-medium text-sm sm:text-base">Online klient</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-16 sm:py-24 bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 sm:mb-20 animate-on-scroll">
                                 <span class="inline-block bg-primary-500/20 text-primary-400 px-4 py-2 rounded-full text-sm font-semibold mb-6">
                     KONTAKT
                 </span>
                <h2 class="display-font text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-black mb-6 tracking-tight text-white">
                    KONTAKTUJTE <span class="text-gradient">MĚ</span>
                </h2>
                <p class="text-lg sm:text-xl text-gray-300 max-w-3xl mx-auto">Začněte svou fitness cestu ještě dnes s profesionálním vedením</p>
            </div>
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16">
                <div class="space-y-8 sm:space-y-10 animate-on-scroll">
                    <div class="flex items-start space-x-4 sm:space-x-6">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-phone text-white text-lg sm:text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="display-font font-bold text-white text-lg sm:text-xl mb-2">Telefon</h4>
                            <p class="text-gray-300 text-base sm:text-lg">+420 778 704 560</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 sm:space-x-6">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-white text-lg sm:text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="display-font font-bold text-white text-lg sm:text-xl mb-2">Email</h4>
                            <p class="text-gray-300 text-base sm:text-lg">preisadam@gmail.com</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 sm:space-x-6">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-white text-lg sm:text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="display-font font-bold text-white text-lg sm:text-xl mb-2">Lokalita</h4>
                            <p class="text-gray-300 text-base sm:text-lg">Jaroměř, Česká republika</p>
                        </div>
                    </div>
                    

                </div>
                
                <div class="bg-gray-800 rounded-3xl p-6 sm:px-10 shadow-2xl border border-gray-700 animate-on-scroll">
                    <form id="contactForm" action="process_contact.php" method="POST" class="space-y-4 sm:space-y-6">
                        <div>
                            <input type="text" id="name" name="name" placeholder="Vaše jméno" required 
                                   class="w-full px-4 sm:px-6 py-3 sm:py-4 border border-gray-600 bg-gray-700 text-white placeholder-gray-400 rounded-2xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base sm:text-lg transition-all duration-300 touch-friendly contact-input">
                        </div>
                        <div>
                            <input type="email" id="email" name="email" placeholder="Váš email" required 
                                   class="w-full px-4 sm:px-6 py-3 sm:py-4 border border-gray-600 bg-gray-700 text-white placeholder-gray-400 rounded-2xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base sm:text-lg transition-all duration-300 touch-friendly contact-input">
                        </div>
                        <div>
                            <input type="tel" id="phone" name="phone" placeholder="Váš telefon" 
                                   class="w-full px-4 sm:px-6 py-3 sm:py-4 border border-gray-600 bg-gray-700 text-white placeholder-gray-400 rounded-2xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base sm:text-lg transition-all duration-300 touch-friendly contact-input">
                        </div>
                        <div>
                            <select id="service" name="service" required 
                                    class="w-full px-4 sm:px-6 py-3 sm:py-4 border border-gray-600 bg-gray-700 text-white rounded-2xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base sm:text-lg transition-all duration-300 touch-friendly contact-input">
                                <option value="" class="bg-gray-700 text-white">Vyberte službu</option>
                                <option value="personal" class="bg-gray-700 text-white">Osobní trénink - 300,- Kč</option>
                                <option value="training_plan" class="bg-gray-700 text-white">Tréninkový plán - 1000,- Kč</option>
                                <option value="meal_plan" class="bg-gray-700 text-white">Jídelní plán - 1000,- Kč</option>
                                <option value="coaching" class="bg-gray-700 text-white">Coaching - 1500,- Kč</option>
                                <option value="competition" class="bg-gray-700 text-white">Příprava na závody - 1500,- Kč</option>
                                <option value="consultation" class="bg-gray-700 text-white">Konzultace - 250,- Kč</option>
                            </select>
                        </div>
                        <div>
                            <textarea id="message" name="message" placeholder="Vaše zpráva" rows="4" 
                                      class="w-full px-4 sm:px-6 py-3 sm:py-4 border border-gray-600 bg-gray-700 text-white placeholder-gray-400 rounded-2xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base sm:text-lg transition-all duration-300 touch-friendly contact-input"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white py-4 sm:py-5 rounded-2xl font-bold text-base sm:text-lg transition-all duration-300 transform hover:scale-105 flex items-center justify-center space-x-3 shadow-2xl touch-friendly btn-premium">
                            <i class="fas fa-paper-plane text-lg sm:text-xl"></i>
                            <span>Odeslat zprávu</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black text-white py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 sm:gap-12 mb-8 sm:mb-12">
                <div class="animate-on-scroll">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-primary-500 rounded-xl flex items-center justify-center">
                            <i class="fas fa-dumbbell text-white text-lg sm:text-xl"></i>
                        </div>
                                                 <span class="display-font text-xl sm:text-2xl font-bold">ADAM PREIS</span>
                    </div>
                    <p class="text-gray-400 mb-6 leading-relaxed text-sm sm:text-base">Osobní trenér s individuálním přístupem ke každému klientovi.</p>
                    <div class="flex space-x-3 sm:space-x-4">
                        <a href="https://www.instagram.com/adam.preis/" target="_blank" rel="noopener noreferrer" class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-900 rounded-xl flex items-center justify-center text-gray-400 hover:text-primary-400 hover:bg-gray-800 transition-all duration-300 touch-friendly btn-premium">
                            <i class="fab fa-instagram text-lg sm:text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <div class="animate-on-scroll">
                    <h4 class="display-font text-lg sm:text-xl font-bold mb-6">Rychlé odkazy</h4>
                    <ul class="space-y-3">
                        <li><a href="#home" class="text-gray-400 hover:text-white transition-colors duration-300 font-medium text-sm sm:text-base footer-link">Domů</a></li>
                        <li><a href="#about" class="text-gray-400 hover:text-white transition-colors duration-300 font-medium text-sm sm:text-base footer-link">O mně</a></li>
                        <li><a href="#services" class="text-gray-400 hover:text-white transition-colors duration-300 font-medium text-sm sm:text-base footer-link">Služby</a></li>
                        <li><a href="#contact" class="text-gray-400 hover:text-white transition-colors duration-300 font-medium text-sm sm:text-base footer-link">Kontakt</a></li>
                    </ul>
                </div>
                
                <div class="animate-on-scroll">
                    <h4 class="display-font text-lg sm:text-xl font-bold mb-6">Služby</h4>
                    <ul class="space-y-3">
                        <li><a href="#services" class="text-gray-400 hover:text-white transition-colors duration-300 font-medium text-sm sm:text-base footer-link">Osobní trénink</a></li>
                        <li><a href="#services" class="text-gray-400 hover:text-white transition-colors duration-300 font-medium text-sm sm:text-base footer-link">Tréninkový plán</a></li>
                        <li><a href="#services" class="text-gray-400 hover:text-white transition-colors duration-300 font-medium text-sm sm:text-base footer-link">Jídelní plán</a></li>
                        <li><a href="#services" class="text-gray-400 hover:text-white transition-colors duration-300 font-medium text-sm sm:text-base footer-link">Coaching</a></li>
                        <li><a href="#services" class="text-gray-400 hover:text-white transition-colors duration-300 font-medium text-sm sm:text-base footer-link">Příprava na závody</a></li>
                        <li><a href="#services" class="text-gray-400 hover:text-white transition-colors duration-300 font-medium text-sm sm:text-base footer-link">Konzultace</a></li>
                    </ul>
                </div>
                
                <div class="animate-on-scroll">
                    <h4 class="display-font text-lg sm:text-xl font-bold mb-6">Kontakt</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center space-x-3">
                            <i class="fas fa-phone text-primary-400"></i>
                            <span class="text-gray-400 font-medium text-sm sm:text-base">+420 778 704 560</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fas fa-envelope text-primary-400 floating-delay-1"></i>
                            <span class="text-gray-400 font-medium text-sm sm:text-base">preisadam@gmail.com</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fas fa-map-marker-alt text-primary-400 floating-delay-2"></i>
                            <span class="text-gray-400 font-medium text-sm sm:text-base">Jaroměř, ČR</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-6 sm:pt-8 text-center animate-on-scroll">
                                 <p class="text-gray-400 font-medium text-sm sm:text-base">&copy; 2025 Adam Preis. Všechna práva vyhrazena.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        let isMenuOpen = false;

        // Add both click and touch events
        const toggleMenu = function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            isMenuOpen = !isMenuOpen;
            if (isMenuOpen) {
                mobileMenu.classList.add('open');
                mobileMenuBtn.innerHTML = '<i class="fas fa-times text-xl"></i>';
                console.log('Menu opened');
            } else {
                mobileMenu.classList.remove('open');
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars text-xl"></i>';
                console.log('Menu closed');
            }
        };

        // Add multiple event listeners for better mobile support
        mobileMenuBtn.addEventListener('click', toggleMenu);
        mobileMenuBtn.addEventListener('touchend', toggleMenu);
        mobileMenuBtn.addEventListener('touchstart', function(e) {
            e.preventDefault();
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('#mobileMenu a').forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.classList.remove('open');
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars text-xl"></i>';
                isMenuOpen = false;
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileMenu.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
                mobileMenu.classList.remove('open');
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars text-xl"></i>';
                isMenuOpen = false;
            }
        });

        // Debug: Check if elements exist
        console.log('Mobile menu button:', mobileMenuBtn);
        console.log('Mobile menu:', mobileMenu);

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Form submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Zobrazení loading stavu
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Odesílám...';
            submitBtn.disabled = true;
            
            fetch('process_contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Úspěch
                    alert(data.message);
                    this.reset();
                } else {
                    // Chyba
                    alert('Chyba: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Došlo k chybě při odesílání. Zkuste to prosím znovu.');
            })
            .finally(() => {
                // Obnovení původního stavu tlačítka
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Navbar background on scroll - keep glass effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            const scrollY = window.scrollY;
            
            // Always keep the glass effect, just adjust opacity slightly for better readability
            if (scrollY > 50) {
                // Slightly more opaque when scrolled for better text readability
                navbar.style.background = 'rgba(255, 255, 255, 0.18)';
                navbar.style.backdropFilter = 'blur(25px)';
                navbar.style.borderBottom = '1px solid rgba(255, 255, 255, 0.3)';
            } else {
                // Same glass effect when at top, just slightly more transparent
                navbar.style.background = 'rgba(255, 255, 255, 0.15)';
                navbar.style.backdropFilter = 'blur(20px)';
                navbar.style.borderBottom = '1px solid rgba(255, 255, 255, 0.25)';
            }
        });
        
        // Set initial navbar style on page load
        window.addEventListener('load', function() {
            const navbar = document.getElementById('navbar');
            navbar.style.background = 'rgba(255, 255, 255, 0.15)';
            navbar.style.backdropFilter = 'blur(20px)';
            navbar.style.borderBottom = '1px solid rgba(255, 255, 255, 0.25)';
        });

        // Touch-friendly interactions for mobile
        if ('ontouchstart' in window) {
            document.querySelectorAll('.touch-friendly').forEach(element => {
                element.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.95)';
                });
                
                element.addEventListener('touchend', function() {
                    this.style.transform = '';
                });
            });
        }
        
        // Professional Animations System
        class AnimationSystem {
            constructor() {
                this.init();
            }
            
            init() {
                this.createParticles();
                this.initScrollAnimations();
                this.initParallax();
                this.initLoadingBar();
                this.initFloatingElements();
            }
            
            // Particle System
            createParticles() {
                const particlesContainer = document.getElementById('particles');
                if (!particlesContainer) return;
                
                const particleCount = 50;
                
                for (let i = 0; i < particleCount; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.animationDelay = Math.random() * 8 + 's';
                    particle.style.animationDuration = (Math.random() * 4 + 4) + 's';
                    particlesContainer.appendChild(particle);
                }
            }
            
            // Scroll-triggered animations
            initScrollAnimations() {
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
            
                         // Parallax Effect - Desktop only, milder effect
             initParallax() {
                 // Only enable parallax on desktop devices
                 if (window.innerWidth <= 768) return;
                 
                 window.addEventListener('scroll', () => {
                     const scrolled = window.pageYOffset;
                     const parallaxElements = document.querySelectorAll('.parallax');
                     
                     parallaxElements.forEach(element => {
                         // Much milder parallax effect (reduced from 0.5 to 0.15)
                         const speed = 0.15;
                         const yPos = -(scrolled * speed);
                         element.style.transform = `translateY(${yPos}px)`;
                     });
                 });
             }
            
            // Loading Bar
            initLoadingBar() {
                const loadingBar = document.getElementById('loadingBar');
                if (!loadingBar) return;
                
                // Simulate loading progress
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
            
            // Floating Elements Animation
            initFloatingElements() {
                const floatingElements = document.querySelectorAll('.floating, .floating-delay-1, .floating-delay-2');
                
                floatingElements.forEach((element, index) => {
                    element.style.animationDelay = `${index * 0.5}s`;
                });
            }
        }
        
        // Initialize Animation System
        document.addEventListener('DOMContentLoaded', () => {
            new AnimationSystem();
        });
        
        // Enhanced hover effects for cards
        document.querySelectorAll('.card-hover').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-12px) rotateX(5deg) rotateY(5deg)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) rotateX(0) rotateY(0)';
            });
        });
        
        // Smooth reveal for text elements
        const textElements = document.querySelectorAll('.text-reveal span');
        textElements.forEach((span, index) => {
            span.style.animationDelay = `${index * 0.2}s`;
        });
        
        // Enhanced button interactions
        document.querySelectorAll('.btn-premium').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px) scale(1.02)';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
                 // Stats counter animation
         const animateStats = () => {
             const statsNumbers = document.querySelectorAll('.stats-number');
             
             statsNumbers.forEach(stat => {
                 const target = parseInt(stat.textContent);
                 const increment = target / 50;
                 let current = 0;
                 
                 const timer = setInterval(() => {
                     current += increment;
                     if (current >= target) {
                         current = target;
                         clearInterval(timer);
                     }
                     stat.textContent = Math.floor(current) + (stat.textContent.includes('+') ? '+' : '') + (stat.textContent.includes('%') ? '%' : '');
                 }, 50);
             });
         };
         
         // Trigger stats animation when about section is visible
         const aboutSection = document.getElementById('about');
         if (aboutSection) {
             const observer = new IntersectionObserver((entries) => {
                 entries.forEach(entry => {
                     if (entry.isIntersecting) {
                         // Add a small delay to ensure the section is fully visible
                         setTimeout(() => {
                             animateStats();
                         }, 300);
                         observer.unobserve(entry.target);
                     }
                 });
             }, {
                 threshold: 0.3, // Trigger when 30% of the section is visible
                 rootMargin: '0px 0px -100px 0px' // Start animation slightly before the section is fully in view
             });
             observer.observe(aboutSection);
         }
        
        // Enhanced form interactions
        document.querySelectorAll('.contact-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-5px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
        
        // Smooth scroll enhancement
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerHeight = document.querySelector('#navbar').offsetHeight;
                    const targetPosition = target.offsetTop - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Performance optimization
        let ticking = false;
        function updateAnimations() {
            if (!ticking) {
                requestAnimationFrame(() => {
                    // Update any performance-critical animations here
                    ticking = false;
                });
                ticking = true;
            }
        }
        
        window.addEventListener('scroll', updateAnimations);
    </script>
</body>
</html>
