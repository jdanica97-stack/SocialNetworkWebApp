<?php
// app/views/profile/edit.php
// Edit Profile View - Redesigned with Modern Dashboard Form Styling & Photo Adjuster

require_once __DIR__ . '/../layouts/header.php';

// Prepare profile image URL or fallback
$avatarUrl = null;
if (!empty($user['profile_image'])) {
    $uploadedPath = __DIR__ . '/../../../public/uploads/avatars/' . basename($user['profile_image']);
    if (file_exists($uploadedPath)) {
        $avatarUrl = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($user['profile_image']));
    } else {
        $avatarUrl = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars($user['profile_image']);
    }
}

// Prepare cover photo URL or fallback
$coverUrl = null;
if (!empty($user['cover_photo'])) {
    $uploadedCoverPath = __DIR__ . '/../../../public/uploads/covers/' . basename($user['cover_photo']);
    if (file_exists($uploadedCoverPath)) {
        $coverUrl = BASE_URL . '/public/uploads/covers/' . htmlspecialchars(basename($user['cover_photo']));
    } else {
        $coverUrl = BASE_URL . '/public/uploads/covers/' . htmlspecialchars($user['cover_photo']);
    }
}

$userInitials = 'U';
if (!empty($user['full_name'])) {
    $parts = explode(' ', trim($user['full_name']));
    if (count($parts) >= 2) {
        $userInitials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1));
    } else {
        $userInitials = strtoupper(mb_substr($user['full_name'], 0, 2));
    }
}
?>

<!-- Cropper.js Stylesheet -->
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/cropper.min.css">

