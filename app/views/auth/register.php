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

        <form action="<?= BASE_URL ?>/index.php?action=register" method="POST" novalidate>
            <?= csrf_field(); ?>
            <!-- Full Name -->
            <div class="mb-3">
                <label for="full_name" class="form-label fw-semibold small text-navy">Full Name</label>
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
                <label for="username" class="form-label fw-semibold small text-navy">Username</label>
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
                <label for="password" class="form-label fw-semibold small text-navy">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                    <input
                        type="password"
                        class="form-control border-start-0 ps-0"
                        id="password"
                        name="password"
                        placeholder="At least 6 characters"
                        required
                    >
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="confirm_password" class="form-label fw-semibold small text-navy">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                    <input
                        type="password"
                        class="form-control border-start-0 ps-0"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Re-enter your password"
                        required
                    >
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-navy py-2-5 fw-semibold shadow-sm">
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

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
