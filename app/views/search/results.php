<?php
// app/views/search/results.php
// Centered Modern Search, Filtering & Reports View for SMCC Connect (Step 11 & Step 12)

$currentUserId = $_SESSION['user_id'] ?? null;
$currentUserInitials = 'U';
if ($currentUserId) {
    $name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
    $parts = explode(' ', trim($name));
    $currentUserInitials = count($parts) >= 2
        ? strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1))
        : strtoupper(mb_substr($name, 0, 2));
}

$userCount = count($users ?? []);
$postCount = count($posts ?? []);
$authorId = $_GET['author_id'] ?? null;
?>

<div class="search-view-container">

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

    <!-- 1. Search & Reports Header Hero -->
    <div class="search-hero">
        <div class="d-inline-flex align-items-center gap-2 mb-2">
            <span class="badge bg-primary text-white fw-bold px-3 py-1 rounded-pill">
                <i class="bi bi-search me-1"></i> Search & Filtering (Step 11)
            </span>
            <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill">
                <i class="bi bi-bar-chart-fill me-1"></i> Summary & Reports (Step 12)
            </span>
        </div>
        <h3 class="search-hero-title">Search & Analytics Reports</h3>
        <p class="search-hero-subtitle">Search users or posts, filter by author, sort results, and explore live engagement statistics</p>
    </div>

    <!-- 2. STEP 12 A: SUMMARY REPORTS KPI CARDS -->
    <div class="row g-2 g-sm-3 mb-4">
        <!-- Matching Posts -->
        <div class="col-6 col-md-3">
            <div class="card-modern p-3 text-center border-0 shadow-sm h-100" style="border-top: 3px solid #3b82f6 !important;">
                <div class="text-muted small fw-semibold mb-1">
                    <i class="bi bi-file-earmark-post text-primary me-1"></i> Matching Posts
                </div>
                <h4 class="fw-bold text-dark mb-0"><?= (int)($summaryStats['total_posts'] ?? 0); ?></h4>
                <small class="text-muted micro-text">Filtered database records</small>
            </div>
        </div>

        <!-- Distinct Authors -->
        <div class="col-6 col-md-3">
            <div class="card-modern p-3 text-center border-0 shadow-sm h-100" style="border-top: 3px solid #10b981 !important;">
                <div class="text-muted small fw-semibold mb-1">
                    <i class="bi bi-people text-success me-1"></i> Distinct Authors
                </div>
                <h4 class="fw-bold text-dark mb-0"><?= (int)($summaryStats['total_authors'] ?? 0); ?></h4>
                <small class="text-muted micro-text">Unique content creators</small>
            </div>
        </div>

        <!-- Total Comments -->
        <div class="col-6 col-md-3">
            <div class="card-modern p-3 text-center border-0 shadow-sm h-100" style="border-top: 3px solid #f59e0b !important;">
                <div class="text-muted small fw-semibold mb-1">
                    <i class="bi bi-chat-left-text text-warning me-1"></i> Total Comments
                </div>
                <h4 class="fw-bold text-dark mb-0"><?= (int)($summaryStats['total_comments'] ?? 0); ?></h4>
                <small class="text-muted micro-text">On matching posts</small>
            </div>
        </div>

        <!-- Total Likes -->
        <div class="col-6 col-md-3">
            <div class="card-modern p-3 text-center border-0 shadow-sm h-100" style="border-top: 3px solid #ef4444 !important;">
                <div class="text-muted small fw-semibold mb-1">
                    <i class="bi bi-heart text-danger me-1"></i> Total Likes
                </div>
                <h4 class="fw-bold text-dark mb-0"><?= (int)($summaryStats['total_likes'] ?? 0); ?></h4>
                <small class="text-muted micro-text">Engagement interactions</small>
            </div>
        </div>
    </div>

    <!-- 3. SEARCH BAR AND FILTERS FORM -->
    <form action="<?= BASE_URL ?>/index.php" method="GET" class="mb-4" role="search">
        <input type="hidden" name="action" value="search">
        <input type="hidden" name="type" value="<?= htmlspecialchars($type); ?>">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter); ?>">

        <!-- Search Input Box -->
        <div class="search-box-centered mb-3">
            <i class="bi bi-search search-icon"></i>
            <input 
                type="text" 
                name="q" 
                class="search-input-centered" 
                placeholder="Search users or post content..." 
                value="<?= htmlspecialchars($query ?? ''); ?>" 
                autocomplete="off"
            >
            <?php if (!empty($query) || !empty($authorId)): ?>
                <a href="<?= BASE_URL ?>/index.php?action=search" class="search-clear-btn" title="Reset Search & Filters">&times;</a>
            <?php endif; ?>
        </div>

        <!-- Author Filter Dropdown & Filter Controls -->
        <div class="card-modern p-3 mb-3 border-0 shadow-sm">
            <div class="row g-2 align-items-center">
                <!-- Author Filter Dropdown -->
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center gap-2">
                        <label for="author_id_select" class="form-label text-muted small fw-bold mb-0 text-nowrap">
                            <i class="bi bi-funnel-fill text-primary me-1"></i> Author Filter:
                        </label>
                        <select name="author_id" id="author_id_select" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                            <option value="">All Authors</option>
                            <?php if (!empty($allAuthors)): ?>
                                <?php foreach ($allAuthors as $authorItem): ?>
                                    <option value="<?= $authorItem['id']; ?>" <?= (!empty($authorId) && (int)$authorId === (int)$authorItem['id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($authorItem['full_name']); ?> (@<?= htmlspecialchars($authorItem['username']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Submit / Clear Buttons -->
                <div class="col-12 col-md-6 text-md-end d-flex gap-2 justify-content-md-end">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="bi bi-search me-1"></i> Apply Filter
                    </button>
                    <?php if (!empty($query) || !empty($authorId)): ?>
                        <a href="<?= BASE_URL ?>/index.php?action=search" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Filter Pills & Sorting Options -->
        <div class="search-filter-group">
            <!-- Result Type Filter Pills -->
            <div class="filter-pills-wrap">
                <a href="<?= BASE_URL ?>/index.php?action=search&q=<?= urlencode($query); ?>&type=all&filter=<?= urlencode($filter); ?><?= !empty($authorId) ? '&author_id=' . (int)$authorId : ''; ?>" 
                   class="filter-pill <?= ($type === 'all') ? 'active' : ''; ?>">
                    All Results
                </a>
                <a href="<?= BASE_URL ?>/index.php?action=search&q=<?= urlencode($query); ?>&type=users&filter=<?= urlencode($filter); ?><?= !empty($authorId) ? '&author_id=' . (int)$authorId : ''; ?>" 
                   class="filter-pill <?= ($type === 'users') ? 'active' : ''; ?>">
                    Users
                </a>
                <a href="<?= BASE_URL ?>/index.php?action=search&q=<?= urlencode($query); ?>&type=posts&filter=<?= urlencode($filter); ?><?= !empty($authorId) ? '&author_id=' . (int)$authorId : ''; ?>" 
                   class="filter-pill <?= ($type === 'posts') ? 'active' : ''; ?>">
                    Posts
                </a>
            </div>

            <!-- Sorting Order Pills (Step 11 & Step 12 B) -->
            <?php if ($type !== 'users'): ?>
                <div class="d-flex align-items-center flex-wrap justify-content-center gap-2 mt-2">
                    <span class="text-muted small fw-semibold me-1"><i class="bi bi-sort-down text-primary me-1"></i>Sort Posts By:</span>
                    
                    <a href="<?= BASE_URL ?>/index.php?action=search&q=<?= urlencode($query); ?>&type=<?= urlencode($type); ?>&filter=newest<?= !empty($authorId) ? '&author_id=' . (int)$authorId : ''; ?>" 
                       class="btn btn-sm rounded-pill px-3 <?= ($filter === 'newest') ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                        <i class="bi bi-clock-history me-1"></i> Newest
                    </a>
                    
                    <a href="<?= BASE_URL ?>/index.php?action=search&q=<?= urlencode($query); ?>&type=<?= urlencode($type); ?>&filter=oldest<?= !empty($authorId) ? '&author_id=' . (int)$authorId : ''; ?>" 
                       class="btn btn-sm rounded-pill px-3 <?= ($filter === 'oldest') ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                        <i class="bi bi-arrow-up-circle me-1"></i> Oldest
                    </a>

                    <a href="<?= BASE_URL ?>/index.php?action=search&q=<?= urlencode($query); ?>&type=<?= urlencode($type); ?>&filter=most_liked<?= !empty($authorId) ? '&author_id=' . (int)$authorId : ''; ?>" 
                       class="btn btn-sm rounded-pill px-3 <?= ($filter === 'most_liked') ? 'btn-danger text-white' : 'btn-outline-danger'; ?>">
                        <i class="bi bi-heart-fill me-1"></i> Most Liked
                    </a>

                    <a href="<?= BASE_URL ?>/index.php?action=search&q=<?= urlencode($query); ?>&type=<?= urlencode($type); ?>&filter=most_commented<?= !empty($authorId) ? '&author_id=' . (int)$authorId : ''; ?>" 
                       class="btn btn-sm rounded-pill px-3 <?= ($filter === 'most_commented') ? 'btn-warning text-dark' : 'btn-outline-warning text-dark'; ?>">
                        <i class="bi bi-chat-dots-fill me-1"></i> Most Commented
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <!-- 4. SEARCH & REPORT RESULTS CONTENT -->
    <?php if ($isEmptySearch && empty($users) && empty($posts)): ?>
        <!-- Initial Guidance State -->
        <div class="search-empty-state">
            <div class="search-empty-icon">
                <i class="bi bi-search"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Search & Filter Social Content</h5>
            <p class="small text-muted mb-3 col-md-9 mx-auto">
                Type a username, full name, or post content keyword above, or filter posts by selecting a specific author from the dropdown list.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="<?= BASE_URL ?>/index.php?action=search&type=posts&filter=newest" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-grid me-1"></i> Browse All Posts
                </a>
                <a href="<?= BASE_URL ?>/index.php?action=search&type=posts&filter=most_liked" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="bi bi-heart me-1"></i> View Most Liked Posts
                </a>
            </div>
        </div>

    <?php elseif (($type === 'all' && empty($users) && empty($posts)) || ($type === 'users' && empty($users)) || ($type === 'posts' && empty($posts))): ?>
        <!-- No Results Found State -->
        <div class="search-empty-state">
            <div class="search-empty-icon">
                <i class="bi bi-slash-circle"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No matching results found</h5>
            <p class="small text-muted mb-0 col-md-9 mx-auto">
                No matching results were found for your search query or filter criteria.
                <br>Try adjusting keywords, selecting another author, or clearing filters.
            </p>
            <div class="mt-3">
                <a href="<?= BASE_URL ?>/index.php?action=search" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                    Clear Search Filters
                </a>
            </div>
        </div>

    <?php else: ?>

        <!-- USERS SECTION (Step 11: Search Users & Profile Links) -->
        <?php if (($type === 'all' || $type === 'users') && !empty($users)): ?>
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-people-fill text-primary me-2"></i>Matching Users (<?= count($users); ?>)
                    </h6>
                </div>

                <div class="d-flex flex-column gap-3">
                    <?php foreach ($users as $user): ?>
                        <?php
                        $uParts = explode(' ', trim($user['full_name']));
                        $uInitials = count($uParts) >= 2
                            ? strtoupper(mb_substr($uParts[0], 0, 1) . mb_substr($uParts[count($uParts)-1], 0, 1))
                            : strtoupper(mb_substr($user['full_name'], 0, 2));
                        ?>
                        <div class="search-user-card">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $user['id']; ?>" class="text-decoration-none">
                                        <?php if (!empty($user['profile_image'])): ?>
                                            <img 
                                                src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars(basename($user['profile_image'])) ?>" 
                                                alt="<?= htmlspecialchars($user['full_name']); ?>" 
                                                class="post-avatar"
                                                style="width: 44px; height: 44px; object-fit: cover;"
                                                onerror="this.onerror=null; this.outerHTML='<div class=\'post-avatar\' style=\'width: 44px; height: 44px; font-size: 1rem;\'><?= htmlspecialchars($uInitials) ?></div>';"
                                            >
                                        <?php else: ?>
                                            <div class="post-avatar" style="width: 44px; height: 44px; font-size: 1rem;">
                                                <?= htmlspecialchars($uInitials) ?>
                                            </div>
                                        <?php endif; ?>
                                    </a>

                                    <div class="min-w-0">
                                        <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $user['id']; ?>" class="fw-bold text-dark text-decoration-none d-block text-truncate">
                                            <?= htmlspecialchars($user['full_name']); ?>
                                        </a>
                                        <div class="text-muted small text-truncate">
                                            @<?= htmlspecialchars($user['username']); ?> &bull; 
                                            <span class="badge bg-light text-secondary border"><?= (int)($user['total_user_posts'] ?? 0); ?> posts</span>
                                        </div>
                                        <?php if (!empty($user['bio'])): ?>
                                            <div class="text-muted small text-truncate mt-1" style="max-width: 380px;">
                                                <?= htmlspecialchars($user['bio']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <a href="<?= BASE_URL ?>/index.php?action=search&author_id=<?= $user['id']; ?>&type=posts" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Filter posts by this author">
                                        <i class="bi bi-funnel me-1"></i> Posts
                                    </a>
                                    <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $user['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-person me-1"></i> Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- POSTS SECTION (Step 11 & Step 12 B: Detailed Reports & Sorting) -->
        <?php if (($type === 'all' || $type === 'posts') && !empty($posts)): ?>
            <div>
                <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-chat-square-text-fill text-warning me-2"></i>Matching Posts (<?= count($posts); ?>)
                    </h6>
                    <span class="badge bg-light text-dark border">
                        Sorted by: <?= htmlspecialchars(ucwords(str_replace('_', ' ', $filter))); ?>
                    </span>
                </div>

                <?php foreach ($posts as $post): ?>
                    <?php
                    $authorParts = explode(' ', trim($post['full_name']));
                    $authorInitials = count($authorParts) >= 2
                        ? strtoupper(mb_substr($authorParts[0], 0, 1) . mb_substr($authorParts[count($authorParts)-1], 0, 1))
                        : strtoupper(mb_substr($post['full_name'], 0, 2));

                    $postTime = date('M j, Y \a\t g:i a', strtotime($post['created_at']));
                    $diffHours = round((time() - strtotime($post['created_at'])) / 3600);
                    $timeBadge = ($diffHours < 24 && $diffHours >= 1) ? $diffHours . 'h ago' : ($diffHours < 1 ? 'Just now' : date('M j, Y', strtotime($post['created_at'])));
                    ?>
                    <article class="post-card mb-3" id="post-<?= $post['id'] ?>">
                        <!-- Post Author Header -->
                        <div class="post-header">
                            <div class="post-author-box">
                                <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $post['user_id']; ?>" class="text-decoration-none">
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
                                    <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $post['user_id']; ?>" class="post-author-name">
                                        <?= htmlspecialchars($post['full_name']); ?>
                                    </a>
                                    <div class="post-meta">
                                        @<?= htmlspecialchars($post['username']); ?> &bull; 
                                        <span title="<?= htmlspecialchars($postTime); ?>"><?= htmlspecialchars($timeBadge); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Post Menu & Author Filter Link -->
                            <div class="d-flex align-items-center gap-2">
                                <a href="<?= BASE_URL ?>/index.php?action=search&author_id=<?= $post['user_id']; ?>&type=posts" class="btn btn-sm btn-light text-secondary border-0 rounded-pill px-2 py-1 micro-text" title="View all posts by this author">
                                    <i class="bi bi-funnel me-1"></i>Author Posts
                                </a>

                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Post actions">
                                        <i class="bi bi-three-dots fs-5"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <?php if ($currentUserId && (int)$currentUserId === (int)$post['user_id']): ?>
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
                                            <li><hr class="dropdown-divider"></li>
                                        <?php endif; ?>
                                        <li>
                                            <a class="dropdown-item small" href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= $post['user_id']; ?>">
                                                <i class="bi bi-person me-2"></i>View author profile
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
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
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- STEP 12 B: DETAILED REPORT ENGAGEMENT METRICS BADGES -->
                        <div class="p-2 bg-light rounded-3 my-2 d-flex align-items-center justify-content-between border">
                            <span class="small text-muted fw-semibold">
                                <i class="bi bi-graph-up text-primary me-1"></i> Post Analytics:
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                    <i class="bi bi-heart-fill me-1"></i> <?= (int)$post['likes_count']; ?> Likes
                                </span>
                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle rounded-pill px-2.5 py-1">
                                    <i class="bi bi-chat-dots-fill me-1"></i> <?= (int)$post['comments_count']; ?> Comments
                                </span>
                            </div>
                        </div>

                        <!-- Post Engagement Actions (Like / Comment) -->
                        <div class="post-footer-actions d-flex align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <form action="<?= BASE_URL ?>/index.php?action=like_post" method="POST" class="d-inline">
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
                        <div class="comments-container d-none" id="comments-box-<?= $post['id'] ?>">
                            <?php if (!empty($post['comments'])): ?>
                                <?php foreach ($post['comments'] as $comment): ?>
                                    <?php
                                    $cParts = explode(' ', trim($comment['full_name'] ?? 'User'));
                                    $cInitials = count($cParts) >= 2
                                        ? strtoupper(mb_substr($cParts[0], 0, 1) . mb_substr($cParts[count($cParts)-1], 0, 1))
                                        : strtoupper(mb_substr($comment['full_name'] ?? 'U', 0, 2));

                                    $cTimeBadge = format_time_ago($comment['created_at']);
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
                                                    <small class="text-muted"><?= htmlspecialchars($cTimeBadge); ?></small>

                                                    <?php if ($currentUserId && (int)$currentUserId === (int)$comment['user_id']): ?>
                                                        <a href="<?= BASE_URL ?>/index.php?action=edit_comment&comment_id=<?= $comment['id'] ?>" class="comment-btn-action text-primary" title="Edit comment">
                                                            <i class="bi bi-pencil-square"></i> Edit
                                                        </a>
                                                        <form action="<?= BASE_URL ?>/index.php?action=delete_comment" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this comment? This action cannot be undone.');">
                                                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                            <button type="submit" class="comment-btn-action text-danger" title="Delete comment">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </button>
                                                        </form>
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
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<script>
function toggleComments(postId) {
    const box = document.getElementById('comments-box-' + postId);
    if (box) {
        box.classList.toggle('d-none');
    }
}
</script>
