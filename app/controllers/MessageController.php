<?php
// app/controllers/MessageController.php
// Handles direct messaging UI, sending messages, conversation loading, and polling

require_once __DIR__ . '/../models/MessageModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class MessageController {
    private $messageModel;
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->messageModel = new MessageModel();
        $this->userModel = new UserModel();
    }

    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            if (!empty($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Please log in to use messages.']);
                exit;
            }
            $_SESSION['error_message'] = 'You must be logged in to view messages.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * Show messages hub view (conversations list + active conversation if selected).
     */
    public function index() {
        $this->requireAuth();
        $currentUserId = (int)$_SESSION['user_id'];

        $conversations = $this->messageModel->getConversations($currentUserId);
        $activeUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        $activeUser = null;
        $messages = [];

        // If an active user is selected or passed via ?user_id=X
        if ($activeUserId > 0 && $activeUserId !== $currentUserId) {
            $activeUser = $this->userModel->findById($activeUserId);
            if ($activeUser) {
                // Mark messages as read
                $this->messageModel->markAsRead($currentUserId, $activeUserId);
                $messages = $this->messageModel->getMessagesBetween($currentUserId, $activeUserId);
            }
        } elseif (!empty($conversations)) {
            // Default to the first conversation if none explicitly selected
            $firstConv = $conversations[0];
            $activeUserId = (int)$firstConv['other_user_id'];
            $activeUser = $this->userModel->findById($activeUserId);
            if ($activeUser) {
                $this->messageModel->markAsRead($currentUserId, $activeUserId);
                $messages = $this->messageModel->getMessagesBetween($currentUserId, $activeUserId);
            }
        }

        $allUsers = $this->userModel->getAllUsers();

        $pageTitle = 'Direct Messages';
        require __DIR__ . '/../views/messages/index.php';
    }

    /**
     * Send message via AJAX.
     */
    public function send() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        if (!validate_csrf_token()) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }

        $senderId = (int)$_SESSION['user_id'];
        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $text = trim($_POST['message'] ?? '');

        if ($receiverId <= 0 || $receiverId === $senderId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid recipient']);
            exit;
        }

        if (empty($text)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
            exit;
        }

        $msgId = $this->messageModel->sendMessage($senderId, $receiverId, $text);

        if ($msgId) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => [
                    'id'          => $msgId,
                    'sender_id'   => $senderId,
                    'receiver_id' => $receiverId,
                    'content'     => $text,
                    'time'        => date('g:i A'),
                    'is_edited'   => false,
                    'is_me'       => true
                ]
            ]);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Failed to send message']);
        exit;
    }

    /**
     * Edit a sent message via AJAX.
     */
    public function edit() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        if (!validate_csrf_token()) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }

        $currentUserId = (int)$_SESSION['user_id'];
        $messageId = (int)($_POST['message_id'] ?? 0);
        $newContent = trim($_POST['message'] ?? '');

        if ($messageId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
            exit;
        }

        if (empty($newContent)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
            exit;
        }

        $message = $this->messageModel->getMessageById($messageId);
        if (!$message) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Message not found']);
            exit;
        }

        if ((int)$message['sender_id'] !== $currentUserId) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You can only edit your own messages']);
            exit;
        }

        $updated = $this->messageModel->updateMessage($messageId, $currentUserId, $newContent);
        if ($updated) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => [
                    'id'        => $messageId,
                    'content'   => $newContent,
                    'is_edited' => true
                ]
            ]);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Failed to update message']);
        exit;
    }

    /**
     * Unsend (delete) a message via AJAX.
     */
    public function unsend() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        if (!validate_csrf_token()) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }

        $currentUserId = (int)$_SESSION['user_id'];
        $messageId = (int)($_POST['message_id'] ?? 0);

        if ($messageId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
            exit;
        }

        $message = $this->messageModel->getMessageById($messageId);
        if (!$message) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Message not found']);
            exit;
        }

        if ((int)$message['sender_id'] !== $currentUserId) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You can only unsend your own messages']);
            exit;
        }

        $unsent = $this->messageModel->unsendMessage($messageId, $currentUserId);
        if ($unsent) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'    => true,
                'message_id' => $messageId,
                'is_unsent'  => true
            ]);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Failed to unsend message']);
        exit;
    }

    /**
     * Poll recent messages between current user and active user.
     */
    public function poll() {
        $this->requireAuth();
        $currentUserId = (int)$_SESSION['user_id'];
        $activeUserId = (int)($_GET['user_id'] ?? 0);

        if ($activeUserId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'messages' => []]);
            exit;
        }

        // Mark incoming messages as read
        $this->messageModel->markAsRead($currentUserId, $activeUserId);
        $rawMessages = $this->messageModel->getMessagesBetween($currentUserId, $activeUserId);

        $messages = [];
        foreach ($rawMessages as $m) {
            $messages[] = [
                'id'          => (int)$m['id'],
                'sender_id'   => (int)$m['sender_id'],
                'receiver_id' => (int)$m['receiver_id'],
                'content'     => $m['message'],
                'is_edited'   => !empty($m['is_edited']),
                'is_unsent'   => !empty($m['is_unsent']),
                'time'        => date('g:i A', strtotime($m['created_at'])),
                'is_me'       => ((int)$m['sender_id'] === $currentUserId)
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success'  => true,
            'messages' => $messages
        ]);
        exit;
    }


    /**
     * Get total unread messages count for navbar polling.
     */
    public function getUnreadCount() {
        $this->requireAuth();
        $currentUserId = (int)$_SESSION['user_id'];
        $count = $this->messageModel->getTotalUnreadCount($currentUserId);

        header('Content-Type: application/json');
        echo json_encode([
            'success'      => true,
            'unread_count' => $count
        ]);
        exit;
    }
}

