<?php
// app/views/auth/register.php
// User Registration View - Modernized Design matching SMCC Connect branding

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="auth-card-wrapper w-100" style="max-width: 480px;">
    <div class="card-modern shadow-lg border-0 p-4 p-md-5 my-auto">
        <div class="text-center mb-4">
            <div class="brand-icon-box mx-auto mb-3" style="width: 52px; height: 52px; font-size: 1.6rem; background-color: var(--sidebar-bg); color: var(--accent-yellow);">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <h3 class="fw-bold text-navy mb-1">Create an Account</h3>
            <p class="text-muted small">Join SMCC Connect and connect with the campus community.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/index.php?action=register" method="POST" id="registerForm" novalidate>
            <?= csrf_field(); ?>
            <!-- Full Name -->
            <div class="mb-3">
                <label for="full_name" class="form-label fw-semibold small text-navy">Full Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                    <input
                        type="text"
                        class="form-control border-start-0 ps-0"
                        id="full_name"
                        name="full_name"
                        value="<?= htmlspecialchars($formData['full_name'] ?? ''); ?>"
                        placeholder="e.g. Maria Santos"
                        required
                        autofocus
                    >
                </div>
            </div>

            <!-- Username -->
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold small text-navy">Username <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-at"></i></span>
                    <input
                        type="text"
                        class="form-control border-start-0 ps-0"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars($formData['username'] ?? ''); ?>"
                        placeholder="e.g. msantos"
                        required
                    >
                </div>
                <div class="form-text small text-muted">Letters, numbers, and underscores only.</div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold small text-navy">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                    <input
                        type="password"
                        class="form-control border-start-0 border-end-0 ps-0"
                        id="password"
                        name="password"
                        placeholder="Create a strong password"
                        required
                        autocomplete="new-password"
                        aria-describedby="passwordRequirements"
                    >
                    <button type="button" class="btn btn-light border border-start-0 text-muted toggle-password-btn" data-target="password" tabindex="-1" title="Toggle password visibility" aria-label="Toggle password visibility">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <!-- Password Requirements Checklist -->
                <div class="password-requirements p-3 mt-2 rounded-3 border" id="passwordRequirements" aria-live="polite">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-semibold text-secondary" style="font-size: 0.78rem; letter-spacing: 0.3px;">PASSWORD REQUIREMENTS</span>
                        <span id="requirementsBadge" class="badge bg-secondary-subtle text-secondary" style="font-size: 0.72rem;">0 of 5 met</span>
                    </div>
                    <ul class="list-unstyled mb-0" style="font-size: 0.8rem;" id="requirementsList">
                        <li class="req-item text-muted mb-1 d-flex align-items-center gap-2" id="req-length" data-satisfied="false">
                            <i class="bi bi-circle req-icon text-muted"></i>
                            <span class="req-text">At least 8 characters long</span>
                        </li>
                        <li class="req-item text-muted mb-1 d-flex align-items-center gap-2" id="req-uppercase" data-satisfied="false">
                            <i class="bi bi-circle req-icon text-muted"></i>
                            <span class="req-text">Contains an uppercase letter</span>
                        </li>
                        <li class="req-item text-muted mb-1 d-flex align-items-center gap-2" id="req-lowercase" data-satisfied="false">
                            <i class="bi bi-circle req-icon text-muted"></i>
                            <span class="req-text">Contains a lowercase letter</span>
                        </li>
                        <li class="req-item text-muted mb-1 d-flex align-items-center gap-2" id="req-number" data-satisfied="false">
                            <i class="bi bi-circle req-icon text-muted"></i>
                            <span class="req-text">Contains a number</span>
                        </li>
                        <li class="req-item text-muted d-flex align-items-center gap-2" id="req-special" data-satisfied="false">
                            <i class="bi bi-circle req-icon text-muted"></i>
                            <span class="req-text">Contains a special character</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="confirm_password" class="form-label fw-semibold small text-navy">Confirm Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                    <input
                        type="password"
                        class="form-control border-start-0 border-end-0 ps-0"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Re-enter your password"
                        required
                        autocomplete="new-password"
                        aria-describedby="confirmFeedback"
                    >
                    <button type="button" class="btn btn-light border border-start-0 text-muted toggle-password-btn" data-target="confirm_password" tabindex="-1" title="Toggle password visibility" aria-label="Toggle password visibility">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <!-- Confirmation Validation Indicator -->
                <div id="confirmFeedback" class="mt-1" style="font-size: 0.8rem; min-height: 20px;" aria-live="polite"></div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-navy py-2-5 fw-semibold shadow-sm" id="submitBtn">
                    <i class="bi bi-person-check-fill me-1"></i> Register Account
                </button>
            </div>

            <div class="text-center pt-3 border-top">
                <span class="text-muted small">Already have an account?</span>
                <a href="<?= BASE_URL ?>/index.php?action=login" class="text-navy small fw-bold text-decoration-none ms-1">
                    Sign In
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const registerForm = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    const requirementsBadge = document.getElementById('requirementsBadge');
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
        const pwd = passwordInput ? passwordInput.value : '';
        let metCount = 0;
        const total = 5;

        for (const key in reqs) {
            const req = reqs[key];
            if (!req.el) continue;

            const satisfied = req.test(pwd);
            if (satisfied) metCount++;

            req.el.dataset.satisfied = satisfied ? 'true' : 'false';
            const icon = req.el.querySelector('.req-icon');

            if (satisfied) {
                req.el.classList.remove('text-muted');
                req.el.classList.add('text-success', 'fw-medium');
                if (icon) {
                    icon.className = 'bi bi-check-circle-fill req-icon text-success';
                }
            } else {
                req.el.classList.remove('text-success', 'fw-medium');
                req.el.classList.add('text-muted');
                if (icon) {
                    icon.className = 'bi bi-circle req-icon text-muted';
                }
            }
        }

        if (requirementsBadge) {
            if (metCount === total) {
                requirementsBadge.textContent = 'All requirements met';
                requirementsBadge.className = 'badge bg-success text-white fw-semibold';
            } else if (metCount > 0) {
                requirementsBadge.textContent = metCount + ' of ' + total + ' met';
                requirementsBadge.className = 'badge bg-warning-subtle text-warning-emphasis fw-semibold';
            } else {
                requirementsBadge.textContent = '0 of 5 met';
                requirementsBadge.className = 'badge bg-secondary-subtle text-secondary fw-normal';
            }
        }

        if (confirmInput && confirmInput.value !== '') {
            validateConfirmation();
        }

        return metCount === total;
    }

    function validateConfirmation() {
        const pwd = passwordInput ? passwordInput.value : '';
        const confirm = confirmInput ? confirmInput.value : '';

        if (!confirmFeedback) return false;

        if (confirm === '') {
            confirmFeedback.innerHTML = '';
            if (confirmInput) {
                confirmInput.classList.remove('is-valid', 'is-invalid');
            }
            return false;
        }

        if (confirm === pwd) {
            confirmFeedback.innerHTML = '<span class="text-success d-flex align-items-center gap-1"><i class="bi bi-check-circle-fill"></i> Passwords match.</span>';
            if (confirmInput) {
                confirmInput.classList.remove('is-invalid');
                confirmInput.classList.add('is-valid');
            }
            return true;
        } else {
            confirmFeedback.innerHTML = '<span class="text-danger d-flex align-items-center gap-1"><i class="bi bi-x-circle-fill"></i> Passwords do not match.</span>';
            if (confirmInput) {
                confirmInput.classList.remove('is-valid');
                confirmInput.classList.add('is-invalid');
            }
            return false;
        }
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', validatePassword);
    }

    if (confirmInput) {
        confirmInput.addEventListener('input', validateConfirmation);
    }

    // Toggle Password Visibility
    document.querySelectorAll('.toggle-password-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;

            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
                this.setAttribute('title', 'Hide password');
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
                this.setAttribute('title', 'Show password');
            }
        });
    });

    // Form Submission Interception
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const isPasswordValid = validatePassword();
            const isConfirmValid = validateConfirmation();

            if (!isPasswordValid) {
                e.preventDefault();
                if (passwordInput) {
                    passwordInput.focus();
                }
                return false;
            }

            if (!isConfirmValid) {
                e.preventDefault();
                if (confirmInput) {
                    confirmInput.focus();
                }
                return false;
            }

            // Prevent duplicate submissions:
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating account...';
            }
        });
    }
});
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
