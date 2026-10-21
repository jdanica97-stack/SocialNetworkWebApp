<?php
// app/views/admin/dashboard.php
// Admin Dashboard View - Modern layout matching SMCC Connect theme

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/PostModel.php';
require_once __DIR__ . '/../../helpers/Security.php';
?>

<!-- ADMIN DASHBOARD HERO HEADER (Redesigned: compact, polished) -->
<div class="card-modern py-3 px-4 mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #0d1b3e 0%, #0f2147 100%); color: #e8f0fe;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill">
                    <i class="bi bi-shield-lock-fill me-1"></i> Administrator Access
                </span>
                <span class="badge hero-pill-badge rounded-pill px-3 py-1 fw-medium" style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.25);">
                    <i class="bi bi-database me-1 text-warning"></i> Database: smcc_connect_db
                </span>
            </div>
            <h2 class="fw-bold mb-0" style="color:#e8f0fe;">Admin Control Center</h2>
            <p class="mb-0 small" style="color:#7b93c8;">
                Manage platform users, moderate posts and comments, inspect platform activity, and enforce server-side security.
            </p>
        </div>
        <div class="text-md-end d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
            <a href="<?= BASE_URL ?>/index.php?action=insights&generate_report=1" class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold shadow-sm">
                <i class="bi bi-bar-chart-line-fill me-1"></i> Community Reports
            </a>
            <a href="<?= BASE_URL ?>/" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Newsfeed
            </a>
        </div>
    </div>
</div>

<!-- FLASH MESSAGES -->
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

<!-- 1. DASHBOARD OVERVIEW SUMMARY CARDS -->
<div class="row g-3 mb-4">
    <!-- Total Users Card -->
    <div class="col-6 col-md-3">
        <div class="card-modern admin-kpi-card h-100 border-0 shadow-sm" style="border-left: 4px solid #3b82f6 !important;">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="admin-kpi-number"><?= (int)($summaryStats['total_users'] ?? 0); ?></div>
                    <div class="admin-kpi-label">Total Users</div>
                    <span class="text-muted micro-text mt-1 d-block">
                        <i class="bi bi-shield-check text-warning me-1"></i> <?= (int)($summaryStats['admin_users'] ?? 0); ?> Admins &bull; <?= (int)($summaryStats['normal_users'] ?? 0); ?> Users
                    </span>
                </div>
                <div class="admin-kpi-icon" style="background: rgba(59,130,246,0.12); color:#3b82f6;">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Posts Card -->
    <div class="col-6 col-md-3">
        <div class="card-modern admin-kpi-card h-100 border-0 shadow-sm" style="border-left: 4px solid #10b981 !important;">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="admin-kpi-number"><?= (int)($summaryStats['total_posts'] ?? 0); ?></div>
                    <div class="admin-kpi-label">Total Posts</div>
                    <span class="text-muted micro-text mt-1 d-block">
                        <i class="bi bi-bar-chart-line text-success me-1"></i> Avg <?= htmlspecialchars($summaryStats['avg_posts_per_user'] ?? 0); ?> posts/user
                    </span>
                </div>
                <div class="admin-kpi-icon" style="background: rgba(16,185,129,0.12); color:#10b981;">
                    <i class="bi bi-file-earmark-post-fill fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Comments Card -->
    <div class="col-6 col-md-3">
        <div class="card-modern admin-kpi-card h-100 border-0 shadow-sm" style="border-left: 4px solid #f59e0b !important;">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="admin-kpi-number"><?= (int)($summaryStats['total_comments'] ?? 0); ?></div>
                    <div class="admin-kpi-label">Total Comments</div>
                    <span class="text-muted micro-text mt-1 d-block">
                        <i class="bi bi-chat-dots text-warning me-1"></i> Avg <?= htmlspecialchars($summaryStats['avg_comments_per_post'] ?? 0); ?> comments/post
                    </span>
                </div>
                <div class="admin-kpi-icon" style="background: rgba(245,158,11,0.12); color:#f59e0b;">
                    <i class="bi bi-chat-left-text-fill fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Likes Card -->
    <div class="col-6 col-md-3">
        <div class="card-modern admin-kpi-card h-100 border-0 shadow-sm" style="border-left: 4px solid #ef4444 !important;">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="admin-kpi-number"><?= (int)($summaryStats['total_likes'] ?? 0); ?></div>
                    <div class="admin-kpi-label">Total Likes</div>
                    <span class="text-muted micro-text mt-1 d-block">
                        <i class="bi bi-hand-thumbs-up text-danger me-1"></i> Engagements logged
                    </span>
                </div>
                <div class="admin-kpi-icon" style="background: rgba(239,68,68,0.12); color:#ef4444;">
                    <i class="bi bi-heart-fill fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ADMIN TABS AND CONTENT WRAPPER -->
