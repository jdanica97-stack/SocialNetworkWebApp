<?php
// app/views/insights/index.php
// Recreated Search & Reports Page for SMCC Connect — Clean & Simplified UI

$currentUserId = $_SESSION['user_id'] ?? null;
?>

<div class="insights-container py-4">
    <div class="search-reports-container mx-auto">

        <!-- Flash Notifications / Error Alerts -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- BACK TO NEWSFEED BUTTON -->
        <div class="mb-3">
            <a href="<?= BASE_URL ?>/" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-medium shadow-sm d-inline-flex align-items-center gap-1.5">
                <i class="bi bi-arrow-left"></i> Back to Newsfeed
            </a>
        </div>

        <!-- MAIN SEARCH & REPORTS CARD -->
        <div class="search-reports-card mb-4">
            <!-- Card Heading -->
            <div class="mb-4">
                <h3 class="fw-bold insights-title mb-1" style="letter-spacing: -0.5px;">Search & Reports</h3>
                <p class="text-secondary mb-0 small">Find users, filter community posts, and inspect interaction statistics.</p>
            </div>

            <!-- Search Form -->
            <form action="<?= BASE_URL ?>/index.php" method="GET" id="insightsSearchForm">
                <input type="hidden" name="action" value="insights">
                <input type="hidden" name="report_sort" value="<?= htmlspecialchars($reportSort); ?>">

                <!-- Full-Width Search Input -->
                <div class="position-relative mb-3">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 search-accent-icon fs-5"></i>
                    <input 
                        type="text" 
                        name="q" 
                        class="form-control form-control-lg ps-5 pe-5 rounded-3" 
                        style="height: 48px; border-color: #dedee2; font-size: 0.95rem;"
                        placeholder="Search post content or user names/usernames..." 
                        value="<?= htmlspecialchars($query); ?>"
                        autocomplete="off"
                    >
                    <?php if (!empty($query) || !empty($authorId) || !empty($startDate) || !empty($endDate) || $contentType !== 'all' || !empty($keyword) || $type !== 'all'): ?>
                        <a href="<?= BASE_URL ?>/index.php?action=insights" class="position-absolute top-50 translate-middle-y end-0 me-3 text-muted text-decoration-none fs-5" title="Clear Search">&times;</a>
                    <?php endif; ?>
                </div>

                <!-- Search Filters (4 Controls in 1 Row on Desktop) -->
                <div class="row g-2 mb-3">
                    <!-- 1. Search In Scope -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label text-secondary micro-text fw-semibold mb-1">Search In</label>
                        <select name="type" class="form-select form-select-sm rounded-3" style="height: 44px; border-color: #dedee2;">
                            <option value="all" <?= ($type === 'all') ? 'selected' : ''; ?>>All (Users & Posts)</option>
                            <option value="users" <?= ($type === 'users') ? 'selected' : ''; ?>>Users Only</option>
                            <option value="posts" <?= ($type === 'posts') ? 'selected' : ''; ?>>Posts Only</option>
                        </select>
                    </div>

                    <!-- 2. Author Filter -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label text-secondary micro-text fw-semibold mb-1">Author</label>
                        <select name="author_id" class="form-select form-select-sm rounded-3" style="height: 44px; border-color: #dedee2;">
                            <option value="">All Authors</option>
                            <?php if (!empty($allAuthors)): ?>
                                <?php foreach ($allAuthors as $auth): ?>
                                    <option value="<?= $auth['id']; ?>" <?= ($authorId && (int)$authorId === (int)$auth['id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($auth['full_name']); ?> (@<?= htmlspecialchars($auth['username']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- 3. From Date -->
                    <div class="col-6 col-md-3">
                        <label class="form-label text-secondary micro-text fw-semibold mb-1">From Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm rounded-3" style="height: 44px; border-color: #dedee2;" value="<?= htmlspecialchars($startDate); ?>">
                    </div>

                    <!-- 4. To Date -->
                    <div class="col-6 col-md-3">
                        <label class="form-label text-secondary micro-text fw-semibold mb-1">To Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm rounded-3" style="height: 44px; border-color: #dedee2;" value="<?= htmlspecialchars($endDate); ?>">
                    </div>
                </div>

                <!-- Sorting & Keyword Row -->
                <div class="row g-2 align-items-center mb-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label text-secondary micro-text fw-semibold mb-1">Sort By</label>
                        <select name="sort" class="form-select form-select-sm rounded-3" style="height: 44px; border-color: #dedee2;">
                            <option value="newest" <?= ($sort === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?= ($sort === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="most_liked" <?= ($sort === 'most_liked') ? 'selected' : ''; ?>>Most Liked</option>
                            <option value="most_commented" <?= ($sort === 'most_commented') ? 'selected' : ''; ?>>Most Commented</option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6">
                        <label class="form-label text-secondary micro-text fw-semibold mb-1">Content Type</label>
                        <select name="content_type" class="form-select form-select-sm rounded-3" style="height: 44px; border-color: #dedee2;">
                            <option value="all" <?= ($contentType === 'all') ? 'selected' : ''; ?>>All Content Types</option>
                            <option value="text" <?= ($contentType === 'text') ? 'selected' : ''; ?>>Text Only</option>
                            <option value="image" <?= ($contentType === 'image') ? 'selected' : ''; ?>>With Images</option>
                        </select>
                    </div>
                </div>

                <!-- Action Row -->
                <div class="pt-3 border-top insights-border d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <button type="submit" name="generate_report" value="1" class="btn btn-report-generate rounded-pill px-3 py-2 fw-medium btn-sm d-inline-flex align-items-center gap-2">
                            <i class="bi bi-bar-chart-line search-accent-icon"></i>
                            <span>Generate Full Community Report</span>
                        </button>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-black rounded-pill px-4 py-2 fw-semibold btn-sm shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-search"></i>
                        <span>Search & Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- COMMUNITY EXPLORATION & RESULTS CARD -->
        <div class="search-reports-card mb-4" id="communityExplorationSection">
            <!-- Top Center Icon -->
            <div class="mx-auto rounded-3 d-flex align-items-center justify-content-center mb-2" style="width: 44px; height: 44px; background-color: #e0f2fe; color: #3478f6;">
                <i class="bi bi-search fs-5"></i>
            </div>

            <h5 class="fw-bold insights-title text-center mb-1">Explore Community<?= (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? ' & Generate Reports' : ''; ?></h5>
            <p class="text-secondary small text-center mb-4 col-md-10 mx-auto">
                Discover community members, search posts, and analyze user interactions<?= (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? ', and generate database reports' : ''; ?>.
            </p>

            <!-- RESULTS DISPLAY -->
            <?php if (($type === 'all' && empty($users) && empty($posts)) || ($type === 'users' && empty($users)) || ($type === 'posts' && empty($posts))): ?>
                <!-- Clean Empty State -->
                <div class="p-4 text-center rounded-3 mb-2 border bg-body">
                    <div class="text-muted mb-2 fs-3"><i class="bi bi-search"></i></div>
                    <h6 class="fw-bold insights-title mb-1">No matching results found</h6>
                    <p class="text-muted small mb-3">No community members or posts matched your search parameters. Try adjusting keywords or clearing filters.</p>
                    <a href="<?= BASE_URL ?>/index.php?action=insights" class="btn btn-outline-secondary btn-sm rounded-pill px-4">Clear Search & Filters</a>
                </div>
            <?php else: ?>

                <!-- USER MATCHES -->
                <?php if (($type === 'all' || $type === 'users') && !empty($users)): ?>
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom insights-border">
                            <span class="fw-bold micro-text text-uppercase text-secondary">
                                <i class="bi bi-people me-1"></i> Matching Members (<?= count($users); ?>)
                            </span>
                        </div>

                        <div class="row g-2">
                            <?php foreach ($users as $u): ?>
                                <?php
                                $uParts = explode(' ', trim($u['full_name']));
                                $uInitials = count($uParts) >= 2
                                    ? strtoupper(mb_substr($uParts[0], 0, 1) . mb_substr($uParts[count($uParts)-1], 0, 1))
                                    : strtoupper(mb_substr($u['full_name'], 0, 2));
                                ?>
                                <div class="col-12 col-md-6">
                                    <div class="p-3 rounded-3 border bg-body h-100 d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2.5 min-w-0">
                                            <a href="<?= BASE_URL ?>/index.php?action=profile&id=<?= $u['id']; ?>" class="text-decoration-none flex-shrink-0">
                                                <?php if (!empty($u['profile_image'])): ?>
                                                    <img 
                                                        src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars(basename($u['profile_image'])) ?>" 
                                                        alt="<?= htmlspecialchars($u['full_name']); ?>" 
                                                        class="rounded-circle border"
                                                        style="width: 38px; height: 38px; object-fit: cover;"
                                                        onerror="this.onerror=null; this.outerHTML='<div class=\'user-avatar-sm\' style=\'width: 38px; height: 38px; font-size: 0.9rem;\'><?= htmlspecialchars($uInitials) ?></div>';"
                                                    >
                                                <?php else: ?>
                                                    <div class="user-avatar-sm" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                        <?= htmlspecialchars($uInitials) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </a>
                                            <div class="min-w-0">
                                                <a href="<?= BASE_URL ?>/index.php?action=profile&id=<?= $u['id']; ?>" class="fw-bold insights-title text-decoration-none d-block text-truncate small">
                                                    <?= htmlspecialchars($u['full_name']); ?>
                                                </a>
                                                <span class="text-muted micro-text d-block text-truncate">@<?= htmlspecialchars($u['username']); ?></span>
                                            </div>
                                        </div>

                                        <a href="<?= BASE_URL ?>/index.php?action=profile&id=<?= $u['id']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 micro-text flex-shrink-0">
                                            Profile
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- POST MATCHES -->
                <?php if (($type === 'all' || $type === 'posts') && !empty($posts)): ?>
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom insights-border">
                            <span class="fw-bold micro-text text-uppercase text-secondary">
                                <i class="bi bi-chat-square-text me-1"></i> Matching Posts (<?= count($posts); ?>)
                            </span>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($posts as $post): ?>
                                <?php
                                $authorParts = explode(' ', trim($post['full_name']));
                                $authorInitials = count($authorParts) >= 2
                                    ? strtoupper(mb_substr($authorParts[0], 0, 1) . mb_substr($authorParts[count($authorParts)-1], 0, 1))
                                    : strtoupper(mb_substr($post['full_name'], 0, 2));

                                $postTime = date('M j, Y \a\t g:i a', strtotime($post['created_at']));
                                ?>
                                <article class="p-3.5 rounded-3 border bg-body" id="post-<?= $post['id'] ?>">
                                    <!-- Author Header -->
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <a href="<?= BASE_URL ?>/index.php?action=profile&id=<?= $post['user_id']; ?>" class="text-decoration-none">
                                                <?php if (!empty($post['profile_image'])): ?>
                                                    <img 
                                                        src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars(basename($post['profile_image'])) ?>" 
                                                        alt="<?= htmlspecialchars($post['full_name']); ?>" 
                                                        class="rounded-circle border"
                                                        style="width: 36px; height: 36px; object-fit: cover;"
                                                        onerror="this.onerror=null; this.outerHTML='<div class=\'user-avatar-sm\' style=\'width: 36px; height: 36px; font-size: 0.85rem;\'><?= htmlspecialchars($authorInitials) ?></div>';"
                                                    >
                                                <?php else: ?>
                                                    <div class="user-avatar-sm" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                                        <?= htmlspecialchars($authorInitials) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </a>
                                            <div>
                                                <a href="<?= BASE_URL ?>/index.php?action=profile&id=<?= $post['user_id']; ?>" class="fw-bold insights-title text-decoration-none small">
                                                    <?= htmlspecialchars($post['full_name']); ?>
                                                </a>
                                                <div class="text-muted micro-text">
                                                    @<?= htmlspecialchars($post['username']); ?> &bull; <?= htmlspecialchars($postTime); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <a href="<?= BASE_URL ?>/index.php?action=insights&author_id=<?= $post['user_id']; ?>&type=posts" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5 micro-text" title="Filter by author">
                                            <i class="bi bi-funnel me-1"></i> Filter Author
                                        </a>
                                    </div>

                                    <!-- Content Body -->
                                    <div class="insights-title mb-2.5 small" style="line-height: 1.5;">
                                        <?= nl2br(htmlspecialchars($post['content'])); ?>
                                    </div>

                                    <!-- Image Attachment -->
                                    <?php if (!empty($post['image'])): ?>
                                        <?php
                                        $postImgFile = __DIR__ . '/../../../public/uploads/posts/' . basename($post['image']);
                                        $hasRealImage = file_exists($postImgFile);
                                        ?>
                                        <div class="rounded-3 overflow-hidden mb-2.5 border bg-body" style="max-height: 380px;">
                                            <?php if ($hasRealImage): ?>
                                                <img src="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($post['image'])) ?>" alt="Post attachment" class="w-100 h-100 object-fit-contain d-block rounded-3" style="max-height: 380px;">
                                            <?php else: ?>
                                                <div class="p-3 text-center text-muted bg-light rounded-3">
                                                    <i class="bi bi-image fs-3 d-block mb-1"></i>
                                                    <span class="micro-text">Attachment: <?= htmlspecialchars(basename($post['image'])); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Engagement Actions -->
                                    <div class="d-flex align-items-center justify-content-between pt-2 border-top insights-border">
                                        <div class="d-flex align-items-center gap-2">
                                            <form action="<?= BASE_URL ?>/index.php?action=like_post" method="POST" class="d-inline">
                                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                <button type="submit" class="btn btn-sm <?= !empty($post['is_liked']) ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-2.5 py-0.5 micro-text fw-medium">
                                                    <i class="bi <?= !empty($post['is_liked']) ? 'bi-heart-fill' : 'bi-heart' ?> me-1"></i> <?= (int)$post['likes_count']; ?>
                                                </button>
                                            </form>

                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 micro-text fw-medium" onclick="toggleComments(<?= $post['id'] ?>)">
                                                <i class="bi bi-chat-dots me-1"></i> <?= (int)$post['comments_count']; ?> Comments
                                            </button>
                                        </div>

                                        <?php if ($currentUserId && (int)$currentUserId === (int)$post['user_id']): ?>
                                            <span class="badge bg-light text-secondary border micro-text insights-badge-subtle">Your Post</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Comments Box -->
                                    <div class="comments-container d-none mt-2 pt-2 border-top insights-border" id="comments-box-<?= $post['id'] ?>">
                                        <?php if (!empty($post['comments'])): ?>
                                            <?php foreach ($post['comments'] as $comment): ?>
                                                <div class="p-2 rounded-2 mb-1.5 small border bg-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <strong class="insights-title micro-text">@<?= htmlspecialchars($comment['username']); ?></strong>
                                                        <span class="text-muted micro-text"><?= date('M j, g:i a', strtotime($comment['created_at'])); ?></span>
                                                    </div>
                                                    <div class="micro-text"><?= nl2br(htmlspecialchars($comment['content'])); ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

            <!-- COMMUNITY REPORTS DISPLAY (WHEN GENERATED FOR ADMIN) -->
            <?php if (!empty($reportGenerated) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="pt-3 border-top insights-border mt-3" id="communityReportsDetails">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold insights-title mb-0">
                            <i class="bi bi-bar-chart-line me-1 text-primary"></i> Generated Community Reports
                        </h6>
                    </div>

                    <!-- SUMMARY CARDS -->
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="p-2.5 rounded-3 border bg-body text-center">
                                <span class="text-muted micro-text fw-bold text-uppercase d-block mb-0.5">Total Users</span>
                                <h4 class="fw-bold insights-title mb-0"><?= (int)($summaryMetrics['total_users'] ?? 0); ?></h4>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="p-2.5 rounded-3 border bg-body text-center">
                                <span class="text-muted micro-text fw-bold text-uppercase d-block mb-0.5">Total Posts</span>
                                <h4 class="fw-bold insights-title mb-0"><?= (int)($summaryMetrics['total_posts'] ?? 0); ?></h4>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="p-2.5 rounded-3 border bg-body text-center">
                                <span class="text-muted micro-text fw-bold text-uppercase d-block mb-0.5">Total Comments</span>
                                <h4 class="fw-bold insights-title mb-0"><?= (int)($summaryMetrics['total_comments'] ?? 0); ?></h4>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="p-2.5 rounded-3 border bg-body text-center">
                                <span class="text-muted micro-text fw-bold text-uppercase d-block mb-0.5">Total Likes</span>
                                <h4 class="fw-bold insights-title mb-0"><?= (int)($summaryMetrics['total_likes'] ?? 0); ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- DETAILED AUTHOR REPORT TABLE -->
                    <div class="p-3 rounded-3 border bg-body mb-2">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-2 pb-2 border-bottom insights-border">
                            <span class="fw-bold insights-title micro-text text-uppercase">
                                Author Activity Report
                            </span>
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="text-muted micro-text">Sort:</span>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/index.php?action=insights&generate_report=1&report_sort=posts_desc#communityReportsDetails" class="btn <?= ($reportSort === 'posts_desc') ? 'btn-dark' : 'btn-outline-secondary' ?> py-0.5 px-2 micro-text">Posts ↓</a>
                                    <a href="<?= BASE_URL ?>/index.php?action=insights&generate_report=1&report_sort=likes_desc#communityReportsDetails" class="btn <?= ($reportSort === 'likes_desc') ? 'btn-dark' : 'btn-outline-secondary' ?> py-0.5 px-2 micro-text">Likes ↓</a>
                                    <a href="<?= BASE_URL ?>/index.php?action=insights&generate_report=1&report_sort=comments_desc#communityReportsDetails" class="btn <?= ($reportSort === 'comments_desc') ? 'btn-dark' : 'btn-outline-secondary' ?> py-0.5 px-2 micro-text">Comments ↓</a>
                                    <a href="<?= BASE_URL ?>/index.php?action=insights&generate_report=1&report_sort=username_asc#communityReportsDetails" class="btn <?= ($reportSort === 'username_asc') ? 'btn-dark' : 'btn-outline-secondary' ?> py-0.5 px-2 micro-text">Name A-Z</a>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($detailedReportRows)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 insights-table">
                                    <thead class="table-light micro-text text-uppercase insights-table-head">
                                        <tr>
                                            <th>#</th>
                                            <th>Author</th>
                                            <th>Username</th>
                                            <th class="text-center">Posts</th>
                                            <th class="text-center">Likes</th>
                                            <th class="text-center">Comments</th>
                                            <th class="text-end">Latest Post</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detailedReportRows as $idx => $r): ?>
                                            <tr>
                                                <td class="text-muted micro-text"><?= $idx + 1; ?></td>
                                                <td>
                                                    <a href="<?= BASE_URL ?>/index.php?action=profile&id=<?= $r['user_id']; ?>" class="fw-bold insights-title text-decoration-none small">
                                                        <?= htmlspecialchars($r['full_name']); ?>
                                                    </a>
                                                </td>
                                                <td class="text-muted small">@<?= htmlspecialchars($r['username']); ?></td>
                                                <td class="text-center"><span class="badge bg-dark rounded-pill px-2"><?= (int)$r['post_count']; ?></span></td>
                                                <td class="text-center"><span class="badge bg-light text-secondary border rounded-pill px-2 insights-badge-subtle"><?= (int)$r['total_likes']; ?></span></td>
                                                <td class="text-center"><span class="badge bg-light text-secondary border rounded-pill px-2 insights-badge-subtle"><?= (int)$r['total_comments']; ?></span></td>
                                                <td class="text-end micro-text text-muted">
                                                    <?= !empty($r['latest_post_date']) ? date('M j, Y', strtotime($r['latest_post_date'])) : 'No activity'; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3 text-muted small">No detailed report data found for current filters.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
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
</script>
