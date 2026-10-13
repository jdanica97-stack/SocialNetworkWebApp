<?php
// app/models/EventModel.php
// Model handling all database queries for Campus Events

require_once __DIR__ . '/../../config/database.php';

class EventModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Retrieve upcoming events for students (displayed on feed and events widget).
     *
     * @param int $limit
     * @return array
     */
    public function getUpcomingEvents($limit = 5) {
        $sql = "SELECT e.*, u.full_name AS organizer_name 
                FROM events e
                LEFT JOIN users u ON e.created_by = u.id
                WHERE e.event_date >= CURDATE()
                ORDER BY e.event_date ASC, e.id ASC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve all events for admin management, sorted by event_date.
     *
     * @return array
     */
    public function getAllEvents() {
        $sql = "SELECT e.*, u.full_name AS organizer_name, u.username AS organizer_username
                FROM events e
                LEFT JOIN users u ON e.created_by = u.id
                ORDER BY e.event_date DESC, e.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single event by ID.
     *
     * @param int $id
     * @return array|false
     */
    public function getEventById($id) {
        $sql = "SELECT * FROM events WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new campus event (admin action).
     *
     * @param string $title
     * @param string|null $description
     * @param string $eventDate Format YYYY-MM-DD
     * @param string|null $eventTime
     * @param string $location
     * @param int|null $createdBy
     * @return bool
     */
    public function createEvent($title, $description, $eventDate, $eventTime, $location, $createdBy = null) {
        $sql = "INSERT INTO events (title, description, event_date, event_time, location, created_by)
                VALUES (:title, :description, :event_date, :event_time, :location, :created_by)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title'       => trim($title),
            ':description' => !empty($description) ? trim($description) : null,
            ':event_date'  => $eventDate,
            ':event_time'  => !empty($eventTime) ? trim($eventTime) : null,
            ':location'    => trim($location),
            ':created_by'  => $createdBy ? (int)$createdBy : null
        ]);
    }

    /**
     * Update an existing event (admin action).
     *
     * @param int $id
     * @param string $title
     * @param string|null $description
     * @param string $eventDate Format YYYY-MM-DD
     * @param string|null $eventTime
     * @param string $location
     * @return bool
     */
    public function updateEvent($id, $title, $description, $eventDate, $eventTime, $location) {
        $sql = "UPDATE events 
                SET title = :title,
                    description = :description,
                    event_date = :event_date,
                    event_time = :event_time,
                    location = :location
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'          => (int)$id,
            ':title'       => trim($title),
            ':description' => !empty($description) ? trim($description) : null,
            ':event_date'  => $eventDate,
            ':event_time'  => !empty($eventTime) ? trim($eventTime) : null,
            ':location'    => trim($location)
        ]);
    }

    /**
     * Delete an event by ID (admin action).
     *
     * @param int $id
     * @return bool
     */
    public function deleteEvent($id) {
        $sql = "DELETE FROM events WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => (int)$id]);
    }
}
