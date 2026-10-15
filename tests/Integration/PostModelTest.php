<?php
// tests/Integration/PostModelTest.php
// Integration tests for PostModel CRUD, likes, engagement metrics, and searching

namespace Tests\Integration;

use Tests\TestCase;
use PostModel;

class PostModelTest extends TestCase {
    private PostModel $postModel;

    protected function setUp(): void {
        parent::setUp();
        $this->postModel = new PostModel();
    }

    public function testCreateAndGetPostById(): void {
        $user = $this->createTestUser('postauthor');

        $postId = $this->postModel->createPost($user['id'], 'Hello world from test!');
        $this->assertIsInt($postId);
        $this->assertGreaterThan(0, $postId);

        $post = $this->postModel->getPostById($postId);
        $this->assertIsArray($post);
        $this->assertEquals('Hello world from test!', $post['content']);
        $this->assertEquals($user['id'], $post['user_id']);
        $this->assertEquals('postauthor', $post['username']);
    }

    public function testUpdatePost(): void {
        $user = $this->createTestUser('updater');
        $postId = $this->postModel->createPost($user['id'], 'Initial content');

        // Author should successfully update post
        $updated = $this->postModel->updatePost($postId, $user['id'], 'Updated content');
        $this->assertTrue($updated);

        $post = $this->postModel->getPostById($postId);
        $this->assertEquals('Updated content', $post['content']);

        // Another user should NOT be able to update someone else's post
        $otherUser = $this->createTestUser('intruder');
        $unauthorizedUpdate = $this->postModel->updatePost($postId, $otherUser['id'], 'Hacked content');
        $this->assertFalse($unauthorizedUpdate);

        $postAfter = $this->postModel->getPostById($postId);
        $this->assertEquals('Updated content', $postAfter['content']);
    }

    public function testDeletePost(): void {
        $user = $this->createTestUser('deleter');
        $postId = $this->postModel->createPost($user['id'], 'Post to be deleted');

        // Unauthorized user cannot delete
        $otherUser = $this->createTestUser('otheruser');
        $unauthorized = $this->postModel->deletePost($postId, $otherUser['id']);
        $this->assertFalse($unauthorized);

        // Author can delete
        $deleted = $this->postModel->deletePost($postId, $user['id']);
        $this->assertTrue($deleted);

        $post = $this->postModel->getPostById($postId);
        $this->assertFalse($post);
    }

    public function testToggleLikeAndLikesCount(): void {
        $author = $this->createTestUser('author1');
        $fan = $this->createTestUser('fan1');
        $postId = $this->postModel->createPost($author['id'], 'Likeable post');

        $this->assertEquals(0, $this->postModel->getLikesCount($postId));

        // Like the post
        $liked = $this->postModel->toggleLike($postId, $fan['id']);
        $this->assertTrue($liked, 'First toggle should like the post');
        $this->assertEquals(1, $this->postModel->getLikesCount($postId));

        // Unlike the post
        $unliked = $this->postModel->toggleLike($postId, $fan['id']);
        $this->assertFalse($unliked, 'Second toggle should unlike the post');
        $this->assertEquals(0, $this->postModel->getLikesCount($postId));
    }

    public function testGetPostWithDetails(): void {
        $author = $this->createTestUser('author2');
        $fan = $this->createTestUser('fan2');
        $postId = $this->postModel->createPost($author['id'], 'Post with details');

        $this->postModel->toggleLike($postId, $fan['id']);
        $this->postModel->addComment($postId, $fan['id'], 'Great post!');

        $details = $this->postModel->getPostWithDetails($postId, $fan['id']);
        $this->assertIsArray($details);
        $this->assertEquals(1, $details['likes_count']);
        $this->assertEquals(1, $details['comments_count']);
        $this->assertEquals(1, $details['is_liked']);
        $this->assertCount(1, $details['comments']);
        $this->assertEquals('Great post!', $details['comments'][0]['content']);
    }

    public function testSearchPostsWithFilter(): void {
        $user = $this->createTestUser('searchauthor');
        $this->postModel->createPost($user['id'], 'Special keyword PHP testing');
        $this->postModel->createPost($user['id'], 'Another regular post');

        $results = $this->postModel->searchPosts('PHP testing', 'newest', $user['id']);
        $this->assertCount(1, $results);
        $this->assertStringContainsString('Special keyword PHP testing', $results[0]['content']);
    }

    public function testMultiImagePostCreationAndParsing(): void {
        $user = $this->createTestUser('galleryuser');
        $images = ['post_1_test1.jpg', 'post_1_test2.png', 'post_1_test3.webp'];

        $postId = $this->postModel->createPost($user['id'], 'Gallery post test', $images);
        $this->assertIsInt($postId);

        $post = $this->postModel->getPostById($postId);
        $this->assertIsArray($post);
        $this->assertIsArray($post['images']);
        $this->assertCount(3, $post['images']);
        $this->assertEquals($images, $post['images']);

        // Update post with modified image list
        $newImages = ['post_1_test1.jpg', 'post_1_test4.jpg'];
        $updated = $this->postModel->updatePost($postId, $user['id'], 'Updated gallery post', $newImages);
        $this->assertTrue($updated);

        $postAfter = $this->postModel->getPostById($postId);
        $this->assertCount(2, $postAfter['images']);
        $this->assertEquals($newImages, $postAfter['images']);
    }

    public function testParseImagesHelper(): void {
        $this->assertSame([], PostModel::parseImages(null));
        $this->assertSame([], PostModel::parseImages(''));
        $this->assertSame([], PostModel::parseImages('[]'));
        $this->assertSame(['single.jpg'], PostModel::parseImages('single.jpg'));
        $this->assertSame(['a.jpg', 'b.png'], PostModel::parseImages(json_encode(['a.jpg', 'b.png'])));
        $this->assertSame(['x.jpg', 'y.png'], PostModel::parseImages(['x.jpg', 'y.png']));
    }
}
