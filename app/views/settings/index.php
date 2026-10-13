<?php
// app/views/settings/index.php
// Dedicated Application Settings View (Account Security, Theme, Notifications & Preferences)

require_once __DIR__ . '/../layouts/header.php';

$activeTab = $_GET['tab'] ?? 'security';
$prefLikes    = $_SESSION['pref_notif_likes'] ?? 1;
$prefComments = $_SESSION['pref_notif_comments'] ?? 1;
$prefFollows  = $_SESSION['pref_notif_follows'] ?? 1;
$prefSound    = $_SESSION['pref_sound'] ?? 0;
?>

<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            
            <!-- Settings Header Banner -->
            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                <div>
                    <h3 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-gear-fill text-primary"></i>
                        <span>Settings</span>
                    </h3>
                    <p class="text-muted small mb-0">Manage your account security, interface preferences, and notifications.</p>
                </div>
                <a href="<?= BASE_URL ?>/index.php?action=edit_profile" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-person-gear me-1"></i> Edit Profile Info
                </a>
            </div>

            <!-- Settings Layout: Navigation Tabs & Tab Content -->
            <div class="row g-4">
                
                <!-- Left Nav Tabs -->
                <div class="col-md-4">
                    <div class="card-modern p-2">
                        <div class="list-group list-group-flush border-0">
                            <a href="<?= BASE_URL ?>/index.php?action=settings&tab=security" 
                               class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 border-0 rounded-3 mb-1 <?= ($activeTab === 'security') ? 'active bg-primary text-white' : 'text-dark' ?>">
                                <i class="bi bi-shield-lock-fill fs-5"></i>
                                <div>
                                    <div class="fw-semibold small">Password & Security</div>
                                    <div class="<?= ($activeTab === 'security') ? 'text-white-50' : 'text-muted' ?>" style="font-size: 0.73rem;">Change login password</div>
                                </div>
                            </a>

                            <a href="<?= BASE_URL ?>/index.php?action=settings&tab=appearance" 
                               class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 border-0 rounded-3 mb-1 <?= ($activeTab === 'appearance') ? 'active bg-primary text-white' : 'text-dark' ?>">
                                <i class="bi bi-palette-fill fs-5"></i>
                                <div>
                                    <div class="fw-semibold small">Appearance & Theme</div>
                                    <div class="<?= ($activeTab === 'appearance') ? 'text-white-50' : 'text-muted' ?>" style="font-size: 0.73rem;">Light & Dark mode</div>
                                </div>
                            </a>

                            <a href="<?= BASE_URL ?>/index.php?action=settings&tab=notifications" 
                               class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 border-0 rounded-3 mb-1 <?= ($activeTab === 'notifications') ? 'active bg-primary text-white' : 'text-dark' ?>">
                                <i class="bi bi-bell-fill fs-5"></i>
                                <div>
                                    <div class="fw-semibold small">Notifications</div>
                                    <div class="<?= ($activeTab === 'notifications') ? 'text-white-50' : 'text-muted' ?>" style="font-size: 0.73rem;">Alerts & live badges</div>
                                </div>
                            </a>

                            <a href="<?= BASE_URL ?>/index.php?action=settings&tab=privacy" 
                               class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 border-0 rounded-3 mb-1 <?= ($activeTab === 'privacy') ? 'active bg-primary text-white' : 'text-dark' ?>">
                                <i class="bi bi-shield-shaded fs-5"></i>
                                <div>
                                    <div class="fw-semibold small">Privacy & Visibility</div>
                                    <div class="<?= ($activeTab === 'privacy') ? 'text-white-50' : 'text-muted' ?>" style="font-size: 0.73rem;">Followers & following privacy</div>
                                </div>
                            </a>

                            <a href="<?= BASE_URL ?>/index.php?action=settings&tab=account" 
                               class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 border-0 rounded-3 <?= ($activeTab === 'account') ? 'active bg-primary text-white' : 'text-dark' ?>">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                                <div>
                                    <div class="fw-semibold small">Account Information</div>
                                    <div class="<?= ($activeTab === 'account') ? 'text-white-50' : 'text-muted' ?>" style="font-size: 0.73rem;">Username & details</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Tab Content -->
                <div class="col-md-8">
                    
                    <!-- TAB 1: PASSWORD & SECURITY -->
                    <?php if ($activeTab === 'security'): ?>
                        <div class="card-modern p-4">
                            <h5 class="fw-bold mb-1 text-dark">Password & Security</h5>
                            <p class="text-muted small mb-4">Ensure your account is using a strong password.</p>

                            <form action="<?= BASE_URL ?>/index.php?action=settings_password" method="POST">
                                <?= csrf_field(); ?>

                                <div class="mb-3">
                                    <label for="current_password" class="form-label fw-semibold small text-secondary">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-key-fill"></i></span>
                                        <input type="password" name="current_password" id="current_password" class="form-control border-start-0" required placeholder="Enter current password">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label fw-semibold small text-secondary">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" name="new_password" id="new_password" class="form-control border-start-0" required minlength="6" placeholder="At least 6 characters">
                                    </div>
                                    <div class="form-text small text-muted">Use uppercase, lowercase, numbers, or symbols for better strength.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label fw-semibold small text-secondary">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-check"></i></span>
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control border-start-0" required minlength="6" placeholder="Repeat new password">
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-check2-circle me-1"></i> Update Password
                                    </button>
                                </div>
                            </form>
                        </div>

                    <!-- TAB 2: APPEARANCE & THEME -->
                    <?php elseif ($activeTab === 'appearance'): ?>
                        <div class="card-modern p-4">
                            <h5 class="fw-bold mb-1 text-dark">Appearance & Theme</h5>
                            <p class="text-muted small mb-4">Customize how SMCC Connect looks on your browser.</p>

                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded-3 text-center cursor-pointer theme-select-box" onclick="setThemeDirect('light')" style="cursor: pointer;">
                                        <div class="p-3 bg-white border rounded mb-2 shadow-sm d-flex align-items-center justify-content-center">
                                            <i class="bi bi-sun-fill text-warning fs-1"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark">Light Theme</h6>
                                        <small class="text-muted">Standard clean collegiate interface</small>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded-3 text-center cursor-pointer theme-select-box" onclick="setThemeDirect('dark')" style="cursor: pointer;">
                                        <div class="p-3 bg-dark border rounded mb-2 shadow-sm d-flex align-items-center justify-content-center">
                                            <i class="bi bi-moon-stars-fill text-info fs-1"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark">Dark Theme</h6>
                                        <small class="text-muted">High-contrast, easy on the eyes</small>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between border">
                                <div>
                                    <div class="fw-semibold text-dark small">Instant Theme Switcher</div>
                                    <div class="text-muted small">Toggle theme anytime using the top navigation bar moon icon.</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="toggleTheme()">
                                    Toggle Now
                                </button>
                            </div>
                        </div>

                    <!-- TAB 3: NOTIFICATIONS -->
                    <?php elseif ($activeTab === 'notifications'): ?>
                        <div class="card-modern p-4">
                            <h5 class="fw-bold mb-1 text-dark">Notification Preferences</h5>
                            <p class="text-muted small mb-4">Choose which notifications you wish to receive on campus.</p>

                            <form action="<?= BASE_URL ?>/index.php?action=settings_preferences" method="POST">
                                <?= csrf_field(); ?>

                                <div class="list-group list-group-flush mb-4">
                                    <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-3 border-0 border-bottom">
                                        <div>
                                            <div class="fw-semibold text-dark small">Likes Notifications</div>
                                            <div class="text-muted small">Receive alert when someone likes your published posts.</div>
                                        </div>
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" name="pref_notif_likes" value="1" <?= $prefLikes ? 'checked' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-3 border-0 border-bottom">
                                        <div>
                                            <div class="fw-semibold text-dark small">Comments Notifications</div>
                                            <div class="text-muted small">Receive alert when a member comments on your post.</div>
                                        </div>
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" name="pref_notif_comments" value="1" <?= $prefComments ? 'checked' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-3 border-0 border-bottom">
                                        <div>
                                            <div class="fw-semibold text-dark small">Followers Notifications</div>
                                            <div class="text-muted small">Receive alert when a peer starts following your profile.</div>
                                        </div>
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" name="pref_notif_follows" value="1" <?= $prefFollows ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-save me-1"></i> Save Preferences
                                    </button>
                                </div>
                            </form>
                        </div>

                    <!-- TAB: PRIVACY & VISIBILITY -->
                    <?php elseif ($activeTab === 'privacy'): ?>
                        <?php $currentPrivacy = $user['privacy_followers'] ?? 'public'; ?>
                        <div class="card-modern p-4">
                            <h5 class="fw-bold mb-1 text-dark">Privacy & Visibility</h5>
                            <p class="text-muted small mb-4">Control who can view your followers and following lists.</p>

                            <form action="<?= BASE_URL ?>/index.php?action=settings_privacy" method="POST">
                                <?= csrf_field(); ?>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-secondary mb-3">Followers & Following Visibility</label>
                                    
                                    <div class="card border p-3 mb-3 rounded-3 <?= ($currentPrivacy === 'public') ? 'border-primary bg-primary-subtle bg-opacity-10' : '' ?>">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="privacy_followers" id="privacy_public" value="public" <?= ($currentPrivacy === 'public') ? 'checked' : '' ?>>
                                            <label class="form-check-label w-100 cursor-pointer" for="privacy_public" style="cursor: pointer;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-globe-americas text-primary fs-5"></i>
                                                    <span class="fw-semibold text-dark">Everyone (Public)</span>
                                                    <span class="badge bg-secondary-subtle text-secondary ms-auto">Default</span>
                                                </div>
                                                <div class="text-muted small mt-1">Anyone visiting your profile can click your Followers and Following counters to see who is on your list.</div>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="card border p-3 rounded-3 <?= ($currentPrivacy === 'private') ? 'border-primary bg-primary-subtle bg-opacity-10' : '' ?>">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="privacy_followers" id="privacy_private" value="private" <?= ($currentPrivacy === 'private') ? 'checked' : '' ?>>
                                            <label class="form-check-label w-100 cursor-pointer" for="privacy_private" style="cursor: pointer;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-lock-fill text-danger fs-5"></i>
                                                    <span class="fw-semibold text-dark">Only Me (Private)</span>
                                                    <span class="badge bg-danger-subtle text-danger ms-auto">Private</span>
                                                </div>
                                                <div class="text-muted small mt-1">Only you can view your followers and following lists. Other campus users visiting your profile will see a polite privacy notice.</div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-shield-check me-1"></i> Save Privacy Settings
                                    </button>
                                </div>
                            </form>
                        </div>

                    <!-- TAB 4: ACCOUNT DETAILS -->
                    <?php elseif ($activeTab === 'account'): ?>
                        <div class="card-modern p-4">
                            <h5 class="fw-bold mb-1 text-dark">Account Information</h5>
                            <p class="text-muted small mb-4">Overview of your registered campus account credentials.</p>

                            <div class="table-responsive mb-4">
                                <table class="table table-borderless align-middle mb-0">
                                    <tbody>
                                        <tr class="border-bottom">
                                            <td class="text-muted fw-semibold small py-3" style="width: 140px;">Account ID</td>
                                            <td class="fw-bold text-dark py-3">#<?= (int)$user['id'] ?></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="text-muted fw-semibold small py-3">Username</td>
                                            <td class="fw-bold text-dark py-3">@<?= htmlspecialchars($user['username']) ?></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="text-muted fw-semibold small py-3">Full Name</td>
                                            <td class="fw-bold text-dark py-3"><?= htmlspecialchars($user['full_name']) ?></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="text-muted fw-semibold small py-3">Account Role</td>
                                            <td class="py-3">
                                                <span class="badge <?= ($user['role'] === 'admin') ? 'bg-warning text-dark' : 'bg-primary-subtle text-primary' ?> px-3 py-1 rounded-pill">
                                                    <?= htmlspecialchars(ucfirst($user['role'] ?? 'user')) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-semibold small py-3">Registered Since</td>
                                            <td class="text-muted py-3"><?= date('F j, Y', strtotime($user['created_at'])) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 border">
                                <div>
                                    <div class="fw-semibold text-dark small">Want to update your bio or photo?</div>
                                    <div class="text-muted small">Manage bio, avatar, and cover photo on the Edit Profile page.</div>
                                </div>
                                <a href="<?= BASE_URL ?>/index.php?action=edit_profile" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    Go to Edit Profile
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
function setThemeDirect(themeName) {
    localStorage.setItem('smcc_theme', themeName);
    if (themeName === 'dark') {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        document.documentElement.classList.add('dark-theme');
    } else {
        document.documentElement.setAttribute('data-bs-theme', 'light');
        document.documentElement.classList.remove('dark-theme');
    }
}
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
