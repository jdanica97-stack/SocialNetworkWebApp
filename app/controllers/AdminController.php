<?php
// app/controllers/AdminController.php
// Controller managing Admin Dashboard requests, server-side role verification, and moderation actions

require_once __DIR__ . '/../models/AdminModel.php';
require_once __DIR__ . '/../models/EventModel.php';

class AdminController {
    private $adminModel;
    private $eventModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->adminModel = new AdminModel();
        $this->eventModel = new EventModel();
    }

    /**
     * Strict Server-Side Authorization Check.
     * Guarantees that unauthenticated users or normal users cannot access admin pages or execute admin actions.
     */
    private function requireAdmin() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'Access Denied: Please log in as an administrator to access the Admin Dashboard.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }

        if (($_SESSION['role'] ?? 'user') !== 'admin') {
            $_SESSION['error_message'] = 'Access Denied: You do not have administrator permissions to view that page.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }

    /**
     * Validate CSRF token for state-changing POST requests.
     */
    private function validateCsrf() {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $_SESSION['error_message'] = 'Security Error: Invalid or expired CSRF token. Action blocked.';
            $tab = $_GET['tab'] ?? $_POST['tab'] ?? 'overview';
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=' . urlencode($tab));
            exit;
        }
    }

    /**
     * Render main Admin Dashboard with Overview, User Management, Post Management, Comment Moderation, and Activity Stats.
     */
    public function index() {
        $this->requireAdmin();

        $tab        = trim($_GET['tab'] ?? 'overview');
        $search     = trim($_GET['q'] ?? $_GET['search'] ?? '');
        $roleFilter = trim($_GET['role'] ?? '');
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $perPage    = 10;

        $validTabs = ['overview', 'users', 'posts', 'comments', 'events', 'stats'];
        if (!in_array($tab, $validTabs, true)) {
            $tab = 'overview';
        }

        // Summary counts for top KPI cards
        $summaryStats = $this->adminModel->getSummaryCounts();

        // Data containers for views
        $usersData = [];
        $postsData = [];
        $commentsData = [];
        $eventsData = [];
        $pagination = [
            'current_page' => $page,
            'total_pages'  => 1,
            'total_items'  => 0,
            'per_page'     => $perPage
        ];

        // Fetch tab-specific records
        switch ($tab) {
            case 'events':
                $eventsData = $this->eventModel->getAllEvents();
                break;
            case 'users':
                $totalUsers = $this->adminModel->getUsersCount($search, $roleFilter);
                $totalPages = max(1, (int)ceil($totalUsers / $perPage));
                $page       = min($page, $totalPages);

                $usersData = $this->adminModel->getPaginatedUsers($search, $roleFilter, $page, $perPage);
                $pagination = [
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'total_items'  => $totalUsers,
                    'per_page'     => $perPage
                ];
                break;

            case 'posts':
                $totalPosts = $this->adminModel->getPostsCount($search);
                $totalPages = max(1, (int)ceil($totalPosts / $perPage));
                $page       = min($page, $totalPages);

                $postsData  = $this->adminModel->getPaginatedPosts($search, $page, $perPage);
                $pagination = [
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'total_items'  => $totalPosts,
                    'per_page'     => $perPage
                ];
                break;

            case 'comments':
                $totalComments = $this->adminModel->getCommentsCount($search);
                $totalPages    = max(1, (int)ceil($totalComments / $perPage));
                $page          = min($page, $totalPages);

                $commentsData = $this->adminModel->getPaginatedComments($search, $page, $perPage);
                $pagination   = [
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'total_items'  => $totalComments,
                    'per_page'     => $perPage
                ];
                break;

            case 'stats':
            case 'overview':
            default:
                break;
        }

        // Fetch ranking activity stats for overview and stats tabs
        $mostActiveUsers = $this->adminModel->getMostActiveUsers(5);
        $mostLikedPosts  = $this->adminModel->getMostLikedPosts(5);

        $pageTitle = 'Admin Dashboard';

        // Check if request is an AJAX tab switch
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || !empty($_GET['ajax']);

        if ($isAjax) {
            require __DIR__ . '/../views/admin/dashboard.php';
            exit;
        }

        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/admin/dashboard.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Change a user's role (Admin / User).
     */
    public function changeRole() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=users');
            exit;
        }

        $this->validateCsrf();

        $targetUserId   = (int)($_POST['user_id'] ?? 0);
        $newRole        = trim($_POST['role'] ?? '');
        $currentAdminId = (int)$_SESSION['user_id'];

        if ($targetUserId <= 0) {
            $_SESSION['error_message'] = 'Invalid user ID.';
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=users');
            exit;
        }

        $res = $this->adminModel->updateUserRole($targetUserId, $newRole, $currentAdminId);

        if ($res['success']) {
            $_SESSION['success_message'] = $res['message'];
        } else {
            $_SESSION['error_message'] = $res['message'];
        }

        header('Location: ' . BASE_URL . '/index.php?action=admin&tab=users');
        exit;
    }

    /**
     * Delete a user account (Admin Action).
     */
    public function deleteUser() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=users');
            exit;
        }

        $this->validateCsrf();

        $targetUserId   = (int)($_POST['user_id'] ?? 0);
        $currentAdminId = (int)$_SESSION['user_id'];

        if ($targetUserId <= 0) {
            $_SESSION['error_message'] = 'Invalid user ID.';
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=users');
            exit;
        }

        $res = $this->adminModel->deleteUserByAdmin($targetUserId, $currentAdminId);

        if ($res['success']) {
            $_SESSION['success_message'] = $res['message'];
        } else {
            $_SESSION['error_message'] = $res['message'];
        }

        header('Location: ' . BASE_URL . '/index.php?action=admin&tab=users');
        exit;
    }

    /**
     * Moderate / Remove an inappropriate post (Admin Action).
     */
    public function deletePost() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=posts');
            exit;
        }

        $this->validateCsrf();

        $postId = (int)($_POST['post_id'] ?? 0);

        if ($postId <= 0) {
            $_SESSION['error_message'] = 'Invalid post ID.';
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=posts');
            exit;
        }

        $deleted = $this->adminModel->deletePostByAdmin($postId);

        if ($deleted) {
            $_SESSION['success_message'] = 'Post removed successfully by Administrator.';
        } else {
            $_SESSION['error_message'] = 'Failed to remove post or post not found.';
        }

        header('Location: ' . BASE_URL . '/index.php?action=admin&tab=posts');
        exit;
    }

    /**
     * Moderate / Remove an inappropriate comment (Admin Action).
     */
    public function deleteComment() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=comments');
            exit;
        }

        $this->validateCsrf();

        $commentId = (int)($_POST['comment_id'] ?? 0);

        if ($commentId <= 0) {
            $_SESSION['error_message'] = 'Invalid comment ID.';
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=comments');
            exit;
        }

        $deleted = $this->adminModel->deleteCommentByAdmin($commentId);

        if ($deleted) {
            $_SESSION['success_message'] = 'Comment removed successfully by Administrator.';
        } else {
            $_SESSION['error_message'] = 'Failed to remove comment or comment not found.';
        }

        header('Location: ' . BASE_URL . '/index.php?action=admin&tab=comments');
        exit;
    }

    /**
     * Create or Update a Campus Event (Admin Action).
     */
    public function saveEvent() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=events');
            exit;
        }

        $this->validateCsrf();

        $id          = (int)($_POST['event_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate   = trim($_POST['event_date'] ?? '');
        $eventTime   = trim($_POST['event_time'] ?? '');
        $location    = trim($_POST['location'] ?? '');

        if (empty($title) || empty($eventDate) || empty($location)) {
            $_SESSION['error_message'] = 'Please fill in all required event fields (Title, Date, and Location).';
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=events');
            exit;
        }

        if ($id > 0) {
            $updated = $this->eventModel->updateEvent($id, $title, $description, $eventDate, $eventTime, $location);
            if ($updated) {
                $_SESSION['success_message'] = 'Campus event updated successfully!';
            } else {
                $_SESSION['error_message'] = 'Failed to update campus event.';
            }
        } else {
            $createdBy = $_SESSION['user_id'] ?? null;
            $created = $this->eventModel->createEvent($title, $description, $eventDate, $eventTime, $location, $createdBy);
            if ($created) {
                $_SESSION['success_message'] = 'New campus event published successfully!';
            } else {
                $_SESSION['error_message'] = 'Failed to publish campus event.';
            }
        }

        header('Location: ' . BASE_URL . '/index.php?action=admin&tab=events');
        exit;
    }

    /**
     * Delete a Campus Event (Admin Action).
     */
    public function deleteEvent() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=events');
            exit;
        }

        $this->validateCsrf();

        $id = (int)($_POST['event_id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error_message'] = 'Invalid event ID.';
            header('Location: ' . BASE_URL . '/index.php?action=admin&tab=events');
            exit;
        }

        $deleted = $this->eventModel->deleteEvent($id);
        if ($deleted) {
            $_SESSION['success_message'] = 'Campus event deleted successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to delete campus event.';
        }

        header('Location: ' . BASE_URL . '/index.php?action=admin&tab=events');
        exit;
    }
}
