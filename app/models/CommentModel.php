<?php
// app/models/CommentModel.php
// Model handling all database operations for comments

require_once __DIR__ . '/../../config/database.php';

class CommentModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Create a new comment.
     *
     * @param int $postId
     * @param int $userId
     * @param string $content
     * @return int|bool The new comment ID or false on failure
     */
    public function createComment($postId, $userId, $content) {
        $sql = "INSERT INTO comments (post_id, user_id, content, created_at) 
                VALUES (:post_id, :user_id, :content, NOW())";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId,
            ':content' => $content
        ]);

        return $success ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Get all comments for a specific post with commenter details.
     *
     * @param int $postId
     * @return array
     */
    public function getCommentsByPost($postId) {
        $sql = "SELECT c.id, c.post_id, c.user_id, c.content, c.created_at,
                       u.username, u.full_name, u.profile_image
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.post_id = :post_id
                ORDER BY c.created_at ASC, c.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':post_id' => $postId]);
        return $stmt->fetchAll();
    }

    /**
     * Get a single comment by ID with commenter and parent post info.
     *
     * @param int $commentId
     * @return array|false
     */
    public function getCommentById($commentId) {
        $sql = "SELECT c.id, c.post_id, c.user_id, c.content, c.created_at,
                       u.username, u.full_name, u.profile_image,
                       p.content AS post_content, p.user_id AS post_user_id, p.created_at AS post_created_at,
                       pu.username AS post_author_username, pu.full_name AS post_author_name
                FROM comments c
                JOIN users u ON c.user_id = u.id
                JOIN posts p ON c.post_id = p.id
                JOIN users pu ON p.user_id = pu.id
                WHERE c.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $commentId]);
        return $stmt->fetch();
    }

    /**
     * Update an existing comment (enforces ownership check in SQL).
     *
     * @param int $commentId
     * @param int $userId
     * @param string $content
     * @return bool
     */
    public function updateComment($commentId, $userId, $content) {
        $sql = "UPDATE comments 
                SET content = :content 
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        $executed = $stmt->execute([
            ':content' => $content,
            ':id'      => $commentId,
            ':user_id' => $userId
        ]);
        return $executed && $stmt->rowCount() > 0;
    }

    /**
     * Delete an existing comment (enforces ownership check in SQL).
     *
     * @param int $commentId
     * @param int $userId
     * @return bool
     */
    public function deleteComment($commentId, $userId) {
        $sql = "DELETE FROM comments 
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id'      => $commentId,
            ':user_id' => $userId
        ]);

        return $stmt->rowCount() > 0;
    }
}
