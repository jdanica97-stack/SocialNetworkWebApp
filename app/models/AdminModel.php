<?php
// app/models/AdminModel.php
// Model handling all database queries and administrative operations for the Admin Dashboard

require_once __DIR__ . '/../../config/database.php';

class AdminModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Retrieve summary statistics for the Admin Dashboard.
     * Demonstrates SQL Subqueries and COUNT() Aggregate Functions.
     *
     * @return array
     */
    public function getSummaryCounts() {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM users) AS total_users,
                    (SELECT COUNT(*) FROM users WHERE role = 'admin') AS admin_users,
                    (SELECT COUNT(*) FROM users WHERE role = 'user') AS normal_users,
                    (SELECT COUNT(*) FROM posts) AS total_posts,
                    (SELECT COUNT(*) FROM comments) AS total_comments,
                    (SELECT COUNT(*) FROM likes) AS total_likes";
        
        $stmt = $this->db->query($sql);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalUsers = (int)($summary['total_users'] ?? 0);
        $totalPosts = (int)($summary['total_posts'] ?? 0);
        $totalComments = (int)($summary['total_comments'] ?? 0);

        $summary['avg_posts_per_user'] = $totalUsers > 0 ? round($totalPosts / $totalUsers, 2) : 0;
        $summary['avg_comments_per_post'] = $totalPosts > 0 ? round($totalComments / $totalPosts, 2) : 0;

        return $summary;
    }

    /**
     * Retrieve paginated list of registered users with post & comment aggregates.
     * Demonstrates: SELECT, WHERE, LEFT JOIN, GROUP BY, COUNT(DISTINCT), ORDER BY, LIMIT, OFFSET.
     * Note: Password hashes and sensitive tokens are strictly excluded from the projection.
     *
     * @param string $search Search query for username or full name
     * @param string $roleFilter Optional role filter ('admin', 'user', or '')
     * @param int $page Current page number
     * @param int $perPage Items per page
     * @return array
     */
    public function getPaginatedUsers($search = '', $roleFilter = '', $page = 1, $perPage = 10) {
        $offset = max(0, ($page - 1) * $perPage);
        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(u.username LIKE :search1 OR u.full_name LIKE :search2)";
            $searchVal = '%' . trim($search) . '%';
            $params[':search1'] = $searchVal;
            $params[':search2'] = $searchVal;
        }

        if (!empty($roleFilter) && in_array($roleFilter, ['admin', 'user'], true)) {
            $whereConditions[] = "u.role = :role";
            $params[':role'] = $roleFilter;
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        $sql = "SELECT 
                    u.id, 
                    u.username, 
                    u.full_name, 
                    u.bio, 
                    u.profile_image, 
                    u.role, 
                    u.created_at,
                    COUNT(DISTINCT p.id) AS post_count,
                    COUNT(DISTINCT c.id) AS comment_count
                FROM users u
                LEFT JOIN posts p ON u.id = p.user_id
                LEFT JOIN comments c ON u.id = c.user_id
                {$whereClause}
                GROUP BY u.id, u.username, u.full_name, u.bio, u.profile_image, u.role, u.created_at
                ORDER BY u.created_at DESC, u.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of users matching search/filter for pagination calculations.
     *
     * @param string $search
     * @param string $roleFilter
     * @return int
     */
    public function getUsersCount($search = '', $roleFilter = '') {
        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(username LIKE :search1 OR full_name LIKE :search2)";
            $searchVal = '%' . trim($search) . '%';
            $params[':search1'] = $searchVal;
            $params[':search2'] = $searchVal;
        }

        if (!empty($roleFilter) && in_array($roleFilter, ['admin', 'user'], true)) {
            $whereConditions[] = "role = :role";
            $params[':role'] = $roleFilter;
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        $sql = "SELECT COUNT(*) FROM users {$whereClause}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Retrieve paginated list of posts across the platform.
     * Demonstrates: SELECT, JOIN, LEFT JOIN, COUNT(DISTINCT), GROUP BY, ORDER BY.
     *
     * @param string $search Keyword search in post content or author info
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getPaginatedPosts($search = '', $page = 1, $perPage = 10) {
        $offset = max(0, ($page - 1) * $perPage);
        $whereClause = "";
        $params = [];

        if (!empty($search)) {
            $whereClause = "WHERE (p.content LIKE :search1 OR u.username LIKE :search2 OR u.full_name LIKE :search3)";
            $searchVal = '%' . trim($search) . '%';
            $params[':search1'] = $searchVal;
            $params[':search2'] = $searchVal;
            $params[':search3'] = $searchVal;
        }

        $sql = "SELECT 
                    p.id AS post_id,
                    p.content,
                    p.image,
                    p.created_at,
                    u.id AS author_id,
                    u.username AS author_username,
                    u.full_name AS author_fullname,
                    u.role AS author_role,
                    COUNT(DISTINCT c.id) AS comment_count,
                    COUNT(DISTINCT l.id) AS like_count
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN comments c ON p.id = c.post_id
                LEFT JOIN likes l ON p.id = l.post_id
                {$whereClause}
                GROUP BY p.id, p.content, p.image, p.created_at, u.id, u.username, u.full_name, u.role
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of posts matching search for pagination calculations.
     *
     * @param string $search
     * @return int
     */
    public function getPostsCount($search = '') {
        $whereClause = "";
        $params = [];

        if (!empty($search)) {
            $whereClause = "WHERE (p.content LIKE :search1 OR u.username LIKE :search2 OR u.full_name LIKE :search3)";
            $searchVal = '%' . trim($search) . '%';
            $params[':search1'] = $searchVal;
            $params[':search2'] = $searchVal;
            $params[':search3'] = $searchVal;
        }

        $sql = "SELECT COUNT(DISTINCT p.id) FROM posts p JOIN users u ON p.user_id = u.id {$whereClause}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Retrieve paginated list of comments for moderation.
     * Demonstrates: SELECT, JOIN (combining comments, commenter, post, post author).
     *
     * @param string $search Search query for comment content, commenter name, or post content
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getPaginatedComments($search = '', $page = 1, $perPage = 10) {
        $offset = max(0, ($page - 1) * $perPage);
        $whereClause = "";
        $params = [];

        if (!empty($search)) {
            $whereClause = "WHERE (c.content LIKE :search1 OR u.username LIKE :search2 OR u.full_name LIKE :search3 OR p.content LIKE :search4)";
            $searchVal = '%' . trim($search) . '%';
            $params[':search1'] = $searchVal;
            $params[':search2'] = $searchVal;
            $params[':search3'] = $searchVal;
            $params[':search4'] = $searchVal;
        }

        $sql = "SELECT 
                    c.id AS comment_id,
                    c.content AS comment_content,
                    c.created_at AS comment_date,
                    c.post_id,
                    u.id AS commenter_id,
                    u.username AS commenter_username,
                    u.full_name AS commenter_fullname,
                    u.role AS commenter_role,
                    p.content AS post_content,
                    pu.username AS post_author_username
                FROM comments c
                JOIN users u ON c.user_id = u.id
                JOIN posts p ON c.post_id = p.id
                JOIN users pu ON p.user_id = pu.id
                {$whereClause}
                ORDER BY c.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of comments for pagination.
     *
     * @param string $search
     * @return int
     */
    public function getCommentsCount($search = '') {
        $whereClause = "";
        $params = [];

        if (!empty($search)) {
            $whereClause = "WHERE (c.content LIKE :search1 OR u.username LIKE :search2 OR u.full_name LIKE :search3 OR p.content LIKE :search4)";
            $searchVal = '%' . trim($search) . '%';
            $params[':search1'] = $searchVal;
            $params[':search2'] = $searchVal;
            $params[':search3'] = $searchVal;
            $params[':search4'] = $searchVal;
        }

        $sql = "SELECT COUNT(*) FROM comments c 
                JOIN users u ON c.user_id = u.id 
                JOIN posts p ON c.post_id = p.id 
                {$whereClause}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Activity Stats: Most active users by post count.
     *
     * @param int $limit
     * @return array
     */
    public function getMostActiveUsers($limit = 5) {
        $sql = "SELECT 
                    u.id AS user_id,
                    u.username,
                    u.full_name,
                    u.role,
                    COUNT(p.id) AS total_posts,
                    MAX(p.created_at) AS last_post_at
                FROM users u
                JOIN posts p ON u.id = p.user_id
                GROUP BY u.id, u.username, u.full_name, u.role
                ORDER BY total_posts DESC, u.username ASC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Activity Stats: Most liked posts across platform.
     *
     * @param int $limit
     * @return array
     */
    public function getMostLikedPosts($limit = 5) {
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Administrative Moderation: Delete a post by admin.
     *
     * @param int $postId
     * @return bool
     */
    public function deletePostByAdmin($postId) {
        // Fetch post image to remove file if present
        $stmt = $this->db->prepare("SELECT image FROM posts WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post && !empty($post['image'])) {
            require_once __DIR__ . '/PostModel.php';
            $images = PostModel::parseImages($post['image']);
            foreach ($images as $img) {
                $imagePath = __DIR__ . '/../../public/uploads/posts/' . basename($img);
                if (file_exists($imagePath) && is_file($imagePath)) {
                    @unlink($imagePath);
                }
            }
        }

        $delStmt = $this->db->prepare("DELETE FROM posts WHERE id = :id");
        return $delStmt->execute([':id' => $postId]);
    }

    /**
     * Administrative Moderation: Delete a comment by admin.
     *
     * @param int $commentId
     * @return bool
     */
    public function deleteCommentByAdmin($commentId) {
        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = :id");
        $stmt->execute([':id' => $commentId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Administrative User Management: Update a user's role.
     * Safeguards against demoting oneself if current admin or demoting last admin.
     *
     * @param int $userId
     * @param string $newRole 'admin' or 'user'
     * @param int $currentAdminId Currently logged in admin ID
     * @return array Res: ['success' => bool, 'message' => string]
     */
    public function updateUserRole($userId, $newRole, $currentAdminId) {
        if (!in_array($newRole, ['admin', 'user'], true)) {
            return ['success' => false, 'message' => 'Invalid role specified.'];
        }

        if ($userId === $currentAdminId && $newRole !== 'admin') {
            return ['success' => false, 'message' => 'Security restriction: You cannot demote your own active administrator account.'];
        }

        // Check if target is the last admin being demoted
        if ($newRole === 'user') {
            $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            $adminCount = (int)$stmt->fetchColumn();
            
            $targetStmt = $this->db->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
            $targetStmt->execute([':id' => $userId]);
            $targetRole = $targetStmt->fetchColumn();

            if ($targetRole === 'admin' && $adminCount <= 1) {
                return ['success' => false, 'message' => 'Action denied: System requires at least one active administrator account.'];
            }
        }

        $updateStmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
        $updated = $updateStmt->execute([':role' => $newRole, ':id' => $userId]);

        if ($updated) {
            return ['success' => true, 'message' => 'User role successfully updated to ' . ucfirst($newRole) . '.'];
        }

        return ['success' => false, 'message' => 'Failed to update user role. Please try again.'];
    }

    /**
     * Administrative User Management: Delete a user account safely.
     * Safeguards against deleting oneself while logged in as admin.
     *
     * @param int $userId
     * @param int $currentAdminId
     * @return array Res: ['success' => bool, 'message' => string]
     */
    public function deleteUserByAdmin($userId, $currentAdminId) {
        if ($userId === $currentAdminId) {
            return ['success' => false, 'message' => 'Security restriction: You cannot delete your own active administrator account while logged in.'];
        }

        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser) {
            return ['success' => false, 'message' => 'Target user account not found.'];
        }

        // Prevent deleting the last admin
        if ($targetUser['role'] === 'admin') {
            $countStmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            $adminCount = (int)$countStmt->fetchColumn();
            if ($adminCount <= 1) {
                return ['success' => false, 'message' => 'Action denied: Cannot delete the sole remaining administrator account.'];
            }
        }

        $delStmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $deleted = $delStmt->execute([':id' => $userId]);

        if ($deleted && $delStmt->rowCount() > 0) {
            // Automatically resequence user IDs so there are no missing IDs/gaps,
            // and ensure AUTO_INCREMENT is reset to the next consecutive number (or 1 if empty)
            $this->reorderUserIds($currentAdminId);

            return ['success' => true, 'message' => 'User account has been removed and database IDs have been synchronized successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to delete user account.'];
    }

    /**
     * Resequence user IDs consecutively starting from 1 (1, 2, 3...)
     * and reset AUTO_INCREMENT to MAX(id) + 1 (or 1 if table is empty).
     * All child tables (posts, comments, likes, follows, events)
     * automatically update via ON UPDATE CASCADE.
     *
     * @param int|null $currentAdminId If provided and current admin's ID changes, updates session.
     * @return bool
     */
    public function reorderUserIds($currentAdminId = null) {
        $users = $this->db->query("SELECT id FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($users)) {
            $this->db->exec("ALTER TABLE users AUTO_INCREMENT = 1");
            return true;
        }

        // Check if IDs are already 1..N with no gaps
        $isSequential = true;
        foreach ($users as $index => $id) {
            if ((int)$id !== ($index + 1)) {
                $isSequential = false;
                break;
            }
        }

        if ($isSequential) {
            $nextId = count($users) + 1;
            $this->db->exec("ALTER TABLE users AUTO_INCREMENT = {$nextId}");
            return true;
        }

        try {
            $this->db->beginTransaction();

            // Step 1: Shift all IDs by a large offset to prevent primary key collision
            $offset = 1000000;
            $this->db->exec("UPDATE users SET id = id + {$offset} ORDER BY id DESC");

            // Step 2: Assign sequential IDs starting from 1
            $shiftedUsers = $this->db->query("SELECT id FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
            $newId = 1;
            $updateStmt = $this->db->prepare("UPDATE users SET id = :new_id WHERE id = :old_id");

            $newAdminId = null;
            foreach ($shiftedUsers as $oldId) {
                $originalId = (int)$oldId - $offset;
                $updateStmt->execute([
                    ':new_id' => $newId,
                    ':old_id' => $oldId
                ]);
                if ($currentAdminId !== null && (int)$originalId === (int)$currentAdminId) {
                    $newAdminId = $newId;
                }
                $newId++;
            }

            $this->db->commit();

            // Step 3: Reset AUTO_INCREMENT to next sequential number
            $this->db->exec("ALTER TABLE users AUTO_INCREMENT = {$newId}");

            // Update session if logged-in admin ID changed
            if ($newAdminId !== null && isset($_SESSION['user_id'])) {
                $_SESSION['user_id'] = $newAdminId;
            }

            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Reorder user IDs error: " . $e->getMessage());
            return false;
        }
    }
}
