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

                <!-- Existing Attachments (if any) -->
                <?php
                $currentImages = !empty($post['images']) ? $post['images'] : PostModel::parseImages($post['image'] ?? null);
                ?>
                <?php if (!empty($currentImages)): ?>
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-semibold text-secondary small mb-0">
                                Current Attachments (<?= count($currentImages); ?>)
                            </label>
                            <div class="form-check text-danger mb-0">
                                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_all_images" value="1" onchange="toggleRemoveAllImages(this)">
                                <label class="form-check-label small fw-semibold" for="remove_all_images">
                                    Remove all
                                </label>
                            </div>
                        </div>

                        <div class="row g-2" id="existingImagesList">
                            <?php foreach ($currentImages as $cIdx => $cImg): ?>
                                <div class="col-6 col-sm-4 col-md-3 existing-image-item">
                                    <div class="card h-100 border shadow-sm position-relative overflow-hidden">
                                        <img 
                                            src="<?= BASE_URL ?>/public/uploads/posts/<?= htmlspecialchars(basename($cImg)); ?>" 
                                            alt="Attachment <?= $cIdx + 1 ?>" 
                                            style="height: 100px; width: 100%; object-fit: cover;"
                                            onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'p-2 small text-muted\'>Attachment</span>';"
                                        >
                                        <div class="card-footer p-1 bg-white border-top text-center">
                                            <div class="form-check form-check-inline m-0">
                                                <input class="form-check-input remove-single-image" type="checkbox" name="remove_images[]" id="remove_img_<?= $cIdx ?>" value="<?= htmlspecialchars($cImg); ?>">
                                                <label class="form-check-label micro-text text-danger" for="remove_img_<?= $cIdx ?>">
                                                    Delete
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted d-block mt-2">Check the "Delete" box on any photo you want to remove.</small>
                    </div>
                <?php endif; ?>

                <!-- Upload New Photos -->
                <div class="mb-4">
                    <label for="postImageInput" class="form-label fw-semibold text-secondary small">
                        <?= !empty($currentImages) ? 'Add More Photos (Optional)' : 'Add Photos (Optional)'; ?>
                    </label>
                    <input 
                        type="file" 
                        id="postImageInput" 
                        name="post_images[]" 
                        accept="image/jpeg,image/png,image/gif,image/webp" 
                        class="form-control"
                        multiple
                        onchange="previewNewImages(this)"
                    >
                    <div class="form-text small text-muted">
                        Supported: JPG, JPEG, PNG, GIF, WEBP. Max size: 5MB per image. Select multiple files if desired.
                    </div>

                    <!-- New Images Multi-Preview Box -->
                    <div id="newImagePreviewContainer" class="mt-3 d-none">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <small class="text-success fw-semibold"><i class="bi bi-eye me-1"></i> New Photos Preview:</small>
                            <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 small" onclick="clearNewImages()">
                                Clear new
                            </button>
                        </div>
                        <div id="newImagePreviewGrid" class="d-flex flex-wrap gap-2"></div>
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
let editFilesDataTransfer = new DataTransfer();

function previewNewImages(input) {
    if (!input.files || input.files.length === 0) return;

    for (let i = 0; i < input.files.length; i++) {
        if (editFilesDataTransfer.items.length >= 10) {
            alert('You can upload a maximum of 10 photos per post.');
            break;
        }
        editFilesDataTransfer.items.add(input.files[i]);
    }

    input.files = editFilesDataTransfer.files;
    renderEditPreviews();
}

function removeEditImage(index) {
    const newDt = new DataTransfer();
    const files = editFilesDataTransfer.files;
    for (let i = 0; i < files.length; i++) {
        if (i !== index) {
            newDt.items.add(files[i]);
        }
    }
    editFilesDataTransfer = newDt;
    const input = document.getElementById('postImageInput');
    if (input) input.files = editFilesDataTransfer.files;
    renderEditPreviews();
}

function clearNewImages() {
    editFilesDataTransfer = new DataTransfer();
    const input = document.getElementById('postImageInput');
    if (input) input.value = '';
    renderEditPreviews();
}

function renderEditPreviews() {
    const container = document.getElementById('newImagePreviewContainer');
    const grid = document.getElementById('newImagePreviewGrid');
    if (!container || !grid) return;

    const files = editFilesDataTransfer.files;
    if (files.length === 0) {
        container.classList.add('d-none');
        grid.innerHTML = '';
        return;
    }

    container.classList.remove('d-none');
    grid.innerHTML = '';

    Array.from(files).forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const card = document.createElement('div');
            card.className = 'position-relative d-inline-block';
            card.style.margin = '4px';
            card.innerHTML = `
                <img src="${e.target.result}" alt="Preview" style="width: 76px; height: 76px; object-fit: cover; border-radius: 8px;" class="border shadow-sm">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle p-0" style="width: 20px; height: 20px; line-height: 18px; transform: translate(30%, -30%); font-size: 13px;" onclick="removeEditImage(${idx})" title="Remove photo">
                    &times;
                </button>
            `;
            grid.appendChild(card);
        };
        reader.readAsDataURL(file);
    });
}

function toggleRemoveAllImages(master) {
    const checkboxes = document.querySelectorAll('.remove-single-image');
    checkboxes.forEach(cb => {
        cb.checked = master.checked;
    });
}
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
