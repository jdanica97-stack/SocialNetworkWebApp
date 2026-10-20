<?php
// app/views/settings/index.php
// Dedicated Account Settings View: Change Name & Update Password

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">

            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i>Home</a></li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Account Settings</li>
                </ol>
            </nav>
            
            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                <div>
                    <h3 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-person-gear text-primary"></i>
                        <span>Account Settings</span>
                    </h3>
                    <p class="text-muted small mb-0">Manage your personal account details and password security.</p>
                </div>
            </div>

            <!-- Global Flash Message Alerts -->
            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <div class="d-flex flex-column gap-4">

                <!-- ========================================================
                     FEATURE 1: ACCOUNT INFORMATION (FULL NAME & USERNAME)
                     ======================================================== -->
                <div class="card-modern p-4 shadow-sm border rounded-4">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom">
                        <div class="fb-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-person-lines-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Account Information</h5>
                            <p class="text-muted small mb-0">Update your public full name and unique username handle.</p>
                        </div>
                    </div>

                    <form action="<?= BASE_URL ?>/index.php?action=settings_account" method="POST" id="accountInfoForm" novalidate>
                        <?= csrf_field(); ?>

                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-semibold small text-secondary">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
                                <input 
                                    type="text" 
                                    name="full_name" 
                                    id="full_name" 
                                    class="form-control border-start-0" 
                                    value="<?= htmlspecialchars($user['full_name'] ?? ''); ?>" 
                                    required 
                                    minlength="2" 
                                    maxlength="100" 
                                    placeholder="Enter your full name"
                                    autocomplete="name"
                                >
                            </div>
                            <div class="form-text small text-muted">
                                This name appears on your profile, posts, comments, and top navigation (2–100 characters).
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold small text-secondary">
                                Username <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-at"></i></span>
                                <input 
                                    type="text" 
                                    name="username" 
                                    id="username" 
                                    class="form-control border-start-0" 
                                    value="<?= htmlspecialchars($user['username'] ?? ''); ?>" 
                                    required 
                                    minlength="3" 
                                    maxlength="50" 
                                    pattern="[a-zA-Z0-9_]+"
                                    placeholder="Enter your new username"
                                    autocomplete="username"
                                >
                            </div>
                            <div class="form-text small text-muted">
                                Letters, numbers, and underscores only. 3–50 characters (e.g. @<?= htmlspecialchars($user['username'] ?? 'username'); ?>).
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-end pt-3 border-top">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" id="saveAccountBtn">
                                <i class="bi bi-check2-circle me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ========================================================
                     FEATURE 2: UPDATE PASSWORD
                     ======================================================== -->
                <div class="card-modern p-4 shadow-sm border rounded-4">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom">
                        <div class="fb-icon-circle bg-warning-subtle text-warning">
                            <i class="bi bi-shield-lock-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Update Password</h5>
                            <p class="text-muted small mb-0">Change your account login password with strong complexity.</p>
                        </div>
                    </div>

                    <form action="<?= BASE_URL ?>/index.php?action=settings_password" method="POST" id="changePasswordForm" novalidate>
                        <?= csrf_field(); ?>

                        <!-- Current Password -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold small text-secondary">
                                Current Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-key-fill"></i></span>
                                <input 
                                    type="password" 
                                    name="current_password" 
                                    id="current_password" 
                                    class="form-control border-start-0 border-end-0 ps-0" 
                                    required 
                                    placeholder="Enter current password"
                                    autocomplete="current-password"
                                >
                                <button type="button" class="btn btn-light border border-start-0 text-muted toggle-pwd-btn" data-target="current_password" tabindex="-1" title="Toggle visibility" aria-label="Toggle password visibility">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold small text-secondary">
                                New Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock-fill"></i></span>
                                <input 
                                    type="password" 
                                    name="new_password" 
                                    id="new_password" 
                                    class="form-control border-start-0 border-end-0 ps-0" 
                                    required 
                                    minlength="8" 
                                    placeholder="Enter new password (min. 8 characters)"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="btn btn-light border border-start-0 text-muted toggle-pwd-btn" data-target="new_password" tabindex="-1" title="Toggle visibility" aria-label="Toggle password visibility">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>

                            <!-- Real-Time Password Checklist -->
                            <div class="card bg-light border p-3 mt-2 rounded-3" id="passwordChecklist">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small fw-semibold text-secondary">Password Requirements:</span>
                                    <span class="badge bg-secondary-subtle text-secondary micro-text" id="requirementsStatus">Incomplete</span>
                                </div>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="req-item text-muted mb-1" id="req-length">
                                        <i class="bi bi-circle me-1 req-icon"></i> At least 8 characters long
                                    </li>
                                    <li class="req-item text-muted mb-1" id="req-uppercase">
                                        <i class="bi bi-circle me-1 req-icon"></i> At least one uppercase letter (A–Z)
                                    </li>
                                    <li class="req-item text-muted mb-1" id="req-lowercase">
                                        <i class="bi bi-circle me-1 req-icon"></i> At least one lowercase letter (a–z)
                                    </li>
                                    <li class="req-item text-muted mb-1" id="req-number">
                                        <i class="bi bi-circle me-1 req-icon"></i> At least one number (0–9)
                                    </li>
                                    <li class="req-item text-muted" id="req-special">
                                        <i class="bi bi-circle me-1 req-icon"></i> At least one special character (@, #, $, %, !, etc.)
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold small text-secondary">
                                Confirm New Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-check"></i></span>
                                <input 
                                    type="password" 
                                    name="confirm_password" 
                                    id="confirm_password" 
                                    class="form-control border-start-0 border-end-0 ps-0" 
                                    required 
                                    minlength="8" 
                                    placeholder="Re-enter new password"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="btn btn-light border border-start-0 text-muted toggle-pwd-btn" data-target="confirm_password" tabindex="-1" title="Toggle visibility" aria-label="Toggle password visibility">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="confirmFeedback" class="mt-1" style="font-size: 0.8rem; min-height: 20px;" aria-live="polite"></div>
                        </div>

                        <div class="d-flex align-items-center justify-content-end pt-3 border-top">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" id="updatePasswordBtn">
                                <i class="bi bi-key-fill me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- Password Validation & Interactive Feedback Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Toggle Password Visibility Buttons
    const toggleButtons = document.querySelectorAll('.toggle-pwd-btn');
    toggleButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            if (!targetInput) return;

            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                targetInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    // 2. Real-Time Password Complexity Checklist
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const requirementsStatus = document.getElementById('requirementsStatus');
    const confirmFeedback = document.getElementById('confirmFeedback');

    const reqs = {
        length: {
            el: document.getElementById('req-length'),
            test: function (pwd) { return pwd.length >= 8; }
        },
        uppercase: {
            el: document.getElementById('req-uppercase'),
            test: function (pwd) { return /[A-Z]/.test(pwd); }
        },
        lowercase: {
            el: document.getElementById('req-lowercase'),
            test: function (pwd) { return /[a-z]/.test(pwd); }
        },
        number: {
            el: document.getElementById('req-number'),
            test: function (pwd) { return /[0-9]/.test(pwd); }
        },
        special: {
            el: document.getElementById('req-special'),
            test: function (pwd) { return /[^a-zA-Z0-9]/.test(pwd); }
        }
    };

    function validatePassword() {
        const val = newPasswordInput.value;
        let satisfiedCount = 0;

        for (const key in reqs) {
            const req = reqs[key];
            if (!req.el) continue;
            const icon = req.el.querySelector('.req-icon');

            if (req.test(val)) {
                satisfiedCount++;
                req.el.classList.remove('text-muted', 'text-danger');
                req.el.classList.add('text-success');
                if (icon) {
                    icon.classList.remove('bi-circle', 'bi-x-circle-fill');
                    icon.classList.add('bi-check-circle-fill');
                }
            } else {
                req.el.classList.remove('text-success');
                req.el.classList.add('text-muted');
                if (icon) {
                    icon.classList.remove('bi-check-circle-fill', 'bi-x-circle-fill');
                    icon.classList.add('bi-circle');
                }
            }
        }

        if (requirementsStatus) {
            if (satisfiedCount === 5) {
                requirementsStatus.textContent = 'All Met';
                requirementsStatus.className = 'badge bg-success-subtle text-success micro-text';
            } else if (satisfiedCount > 0) {
                requirementsStatus.textContent = satisfiedCount + ' of 5 Met';
                requirementsStatus.className = 'badge bg-warning-subtle text-warning micro-text';
            } else {
                requirementsStatus.textContent = 'Incomplete';
                requirementsStatus.className = 'badge bg-secondary-subtle text-secondary micro-text';
            }
        }

        validateConfirmation();
    }

    function validateConfirmation() {
        const pwd = newPasswordInput.value;
        const confirm = confirmPasswordInput.value;

        if (!confirmFeedback) return;

        if (confirm.length === 0) {
            confirmFeedback.textContent = '';
            confirmFeedback.className = 'mt-1';
            confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
            return;
        }

        if (pwd === confirm) {
            confirmFeedback.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Passwords match</span>';
            confirmPasswordInput.classList.remove('is-invalid');
            confirmPasswordInput.classList.add('is-valid');
        } else {
            confirmFeedback.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i> Passwords do not match</span>';
            confirmPasswordInput.classList.remove('is-valid');
            confirmPasswordInput.classList.add('is-invalid');
        }
    }

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', validatePassword);
    }
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', validateConfirmation);
    }
});
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
