<?php
// app/controllers/PostController.php
// Controller handling posts CRUD, comments, and likes

require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class PostController {
    private $postModel;
    private $userModel;
    private $notificationModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->postModel = new PostModel();
        $this->userModel = new UserModel();
        $this->notificationModel = new NotificationModel();
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
     * Process multiple or single image uploads securely.
     * Supports array inputs (e.g. post_images[]) or single file inputs.
     *
     * @param string $inputKey The $_FILES key to inspect ('post_images' or 'post_image')
     * @param array &$errors Array to collect error messages
     * @param int $userId Current user ID for naming
     * @param int $maxUploads Maximum allowed images (default 10)
     * @return array Array of saved filenames
     */
    private function handleMultipleImageUploads($inputKey, &$errors, $userId, $maxUploads = 10) {
        if (!isset($_FILES[$inputKey])) {
            return [];
        }

        $raw = $_FILES[$inputKey];
        $fileList = [];

        if (is_array($raw['name'])) {
            $total = count($raw['name']);
            for ($i = 0; $i < $total; $i++) {
                if ($raw['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    $fileList[] = [
                        'name'     => $raw['name'][$i],
                        'type'     => $raw['type'][$i] ?? '',
                        'tmp_name' => $raw['tmp_name'][$i],
                        'error'    => $raw['error'][$i],
                        'size'     => $raw['size'][$i],
                    ];
                }
            }
        } else {
            if ($raw['error'] !== UPLOAD_ERR_NO_FILE) {
                $fileList[] = $raw;
            }
        }

        if (empty($fileList)) {
            return [];
        }

        if (count($fileList) > $maxUploads) {
            $errors[] = "You can upload a maximum of {$maxUploads} images per post.";
            return [];
        }

        $savedFiles = [];
        $uploadDir = __DIR__ . '/../../public/uploads/posts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMimes      = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize           = 5 * 1024 * 1024; // 5MB per file

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        foreach ($fileList as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Failed to upload image "' . htmlspecialchars($file['name']) . '" (Error code: ' . $file['error'] . ').';
                continue;
            }

            if ($file['size'] > $maxSize) {
                $errors[] = 'Image "' . htmlspecialchars($file['name']) . '" exceeds the 5MB size limit.';
                continue;
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions, true)) {
                $errors[] = 'Invalid extension for "' . htmlspecialchars($file['name']) . '". Only JPG, JPEG, PNG, GIF, and WEBP are allowed.';
                continue;
            }

            $mimeType = finfo_file($finfo, $file['tmp_name']);
            if (!in_array($mimeType, $allowedMimes, true)) {
                $errors[] = 'Invalid image file type for "' . htmlspecialchars($file['name']) . '".';
                continue;
            }

            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                $errors[] = 'File "' . htmlspecialchars($file['name']) . '" is not a valid image.';
                continue;
            }

            $safeFileName = 'post_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $destPath = $uploadDir . $safeFileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $savedFiles[] = $safeFileName;
            } else {
                $errors[] = 'Could not save uploaded image "' . htmlspecialchars($file['name']) . '".';
            }
        }

        finfo_close($finfo);

        if (!empty($errors)) {
            foreach ($savedFiles as $saved) {
                $this->deleteImageFile($saved);
            }
            return [];
        }

        return $savedFiles;
    }

    /**
     * Process image upload securely (single file backward compatibility).
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
     * Delete multiple image files from the public/uploads/posts directory.
     *
     * @param string|array|null $images
     */
    private function deleteMultipleImageFiles($images) {
        $parsed = PostModel::parseImages($images);
        foreach ($parsed as $img) {
            $this->deleteImageFile($img);
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
     * Display a single post with comments (Single Post View).
     */
    public function view() {
        $postId = (int)($_GET['post_id'] ?? $_GET['id'] ?? 0);

        if ($postId <= 0) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $post = $this->postModel->getPostWithDetails($postId, $currentUserId);

        if (!$post) {
            require_once __DIR__ . '/../core/ErrorHandler.php';
            ErrorHandler::renderErrorPage(404, 'Post Not Found', 'The requested post does not exist or may have been removed.');
            exit;
        }

        $highlightCommentId = (int)($_GET['comment_id'] ?? 0);
        $pageTitle = 'Post by ' . htmlspecialchars($post['full_name']);
        require __DIR__ . '/../views/posts/view.php';
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

            $content = trim($_POST['content'] ?? '');
            $userId  = (int)$_SESSION['user_id'];
            $errors  = [];

            $hasImageUpload = false;
            if (isset($_FILES['post_images'])) {
                if (is_array($_FILES['post_images']['error'])) {
                    foreach ($_FILES['post_images']['error'] as $err) {
                        if ($err !== UPLOAD_ERR_NO_FILE) {
                            $hasImageUpload = true;
                            break;
                        }
                    }
                } elseif ($_FILES['post_images']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $hasImageUpload = true;
                }
            }
            if (!$hasImageUpload && isset($_FILES['post_image']) && $_FILES['post_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $hasImageUpload = true;
            }

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

            // Handle optional multiple image uploads
            $uploadedImages = [];
            if (isset($_FILES['post_images'])) {
                $uploadedImages = $this->handleMultipleImageUploads('post_images', $errors, $userId);
            }
            if (empty($uploadedImages) && empty($errors) && isset($_FILES['post_image'])) {
                $single = $this->handleImageUpload($_FILES['post_image'], $errors, $userId);
                if ($single) {
                    $uploadedImages = [$single];
                }
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

            $postId = false;
            try {
                $postId = $this->postModel->createPost($userId, $content, $uploadedImages);
            } catch (Throwable $e) {
                error_log("Post creation DB error: " . $e->getMessage());
                $postId = false;
            }

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
                            'image' => !empty($uploadedImages) ? $uploadedImages[0] : null,
                            'images' => $uploadedImages,
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
                $this->deleteMultipleImageFiles($uploadedImages);
                $errMsg = 'We could not save your post due to a system issue. Please try again.';
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
            ErrorHandler::renderErrorPage(400, 'Invalid Request', 'The provided post identifier is invalid.');
            exit;
        }

        $post = $this->postModel->getPostById($postId);
        if (!$post) {
            ErrorHandler::renderErrorPage(404, 'Post Not Found', 'The post you are attempting to edit could not be found or has been deleted.');
            exit;
        }

        // Strict Server-Side Ownership Check
        if ((int)$post['user_id'] !== $userId) {
            ErrorHandler::renderErrorPage(403, 'Permission Denied', 'You do not have permission to edit this post. You can only edit your own posts.');
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

        // CSRF Token Validation
        if (!validate_csrf_token()) {
            $_SESSION['error_message'] = 'Security Error: Invalid or expired CSRF token.';
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
        $removeAllImages = !empty($_POST['remove_image']);
        $removeSpecific = $_POST['remove_images'] ?? [];
        if (!is_array($removeSpecific)) {
            $removeSpecific = [$removeSpecific];
        }

        $existingImages = PostModel::parseImages($post['image'] ?? null);
        $remainingImages = [];
        $filesToDelete = [];

        if ($removeAllImages) {
            $filesToDelete = $existingImages;
            $remainingImages = [];
        } else {
            foreach ($existingImages as $img) {
                if (in_array($img, $removeSpecific, true)) {
                    $filesToDelete[] = $img;
                } else {
                    $remainingImages[] = $img;
                }
            }
        }

        $errors = [];
        $formData = ['content' => $content];

        // Handle new image uploads (supports post_images[] and post_image)
        $newUploadedImages = [];
        if (isset($_FILES['post_images'])) {
            $newUploadedImages = $this->handleMultipleImageUploads('post_images', $errors, $userId);
        }
        if (empty($newUploadedImages) && empty($errors) && isset($_FILES['post_image'])) {
            $single = $this->handleImageUpload($_FILES['post_image'], $errors, $userId);
            if ($single) {
                $newUploadedImages = [$single];
            }
        }

        $finalImages = array_merge($remainingImages, $newUploadedImages);

        // Validation: post cannot be empty if there's no image
        if (empty($content) && empty($finalImages)) {
            $errors[] = 'Post content or image cannot be empty.';
        }

        // If there were validation or upload errors, render edit form with errors
        if (!empty($errors)) {
            // Clean up any newly uploaded images from disk
            $this->deleteMultipleImageFiles($newUploadedImages);

            $pageTitle = 'Edit Post';
            require __DIR__ . '/../views/posts/edit.php';
            return;
        }

        // Update post in database
        $updated = $this->postModel->updatePost($postId, $userId, $content, $finalImages);

        if ($updated) {
            // Remove deleted files from disk
            $this->deleteMultipleImageFiles($filesToDelete);

            $_SESSION['success_message'] = 'Post updated successfully!';
            header('Location: ' . BASE_URL . '/index.php#post-' . $postId);
            exit;
        } else {
            $this->deleteMultipleImageFiles($newUploadedImages);
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

        // CSRF Token Validation
        if (!validate_csrf_token()) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Security Error: Invalid or expired CSRF token.']);
                exit;
            }
            $_SESSION['error_message'] = 'Security Error: Invalid or expired CSRF token.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        if ($postId <= 0) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Invalid post ID.']);
                exit;
            }
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Verify that the post actually exists before toggling like
        $post = $this->postModel->getPostById($postId);
        if (!$post) {
            $errMsg = 'The post you are trying to like does not exist or has been removed.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $errMsg]);
                exit;
            }
            $_SESSION['error_message'] = $errMsg;
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        try {
            $isLiked = $this->postModel->toggleLike($postId, $userId);
            $likesCount = $this->postModel->getLikesCount($postId);

            if ($isLiked && (int)$post['user_id'] !== $userId) {
                $actorName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Someone';
                $this->notificationModel->createNotification(
                    (int)$post['user_id'],
                    $userId,
                    'like',
                    $postId,
                    $actorName . ' liked your post.'
                );
            }
        } catch (Throwable $e) {
            error_log("Error toggling like on post {$postId}: " . $e->getMessage());
            $errMsg = 'Could not update like status at this time. Please try again.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $errMsg]);
                exit;
            }
            $_SESSION['error_message'] = $errMsg;
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = 'Invalid request method for deleting a post.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // CSRF Token Validation
        if (!validate_csrf_token()) {
            $_SESSION['error_message'] = 'Security Error: Invalid or expired CSRF token.';
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
            $_SESSION['error_message'] = 'Unauthorized: You can only delete your own posts.';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Delete all post image files if present
        if (!empty($post['image'])) {
            $this->deleteMultipleImageFiles($post['image']);
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
