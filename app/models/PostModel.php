<?php
// app/models/PostModel.php
// Model handling all database operations for posts, comments, and likes

require_once __DIR__ . '/../../config/database.php';

class PostModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Get all posts with author info, like counts, and comment counts.
     *
     * @param int|null $currentUserId To check if the current user liked each post
     * @param string $order 'newest' or 'oldest'
     * @return array
     */
    public function getAllPosts($currentUserId = null, $order = 'newest') {
        $orderBy = ($order === 'oldest') ? 'p.created_at ASC, p.id ASC' : 'p.created_at DESC, p.id DESC';
        $sql = "SELECT p.*, 
                       u.username, 
                       u.full_name, 
                       u.profile_image,
                       COUNT(DISTINCT l.id) AS likes_count,
                       COUNT(DISTINCT c.id) AS comments_count,
                       MAX(CASE WHEN l.user_id = :current_user_id THEN 1 ELSE 0 END) AS is_liked
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN likes l ON p.id = l.post_id
                LEFT JOIN comments c ON p.id = c.post_id
                GROUP BY p.id
                ORDER BY {$orderBy}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':current_user_id' => $currentUserId ?? 0]);
        $posts = $stmt->fetchAll();

        // Attach comments and parsed images to each post
        foreach ($posts as &$post) {
            $post['images'] = self::parseImages($post['image'] ?? null);
            $post['comments'] = $this->getCommentsByPostId($post['id']);
        }

        return $posts;
    }

    /**
     * Get all posts created by a specific user with author info, like counts, and comment counts.
     *
     * @param int $userId Profile user ID whose posts to retrieve
     * @param int|null $currentUserId Currently logged-in user ID (to check if they liked the post)
     * @return array
     */
    public function getPostsByUserId($userId, $currentUserId = null) {
        $sql = "SELECT p.*, 
                       u.username, 
                       u.full_name, 
                       u.profile_image,
                       COUNT(DISTINCT l.id) AS likes_count,
                       COUNT(DISTINCT c.id) AS comments_count,
                       MAX(CASE WHEN l.user_id = :current_user_id THEN 1 ELSE 0 END) AS is_liked
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN likes l ON p.id = l.post_id
                LEFT JOIN comments c ON p.id = c.post_id
                WHERE p.user_id = :user_id
                GROUP BY p.id
                ORDER BY p.created_at DESC, p.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id'         => $userId,
            ':current_user_id' => $currentUserId ?? 0
        ]);
        $posts = $stmt->fetchAll();

        // Attach comments and parsed images to each post
        foreach ($posts as &$post) {
            $post['images'] = self::parseImages($post['image'] ?? null);
            $post['comments'] = $this->getCommentsByPostId($post['id']);
        }

        return $posts;
    }

    /**
     * Get all comments for a specific post.
     *
     * @param int $postId
     * @return array
     */
    public function getCommentsByPostId($postId) {
        require_once __DIR__ . '/CommentModel.php';
        $commentModel = new CommentModel();
        return $commentModel->getCommentsByPost($postId);
    }

    /**
     * Parse an image column into a clean array of image filenames.
     * Supports JSON array string, comma-separated string, PHP array, or single filename string.
     *
     * @param mixed $imageField
     * @return array
     */
    public static function parseImages($imageField) {
        if (empty($imageField)) {
            return [];
        }
        if (is_array($imageField)) {
            return array_values(array_filter($imageField));
        }
        $trimmed = trim((string)$imageField);
        if ($trimmed === '' || $trimmed === '[]' || $trimmed === '{}') {
            return [];
        }
        $first = substr($trimmed, 0, 1);
        $last  = substr($trimmed, -1);
        if (($first === '[' && $last === ']') || ($first === '{' && $last === '}')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                return array_values(array_filter($decoded, function($img) {
                    return !empty($img) && is_string($img);
                }));
            }
        }
        if (strpos($trimmed, ',') !== false) {
            $parts = array_map('trim', explode(',', $trimmed));
            return array_values(array_filter($parts));
        }
        return [$trimmed];
    }

    /**
     * Create a new post.
     *
     * @param int $userId
     * @param string $content
     * @param string|array|null $image Single filename or array of filenames
     * @return int|bool
     */
    public function createPost($userId, $content, $image = null) {
        $imageValue = null;
        if (is_array($image)) {
            $clean = array_values(array_filter($image));
            if (count($clean) === 1) {
                $imageValue = $clean[0];
            } elseif (count($clean) > 1) {
                $imageValue = json_encode($clean);
            }
        } elseif (!empty($image)) {
            $imageValue = (string)$image;
        }

        $sql = "INSERT INTO posts (user_id, content, image, created_at) VALUES (:user_id, :content, :image, NOW())";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':user_id' => $userId,
            ':content' => $content,
            ':image'   => $imageValue
        ]);

        return $success ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Add a comment to a post.
     *
     * @param int $postId
     * @param int $userId
     * @param string $content
     * @return bool
     */
    public function addComment($postId, $userId, $content) {
        require_once __DIR__ . '/CommentModel.php';
        $commentModel = new CommentModel();
        return (bool)$commentModel->createComment($postId, $userId, $content);
    }

    /**
     * Toggle like on a post.
     *
     * @param int $postId
     * @param int $userId
     * @return bool True if liked, False if unliked
     */
    public function toggleLike($postId, $userId) {
        $checkSql = "SELECT id FROM likes WHERE post_id = :post_id AND user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($checkSql);
        $stmt->execute([':post_id' => $postId, ':user_id' => $userId]);
        $like = $stmt->fetch();

        if ($like) {
            $delSql = "DELETE FROM likes WHERE id = :id";
            $delStmt = $this->db->prepare($delSql);
            $delStmt->execute([':id' => $like['id']]);
            return false;
        } else {
            $insSql = "INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)";
            $insStmt = $this->db->prepare($insSql);
            $insStmt->execute([':post_id' => $postId, ':user_id' => $userId]);
            return true;
        }
    }

    /**
     * Get the total likes count for a specific post.
     *
     * @param int $postId
     * @return int
     */
    public function getLikesCount($postId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id");
        $stmt->execute([':post_id' => $postId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get list of users who liked/reacted to a specific post.
     *
     * @param int $postId
     * @param int|null $currentUserId
     * @return array
     */
    public function getPostLikers($postId, $currentUserId = null) {
        $postId = (int)$postId;
        $currentUserId = $currentUserId ? (int)$currentUserId : 0;

        if ($postId <= 0) {
            return [];
        }

        if ($currentUserId > 0) {
            $sql = "SELECT u.id, u.username, u.full_name, u.profile_image, u.bio,
                           (SELECT COUNT(*) FROM follows WHERE follower_id = :current_user_id1 AND following_id = u.id) AS is_following,
                           (SELECT COUNT(*) FROM follows WHERE follower_id = u.id AND following_id = :current_user_id2) AS is_followed_by
                    FROM likes l
                    JOIN users u ON l.user_id = u.id
                    WHERE l.post_id = :post_id
                    ORDER BY l.id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':post_id' => $postId,
                ':current_user_id1' => $currentUserId,
                ':current_user_id2' => $currentUserId
            ]);
        } else {
            $sql = "SELECT u.id, u.username, u.full_name, u.profile_image, u.bio,
                           0 AS is_following,
                           0 AS is_followed_by
                    FROM likes l
                    JOIN users u ON l.user_id = u.id
                    WHERE l.post_id = :post_id
                    ORDER BY l.id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':post_id' => $postId]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a post (only allowed if post belongs to user).
     *
     * @param int $postId
     * @param int $userId
     * @return bool
     */
    public function deletePost($postId, $userId) {
        $sql = "DELETE FROM posts WHERE id = :post_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':post_id' => $postId, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get a post by its ID (including author information).
     *
     * @param int $postId
     * @return array|false
     */
    public function getPostById($postId) {
        $sql = "SELECT p.*, 
                       u.username, 
                       u.full_name, 
                       u.profile_image
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = :id 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $postId]);
        $post = $stmt->fetch();
        if ($post) {
            $post['images'] = self::parseImages($post['image'] ?? null);
        }
        return $post;
    }

    /**
     * Get a single post with author info, like counts, comment counts, and attached comments.
     *
     * @param int $postId
     * @param int|null $currentUserId
     * @return array|false
     */
    public function getPostWithDetails($postId, $currentUserId = null) {
        $sql = "SELECT p.*, 
                       u.username, 
                       u.full_name, 
                       u.profile_image,
                       COUNT(DISTINCT l.id) AS likes_count,
                       COUNT(DISTINCT c.id) AS comments_count,
                       MAX(CASE WHEN l.user_id = :current_user_id THEN 1 ELSE 0 END) AS is_liked
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN likes l ON p.id = l.post_id
                LEFT JOIN comments c ON p.id = c.post_id
                WHERE p.id = :id
                GROUP BY p.id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => (int)$postId,
            ':current_user_id' => $currentUserId ?? 0
        ]);
        $post = $stmt->fetch();

        if ($post) {
            $post['images'] = self::parseImages($post['image'] ?? null);
            $post['comments'] = $this->getCommentsByPostId($post['id']);
        }

        return $post;
    }

    /**
     * Update an existing post (only if user is author).
     *
     * @param int $postId
     * @param int $userId
     * @param string $content
     * @param string|array|null $image
     * @return bool
     */
    public function updatePost($postId, $userId, $content, $image = null) {
        // Verify ownership first
        $checkStmt = $this->db->prepare("SELECT id FROM posts WHERE id = :post_id AND user_id = :user_id");
        $checkStmt->execute([':post_id' => $postId, ':user_id' => $userId]);
        if (!$checkStmt->fetch()) {
            return false;
        }

        $imageValue = null;
        if (is_array($image)) {
            $clean = array_values(array_filter($image));
            if (count($clean) === 1) {
                $imageValue = $clean[0];
            } elseif (count($clean) > 1) {
                $imageValue = json_encode($clean);
            }
        } elseif (!empty($image)) {
            $imageValue = (string)$image;
        }

        $sql = "UPDATE posts 
                SET content = :content, image = :image 
                WHERE id = :post_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $executed = $stmt->execute([
            ':content' => $content,
            ':image'   => $imageValue,
            ':post_id' => $postId,
            ':user_id' => $userId
        ]);
        return $executed;
    }

    /**
     * Search posts by content with optional author filtering, sorting, and engagement metrics.
     * Demonstrates SQL JOIN, Aggregate functions (COUNT), GROUP BY, ORDER BY, and Subqueries.
     *
     * @param string $query Search keyword
     * @param string $filter Sort order: 'newest', 'oldest', 'most_liked', 'most_commented'
     * @param int|null $currentUserId Currently logged-in user ID
     * @param int|null $authorId Optional user ID to filter posts by author
     * @return array
     */
    public function searchPosts($query = '', $filter = 'newest', $currentUserId = null, $authorId = null) {
        $whereConditions = [];
        $params = [];

        if (!empty($currentUserId)) {
            $params[':current_user_id'] = (int)$currentUserId;
        } else {
            $params[':current_user_id'] = 0;
        }

        if (trim($query) !== '') {
            $whereConditions[] = "p.content LIKE :search_term";
            $params[':search_term'] = '%' . trim($query) . '%';
        }

        if (!empty($authorId)) {
            $whereConditions[] = "p.user_id = :author_id";
            $params[':author_id'] = (int)$authorId;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // Determine sorting order based on filter parameter (ORDER BY)
        switch ($filter) {
            case 'oldest':
                $orderBy = 'p.created_at ASC, p.id ASC';
                break;
            case 'most_liked':
                $orderBy = 'likes_count DESC, p.created_at DESC, p.id DESC';
                break;
            case 'most_commented':
                $orderBy = 'comments_count DESC, p.created_at DESC, p.id DESC';
                break;
            case 'newest':
            default:
                $orderBy = 'p.created_at DESC, p.id DESC';
                break;
        }

        // SQL Query demonstrating:
        // 1. JOIN (JOIN users u ON p.user_id = u.id, LEFT JOIN likes l, LEFT JOIN comments c)
        // 2. Aggregate functions (COUNT(DISTINCT l.id), COUNT(DISTINCT c.id))
        // 3. Subquery for user like status (SELECT COUNT(*) FROM likes ...)
        // 4. GROUP BY (GROUP BY p.id)
        // 5. ORDER BY ($orderBy)
        $sql = "SELECT p.*, 
                       u.username, 
                       u.full_name, 
                       u.profile_image,
                       COUNT(DISTINCT l.id) AS likes_count,
                       COUNT(DISTINCT c.id) AS comments_count,
                       (SELECT COUNT(*) FROM likes l_sub WHERE l_sub.post_id = p.id AND l_sub.user_id = :current_user_id) > 0 AS is_liked
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN likes l ON p.id = l.post_id
                LEFT JOIN comments c ON p.id = c.post_id
                {$whereClause}
                GROUP BY p.id, p.user_id, p.content, p.image, p.created_at, u.username, u.full_name, u.profile_image
                ORDER BY {$orderBy}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        // Attach comments and parsed images to each post
        foreach ($posts as &$post) {
            $post['images'] = self::parseImages($post['image'] ?? null);
            $post['comments'] = $this->getCommentsByPostId($post['id']);
        }

        return $posts;
    }

    /**
     * Calculate summary statistics for matching posts and filters.
     * Demonstrates SQL JOIN, Aggregate functions (COUNT DISTINCT), and Subqueries.
     * Avoids double-counting comments and likes.
     *
     * @param string $query Search keyword
     * @param int|null $authorId Optional user ID filter
     * @return array
     */
    public function getSearchSummaryStats($query = '', $authorId = null) {
        $whereConditions = [];
        $params = [];

        if (trim($query) !== '') {
            $whereConditions[] = "p.content LIKE :search_term";
            $params[':search_term'] = '%' . trim($query) . '%';
        }

        if (!empty($authorId)) {
            $whereConditions[] = "p.user_id = :author_id";
            $params[':author_id'] = (int)$authorId;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // Query using JOIN and Aggregate functions with DISTINCT to prevent duplicate counting
        $sql = "SELECT 
                    COUNT(DISTINCT p.id) AS total_posts,
                    COUNT(DISTINCT p.user_id) AS total_authors,
                    COUNT(DISTINCT c.id) AS total_comments,
                    COUNT(DISTINCT l.id) AS total_likes
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN comments c ON p.id = c.post_id
                LEFT JOIN likes l ON p.id = l.post_id
                {$whereClause}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch();

        return [
            'total_posts'   => (int)($stats['total_posts'] ?? 0),
            'total_authors' => (int)($stats['total_authors'] ?? 0),
            'total_comments'=> (int)($stats['total_comments'] ?? 0),
            'total_likes'   => (int)($stats['total_likes'] ?? 0)
        ];
    }

    /**
     * Advanced Post Search for Community Insights.
     * Supports query, author_id, date range, content_type, sorting, and user like status.
     *
     * @param array $params
     * @return array
     */
    public function searchPostsAdvanced($params = []) {
        $query = trim($params['query'] ?? '');
        $authorId = !empty($params['author_id']) ? (int)$params['author_id'] : null;
        $startDate = trim($params['start_date'] ?? '');
        $endDate = trim($params['end_date'] ?? '');
        $contentType = trim($params['content_type'] ?? 'all');
        $sort = trim($params['sort'] ?? 'newest');
        $currentUserId = (int)($params['current_user_id'] ?? 0);

        $whereConditions = [];
        $sqlParams = [':current_user_id' => $currentUserId];

        if ($query !== '') {
            $whereConditions[] = "(p.content LIKE :search1 OR u.full_name LIKE :search2 OR u.username LIKE :search3)";
            $searchVal = '%' . $query . '%';
            $sqlParams[':search1'] = $searchVal;
            $sqlParams[':search2'] = $searchVal;
            $sqlParams[':search3'] = $searchVal;
        }

        if ($authorId) {
            $whereConditions[] = "p.user_id = :author_id";
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

        switch ($sort) {
            case 'oldest':
                $orderBy = 'p.created_at ASC, p.id ASC';
                break;
            case 'most_liked':
                $orderBy = 'likes_count DESC, p.created_at DESC';
                break;
            case 'most_commented':
                $orderBy = 'comments_count DESC, p.created_at DESC';
                break;
            case 'newest':
            default:
                $orderBy = 'p.created_at DESC, p.id DESC';
                break;
        }

        $sql = "SELECT p.*, 
                       u.username, 
                       u.full_name, 
                       u.profile_image,
                       COUNT(DISTINCT l.id) AS likes_count,
                       COUNT(DISTINCT c.id) AS comments_count,
                       MAX(CASE WHEN l.user_id = :current_user_id THEN 1 ELSE 0 END) AS is_liked
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN likes l ON p.id = l.post_id
                LEFT JOIN comments c ON p.id = c.post_id
                {$whereClause}
                GROUP BY p.id, p.user_id, p.content, p.image, p.created_at, u.username, u.full_name, u.profile_image
                ORDER BY {$orderBy}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($sqlParams);
        $posts = $stmt->fetchAll();

        foreach ($posts as &$post) {
            $post['images'] = self::parseImages($post['image'] ?? null);
            $post['comments'] = $this->getCommentsByPostId($post['id']);
        }

        return $posts;
    }
}