<div id="adminDashboardTabsWrapper">
    <!-- NAVIGATION TABS (horizontally scrollable on mobile) -->
    <div class="admin-tabs-scroll mb-3">
        <ul class="nav nav-tabs border-bottom-0 gap-1 flex-nowrap" id="adminTabs" style="min-width: max-content;">
            <li class="nav-item">
                <a class="nav-link rounded-3 fw-semibold admin-tab-btn <?= ($tab === 'overview') ? 'active' : ''; ?>"
                   href="<?= BASE_URL ?>/index.php?action=admin&tab=overview"
                   data-tab="overview">
                    <i class="bi bi-speedometer2 me-1 text-primary"></i> Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-3 fw-semibold admin-tab-btn <?= ($tab === 'users') ? 'active' : ''; ?>"
                   href="<?= BASE_URL ?>/index.php?action=admin&tab=users"
                   data-tab="users">
                    <i class="bi bi-people-fill me-1 text-success"></i> User Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-3 fw-semibold admin-tab-btn <?= ($tab === 'posts') ? 'active' : ''; ?>"
                   href="<?= BASE_URL ?>/index.php?action=admin&tab=posts"
                   data-tab="posts">
                    <i class="bi bi-file-earmark-post me-1 text-info"></i> Post Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-3 fw-semibold admin-tab-btn <?= ($tab === 'comments') ? 'active' : ''; ?>"
                   href="<?= BASE_URL ?>/index.php?action=admin&tab=comments"
                   data-tab="comments">
                    <i class="bi bi-chat-left-dots-fill me-1 text-warning"></i> Comment Moderation
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-3 fw-semibold admin-tab-btn <?= ($tab === 'events') ? 'active' : ''; ?>"
                   href="<?= BASE_URL ?>/index.php?action=admin&tab=events"
                   data-tab="events">
                    <i class="bi bi-calendar-event-fill me-1 text-primary"></i> Campus Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-3 fw-semibold admin-tab-btn <?= ($tab === 'stats') ? 'active' : ''; ?>"
                   href="<?= BASE_URL ?>/index.php?action=admin&tab=stats"
                   data-tab="stats">
                    <i class="bi bi-graph-up-arrow me-1 text-danger"></i> Platform Activity Stats
                </a>
            </li>
        </ul>
    </div>

    <!-- DYNAMIC TAB PANELS CONTAINER -->
    <div id="adminTabPanelsContainer">

