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

    <!-- Search Header -->
    <div class="search-header-clean mb-4 text-center">
        <h3 class="fw-bold text-dark mb-1">Search</h3>
        <p class="text-muted small mb-0">Find students, faculty, posts, and topics across SMCC Connect</p>
    </div>

    <!-- Main Search Bar -->
    <form action="<?= BASE_URL ?>/index.php" method="GET" class="mb-4" role="search" id="searchMainForm">
        <input type="hidden" name="action" value="search">
        <input type="hidden" name="type" value="<?= htmlspecialchars($type); ?>" id="searchTypeInput">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter); ?>" id="searchFilterInput">

        <div class="search-input-wrapper mb-3">
            <i class="bi bi-search search-icon-lead"></i>
            <input 
                type="text" 
                name="q" 
                class="search-main-control" 
                placeholder="Search by name, username, or keywords..." 
                value="<?= htmlspecialchars($query ?? ''); ?>" 
                autocomplete="off"
            >
            <?php if (!empty($query) || !empty($authorId)): ?>
                <a href="<?= BASE_URL ?>/index.php?action=search" class="search-clear-btn" title="Clear Search">
                    <i class="bi bi-x"></i>
                </a>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary search-submit-btn">
                Search
            </button>
        </div>

        <!-- Filter & Tab Navigation Bar -->
        <div class="search-nav-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 rounded-3">
            <!-- Type Tabs -->
            <ul class="nav nav-pills search-tabs-pills" role="tablist">
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/index.php?action=search&q=<?= urlencode($query); ?>&type=all&filter=<?= urlencode($filter); ?><?= !empty($authorId) ? '&author_id=' . (int)$authorId : ''; ?>" 
                       class="nav-link <?= ($type === 'all') ? 'active' : ''; ?>">
                        All Results
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/index.php?action=search&q=<?= urlencode($query); ?>&type=users&filter=<?= urlencode($filter); ?><?= !empty($authorId) ? '&author_id=' . (int)$authorId : ''; ?>" 
                       class="nav-link <?= ($type === 'users') ? 'active' : ''; ?>">
                        <i class="bi bi-people me-1"></i> People <?= !empty($users) ? '(' . count($users) . ')' : ''; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/index.php?action=search&q=<?= urlencode($query); ?>&type=posts&filter=<?= urlencode($filter); ?><?= !empty($authorId) ? '&author_id=' . (int)$authorId : ''; ?>" 
                       class="nav-link <?= ($type === 'posts') ? 'active' : ''; ?>">
                        <i class="bi bi-file-earmark-text me-1"></i> Posts <?= !empty($posts) ? '(' . count($posts) . ')' : ''; ?>
                    </a>
                </li>
            </ul>

            <!-- Refine Dropdowns: Author & Sort -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Author Filter -->
                <div class="dropdown">
                    <select name="author_id" id="author_id_select" class="form-select form-select-sm search-select-pill" onchange="this.form.submit()" aria-label="Filter by author">
                        <option value="">All Authors</option>
                        <?php if (!empty($allAuthors)): ?>
                            <?php foreach ($allAuthors as $authorItem): ?>
                                <option value="<?= $authorItem['id']; ?>" <?= (!empty($authorId) && (int)$authorId === (int)$authorItem['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($authorItem['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Sort Order (When posts are shown) -->
                <?php if ($type !== 'users'): ?>
                    <select name="filter" id="filter_select" class="form-select form-select-sm search-select-pill" onchange="this.form.submit()" aria-label="Sort results">
                        <option value="newest" <?= ($filter === 'newest') ? 'selected' : ''; ?>>Newest</option>
                        <option value="oldest" <?= ($filter === 'oldest') ? 'selected' : ''; ?>>Oldest</option>
                        <option value="most_liked" <?= ($filter === 'most_liked') ? 'selected' : ''; ?>>Most Liked</option>
                        <option value="most_commented" <?= ($filter === 'most_commented') ? 'selected' : ''; ?>>Most Commented</option>
                    </select>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- Results Overview Ribbon (When searching or filtered) -->
    <?php if (!$isEmptySearch || !empty($users) || !empty($posts)): ?>
        <div class="d-flex align-items-center justify-content-between px-1 mb-3">
            <span class="text-muted small">
                Showing results for <strong class="text-dark">"<?= htmlspecialchars($query ?: ($selectedAuthorName ? 'Author: ' . $selectedAuthorName : 'All')); ?>"</strong>
                <?php if (!empty($authorId) && !empty($selectedAuthorName)): ?>
                    <span class="badge bg-light text-secondary border ms-1">By <?= htmlspecialchars($selectedAuthorName); ?></span>
                <?php endif; ?>
            </span>
            <span class="text-muted small">
                <?= count($users) + count($posts); ?> matches
            </span>
        </div>
    <?php endif; ?>

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
                                        <img src="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($singleImg)) ?>" alt="Post attachment" loading="lazy">
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($totalPostImages > 1): ?>
                            <div class="post-gallery post-gallery-<?= min($totalPostImages, 4) ?> mb-3" data-post-id="<?= (int)$post['id'] ?>">
                                <?php foreach (array_slice($postImages, 0, 4) as $idx => $imgFile): ?>
                                    <?php
                                    $isLastQuadrant = ($idx === 3 && $totalPostImages > 4);
                                    $remainingCount = $totalPostImages - 4;
                                    ?>
                                    <div class="post-gallery-item">
                                        <a href="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($imgFile)) ?>" class="post-lightbox-trigger" data-gallery="post-<?= (int)$post['id'] ?>" title="View image <?= $idx + 1 ?>">
                                            <img src="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($imgFile)) ?>" alt="Post image <?= $idx + 1 ?>" loading="lazy">
                                            <?php if ($isLastQuadrant): ?>
                                                <div class="post-gallery-overlay">
                                                    <span>+<?= $remainingCount ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($totalPostImages > 4): ?>
                                    <?php foreach (array_slice($postImages, 4) as $extraIdx => $extraImg): ?>
                                        <a href="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($extraImg)) ?>" class="post-lightbox-trigger d-none" data-gallery="post-<?= (int)$post['id'] ?>" title="View image <?= 5 + $extraIdx ?>"></a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
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
    if (!box) return;
    if (box.classList.contains('d-none')) {
        box.classList.remove('d-none');
        const input = box.querySelector('input[name="content"], textarea[name="content"]');
        if (input) setTimeout(() => input.focus(), 60);
    } else {
        box.classList.add('d-none');
    }
}
</script>
