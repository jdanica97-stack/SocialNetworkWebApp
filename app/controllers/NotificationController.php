<?php
// app/controllers/NotificationController.php
// Handles notification polling, listing, and read receipts

require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/CommentModel.php';

class NotificationController {
    private $notificationModel;
    private $commentModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->notificationModel = new NotificationModel();
        $this->commentModel = new CommentModel();
    }

    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
    }

    /**
     * Get unread count for badge polling.
     */
    public function getUnreadCount() {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];
        $count = $this->notificationModel->getUnreadCount($userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'unread_count' => $count
        ]);
        exit;
    }

    /**
     * Get notification list for dropdown.
     */
    public function getNotifications() {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];
        $notifications = $this->notificationModel->getUserNotifications($userId, 15);

        // Format dates and avatar initials for presentation
        $formatted = [];
        foreach ($notifications as $n) {
            $initials = 'U';
            if (!empty($n['actor_name'])) {
                $parts = explode(' ', trim($n['actor_name']));
                if (count($parts) >= 2) {
                    $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1));
                } else {
                    $initials = strtoupper(mb_substr($n['actor_name'], 0, 2));
                }
            }

            // Time badge calculation
            $timeAgo = format_time_ago($n['created_at']);

            $commentId = null;
            $postId = null;

            // Calculate target destination URL (direct link to standalone post or user profile or comment)
            $targetUrl = BASE_URL . '/index.php';
            if ($n['type'] === 'like') {
                if (!empty($n['entity_id'])) {
                    $postId = (int)$n['entity_id'];
                    $targetUrl = BASE_URL . '/index.php?action=view_post&post_id=' . $postId;
                }
            } elseif ($n['type'] === 'comment') {
                if (!empty($n['entity_id'])) {
                    $rawEntityId = (int)$n['entity_id'];
                    // Check if entity_id is a comment ID
                    $comment = $this->commentModel->getCommentById($rawEntityId);
                    if ($comment) {
                        $commentId = (int)$comment['id'];
                        $postId = (int)$comment['post_id'];
                    } else {
                        // Historical fallback: entity_id might have been the post_id
                        $postId = $rawEntityId;
                        // Try to find the latest comment by this actor on this post
                        $db = Database::connect();
                        $cStmt = $db->prepare("SELECT id FROM comments WHERE post_id = :post_id AND user_id = :actor_id ORDER BY id DESC LIMIT 1");
                        $cStmt->execute([':post_id' => $postId, ':actor_id' => (int)$n['actor_id']]);
                        $foundComment = $cStmt->fetch();
                        if ($foundComment) {
                            $commentId = (int)$foundComment['id'];
                        }
                    }

                    if ($commentId && $postId) {
                        $targetUrl = BASE_URL . '/index.php?action=view_post&post_id=' . $postId . '&comment_id=' . $commentId . '#comment-' . $commentId;
                    } elseif ($postId) {
                        $targetUrl = BASE_URL . '/index.php?action=view_post&post_id=' . $postId;
                    }
                }
            } elseif ($n['type'] === 'follow') {
                if (!empty($n['actor_id'])) {
                    $targetUrl = BASE_URL . '/index.php?action=profile&user_id=' . (int)$n['actor_id'];
                }
            }

            $formatted[] = [
                'id'             => (int)$n['id'],
                'actor_id'       => (int)$n['actor_id'],
                'actor_name'     => $n['actor_name'],
                'actor_username' => $n['actor_username'],
                'actor_image'    => $n['actor_image'],
                'actor_initials' => $initials,
                'type'           => $n['type'],
                'entity_id'      => $n['entity_id'],
                'post_id'        => $postId,
                'comment_id'     => $commentId,
                'target_url'     => $targetUrl,
                'message'        => $n['message'],
                'is_read'        => (int)$n['is_read'],
                'time_ago'       => $timeAgo,
                'created_at'     => $n['created_at']
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success'       => true,
            'notifications' => $formatted
        ]);
        exit;
    }

    /**
     * Mark notifications as read.
     */
    public function markRead() {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];
        $notificationId = !empty($_POST['notification_id']) ? (int)$_POST['notification_id'] : null;

        $this->notificationModel->markAsRead($userId, $notificationId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
