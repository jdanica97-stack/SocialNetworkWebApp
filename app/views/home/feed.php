<?php
// app/views/home/feed.php
// Main Social Feed and Dashboard view matching reference design

$currentUserId = $_SESSION['user_id'] ?? null;
?>

<div class="feed-layout-container mx-auto">

    <!-- Flash Notifications -->
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="row g-4">
        <!-- ==============================================
             CENTER COLUMN: POST COMPOSER & SOCIAL FEED
             ============================================== -->
        <div class="col-lg-7 col-xl-8">

            <?php if (!$currentUserId): ?>
                <!-- Guest Landing Home Page (No social posts exposed to guests) -->
                <div class="card-modern p-5 text-center mb-4">
                    <div class="mb-3">
                        <div class="brand-icon-box mx-auto" style="width: 58px; height: 58px; font-size: 1.8rem; border-radius: 14px;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-dark mb-2">Welcome to SMCC Connect</h2>
                    <p class="text-muted fs-6 mb-4 col-md-10 mx-auto">
                        The campus social networking platform for SMCC. Sign in or create an account to view campus posts, connect with fellow students, comment, and engage with the community.
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="<?= BASE_URL ?>/index.php?action=login" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-medium">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                        </a>
                        <a href="<?= BASE_URL ?>/index.php?action=register" class="btn btn-accent px-4 py-2 rounded-pill fw-medium">
                            <i class="bi bi-person-plus-fill me-1"></i> Create Account
                        </a>
                    </div>
                </div>

                <!-- Campus Highlights Cards for Guests -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card-modern p-4 text-center h-100">
                            <i class="bi bi-chat-heart text-primary fs-2 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark">Social Newsfeed</h6>
                            <p class="small text-muted mb-0">Share your thoughts, photos, and updates with fellow students.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-modern p-4 text-center h-100">
                            <i class="bi bi-search text-warning fs-2 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark">Search & Discover</h6>
                            <p class="small text-muted mb-0">Easily find peers, professors, and discussions across campus.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-modern p-4 text-center h-100">
                            <i class="bi bi-shield-check text-success fs-2 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark">Protected Community</h6>
                            <p class="small text-muted mb-0">Private to verified members of the SMCC collegiate network.</p>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- 1. Post Creation Card -->
                <div class="composer-card">
                    <form action="<?= BASE_URL ?>/index.php?action=create_post" method="POST" enctype="multipart/form-data">
                        <?= csrf_field(); ?>
                        <div class="composer-input-row">
                            <?php if (!empty($userAvatarPath)): ?>
                                <img 
                                    src="<?= $userAvatarPath ?>" 
                                    alt="<?= htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>" 
                                    class="user-avatar-sm" 
                                    style="width: 40px; height: 40px; object-fit: cover;"
                                    onerror="this.onerror=null; this.outerHTML='<div class=&quot;user-avatar-sm&quot; style=&quot;width: 40px; height: 40px; font-size: 0.9rem;&quot;><?= htmlspecialchars($userInitials ?? 'U') ?></div>';"
                                >
                            <?php else: ?>
                                <div class="user-avatar-sm" style="width: 40px; height: 40px; font-size: 0.9rem;">
                                    <?= htmlspecialchars($userInitials ?? 'U'); ?>
                                </div>
                            <?php endif; ?>
                            <textarea 
                                name="content" 
                                class="composer-input" 
                                rows="2" 
                                placeholder="What's happening on campus?"
                                required
                            ></textarea>
                        </div>

                        <!-- Image Preview Box (Hidden until selected) -->
                        <div id="imagePreviewContainer" class="mt-2 ps-5 d-none">
                            <div class="position-relative d-inline-block">
                                <img id="imagePreview" src="#" alt="Preview" style="max-height: 120px; border-radius: 8px;" class="border">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-0" style="width: 20px; height: 20px; line-height: 18px;" onclick="clearSelectedImage()">
                                    &times;
                                </button>
                            </div>
                        </div>

                        <div class="composer-actions-row">
                            <div class="d-flex align-items-center gap-2">
                                <label for="postImageInput" class="composer-tool-btn mb-0" title="Upload Photo" style="cursor: pointer;">
                                    <i class="bi bi-image text-secondary"></i>
                                </label>
                                <input type="file" id="postImageInput" name="post_image" accept="image/*" class="d-none" onchange="previewImage(this)">
                            </div>
                            <button type="submit" class="btn btn-accent btn-sm px-4">
                                Post
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Feed Header and Filter Bar -->
                <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-rss-fill text-warning me-2"></i>Campus Feed
                    </h6>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Feed Filter">
                        <a href="<?= BASE_URL ?>/index.php?filter=newest" class="btn <?= (!isset($_GET['filter']) || $_GET['filter'] === 'newest') ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                            Newest
                        </a>
                        <a href="<?= BASE_URL ?>/index.php?filter=oldest" class="btn <?= (isset($_GET['filter']) && $_GET['filter'] === 'oldest') ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                            Oldest
                        </a>
                    </div>
                </div>

                <!-- 2. Social Posts Feed -->
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                    <?php
                    // Compute author initials
                    $authorParts = explode(' ', trim($post['full_name']));
                    $authorInitials = count($authorParts) >= 2
                        ? strtoupper(mb_substr($authorParts[0], 0, 1) . mb_substr($authorParts[count($authorParts)-1], 0, 1))
                        : strtoupper(mb_substr($post['full_name'], 0, 2));

                    // Time formatting
                    $postTime = date('M j, Y \a\t g:i a', strtotime($post['created_at']));
                    $timeBadge = format_time_ago($post['created_at']);
                    ?>
                    <article class="post-card" id="post-<?= $post['id'] ?>">
                        <!-- Post Author Header -->
                        <div class="post-header">
                            <div class="post-author-box">
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

                                <div>
                                    <a href="#" class="post-author-name"><?= htmlspecialchars($post['full_name']); ?></a>
                                    <div class="post-meta">
                                        @<?= htmlspecialchars($post['username']); ?> &bull; <?= htmlspecialchars($timeBadge); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Post Menu Dropdown -->
                            <?php if ($currentUserId && $currentUserId == $post['user_id']): ?>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Post actions">
                                    <i class="bi bi-three-dots fs-5"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li>
                                        <a class="dropdown-item small text-secondary" href="<?= BASE_URL ?>/index.php?action=edit_post&post_id=<?= $post['id'] ?>">
                                             <i class="bi bi-pencil-square me-2 text-muted"></i>Edit post
                                        </a>
                                    </li>
                                    <li>
                                        <form action="<?= BASE_URL ?>/index.php?action=delete_post" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                            <button type="submit" class="dropdown-item small text-secondary border-0 bg-transparent w-100 text-start">
                                                <i class="bi bi-trash me-2 text-muted"></i>Delete post
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Post Text Content -->
                        <div class="post-content">
                            <?= nl2br(htmlspecialchars($post['content'])); ?>
                        </div>

                        <!-- Optional Post Image -->
                        <?php if (!empty($post['image'])): ?>
                            <?php
                            $postImgFile = __DIR__ . '/../../../public/uploads/posts/' . basename($post['image']);
                            $hasRealImage = file_exists($postImgFile);
                            ?>
                            <?php if ($hasRealImage): ?>
                                <div class="post-image-container">
                                    <img src="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($post['image'])) ?>" alt="Post attachment">
                                </div>
                            <?php else: ?>
                                <div class="post-image-placeholder">
                                    <i class="bi bi-image"></i>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Post Engagement Actions (Like / Comment) -->
                        <div class="post-footer-actions d-flex align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <form action="<?= BASE_URL ?>/index.php?action=like_post" method="POST" class="d-inline">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" class="post-action-btn <?= !empty($post['is_liked']) ? 'liked' : '' ?>" title="Like">
                                        <i class="bi <?= !empty($post['is_liked']) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                        <span><?= (int)$post['likes_count']; ?></span>
                                    </button>
                                </form>

                                <button type="button" class="post-action-btn" onclick="toggleComments(<?= $post['id'] ?>)" title="Comments">
                                    <i class="bi bi-chat"></i>
                                    <span><?= (int)$post['comments_count']; ?></span>
                                </button>
                            </div>
                        </div>

                        <!-- Comments Section -->
                        <div class="comments-container" id="comments-box-<?= $post['id'] ?>">
                            <?php if (!empty($post['comments'])): ?>
                                <?php foreach ($post['comments'] as $comment): ?>
                                    <?php
                                    $cParts = explode(' ', trim($comment['full_name'] ?? 'User'));
                                    $cInitials = count($cParts) >= 2
                                        ? strtoupper(mb_substr($cParts[0], 0, 1) . mb_substr($cParts[count($cParts)-1], 0, 1))
                                        : strtoupper(mb_substr($comment['full_name'] ?? 'U', 0, 2));

                                    $cTimeBadge = format_time_ago($comment['created_at']);
                                    $cFullTime = date('M j, Y \a\t g:i a', strtotime($comment['created_at']));
                                    ?>
                                    <div class="comment-bubble" id="comment-<?= $comment['id'] ?>">
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

                                        <div class="comment-content-box">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                                <div>
                                                    <span class="comment-author"><?= htmlspecialchars($comment['full_name']); ?></span>
                                                    <span class="text-muted small">@<?= htmlspecialchars($comment['username']); ?></span>
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
                                                                    <a class="dropdown-item py-1 text-secondary d-flex align-items-center gap-2" href="<?= BASE_URL ?>/index.php?action=edit_comment&comment_id=<?= $comment['id'] ?>">
                                                                        <i class="bi bi-pencil-square text-muted"></i>
                                                                        <span>Edit</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <form action="<?= BASE_URL ?>/index.php?action=delete_comment" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this comment? This action cannot be undone.');">
                                                                        <?= csrf_field(); ?>
                                                                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                                        <button type="submit" class="dropdown-item py-1 text-secondary d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start">
                                                                            <i class="bi bi-trash text-muted"></i>
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
                            <?php endif; ?>

                            <!-- Add Comment Form -->
                            <?php if ($currentUserId): ?>
                                <form action="<?= BASE_URL ?>/index.php?action=create_comment" method="POST" class="comment-input-row">
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
                                <div class="text-center py-2">
                                    <a href="<?= BASE_URL ?>/index.php?action=login" class="text-muted small text-decoration-none">
                                        Sign in to comment
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card-modern p-5 text-center text-muted">
                    <i class="bi bi-chat-square-text fs-1 mb-3 text-secondary"></i>
                    <h5>No posts yet</h5>
                    <p class="small mb-0">Be the first to share an update on SMCC Connect!</p>
                </div>
            <?php endif; ?>
            <?php endif; // End if (!$currentUserId) ?>
        </div>

        <!-- ==============================================
             RIGHT SIDEBAR: UPCOMING EVENTS
             ============================================== -->
        <div class="col-lg-5 col-xl-4">

            <!-- 1. Upcoming Events Card -->
            <div class="widget-card">
                <div class="widget-header d-flex justify-content-between align-items-center">
                    <h6 class="widget-title mb-0">Upcoming Events</h6>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/index.php?action=admin&tab=events" class="widget-link small text-primary fw-semibold">
                            <i class="bi bi-gear-fill me-1"></i>Manage
                        </a>
                    <?php else: ?>
                        <span class="badge bg-light text-secondary border micro-text">Campus Calendar</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($upcomingEvents)): ?>
                    <?php foreach ($upcomingEvents as $evt): ?>
                        <?php
                            $evtTimestamp = !empty($evt['event_date']) ? strtotime($evt['event_date']) : time();
                            $evtMonth = strtoupper(date('M', $evtTimestamp));
                            $evtDay = date('d', $evtTimestamp);
                            $dateFormatted = date('M d', $evtTimestamp);
                            if (!empty($evt['event_time'])) {
                                $dateFormatted .= ' &bull; ' . htmlspecialchars($evt['event_time']);
                            }
                        ?>
                        <div class="event-item">
                            <div class="event-date-box">
                                <span class="event-month"><?= htmlspecialchars($evtMonth); ?></span>
                                <span class="event-day"><?= htmlspecialchars($evtDay); ?></span>
                            </div>
                            <div class="event-info">
                                <div class="event-title"><?= htmlspecialchars($evt['title']); ?></div>
                                <div class="event-meta">
                                    <?= $dateFormatted; ?><br>
                                    <span class="text-secondary"><?= htmlspecialchars($evt['location']); ?></span>
                                    <?php if (!empty($evt['description'])): ?>
                                        <div class="micro-text text-muted mt-1 text-truncate" style="max-width: 190px;" title="<?= htmlspecialchars($evt['description']); ?>">
                                            <?= htmlspecialchars($evt['description']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <i class="bi bi-calendar-event event-icon text-muted"></i>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-3 text-center text-muted small">
                        <i class="bi bi-calendar-check fs-4 d-block mb-1 opacity-50"></i>
                        No upcoming events scheduled right now.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleComments(postId) {
    const box = document.getElementById('comments-box-' + postId);
    if (box) {
        box.classList.toggle('d-none');
    }
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const container = document.getElementById('imagePreviewContainer');
            if (preview && container) {
                preview.src = e.target.result;
                container.classList.remove('d-none');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearSelectedImage() {
    const input = document.getElementById('postImageInput');
    const container = document.getElementById('imagePreviewContainer');
    if (input) input.value = '';
    if (container) container.classList.add('d-none');
}
</script>
