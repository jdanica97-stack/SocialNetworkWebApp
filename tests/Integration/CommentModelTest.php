<?php
// tests/Integration/CommentModelTest.php
// Integration tests for CommentModel CRUD, post association, and access boundaries

namespace Tests\Integration;

use Tests\TestCase;
use CommentModel;
use PostModel;

class CommentModelTest extends TestCase {
    private CommentModel $commentModel;
    private PostModel $postModel;

    protected function setUp(): void {
        parent::setUp();
        $this->commentModel = new CommentModel();
        $this->postModel = new PostModel();
    }

    public function testCreateAndGetCommentsByPost(): void {
        $author = $this->createTestUser('postauthor');
        $commenter = $this->createTestUser('commenter1');
        $postId = $this->postModel->createPost($author['id'], 'Main post content');

        $commentId = $this->commentModel->createComment($postId, $commenter['id'], 'First test comment');
        $this->assertIsInt($commentId);
        $this->assertGreaterThan(0, $commentId);

        $comments = $this->commentModel->getCommentsByPost($postId);
        $this->assertCount(1, $comments);
        $this->assertEquals('First test comment', $comments[0]['content']);
        $this->assertEquals('commenter1', $comments[0]['username']);
    }

    public function testGetCommentById(): void {
        $author = $this->createTestUser('author3');
        $commenter = $this->createTestUser('commenter2');
        $postId = $this->postModel->createPost($author['id'], 'Post for getCommentById');

        $commentId = $this->commentModel->createComment($postId, $commenter['id'], 'Target comment');
        $comment = $this->commentModel->getCommentById($commentId);

        $this->assertIsArray($comment);
        $this->assertEquals($commentId, $comment['id']);
        $this->assertEquals('Target comment', $comment['content']);
        $this->assertEquals($commenter['id'], $comment['user_id']);
    }

    public function testUpdateComment(): void {
        $author = $this->createTestUser('author4');
        $commenter = $this->createTestUser('commenter3');
        $postId = $this->postModel->createPost($author['id'], 'Post content');

        $commentId = $this->commentModel->createComment($postId, $commenter['id'], 'Original comment text');

        // Author of comment can update
        $updated = $this->commentModel->updateComment($commentId, $commenter['id'], 'Edited comment text');
        $this->assertTrue($updated);

        $comment = $this->commentModel->getCommentById($commentId);
        $this->assertEquals('Edited comment text', $comment['content']);

        // Unauthorized user cannot update
        $otherUser = $this->createTestUser('stranger');
        $unauthorized = $this->commentModel->updateComment($commentId, $otherUser['id'], 'Hacked comment');
        $this->assertFalse($unauthorized);
    }

    public function testDeleteComment(): void {
        $author = $this->createTestUser('author5');
        $commenter = $this->createTestUser('commenter4');
        $postId = $this->postModel->createPost($author['id'], 'Post content');

        $commentId = $this->commentModel->createComment($postId, $commenter['id'], 'Comment to delete');

        // Unauthorized user cannot delete
        $otherUser = $this->createTestUser('otherperson');
        $unauthorized = $this->commentModel->deleteComment($commentId, $otherUser['id']);
        $this->assertFalse($unauthorized);

        // Comment owner can delete
        $deleted = $this->commentModel->deleteComment($commentId, $commenter['id']);
        $this->assertTrue($deleted);

        $commentAfter = $this->commentModel->getCommentById($commentId);
        $this->assertFalse($commentAfter);
    }
}