<!-- TAB CONTENT 1: OVERVIEW -->
<?php if ($tab === 'overview'): ?>
    <div class="row g-4 mb-4">
        <!-- Quick Stats & Top Active Users -->
        <div class="col-12 col-lg-7">
            <div class="card-modern p-4 h-100 shadow-sm">
                <div class="admin-panel-header border-bottom pb-2 mb-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-trophy-fill text-warning me-2"></i> Top Active Contributors
                        </h5>
                        <span class="badge bg-light text-dark border">Ranked by Posts</span>
                    </div>
                </div>

                <?php if (!empty($mostActiveUsers)): ?>
                    <div>
                        <?php foreach ($mostActiveUsers as $idx => $u): ?>
                            <?php
                            $initials = strtoupper(mb_substr($u['full_name'] ?: $u['username'], 0, 2));
                            ?>
                            <div class="admin-contributor-row d-flex align-items-center justify-content-between py-2 px-2 rounded-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:24px; height:24px; font-size:0.75rem; flex-shrink:0;">
                                        <?= $idx + 1; ?>
                                    </span>
                                    <div class="user-avatar-sm" style="width:36px; height:36px; font-size:0.85rem; flex-shrink:0;">
                                        <?= htmlspecialchars($initials); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark lh-sm">
                                            <?= htmlspecialchars($u['full_name']); ?>
                                            <?php if ($u['role'] === 'admin'): ?>
                                                <span class="badge bg-warning text-dark micro-text ms-1">Admin</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-muted small">@<?= htmlspecialchars($u['username']); ?></span>
                                    </div>
                                </div>
                                <div class="text-end ms-2">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                        <?= (int)$u['total_posts']; ?> <?= $u['total_posts'] == 1 ? 'post' : 'posts'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">No contributors found yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Most Liked Posts Preview -->
        <div class="col-12 col-lg-5">
            <div class="card-modern p-4 h-100 shadow-sm">
                <div class="admin-panel-header border-bottom pb-2 mb-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-heart-fill text-danger me-2"></i> Most Liked Content
                        </h5>
                        <span class="badge bg-light text-dark border">Engagements</span>
                    </div>
                </div>

                <?php if (!empty($mostLikedPosts)): ?>
                    <div>
                        <?php foreach ($mostLikedPosts as $lp): ?>
                            <div class="py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark small">@<?= htmlspecialchars($lp['author_username']); ?></span>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                        <i class="bi bi-heart-fill me-1"></i> <?= (int)$lp['like_count']; ?>
                                    </span>
                                </div>
                                <p class="text-secondary small mb-1 text-truncate" style="max-width: 100%;">
                                    "<?= htmlspecialchars($lp['content']); ?>"
                                </p>
                                <span class="text-muted micro-text"><?= date('M d, Y h:i A', strtotime($lp['created_at'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">No liked posts recorded.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- TAB CONTENT 2: USER MANAGEMENT (REQUIREMENT 4) -->
<?php if ($tab === 'users'): ?>
    <div class="card-modern admin-content-panel p-4 shadow-sm mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3 border-bottom pb-3">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-people-fill text-success me-2"></i> Platform Registered Users
                </h5>
                <p class="text-muted small mb-0">
                    Manage member accounts, review user roles, and adjust permissions securely. Sensitive security credentials (password hashes) are excluded.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASE_URL ?>/index.php?action=admin_reorder_users" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="return confirm('Resequence all user IDs starting from 1 with no gaps?');" title="Renumber user IDs consecutively (1, 2, 3...) and reset AUTO_INCREMENT">
                    <i class="bi bi-arrow-repeat me-1"></i> Resequence IDs (1..N)
                </a>
                <span class="badge bg-primary rounded-pill px-3 py-2 align-self-start align-self-md-auto">
                    Total Matches: <?= (int)$pagination['total_items']; ?>
                </span>
            </div>
        </div>

        <!-- SEARCH AND ROLE FILTER TOOLBAR -->
        <form action="<?= BASE_URL ?>/index.php" method="GET" class="row g-2 mb-4 align-items-center">
            <input type="hidden" name="action" value="admin">
            <input type="hidden" name="tab" value="users">

            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Search by username or full name..." value="<?= htmlspecialchars($search); ?>">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    <option value="admin" <?= ($roleFilter === 'admin') ? 'selected' : ''; ?>>Administrators Only</option>
                    <option value="user" <?= ($roleFilter === 'user') ? 'selected' : ''; ?>>Regular Users Only</option>
                </select>
            </div>

            <div class="col-6 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Apply Filter
                </button>
                <a href="<?= BASE_URL ?>/index.php?action=admin&tab=users" class="btn btn-outline-secondary btn-sm rounded-pill">
                    Reset
                </a>
            </div>
        </form>

        <!-- USERS TABLE -->
        <?php if (!empty($usersData)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User Details</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th class="text-center">Activity</th>
                            <th>Registered Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usersData as $u): ?>
                            <?php
                            $initials = strtoupper(mb_substr($u['full_name'] ?: $u['username'], 0, 2));
                            $isSelf = ((int)$u['id'] === (int)$_SESSION['user_id']);
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-sm" style="width:36px; height:36px; font-size:0.85rem;">
                                            <?= htmlspecialchars($initials); ?>
                                        </div>
                                        <div>
                                            <a href="<?= BASE_URL ?>/index.php?action=profile&id=<?= (int)$u['id']; ?>" class="fw-bold text-dark text-decoration-none">
                                                <?= htmlspecialchars($u['full_name']); ?>
                                            </a>
                                            <?php if ($isSelf): ?>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle micro-text ms-1">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="text-secondary fw-semibold">@<?= htmlspecialchars($u['username']); ?></span></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill"><i class="bi bi-shield-check me-1"></i> Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border px-3 py-1 rounded-pill">User</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border me-1"><?= (int)$u['post_count']; ?> posts</span>
                                    <span class="badge bg-light text-dark border"><?= (int)$u['comment_count']; ?> comments</span>
                                </td>
                                <td class="small text-muted"><?= date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        <!-- CHANGE ROLE FORM -->
                                        <form action="<?= BASE_URL ?>/index.php?action=admin_change_role" method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id']; ?>">
                                            <?php if ($u['role'] === 'admin'): ?>
                                                <input type="hidden" name="role" value="user">
                                                <button type="submit" class="btn btn-outline-warning btn-sm" title="Demote to User" <?= $isSelf ? 'disabled' : ''; ?> onclick="return confirm('Are you sure you want to demote this admin to regular user?');">
                                                    <i class="bi bi-arrow-down-circle"></i> Demote
                                                </button>
                                            <?php else: ?>
                                                <input type="hidden" name="role" value="admin">
                                                <button type="submit" class="btn btn-outline-success btn-sm" title="Promote to Admin" onclick="return confirm('Are you sure you want to promote this user to Administrator?');">
                                                    <i class="bi bi-arrow-up-circle"></i> Promote
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <!-- DELETE USER FORM -->
                                        <form action="<?= BASE_URL ?>/index.php?action=admin_delete_user" method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete User Account" <?= $isSelf ? 'disabled' : ''; ?> onclick="return confirm('WARNING: Are you sure you want to delete account @<?= htmlspecialchars($u['username']); ?>? All their posts and comments will also be removed.');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION LINKS -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li class="page-item <?= ($pagination['current_page'] <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/index.php?action=admin&tab=users&q=<?= urlencode($search); ?>&role=<?= urlencode($roleFilter); ?>&page=<?= $pagination['current_page'] - 1; ?>">Previous</a>
                        </li>
                        <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                            <li class="page-item <?= ($p == $pagination['current_page']) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/index.php?action=admin&tab=users&q=<?= urlencode($search); ?>&role=<?= urlencode($roleFilter); ?>&page=<?= $p; ?>"><?= $p; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($pagination['current_page'] >= $pagination['total_pages']) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/index.php?action=admin&tab=users&q=<?= urlencode($search); ?>&role=<?= urlencode($roleFilter); ?>&page=<?= $pagination['current_page'] + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-2 d-block mb-2"></i>
                No users found matching your criteria.
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- TAB CONTENT 3: POST MANAGEMENT (REQUIREMENT 5) -->
<?php if ($tab === 'posts'): ?>
    <div class="card-modern admin-content-panel p-4 shadow-sm mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3 border-bottom pb-3">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-file-earmark-post text-info me-2"></i> Platform Posts Overview &amp; Moderation
                </h5>
                <p class="text-muted small mb-0">
                    Review and moderate user posts across the network. Authorized administrators can remove inappropriate content.
                </p>
            </div>
            <span class="badge bg-info rounded-pill px-3 py-2 align-self-start align-self-md-auto text-white">
                Total Posts: <?= (int)$pagination['total_items']; ?>
            </span>
        </div>

        <!-- SEARCH TOOLBAR -->
        <form action="<?= BASE_URL ?>/index.php" method="GET" class="row g-2 mb-4">
            <input type="hidden" name="action" value="admin">
            <input type="hidden" name="tab" value="posts">

            <div class="col-12 col-md-8">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Search post content or author name..." value="<?= htmlspecialchars($search); ?>">
                </div>
            </div>

            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Search
                </button>
                <a href="<?= BASE_URL ?>/index.php?action=admin&tab=posts" class="btn btn-outline-secondary btn-sm rounded-pill">
                    Reset
                </a>
            </div>
        </form>

        <!-- POSTS TABLE -->
        <?php if (!empty($postsData)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Author</th>
                            <th>Content Preview</th>
                            <th class="text-center">Image</th>
                            <th class="text-center">Engagements</th>
                            <th>Posted Date</th>
                            <th class="text-end">Moderation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($postsData as $p): ?>
                            <tr>
                                <td class="text-muted fw-bold">#<?= (int)$p['post_id']; ?></td>
                                <td>
                                    <div>
                                        <a href="<?= BASE_URL ?>/index.php?action=profile&id=<?= (int)$p['author_id']; ?>" class="fw-bold text-dark text-decoration-none">
                                            <?= htmlspecialchars($p['author_fullname']); ?>
                                        </a>
                                        <span class="d-block text-muted micro-text">@<?= htmlspecialchars($p['author_username']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-dark small text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($p['content']); ?>">
                                        <?= htmlspecialchars($p['content']); ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $pImages = PostModel::parseImages($p['image'] ?? null);
                                    $pImgCount = count($pImages);
                                    ?>
                                    <?php if ($pImgCount > 0): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                            <i class="bi <?= $pImgCount > 1 ? 'bi-images' : 'bi-image' ?> me-1"></i>
                                            <?= $pImgCount === 1 ? '1 Image' : $pImgCount . ' Images' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted micro-text">None</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger rounded-pill me-1"><i class="bi bi-heart-fill"></i> <?= (int)$p['like_count']; ?></span>
                                    <span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-chat-left-dots-fill"></i> <?= (int)$p['comment_count']; ?></span>
                                </td>
                                <td class="small text-muted"><?= date('M d, Y h:i A', strtotime($p['created_at'])); ?></td>
                                <td class="text-end">
                                    <form action="<?= BASE_URL ?>/index.php?action=admin_delete_post" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                        <input type="hidden" name="post_id" value="<?= (int)$p['post_id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete post #<?= (int)$p['post_id']; ?> by @<?= htmlspecialchars($p['author_username']); ?>?');">
                                            <i class="bi bi-trash me-1"></i> Remove Post
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li class="page-item <?= ($pagination['current_page'] <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/index.php?action=admin&tab=posts&q=<?= urlencode($search); ?>&page=<?= $pagination['current_page'] - 1; ?>">Previous</a>
                        </li>
                        <?php for ($pg = 1; $pg <= $pagination['total_pages']; $pg++): ?>
                            <li class="page-item <?= ($pg == $pagination['current_page']) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/index.php?action=admin&tab=posts&q=<?= urlencode($search); ?>&page=<?= $pg; ?>"><?= $pg; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($pagination['current_page'] >= $pagination['total_pages']) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/index.php?action=admin&tab=posts&q=<?= urlencode($search); ?>&page=<?= $pagination['current_page'] + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark-x fs-2 d-block mb-2"></i>
                No posts found matching search filter.
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- TAB CONTENT 4: COMMENT MODERATION (REQUIREMENT 6) -->
<?php if ($tab === 'comments'): ?>
    <div class="card-modern admin-content-panel p-4 shadow-sm mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3 border-bottom pb-3">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-chat-left-dots-fill text-warning me-2"></i> Comment Moderation Control
                </h5>
                <p class="text-muted small mb-0">
                    Inspect user comments across the platform and remove inappropriate or abusive remarks.
                </p>
            </div>
            <span class="badge bg-warning text-dark rounded-pill px-3 py-2 align-self-start align-self-md-auto">
                Total Comments: <?= (int)$pagination['total_items']; ?>
            </span>
        </div>

        <!-- SEARCH TOOLBAR -->
        <form action="<?= BASE_URL ?>/index.php" method="GET" class="row g-2 mb-4">
            <input type="hidden" name="action" value="admin">
            <input type="hidden" name="tab" value="comments">

            <div class="col-12 col-md-8">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Search comment content, commenter, or post text..." value="<?= htmlspecialchars($search); ?>">
                </div>
            </div>

            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Search
                </button>
                <a href="<?= BASE_URL ?>/index.php?action=admin&tab=comments" class="btn btn-outline-secondary btn-sm rounded-pill">
                    Reset
                </a>
            </div>
        </form>

        <!-- COMMENTS TABLE -->
        <?php if (!empty($commentsData)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Commenter</th>
                            <th>Comment Text</th>
                            <th>Parent Post</th>
                            <th>Date</th>
                            <th class="text-end">Moderation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commentsData as $c): ?>
                            <tr>
                                <td class="text-muted fw-bold">#<?= (int)$c['comment_id']; ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/index.php?action=profile&id=<?= (int)$c['commenter_id']; ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= htmlspecialchars($c['commenter_fullname']); ?>
                                    </a>
                                    <span class="d-block text-muted micro-text">@<?= htmlspecialchars($c['commenter_username']); ?></span>
                                </td>
                                <td>
                                    <div class="bg-light p-2 rounded-3 small text-dark" style="max-width: 300px;">
                                        "<?= htmlspecialchars($c['comment_content']); ?>"
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted small text-truncate" style="max-width: 220px;">
                                        Post #<?= (int)$c['post_id']; ?> by @<?= htmlspecialchars($c['post_author_username']); ?>:
                                        "<?= htmlspecialchars($c['post_content']); ?>"
                                    </div>
                                </td>
                                <td class="small text-muted"><?= date('M d, Y h:i A', strtotime($c['comment_date'])); ?></td>
                                <td class="text-end">
                                    <form action="<?= BASE_URL ?>/index.php?action=admin_delete_comment" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                        <input type="hidden" name="comment_id" value="<?= (int)$c['comment_id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete comment #<?= (int)$c['comment_id']; ?>?');">
                                            <i class="bi bi-trash me-1"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li class="page-item <?= ($pagination['current_page'] <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/index.php?action=admin&tab=comments&q=<?= urlencode($search); ?>&page=<?= $pagination['current_page'] - 1; ?>">Previous</a>
                        </li>
                        <?php for ($cg = 1; $cg <= $pagination['total_pages']; $cg++): ?>
                            <li class="page-item <?= ($cg == $pagination['current_page']) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/index.php?action=admin&tab=comments&q=<?= urlencode($search); ?>&page=<?= $cg; ?>"><?= $cg; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($pagination['current_page'] >= $pagination['total_pages']) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/index.php?action=admin&tab=comments&q=<?= urlencode($search); ?>&page=<?= $pagination['current_page'] + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-chat-left-x fs-2 d-block mb-2"></i>
                No comments found matching filter.
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- TAB CONTENT 5: PLATFORM ACTIVITY STATS (REQUIREMENT 7) -->
<?php if ($tab === 'stats'): ?>
    <div class="row g-4 mb-4">
        <!-- Most Active Users -->
        <div class="col-12 col-lg-6">
            <div class="card-modern admin-content-panel p-4 h-100 shadow-sm">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="bi bi-graph-up text-primary me-2"></i> Most Active Users (Post Counts)
                </h5>
                <?php if (!empty($mostActiveUsers)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th class="text-center">Total Posts</th>
                                    <th class="text-end">Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mostActiveUsers as $index => $u): ?>
                                    <tr>
                                        <td class="fw-bold text-muted"><?= $index + 1; ?></td>
                                        <td>
                                            <span class="fw-bold text-dark">@<?= htmlspecialchars($u['username']); ?></span>
                                            <span class="d-block text-muted micro-text"><?= htmlspecialchars($u['full_name']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary rounded-pill px-3"><?= (int)$u['total_posts']; ?></span>
                                        </td>
                                        <td class="text-end micro-text text-muted">
                                            <?= !empty($u['last_post_at']) ? date('M d, Y', strtotime($u['last_post_at'])) : 'N/A'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">No activity records.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Most Liked Posts -->
        <div class="col-12 col-lg-6">
            <div class="card-modern admin-content-panel p-4 h-100 shadow-sm">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="bi bi-heart-fill text-danger me-2"></i> Most Liked Posts Ranking
                </h5>
                <?php if (!empty($mostLikedPosts)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($mostLikedPosts as $lp): ?>
                            <div class="list-group-item px-0 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark small">@<?= htmlspecialchars($lp['author_username']); ?></span>
                                    <span class="badge bg-danger rounded-pill px-3 py-1">
                                        <i class="bi bi-heart-fill me-1"></i> <?= (int)$lp['like_count']; ?> Likes
                                    </span>
                                </div>
                                <p class="text-secondary small mb-1 text-truncate">
                                    "<?= htmlspecialchars($lp['content']); ?>"
                                </p>
                                <span class="text-muted micro-text"><?= date('M d, Y h:i A', strtotime($lp['created_at'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">No liked posts available.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Community Reports Generator Card for Admin -->
    <div class="card-modern admin-content-panel p-4 mb-4 shadow-sm border-0" style="background: linear-gradient(135deg, rgba(25, 47, 93, 0.05) 0%, rgba(241, 189, 39, 0.08) 100%);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary rounded-pill px-2.5 py-1 micro-text fw-bold">Admin Analytics</span>
                    <h5 class="fw-bold text-dark mb-0">Full Community Database Reports</h5>
                </div>
                <p class="text-secondary small mb-0">
                    Generate comprehensive SQL analytics including author activity rankings, interaction summaries, and timeline metrics.
                </p>
            </div>
            <div>
                <a href="<?= BASE_URL ?>/index.php?action=insights&generate_report=1" class="btn btn-warning rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart-line-fill"></i>
                    <span>Generate Full Community Report</span>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- TAB CONTENT 6: CAMPUS EVENTS MANAGEMENT -->
<?php if ($tab === 'events'): ?>
    <div class="card-modern admin-content-panel p-4 mb-4 shadow-sm">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 border-bottom pb-3 mb-4">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-calendar-event-fill text-primary me-2"></i> Campus Events Management
                </h5>
                <p class="text-muted small mb-0">
                    Publish, update, and manage official campus events shown on students' feeds and events widget.
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-navy btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalCreateEvent">
                    <i class="bi bi-plus-lg me-1"></i> Add New Event
                </button>
            </div>
        </div>

        <?php if (!empty($eventsData)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 130px;">Date &amp; Time</th>
                            <th>Event Title</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Posted By</th>
                            <th class="text-end" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventsData as $ev): ?>
                            <?php 
                                $evDateObj = !empty($ev['event_date']) ? strtotime($ev['event_date']) : time();
                                $isPast = ($evDateObj < strtotime('today'));
                            ?>
                            <tr class="<?= $isPast ? 'opacity-75' : ''; ?>">
                                <td>
                                    <div class="fw-bold <?= $isPast ? 'text-muted' : 'text-primary'; ?>">
                                        <?= date('M d, Y', $evDateObj); ?>
                                    </div>
                                    <?php if (!empty($ev['event_time'])): ?>
                                        <div class="text-muted micro-text">
                                            <i class="bi bi-clock me-1"></i><?= htmlspecialchars($ev['event_time']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($isPast): ?>
                                        <span class="badge bg-secondary micro-text">Past</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success micro-text">Upcoming</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-dark"><?= htmlspecialchars($ev['title']); ?></strong>
                                </td>
                                <td>
                                    <span class="small text-secondary">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($ev['location']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-muted" style="max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($ev['description'] ?? ''); ?>">
                                        <?= !empty($ev['description']) ? htmlspecialchars($ev['description']) : '<em class="text-muted">No description</em>'; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="small text-secondary">
                                        <?= htmlspecialchars($ev['organizer_name'] ?? 'Administrator'); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <!-- View Full Details Preview Button -->
                                        <button type="button" 
                                                class="btn btn-outline-secondary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalAdminViewEvent<?= (int)$ev['id']; ?>"
                                                title="View Full Details Preview">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <!-- Edit Event Button -->
                                        <button type="button" 
                                                class="btn btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEditEvent<?= (int)$ev['id']; ?>"
                                                title="Edit Event">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <!-- Delete Event Button -->
                                        <form action="<?= BASE_URL ?>/index.php?action=admin_delete_event" method="POST" class="d-inline" onsubmit="return confirm('Delete this campus event? Students will no longer see it on their feed.');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="event_id" value="<?= (int)$ev['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger" title="Delete Event">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- MODALS FOR VIEWING & EDITING EVENTS (Cleanly positioned outside table) -->
            <?php foreach ($eventsData as $ev): ?>
                <?php 
                    $evDateObj = !empty($ev['event_date']) ? strtotime($ev['event_date']) : time();
                    $isPast = ($evDateObj < strtotime('today'));
                ?>
                <!-- 1. PREVIEW DETAILS MODAL -->
                <div class="modal fade" id="modalAdminViewEvent<?= (int)$ev['id']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header border-0 bg-primary bg-opacity-10 py-3 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-calendar-event-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="modal-title fw-bold text-dark mb-0"><?= htmlspecialchars($ev['title']); ?></h5>
                                        <span class="micro-text text-muted">Student View Preview &bull; Campus Event</span>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
                                    <?php if ($isPast): ?>
                                        <span class="badge bg-secondary rounded-pill px-3 py-1.5"><i class="bi bi-clock-history me-1"></i>Past Event</span>
                                    <?php else: ?>
                                        <span class="badge bg-success rounded-pill px-3 py-1.5"><i class="bi bi-calendar-check me-1"></i>Upcoming Campus Event</span>
                                    <?php endif; ?>
                                    <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($ev['location']); ?></span>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-sm-6">
                                        <div class="p-3 rounded-3 bg-light border">
                                            <div class="text-muted small fw-semibold mb-1"><i class="bi bi-calendar3 text-primary me-1"></i> Date</div>
                                            <div class="fw-bold text-dark"><?= date('l, F j, Y', $evDateObj); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-3 rounded-3 bg-light border">
                                            <div class="text-muted small fw-semibold mb-1"><i class="bi bi-clock text-warning me-1"></i> Time</div>
                                            <div class="fw-bold text-dark"><?= !empty($ev['event_time']) ? htmlspecialchars($ev['event_time']) : 'Time not specified'; ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 rounded-3 bg-white border shadow-sm mb-3">
                                    <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                                        <i class="bi bi-file-text-fill text-primary"></i>
                                        <span>Full Description &amp; Information</span>
                                    </h6>
                                    <div class="text-secondary" style="line-height: 1.7; font-size: 0.95rem;">
                                        <?= !empty($ev['description']) ? nl2br(htmlspecialchars($ev['description'])) : '<em class="text-muted">No description provided.</em>'; ?>
                                    </div>
                                </div>

                                <div class="small text-muted d-flex justify-content-between align-items-center">
                                    <span>Posted by: <strong><?= htmlspecialchars($ev['organizer_name'] ?? 'Administrator'); ?></strong></span>
                                    <span>Last Updated: <?= htmlspecialchars($ev['updated_at'] ?? $ev['created_at'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            <div class="modal-footer border-top bg-light py-2 px-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalEditEvent<?= (int)$ev['id']; ?>">
                                    <i class="bi bi-pencil-square me-1"></i>Edit Information
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. EDIT EVENT MODAL -->
                <div class="modal fade" id="modalEditEvent<?= (int)$ev['id']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <form action="<?= BASE_URL ?>/index.php?action=admin_save_event" method="POST">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="event_id" value="<?= (int)$ev['id']; ?>">
                                <div class="modal-header border-bottom bg-light py-3 px-4">
                                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                        <span>Edit Campus Event Information</span>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Event Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($ev['title']); ?>" required placeholder="e.g. CCIS Days Celebration">
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Event Date <span class="text-danger">*</span></label>
                                            <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($ev['event_date']); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Event Time</label>
                                            <input type="text" name="event_time" class="form-control" placeholder="e.g. 8:00 AM - 12:00 PM" value="<?= htmlspecialchars($ev['event_time'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Venue / Location <span class="text-danger">*</span></label>
                                        <input type="text" name="location" class="form-control" placeholder="e.g. SMCC Gymnasium or Quadrangle" value="<?= htmlspecialchars($ev['location']); ?>" required>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold">Full Event Description &amp; Details</label>
                                        <textarea name="description" rows="5" class="form-control" placeholder="Enter complete guidelines, schedule, dress code, and details for students..."><?= htmlspecialchars($ev['description'] ?? ''); ?></textarea>
                                        <div class="form-text small text-muted">
                                            This complete text will be shown when students click on the event to view full details.
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-top bg-light py-3 px-4 d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
                                        <i class="bi bi-check2-circle me-1"></i> Update Event Information
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <h6 class="fw-bold">No campus events found</h6>
                <p class="small text-muted mb-3">Publish upcoming events so students can see them in their newsfeed widget.</p>
                <button type="button" class="btn btn-navy btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalCreateEvent">
                    <i class="bi bi-plus-lg me-1"></i> Add Event Now
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- CREATE EVENT MODAL -->
    <div class="modal fade" id="modalCreateEvent" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="<?= BASE_URL ?>/index.php?action=admin_save_event" method="POST">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="event_id" value="0">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bi bi-calendar-plus text-primary me-2"></i>Publish Campus Event
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Event Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Intramurals Opening Ceremony" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Event Date <span class="text-danger">*</span></label>
                                <input type="date" name="event_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Event Time</label>
                                <input type="text" name="event_time" class="form-control" placeholder="e.g. 8:00 AM">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. SMCC Gymnasium" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold">Description</label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Provide event details, instructions, or agenda for students..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy btn-sm rounded-pill px-4">Publish Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

    </div><!-- Close #adminTabPanelsContainer -->
</div><!-- Close #adminDashboardTabsWrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabsNav = document.getElementById('adminTabs');
    var panelsContainer = document.getElementById('adminTabPanelsContainer');
    if (!tabsNav || !panelsContainer) return;

    tabsNav.addEventListener('click', function(e) {
        var link = e.target.closest('.admin-tab-btn');
        if (!link) return;

        e.preventDefault();

        var tabName = link.getAttribute('data-tab');
        var targetUrl = link.getAttribute('href');

        // Update active class on tab buttons immediately
        // Only toggle .active — CSS handles all styling via .admin-tab-btn.active / :not(.active)
        tabsNav.querySelectorAll('.admin-tab-btn').forEach(function(btn) {
            btn.classList.remove('active', 'bg-white', 'shadow-sm', 'border', 'bg-light', 'text-secondary');
        });
        link.classList.add('active');

        // Smooth subtle fade on container while loading
        panelsContainer.style.opacity = '0.5';
        panelsContainer.style.transition = 'opacity 0.15s ease';

        // Fetch new tab content via AJAX
        var fetchUrl = targetUrl + (targetUrl.indexOf('?') !== -1 ? '&' : '?') + 'ajax=1';

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) {
            return res.text();
        })
        .then(function(html) {
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');
            var newContent = doc.getElementById('adminTabPanelsContainer');

            if (newContent) {
                panelsContainer.innerHTML = newContent.innerHTML;
            } else {
                // Fallback: extract anything outside hero header
                var fallbackContent = doc.getElementById('adminDashboardTabsWrapper');
                if (fallbackContent) {
                    var inner = fallbackContent.querySelector('#adminTabPanelsContainer');
                    if (inner) panelsContainer.innerHTML = inner.innerHTML;
                }
            }

            // Update browser URL without reloading or jumping
            window.history.pushState({ tab: tabName }, '', targetUrl);

            panelsContainer.style.opacity = '1';

            // Re-initialize any bootstrap components (tooltips, dropdowns, modals) in dynamic content
            if (window.bootstrap) {
                var tooltips = panelsContainer.querySelectorAll('[data-bs-toggle="tooltip"]');
                tooltips.forEach(function(el) { new bootstrap.Tooltip(el); });
            }
        })
        .catch(function(err) {
            console.error('Tab load failed:', err);
            // Fallback to normal navigation if fetch fails
            window.location.href = targetUrl;
        });
    });

    // Handle back/forward browser buttons
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.tab) {
            var btn = tabsNav.querySelector('[data-tab="' + e.state.tab + '"]');
            if (btn) btn.click();
        } else {
            window.location.reload();
        }
    });
});
</script>
