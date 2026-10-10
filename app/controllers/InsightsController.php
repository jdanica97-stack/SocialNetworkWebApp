<?php
// app/controllers/InsightsController.php
// Controller handling the Community Insights module (Search, Filtering, and Reports)

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/ReportModel.php';

class InsightsController {
    private $userModel;
    private $postModel;
    private $reportModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
        $this->postModel = new PostModel();
        $this->reportModel = new ReportModel();
    }

    /**
     * Enforce authentication for Community Insights module.
     */
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'You must be logged in to access Community Insights.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * Main action for Community Insights search, filtering, and reporting.
     */
    public function index() {
        $this->requireAuth();

        $currentUserId = (int)$_SESSION['user_id'];
        $errorMessage = '';

        // 1. Sanitize & Retrieve Inputs
        $query = trim($_GET['q'] ?? '');
        $keyword = trim($_GET['keyword'] ?? '');
        $type = trim($_GET['type'] ?? 'all'); // 'all', 'users', 'posts'
        $contentType = trim($_GET['content_type'] ?? 'all'); // 'all', 'text', 'image'
        $sort = trim($_GET['sort'] ?? 'newest'); // 'newest', 'oldest', 'most_liked', 'most_commented'
        $reportSort = trim($_GET['report_sort'] ?? 'posts_desc'); // 'posts_desc', 'posts_asc', 'likes_desc', 'comments_desc', 'username_asc'
        $authorId = isset($_GET['author_id']) && $_GET['author_id'] !== '' ? (int)$_GET['author_id'] : null;
        $startDate = trim($_GET['start_date'] ?? '');
        $endDate = trim($_GET['end_date'] ?? '');

        // 2. Validate Inputs & Allowlists
        $validTypes = ['all', 'users', 'posts'];
        if (!in_array($type, $validTypes, true)) {
            $type = 'all';
        }

        $validContentTypes = ['all', 'text', 'image'];
        if (!in_array($contentType, $validContentTypes, true)) {
            $contentType = 'all';
        }

        $validSorts = ['newest', 'oldest', 'most_liked', 'most_commented'];
        if (!in_array($sort, $validSorts, true)) {
            $sort = 'newest';
        }

        $validReportSorts = ['posts_desc', 'posts_asc', 'likes_desc', 'comments_desc', 'username_asc'];
        if (!in_array($reportSort, $validReportSorts, true)) {
            $reportSort = 'posts_desc';
        }

        // Validate YYYY-MM-DD Date Formats
        if (!empty($startDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = '';
        }
        if (!empty($endDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = '';
        }

        // Date validation: End date cannot be earlier than Start date
        if (!empty($startDate) && !empty($endDate) && strtotime($endDate) < strtotime($startDate)) {
            $errorMessage = 'Validation Error: "To Date" cannot be earlier than "From Date". Please select a valid date range.';
        }

        // Combine search query and keyword if keyword provided
        $effectiveSearch = $query;
        if (!empty($keyword)) {
            $effectiveSearch = !empty($query) ? $query . ' ' . $keyword : $keyword;
        }

        // 3. Authors List for Filter Dropdown
        $allAuthors = [];
        try {
            $allAuthors = $this->userModel->getAllUsers();
        } catch (Exception $e) {
            error_log("Error fetching authors list: " . $e->getMessage());
        }

        // 4. Execute Search Queries (Users & Posts)
        $users = [];
        $posts = [];

        if (empty($errorMessage)) {
            try {
                if ($type === 'all' || $type === 'users') {
                    $users = $this->userModel->searchUsers($query);
                }

                if ($type === 'all' || $type === 'posts') {
                    $posts = $this->postModel->searchPostsAdvanced([
                        'query'           => $effectiveSearch,
                        'author_id'       => $authorId,
                        'start_date'      => $startDate,
                        'end_date'        => $endDate,
                        'content_type'    => $contentType,
                        'sort'            => $sort,
                        'current_user_id' => $currentUserId
                    ]);
                }
            } catch (Exception $e) {
                error_log("Search execution error: " . $e->getMessage());
                $errorMessage = "Search Error: An error occurred while retrieving search results. Please try again.";
            }
        }

        // 5. Execute Community Reports Queries ONLY if Generate Report button was pressed or action=reports
        $isReportRequested = !empty($_GET['generate_report']) || ($_GET['action'] ?? '') === 'reports';
        $reportGenerated = false;

        $summaryMetrics = [
            'total_users'    => 0,
            'total_posts'    => 0,
            'total_comments' => 0,
            'total_likes'    => 0
        ];
        $postsGroupedByAuthor = [];
        $mostActiveMembers = [];
        $postActivityTimeline = [];
        $detailedReportRows = [];

        if ($isReportRequested && empty($errorMessage)) {
            $filterParams = [
                'search'        => $effectiveSearch,
                'author_id'     => $authorId,
                'start_date'    => $startDate,
                'end_date'      => $endDate,
                'content_type'  => $contentType,
                'report_sort'   => $reportSort
            ];

            try {
                $summaryMetrics = $this->reportModel->getFilteredSummaryMetrics($filterParams);
                $postsGroupedByAuthor = $this->reportModel->getPostsGroupedByAuthor($filterParams);
                $mostActiveMembers = $this->reportModel->getMostActiveMembers($filterParams, 5);
                $postActivityTimeline = $this->reportModel->getPostActivityTimeline($filterParams);
                $detailedReportRows = $this->reportModel->getDetailedReportTable($filterParams);
                $reportGenerated = true;
            } catch (Exception $e) {
                error_log("Reports query error: " . $e->getMessage());
            }
        }

        // Calculate active filter count for UI badge
        $activeFilterCount = 0;
        if (!empty($query)) $activeFilterCount++;
        if (!empty($authorId)) $activeFilterCount++;
        if (!empty($startDate)) $activeFilterCount++;
        if (!empty($endDate)) $activeFilterCount++;
        if ($contentType !== 'all') $activeFilterCount++;
        if ($sort !== 'newest') $activeFilterCount++;
        if (!empty($keyword)) $activeFilterCount++;

        $pageTitle = 'Community Insights';

        // Render Community Insights View
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/insights/index.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}
