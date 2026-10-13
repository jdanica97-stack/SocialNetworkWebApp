<?php
// app/views/comments/edit.php
// Edit Comment View matching the application's clean Bootstrap design

require_once __DIR__ . '/../layouts/header.php';

// Author initials for the commenter
$authorParts = explode(' ', trim($comment['full_name'] ?? 'User'));
$authorInitials = count($authorParts) >= 2
    ? strtoupper(mb_substr($authorParts[0], 0, 1) . mb_substr($authorParts[count($authorParts)-1], 0, 1))
    : strtoupper(mb_substr($comment['full_name'] ?? 'U', 0, 2));

// Post author initials
$postAuthorParts = explode(' ', trim($comment['post_author_name'] ?? 'User'));
$postAuthorInitials = count($postAuthorParts) >= 2
    ? strtoupper(mb_substr($postAuthorParts[0], 0, 1) . mb_substr($postAuthorParts[count($postAuthorParts)-1], 0, 1))
    : strtoupper(mb_substr($comment['post_author_name'] ?? 'U', 0, 2));
?>

<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        <!-- Flash Error Notifications -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Please check the errors below:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card-modern p-4 p-md-5 my-2">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="brand-icon-box" style="width: 40px; height: 40px; font-size: 1.25rem;">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">Edit Comment</h4>
                        <p class="text-muted small mb-0">Modify your comment</p>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/index.php#post-<?= (int)$comment['post_id']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
            </div>

            <!-- Post Context Box -->
            <div class="mb-4 p-3 bg-light rounded-3 border border-light-subtle">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-secondary-subtle text-secondary small">Replying to Post</span>
                    <span class="text-muted small">&bull; Posted by <strong><?= htmlspecialchars($comment['post_author_name'] ?? ''); ?></strong> (@<?= htmlspecialchars($comment['post_author_username'] ?? ''); ?>)</span>
                </div>
                <div class="text-muted small fst-italic ps-2 border-start border-3 border-warning">
                    <?= nl2br(htmlspecialchars(mb_strimwidth($comment['post_content'] ?? '', 0, 160, '...'))); ?>
                </div>
            </div>

            <!-- Comment Author Snippet -->
            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-4 border border-light-subtle">
                <?php if (!empty($comment['profile_image'])): ?>
                    <img 
                        src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars(basename($comment['profile_image'])) ?>" 
                        alt="<?= htmlspecialchars($comment['full_name']); ?>" 
                        class="rounded-circle"
                        style="width: 40px; height: 40px; object-fit: cover;"
                        onerror="this.onerror=null; this.outerHTML='<div class=\'user-avatar-sm\' style=\'width: 40px; height: 40px;\'><?= htmlspecialchars($authorInitials) ?></div>';"
                    >
                <?php else: ?>
                    <div class="user-avatar-sm" style="width: 40px; height: 40px; font-size: 0.9rem;">
                        <?= htmlspecialchars($authorInitials) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($comment['full_name']); ?></span>
                    <small class="text-muted">
                        @<?= htmlspecialchars($comment['username']); ?> &bull; Originally commented on <?= date('M j, Y \a\t g:i a', strtotime($comment['created_at'])); ?>
                    </small>
                </div>
            </div>

            <!-- Edit Comment Form -->
            <form action="<?= BASE_URL ?>/index.php?action=update_comment" method="POST">
                <?= csrf_field(); ?>
                <input type="hidden" name="comment_id" value="<?= (int)$comment['id']; ?>">

                <!-- Content Textarea -->
                <div class="mb-4">
                    <label for="commentContentInput" class="form-label fw-semibold text-secondary small">
                        Comment Content <span class="text-danger">*</span>
                    </label>
                    <textarea 
                        id="commentContentInput"
                        name="content" 
                        class="form-control" 
                        rows="4" 
                        placeholder="Write your comment..."
                        maxlength="1000"
                        required
                        autofocus
                        style="border-radius: 10px; font-size: 0.95rem;"
                    ><?= htmlspecialchars($formData['content'] ?? $comment['content']); ?></textarea>
                    <div class="form-text small text-muted d-flex justify-content-between mt-1">
                        <span>Keep comments respectful and on-topic.</span>
                        <span id="charCounter">Max 1,000 characters</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                    <a href="<?= BASE_URL ?>/index.php#post-<?= (int)$comment['post_id']; ?>" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-accent px-4 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('commentContentInput');
    const counter = document.getElementById('charCounter');
    if (textarea && counter) {
        const updateCount = function() {
            const len = textarea.value.length;
            counter.textContent = len + ' / 1000';
        };
        textarea.addEventListener('input', updateCount);
        updateCount();
    }
});
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
