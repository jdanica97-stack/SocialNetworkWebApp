/**
 * SMCC Connect - AJAX Interactions (Post creation, Commenting, Liking)
 * Prevents full page reloads for seamless user experience.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Global BASE_URL fallback helper
    const baseUrl = (typeof window.BASE_URL !== 'undefined') ? window.BASE_URL : '';

    // =========================================================================
    // 1. AJAX LIKING
    // =========================================================================
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !form.action || !form.action.includes('action=like_post')) {
            return;
        }

        e.preventDefault();
        const formData = new FormData(form);
        formData.append('ajax', '1');

        // Include CSRF token from form or meta tag
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        if (csrfToken && !formData.has('csrf_token')) {
            formData.append('csrf_token', csrfToken);
        }

        // Instant UI Toggle (Optimistic Update)
        const submitBtn = form.querySelector('button[type="submit"]');
        const heartIcon = submitBtn ? submitBtn.querySelector('i') : null;
        const countSpan = submitBtn ? submitBtn.querySelector('span') : null;

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success) {
                updatePostLikesUI(data.post_id, data.is_liked, data.likes_count);
            }
        })
        .catch(err => console.error('Error toggling like:', err));
    });

    function updatePostLikesUI(postId, isLiked, likesCount) {
        const likeForms = document.querySelectorAll(`form[action*="action=like_post"] input[name="post_id"][value="${postId}"]`);
        likeForms.forEach(input => {
            const form = input.closest('form');
            if (!form) return;
            const btn = form.querySelector('button[type="submit"]');
            if (!btn) return;

            const heartIcon = btn.querySelector('i');
            const countSpan = btn.querySelector('span');

            // Handle feed style button (.post-action-btn)
            if (btn.classList.contains('post-action-btn')) {
                if (isLiked) {
                    btn.classList.add('liked');
                    if (heartIcon) heartIcon.className = 'bi bi-heart-fill';
                } else {
                    btn.classList.remove('liked');
                    if (heartIcon) heartIcon.className = 'bi bi-heart';
                }
                if (countSpan) countSpan.textContent = likesCount;
            } 
            // Handle insights / search style button (.btn-danger / .btn-outline-danger)
            else {
                if (isLiked) {
                    btn.classList.remove('btn-outline-danger');
                    btn.classList.add('btn-danger');
                    if (heartIcon) heartIcon.className = 'bi bi-heart-fill me-1';
                } else {
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-outline-danger');
                    if (heartIcon) heartIcon.className = 'bi bi-heart me-1';
                }
                btn.childNodes.forEach(node => {
                    if (node.nodeType === Node.TEXT_NODE) {
                        node.textContent = ' ' + likesCount;
                    }
                });
            }
        });
    }

    // =========================================================================
    // 2. AJAX COMMENTING
    // =========================================================================
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !form.action || (!form.action.includes('action=create_comment') && !form.action.includes('action=add_comment'))) {
            return;
        }

        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const inputField = form.querySelector('input[name="content"]');

        if (!inputField || !inputField.value.trim()) return;

        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('ajax', '1');

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        if (csrfToken && !formData.has('csrf_token')) {
            formData.append('csrf_token', csrfToken);
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            if (data && data.success) {
                inputField.value = '';
                appendCommentToPost(data.post_id, data.comment, data.comments_count);
            } else if (data && data.error) {
                alert(data.error);
            }
        })
        .catch(err => {
            if (submitBtn) submitBtn.disabled = false;
            console.error('Error posting comment:', err);
        });
    });

    function appendCommentToPost(postId, comment, totalCommentsCount) {
        const commentsContainer = document.getElementById(`comments-box-${postId}`);
        if (!commentsContainer) return;

        // If hidden (e.g. insights page), reveal it
        if (commentsContainer.classList.contains('d-none')) {
            commentsContainer.classList.remove('d-none');
        }

        const avatarHtml = comment.profile_image 
            ? `<img src="${baseUrl}/public/uploads/avatars/${comment.profile_image}" alt="${escapeHtml(comment.full_name)}" class="comment-avatar" onerror="this.onerror=null; this.outerHTML='<div class=\\'comment-avatar\\'>${escapeHtml(comment.initials)}</div>';">`
            : `<div class="comment-avatar">${escapeHtml(comment.initials)}</div>`;

        const commentBubbleHtml = `
            <div class="comment-bubble" id="comment-${comment.id}">
                ${avatarHtml}
                <div class="comment-content-box">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                        <div>
                            <span class="comment-author">${escapeHtml(comment.full_name)}</span>
                            <span class="text-muted small">@${escapeHtml(comment.username)}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <small class="text-muted" title="${escapeHtml(comment.created_at)}">
                                ${escapeHtml(comment.time_badge)}
                            </small>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Comment options" style="line-height: 1;">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="min-width: 120px; font-size: 0.85rem;">
                                    <li>
                                        <a class="dropdown-item py-1 text-secondary d-flex align-items-center gap-2" href="${baseUrl}/index.php?action=edit_comment&comment_id=${comment.id}">
                                            <i class="bi bi-pencil-square text-muted"></i>
                                            <span>Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <form action="${baseUrl}/index.php?action=delete_comment" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this comment? This action cannot be undone.');">
                                            <input type="hidden" name="csrf_token" value="${escapeHtml(document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '')}">
                                            <input type="hidden" name="comment_id" value="${comment.id}">
                                            <button type="submit" class="dropdown-item py-1 text-secondary d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start">
                                                <i class="bi bi-trash text-muted"></i>
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="comment-text">
                        ${escapeHtml(comment.content).replace(/\n/g, '<br>')}
                    </div>
                </div>
            </div>
        `;

        // Check if inserting before form or appending to end
        const commentForm = commentsContainer.querySelector('form.comment-input-row');
        if (commentForm) {
            commentForm.insertAdjacentHTML('beforebegin', commentBubbleHtml);
        } else {
            commentsContainer.insertAdjacentHTML('beforeend', commentBubbleHtml);
        }

        // Update comment counter elements
        const postCard = document.getElementById(`post-${postId}`);
        if (postCard) {
            const commentCountSpan = postCard.querySelector('.post-action-btn[onclick*="toggleComments"] span');
            if (commentCountSpan) {
                commentCountSpan.textContent = totalCommentsCount;
            }
        }
    }

    // =========================================================================
    // 3. AJAX POST CREATION
    // =========================================================================
    const composerForm = document.querySelector('.composer-card form');
    if (composerForm) {
        composerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = composerForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Post';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Posting...';
            }

            const formData = new FormData(composerForm);
            formData.append('ajax', '1');

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
            if (csrfToken && !formData.has('csrf_token')) {
                formData.append('csrf_token', csrfToken);
            }

            fetch(composerForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }

                if (data && data.success) {
                    composerForm.reset();
                    if (typeof window.clearSelectedImage === 'function') {
                        window.clearSelectedImage();
                    }
                    prependNewPostToFeed(data.post);
                } else if (data && data.error) {
                    alert(data.error);
                }
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
                console.error('Error publishing post:', err);
            });
        });
    }

    function prependNewPostToFeed(post) {
        const composerCard = document.querySelector('.composer-card');
        const feedHeader = composerCard ? composerCard.nextElementSibling : null;

        // Parent container for post cards
        let feedParent = composerCard ? composerCard.parentElement : null;
        if (!feedParent) return;

        // If "No posts yet" card is present, remove it
        const emptyStateCard = feedParent.querySelector('.card-modern.p-5.text-center.text-muted');
        if (emptyStateCard) {
            emptyStateCard.remove();
        }

        const avatarHtml = post.profile_image
            ? `<img src="${baseUrl}/public/uploads/avatars/${post.profile_image}" alt="${escapeHtml(post.full_name)}" class="post-avatar" onerror="this.onerror=null; this.outerHTML='<div class=\\'post-avatar\\'>${escapeHtml(post.initials)}</div>';">`
            : `<div class="post-avatar">${escapeHtml(post.initials)}</div>`;

        const imageHtml = post.image 
            ? `<div class="post-image-container"><img src="${baseUrl}/public/uploads/posts/${post.image}" alt="Post attachment"></div>`
            : '';

        const postCardHtml = `
            <article class="post-card" id="post-${post.id}">
                <div class="post-header">
                    <div class="post-author-box">
                        ${avatarHtml}
                        <div>
                            <a href="#" class="post-author-name">${escapeHtml(post.full_name)}</a>
                            <div class="post-meta">
                                @${escapeHtml(post.username)} &bull; ${escapeHtml(post.time_badge)}
                            </div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Post actions">
                            <i class="bi bi-three-dots fs-5"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li>
                                <a class="dropdown-item small text-secondary" href="${baseUrl}/index.php?action=edit_post&post_id=${post.id}">
                                    <i class="bi bi-pencil-square me-2 text-muted"></i>Edit post
                                </a>
                            </li>
                            <li>
                                <form action="${baseUrl}/index.php?action=delete_post" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="${escapeHtml(document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '')}">
                                    <input type="hidden" name="post_id" value="${post.id}">
                                    <button type="submit" class="dropdown-item small text-secondary border-0 bg-transparent w-100 text-start">
                                        <i class="bi bi-trash me-2 text-muted"></i>Delete post
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="post-content">
                    ${escapeHtml(post.content).replace(/\n/g, '<br>')}
                </div>

                ${imageHtml}

                <div class="post-footer-actions d-flex align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <form action="${baseUrl}/index.php?action=like_post" method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="${escapeHtml(document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '')}">
                            <input type="hidden" name="post_id" value="${post.id}">
                            <button type="submit" class="post-action-btn" title="Like">
                                <i class="bi bi-heart"></i>
                                <span>0</span>
                            </button>
                        </form>
                        <button type="button" class="post-action-btn" onclick="toggleComments(${post.id})" title="Comments">
                            <i class="bi bi-chat"></i>
                            <span>0</span>
                        </button>
                    </div>
                </div>

                <div class="comments-container d-none" id="comments-box-${post.id}">
                    <form action="${baseUrl}/index.php?action=create_comment" method="POST" class="comment-input-row">
                        <input type="hidden" name="csrf_token" value="${escapeHtml(document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '')}">
                        <input type="hidden" name="post_id" value="${post.id}">
                        <input type="text" name="content" class="form-control" placeholder="Write a comment..." maxlength="1000" required>
                        <button type="submit" class="btn btn-sm btn-accent rounded-pill px-3" title="Post comment">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                </div>
            </article>
        `;

        const firstPost = feedParent.querySelector('.post-card');
        if (firstPost) {
            firstPost.insertAdjacentHTML('beforebegin', postCardHtml);
        } else {
            feedParent.insertAdjacentHTML('beforeend', postCardHtml);
        }
    }

    // =========================================================================
    // 4. AJAX FOLLOW & UNFOLLOW
    // =========================================================================
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !form.action || !form.action.includes('action=follow_user')) {
            return;
        }

        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('ajax', '1');

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        if (csrfToken && !formData.has('csrf_token')) {
            formData.append('csrf_token', csrfToken);
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            if (data && data.success) {
                // Update Follow button state
                if (submitBtn) {
                    const icon = submitBtn.querySelector('i');
                    const textSpan = submitBtn.querySelector('span');

                    if (data.is_following) {
                        submitBtn.className = 'btn btn-outline-secondary px-4 fw-semibold rounded-pill';
                        if (icon) icon.className = 'bi bi-person-check-fill me-1';
                        if (textSpan) textSpan.textContent = 'Following';
                    } else {
                        submitBtn.className = 'btn btn-navy px-4 fw-semibold rounded-pill';
                        if (icon) icon.className = 'bi bi-person-plus-fill me-1';
                        if (textSpan) textSpan.textContent = 'Follow';
                    }
                }

                // Update Followers counter if present on profile
                const followersCountEl = document.getElementById('profileFollowersCount');
                if (followersCountEl && typeof data.followers_count !== 'undefined') {
                    followersCountEl.textContent = data.followers_count;
                }
            } else if (data && data.error) {
                alert(data.error);
            }
        })
        .catch(err => {
            if (submitBtn) submitBtn.disabled = false;
            console.error('Follow error:', err);
        });
    });

    // =========================================================================
    // 5. REAL-TIME NOTIFICATIONS & MESSAGE UNREAD BADGES POLLING
    // =========================================================================
    const notifBadge = document.getElementById('notifBadge');
    const messagesBadge = document.getElementById('messagesBadge');
    const notifListContainer = document.getElementById('notificationsDropdownList');
    const btnMarkAllNotifsRead = document.getElementById('btnMarkAllNotifsRead');

    function pollCounters() {
        // Poll unread notifications
        fetch(`${baseUrl}/index.php?action=unread_notifications`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success && notifBadge) {
                const count = parseInt(data.unread_count, 10) || 0;
                if (count > 0) {
                    notifBadge.textContent = count > 99 ? '99+' : count;
                    notifBadge.classList.remove('d-none');
                } else {
                    notifBadge.classList.add('d-none');
                }
            }
        })
        .catch(err => console.debug('Notif poll:', err));

        // Poll unread messages
        fetch(`${baseUrl}/index.php?action=unread_messages`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success && messagesBadge) {
                const count = parseInt(data.unread_count, 10) || 0;
                if (count > 0) {
                    messagesBadge.textContent = count > 99 ? '99+' : count;
                    messagesBadge.classList.remove('d-none');
                } else {
                    messagesBadge.classList.add('d-none');
                }
            }
        })
        .catch(err => console.debug('Messages poll:', err));
    }

    // Load full notifications when dropdown opens
    const notifDropdownBtn = document.getElementById('notificationsDropdownBtn');
    if (notifDropdownBtn && notifListContainer) {
        notifDropdownBtn.addEventListener('show.bs.dropdown', function() {
            fetch(`${baseUrl}/index.php?action=notifications`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success && Array.isArray(data.notifications)) {
                    renderNotifications(data.notifications);
                }
            })
            .catch(err => console.error('Error fetching notifications:', err));
        });
    }

    function renderNotifications(notifs) {
        if (!notifListContainer) return;
        if (notifs.length === 0) {
            notifListContainer.innerHTML = `
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-bell-slash fs-3 d-block mb-1 opacity-50"></i>
                    No notifications yet
                </div>
            `;
            return;
        }

        let html = '<div class="list-group list-group-flush">';
        notifs.forEach(n => {
            let iconClass = 'bi-bell-fill text-primary';
            if (n.type === 'like') iconClass = 'bi-heart-fill text-danger';
            if (n.type === 'comment') iconClass = 'bi-chat-fill text-primary';
            if (n.type === 'follow') iconClass = 'bi-person-plus-fill text-success';

            const unreadStyle = (n.is_read == 0) ? 'background-color: var(--bs-light-bg-subtle, rgba(13, 110, 253, 0.05)); font-weight: 500;' : '';
            const targetUrl = n.target_url || `${baseUrl}/index.php`;

            html += `
                <a href="${escapeHtml(targetUrl)}" 
                   class="list-group-item list-group-item-action p-3 border-0 border-bottom d-flex align-items-start gap-3 notification-item" 
                   data-notification-id="${n.id}"
                   data-is-read="${n.is_read}"
                   data-type="${escapeHtml(n.type)}"
                   data-entity-id="${n.entity_id || ''}"
                   style="${unreadStyle}">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="background: rgba(0,0,0,0.05); width: 36px; height: 36px;">
                        <i class="bi ${iconClass}"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="small text-dark mb-1 text-break">${escapeHtml(n.message)}</div>
                        <small class="text-muted" style="font-size: 0.72rem;">${escapeHtml(n.time_ago)}</small>
                    </div>
                    ${(n.is_read == 0) ? '<span class="badge bg-primary rounded-pill p-1 mt-1" style="width: 7px; height: 7px; display: inline-block;"> </span>' : ''}
                </a>
            `;
        });
        html += '</div>';
        notifListContainer.innerHTML = html;

        // Attach click handlers to notification items
        const notifItems = notifListContainer.querySelectorAll('.notification-item');
        notifItems.forEach(item => {
            item.addEventListener('click', function(e) {
                const notifId = this.getAttribute('data-notification-id');
                const isRead = this.getAttribute('data-is-read');
                const targetUrl = this.getAttribute('href');
                const notifType = this.getAttribute('data-type');
                const entityId = this.getAttribute('data-entity-id');

                // Mark this notification as read on the backend asynchronously
                if (isRead === '0' && notifId) {
                    markSingleNotificationRead(notifId);
                }

                // If clicking a like/comment notification while already on the home/feed page
                const isCurrentFeedPage = window.location.pathname.endsWith('/index.php') || window.location.pathname.endsWith('/') || window.location.search === '' || window.location.search === '?action=home';
                if ((notifType === 'like' || notifType === 'comment') && entityId && isCurrentFeedPage && !window.location.search.includes('action=')) {
                    const targetPost = document.getElementById('post-' + entityId);
                    if (targetPost) {
                        e.preventDefault();
                        
                        // Close dropdown
                        const dropdownInstance = bootstrap.Dropdown.getInstance(document.getElementById('notificationsDropdownBtn'));
                        if (dropdownInstance) {
                            dropdownInstance.hide();
                        }

                        // If it's a comment, expand the comments box
                        if (notifType === 'comment') {
                            const commentsBox = document.getElementById('comments-box-' + entityId);
                            if (commentsBox && commentsBox.classList.contains('d-none')) {
                                commentsBox.classList.remove('d-none');
                            }
                        }

                        // Smoothly scroll to the target post and apply highlight
                        targetPost.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        highlightPostElement(targetPost);
                        history.pushState(null, null, '#post-' + entityId);
                        return;
                    }
                }
                // Otherwise allow standard navigation to targetUrl
            });
        });
    }

    // Helper: Mark single notification read
    function markSingleNotificationRead(notifId) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        const fd = new FormData();
        fd.append('notification_id', notifId);
        fd.append('csrf_token', csrfToken);

        fetch(`${baseUrl}/index.php?action=mark_notifications_read`, {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                // Decrement or hide badge
                if (notifBadge && !notifBadge.classList.contains('d-none')) {
                    const currentCount = parseInt(notifBadge.textContent, 10) || 0;
                    if (currentCount > 1) {
                        notifBadge.textContent = currentCount - 1;
                    } else {
                        notifBadge.classList.add('d-none');
                    }
                }
            }
        })
        .catch(err => console.debug('Mark single read error:', err));
    }

    // Post highlight helper
    function highlightPostElement(postEl) {
        if (!postEl) return;
        postEl.classList.remove('post-highlight');
        void postEl.offsetWidth; // Force CSS reflow
        postEl.classList.add('post-highlight');
        setTimeout(() => {
            postEl.classList.remove('post-highlight');
        }, 4000);
    }

    // Highlight post on page load if URL contains hash (#post-X)
    if (window.location.hash && window.location.hash.startsWith('#post-')) {
        const targetPost = document.getElementById(window.location.hash.substring(1));
        if (targetPost) {
            setTimeout(() => {
                targetPost.scrollIntoView({ behavior: 'smooth', block: 'center' });
                highlightPostElement(targetPost);
                
                // If comments box is collapsed, optionally open it
                const postId = window.location.hash.replace('#post-', '');
                const commentsBox = document.getElementById('comments-box-' + postId);
                if (commentsBox && commentsBox.classList.contains('d-none')) {
                    commentsBox.classList.remove('d-none');
                }
            }, 300);
        }
    }

    // Mark all read button
    if (btnMarkAllNotifsRead) {
        btnMarkAllNotifsRead.addEventListener('click', function(e) {
            e.preventDefault();
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            const fd = new FormData();
            fd.append('csrf_token', csrfToken);

            fetch(`${baseUrl}/index.php?action=mark_notifications_read`, {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    if (notifBadge) notifBadge.classList.add('d-none');
                    // Refresh dropdown list
                    if (notifDropdownBtn) {
                        fetch(`${baseUrl}/index.php?action=notifications`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(r => r.json())
                        .then(d => {
                            if (d && d.success) renderNotifications(d.notifications);
                        });
                    }
                }
            });
        });
    }

    // Run counters poll immediately and every 12 seconds
    pollCounters();
    setInterval(pollCounters, 12000);

    // Helper: Escaping HTML
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
