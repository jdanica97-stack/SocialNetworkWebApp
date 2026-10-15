<?php
// tests/Integration/MessageModelTest.php
// Integration tests for direct messaging, conversation threads, and read receipts

namespace Tests\Integration;

use Tests\TestCase;
use MessageModel;

class MessageModelTest extends TestCase {
    private MessageModel $messageModel;

    protected function setUp(): void {
        parent::setUp();
        $this->messageModel = new MessageModel();
    }

    public function testSendMessageAndRetrieveConversation(): void {
        $userA = $this->createTestUser('usera', 'Pass123!', 'User Alpha');
        $userB = $this->createTestUser('userb', 'Pass123!', 'User Beta');

        $sent = $this->messageModel->sendMessage($userA['id'], $userB['id'], 'Hello Beta!');
        $this->assertIsInt($sent);
        $this->assertGreaterThan(0, $sent);

        $sentReply = $this->messageModel->sendMessage($userB['id'], $userA['id'], 'Hi Alpha, how are you?');
        $this->assertIsInt($sentReply);
        $this->assertGreaterThan(0, $sentReply);

        $messages = $this->messageModel->getMessagesBetween($userA['id'], $userB['id']);
        $this->assertCount(2, $messages);
        $this->assertEquals('Hello Beta!', $messages[0]['message']);
        $this->assertEquals('Hi Alpha, how are you?', $messages[1]['message']);
    }

    public function testMarkAsReadAndUnreadCount(): void {
        $sender = $this->createTestUser('senderman');
        $receiver = $this->createTestUser('receiverman');

        $this->assertEquals(0, $this->messageModel->getTotalUnreadCount($receiver['id']));

        // Send two unread messages
        $this->messageModel->sendMessage($sender['id'], $receiver['id'], 'Unread message 1');
        $this->messageModel->sendMessage($sender['id'], $receiver['id'], 'Unread message 2');

        $this->assertEquals(2, $this->messageModel->getTotalUnreadCount($receiver['id']));

        // Receiver marks sender's messages as read
        $this->messageModel->markAsRead($receiver['id'], $sender['id']);

        $this->assertEquals(0, $this->messageModel->getTotalUnreadCount($receiver['id']));
    }

    public function testGetConversationsSummary(): void {
        $mainUser = $this->createTestUser('mainuser');
        $friend1 = $this->createTestUser('friend1');
        $friend2 = $this->createTestUser('friend2');

        $this->messageModel->sendMessage($mainUser['id'], $friend1['id'], 'Message to friend 1');
        $this->messageModel->sendMessage($friend2['id'], $mainUser['id'], 'Message from friend 2');

        $conversations = $this->messageModel->getConversations($mainUser['id']);
        $this->assertCount(2, $conversations);
    }

    public function testEditMessageByAuthor(): void {
        $sender = $this->createTestUser('editsender');
        $receiver = $this->createTestUser('editreceiver');

        $msgId = $this->messageModel->sendMessage($sender['id'], $receiver['id'], 'Original content');
        $this->assertIsInt($msgId);

        // Edit message as author
        $edited = $this->messageModel->updateMessage($msgId, $sender['id'], 'Updated content here');
        $this->assertTrue($edited);

        $fetched = $this->messageModel->getMessageById($msgId);
        $this->assertNotNull($fetched);
        $this->assertEquals('Updated content here', $fetched['message']);
        $this->assertEquals(1, (int)$fetched['is_edited']);
    }

    public function testEditMessageUnauthorizedFails(): void {
        $sender = $this->createTestUser('origauthor');
        $receiver = $this->createTestUser('impostor');

        $msgId = $this->messageModel->sendMessage($sender['id'], $receiver['id'], 'Author only message');

        // Receiver attempts to edit sender's message
        $result = $this->messageModel->updateMessage($msgId, $receiver['id'], 'Hacked content');
        $this->assertFalse($result);

        // Verify message content was not changed
        $fetched = $this->messageModel->getMessageById($msgId);
        $this->assertEquals('Author only message', $fetched['message']);
    }

    public function testUnsendMessageByAuthor(): void {
        $sender = $this->createTestUser('unsendauthor');
        $receiver = $this->createTestUser('unsendpeer');

        $msgId = $this->messageModel->sendMessage($sender['id'], $receiver['id'], 'Message to recall');
        $this->assertIsInt($msgId);

        // Verify message exists
        $this->assertNotEmpty($this->messageModel->getMessageById($msgId));

        // Author unsends message
        $unsent = $this->messageModel->unsendMessage($msgId, $sender['id']);
        $this->assertTrue($unsent);

        // Message should be marked as unsent
        $fetched = $this->messageModel->getMessageById($msgId);
        $this->assertNotEmpty($fetched);
        $this->assertEquals(1, (int)$fetched['is_unsent']);

        // Attempting to edit an unsent message should fail
        $editAttempt = $this->messageModel->updateMessage($msgId, $sender['id'], 'Attempting edit on unsent');
        $this->assertFalse($editAttempt);

        // Chat history preserves message with is_unsent = 1
        $messages = $this->messageModel->getMessagesBetween($sender['id'], $receiver['id']);
        $this->assertCount(1, $messages);
        $this->assertEquals(1, (int)$messages[0]['is_unsent']);
    }


    public function testUnsendMessageUnauthorizedFails(): void {
        $sender = $this->createTestUser('trueowner');
        $otherUser = $this->createTestUser('otherperson');

        $msgId = $this->messageModel->sendMessage($sender['id'], $otherUser['id'], 'Permanent note');

        // Non-sender attempts to unsend
        $result = $this->messageModel->unsendMessage($msgId, $otherUser['id']);
        $this->assertFalse($result);

        // Message still exists
        $this->assertNotEmpty($this->messageModel->getMessageById($msgId));
    }
}

