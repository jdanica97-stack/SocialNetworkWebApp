<?php
// app/views/reports/index.php
// View for Reports and Analytics Dashboard matching SMCC Connect UI theme

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- HEADER HERO CARD -->
<div class="card-modern p-4 mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #192f5d 0%, #1e3a8a 100%); color: #ffffff;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill">
                    <i class="bi bi-cpu-fill me-1"></i> SQL Analytics Module
                </span>
                <span class="badge hero-pill-badge rounded-pill px-3 py-1 fw-medium" style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.25);">
                    Step 12 Requirements
                </span>
            </div>
            <h2 class="fw-bold mb-1 text-white">System Reports & Data Analytics</h2>
            <p class="mb-0 text-white-50 small">
                Real-time relational database analytics, user activity stats, post metrics, and SQL aggregate reports.
            </p>
        </div>
        <div class="text-md-end">
            <span class="d-block text-white-50 small">Database: <strong>smcc_connect_db</strong></span>
            <span class="badge bg-success bg-opacity-75 text-white mt-1">
                <i class="bi bi-shield-check me-1"></i> Read-Only PDO Queries
            </span>
        </div>
    </div>
</div>

<!-- KPI SUMMARY STATS CARDS (REQUIREMENT A: SYSTEM SUMMARY) -->
<div class="row g-3 mb-4">
    <!-- Total Registered Users -->
    <div class="col-6 col-md-3">
        <div class="card-modern p-3 h-100 border-0 shadow-sm" style="border-left: 4px solid #3b82f6 !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Total Users</span>
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1"><?= (int)($summaryData['total_users'] ?? 0); ?></h3>
            <span class="text-muted micro-text">
                <i class="bi bi-person-check text-success me-1"></i> Registered accounts
            </span>
        </div>
    </div>

    <!-- Total Posts -->
    <div class="col-6 col-md-3">
        <div class="card-modern p-3 h-100 border-0 shadow-sm" style="border-left: 4px solid #10b981 !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Total Posts</span>
                <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                    <i class="bi bi-file-earmark-post-fill fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1"><?= (int)($summaryData['total_posts'] ?? 0); ?></h3>
            <span class="text-muted micro-text">
                <i class="bi bi-calculator text-primary me-1"></i> Avg <?= htmlspecialchars($summaryData['avg_posts_per_user']); ?> posts/user
            </span>
        </div>
    </div>

    <!-- Total Comments -->
    <div class="col-6 col-md-3">
        <div class="card-modern p-3 h-100 border-0 shadow-sm" style="border-left: 4px solid #f59e0b !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Total Comments</span>
                <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                    <i class="bi bi-chat-left-text-fill fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1"><?= (int)($summaryData['total_comments'] ?? 0); ?></h3>
            <span class="text-muted micro-text">
                <i class="bi bi-chat-dots text-warning me-1"></i> Avg <?= htmlspecialchars($summaryData['avg_comments_per_post']); ?> comments/post
            </span>
        </div>
    </div>

    <!-- Total Likes -->
    <div class="col-6 col-md-3">
        <div class="card-modern p-3 h-100 border-0 shadow-sm" style="border-left: 4px solid #ef4444 !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Total Likes</span>
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                    <i class="bi bi-heart-fill fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1"><?= (int)($summaryData['total_likes'] ?? 0); ?></h3>
            <span class="text-muted micro-text">
                <i class="bi bi-hand-thumbs-up text-danger me-1"></i> User engagements
            </span>
        </div>
    </div>
</div>

