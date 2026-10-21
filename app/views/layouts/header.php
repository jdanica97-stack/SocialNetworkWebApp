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

    <!-- Theme Controller (Loads early to prevent flicker & provide window.toggleTheme) -->
    <script>window.BASE_URL = "<?= BASE_URL ?>";</script>
    <script src="<?= BASE_URL ?>/public/assets/js/theme.js"></script>

    <!-- Cropper.js CSS (Image Crop/Adjust) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">

    <!-- Custom Modern Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?>">
</head>
<body class="<?= $isLoggedIn ? 'app-logged-in' : 'app-logged-out' ?>">

<div class="app-layout">

    <!-- MAIN CONTENT AREA WRAPPER -->
    <div class="app-main">

        <!-- TOP HEADER -->
        <header class="app-header">
            <div class="header-container">
                <?php if ($isLoggedIn): ?>
                    <!-- Header Left: SMCC Connect Brand Logo and Name -->
                    <div class="header-brand-group d-flex align-items-center me-3">
                        <a href="<?= BASE_URL ?>/" class="brand-link align-items-center gap-2" id="headerLogoBtn" title="SMCC Connect Home">
                            <div class="brand-icon-box flex-shrink-0">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <span class="fs-5 brand-title fw-bold d-none d-sm-inline">SMCC Connect</span>
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

                        <!-- Facebook-Style Profile Dropdown (Only for Authenticated Users) -->
                        <div class="dropdown fb-profile-dropdown">
                            <button class="fb-profile-trigger" 
                                    type="button" 
                                    id="headerUserDropdown" 
                                    data-bs-toggle="dropdown" 
                                    data-bs-auto-close="true"
                                    aria-expanded="false" 
                                    title="Account Menu" 
                                    aria-label="Account Menu">
                                <?php if (!empty($userAvatarPath)): ?>
                                    <img src="<?= $userAvatarPath ?>" alt="<?= htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>" class="fb-avatar-trigger" onerror="this.onerror=null; this.outerHTML='<div class=\'fb-avatar-trigger\'><?= htmlspecialchars($userInitials) ?></div>';">
                                <?php else: ?>
                                    <div class="fb-avatar-trigger">
                                        <?= htmlspecialchars($userInitials); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="header-user-name d-none d-md-inline text-truncate"><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?></span>
                                <i class="bi bi-chevron-down fb-chevron ms-1 d-none d-sm-inline"></i>
                            </button>

                            <div class="dropdown-menu dropdown-menu-end fb-dropdown-menu shadow border-0" aria-labelledby="headerUserDropdown">
                                <!-- Top Profile Card / Header -->
                                <a href="<?= BASE_URL ?>/index.php?action=profile" class="fb-user-card text-decoration-none">
                                    <?php if (!empty($userAvatarPath)): ?>
                                        <img src="<?= $userAvatarPath ?>" alt="<?= htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>" class="fb-avatar-card" onerror="this.onerror=null; this.outerHTML='<div class=\'fb-avatar-card\'><?= htmlspecialchars($userInitials) ?></div>';">
                                    <?php else: ?>
                                        <div class="fb-avatar-card">
                                            <?= htmlspecialchars($userInitials); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex flex-column min-w-0 flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <span class="fb-user-fullname text-truncate"><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?></span>
                                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                                <span class="badge bg-warning text-dark micro-text flex-shrink-0">Admin</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="fb-user-subtext text-truncate">@<?= htmlspecialchars($_SESSION['username'] ?? 'user'); ?></span>
                                    </div>
                                </a>

                                <div class="fb-menu-divider"></div>

                                <!-- Option 1: View Profile -->
                                <a class="dropdown-item fb-menu-item" href="<?= BASE_URL ?>/index.php?action=profile">
                                    <div class="fb-menu-item-left">
                                        <div class="fb-icon-circle">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <span class="fb-item-label">View Profile</span>
                                    </div>
                                    <i class="bi bi-chevron-right fb-arrow-right"></i>
                                </a>

                                <!-- Option 2: Account Settings -->
                                <a class="dropdown-item fb-menu-item" href="<?= BASE_URL ?>/index.php?action=settings" title="Account Settings">
                                    <div class="fb-menu-item-left">
                                        <div class="fb-icon-circle">
                                            <i class="bi bi-gear-fill"></i>
                                        </div>
                                        <span class="fb-item-label">Account Settings</span>
                                    </div>
                                    <i class="bi bi-chevron-right fb-arrow-right"></i>
                                </a>

                                <div class="fb-menu-divider"></div>

                                <!-- Option 3: Sign Out -->
                                <a class="dropdown-item fb-menu-item fb-menu-signout" href="<?= BASE_URL ?>/index.php?action=logout">
                                    <div class="fb-menu-item-left">
                                        <div class="fb-icon-circle fb-icon-signout">
                                            <i class="bi bi-box-arrow-right"></i>
                                        </div>
                                        <span class="fb-item-label text-danger">Sign Out</span>
                                    </div>
                                    <i class="bi bi-chevron-right fb-arrow-right"></i>
                                </a>
                            </div>
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
            <?php if (!empty($_SESSION['error_message']) && $currentAction !== 'home' && $currentAction !== 'insights'): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 mx-auto" style="max-width: 900px;" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success_message']) && $currentAction !== 'home' && $currentAction !== 'insights' && $currentAction !== 'login'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 mx-auto" style="max-width: 900px;" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
