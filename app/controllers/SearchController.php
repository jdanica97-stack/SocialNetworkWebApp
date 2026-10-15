<?php
// app/controllers/SearchController.php
// Controller handling search for users and posts, with filtering

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/FollowModel.php';

class SearchController {
    private $userModel;
    private $postModel;
    private $followModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
        $this->postModel = new PostModel();
        $this->followModel = new FollowModel();
    }

    /**
     * Enforce authentication for search.
     * Prevents guest users from accessing protected social search results.
     */
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'You must be logged in to search and view social content.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * Main search, filter, and reporting handler.
     */
    public function index() {
        $this->requireAuth();

        $currentUserId = (int)$_SESSION['user_id'];
        $rawQuery = $_GET['q'] ?? '';
        $query = trim($rawQuery);
        $type = trim($_GET['type'] ?? 'all'); // 'all', 'users', 'posts'
        $filter = trim($_GET['filter'] ?? 'newest'); // 'newest', 'oldest', 'most_liked', 'most_commented'
        $authorId = isset($_GET['author_id']) && $_GET['author_id'] !== '' ? (int)$_GET['author_id'] : null;

        // Valid types
        $validTypes = ['all', 'users', 'posts'];
        if (!in_array($type, $validTypes, true)) {
            $type = 'all';
        }

        // Valid post sorting filters (Step 11 & Step 12 requirements)
        $validFilters = ['newest', 'oldest', 'most_liked', 'most_commented'];
        if (!in_array($filter, $validFilters, true)) {
            $filter = 'newest';
        }

        $users = [];
        $posts = [];
        $summaryStats = [
            'total_posts'   => 0,
            'total_authors' => 0,
            'total_comments'=> 0,
            'total_likes'   => 0
        ];
        $allAuthors = $this->userModel->getAllUsers();

        $isEmptySearch = ($query === '' && empty($authorId));

        if (!$isEmptySearch) {
            // Execute search queries inside models using prepared statements
            if ($type === 'all' || $type === 'users') {
                $users = $this->userModel->searchUsers($query);
                if (!empty($users) && $currentUserId) {
                    foreach ($users as &$u) {
                        $uId = (int)$u['id'];
                        if ($uId !== $currentUserId) {
                            $u['is_following'] = $this->followModel->isFollowing($currentUserId, $uId);
                            $u['is_followed_by'] = $this->followModel->isFollowing($uId, $currentUserId);
                        } else {
                            $u['is_following'] = false;
                            $u['is_followed_by'] = false;
                        }
                    }
                    unset($u);
                }
            }

            if ($type === 'all' || $type === 'posts') {
                $posts = $this->postModel->searchPosts($query, $filter, $currentUserId, $authorId);
            }
            // Always compute summary statistics based on current search query and author filter
            $summaryStats = $this->postModel->getSearchSummaryStats($query, $authorId);
        } else {
            // Default summary stats for overall database dataset when landing on search page
            $summaryStats = $this->postModel->getSearchSummaryStats('', null);
        }

        $selectedAuthorName = '';
        if ($authorId) {
            $authorObj = $this->userModel->findById($authorId);
            if ($authorObj) {
                $selectedAuthorName = $authorObj['full_name'];
            }
        }

        $pageTitle = $isEmptySearch 
            ? 'Search & Reports' 
            : 'Search: ' . ($query !== '' ? $query : ($selectedAuthorName ? 'Author: ' . $selectedAuthorName : 'Filtered Results'));

        // Render search view wrapped in header and footer
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/search/results.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}
