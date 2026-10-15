<?php
// app/views/posts/view.php
// Dedicated Single Post & Discussion View (Direct link from notifications, bookmarks, and shares)

require_once __DIR__ . '/../layouts/header.php';

$currentUserId = $_SESSION['user_id'] ?? null;

// Author initials
$authorParts = explode(' ', trim($post['full_name']));
$authorInitials = count($authorParts) >= 2
    ? strtoupper(mb_substr($authorParts[0], 0, 1) . mb_substr($authorParts[count($authorParts)-1], 0, 1))
    : strtoupper(mb_substr($post['full_name'], 0, 2));

$postTime = date('M j, Y \a\t g:i a', strtotime($post['created_at']));
$timeBadge = format_time_ago($post['created_at']);
$highlightCommentId = (int)($highlightCommentId ?? ($_GET['comment_id'] ?? 0));
?>

<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">

            <!-- Flash Notifications -->
            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Navigation Back Bar -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <a href="<?= BASE_URL ?>/" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Feed</span>
                </a>
                <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                    <i class="bi bi-chat-left-text me-1 text-primary"></i> Post Discussion
                </span>
            </div>

            <!-- Single Post Card -->
            <article class="post-card shadow-sm mb-4" id="post-<?= (int)$post['id'] ?>">
                <!-- Post Author Header -->
                <div class="post-header">
                    <div class="post-author-box">
                        <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$post['user_id']; ?>" class="text-decoration-none flex-shrink-0" title="View <?= htmlspecialchars($post['full_name']); ?>'s profile">
                            <?php if (!empty($post['profile_image'])): ?>
                                <img 
                                    src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars(basename($post['profile_image'])) ?>" 
                                    alt="<?= htmlspecialchars($post['full_name']); ?>" 
                                    class="post-avatar"
                                    onerror="this.onerror=null; this.outerHTML='<div class=\'post-avatar\'><?= htmlspecialchars($authorInitials) ?></div>';"
                                >
                            <?php else: ?>
                                <div class="post-avatar">
                                    <?= htmlspecialchars($authorInitials) ?>
                                </div>
                            <?php endif; ?>
                        </a>

                        <div>
                            <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$post['user_id']; ?>" class="post-author-name" title="View <?= htmlspecialchars($post['full_name']); ?>'s profile">
                                <?= htmlspecialchars($post['full_name']); ?>
                            </a>
                            <div class="post-meta">
                                <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$post['user_id']; ?>" class="text-muted text-decoration-none">@<?= htmlspecialchars($post['username']); ?></a> &bull; <?= htmlspecialchars($timeBadge); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Post Menu Dropdown -->
                    <?php if ($currentUserId && (int)$currentUserId === (int)$post['user_id']): ?>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Post actions">
                            <i class="bi bi-three-dots fs-5"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li>
                                <a class="dropdown-item small action-item-edit" href="<?= BASE_URL ?>/index.php?action=edit_post&post_id=<?= $post['id'] ?>">
                                     <i class="bi bi-pencil-square me-2"></i>Edit post
                                </a>
                            </li>
                            <li>
                                <form action="<?= BASE_URL ?>/index.php?action=delete_post" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" class="dropdown-item small action-item-delete border-0 bg-transparent w-100 text-start">
                                        <i class="bi bi-trash me-2"></i>Delete post
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Post Text Content -->
                <div class="post-content fs-6">
                    <?= nl2br(htmlspecialchars($post['content'])); ?>
                </div>

                <!-- Optional Post Image(s) -->
                <?php
                $postImages = !empty($post['images']) ? $post['images'] : PostModel::parseImages($post['image'] ?? null);
                $totalPostImages = count($postImages);
                ?>
                <?php if ($totalPostImages === 1): ?>
                    <?php
                    $singleImg = $postImages[0];
                    $postImgFile = __DIR__ . '/../../../public/uploads/posts/' . basename($singleImg);
                    $hasRealImage = file_exists($postImgFile);
                    ?>
                    <?php if ($hasRealImage): ?>
                        <div class="post-image-container mb-3">
                            <a href="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($singleImg)) ?>" class="post-lightbox-trigger" data-gallery="post-<?= (int)$post['id'] ?>" title="View full image">
                                <img src="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($singleImg)) ?>" alt="Post attachment" class="img-fluid rounded-3">
                            </a>
                        </div>
                    <?php endif; ?>
                <?php elseif ($totalPostImages > 1): ?>
                    <div class="d-flex flex-column gap-3 mb-3">
                        <?php foreach ($postImages as $idx => $imgFile): ?>
                            <?php
                            $realPath = __DIR__ . '/../../../public/uploads/posts/' . basename($imgFile);
                            if (!file_exists($realPath)) continue;
                            ?>
                            <div class="post-image-container mb-0">
                                <a href="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($imgFile)) ?>" class="post-lightbox-trigger" data-gallery="post-<?= (int)$post['id'] ?>" title="View image <?= $idx + 1 ?>">
                                    <img src="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($imgFile)) ?>" alt="Post attachment <?= $idx + 1 ?>" class="img-fluid rounded-3">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Post Engagement Actions (Like / Comment) -->
                <div class="post-footer-actions d-flex align-items-center py-2 border-top border-bottom my-3">
                    <div class="d-flex align-items-center gap-3">
                        <form action="<?= BASE_URL ?>/index.php?action=like_post" method="POST" class="d-inline">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <button type="submit" class="post-action-btn <?= !empty($post['is_liked']) ? 'liked' : '' ?>" title="Like">
                                <i class="bi <?= !empty($post['is_liked']) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                <span><?= (int)$post['likes_count']; ?></span>
                            </button>
                        </form>

                        <button type="button" class="post-action-btn" onclick="toggleComments(<?= $post['id'] ?>)" title="Toggle Comments" aria-label="Toggle Comments">
                            <i class="bi bi-chat"></i>
                            <span><?= (int)$post['comments_count']; ?></span>
                        </button>
                    </div>
                </div>

                <!-- Dedicated Comments Discussion Section -->
                <div class="comments-container mt-3" id="comments-box-<?= $post['id'] ?>">
                    <h6 class="fw-bold text-dark mb-3 small d-flex align-items-center gap-2">
                        <i class="bi bi-chat-dots text-primary"></i>
                        <span>Comments (<?= (int)$post['comments_count']; ?>)</span>
                    </h6>

                    <?php if (!empty($post['comments'])): ?>
                        <?php foreach ($post['comments'] as $comment): ?>
                            <?php
                            $cParts = explode(' ', trim($comment['full_name'] ?? 'User'));
                            $cInitials = count($cParts) >= 2
                                ? strtoupper(mb_substr($cParts[0], 0, 1) . mb_substr($cParts[count($cParts)-1], 0, 1))
                                : strtoupper(mb_substr($comment['full_name'] ?? 'U', 0, 2));

                            $cTimeBadge = format_time_ago($comment['created_at']);
                            $cFullTime = date('M j, Y \a\t g:i a', strtotime($comment['created_at']));
                            $isCommentTarget = ($highlightCommentId && $highlightCommentId === (int)$comment['id']);
                            ?>
                            <div class="comment-bubble <?= $isCommentTarget ? 'comment-highlight' : '' ?>" id="comment-<?= $comment['id'] ?>">
                                <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$comment['user_id']; ?>" class="text-decoration-none flex-shrink-0" title="View <?= htmlspecialchars($comment['full_name']); ?>'s profile">
                                    <?php if (!empty($comment['profile_image'])): ?>
                                        <img 
                                            src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars(basename($comment['profile_image'])) ?>" 
                                            alt="<?= htmlspecialchars($comment['full_name']); ?>" 
                                            class="comment-avatar"
                                            onerror="this.onerror=null; this.outerHTML='<div class=\'comment-avatar\'><?= htmlspecialchars($cInitials) ?></div>';"
                                        >
                                    <?php else: ?>
                                        <div class="comment-avatar">
                                            <?= htmlspecialchars($cInitials) ?>
                                        </div>
                                    <?php endif; ?>
                                </a>

                                <div class="comment-content-box">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                        <div>
                                            <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$comment['user_id']; ?>" class="comment-author text-decoration-none" title="View <?= htmlspecialchars($comment['full_name']); ?>'s profile">
                                                <?= htmlspecialchars($comment['full_name']); ?>
                                            </a>
                                            <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$comment['user_id']; ?>" class="text-muted small text-decoration-none">@<?= htmlspecialchars($comment['username']); ?></a>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <small class="text-muted" title="<?= htmlspecialchars($cFullTime); ?>">
                                                <?= htmlspecialchars($cTimeBadge); ?>
                                            </small>

                                            <?php if ($currentUserId && (int)$currentUserId === (int)$comment['user_id']): ?>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Comment options" style="line-height: 1;">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="min-width: 120px; font-size: 0.85rem;">
                                                        <li>
                                                            <a class="dropdown-item py-1 action-item-edit d-flex align-items-center gap-2" href="<?= BASE_URL ?>/index.php?action=edit_comment&comment_id=<?= $comment['id'] ?>">
                                                                <i class="bi bi-pencil-square"></i>
                                                                <span>Edit</span>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <form action="<?= BASE_URL ?>/index.php?action=delete_comment" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this comment? This action cannot be undone.');">
                                                                <?= csrf_field(); ?>
                                                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                                <button type="submit" class="dropdown-item py-1 action-item-delete d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start">
                                                                    <i class="bi bi-trash"></i>
                                                                    <span>Delete</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="comment-text">
                                        <?= nl2br(htmlspecialchars($comment['content'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small" id="noCommentsYetMsg">
                            <i class="bi bi-chat-square text-secondary fs-4 d-block mb-1 opacity-50"></i>
                            No comments yet. Be the first to share your thoughts!
                        </div>
                    <?php endif; ?>

                    <!-- Add Comment Form -->
                    <?php if ($currentUserId): ?>
                        <form action="<?= BASE_URL ?>/index.php?action=create_comment" method="POST" class="comment-input-row mt-3">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <input 
                                type="text" 
                                name="content" 
                                class="form-control" 
                                placeholder="Write a comment..." 
                                maxlength="1000" 
                                required
                            >
                            <button type="submit" class="btn btn-sm btn-accent rounded-pill px-3" title="Post comment">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-3 mt-2 bg-light rounded-3 border">
                            <a href="<?= BASE_URL ?>/index.php?action=login" class="text-muted small text-decoration-none">
                                Sign in to join the discussion
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

        </div>
    </div>
</div>

<script>
function toggleComments(postId) {
    const box = document.getElementById('comments-box-' + postId);
    if (!box) return;
    if (box.classList.contains('d-none')) {
        box.classList.remove('d-none');
        const input = box.querySelector('input[name="content"], textarea[name="content"]');
        if (input) setTimeout(() => input.focus(), 60);
    } else {
        box.classList.add('d-none');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // If a comment was specifically targeted by notification, scroll smoothly into view
    <?php if ($highlightCommentId): ?>
        const targetComment = document.getElementById('comment-<?= (int)$highlightCommentId ?>');
        if (targetComment) {
            setTimeout(function() {
                targetComment.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 250);
        }
    <?php endif; ?>

    // Clear hash/query param state on refresh so reloading leaves the highlight clear
    if (window.history && window.history.replaceState) {
        const cleanUrl = window.location.pathname + '?action=view_post&post_id=<?= (int)$post['id'] ?>';
        window.history.replaceState(null, document.title, cleanUrl);
    }
});
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
