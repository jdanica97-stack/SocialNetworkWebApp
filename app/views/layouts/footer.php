<?php
// app/views/layouts/footer.php
// Reusable Modern HTML Footer Layout
?>
        <!-- Closing Page Content Container -->
        </main>

        <!-- Modern Footer -->
        <footer class="bg-white border-top py-3 px-4 text-muted small text-start">
            &copy; <?= date('Y'); ?> <strong>SMCC Connect</strong>. All rights reserved.
        </footer>

    <!-- Closing Main Content Area Wrapper -->
    </div>

<!-- Closing App Layout Wrapper -->
</div>

<!-- Cropper.js Script (Image Crop/Adjust) -->
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

<!-- Bootstrap 5.3 Bundle with Popper JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Theme Script loaded in header.php -->
<!-- AJAX Interactions Script (Likes, Comments, Post Creation) -->
<script src="<?= BASE_URL ?>/public/assets/js/interactions.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/interactions.js') ?>"></script>

<!-- Keyboard Event Handlers: Quick Search Shortcut (Cmd+K / Ctrl+K) -->
<script>
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
        const searchInput = document.querySelector('.header-search input[name="q"]');
        if (searchInput) {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    }
});
</script>

<!-- Image Crop/Adjust Modal -->
<div class="modal fade" id="imageCropModal" tabindex="-1" aria-labelledby="imageCropModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-crop text-primary fs-5"></i>
                    <h6 class="modal-title fw-bold mb-0" id="imageCropModalLabel">Adjust Photo</h6>
                </div>
                <button type="button" class="btn-close" id="cropCancelBtn" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="background: #1a1a2e;">
                <div style="width: 100%; max-height: 420px; overflow: hidden;">
                    <img id="cropperImage" src="" alt="Crop preview" style="display: block; max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-3 d-flex justify-content-between">
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="cropRotateLeft" title="Rotate left">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="cropRotateRight" title="Rotate right">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="cropFlipH" title="Flip horizontal">
                        <i class="bi bi-symmetry-vertical"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="cropReset" title="Reset">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="cropSkipBtn">
                        Skip
                    </button>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-semibold" id="cropApplyBtn">
                        <i class="bi bi-check-lg me-1"></i>Apply
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Cropper modal overrides */
#imageCropModal .cropper-view-box,
#imageCropModal .cropper-face {
    border-radius: 0;
}
#imageCropModal .cropper-line,
#imageCropModal .cropper-point {
    background-color: #2563eb;
}
#imageCropModal .modal-body {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 280px;
}
</style>

<!-- Universal Post Likers / Who Reacted Modal -->
<div class="modal fade" id="postLikersModal" tabindex="-1" aria-labelledby="postLikersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 460px;">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-3 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger" style="width: 32px; height: 32px;">
                        <i class="bi bi-heart-fill fs-6"></i>
                    </span>
                    <h6 class="modal-title fw-bold mb-0" id="postLikersModalLabel">People who reacted</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="postLikersModalBody" style="min-height: 180px; max-height: 420px; overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border spinner-border-sm text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted small mt-2 mb-0">Loading reactions...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Universal Image Lightbox Modal -->
<div class="modal fade" id="postImageLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-black border-0">
            <div class="modal-header border-0 py-3 px-4 d-flex justify-content-between align-items-center position-absolute top-0 start-0 end-0" style="z-index: 1060; background: linear-gradient(180deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%);">
                <span class="text-white small fw-semibold" id="lightboxCounter">Photo 1 of 1</span>
                <button type="button" class="btn btn-sm btn-outline-light rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" data-bs-dismiss="modal" aria-label="Close" style="width: 38px; height: 38px;">
                    <i class="bi bi-x-lg fs-6"></i>
                </button>
            </div>
            <div class="modal-body p-0 d-flex align-items-center justify-content-center position-relative w-100 h-100" style="background: rgba(0, 0, 0, 0.96); overflow: hidden;">
                <img id="lightboxImage" src="" alt="Enlarged photo" style="max-height: 92vh; max-width: 96vw; width: auto; height: auto; object-fit: contain; border-radius: 6px; box-shadow: 0 10px 40px rgba(0,0,0,0.85);">
                <button type="button" class="btn btn-dark bg-opacity-75 text-white position-absolute start-0 top-50 translate-middle-y ms-3 rounded-circle border-0 d-none d-flex align-items-center justify-content-center shadow" id="lightboxPrevBtn" style="width: 52px; height: 52px; z-index: 1060;" aria-label="Previous photo">
                    <i class="bi bi-chevron-left fs-3"></i>
                </button>
                <button type="button" class="btn btn-dark bg-opacity-75 text-white position-absolute end-0 top-50 translate-middle-y me-3 rounded-circle border-0 d-none d-flex align-items-center justify-content-center shadow" id="lightboxNextBtn" style="width: 52px; height: 52px; z-index: 1060;" aria-label="Next photo">
                    <i class="bi bi-chevron-right fs-3"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Universal Lightbox Handler
document.addEventListener('DOMContentLoaded', function() {
    let currentGalleryLinks = [];
    let currentGalleryIndex = 0;
    const lightboxModalEl = document.getElementById('postImageLightboxModal');
    if (!lightboxModalEl) return;
    const lightboxModal = new bootstrap.Modal(lightboxModalEl);
    const lightboxImg = document.getElementById('lightboxImage');
    const lightboxCounter = document.getElementById('lightboxCounter');
    const prevBtn = document.getElementById('lightboxPrevBtn');
    const nextBtn = document.getElementById('lightboxNextBtn');

    function showLightboxIndex(index) {
        if (!currentGalleryLinks.length) return;
        if (index < 0) index = currentGalleryLinks.length - 1;
        if (index >= currentGalleryLinks.length) index = 0;
        currentGalleryIndex = index;

        const targetLink = currentGalleryLinks[currentGalleryIndex];
        const src = targetLink.getAttribute('href');
        lightboxImg.src = src;

        if (currentGalleryLinks.length > 1) {
            lightboxCounter.textContent = `Photo ${currentGalleryIndex + 1} of ${currentGalleryLinks.length}`;
            prevBtn.classList.remove('d-none');
            nextBtn.classList.remove('d-none');
        } else {
            lightboxCounter.textContent = `Photo 1 of 1`;
            prevBtn.classList.add('d-none');
            nextBtn.classList.add('d-none');
        }
    }

    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.post-lightbox-trigger');
        if (!trigger) return;
        e.preventDefault();

        const galleryId = trigger.getAttribute('data-gallery');
        if (galleryId) {
            currentGalleryLinks = Array.from(document.querySelectorAll(`.post-lightbox-trigger[data-gallery="${galleryId}"]`));
        } else {
            currentGalleryLinks = [trigger];
        }

        currentGalleryIndex = currentGalleryLinks.indexOf(trigger);
        if (currentGalleryIndex === -1) currentGalleryIndex = 0;

        showLightboxIndex(currentGalleryIndex);
        lightboxModal.show();
    });

    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            showLightboxIndex(currentGalleryIndex - 1);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            showLightboxIndex(currentGalleryIndex + 1);
        });
    }

    document.addEventListener('keydown', function(e) {
        if (!lightboxModalEl.classList.contains('show')) return;
        if (e.key === 'ArrowLeft') {
            showLightboxIndex(currentGalleryIndex - 1);
        } else if (e.key === 'ArrowRight') {
            showLightboxIndex(currentGalleryIndex + 1);
        }
    });
});
</script>

</body>
</html>
