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
    if (function_exists('csrf_token')) {
        csrf_token();
    } else {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Get current user avatar if logged in
$userInitials = 'U';
$userAvatarPath = null;
if ($isLoggedIn) {
    // Sync latest profile details if UserModel available or query DB to keep session fresh
    if (!isset($_SESSION['profile_image_synced']) || $_SESSION['profile_image_synced'] < (time() - 60)) {
        require_once __DIR__ . '/../../models/UserModel.php';
        $headerUserModel = new UserModel();
        $currentUserData = $headerUserModel->findById((int)$_SESSION['user_id']);
        if ($currentUserData) {
            $_SESSION['profile_image'] = $currentUserData['profile_image'] ?? null;
            $_SESSION['full_name'] = $currentUserData['full_name'] ?? $_SESSION['full_name'];
            $_SESSION['profile_image_synced'] = time();
        }
    }

    $name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        $userInitials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1));
    } else {
        $userInitials = strtoupper(mb_substr($name, 0, 2));
    }

    if (!empty($_SESSION['profile_image'])) {
        $uploadedPath = __DIR__ . '/../../../public/uploads/avatars/' . basename($_SESSION['profile_image']);
        if (file_exists($uploadedPath)) {
            $userAvatarPath = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($_SESSION['profile_image']));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
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

    <!-- Cropper.js CSS (Image Crop/Adjust) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">

    <!-- Custom Modern Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?>">
</head>
<body class="<?= $isLoggedIn ? 'app-logged-in' : 'app-logged-out' ?>">

<div class="app-layout">

    <?php if ($isLoggedIn): ?>
        <!-- Mobile Backdrop -->
        <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

        <!-- LEFT SIDEBAR (Only for Logged-In Users) -->
        <aside class="app-sidebar" id="appSidebar">
            <!-- Sidebar Brand / Logo -->
            <div class="sidebar-header d-flex align-items-center justify-content-between">
                <a href="<?= BASE_URL ?>/" class="brand-link" onclick="toggleSidebar(event)" title="SMCC Connect">
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
                <a href="<?= BASE_URL ?>/index.php?action=profile" class="nav-link <?= ($currentAction === 'profile') ? 'active' : '' ?>">
                    <i class="bi bi-person-fill"></i>
                    <span>Profile</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?action=messages" class="nav-link <?= ($currentAction === 'messages') ? 'active' : '' ?>">
                    <i class="bi bi-chat-dots"></i>
                    <span>Messages</span>
                </a>
                <a href="<?= BASE_URL ?>/index.php?action=settings" class="nav-link <?= (strpos($currentAction, 'settings') === 0) ? 'active' : '' ?>">
                    <i class="bi bi-gear-fill"></i>
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
                    <!-- Header Left: Menu Toggle Icon -> Yellow Logo -> SMCC Connect Brand Name -->
                    <div class="header-brand-group d-flex align-items-center me-3 gap-2">
                        <!-- Navigation Menu Toggle Button -->
                        <button class="header-menu-toggle-btn btn border-0 shadow-none bg-transparent d-flex align-items-center justify-content-center p-1" 
                                type="button" 
                                id="headerNavToggleBtn" 
                                onclick="toggleSidebar(event)" 
                                title="Toggle sidebar"
                                aria-label="Toggle sidebar"
                                style="border: none !important; background: transparent !important; outline: none !important; box-shadow: none !important;">
                            <svg class="minimalist-menu-icon" width="22" height="18" viewBox="0 0 22 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <line x1="2" y1="3" x2="20" y2="3" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                <line x1="5" y1="9" x2="17" y2="9" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                <line x1="8" y1="15" x2="14" y2="15" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                            </svg>
                        </button>

                        <!-- Brand: Yellow Logo Container and Brand Name -->
                        <a href="<?= BASE_URL ?>/" class="brand-link text-dark align-items-center gap-2" id="headerLogoBtn" title="SMCC Connect Home">
                            <div class="brand-icon-box flex-shrink-0">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <span class="fs-5 font-navy fw-bold d-none d-sm-inline">SMCC Connect</span>
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

                        <!-- Notifications Dropdown -->
                        <div class="dropdown">
                            <button class="header-icon-btn position-relative dropdown-toggle" id="notificationsDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications" aria-label="Notifications">
                                <i class="bi bi-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="notifBadge" style="font-size: 0.65rem;">
                                    0
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-0" aria-labelledby="notificationsDropdownBtn" style="width: 320px; max-width: 90vw;">
                                <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-body">
                                    <h6 class="mb-0 fw-bold text-dark">Notifications</h6>
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none small" id="btnMarkAllNotifsRead">
                                        Mark all read
                                    </button>
                                </div>
                                <div class="overflow-y-auto" id="notificationsDropdownList" style="max-height: 350px;">
                                    <div class="p-4 text-center text-muted small" id="notifEmptyState">
                                        <i class="bi bi-bell-slash fs-3 d-block mb-1 opacity-50"></i>
                                        No new notifications
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Messages Button with Live Unread Counter -->
                        <a href="<?= BASE_URL ?>/index.php?action=messages" class="header-icon-btn position-relative" title="Messages" aria-label="Messages">
                            <i class="bi bi-chat-text"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary d-none" id="messagesBadge" style="font-size: 0.65rem;">
                                0
                            </span>
                        </a>

                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <a href="#" class="header-user-btn dropdown-toggle" id="headerUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php if (!empty($userAvatarPath)): ?>
                                    <img src="<?= $userAvatarPath ?>" alt="<?= htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>" class="user-avatar-sm" onerror="this.onerror=null; this.outerHTML='<div class=\'user-avatar-sm\'><?= htmlspecialchars($userInitials) ?></div>';">
                                <?php else: ?>
                                    <div class="user-avatar-sm">
                                        <?= htmlspecialchars($userInitials); ?>
                                    </div>
                                <?php endif; ?>
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
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= BASE_URL ?>/index.php?action=settings">
                                        <i class="bi bi-gear text-muted"></i>
                                        <span>Settings</span>
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

            <!-- Global Flash Messages -->
            <?php if (!empty($_SESSION['error_message']) && $currentAction !== 'home' && $currentAction !== 'insights' && $currentAction !== 'admin'): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 mx-auto" style="max-width: 900px;" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success_message']) && $currentAction !== 'home' && $currentAction !== 'insights' && $currentAction !== 'admin' && $currentAction !== 'login'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 mx-auto" style="max-width: 900px;" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
