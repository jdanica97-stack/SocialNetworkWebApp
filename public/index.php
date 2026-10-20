<?php
// public/index.php
// Main entry point and front controller routing for SocialNetworkWebApp

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load centralized Security and Error Handlers
require_once __DIR__ . '/../app/helpers/Security.php';
require_once __DIR__ . '/../app/helpers/ErrorHandler.php';
ErrorHandler::register();

// Ensure CSRF token is always initialized in the session
csrf_token();

// Compute BASE_URL for clean routing and asset linking
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $base = preg_replace('#/public.*$#', '', $scriptDir);
    define('BASE_URL', rtrim($base, '/'));
}

// Action-based routing
$action = $_GET['action'] ?? $_POST['action'] ?? 'home';

switch ($action) {
    case 'register':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $authController = new AuthController();
        $authController->register();
        break;

    case 'login':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $authController = new AuthController();
        $authController->login();
        break;

    case 'logout':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $authController = new AuthController();
        $authController->logout();
        break;

    case 'profile':
        require_once __DIR__ . '/../app/controllers/ProfileController.php';
        $profileController = new ProfileController();
        $profileController->view();
        break;

    case 'edit_profile':
        require_once __DIR__ . '/../app/controllers/ProfileController.php';
        $profileController = new ProfileController();
        $profileController->edit();
        break;

    // Dedicated Account Settings Routing (Change Name, Change Username & Update Password)
    case 'settings':
        require_once __DIR__ . '/../app/controllers/SettingsController.php';
        $settingsController = new SettingsController();
        $settingsController->index();
        break;

    case 'settings_account':
    case 'settings_name':
    case 'settings_username':
        require_once __DIR__ . '/../app/controllers/SettingsController.php';
        $settingsController = new SettingsController();
        $settingsController->updateAccountInfo();
        break;

    case 'settings_password':
        require_once __DIR__ . '/../app/controllers/SettingsController.php';
        $settingsController = new SettingsController();
        $settingsController->changePassword();
        break;

    case 'get_follow_list':
        require_once __DIR__ . '/../app/controllers/ProfileController.php';
        $profileController = new ProfileController();
        $profileController->getFollowList();
        break;

    // Post, Comment, Like actions
    case 'view_post':
    case 'post':
        require_once __DIR__ . '/../app/controllers/PostController.php';
        $postController = new PostController();
        $postController->view();
        break;

    case 'create_post':
        require_once __DIR__ . '/../app/controllers/PostController.php';
        $postController = new PostController();
        $postController->create();
        break;

    case 'edit_post':
        require_once __DIR__ . '/../app/controllers/PostController.php';
        $postController = new PostController();
        $postController->edit();
        break;

    case 'update_post':
        require_once __DIR__ . '/../app/controllers/PostController.php';
        $postController = new PostController();
        $postController->update();
        break;

    case 'create_comment':
    case 'add_comment':
        require_once __DIR__ . '/../app/controllers/CommentController.php';
        $commentController = new CommentController();
        $commentController->create();
        break;

    case 'edit_comment':
        require_once __DIR__ . '/../app/controllers/CommentController.php';
        $commentController = new CommentController();
        $commentController->edit();
        break;

    case 'update_comment':
        require_once __DIR__ . '/../app/controllers/CommentController.php';
        $commentController = new CommentController();
        $commentController->update();
        break;

    case 'delete_comment':
        require_once __DIR__ . '/../app/controllers/CommentController.php';
        $commentController = new CommentController();
        $commentController->delete();
        break;

    case 'get_post_likers':
        require_once __DIR__ . '/../app/controllers/PostController.php';
        $postController = new PostController();
        $postController->getLikers();
        break;

    case 'like_post':
        require_once __DIR__ . '/../app/controllers/PostController.php';
        $postController = new PostController();
        $postController->like();
        break;

    case 'delete_post':
        require_once __DIR__ . '/../app/controllers/PostController.php';
        $postController = new PostController();
        $postController->delete();
        break;

    // Community Insights (Search, Filtering, and Reports)
    case 'insights':
    case 'search':
    case 'reports':
        require_once __DIR__ . '/../app/controllers/InsightsController.php';
        $insightsController = new InsightsController();
        $insightsController->index();
        break;

    // Administrator Control Center & Moderation Actions
    case 'admin':
        require_once __DIR__ . '/../app/controllers/AdminController.php';
        $adminController = new AdminController();
        $adminController->index();
        break;

    case 'admin_change_role':
        require_once __DIR__ . '/../app/controllers/AdminController.php';
        $adminController = new AdminController();
        $adminController->changeRole();
        break;

    case 'admin_delete_user':
        require_once __DIR__ . '/../app/controllers/AdminController.php';
        $adminController = new AdminController();
        $adminController->deleteUser();
        break;

    case 'admin_delete_post':
        require_once __DIR__ . '/../app/controllers/AdminController.php';
        $adminController = new AdminController();
        $adminController->deletePost();
        break;

    case 'admin_reorder_users':
        require_once __DIR__ . '/../app/controllers/AdminController.php';
        $adminController = new AdminController();
        $adminController->reorderUsers();
        break;

    case 'admin_delete_comment':
        require_once __DIR__ . '/../app/controllers/AdminController.php';
        $adminController = new AdminController();
        $adminController->deleteComment();
        break;

    case 'admin_save_event':
        require_once __DIR__ . '/../app/controllers/AdminController.php';
        $adminController = new AdminController();
        $adminController->saveEvent();
        break;

    case 'admin_delete_event':
        require_once __DIR__ . '/../app/controllers/AdminController.php';
        $adminController = new AdminController();
        $adminController->deleteEvent();
        break;

    // Follow and Unfollow Actions
    case 'follow_user':
        require_once __DIR__ . '/../app/controllers/FollowController.php';
        $followController = new FollowController();
        $followController->toggle();
        break;

    // Additional Navigation Pages
    case 'people':
    case 'groups':
    case 'events':
    case 'saved':
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'You must be logged in to access that page.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
        $pageTitle = ucfirst($action);
        require_once __DIR__ . '/../app/views/layouts/header.php';
        ?>
        <div class="card-modern p-5 text-center">
            <div class="mb-3 text-primary">
                <?php
                $iconMap = [
                    'people' => 'bi-people',
                    'groups' => 'bi-people-gear',
                    'events' => 'bi-calendar-event',
                    'saved'  => 'bi-bookmark'
                ];
                $icon = $iconMap[$action] ?? 'bi-grid';
                ?>
                <i class="bi <?= $icon ?> fs-1" style="color: var(--accent-yellow);"></i>
            </div>
            <h3 class="fw-bold text-dark"><?= htmlspecialchars(ucfirst($action)); ?></h3>
            <p class="text-muted col-md-6 mx-auto mb-4">
                Explore campus <?= htmlspecialchars(strtolower($action)); ?> and connect with members of the SMCC community.
            </p>
            <a href="<?= BASE_URL ?>/" class="btn btn-outline-primary px-4 rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Back to Feed
            </a>
        </div>
        <?php
        require_once __DIR__ . '/../app/views/layouts/footer.php';
        break;

    case 'home':
    case '':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
        $pageTitle = 'Home';
        require_once __DIR__ . '/../app/models/PostModel.php';
        require_once __DIR__ . '/../app/models/UserModel.php';
        require_once __DIR__ . '/../app/models/EventModel.php';

        $postModel = new PostModel();
        $userModel = new UserModel();
        $eventModel = new EventModel();

        $currentUserId = $_SESSION['user_id'];
        $feedFilter = $_GET['filter'] ?? 'newest';
        $posts = $postModel->getAllPosts($currentUserId, $feedFilter);
        $upcomingEvents = $eventModel->getUpcomingEvents(10);

        require_once __DIR__ . '/../app/views/layouts/header.php';
        require_once __DIR__ . '/../app/views/home/feed.php';
        require_once __DIR__ . '/../app/views/layouts/footer.php';
        break;

    default:
        // Return friendly 404 Not Found for unrecognized action routes
        ErrorHandler::renderErrorPage(404, 'Page Not Found', 'The page or action you requested ("' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '") does not exist on SMCC Connect.');
        break;
}
