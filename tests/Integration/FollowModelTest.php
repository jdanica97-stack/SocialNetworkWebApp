<?php
// tests/Integration/FollowModelTest.php
// Integration tests for FollowModel (following, unfollowing, follower counts and lists)

namespace Tests\Integration;

use Tests\TestCase;
use FollowModel;

class FollowModelTest extends TestCase {
    private FollowModel $followModel;

    protected function setUp(): void {
        parent::setUp();
        $this->followModel = new FollowModel();
    }

    public function testFollowAndUnfollow(): void {
        $userA = $this->createTestUser('followerA');
        $userB = $this->createTestUser('followingB');

        $this->assertFalse($this->followModel->isFollowing($userA['id'], $userB['id']));

        // User A follows User B
        $followed = $this->followModel->follow($userA['id'], $userB['id']);
        $this->assertTrue($followed);
        $this->assertTrue($this->followModel->isFollowing($userA['id'], $userB['id']));

        $this->assertEquals(1, $this->followModel->getFollowersCount($userB['id']));
        $this->assertEquals(1, $this->followModel->getFollowingCount($userA['id']));

        // User A unfollows User B
        $unfollowed = $this->followModel->unfollow($userA['id'], $userB['id']);
        $this->assertTrue($unfollowed);
        $this->assertFalse($this->followModel->isFollowing($userA['id'], $userB['id']));

        $this->assertEquals(0, $this->followModel->getFollowersCount($userB['id']));
        $this->assertEquals(0, $this->followModel->getFollowingCount($userA['id']));
    }

    public function testFollowSelfIsPrevented(): void {
        $user = $this->createTestUser('selfuser');

        // A user cannot follow themselves
        $result = $this->followModel->follow($user['id'], $user['id']);
        $this->assertFalse($result);
        $this->assertFalse($this->followModel->isFollowing($user['id'], $user['id']));
    }

    public function testGetFollowersAndFollowingLists(): void {
        $user1 = $this->createTestUser('userone');
        $user2 = $this->createTestUser('usertwo');
        $user3 = $this->createTestUser('userthree');

        $this->followModel->follow($user1['id'], $user2['id']);
        $this->followModel->follow($user3['id'], $user2['id']);

        $followers = $this->followModel->getFollowers($user2['id']);
        $this->assertCount(2, $followers);

        $following = $this->followModel->getFollowing($user1['id']);
        $this->assertCount(1, $following);
        $this->assertEquals('usertwo', $following[0]['username']);
    }
}
