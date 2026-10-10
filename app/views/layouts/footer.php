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
<script src="<?= BASE_URL ?>/public/assets/js/interactions.js"></script>

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

</body>
</html>