<!-- REPORT FILTERING TOOLBAR (REQUIREMENT 5: REPORT FILTERING) -->
<div class="card-modern p-3 mb-4 shadow-sm">
    <form action="<?= BASE_URL ?>/index.php" method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="action" value="reports">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($activeTab); ?>">

        <!-- Search Username or Keyword -->
        <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label text-muted small fw-semibold mb-1">
                <i class="bi bi-search me-1"></i> Filter by Username / Keyword
            </label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search user or post content..." value="<?= htmlspecialchars($search); ?>">
        </div>

        <!-- Date Range: Start Date -->
        <div class="col-6 col-sm-3 col-md-2">
            <label class="form-label text-muted small fw-semibold mb-1">
                <i class="bi bi-calendar-event me-1"></i> From Date
            </label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($startDate); ?>">
        </div>

        <!-- Date Range: End Date -->
        <div class="col-6 col-sm-3 col-md-2">
            <label class="form-label text-muted small fw-semibold mb-1">
                <i class="bi bi-calendar-event-fill me-1"></i> To Date
            </label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($endDate); ?>">
        </div>

        <!-- Sorting -->
        <div class="col-12 col-sm-6 col-md-2">
            <label class="form-label text-muted small fw-semibold mb-1">
                <i class="bi bi-sort-down me-1"></i> Sort Order
            </label>
            <select name="sort" class="form-select form-select-sm">
                <option value="posts_desc" <?= ($sort === 'posts_desc') ? 'selected' : ''; ?>>Posts (Highest first)</option>
                <option value="posts_asc" <?= ($sort === 'posts_asc') ? 'selected' : ''; ?>>Posts (Lowest first)</option>
                <option value="username_asc" <?= ($sort === 'username_asc') ? 'selected' : ''; ?>>Username (A-Z)</option>
            </select>
        </div>

        <!-- Submit & Reset Buttons -->
        <div class="col-12 col-sm-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 rounded-pill">
                <i class="bi bi-funnel-fill me-1"></i> Filter
            </button>
            <a href="<?= BASE_URL ?>/index.php?action=reports&tab=<?= htmlspecialchars($activeTab); ?>" class="btn btn-outline-secondary btn-sm rounded-pill" title="Reset Filters">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- NAVIGATION TABS FOR REPORTS -->
<ul class="nav nav-tabs nav-fill mb-4 border-bottom-0 gap-1">
    <li class="nav-item">
        <a class="nav-link rounded-3 fw-semibold <?= ($activeTab === 'summary') ? 'active bg-white shadow-sm border' : 'bg-light text-secondary'; ?>" href="<?= BASE_URL ?>/index.php?action=reports&tab=summary&search=<?= urlencode($search) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&sort=<?= urlencode($sort) ?>">
            <i class="bi bi-grid-1x2-fill me-1 text-primary"></i> Summary & Top Users
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-3 fw-semibold <?= ($activeTab === 'user_posts') ? 'active bg-white shadow-sm border' : 'bg-light text-secondary'; ?>" href="<?= BASE_URL ?>/index.php?action=reports&tab=user_posts&search=<?= urlencode($search) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&sort=<?= urlencode($sort) ?>">
            <i class="bi bi-people-fill me-1 text-success"></i> Posts per User
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-3 fw-semibold <?= ($activeTab === 'post_likes') ? 'active bg-white shadow-sm border' : 'bg-light text-secondary'; ?>" href="<?= BASE_URL ?>/index.php?action=reports&tab=post_likes&search=<?= urlencode($search) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&sort=<?= urlencode($sort) ?>">
            <i class="bi bi-file-post me-1 text-warning"></i> Recent & Liked Posts
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-3 fw-semibold <?= ($activeTab === 'comments') ? 'active bg-white shadow-sm border' : 'bg-light text-secondary'; ?>" href="<?= BASE_URL ?>/index.php?action=reports&tab=comments&search=<?= urlencode($search) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&sort=<?= urlencode($sort) ?>">
            <i class="bi bi-chat-square-text-fill me-1 text-danger"></i> Comments Report
        </a>
    </li>
</ul>

<!-- TAB CONTENT SECTIONS -->

