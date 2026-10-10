<?php
// app/controllers/PostController.php
// Controller handling posts CRUD, comments, and likes

require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class PostController {
    private $postModel;
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->postModel = new PostModel();
        $this->userModel = new UserModel();
    }

    /**
     * Enforce authentication.
     */
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'You must be logged in to perform that action.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * Process image upload securely.
     *
     * @param array $file The $_FILES entry
     * @param array &$errors Array to collect error messages
     * @param int $userId Current user ID for naming
     * @return string|null Filename if uploaded successfully, null otherwise
     */
    private function handleImageUpload($file, &$errors, $userId) {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $fileError = $file['error'];
        if ($fileError !== UPLOAD_ERR_OK) {
            $errors[] = 'Failed to upload image. (Error code: ' . $fileError . ')';
            return null;
        }

        $tmpPath  = $file['tmp_name'];
        $fileName = $file['name'];
        $fileSize = $file['size'];

        // Maximum size: 5MB
        $maxSize = 5 * 1024 * 1024;
        if ($fileSize > $maxSize) {
            $errors[] = 'Image size must not exceed 5MB.';
            return null;
        }

        // Validate extension
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions, true)) {
            $errors[] = 'Invalid image extension. Only JPG, JPEG, PNG, GIF, and WEBP formats are allowed.';
            return null;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes, true)) {
            $errors[] = 'Invalid image file type. Please upload a real image.';
            return null;
        }

        // Validate image content
        $imageInfo = @getimagesize($tmpPath);
        if ($imageInfo === false) {
            $errors[] = 'Uploaded file is not a valid image.';
            return null;
        }

        // Generate safe unique filename
        $uploadDir = __DIR__ . '/../../public/uploads/posts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeFileName = 'post_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $destPath = $uploadDir . $safeFileName;

        if (move_uploaded_file($tmpPath, $destPath)) {
            return $safeFileName;
        } else {
            $errors[] = 'Could not save the uploaded image. Please check folder permissions.';
            return null;
        }
    }

    /**
     * Delete an image file from the public/uploads/posts directory.
     *
     * @param string|null $imageName
     */
    private function deleteImageFile($imageName) {
        if (!empty($imageName)) {
            $filePath = __DIR__ . '/../../public/uploads/posts/' . basename($imageName);
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }
        }
    }

    /**
     * Display feed / posts list.
     */
    public function index() {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }

    /**
     * Create a post.
     */
    public function create() {
        $this->requireAuth();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || isset($_POST['ajax']) || isset($_GET['ajax'])
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = trim($_POST['content'] ?? '');
            $userId  = (int)$_SESSION['user_id'];
            $errors  = [];

            $hasImageUpload = isset($_FILES['post_image']) && $_FILES['post_image']['error'] !== UPLOAD_ERR_NO_FILE;

            if (empty($content) && !$hasImageUpload) {
                $errMsg = 'Post content or image cannot be empty.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $errMsg]);
                    exit;
                }
                $_SESSION['error_message'] = $errMsg;
                header('Location: ' . BASE_URL . '/index.php');
                exit;
            }

            // Handle optional image upload
            $imageName = null;
            if ($hasImageUpload) {
                $imageName = $this->handleImageUpload($_FILES['post_image'], $errors, $userId);
            }

            if (!empty($errors)) {
                $errMsg = implode(' ', $errors);
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $errMsg]);
                    exit;
                }
                $_SESSION['error_message'] = $errMsg;
                header('Location: ' . BASE_URL . '/index.php');
                exit;
            }

            $postId = $this->postModel->createPost($userId, $content, $imageName);
            if ($postId) {
                $user = $this->userModel->findById($userId);
                $authorFullName = $user['full_name'] ?? 'User';
                $authorUsername = $user['username'] ?? '';
                $authorProfileImage = $user['profile_image'] ?? null;

                $authorParts = explode(' ', trim($authorFullName));
                $authorInitials = count($authorParts) >= 2
                    ? strtoupper(mb_substr($authorParts[0], 0, 1) . mb_substr($authorParts[count($authorParts)-1], 0, 1))
                    : strtoupper(mb_substr($authorFullName, 0, 2));

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Post published successfully!',
                        'post' => [
                            'id' => (int)$postId,
                            'user_id' => $userId,
                            'content' => $content,
                            'image' => $imageName,
                            'full_name' => $authorFullName,
                            'username' => $authorUsername,
                            'profile_image' => $authorProfileImage,
                            'initials' => $authorInitials,
                            'created_at' => date('M j, Y \a\t g:i a'),
                            'time_badge' => 'Just now',
                            'likes_count' => 0,
                            'comments_count' => 0,
                            'is_liked' => 0,
                            'comments' => []
                        ]
                    ]);
                    exit;
                }

                $_SESSION['success_message'] = 'Post published successfully!';
            } else {
                if ($imageName) {
                    $this->deleteImageFile($imageName);
                }
                $errMsg = 'Failed to publish post.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $errMsg]);
                    exit;
                }
                $_SESSION['error_message'] = $errMsg;
            }
        }

        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }

    /**
     * Show edit form for a post owned by the logged-in user.
     */
    public function edit() {
        $this->requireAuth();

        $postId = (int)($_GET['post_id'] ?? $_GET['id'] ?? 0);
        $userId = (int)$_SESSION['user_id'];

        if ($postId <= 0) {
            $_SESSION['error_message'] = 'Invalid post ID.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $post = $this->postModel->getPostById($postId);
        if (!$post) {
            $_SESSION['error_message'] = 'Post not found.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Strict Server-Side Ownership Check
        if ((int)$post['user_id'] !== $userId) {
            $_SESSION['error_message'] = 'Unauthorized: You can only edit your own posts.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $pageTitle = 'Edit Post';
        $formData = [
            'content' => $post['content']
        ];
        $errors = [];

        require __DIR__ . '/../views/posts/edit.php';
    }

    /**
     * Process post update submission.
     */
    public function update() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        $userId = (int)$_SESSION['user_id'];

        if ($postId <= 0) {
            $_SESSION['error_message'] = 'Invalid post ID.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $post = $this->postModel->getPostById($postId);
        if (!$post) {
            $_SESSION['error_message'] = 'Post not found.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Strict Server-Side Ownership Check
        if ((int)$post['user_id'] !== $userId) {
            $_SESSION['error_message'] = 'Unauthorized: You can only edit your own posts.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $content = trim($_POST['content'] ?? '');
        $removeImage = !empty($_POST['remove_image']);
        $hasNewImage = isset($_FILES['post_image']) && $_FILES['post_image']['error'] !== UPLOAD_ERR_NO_FILE;

        $errors = [];
        $formData = ['content' => $content];

        // Determine final image state
        $finalImage = $post['image'];

        // Handle new image upload
        $uploadedNewImage = null;
        if ($hasNewImage) {
            $uploadedNewImage = $this->handleImageUpload($_FILES['post_image'], $errors, $userId);
            if ($uploadedNewImage !== null) {
                $finalImage = $uploadedNewImage;
            }
        } elseif ($removeImage) {
            $finalImage = null;
        }

        // Validation: post cannot be empty if there's no image
        if (empty($content) && empty($finalImage)) {
            $errors[] = 'Post content or image cannot be empty.';
        }

        // If there were validation or upload errors, render edit form with errors
        if (!empty($errors)) {
            // If an image was saved during this attempt, clean it up
            if ($uploadedNewImage) {
                $this->deleteImageFile($uploadedNewImage);
            }

            $pageTitle = 'Edit Post';
            require __DIR__ . '/../views/posts/edit.php';
            return;
        }

        // Update post in database
        $updated = $this->postModel->updatePost($postId, $userId, $content, $finalImage);

        if ($updated) {
            // If new image was saved, remove the old one from disk
            if ($uploadedNewImage && !empty($post['image'])) {
                $this->deleteImageFile($post['image']);
            } elseif ($removeImage && !empty($post['image'])) {
                $this->deleteImageFile($post['image']);
            }

            $_SESSION['success_message'] = 'Post updated successfully!';
            header('Location: ' . BASE_URL . '/index.php#post-' . $postId);
            exit;
        } else {
            if ($uploadedNewImage) {
                $this->deleteImageFile($uploadedNewImage);
            }
            $_SESSION['error_message'] = 'Failed to update post. Please try again.';
            header('Location: ' . BASE_URL . '/index.php?action=edit_post&post_id=' . $postId);
            exit;
        }
    }

    /**
     * Add comment (delegates to CommentController).
     */
    public function comment() {
        require_once __DIR__ . '/CommentController.php';
        $commentController = new CommentController();
        $commentController->create();
    }

    /**
     * Toggle like.
     */
    public function like() {
        $this->requireAuth();

        $postId = (int)($_POST['post_id'] ?? $_GET['post_id'] ?? 0);
        $userId = (int)$_SESSION['user_id'];

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || isset($_POST['ajax']) || isset($_GET['ajax'])
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($postId <= 0) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Invalid post ID.']);
                exit;
            }
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $isLiked = $this->postModel->toggleLike($postId, $userId);
        $likesCount = $this->postModel->getLikesCount($postId);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'is_liked' => $isLiked,
                'likes_count' => $likesCount,
                'post_id' => $postId
            ]);
            exit;
        }

        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }

    /**
     * Delete post.
     */
    public function delete() {
        $this->requireAuth();

        $postId = (int)($_POST['post_id'] ?? $_GET['post_id'] ?? 0);
        $userId = (int)$_SESSION['user_id'];

        if ($postId <= 0) {
            $_SESSION['error_message'] = 'Invalid post ID.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $post = $this->postModel->getPostById($postId);
        if (!$post) {
            $_SESSION['error_message'] = 'Post not found.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Strict Server-Side Ownership Check
        if ((int)$post['user_id'] !== $userId) {
            $_SESSION['error_message'] = 'Unauthorized: You can only delete your own posts.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Delete post image file if present
        if (!empty($post['image'])) {
            $this->deleteImageFile($post['image']);
        }

        $deleted = $this->postModel->deletePost($postId, $userId);
        if ($deleted) {
            $_SESSION['success_message'] = 'Post deleted successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to delete post.';
        }

        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}
