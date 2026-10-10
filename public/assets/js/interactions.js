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

        // Instant UI Toggle (Optimistic Update)
        const submitBtn = form.querySelector('button[type="submit"]');
        const heartIcon = submitBtn ? submitBtn.querySelector('i') : null;
        const countSpan = submitBtn ? submitBtn.querySelector('span') : null;

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
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

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
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
                            <a href="${baseUrl}/index.php?action=edit_comment&comment_id=${comment.id}" class="comment-btn-action text-primary" title="Edit comment">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                            <form action="${baseUrl}/index.php?action=delete_comment" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this comment? This action cannot be undone.');">
                                <input type="hidden" name="comment_id" value="${comment.id}">
                                <button type="submit" class="comment-btn-action text-danger" title="Delete comment">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
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

            fetch(composerForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
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
                                <a class="dropdown-item small text-primary" href="${baseUrl}/index.php?action=edit_post&post_id=${post.id}">
                                    <i class="bi bi-pencil-square me-2"></i>Edit post
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item small text-danger" href="${baseUrl}/index.php?action=delete_post&post_id=${post.id}" onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                    <i class="bi bi-trash me-2"></i>Delete post
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small" href="#"><i class="bi bi-bookmark me-2"></i>Save post</a></li>
                            <li><a class="dropdown-item small" href="#"><i class="bi bi-link-45deg me-2"></i>Copy link</a></li>
                        </ul>
                    </div>
                </div>

                <div class="post-content">
                    ${escapeHtml(post.content).replace(/\n/g, '<br>')}
                </div>

                ${imageHtml}

                <div class="post-footer-actions d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <form action="${baseUrl}/index.php?action=like_post" method="POST" class="d-inline">
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

                    <div class="d-flex align-items-center gap-1">
                        <a href="${baseUrl}/index.php?action=edit_post&post_id=${post.id}" class="btn btn-sm btn-light text-primary border-0 rounded-pill px-2 py-1 small" title="Edit your post">
                            <i class="bi bi-pencil-square me-1"></i>Edit
                        </a>
                        <a href="${baseUrl}/index.php?action=delete_post&post_id=${post.id}" class="btn btn-sm btn-light text-danger border-0 rounded-pill px-2 py-1 small" onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.');" title="Delete your post">
                            <i class="bi bi-trash me-1"></i>Delete
                        </a>
                    </div>
                </div>

                <div class="comments-container d-none" id="comments-box-${post.id}">
                    <form action="${baseUrl}/index.php?action=create_comment" method="POST" class="comment-input-row">
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
