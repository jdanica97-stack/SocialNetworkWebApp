<?php
// app/models/NotificationModel.php
// Handles all user activity notifications (likes, comments, follows)

require_once __DIR__ . '/../../config/database.php';

class NotificationModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Create a notification. Avoids creating self-notifications.
     *
     * @param int $userId Target user who receives notification
     * @param int $actorId User performing the action
     * @param string $type 'like', 'comment', 'follow'
     * @param int|null $entityId Post ID or related object ID
     * @param string $message Descriptive text
     * @return bool
     */
    public function createNotification($userId, $actorId, $type, $entityId, $message) {
        $userId = (int)$userId;
        $actorId = (int)$actorId;

        // Never notify someone about their own actions
        if ($userId <= 0 || $actorId <= 0 || $userId === $actorId) {
            return false;
        }

        // Avoid spamming duplicate unread notifications for like or follow
        if (in_array($type, ['like', 'follow'], true)) {
            $checkSql = "SELECT id FROM notifications 
                         WHERE user_id = :user_id AND actor_id = :actor_id AND type = :type";
            $params = [
                ':user_id'  => $userId,
                ':actor_id' => $actorId,
                ':type'     => $type
            ];
            if ($entityId !== null) {
                $checkSql .= " AND entity_id = :entity_id";
                $params[':entity_id'] = (int)$entityId;
            }
            $checkSql .= " AND is_read = 0 LIMIT 1";

            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute($params);
            if ($checkStmt->fetch()) {
                return true; // Already notified recently
            }
        }

        $sql = "INSERT INTO notifications (user_id, actor_id, type, entity_id, message, is_read, created_at)
                VALUES (:user_id, :actor_id, :type, :entity_id, :message, 0, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id'   => $userId,
            ':actor_id'  => $actorId,
            ':type'      => $type,
            ':entity_id' => $entityId ? (int)$entityId : null,
            ':message'   => $message
        ]);
    }

    /**
     * Get unread notifications count for a specific user.
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => (int)$userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get recent notifications for a user with actor metadata.
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUserNotifications($userId, $limit = 20) {
        $sql = "SELECT n.*, u.username AS actor_username, u.full_name AS actor_name, u.profile_image AS actor_image
                FROM notifications n
                JOIN users u ON n.actor_id = u.id
                WHERE n.user_id = :user_id
                ORDER BY n.created_at DESC
                LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => (int)$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Mark a single notification or all user notifications as read.
     *
     * @param int $userId
     * @param int|null $notificationId
     * @return bool
     */
    public function markAsRead($userId, $notificationId = null) {
        $userId = (int)$userId;
        if ($notificationId !== null) {
            $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id'      => (int)$notificationId,
                ':user_id' => $userId
            ]);
        } else {
            $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);
        }
    }
}
