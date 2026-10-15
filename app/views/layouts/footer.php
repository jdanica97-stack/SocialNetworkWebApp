<?php
// app/views/layouts/footer.php
// Reusable Modern HTML Footer Layout
?>
        <!-- Closing Page Content Container -->
        </main>

        <!-- Modern Footer -->
        <footer class="bg-white border-top py-3 px-4 text-muted small d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <div>
                &copy; <?= date('Y'); ?> <strong>SMCC Connect</strong>. All rights reserved.
            </div>
            <div class="d-flex gap-3">
                <a href="#" class="text-muted text-decoration-none">Privacy Policy</a>
                <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
                <a href="#" class="text-muted text-decoration-none">Help & Support</a>
            </div>
        </footer>

    <!-- Closing Main Content Area Wrapper -->
    </div>

<!-- Closing App Layout Wrapper -->
</div>

<!-- Bootstrap 5.3 Bundle with Popper JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Shared Theme Script (Dark Mode / Light Mode) -->
<script src="<?= BASE_URL ?>/public/assets/js/theme.js"></script>
<!-- AJAX Interactions Script (Likes, Comments, Post Creation) -->
<script src="<?= BASE_URL ?>/public/assets/js/interactions.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/interactions.js') ?>"></script>

<!-- Sidebar Toggle & Keyboard Event Handlers -->
<script>
function toggleSidebar(e) {
    if (e && e.preventDefault) {
        e.preventDefault();
        if (e.stopPropagation) e.stopPropagation();
    }
    const sidebar = document.getElementById('appSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (sidebar && backdrop) {
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('show');
    }
}

// Keyboard Listeners: Close side panel on Escape, focus search on Cmd+K / Ctrl+K
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const sidebar = document.getElementById('appSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (sidebar && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            if (backdrop) backdrop.classList.remove('show');
        }
    } else if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
        const searchInput = document.querySelector('.header-search input[name="q"]');
        if (searchInput) {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    }
});
</script>

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
