<?php
// tests/Integration/NotificationModelTest.php
// Integration tests for NotificationModel (creation, unread count, read receipts)

namespace Tests\Integration;

use Tests\TestCase;
use NotificationModel;

class NotificationModelTest extends TestCase {
    private NotificationModel $notifModel;

    protected function setUp(): void {
        parent::setUp();
        $this->notifModel = new NotificationModel();
    }

    public function testCreateAndGetNotifications(): void {
        $user = $this->createTestUser('targetuser');
        $actor = $this->createTestUser('actoruser');

        $created = $this->notifModel->createNotification(
            $user['id'],
            $actor['id'],
            'like',
            42,
            'Actor User liked your post.'
        );

        $this->assertTrue($created);

        $list = $this->notifModel->getUserNotifications($user['id']);
        $this->assertCount(1, $list);
        $this->assertEquals('like', $list[0]['type']);
        $this->assertEquals('Actor User liked your post.', $list[0]['message']);
        $this->assertEquals(0, $list[0]['is_read']);
    }

    public function testGetUnreadCountAndMarkAsRead(): void {
        $user = $this->createTestUser('notifuser');
        $actor = $this->createTestUser('actor2');

        $this->assertEquals(0, $this->notifModel->getUnreadCount($user['id']));

        $id1 = $this->notifModel->createNotification($user['id'], $actor['id'], 'like', 1, 'Notice 1');
        $id2 = $this->notifModel->createNotification($user['id'], $actor['id'], 'comment', 2, 'Notice 2');

        $this->assertEquals(2, $this->notifModel->getUnreadCount($user['id']));

        // Mark single notification as read
        $marked = $this->notifModel->markAsRead($user['id'], $id1);
        $this->assertTrue($marked);
        $this->assertEquals(1, $this->notifModel->getUnreadCount($user['id']));

        // Mark all remaining notifications as read
        $markedAll = $this->notifModel->markAsRead($user['id']);
        $this->assertTrue($markedAll);
        $this->assertEquals(0, $this->notifModel->getUnreadCount($user['id']));
    }
}
