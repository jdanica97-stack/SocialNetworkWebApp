<?php
// app/views/layouts/header.php
// Reusable Modern HTML Header & Navigation Layout matching the Reference Design

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $base = preg_replace('#/public.*$#', '', $scriptDir);
    define('BASE_URL', rtrim($base, '/'));
}

$currentAction = $_GET['action'] ?? 'home';
$isLoggedIn = isset($_SESSION['user_id']);

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get current user avatar if logged in
$userInitials = 'U';
$userAvatarPath = null;
if ($isLoggedIn) {
    $name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        $userInitials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1));
    } else {
        $userInitials = strtoupper(mb_substr($name, 0, 2));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - SMCC Connect' : 'SMCC Connect'; ?></title>

    <!-- Bootstrap 5.3 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Theme initialization (inline to prevent theme flash) -->
    <script>
        window.BASE_URL = "<?= BASE_URL ?>";
        (function() {
            var stored = localStorage.getItem('smcc_theme_preference');
            var theme = 'light';
            if (stored === 'dark' || stored === 'light') {
                theme = stored;
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                theme = 'dark';
            }
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                document.documentElement.classList.add('dark-theme');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
                document.documentElement.classList.remove('dark-theme');
            }
        })();
    </script>

    <!-- Custom Modern Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
</head>
<body class="<?= $isLoggedIn ? 'app-logged-in' : 'app-logged-out' ?>">

