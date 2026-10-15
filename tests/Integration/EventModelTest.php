<?php
// tests/Integration/EventModelTest.php
// Integration tests for Campus Event operations (create, update, details retrieval, delete)

namespace Tests\Integration;

use Tests\TestCase;
use EventModel;

class EventModelTest extends TestCase {
    private EventModel $eventModel;

    protected function setUp(): void {
        parent::setUp();
        $this->eventModel = new EventModel();
    }

    public function testCreateAndGetEventById(): void {
        $admin = $this->createTestUser('eventadmin', 'AdminPass123!', 'Event Admin', 'admin');

        $created = $this->eventModel->createEvent(
            'Grand Alumni Homecoming',
            'All alumni batches are welcome to attend the gala night.',
            '2026-11-20',
            '6:00 PM',
            'SMCC Gymnasium',
            $admin['id']
        );
        $this->assertTrue($created);

        $events = $this->eventModel->getAllEvents();
        $this->assertCount(1, $events);
        $eventId = (int)$events[0]['id'];

        $event = $this->eventModel->getEventById($eventId);
        $this->assertIsArray($event);
        $this->assertEquals('Grand Alumni Homecoming', $event['title']);
        $this->assertEquals('All alumni batches are welcome to attend the gala night.', $event['description']);
        $this->assertEquals('2026-11-20', $event['event_date']);
        $this->assertEquals('6:00 PM', $event['event_time']);
        $this->assertEquals('SMCC Gymnasium', $event['location']);
    }

    public function testUpdateEventInformation(): void {
        $admin = $this->createTestUser('admineditor', 'Pass123!', 'Editor Admin', 'admin');

        $this->eventModel->createEvent(
            'Original Event Title',
            'Original description',
            '2026-12-01',
            '9:00 AM',
            'Old Location',
            $admin['id']
        );

        $events = $this->eventModel->getAllEvents();
        $eventId = (int)$events[0]['id'];

        // Update event with complete new details
        $updated = $this->eventModel->updateEvent(
            $eventId,
            'Updated Event Title',
            'Updated full description with schedule and guidelines',
            '2026-12-05',
            '10:30 AM',
            'New Main Auditorium'
        );
        $this->assertTrue($updated);

        $refreshed = $this->eventModel->getEventById($eventId);
        $this->assertEquals('Updated Event Title', $refreshed['title']);
        $this->assertEquals('Updated full description with schedule and guidelines', $refreshed['description']);
        $this->assertEquals('2026-12-05', $refreshed['event_date']);
        $this->assertEquals('10:30 AM', $refreshed['event_time']);
        $this->assertEquals('New Main Auditorium', $refreshed['location']);
    }

    public function testGetUpcomingEventsFiltersPastEvents(): void {
        $admin = $this->createTestUser('adminplanner');

        $futureDate = date('Y-m-d', strtotime('+7 days'));
        $pastDate = date('Y-m-d', strtotime('-7 days'));

        $this->eventModel->createEvent('Future Tech Summit', 'Future event description', $futureDate, '1:00 PM', 'Hall A', $admin['id']);
        $this->eventModel->createEvent('Past Hackathon', 'Past event description', $pastDate, '8:00 AM', 'Lab 1', $admin['id']);

        $upcoming = $this->eventModel->getUpcomingEvents(10);
        $this->assertCount(1, $upcoming);
        $this->assertEquals('Future Tech Summit', $upcoming[0]['title']);
    }

    public function testDeleteEvent(): void {
        $admin = $this->createTestUser('admindeletor');

        $this->eventModel->createEvent('Event to Delete', 'Description', '2026-11-15', '2:00 PM', 'Room 101', $admin['id']);

        $events = $this->eventModel->getAllEvents();
        $eventId = (int)$events[0]['id'];

        $deleted = $this->eventModel->deleteEvent($eventId);
        $this->assertTrue($deleted);

        $event = $this->eventModel->getEventById($eventId);
        $this->assertFalse($event);
    }
}