<?php if ($activeTab === 'summary'): ?>
    <!-- TAB 1: SYSTEM OVERVIEW & MOST ACTIVE USERS (REQUIREMENT C) -->
    <div class="row g-4 mb-4">
        <!-- Most Active Users Card -->
        <div class="col-12 col-lg-7">
            <div class="card-modern p-4 h-100 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-trophy-fill text-warning me-2"></i> Most Active Users (Ranked by Posts)
                    </h5>
                    <span class="badge bg-light text-dark border">ORDER BY total_posts DESC</span>
                </div>

                <?php if (!empty($mostActiveUsers)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($mostActiveUsers as $index => $user): ?>
                            <?php
                            $rankBadge = match ($index) {
                                0 => '<span class="badge bg-warning text-dark me-2">#1</span>',
                                1 => '<span class="badge bg-secondary me-2">#2</span>',
                                2 => '<span class="badge bg-danger bg-opacity-75 me-2">#3</span>',
                                default => '<span class="badge bg-light text-dark border me-2">#' . ($index + 1) . '</span>'
                            };

                            $initials = strtoupper(mb_substr($user['full_name'] ?: $user['username'], 0, 2));
                            ?>
                            <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <?= $rankBadge; ?>
                                    <div class="user-avatar-sm flex-shrink-0" style="width:40px; height:40px; font-size:0.9rem;">
                                        <?= htmlspecialchars($initials); ?>
                                    </div>
                                    <div>
                                        <a href="<?= BASE_URL ?>/index.php?action=profile&id=<?= (int)$user['user_id'] ?>" class="fw-bold text-dark text-decoration-none">
                                            <?= htmlspecialchars($user['full_name']); ?>
                                        </a>
                                        <div class="text-muted small">@<?= htmlspecialchars($user['username']); ?></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary rounded-pill fs-6 px-3 py-1">
                                        <?= (int)$user['total_posts']; ?> <?= $user['total_posts'] == 1 ? 'post' : 'posts'; ?>
                                    </span>
                                    <?php if (!empty($user['last_post_at'])): ?>
                                        <div class="text-muted micro-text mt-1">
                                            Last: <?= date('M d, Y', strtotime($user['last_post_at'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                        No active users found.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Summary & Database Details -->
        <div class="col-12 col-lg-5">
            <div class="card-modern p-4 h-100 shadow-sm">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="bi bi-database-check text-primary me-2"></i> Database Metrics Breakdown
                </h5>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="bi bi-people me-2"></i> Registered Members</span>
                        <strong class="text-dark"><?= (int)($summaryData['total_users'] ?? 0); ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="bi bi-file-earmark-text me-2"></i> User Posts</span>
                        <strong class="text-dark"><?= (int)($summaryData['total_posts'] ?? 0); ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="bi bi-chat-left-dots me-2"></i> Total Comments</span>
                        <strong class="text-dark"><?= (int)($summaryData['total_comments'] ?? 0); ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="bi bi-heart me-2"></i> Total Likes</span>
                        <strong class="text-dark"><?= (int)($summaryData['total_likes'] ?? 0); ?></strong>
                    </li>
                </ul>

                <div class="bg-light p-3 rounded-3 border">
                    <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-info-circle me-1"></i> Aggregate Ratios</h6>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Posts per Registered User:</span>
                        <strong class="text-dark"><?= htmlspecialchars($summaryData['avg_posts_per_user']); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Comments per Post:</span>
                        <strong class="text-dark"><?= htmlspecialchars($summaryData['avg_comments_per_post']); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($activeTab === 'user_posts'): ?>
    <!-- TAB 2: POSTS PER USER REPORT (REQUIREMENT B) -->
    <div class="card-modern p-4 shadow-sm mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2 border-bottom pb-3">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-file-person-fill text-success me-2"></i> Posts Created per User
                </h5>
                <p class="text-muted small mb-0">
                    Includes all registered users, including users with zero posts (using SQL <code>LEFT JOIN</code> & <code>GROUP BY</code>).
                </p>
            </div>
            <span class="badge bg-light text-dark border align-self-start align-self-md-auto">
                Count: <?= count($postsPerUser); ?> Users
            </span>
        </div>

        <?php if (!empty($postsPerUser)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 50px;">#</th>
                            <th scope="col">User</th>
                            <th scope="col">Username</th>
                            <th scope="col">Joined Date</th>
                            <th scope="col" class="text-center">Post Count</th>
                            <th scope="col" class="text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($postsPerUser as $index => $row): ?>
                            <?php $initials = strtoupper(mb_substr($row['full_name'] ?: $row['username'], 0, 2)); ?>
                            <tr>
                                <th scope="row" class="text-muted"><?= $index + 1; ?></th>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-sm" style="width:32px; height:32px; font-size:0.75rem;">
                                            <?= htmlspecialchars($initials); ?>
                                        </div>
                                        <a href="<?= BASE_URL ?>/index.php?action=profile&id=<?= (int)$row['user_id'] ?>" class="fw-bold text-dark text-decoration-none">
                                            <?= htmlspecialchars($row['full_name']); ?>
                                        </a>
                                    </div>
                                </td>
                                <td><span class="text-muted">@<?= htmlspecialchars($row['username']); ?></span></td>
                                <td class="small text-muted"><?= date('M d, Y', strtotime($row['joined_at'])); ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $row['post_count'] > 0 ? 'bg-primary' : 'bg-secondary bg-opacity-50'; ?> px-3 py-1 rounded-pill">
                                        <?= (int)$row['post_count']; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php if ($row['post_count'] > 0): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active Contributor</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border rounded-pill">No Posts Yet</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-search fs-2 d-block mb-2 text-muted"></i>
                No matching users found for query "<strong><?= htmlspecialchars($search); ?></strong>".
            </div>
        <?php endif; ?>
    </div>

<?php elseif ($activeTab === 'post_likes'): ?>
    <!-- TAB 3: RECENT POSTS & MOST LIKED POSTS (REQUIREMENTS D & E) -->
    <div class="row g-4 mb-4">
        <!-- Requirement E: Most Liked Posts -->
        <div class="col-12 col-lg-5">
            <div class="card-modern p-4 h-100 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-heart-fill text-danger me-2"></i> Most Liked Posts
                    </h5>
                    <span class="badge bg-light text-dark border">HAVING & COUNT()</span>
                </div>

                <?php if (!empty($mostLikedPosts)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($mostLikedPosts as $idx => $post): ?>
                            <div class="list-group-item px-0 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="fw-bold text-dark small">@<?= htmlspecialchars($post['author_username']); ?></span>
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1">
                                        <i class="bi bi-heart-fill me-1"></i> <?= (int)$post['like_count']; ?>
                                    </span>
                                </div>
                                <p class="text-secondary small mb-1 text-truncate" style="max-width: 100%;">
                                    "<?= htmlspecialchars($post['content']); ?>"
                                </p>
                                <span class="text-muted micro-text"><?= date('M d, Y h:i A', strtotime($post['created_at'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-heartbreak fs-3 d-block mb-2"></i> No liked posts yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Requirement D: Recent Posts Report -->
        <div class="col-12 col-lg-7">
            <div class="card-modern p-4 h-100 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-clock-history text-primary me-2"></i> Recent Posts Log
                    </h5>
                    <span class="badge bg-light text-dark border">COUNT(DISTINCT)</span>
                </div>

                <?php if (!empty($recentPosts)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Author</th>
                                    <th scope="col">Content Preview</th>
                                    <th scope="col" class="text-center">Comments</th>
                                    <th scope="col" class="text-center">Likes</th>
                                    <th scope="col" class="text-end">Posted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPosts as $p): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark small">@<?= htmlspecialchars($p['author_username']); ?></span>
                                        </td>
                                        <td>
                                            <div class="text-dark small text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($p['content']); ?>">
                                                <?= htmlspecialchars($p['content']); ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark rounded-pill">
                                                <?= (int)$p['comment_count']; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger rounded-pill">
                                                <?= (int)$p['like_count']; ?>
                                            </span>
                                        </td>
                                        <td class="text-end micro-text text-muted">
                                            <?= date('M d, g:i a', strtotime($p['created_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i> No recent posts matching filters.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php elseif ($activeTab === 'comments'): ?>
    <!-- TAB 4: COMMENTS REPORT (REQUIREMENT F) -->
    <div class="row g-4 mb-4">
        <!-- User Comments Breakdown -->
        <div class="col-12 col-lg-5">
            <div class="card-modern p-4 h-100 shadow-sm">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="bi bi-person-fill-gear text-danger me-2"></i> Total Comments per User
                </h5>

                <?php if (!empty($commentsReport['user_comments'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th class="text-end">Total Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($commentsReport['user_comments'] as $cUser): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark small"><?= htmlspecialchars($cUser['full_name']); ?></div>
                                            <div class="text-muted micro-text">@<?= htmlspecialchars($cUser['username']); ?></div>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-danger rounded-pill px-3 py-1">
                                                <?= (int)$cUser['comment_count']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">No comment data available.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Comments Log -->
        <div class="col-12 col-lg-7">
            <div class="card-modern p-4 h-100 shadow-sm">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="bi bi-chat-left-quote-fill text-primary me-2"></i> Recent Comments Activity Log
                </h5>

                <?php if (!empty($commentsReport['recent_comments'])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($commentsReport['recent_comments'] as $rc): ?>
                            <div class="list-group-item px-0 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-primary small">
                                        @<?= htmlspecialchars($rc['commenter_username']); ?> 
                                        <span class="text-muted fw-normal">commented on</span> 
                                        @<?= htmlspecialchars($rc['post_author_username']); ?>'s post
                                    </span>
                                    <span class="text-muted micro-text"><?= date('M d, g:i a', strtotime($rc['comment_date'])); ?></span>
                                </div>
                                <div class="bg-light p-2 rounded-3 small text-dark mb-1">
                                    "<?= htmlspecialchars($rc['comment_content']); ?>"
                                </div>
                                <div class="text-muted micro-text text-truncate">
                                    Post: "<?= htmlspecialchars($rc['post_content']); ?>"
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">No recent comments recorded.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- SQL REQUIREMENTS DEMONSTRATION SUMMARY BOX (REQUIREMENT 4) -->
<div class="card-modern p-4 shadow-sm mb-4 bg-light border">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold text-dark mb-0">
            <i class="bi bi-code-slash text-primary me-2"></i> Demonstrated SQL Query Operations
        </h5>
        <span class="badge bg-secondary">School Project Verification</span>
    </div>

    <div class="row g-3 small">
        <div class="col-12 col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <strong class="text-primary d-block mb-1"><i class="bi bi-check-circle-fill me-1"></i> SELECT & WHERE</strong>
                <p class="text-muted mb-0">Used for retrieving filtered records across users, posts, comments, and likes with parameterized inputs.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <strong class="text-success d-block mb-1"><i class="bi bi-check-circle-fill me-1"></i> INNER JOIN & LEFT JOIN</strong>
                <p class="text-muted mb-0">Combines users and posts with comments and likes, preserving zero-post users with <code>LEFT JOIN</code>.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <strong class="text-warning text-dark d-block mb-1"><i class="bi bi-check-circle-fill me-1"></i> COUNT() & GROUP BY</strong>
                <p class="text-muted mb-0">Aggregates post counts, comments, and likes per user and post using <code>COUNT(DISTINCT)</code>.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <strong class="text-danger d-block mb-1"><i class="bi bi-check-circle-fill me-1"></i> ORDER BY & LIMIT</strong>
                <p class="text-muted mb-0">Ranks most active users and top liked posts from highest to lowest with structured ordering.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <strong class="text-info text-dark d-block mb-1"><i class="bi bi-check-circle-fill me-1"></i> Subqueries & Aggregates</strong>
                <p class="text-muted mb-0">Executes nested SQL count queries in system summary metric calculations.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <strong class="text-dark d-block mb-1"><i class="bi bi-shield-lock-fill me-1"></i> Prepared Statements</strong>
                <p class="text-muted mb-0">All dynamic filter values are bound using PDO prepared statements to protect against SQL injection.</p>
            </div>
        </div>
    </div>
</div>
