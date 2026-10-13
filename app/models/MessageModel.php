<?php
// app/models/MessageModel.php
// Handles direct messaging between users (1-on-1 private messaging)

require_once __DIR__ . '/../../config/database.php';

class MessageModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Send a private message.
     *
     * @param int $senderId
     * @param int $receiverId
     * @param string $message
     * @return int|false Returns lastInsertId or false on failure
     */
    public function sendMessage($senderId, $receiverId, $message) {
        $senderId = (int)$senderId;
        $receiverId = (int)$receiverId;
        $message = trim($message);

        if ($senderId <= 0 || $receiverId <= 0 || $senderId === $receiverId || empty($message)) {
            return false;
        }

        $sql = "INSERT INTO messages (sender_id, receiver_id, message, is_read, created_at)
                VALUES (:sender_id, :receiver_id, :message, 0, NOW())";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            ':sender_id'   => $senderId,
            ':receiver_id' => $receiverId,
            ':message'     => $message
        ]);

        return $res ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Get list of conversations for a user with the other user info and last message.
     *
     * @param int $userId
     * @return array
     */
    public function getConversations($userId) {
        $userId = (int)$userId;
        $sql = "SELECT 
                    u.id AS other_user_id,
                    u.username AS other_username,
                    u.full_name AS other_full_name,
                    u.profile_image AS other_profile_image,
                    m.id AS last_message_id,
                    m.message AS last_message,
                    m.created_at AS last_message_time,
                    m.sender_id AS last_sender_id,
                    (SELECT COUNT(*) FROM messages 
                     WHERE sender_id = u.id AND receiver_id = :user_id_unread AND is_read = 0) AS unread_count
                FROM users u
                INNER JOIN messages m ON m.id = (
                    SELECT MAX(m2.id)
                    FROM messages m2
                    WHERE (m2.sender_id = :user_id1 AND m2.receiver_id = u.id)
                       OR (m2.sender_id = u.id AND m2.receiver_id = :user_id2)
                )
                WHERE u.id != :user_id3
                ORDER BY m.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id_unread' => $userId,
            ':user_id1'       => $userId,
            ':user_id2'       => $userId,
            ':user_id3'       => $userId
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Get chat history between two users.
     *
     * @param int $userId1
     * @param int $userId2
     * @param int $limit
     * @return array
     */
    public function getMessagesBetween($userId1, $userId2, $limit = 50) {
        $userId1 = (int)$userId1;
        $userId2 = (int)$userId2;

        $sql = "SELECT m.*, 
                       s.username AS sender_username, s.full_name AS sender_name, s.profile_image AS sender_image
                FROM messages m
                JOIN users s ON m.sender_id = s.id
                WHERE (m.sender_id = :u1a AND m.receiver_id = :u2a)
                   OR (m.sender_id = :u2b AND m.receiver_id = :u1b)
                ORDER BY m.id ASC
                LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':u1a' => $userId1,
            ':u2a' => $userId2,
            ':u2b' => $userId2,
            ':u1b' => $userId1
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Mark incoming messages from a specific sender as read.
     *
     * @param int $receiverId The current logged in user
     * @param int $senderId The other user whose messages are being read
     * @return bool
     */
    public function markAsRead($receiverId, $senderId) {
        $sql = "UPDATE messages SET is_read = 1 
                WHERE receiver_id = :receiver_id AND sender_id = :sender_id AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':receiver_id' => (int)$receiverId,
            ':sender_id'   => (int)$senderId
        ]);
    }

    /**
     * Get total unread message count for a user across all conversations.
     *
     * @param int $userId
     * @return int
     */
    public function getTotalUnreadCount($userId) {
        $sql = "SELECT COUNT(*) FROM messages WHERE receiver_id = :user_id AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => (int)$userId]);
        return (int)$stmt->fetchColumn();
    }
}
