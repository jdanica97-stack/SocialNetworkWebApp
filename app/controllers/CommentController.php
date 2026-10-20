<?php
// app/controllers/CommentController.php
// Controller handling comments CRUD operations and server-side authorization

require_once __DIR__ . '/../models/CommentModel.php';
require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class CommentController {
    private $commentModel;
    private $postModel;
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->commentModel = new CommentModel();
        $this->postModel    = new PostModel();
        $this->userModel    = new UserModel();
    }

    /**
     * Enforce authentication for comment actions.
     */
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'You must be logged in to perform that action.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * Create / Add a comment to a post.
     */
    public function create() {
        $this->requireAuth();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || isset($_POST['ajax']) || isset($_GET['ajax'])
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
                exit;
            }
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // CSRF Token Validation
        if (!validate_csrf_token()) {
            $errMsg = 'Security Error: Invalid or expired CSRF token.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $errMsg]);
                exit;
            }
            $_SESSION['error_message'] = $errMsg;
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $postId  = (int)($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $userId  = (int)$_SESSION['user_id'];

        // Validate post ID
        if ($postId <= 0) {
            $errMsg = 'Invalid post ID.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $errMsg]);
                exit;
            }
            $_SESSION['error_message'] = $errMsg;
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Verify that the post exists
        $post = $this->postModel->getPostById($postId);
        if (!$post) {
            $errMsg = 'The post you are trying to comment on does not exist or has been deleted.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $errMsg]);
                exit;
            }
            $_SESSION['error_message'] = $errMsg;
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Validate content
        if ($content === '') {
            $errMsg = 'Comment cannot be empty.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $errMsg]);
                exit;
            }
            $_SESSION['error_message'] = $errMsg;
            header('Location: ' . BASE_URL . '/index.php#post-' . $postId);
            exit;
        }

        if (mb_strlen($content) > 1000) {
            $errMsg = 'Comment cannot exceed 1,000 characters.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $errMsg]);
                exit;
            }
            $_SESSION['error_message'] = $errMsg;
            header('Location: ' . BASE_URL . '/index.php#post-' . $postId);
            exit;
        }

        $newCommentId = false;
        try {
            $newCommentId = $this->commentModel->createComment($postId, $userId, $content);
        } catch (Throwable $e) {
            error_log("Comment creation DB error: " . $e->getMessage());
            $newCommentId = false;
        }

        if ($newCommentId) {
            $user = $this->userModel->findById($userId);
            $allComments = $this->commentModel->getCommentsByPost($postId);
            $commentsCount = count($allComments);

            $cFullName = $user['full_name'] ?? 'User';
            $cUsername = $user['username'] ?? '';
            $cProfileImage = $user['profile_image'] ?? null;

            $cParts = explode(' ', trim($cFullName));
            $cInitials = count($cParts) >= 2
                ? strtoupper(mb_substr($cParts[0], 0, 1) . mb_substr($cParts[count($cParts)-1], 0, 1))
                : strtoupper(mb_substr($cFullName, 0, 2));

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'post_id' => $postId,
                    'comments_count' => $commentsCount,
                    'comment' => [
                        'id' => (int)$newCommentId,
                        'post_id' => $postId,
                        'user_id' => $userId,
                        'content' => $content,
                        'full_name' => $cFullName,
                        'username' => $cUsername,
                        'profile_image' => $cProfileImage,
                        'initials' => $cInitials,
                        'time_badge' => 'Just now',
                        'created_at' => date('M j, Y \a\t g:i a')
                    ]
                ]);
                exit;
            }

            $_SESSION['success_message'] = 'Comment added successfully!';
        } else {
            $errMsg = 'Failed to post comment. Please try again.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $errMsg]);
                exit;
            }
            $_SESSION['error_message'] = $errMsg;
        }

        header('Location: ' . BASE_URL . '/index.php#post-' . $postId);
        exit;
    }

    /**
     * Show edit form for a comment owned by the logged-in user.
     */
    public function edit() {
        $this->requireAuth();

        $commentId = (int)($_GET['comment_id'] ?? $_GET['id'] ?? 0);
        $userId    = (int)$_SESSION['user_id'];

        if ($commentId <= 0) {
            ErrorHandler::renderErrorPage(400, 'Invalid Request', 'The provided comment identifier is invalid.');
            exit;
        }

        $comment = $this->commentModel->getCommentById($commentId);
        if (!$comment) {
            ErrorHandler::renderErrorPage(404, 'Comment Not Found', 'The comment you are attempting to edit could not be found or has been deleted.');
            exit;
        }

        // Strict Server-Side Ownership Check
        if ((int)$comment['user_id'] !== $userId) {
            ErrorHandler::renderErrorPage(403, 'Permission Denied', 'You do not have permission to edit this comment. You can only edit your own comments.');
            exit;
        }

        $pageTitle = 'Edit Comment';
        $formData  = [
            'content' => $comment['content']
        ];
        $errors = [];

        require __DIR__ . '/../views/comments/edit.php';
    }

    /**
     * Process comment update submission.
     */
    public function update() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // CSRF Token Validation
        if (!validate_csrf_token()) {
            $_SESSION['error_message'] = 'Security Error: Invalid or expired CSRF token.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $userId    = (int)$_SESSION['user_id'];

        if ($commentId <= 0) {
            $_SESSION['error_message'] = 'Invalid comment ID.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $comment = $this->commentModel->getCommentById($commentId);
        if (!$comment) {
            $_SESSION['error_message'] = 'Comment not found or has been deleted.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Strict Server-Side Ownership Check
        if ((int)$comment['user_id'] !== $userId) {
            $_SESSION['error_message'] = 'Unauthorized: You can only edit your own comments.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $content  = trim($_POST['content'] ?? '');
        $errors   = [];
        $formData = ['content' => $content];

        if ($content === '') {
            $errors[] = 'Comment cannot be empty.';
        } elseif (mb_strlen($content) > 1000) {
            $errors[] = 'Comment cannot exceed 1,000 characters.';
        }

        if (!empty($errors)) {
            $pageTitle = 'Edit Comment';
            require __DIR__ . '/../views/comments/edit.php';
            return;
        }

        $updated = $this->commentModel->updateComment($commentId, $userId, $content);
        if ($updated) {
            $_SESSION['success_message'] = 'Comment updated successfully!';
            header('Location: ' . BASE_URL . '/index.php#post-' . $comment['post_id']);
            exit;
        } else {
            $_SESSION['error_message'] = 'Failed to update comment. Please try again.';
            header('Location: ' . BASE_URL . '/index.php?action=edit_comment&comment_id=' . $commentId);
            exit;
        }
    }

    /**
     * Delete an existing comment owned by the logged-in user.
     */
    public function delete() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = 'Invalid request method for deleting a comment.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // CSRF Token Validation
        if (!validate_csrf_token()) {
            $_SESSION['error_message'] = 'Security Error: Invalid or expired CSRF token.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $userId    = (int)$_SESSION['user_id'];

        if ($commentId <= 0) {
            $_SESSION['error_message'] = 'Invalid comment ID.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $comment = $this->commentModel->getCommentById($commentId);
        if (!$comment) {
            $_SESSION['error_message'] = 'Comment not found or has already been deleted.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Strict Server-Side Ownership Check
        if ((int)$comment['user_id'] !== $userId) {
            $_SESSION['error_message'] = 'Unauthorized: You can only delete your own comments.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $deleted = $this->commentModel->deleteComment($commentId, $userId);
        if ($deleted) {
            $_SESSION['success_message'] = 'Comment deleted successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to delete comment.';
        }

        header('Location: ' . BASE_URL . '/index.php#post-' . $comment['post_id']);
        exit;
    }
}