<div class="app-layout">

    <?php if ($isLoggedIn): ?>
        <!-- Mobile Backdrop -->
        <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

        <!-- LEFT SIDEBAR (Only for Logged-In Users) -->
        <aside class="app-sidebar" id="appSidebar">
            <!-- Sidebar Brand / Logo -->
            <div class="sidebar-header">
                <a href="<?= BASE_URL ?>/" class="brand-link" onclick="toggleSidebar(event)" title="Close Navigation Menu">
                    <div class="brand-icon-box">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <span>SMCC Connect</span>
                </a>
            </div>

            <!-- Sidebar Navigation Menu -->
            <div class="sidebar-nav">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <!-- ADMIN DASHBOARD LINK FOR AUTHORIZED ADMINS ONLY -->
                    <a href="<?= BASE_URL ?>/index.php?action=admin" class="nav-link <?= (strpos($currentAction, 'admin') === 0 || $currentAction === 'admin') ? 'active' : '' ?>">
                        <i class="bi bi-shield-lock-fill text-warning"></i>
                        <span>Admin Dashboard</span>
                    </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/" class="nav-link <?= ($currentAction === 'home') ? 'active' : '' ?>">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Home</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?action=messages" class="nav-link <?= ($currentAction === 'messages') ? 'active' : '' ?>">
                    <i class="bi bi-chat-dots"></i>
                    <span>Messages</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?action=saved" class="nav-link <?= ($currentAction === 'saved') ? 'active' : '' ?>">
                    <i class="bi bi-bookmark"></i>
                    <span>Saved</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?action=edit_profile" class="nav-link <?= ($currentAction === 'edit_profile') ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </div>

            <!-- Sidebar Footer (Sign Out) -->
            <div class="sidebar-footer">
                <a href="<?= BASE_URL ?>/index.php?action=logout" class="nav-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Sign Out</span>
                </a>
            </div>
        </aside>
    <?php endif; ?>

    <!-- MAIN CONTENT AREA WRAPPER -->
    <div class="app-main <?= $isLoggedIn ? '' : 'no-sidebar' ?>">

        <!-- TOP HEADER -->
        <header class="app-header">
            <div class="header-container">
                <?php if ($isLoggedIn): ?>
                    <!-- Website Logo (Clicking Toggles Side Panel) -->
                    <div class="d-flex align-items-center me-3">
                        <a href="<?= BASE_URL ?>/" class="brand-link text-dark" id="headerLogoBtn" onclick="toggleSidebar(event)" title="Toggle Side Panel">
                            <div class="brand-icon-box">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <span class="fs-5 font-navy ms-1 fw-bold d-none d-sm-inline">SMCC Connect</span>
                        </a>
                    </div>

                    <!-- Search Bar -->
                    <form action="<?= BASE_URL ?>/index.php" method="GET" class="header-search" role="search">
                        <input type="hidden" name="action" value="search">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Search posts, people, or topics..." aria-label="Search" autocomplete="off">
                        <span class="search-shortcut">⌘K</span>
                    </form>

                    <!-- Right Header Actions -->
                    <div class="header-actions">
                        <!-- Dark Mode / Light Mode Toggle -->
                        <button class="header-icon-btn theme-toggle-btn" type="button" onclick="toggleTheme()" title="Switch to Dark Mode" aria-label="Switch to Dark Mode">
                            <i class="theme-toggle-icon bi bi-moon-fill"></i>
                        </button>

                        <button class="header-icon-btn" title="Notifications" aria-label="Notifications">
                            <i class="bi bi-bell"></i>
                        </button>
                        <button class="header-icon-btn" title="Messages" aria-label="Messages">
                            <i class="bi bi-chat-text"></i>
                        </button>

                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <a href="#" class="header-user-btn dropdown-toggle" id="headerUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar-sm">
                                    <?= htmlspecialchars($userInitials); ?>
                                </div>
                                <span class="header-user-name d-none d-sm-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?></span>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <span class="badge bg-warning text-dark ms-1">Admin</span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="headerUserDropdown">
                                <li>
                                    <div class="px-3 py-2 border-bottom">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <p class="mb-0 fw-bold small text-dark"><?= htmlspecialchars($_SESSION['full_name'] ?? ''); ?></p>
                                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                                <span class="badge bg-warning text-dark micro-text">Admin</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mb-0 text-muted small">@<?= htmlspecialchars($_SESSION['username'] ?? ''); ?></p>
                                    </div>
                                </li>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 fw-semibold text-warning" href="<?= BASE_URL ?>/index.php?action=admin">
                                            <i class="bi bi-shield-lock-fill"></i>
                                            <span>Admin Dashboard</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= BASE_URL ?>/index.php?action=profile">
                                        <i class="bi bi-person text-muted"></i>
                                        <span>My Profile</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= BASE_URL ?>/index.php?action=edit_profile">
                                        <i class="bi bi-pencil-square text-muted"></i>
                                        <span>Edit Profile</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" href="<?= BASE_URL ?>/index.php?action=logout">
                                        <i class="bi bi-box-arrow-right"></i>
                                        <span>Sign Out</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Public / Unauthenticated Header -->
                    <div class="d-flex align-items-center">
                        <a href="<?= BASE_URL ?>/" class="brand-link text-dark">
                            <div class="brand-icon-box">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <span class="fs-5 font-navy ms-2">SMCC Connect</span>
                        </a>
                    </div>

                    <div class="header-actions">
                        <!-- Dark Mode / Light Mode Toggle -->
                        <button class="header-icon-btn theme-toggle-btn me-2" type="button" onclick="toggleTheme()" title="Switch to Dark Mode" aria-label="Switch to Dark Mode">
                            <i class="theme-toggle-icon bi bi-moon-fill"></i>
                        </button>

                        <a href="<?= BASE_URL ?>/index.php?action=login" class="btn <?= ($currentAction === 'login') ? 'btn-navy' : 'btn-outline-navy' ?> btn-sm px-3 rounded-pill fw-semibold me-2">Log In</a>
                        <a href="<?= BASE_URL ?>/index.php?action=register" class="btn <?= ($currentAction === 'register') ? 'btn-accent' : 'btn-outline-accent' ?> btn-sm rounded-pill px-3 fw-semibold">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- PAGE CONTENT CONTAINER -->
        <main class="<?= $isLoggedIn ? 'p-3 p-md-4 flex-grow-1' : 'auth-main flex-grow-1 d-flex align-items-center justify-content-center p-3 p-md-4' ?>">
