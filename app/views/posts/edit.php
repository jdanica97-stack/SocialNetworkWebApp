<?php
// app/views/posts/edit.php
// Edit Post View matching the application's clean Bootstrap design

require_once __DIR__ . '/../layouts/header.php';

$authorParts = explode(' ', trim($post['full_name']));
$authorInitials = count($authorParts) >= 2
    ? strtoupper(mb_substr($authorParts[0], 0, 1) . mb_substr($authorParts[count($authorParts)-1], 0, 1))
    : strtoupper(mb_substr($post['full_name'], 0, 2));
?>

<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        <!-- Flash Error Notifications -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Please check the errors below:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card-modern p-4 p-md-5 my-2">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="brand-icon-box" style="width: 40px; height: 40px; font-size: 1.25rem;">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">Edit Post</h4>
                        <p class="text-muted small mb-0">Modify your post content or attachment</p>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
            </div>

            <!-- Author Preview Snippet -->
            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-4 border border-light-subtle">
                <?php if (!empty($post['profile_image'])): ?>
                    <img 
                        src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars(basename($post['profile_image'])) ?>" 
                        alt="<?= htmlspecialchars($post['full_name']); ?>" 
                        class="rounded-circle"
                        style="width: 42px; height: 42px; object-fit: cover;"
                        onerror="this.onerror=null; this.outerHTML='<div class=\'user-avatar-sm\' style=\'width: 42px; height: 42px;\'><?= htmlspecialchars($authorInitials) ?></div>';"
                    >
                <?php else: ?>
                    <div class="user-avatar-sm" style="width: 42px; height: 42px; font-size: 0.95rem;">
                        <?= htmlspecialchars($authorInitials) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($post['full_name']); ?></span>
                    <small class="text-muted">
                        @<?= htmlspecialchars($post['username']); ?> &bull; Originally posted on <?= date('M j, Y \a\t g:i a', strtotime($post['created_at'])); ?>
                    </small>
                </div>
            </div>

            <!-- Edit Form -->
            <form action="<?= BASE_URL ?>/index.php?action=update_post" method="POST" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="post_id" value="<?= (int)$post['id']; ?>">

                <!-- Content Textarea -->
                <div class="mb-4">
                    <label for="postContentInput" class="form-label fw-semibold text-secondary small">
                        Post Content <span class="text-muted fw-normal">(required if no image)</span>
                    </label>
                    <textarea 
                        id="postContentInput"
                        name="content" 
                        class="form-control" 
                        rows="5" 
                        placeholder="What would you like to share?"
                        style="border-radius: 10px; font-size: 0.95rem;"
                    ><?= htmlspecialchars($formData['content'] ?? $post['content']); ?></textarea>
                </div>

                <!-- Existing Image (if any) -->
                <?php if (!empty($post['image'])): ?>
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-semibold text-secondary small d-block">Current Attachment</label>
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                            <div class="position-relative d-inline-block">
                                <img 
                                    src="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($post['image'])); ?>" 
                                    alt="Current attachment" 
                                    class="rounded-3 border shadow-sm"
                                    style="max-height: 140px; max-width: 200px; object-fit: cover;"
                                    onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'badge bg-secondary\'>Attached: <?= htmlspecialchars(basename($post['image'])); ?></span>';"
                                >
                            </div>
                            <div>
                                <div class="form-check text-danger">
                                    <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                                    <label class="form-check-label fw-semibold small" for="remove_image">
                                        <i class="bi bi-trash3 me-1"></i> Remove current image
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1">Check this box to delete the image from this post.</small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Upload New / Replace Image -->
                <div class="mb-4">
                    <label for="postImageInput" class="form-label fw-semibold text-secondary small">
                        <?= !empty($post['image']) ? 'Replace Image (Optional)' : 'Add Image (Optional)'; ?>
                    </label>
                    <input 
                        type="file" 
                        id="postImageInput" 
                        name="post_image" 
                        accept="image/jpeg,image/png,image/gif,image/webp" 
                        class="form-control"
                        onchange="previewNewImage(this)"
                    >
                    <div class="form-text small text-muted">
                        Supported: JPG, JPEG, PNG, GIF, WEBP. Max size: 5MB.
                    </div>

                    <!-- New Image Preview Box -->
                    <div id="newImagePreviewContainer" class="mt-3 d-none">
                        <small class="text-success fw-semibold d-block mb-1"><i class="bi bi-eye me-1"></i> New Image Preview:</small>
                        <div class="position-relative d-inline-block">
                            <img id="newImagePreview" src="#" alt="New Preview" style="max-height: 140px; border-radius: 8px;" class="border shadow-sm">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-0" style="width: 22px; height: 22px; line-height: 20px;" onclick="clearNewImage()">
                                &times;
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                    <a href="<?= BASE_URL ?>/" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left me-1"></i> Back to Feed
                    </a>
                    <button type="submit" class="btn btn-accent px-4 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
function previewNewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('newImagePreview');
            const container = document.getElementById('newImagePreviewContainer');
            if (preview && container) {
                preview.src = e.target.result;
                container.classList.remove('d-none');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearNewImage() {
    const input = document.getElementById('postImageInput');
    const container = document.getElementById('newImagePreviewContainer');
    if (input) input.value = '';
    if (container) container.classList.add('d-none');
}
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
