<?php
// app/models/UserModel.php
// Model handling all database operations for users

require_once __DIR__ . '/../../config/database.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Register / create a new user.
     *
     * @param string $username
     * @param string $passwordHash
     * @param string $fullName
     * @return bool
     */
    public function create($username, $passwordHash, $fullName) {
        $sql = "INSERT INTO users (username, password, full_name) VALUES (:username, :password, :full_name)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username'  => $username,
            ':password'  => $passwordHash,
            ':full_name' => $fullName
        ]);
    }

    /**
     * Find a user by username.
     *
     * @param string $username
     * @return array|false Returns user row as associative array or false if not found.
     */
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    /**
     * Check whether a username already exists.
     *
     * @param string $username
     * @return bool True if username exists, false otherwise.
     */
    public function usernameExists($username) {
        $sql = "SELECT id FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        return (bool)$stmt->fetch();
    }

    /**
     * Get password hash for a user by ID.
     *
     * @param int $id
     * @return string|false
     */
    public function getPasswordHash($id) {
        $sql = "SELECT password FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetchColumn();
    }

    /**
     * Update user password.
     *
     * @param int $id
     * @param string $newPasswordHash
     * @return bool
     */
    public function updatePassword($id, $newPasswordHash) {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password' => $newPasswordHash,
            ':id'       => (int)$id
        ]);
    }

    /**
     * Find a user by ID.
     *
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT id, username, full_name, bio, profile_image, cover_photo, role, created_at FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get a user's profile by user ID.
     * Alias/dedicated method for retrieving safe profile details (excluding password).
     *
     * @param int $id
     * @return array|false
     */
    public function getProfileById($id) {
        return $this->findById($id);
    }

    /**
     * Update user's profile details (full_name and bio).
     *
     * @param int $id
     * @param string $fullName
     * @param string|null $bio
     * @return bool
     */
    public function updateProfile($id, $fullName, $bio) {
        $sql = "UPDATE users SET full_name = :full_name, bio = :bio WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':full_name' => $fullName,
            ':bio'       => $bio,
            ':id'        => $id
        ]);
    }

    /**
     * Update user's profile image.
     *
     * @param int $id
     * @param string|null $profileImage Filename or path relative to avatars folder, or null to remove
     * @return bool
     */
    public function updateProfileImage($id, $profileImage) {
        $sql = "UPDATE users SET profile_image = :profile_image WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':profile_image' => $profileImage,
            ':id'            => $id
        ]);
    }

    /**
     * Update user's cover photo.
     *
     * @param int $id
     * @param string|null $coverPhoto Filename relative to covers folder, or null to remove
     * @return bool
     */
    public function updateCoverPhoto($id, $coverPhoto) {
        $sql = "UPDATE users SET cover_photo = :cover_photo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':cover_photo' => $coverPhoto,
            ':id'          => $id
        ]);
    }

    /**
     * Search users by username or full name (case-insensitive partial matching).
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchUsers($query) {
        $searchTerm = '%' . trim($query) . '%';
        $sql = "SELECT u.id, u.username, u.full_name, u.bio, u.profile_image, u.created_at,
                       (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id) AS total_user_posts
                FROM users u
                WHERE u.username LIKE ? OR u.full_name LIKE ?
                ORDER BY u.full_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    /**
     * Get all users for filtering purposes.
     *
     * @return array
     */
    public function getAllUsers() {
        $sql = "SELECT id, username, full_name FROM users ORDER BY full_name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}

