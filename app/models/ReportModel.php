<?php
// app/models/ReportModel.php
// Model handling SQL aggregate queries, JOINs, subqueries, filtering, and report generation

require_once __DIR__ . '/../../config/database.php';

class ReportModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * A. System Summary Report
     * Demonstrates SQL Aggregate functions (COUNT) and Subqueries.
     * Returns aggregate counts for total users, posts, comments, and likes.
     *
     * @return array
     */
    public function getSystemSummary() {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM users) AS total_users,
                    (SELECT COUNT(*) FROM posts) AS total_posts,
                    (SELECT COUNT(*) FROM comments) AS total_comments,
                    (SELECT COUNT(*) FROM likes) AS total_likes";
        
        $stmt = $this->db->query($sql);
        $summary = $stmt->fetch();

        $totalUsers = (int)($summary['total_users'] ?? 0);
        $totalPosts = (int)($summary['total_posts'] ?? 0);
        $totalComments = (int)($summary['total_comments'] ?? 0);

        $summary['avg_posts_per_user'] = $totalUsers > 0 ? round($totalPosts / $totalUsers, 2) : 0;
        $summary['avg_comments_per_post'] = $totalPosts > 0 ? round($totalComments / $totalPosts, 2) : 0;

        return $summary;
    }

    /**
     * B. Posts per User Report (with filtering and sorting)
     * Demonstrates: SELECT, LEFT JOIN, GROUP BY, COUNT(), ORDER BY, prepared statements.
     * Includes users with 0 posts.
     *
     * @param string $searchTerm Optional username/name search filter
     * @param string $sortOrder Sort order ('posts_desc', 'posts_asc', 'username_asc')
     * @return array
     */
    public function getPostsPerUser($searchTerm = '', $sortOrder = 'posts_desc') {
        $whereClause = "";
        $params = [];

        if (!empty($searchTerm)) {
            $whereClause = "WHERE u.username LIKE :search1 OR u.full_name LIKE :search2";
            $searchVal = '%' . trim($searchTerm) . '%';
            $params[':search1'] = $searchVal;
            $params[':search2'] = $searchVal;
        }

        switch ($sortOrder) {
            case 'posts_asc':
                $orderBy = "post_count ASC, u.username ASC";
                break;
            case 'username_asc':
                $orderBy = "u.username ASC";
                break;
            case 'posts_desc':
            default:
                $orderBy = "post_count DESC, u.username ASC";
                break;
        }

        $sql = "SELECT 
                    u.id AS user_id,
                    u.username,
                    u.full_name,
                    u.profile_image,
                    u.created_at AS joined_at,
                    COUNT(p.id) AS post_count
                FROM users u
                LEFT JOIN posts p ON u.id = p.user_id
                {$whereClause}
                GROUP BY u.id, u.username, u.full_name, u.profile_image, u.created_at
                ORDER BY {$orderBy}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * C. Most Active Users Report
     * Demonstrates: SELECT, INNER JOIN, GROUP BY, COUNT(), ORDER BY, LIMIT
     * Displays top active users ranked by total post count.
     *
     * @param int $limit
     * @return array
     */
    public function getMostActiveUsers($limit = 5) {
        $sql = "SELECT 
                    u.id AS user_id,
                    u.username,
                    u.full_name,
                    u.profile_image,
                    COUNT(p.id) AS total_posts,
                    MAX(p.created_at) AS last_post_at
                FROM users u
                JOIN posts p ON u.id = p.user_id
                GROUP BY u.id, u.username, u.full_name, u.profile_image
                ORDER BY total_posts DESC, u.username ASC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * D. Recent Posts Report (with date range and keyword filtering)
     * Demonstrates: SELECT, JOIN, LEFT JOIN, COUNT(DISTINCT), GROUP BY, WHERE, ORDER BY
     * Avoids duplicate rows when aggregating likes and comments.
     *
     * @param string $startDate Optional YYYY-MM-DD
     * @param string $endDate Optional YYYY-MM-DD
     * @param string $searchTerm Optional keyword search
     * @param int $limit
     * @return array
     */
    public function getRecentPostsReport($startDate = '', $endDate = '', $searchTerm = '', $limit = 20) {
        $whereConditions = [];
        $params = [];

        if (!empty($startDate)) {
            $whereConditions[] = "DATE(p.created_at) >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if (!empty($endDate)) {
            $whereConditions[] = "DATE(p.created_at) <= :end_date";
            $params[':end_date'] = $endDate;
        }

        if (!empty($searchTerm)) {
            $whereConditions[] = "(p.content LIKE :search1 OR u.username LIKE :search2 OR u.full_name LIKE :search3)";
            $searchVal = '%' . trim($searchTerm) . '%';
            $params[':search1'] = $searchVal;
            $params[':search2'] = $searchVal;
            $params[':search3'] = $searchVal;
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        $sql = "SELECT 
                    p.id AS post_id,
                    p.content,
                    p.image,
                    p.created_at,
                    u.id AS author_id,
                    u.username AS author_username,
                    u.full_name AS author_fullname,
                    u.profile_image AS author_avatar,
                    COUNT(DISTINCT c.id) AS comment_count,
                    COUNT(DISTINCT l.id) AS like_count
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN comments c ON p.id = c.post_id
                LEFT JOIN likes l ON p.id = l.post_id
                {$whereClause}
                GROUP BY p.id, p.content, p.image, p.created_at, u.id, u.username, u.full_name, u.profile_image
                ORDER BY p.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * E. Most Liked Posts Report
     * Demonstrates: SELECT, JOIN, LEFT JOIN, COUNT(), GROUP BY, ORDER BY
     * Posts ranked by number of likes.
     *
     * @param int $limit
     * @return array
     */
    public function getMostLikedPosts($limit = 10) {
        $sql = "SELECT 
                    p.id AS post_id,
                    p.content,
                    p.created_at,
                    u.username AS author_username,
                    u.full_name AS author_fullname,
                    COUNT(l.id) AS like_count
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN likes l ON p.id = l.post_id
                GROUP BY p.id, p.content, p.created_at, u.username, u.full_name
                ORDER BY like_count DESC, p.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * F. Comments Statistics & Log Report
     * Demonstrates: User comment aggregates and recent comment list with parent post previews.
     *
     * @return array
     */
    public function getCommentsReport() {
        // 1. Total comments per user
        $userCommentsSql = "SELECT 
                                u.id AS user_id,
                                u.username,
                                u.full_name,
                                COUNT(c.id) AS comment_count
                            FROM users u
                            LEFT JOIN comments c ON u.id = c.user_id
                            GROUP BY u.id, u.username, u.full_name
                            ORDER BY comment_count DESC, u.username ASC";
        $stmt1 = $this->db->query($userCommentsSql);
        $userComments = $stmt1->fetchAll();

        // 2. Recent comments with authors and associated post previews
        $recentCommentsSql = "SELECT 
                                c.id AS comment_id,
                                c.content AS comment_content,
                                c.created_at AS comment_date,
                                u.username AS commenter_username,
                                u.full_name AS commenter_fullname,
                                p.id AS post_id,
                                p.content AS post_content,
                                pu.username AS post_author_username
                              FROM comments c
                              JOIN users u ON c.user_id = u.id
                              JOIN posts p ON c.post_id = p.id
                              JOIN users pu ON p.user_id = pu.id
                              ORDER BY c.created_at DESC
                              LIMIT 15";
        $stmt2 = $this->db->query($recentCommentsSql);
        $recentComments = $stmt2->fetchAll();

        return [
            'user_comments'   => $userComments,
            'recent_comments' => $recentComments
        ];
    }

    /**
     * Build WHERE conditions and parameters for filtering report queries.
     *
     * @param array $params
     * @return array [$whereClause, $sqlParams]
     */
    private function buildReportWhereClause($params = []) {
        $search = trim($params['search'] ?? '');
        $authorId = !empty($params['author_id']) ? (int)$params['author_id'] : null;
        $startDate = trim($params['start_date'] ?? '');
        $endDate = trim($params['end_date'] ?? '');
        $contentType = trim($params['content_type'] ?? 'all');

        $whereConditions = [];
        $sqlParams = [];

        if ($search !== '') {
            $whereConditions[] = "(p.content LIKE :search1 OR u.full_name LIKE :search2 OR u.username LIKE :search3)";
            $searchVal = '%' . $search . '%';
            $sqlParams[':search1'] = $searchVal;
            $sqlParams[':search2'] = $searchVal;
            $sqlParams[':search3'] = $searchVal;
        }

        if ($authorId) {
            $whereConditions[] = "u.id = :author_id";
            $sqlParams[':author_id'] = $authorId;
        }

        if ($startDate !== '') {
            $whereConditions[] = "DATE(p.created_at) >= :start_date";
            $sqlParams[':start_date'] = $startDate;
        }

        if ($endDate !== '') {
            $whereConditions[] = "DATE(p.created_at) <= :end_date";
            $sqlParams[':end_date'] = $endDate;
        }

        if ($contentType === 'text') {
            $whereConditions[] = "(p.image IS NULL OR p.image = '')";
        } elseif ($contentType === 'image') {
            $whereConditions[] = "(p.image IS NOT NULL AND p.image != '')";
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        return [$whereClause, $sqlParams];
    }

    /**
     * Get Filtered Summary Metrics for Summary Cards.
     *
     * @param array $params
     * @return array
     */
    public function getFilteredSummaryMetrics($params = []) {
        list($whereClause, $sqlParams) = $this->buildReportWhereClause($params);

        $sql = "SELECT 
                    (SELECT COUNT(*) FROM users) AS total_users,
                    COUNT(DISTINCT p.id) AS total_posts,
                    COUNT(DISTINCT c.id) AS total_comments,
                    COUNT(DISTINCT l.id) AS total_likes
                FROM users u
                LEFT JOIN posts p ON u.id = p.user_id
                LEFT JOIN comments c ON p.id = c.post_id
                LEFT JOIN likes l ON p.id = l.post_id
                {$whereClause}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($sqlParams);
        $row = $stmt->fetch();

        return [
            'total_users'    => (int)($row['total_users'] ?? 0),
            'total_posts'    => (int)($row['total_posts'] ?? 0),
            'total_comments' => (int)($row['total_comments'] ?? 0),
            'total_likes'    => (int)($row['total_likes'] ?? 0)
        ];
    }

    /**
     * Report B: Posts Grouped by Author.
     *
     * @param array $params
     * @return array
     */
    public function getPostsGroupedByAuthor($params = []) {
        list($whereClause, $sqlParams) = $this->buildReportWhereClause($params);

        $sql = "SELECT 
                    u.id AS user_id,
                    u.full_name,
                    u.username,
                    u.profile_image,
                    COUNT(DISTINCT p.id) AS post_count,
                    MAX(p.created_at) AS latest_post_date
                FROM users u
                LEFT JOIN posts p ON u.id = p.user_id
                {$whereClause}
                GROUP BY u.id, u.full_name, u.username, u.profile_image
                ORDER BY post_count DESC, u.username ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($sqlParams);
        return $stmt->fetchAll();
    }

    /**
     * Report C: Most Active Community Members.
     *
     * @param array $params
     * @param int $limit
     * @return array
     */
    public function getMostActiveMembers($params = [], $limit = 5) {
        list($whereClause, $sqlParams) = $this->buildReportWhereClause($params);

        $sql = "SELECT 
                    u.id AS user_id,
                    u.full_name,
                    u.username,
                    u.profile_image,
                    COUNT(DISTINCT p.id) AS total_posts,
                    MAX(p.created_at) AS last_post_at
                FROM users u
                JOIN posts p ON u.id = p.user_id
                {$whereClause}
                GROUP BY u.id, u.full_name, u.username, u.profile_image
                ORDER BY total_posts DESC, u.username ASC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($sqlParams as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Report D: Post Activity Within Selected Date Range (Timeline Breakdown).
     *
     * @param array $params
     * @return array
     */
    public function getPostActivityTimeline($params = []) {
        list($whereClause, $sqlParams) = $this->buildReportWhereClause($params);

        $sql = "SELECT 
                    DATE(p.created_at) AS post_date,
                    COUNT(DISTINCT p.id) AS daily_posts,
                    COUNT(DISTINCT p.user_id) AS active_authors
                FROM posts p
                JOIN users u ON p.user_id = u.id
                {$whereClause}
                GROUP BY DATE(p.created_at)
                ORDER BY post_date DESC
                LIMIT 15";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($sqlParams);
        return $stmt->fetchAll();
    }

    /**
     * Report E: Detailed Report Table (Author detailed metrics with allowlisted sorting).
     *
     * @param array $params
     * @return array
     */
    public function getDetailedReportTable($params = []) {
        list($whereClause, $sqlParams) = $this->buildReportWhereClause($params);
        $reportSort = trim($params['report_sort'] ?? 'posts_desc');

        switch ($reportSort) {
            case 'posts_asc':
                $orderBy = 'post_count ASC, u.username ASC';
                break;
            case 'likes_desc':
                $orderBy = 'total_likes DESC, post_count DESC, u.username ASC';
                break;
            case 'comments_desc':
                $orderBy = 'total_comments DESC, post_count DESC, u.username ASC';
                break;
            case 'username_asc':
                $orderBy = 'u.username ASC';
                break;
            case 'posts_desc':
            default:
                $orderBy = 'post_count DESC, total_likes DESC, u.username ASC';
                break;
        }

        $sql = "SELECT 
                    u.id AS user_id,
                    u.full_name,
                    u.username,
                    u.profile_image,
                    COUNT(DISTINCT p.id) AS post_count,
                    MAX(p.created_at) AS latest_post_date,
                    COUNT(DISTINCT l.id) AS total_likes,
                    COUNT(DISTINCT c.id) AS total_comments
                FROM users u
                LEFT JOIN posts p ON u.id = p.user_id
                LEFT JOIN likes l ON p.id = l.post_id
                LEFT JOIN comments c ON p.id = c.post_id
                {$whereClause}
                GROUP BY u.id, u.full_name, u.username, u.profile_image
                ORDER BY {$orderBy}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($sqlParams);
        return $stmt->fetchAll();
    }
}
