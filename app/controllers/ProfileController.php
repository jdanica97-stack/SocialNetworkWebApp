<?php
// app/controllers/ProfileController.php
// Controller handling viewing and editing of the logged-in user profile

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/FollowModel.php';

class ProfileController {
    private $userModel;
    private $postModel;
    private $followModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel   = new UserModel();
        $this->postModel   = new PostModel();
        $this->followModel = new FollowModel();
    }

    /**
     * Enforce authentication for profile actions.
     * Redirects unauthenticated visitors to login.
     */
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'You must be logged in to access that page.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * View a user's profile and their posts.
     * Defaults to the logged-in user or accepts user_id / id query parameter.
     */
    public function view() {
        $this->requireAuth();

        $loggedInUserId = (int)$_SESSION['user_id'];
        $requestedUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : $loggedInUserId);
        $userId = ($requestedUserId > 0) ? $requestedUserId : $loggedInUserId;

        $user = $this->userModel->getProfileById($userId);

        if (!$user) {
            if ($userId === $loggedInUserId) {
                // If session exists but user row was deleted or not found
                unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['full_name']);
                $_SESSION['error_message'] = 'User profile not found. Please log in again.';
                header('Location: ' . BASE_URL . '/index.php?action=login');
                exit;
            } else {
                ErrorHandler::renderErrorPage(404, 'Profile Not Found', 'The requested user profile does not exist or may have been removed.');
                exit;
            }
        }

        // Retrieve only posts belonging to the profile user, ordered newest first
        $posts = $this->postModel->getPostsByUserId($userId, $loggedInUserId);

        $isOwnProfile = ($userId === $loggedInUserId);
        $isFollowing = !$isOwnProfile ? $this->followModel->isFollowing($loggedInUserId, $userId) : false;
        $isFollowedBy = !$isOwnProfile ? $this->followModel->isFollowing($userId, $loggedInUserId) : false;
        $followersCount = $this->followModel->getFollowersCount($userId);
        $followingCount = $this->followModel->getFollowingCount($userId);

        $pageTitle = $isOwnProfile ? 'My Profile' : $user['full_name'] . "'s Profile";
        require __DIR__ . '/../views/profile/profile.php';
    }

    /**
     * Show edit form or process edit submission for the logged-in user's profile.
     */
    public function edit() {
        $this->requireAuth();

        $userId = (int)$_SESSION['user_id'];
        $user = $this->userModel->getProfileById($userId);

        if (!$user) {
            unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['full_name']);
            $_SESSION['error_message'] = 'User profile not found. Please log in again.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }

        $errors = [];
        $formData = [
            'bio' => $user['bio'] ?? ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF validation
            if (!validate_csrf_token()) {
                $errors[] = 'Security Error: Invalid or expired CSRF token. Please try submitting again.';
            }

            $bio = trim($_POST['bio'] ?? '');
            $formData['bio'] = $bio;

            // 1. Validate Bio
            if (mb_strlen($bio) > 500) {
                $errors[] = 'Bio must not exceed 500 characters.';
            }

            // Normalize empty bio to null or empty string
            $cleanBio = ($bio === '') ? null : $bio;

            // 3. Process Profile Picture Upload (if uploaded or adjusted)
            $newProfileImage = null;
            $removePicture = !empty($_POST['remove_picture']);
            $croppedImageData = trim($_POST['cropped_image_data'] ?? '');

            if ($removePicture) {
                // User opted to remove existing picture
                if (!empty($user['profile_image'])) {
                    $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
                    $oldFilePath = $uploadDir . basename($user['profile_image']);
                    if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                $newProfileImage = ''; // flag to set null in db
            } elseif (!empty($croppedImageData)) {
                // Process adjusted / cropped base64 image from canvas
                if (preg_match('#^data:(image/(?:jpeg|png|webp));base64,(.+)$#i', $croppedImageData, $matches)) {
                    $rawImage = base64_decode($matches[2], true);
                    if ($rawImage === false) {
                        $errors[] = 'Invalid adjusted image data received.';
                    } elseif (strlen($rawImage) > 5 * 1024 * 1024) {
                        $errors[] = 'Profile picture must not exceed 5MB.';
                    } else {
                        $imageInfo = @getimagesizefromstring($rawImage);
                        if ($imageInfo === false) {
                            $errors[] = 'Adjusted image could not be verified as a valid image.';
                        } else {
                            $extension = 'jpg';
                            if ($imageInfo[2] === IMAGETYPE_PNG) {
                                $extension = 'png';
                            } elseif (defined('IMAGETYPE_WEBP') && $imageInfo[2] === IMAGETYPE_WEBP) {
                                $extension = 'webp';
                            }

                            $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }

                            $safeFileName = 'avatar_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                            $destination  = $uploadDir . $safeFileName;

                            if (file_put_contents($destination, $rawImage) !== false) {
                                $newProfileImage = $safeFileName;

                                // Clean up previous image file if it exists in uploads/avatars/
                                if (!empty($user['profile_image'])) {
                                    $oldFilePath = $uploadDir . basename($user['profile_image']);
                                    if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                                        @unlink($oldFilePath);
                                    }
                                }
                            } else {
                                $errors[] = 'Could not save the adjusted image file. Please check folder permissions.';
                            }
                        }
                    }
                } else {
                    $errors[] = 'Invalid image format received from adjustment tool.';
                }
            } elseif (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Standard file upload fallback
                $fileError = $_FILES['profile_image']['error'];
                if ($fileError !== UPLOAD_ERR_OK) {
                    $errors[] = 'Failed to upload profile picture. (Error code: ' . $fileError . ')';
                } else {
                    $tmpPath  = $_FILES['profile_image']['tmp_name'];
                    $fileName = $_FILES['profile_image']['name'];
                    $fileSize = $_FILES['profile_image']['size'];

                    // Max 2MB (2 * 1024 * 1024)
                    $maxSize = 2 * 1024 * 1024;
                    if ($fileSize > $maxSize) {
                        $errors[] = 'Profile picture must not exceed 2MB.';
                    }

                    // Validate extension against safe list
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($extension, $allowedExtensions, true)) {
                        $errors[] = 'Invalid image extension. Only JPG, PNG, GIF, and WEBP formats are allowed.';
                    }

                    // Validate MIME type via finfo and getimagesize
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $tmpPath);
                    finfo_close($finfo);

                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($mimeType, $allowedMimes, true)) {
                        $errors[] = 'Invalid image file type. Please upload a real image.';
                    }

                    $imageInfo = @getimagesize($tmpPath);
                    if ($imageInfo === false) {
                        $errors[] = 'Uploaded file is not a valid image.';
                    }

                    // If file is valid, generate safe unique filename
                    if (empty($errors)) {
                        $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        $safeFileName = 'avatar_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                        $destination  = $uploadDir . $safeFileName;

                        if (move_uploaded_file($tmpPath, $destination)) {
                            $newProfileImage = $safeFileName;

                            // Clean up previous image file if it exists in uploads/avatars/
                            if (!empty($user['profile_image'])) {
                                $oldFilePath = $uploadDir . basename($user['profile_image']);
                                if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                                    @unlink($oldFilePath);
                                }
                            }
                        } else {
                            $errors[] = 'Could not save the uploaded image file. Please check folder permissions.';
                        }
                    }
                }
            }

            // 3.5. Process Cover Photo Upload / Removal / Crop
            $newCoverPhoto = null;
            $removeCover = !empty($_POST['remove_cover']);
            $croppedCoverData = trim($_POST['cropped_cover_data'] ?? '');

            if ($removeCover) {
                if (!empty($user['cover_photo'])) {
                    $coverDir = __DIR__ . '/../../public/uploads/covers/';
                    $oldCoverPath = $coverDir . basename($user['cover_photo']);
                    if (file_exists($oldCoverPath) && is_file($oldCoverPath)) {
                        @unlink($oldCoverPath);
                    }
                }
                $newCoverPhoto = ''; // flag to set null in db
            } elseif (!empty($croppedCoverData)) {
                if (preg_match('#^data:(image/(?:jpeg|png|webp));base64,(.+)$#i', $croppedCoverData, $matches)) {
                    $rawCover = base64_decode($matches[2], true);
                    if ($rawCover === false) {
                        $errors[] = 'Invalid cover photo data received.';
                    } elseif (strlen($rawCover) > 5 * 1024 * 1024) {
                        $errors[] = 'Cover photo must not exceed 5MB.';
                    } else {
                        $coverInfo = @getimagesizefromstring($rawCover);
                        if ($coverInfo === false) {
                            $errors[] = 'Cover photo could not be verified as a valid image.';
                        } else {
                            $cExtension = 'jpg';
                            if ($coverInfo[2] === IMAGETYPE_PNG) {
                                $cExtension = 'png';
                            } elseif (defined('IMAGETYPE_WEBP') && $coverInfo[2] === IMAGETYPE_WEBP) {
                                $cExtension = 'webp';
                            }

                            $coverDir = __DIR__ . '/../../public/uploads/covers/';
                            if (!is_dir($coverDir)) {
                                mkdir($coverDir, 0755, true);
                            }

                            $safeCoverName = 'cover_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $cExtension;
                            $coverDestination = $coverDir . $safeCoverName;

                            if (file_put_contents($coverDestination, $rawCover) !== false) {
                                $newCoverPhoto = $safeCoverName;

                                if (!empty($user['cover_photo'])) {
                                    $oldCoverPath = $coverDir . basename($user['cover_photo']);
                                    if (file_exists($oldCoverPath) && is_file($oldCoverPath)) {
                                        @unlink($oldCoverPath);
                                    }
                                }
                            } else {
                                $errors[] = 'Could not save cover photo. Please check folder permissions.';
                            }
                        }
                    }
                } else {
                    $errors[] = 'Invalid cover photo format received.';
                }
            } elseif (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $coverError = $_FILES['cover_photo']['error'];
                if ($coverError !== UPLOAD_ERR_OK) {
                    $errors[] = 'Failed to upload cover photo. (Error code: ' . $coverError . ')';
                } else {
                    $tmpCoverPath = $_FILES['cover_photo']['tmp_name'];
                    $coverName    = $_FILES['cover_photo']['name'];
                    $coverSize    = $_FILES['cover_photo']['size'];

                    if ($coverSize > 5 * 1024 * 1024) {
                        $errors[] = 'Cover photo must not exceed 5MB.';
                    }

                    $cExtension = strtolower(pathinfo($coverName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($cExtension, $allowedExts, true)) {
                        $errors[] = 'Invalid cover photo extension. Only JPG, PNG, GIF, and WEBP formats are allowed.';
                    }

                    $coverInfo = @getimagesize($tmpCoverPath);
                    if ($coverInfo === false) {
                        $errors[] = 'Uploaded cover file is not a valid image.';
                    }

                    if (empty($errors)) {
                        $coverDir = __DIR__ . '/../../public/uploads/covers/';
                        if (!is_dir($coverDir)) {
                            mkdir($coverDir, 0755, true);
                        }

                        $safeCoverName = 'cover_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $cExtension;
                        $coverDestination = $coverDir . $safeCoverName;

                        if (move_uploaded_file($tmpCoverPath, $coverDestination)) {
                            $newCoverPhoto = $safeCoverName;

                            if (!empty($user['cover_photo'])) {
                                $oldCoverPath = $coverDir . basename($user['cover_photo']);
                                if (file_exists($oldCoverPath) && is_file($oldCoverPath)) {
                                    @unlink($oldCoverPath);
                                }
                            }
                        } else {
                            $errors[] = 'Could not save the uploaded cover photo file.';
                        }
                    }
                }
            }

            // 4. If all validations pass, save changes to DB
            if (empty($errors)) {
                try {
                    $updated = $this->userModel->updateProfile($userId, $user['full_name'], $cleanBio, $user['username']);

                    if ($newProfileImage !== null) {
                        $imageToSave = ($newProfileImage === '') ? null : $newProfileImage;
                        $this->userModel->updateProfileImage($userId, $imageToSave);
                        $_SESSION['profile_image'] = $imageToSave;
                    }

                    if ($newCoverPhoto !== null) {
                        $coverToSave = ($newCoverPhoto === '') ? null : $newCoverPhoto;
                        $this->userModel->updateCoverPhoto($userId, $coverToSave);
                    }

                    if ($updated !== false) {
                        $_SESSION['profile_image_synced'] = time();
                        $_SESSION['success_message'] = 'Your profile has been updated successfully!';
                        header('Location: ' . BASE_URL . '/index.php?action=profile');
                        exit;
                    } else {
                        $errors[] = 'Failed to update profile. Please try again.';
                    }
                } catch (PDOException $e) {
                    error_log("Profile update DB error: " . $e->getMessage());
                    if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate') !== false) {
                        $errors[] = 'Username is already taken. Please choose another.';
                    } else {
                        $errors[] = 'A database error occurred while updating your profile. Please try again.';
                    }
                } catch (Throwable $e) {
                    error_log("Profile update DB error: " . $e->getMessage());
                    $errors[] = 'We could not save your profile changes due to a system issue. Please try again.';
                }
            }
        }

        $pageTitle = 'Edit Profile';
        require __DIR__ . '/../views/profile/edit.php';
    }

    /**
     * AJAX endpoint to get list of followers or following for a user,
     * respecting the user's privacy settings.
     */
    public function getFollowList() {
        $this->requireAuth();
        $currentUserId = (int)$_SESSION['user_id'];
        $targetUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $currentUserId;
        $type = ($_GET['type'] ?? 'followers') === 'following' ? 'following' : 'followers';

        header('Content-Type: application/json');

        if ($targetUserId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user ID.']);
            exit;
        }

        $targetUser = $this->userModel->getProfileById($targetUserId);
        if (!$targetUser) {
            echo json_encode(['success' => false, 'error' => 'User not found.']);
            exit;
        }

        // Privacy Check:
        // If profile is private and current user is NOT the owner and NOT an admin:
        $isOwner = ($currentUserId === $targetUserId);
        $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
        $isPrivate = (($targetUser['privacy_followers'] ?? 'public') === 'private');

        if ($isPrivate && !$isOwner && !$isAdmin) {
            echo json_encode([
                'success' => false,
                'is_private' => true,
                'message' => 'This account has made their follower and following list private.'
            ]);
            exit;
        }

        // Fetch users
        if ($type === 'following') {
            $users = $this->followModel->getFollowing($targetUserId);
        } else {
            $users = $this->followModel->getFollowers($targetUserId);
        }

        // Add initials and formatted fields
        $formatted = [];
        foreach ($users as $u) {
            $uParts = explode(' ', trim($u['full_name']));
            $uInitials = count($uParts) >= 2
                ? strtoupper(mb_substr($uParts[0], 0, 1) . mb_substr($uParts[count($uParts)-1], 0, 1))
                : strtoupper(mb_substr($u['full_name'], 0, 2));

            $isFollowingThisUser = ($currentUserId !== (int)$u['id']) 
                ? $this->followModel->isFollowing($currentUserId, (int)$u['id']) 
                : null;

            $isFollowedByThisUser = ($currentUserId !== (int)$u['id'])
                ? $this->followModel->isFollowing((int)$u['id'], $currentUserId)
                : false;

            $formatted[] = [
                'id' => (int)$u['id'],
                'username' => $u['username'],
                'full_name' => $u['full_name'],
                'profile_image' => $u['profile_image'],
                'initials' => $uInitials,
                'is_following' => $isFollowingThisUser,
                'is_followed_by' => $isFollowedByThisUser,
                'is_self' => ($currentUserId === (int)$u['id'])
            ];
        }

        echo json_encode([
            'success' => true,
            'is_private' => false,
            'type' => $type,
            'target_name' => $targetUser['full_name'],
            'count' => count($formatted),
            'users' => $formatted
        ]);
        exit;
    }
}