<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
        <div class="card-modern p-4 p-md-5 my-2">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <div>
                    <h3 class="fw-bold mb-1 text-dark">Edit Profile</h3>
                    <p class="text-muted small mb-0">Update your public profile information, bio, and photos</p>
                </div>
                <span class="badge" style="background-color: #f1f5f9; color: #475569; font-weight: 600; padding: 0.5rem 0.85rem; border-radius: 20px;">
                    @<?= htmlspecialchars($user['username']); ?>
                </span>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Please review the errors below:</strong>
                    <ul class="mb-0 mt-2 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/index.php?action=edit_profile" method="POST" enctype="multipart/form-data" id="profileEditForm" novalidate>
                <?= csrf_field(); ?>
                
                <!-- Hidden input for adjusted/cropped image base64 -->
                <input type="hidden" name="cropped_image_data" id="cropped_image_data" value="">

                <!-- Avatar Preview & Upload Area -->
                <div class="mb-4 p-3 bg-light rounded-3 border border-light-subtle avatar-edit-container" id="avatarDropZone">
                    <div class="d-flex flex-column flex-sm-row align-items-center gap-4">
                        <!-- Avatar Display Container -->
                        <div class="flex-shrink-0 text-center position-relative">
                            <div id="avatarPreviewWrapper" class="position-relative d-inline-block">
                                <?php if ($avatarUrl): ?>
                                    <img 
                                        src="<?= $avatarUrl ?>" 
                                        alt="Current Avatar" 
                                        id="avatarPreview"
                                        class="rounded-circle border border-3 border-white shadow-sm"
                                        style="width: 88px; height: 88px; object-fit: cover;"
                                        onerror="this.onerror=null; this.outerHTML='<div id=\'avatarPreviewPlaceholder\' class=\'rounded-circle border border-3 border-white shadow-sm text-white d-inline-flex align-items-center justify-content-center fs-3 fw-bold\' style=\'width: 88px; height: 88px; background-color: #07193d;\'><?= htmlspecialchars($userInitials) ?></div>';"
                                    >
                                <?php else: ?>
                                    <div 
                                        id="avatarPreviewPlaceholder"
                                        class="rounded-circle border border-3 border-white shadow-sm text-white d-inline-flex align-items-center justify-content-center fs-3 fw-bold"
                                        style="width: 88px; height: 88px; background-color: #07193d;"
                                    >
                                        <?= htmlspecialchars($userInitials); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Avatar Actions & Controls -->
                        <div class="flex-grow-1 w-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-semibold mb-0 small text-secondary">Profile Picture</label>
                                <span class="badge bg-secondary-subtle text-secondary small">1:1 Square/Circle</span>
                            </div>

                            <p class="small text-muted mb-2">
                                Upload a photo and adjust (zoom, reposition, or rotate) to fit perfectly.
                            </p>

                            <!-- Buttons Row -->
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <label for="profile_image" class="btn btn-sm btn-outline-primary fw-semibold cursor-pointer mb-0">
                                    <i class="bi bi-camera me-1"></i> Choose New Photo
                                </label>
                                <input 
                                    type="file" 
                                    class="d-none" 
                                    id="profile_image" 
                                    name="profile_image" 
                                    accept="image/jpeg,image/png,image/gif,image/webp"
                                >

                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-outline-secondary fw-semibold <?= empty($avatarUrl) ? 'd-none' : '' ?>" 
                                    id="btnOpenAdjustModal" 
                                    title="Adjust and crop photo position"
                                >
                                    <i class="bi bi-crop me-1"></i> Adjust Photo
                                </button>
                            </div>

                            <!-- Success / Status alert when image adjusted -->
                            <div id="cropStatusAlert" class="d-none alert alert-success py-2 px-3 small border-0 mb-2 d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                                <div>Photo adjusted! Click <strong>Save Changes</strong> below to save.</div>
                            </div>

                            <div class="form-text small text-muted">
                                Supported formats: JPG, PNG, WEBP, GIF. Max file size: 5MB.
                            </div>

                            <?php if (!empty($user['profile_image'])): ?>
                                <div class="form-check mt-2 pt-1 border-top border-light-subtle">
                                    <input class="form-check-input" type="checkbox" name="remove_picture" id="remove_picture" value="1">
                                    <label class="form-check-label text-danger small" for="remove_picture">
                                        Remove current picture
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Cover Photo Preview & Upload Area -->
                <div class="mb-4 p-3 bg-light rounded-3 border border-light-subtle">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label fw-semibold mb-0 small text-secondary">Profile Banner / Cover Photo</label>
                        <span class="badge bg-secondary-subtle text-secondary small">Banner (16:9 / 3:1)</span>
                    </div>
                    
                    <div class="rounded-3 overflow-hidden position-relative mb-3 border" style="height: 140px; <?= $coverUrl ? "background: url('" . $coverUrl . "') center center / cover no-repeat;" : "background: linear-gradient(135deg, #07193d 0%, #1e3a8a 100%);" ?>" id="coverPreviewContainer">
                        <div class="position-absolute bottom-0 end-0 m-2">
                            <label for="cover_photo" class="btn btn-sm btn-light shadow-sm fw-semibold mb-0 cursor-pointer">
                                <i class="bi bi-camera me-1"></i> Change Cover Photo
                            </label>
                            <input 
                                type="file" 
                                class="d-none" 
                                id="cover_photo" 
                                name="cover_photo" 
                                accept="image/jpeg,image/png,image/gif,image/webp"
                            >
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="form-text small text-muted">
                            Recommended size: 1200x400. Max file size: 5MB (JPG, PNG, WEBP).
                        </div>
                        <?php if (!empty($user['cover_photo'])): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_cover" id="remove_cover" value="1">
                                <label class="form-check-label text-danger small" for="remove_cover">
                                    Remove cover photo
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bio -->
                <div class="mb-4">
                    <label for="bio" class="form-label fw-semibold small text-secondary">Bio</label>
                    <textarea 
                        class="form-control" 
                        id="bio" 
                        name="bio" 
                        rows="3" 
                        maxlength="500" 
                        placeholder="Write a few lines about yourself, your department, hobbies..."
                    ><?= htmlspecialchars($formData['bio'] ?? ''); ?></textarea>
                    <div class="form-text small text-muted text-end">Maximum 500 characters</div>
                </div>

                <!-- Form Action Buttons -->
                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                    <a href="<?= BASE_URL ?>/index.php?action=profile" class="btn btn-outline-secondary px-4">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-accent px-4 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================
     MODAL: ADJUST / CROP PROFILE PICTURE
     ============================================== -->
<div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="cropModalLabel">
                    <i class="bi bi-crop text-primary"></i>
                    <span>Adjust Profile Picture</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    Drag the photo to reposition your face inside the circle. Use the slider or mouse wheel / pinch gestures to zoom in or out.
                </p>

                <div class="row g-4 align-items-center">
                    <!-- Main Cropper Viewport -->
                    <div class="col-md-8">
                        <div class="cropper-wrapper">
                            <img id="cropperImageTarget" src="" alt="Adjust Avatar" style="max-width: 100%;">
                        </div>
                    </div>

                    <!-- Live Circular Preview & Guidance -->
                    <div class="col-md-4 text-center">
                        <label class="form-label fw-semibold text-secondary small mb-2 d-block">Live Preview</label>
                        <div class="d-flex justify-content-center mb-3">
                            <div class="avatar-preview-box border border-3 border-white">
                                <div class="avatar-crop-preview"></div>
                            </div>
                        </div>
                        <span class="badge bg-light text-secondary border small mb-3">Circular Avatar</span>

                        <div class="small text-muted text-start bg-light p-3 rounded-3 border">
                            <div class="mb-1"><i class="bi bi-arrows-move me-1 text-primary"></i> <strong>Drag</strong> to move photo</div>
                            <div class="mb-1"><i class="bi bi-zoom-in me-1 text-primary"></i> <strong>Scroll / Slider</strong> to zoom</div>
                            <div><i class="bi bi-arrow-repeat me-1 text-primary"></i> <strong>Rotate</strong> if crooked</div>
                        </div>
                    </div>
                </div>

                <!-- Adjustment Controls Toolbar -->
                <div class="mt-4 pt-3 border-top">
                    <div class="row g-3 align-items-center">
                        <!-- Zoom Slider & Controls -->
                        <div class="col-lg-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="zoomSlider" class="form-label small fw-semibold text-secondary mb-0">
                                    <i class="bi bi-zoom-in me-1"></i> Zoom Level
                                </label>
                                <span id="zoomPercentText" class="badge bg-light text-dark border small">100%</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnZoomOut" title="Zoom Out">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <input type="range" class="form-range flex-grow-1" id="zoomSlider" min="0.2" max="3" step="0.02" value="1">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnZoomIn" title="Zoom In">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Rotate, Flip & Reset Controls -->
                        <div class="col-lg-6 d-flex flex-wrap align-items-center justify-content-lg-end gap-2 pt-2 pt-lg-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnRotateCounterClockwise" title="Rotate Counter-Clockwise 90°">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> -90°
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnRotateClockwise" title="Rotate Clockwise 90°">
                                <i class="bi bi-arrow-clockwise me-1"></i> +90°
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnFlipHorizontal" title="Flip Horizontal">
                                <i class="bi bi-symmetry-vertical me-1"></i> Flip
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetAdjust" title="Reset image adjustment">
                                <i class="bi bi-arrow-repeat me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light px-4 py-3 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-accent px-4 fw-semibold" id="btnApplyCrop">
                    <i class="bi bi-check2-circle me-1"></i> Apply & Done
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cropper.js Library Script -->
<script src="<?= BASE_URL ?>/public/assets/js/cropper.min.js"></script>

<!-- Interactive Image Adjustment Controller Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('profile_image');
    const croppedDataInput = document.getElementById('cropped_image_data');
    const btnOpenAdjustModal = document.getElementById('btnOpenAdjustModal');
    const cropModalEl = document.getElementById('cropModal');
    const cropModal = new bootstrap.Modal(cropModalEl);
    const cropperImageTarget = document.getElementById('cropperImageTarget');
    const btnApplyCrop = document.getElementById('btnApplyCrop');
    const cropStatusAlert = document.getElementById('cropStatusAlert');
    const removePictureCheck = document.getElementById('remove_picture');

    // Toolbar elements
    const zoomSlider = document.getElementById('zoomSlider');
    const zoomPercentText = document.getElementById('zoomPercentText');
    const btnZoomIn = document.getElementById('btnZoomIn');
    const btnZoomOut = document.getElementById('btnZoomOut');
    const btnRotateCounterClockwise = document.getElementById('btnRotateCounterClockwise');
    const btnRotateClockwise = document.getElementById('btnRotateClockwise');
    const btnFlipHorizontal = document.getElementById('btnFlipHorizontal');
    const btnResetAdjust = document.getElementById('btnResetAdjust');

    let cropper = null;
    let baseZoomRatio = 1;
    let flipScaleX = 1;
    let currentImageSrc = "<?= $avatarUrl ? addslashes($avatarUrl) : '' ?>";

    // Initialize or re-initialize Cropper instance
    function initCropper() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        cropper = new Cropper(cropperImageTarget, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.88,
            restore: false,
            guides: false,
            center: false,
            highlight: false,
            cropBoxMovable: false,
            cropBoxResizable: false,
            toggleDragModeOnDblclick: false,
            preview: '.avatar-crop-preview',
            ready: function() {
                const canvasData = cropper.getCanvasData();
                baseZoomRatio = canvasData.width / canvasData.naturalWidth;
                zoomSlider.value = 1;
                zoomPercentText.textContent = '100%';
                flipScaleX = 1;
            },
            zoom: function(e) {
                if (baseZoomRatio > 0) {
                    const currentRatio = e.detail.ratio / baseZoomRatio;
                    zoomSlider.value = Math.max(0.2, Math.min(3, currentRatio)).toFixed(2);
                    zoomPercentText.textContent = Math.round(currentRatio * 100) + '%';
                }
            }
        });
    }

    // When modal is fully shown, initialize Cropper to ensure exact DOM dimensions
    cropModalEl.addEventListener('shown.bs.modal', function() {
        initCropper();
    });

    // Clean up cropper on hide to free memory
    cropModalEl.addEventListener('hidden.bs.modal', function() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });

    // Handle File Selection from Input
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files && e.target.files[0];
        if (!file) return;

        if (!file.type.match(/^image\//)) {
            alert('Please select an image file (JPG, PNG, GIF, WEBP).');
            fileInput.value = '';
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('Selected image exceeds the 10MB limit. Please choose a smaller file.');
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(evt) {
            currentImageSrc = evt.target.result;
            cropperImageTarget.src = currentImageSrc;
            btnOpenAdjustModal.classList.remove('d-none');
            cropModal.show();
        };
        reader.readAsDataURL(file);
    });

    // Open Adjustment Modal with existing or selected image
    btnOpenAdjustModal.addEventListener('click', function() {
        if (!currentImageSrc) {
            fileInput.click();
            return;
        }
        cropperImageTarget.src = currentImageSrc;
        cropModal.show();
    });

    // Zoom Controls
    zoomSlider.addEventListener('input', function() {
        if (!cropper || !baseZoomRatio) return;
        const factor = parseFloat(this.value);
        cropper.zoomTo(baseZoomRatio * factor);
        zoomPercentText.textContent = Math.round(factor * 100) + '%';
    });

    btnZoomIn.addEventListener('click', function() {
        if (!cropper) return;
        cropper.zoom(0.1);
    });

    btnZoomOut.addEventListener('click', function() {
        if (!cropper) return;
        cropper.zoom(-0.1);
    });

    // Rotate Controls
    btnRotateCounterClockwise.addEventListener('click', function() {
        if (!cropper) return;
        cropper.rotate(-90);
    });

    btnRotateClockwise.addEventListener('click', function() {
        if (!cropper) return;
        cropper.rotate(90);
    });

    // Flip Control
    btnFlipHorizontal.addEventListener('click', function() {
        if (!cropper) return;
        flipScaleX = -flipScaleX;
        cropper.scaleX(flipScaleX);
    });

    // Reset Control
    btnResetAdjust.addEventListener('click', function() {
        if (!cropper) return;
        cropper.reset();
        flipScaleX = 1;
        zoomSlider.value = 1;
        zoomPercentText.textContent = '100%';
    });

    // Apply Adjusted / Cropped Image
    btnApplyCrop.addEventListener('click', function() {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        if (!canvas) {
            alert('Failed to process image adjustment. Please try again.');
            return;
        }

        const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.92);

        // Store base64 data for form submit
        croppedDataInput.value = croppedDataUrl;

        // Update form preview element
        const previewImg = document.getElementById('avatarPreview');
        const previewPlaceholder = document.getElementById('avatarPreviewPlaceholder');
        const wrapper = document.getElementById('avatarPreviewWrapper');

        if (previewImg) {
            previewImg.src = croppedDataUrl;
            previewImg.classList.remove('d-none');
        } else if (previewPlaceholder && wrapper) {
            const newImg = document.createElement('img');
            newImg.id = 'avatarPreview';
            newImg.src = croppedDataUrl;
            newImg.alt = 'Adjusted Avatar';
            newImg.className = 'rounded-circle border border-3 border-white shadow-sm';
            newImg.style.width = '88px';
            newImg.style.height = '88px';
            newImg.style.objectFit = 'cover';
            wrapper.replaceChild(newImg, previewPlaceholder);
        }

        // Uncheck remove picture checkbox if checked
        if (removePictureCheck) {
            removePictureCheck.checked = false;
        }

        // Show feedback alert & make sure adjust button is visible
        if (cropStatusAlert) {
            cropStatusAlert.classList.remove('d-none');
        }
        btnOpenAdjustModal.classList.remove('d-none');

        // Close modal
        cropModal.hide();
    });

    // Drag and Drop file support on avatarDropZone
    const dropZone = document.getElementById('avatarDropZone');
    if (dropZone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('border-primary');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('border-primary');
            });
        });

        dropZone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt && dt.files;
            if (files && files.length > 0) {
                fileInput.files = files;
                const changeEvent = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(changeEvent);
            }
        });
    }

    // Cover Photo Instant Preview
    const coverInput = document.getElementById('cover_photo');
    const coverContainer = document.getElementById('coverPreviewContainer');
    if (coverInput && coverContainer) {
        coverInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('Cover photo must be less than 5MB.');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    coverContainer.style.background = `url('${e.target.result}') center center / cover no-repeat`;
                    const removeCoverCheck = document.getElementById('remove_cover');
                    if (removeCoverCheck) removeCoverCheck.checked = false;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

