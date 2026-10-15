<?php
// app/views/messages/index.php
// Direct Messages Hub View - Two Column Responsive Messaging Experience

require_once __DIR__ . '/../layouts/header.php';

$activeUserId = !empty($activeUser) ? (int)$activeUser['id'] : 0;
$activeUserAvatar = null;
if (!empty($activeUser['profile_image'])) {
    $uploadedPath = __DIR__ . '/../../../public/uploads/avatars/' . basename($activeUser['profile_image']);
    if (file_exists($uploadedPath)) {
        $activeUserAvatar = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($activeUser['profile_image']));
    }
}

$activeUserInitials = 'U';
if (!empty($activeUser['full_name'])) {
    $parts = explode(' ', trim($activeUser['full_name']));
    if (count($parts) >= 2) {
        $activeUserInitials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1));
    } else {
        $activeUserInitials = strtoupper(mb_substr($activeUser['full_name'], 0, 2));
    }
}
?>

<div class="messages-container card-modern overflow-hidden mb-4" style="height: calc(100vh - 160px); min-height: 550px;">
    <div class="row g-0 h-100">
        
        <!-- ==============================================
             LEFT COLUMN: CONVERSATION LIST & NEW CHAT
             ============================================== -->
        <div class="col-md-4 col-lg-4 border-end d-flex flex-column h-100 messages-sidebar bg-body">
            
            <!-- Sidebar Header & Action -->
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-chat-dots-fill text-primary"></i>
                    <span>Messages</span>
                </h5>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#newChatModal" title="New Message">
                    <i class="bi bi-pencil-square me-1"></i> New
                </button>
            </div>

            <!-- Conversations List (Scrollable) -->
            <div class="flex-grow-1 overflow-y-auto" id="conversationsListContainer" style="overflow-x: hidden;">
                <?php if (empty($conversations)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-chat-square-text fs-1 mb-2 d-block opacity-50"></i>
                        <p class="mb-2 fw-semibold">No conversations yet</p>
                        <small class="d-block mb-3">Reach out to campus peers or start a conversation.</small>
                        <button type="button" class="btn btn-sm btn-navy rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#newChatModal">
                            Start a chat
                        </button>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($conversations as $conv): 
                            $convId = (int)$conv['other_user_id'];
                            $isSelected = ($convId === $activeUserId);
                            
                            $cAvatar = null;
                            if (!empty($conv['other_profile_image'])) {
                                $cPath = __DIR__ . '/../../../public/uploads/avatars/' . basename($conv['other_profile_image']);
                                if (file_exists($cPath)) {
                                    $cAvatar = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($conv['other_profile_image']));
                                }
                            }

                            $cInitials = 'U';
                            if (!empty($conv['other_full_name'])) {
                                $p = explode(' ', trim($conv['other_full_name']));
                                $cInitials = count($p) >= 2 ? strtoupper(mb_substr($p[0], 0, 1) . mb_substr($p[count($p)-1], 0, 1)) : strtoupper(mb_substr($conv['other_full_name'], 0, 2));
                            }
                        ?>
                            <a href="<?= BASE_URL ?>/index.php?action=messages&user_id=<?= $convId ?>" 
                               class="list-group-item list-group-item-action p-3 d-flex align-items-center gap-3 border-0 border-bottom <?= $isSelected ? 'active bg-light-subtle' : '' ?>"
                               style="transition: background-color 0.15s ease;">
                                
                                <div class="position-relative flex-shrink-0">
                                    <?php if ($cAvatar): ?>
                                        <img src="<?= $cAvatar ?>" alt="<?= htmlspecialchars($conv['other_full_name']) ?>" class="rounded-circle" style="width: 44px; height: 44px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px; background-color: #07193d; font-size: 0.9rem;">
                                            <?= htmlspecialchars($cInitials) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="mb-0 fw-bold text-truncate text-dark" style="max-width: 140px;">
                                            <?= htmlspecialchars($conv['other_full_name']) ?>
                                        </h6>
                                        <small class="text-muted" style="font-size: 0.72rem;">
                                            <?= !empty($conv['last_message_time']) ? date('M j', strtotime($conv['last_message_time'])) : '' ?>
                                        </small>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <?php
                                        if (!empty($conv['last_message_is_unsent'])) {
                                            $lastText = ((int)$conv['last_sender_id'] === (int)$_SESSION['user_id'])
                                                ? '<span class="fst-italic opacity-75"><i class="bi bi-slash-circle me-1"></i>You unsent a message</span>'
                                                : '<span class="fst-italic opacity-75"><i class="bi bi-slash-circle me-1"></i>Unsent a message</span>';
                                        } else {
                                            $lastText = htmlspecialchars($conv['last_message'] ?? '');
                                            if (!empty($conv['last_message_is_edited'])) {
                                                $lastText .= ' <small class="fst-italic opacity-75">(edited)</small>';
                                            }
                                        }
                                        ?>
                                        <p class="mb-0 text-muted small text-truncate" style="max-width: 180px;">
                                            <?= $lastText ?>
                                        </p>
                                        <?php if (!empty($conv['unread_count']) && (int)$conv['unread_count'] > 0): ?>
                                            <span class="badge rounded-pill bg-primary ms-1" style="font-size: 0.7rem;">
                                                <?= (int)$conv['unread_count'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ==============================================
             RIGHT COLUMN: CHAT CONVERSATION VIEW
             ============================================== -->
        <div class="col-md-8 col-lg-8 d-flex flex-column h-100 bg-card">
            
            <?php if ($activeUser): ?>
                <!-- Active Chat Header -->
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-body">
                    <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$activeUser['id'] ?>" class="d-flex align-items-center gap-3 text-decoration-none" title="View <?= htmlspecialchars($activeUser['full_name']) ?>'s profile">
                        <div class="position-relative">
                            <?php if ($activeUserAvatar): ?>
                                <img src="<?= $activeUserAvatar ?>" alt="<?= htmlspecialchars($activeUser['full_name']) ?>" class="rounded-circle" style="width: 42px; height: 42px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; background-color: #07193d; font-size: 0.9rem;">
                                    <?= htmlspecialchars($activeUserInitials) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($activeUser['full_name']) ?></h6>
                            <small class="text-muted">@<?= htmlspecialchars($activeUser['username']) ?></small>
                        </div>
                    </a>
                    <div>
                        <a href="<?= BASE_URL ?>/index.php?action=profile&user_id=<?= (int)$activeUser['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="View Profile">
                            <i class="bi bi-person me-1"></i> Profile
                        </a>
                    </div>
                </div>

                <!-- Chat Messages Scroll Container -->
                <div class="flex-grow-1 p-3 overflow-y-auto d-flex flex-column gap-2" id="chatMessagesArea" style="background-color: var(--bs-body-bg);">
                    <?php if (empty($messages)): ?>
                        <div class="my-auto text-center text-muted py-5" id="chatEmptyState">
                            <i class="bi bi-chat-heart fs-1 text-primary mb-2 d-block opacity-75"></i>
                            <p class="fw-semibold mb-1">Say hello to <?= htmlspecialchars($activeUser['full_name']) ?>!</p>
                            <small>This is the start of your direct conversation.</small>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): 
                            $isMe = ((int)$msg['sender_id'] === (int)$_SESSION['user_id']);
                            $isUnsent = !empty($msg['is_unsent']);
                            $isEdited = !empty($msg['is_edited']);
                        ?>
                            <div class="message-row d-flex flex-column <?= $isMe ? 'align-items-end' : 'align-items-start' ?> mb-1" 
                                 data-message-id="<?= (int)$msg['id'] ?>" 
                                 data-content="<?= htmlspecialchars($msg['message'], ENT_QUOTES) ?>"
                                 data-edited="<?= $isEdited ? '1' : '0' ?>"
                                 data-unsent="<?= $isUnsent ? '1' : '0' ?>">
                                
                                <?php if ($isUnsent): ?>
                                    <!-- Unsent Message Bubble -->
                                    <div class="p-2 px-3 rounded-4 shadow-sm border border-secondary-subtle" style="max-width: 75%; background-color: var(--bs-tertiary-bg); color: var(--bs-secondary-color);">
                                        <div class="fst-italic small d-flex align-items-center gap-1">
                                            <i class="bi bi-slash-circle"></i>
                                            <span><?= $isMe ? 'You unsent a message' : htmlspecialchars($activeUser['full_name']) . ' unsent a message' ?></span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 px-1 mt-1">
                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            <?= date('g:i A', strtotime($msg['created_at'])) ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <!-- Active Message Bubble -->
                                    <div class="d-flex align-items-center gap-1 <?= $isMe ? 'flex-row' : '' ?>" style="max-width: 80%;">
                                        <?php if ($isMe): ?>
                                            <div class="dropdown message-actions-dropdown">
                                                <button class="message-action-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Message actions">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="font-size: 0.85rem; min-width: 120px;">
                                                    <li>
                                                        <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-1 btn-edit-msg action-item-edit" data-id="<?= (int)$msg['id'] ?>">
                                                            <i class="bi bi-pencil"></i>
                                                            <span>Edit</span>
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider my-1"></li>
                                                    <li>
                                                        <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-1 btn-unsend-msg action-item-delete" data-id="<?= (int)$msg['id'] ?>">
                                                            <i class="bi bi-trash3"></i>
                                                            <span>Unsend</span>
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                        <div class="p-2 px-3 rounded-4 shadow-sm" style="<?= $isMe ? 'background-color: #07193d; color: #fff;' : 'background-color: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color);' ?>">
                                            <div class="text-break message-text-content" style="white-space: pre-wrap; font-size: 0.93rem;"><?= htmlspecialchars($msg['message']) ?></div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-1 px-1 mt-1">
                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            <?= date('g:i A', strtotime($msg['created_at'])) ?>
                                        </small>
                                        <small class="text-muted fst-italic edited-badge <?= $isEdited ? '' : 'd-none' ?>" style="font-size: 0.68rem;">• Edited</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Message Input Footer -->
                <div class="p-3 border-top bg-body">
                    <form id="chatMessageForm" class="d-flex align-items-center gap-2">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="receiver_id" id="chatReceiverId" value="<?= (int)$activeUser['id'] ?>">
                        <input 
                            type="text" 
                            name="message" 
                            id="chatInputMessage" 
                            class="form-control rounded-pill px-3 py-2" 
                            placeholder="Type a message..." 
                            autocomplete="off"
                            required
                        >
                        <button type="submit" id="btnSendMessage" class="btn btn-navy rounded-circle d-flex align-items-center justify-content-center p-0 flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bi bi-send-fill fs-6"></i>
                        </button>
                    </form>
                </div>

            <?php else: ?>
                <!-- No Active Conversation Selected State -->
                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center text-muted p-4">
                    <div class="rounded-circle bg-light p-4 mb-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-chat-left-dots text-primary fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Select a conversation</h5>
                    <p class="small text-muted mb-4 col-md-6">Choose an existing conversation from the left or start a new message with any campus member.</p>
                    <button type="button" class="btn btn-navy rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#newChatModal">
                        <i class="bi bi-plus-lg me-1"></i> New Message
                    </button>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- ==============================================
     MODAL: START NEW CHAT (SELECT USER)
     ============================================== -->
<div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="newChatModalLabel">
                    <i class="bi bi-chat-plus-fill text-primary"></i>
                    <span>Start a New Conversation</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <input type="text" id="filterUsersInput" class="form-control rounded-pill mb-3" placeholder="Search campus members..." autocomplete="off">
                <div class="list-group list-group-flush overflow-y-auto" id="allUsersList" style="max-height: 320px;">
                    <?php if (!empty($allUsers)): ?>
                        <?php foreach ($allUsers as $u): 
                            if ((int)$u['id'] === (int)$_SESSION['user_id']) continue;
                        ?>
                            <a href="<?= BASE_URL ?>/index.php?action=messages&user_id=<?= (int)$u['id'] ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 px-3 border-0 rounded-3 mb-1 user-select-item">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 38px; height: 38px; background-color: #07193d; font-size: 0.85rem;">
                                    <?= strtoupper(mb_substr($u['full_name'], 0, 1)) ?>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-bold text-dark text-truncate member-name"><?= htmlspecialchars($u['full_name']) ?></div>
                                    <small class="text-muted member-user">@<?= htmlspecialchars($u['username']) ?></small>
                                </div>
                                <i class="bi bi-chevron-right text-muted small"></i>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No other users found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     MODAL: EDIT MESSAGE
     ============================================== -->
<div class="modal fade" id="editMessageModal" tabindex="-1" aria-labelledby="editMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="editMessageModalLabel">
                    <i class="bi bi-pencil-square text-primary"></i>
                    <span>Edit Message</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditMessage">
                <div class="modal-body p-3">
                    <input type="hidden" name="message_id" id="modalEditMessageId" value="">
                    <label for="modalEditMessageText" class="form-label small fw-semibold text-muted mb-1">Message Content</label>
                    <textarea 
                        name="message" 
                        id="modalEditMessageText" 
                        class="form-control rounded-3" 
                        rows="3" 
                        placeholder="Edit your message..." 
                        required
                    ></textarea>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-info-circle me-1"></i> Edited messages will show an "Edited" label.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnSaveEditMessage" class="btn btn-sm btn-navy rounded-pill px-4">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================
     MODAL: UNSEND CONFIRMATION
     ============================================== -->
<div class="modal fade" id="unsendMessageModal" tabindex="-1" aria-labelledby="unsendMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2" id="unsendMessageModalLabel">
                    <i class="bi bi-trash3-fill"></i>
                    <span>Unsend Message?</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <input type="hidden" id="modalUnsendMessageId" value="">
                <p class="text-dark mb-2">Are you sure you want to unsend this message?</p>
                <p class="text-muted small mb-3">This message will be removed for everyone in this conversation.</p>
                <div class="p-2 px-3 rounded-3 bg-light border text-muted fst-italic text-break small" id="modalUnsendMessagePreview">
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-3">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnConfirmUnsend" class="btn btn-sm btn-danger rounded-pill px-4">
                    <i class="bi bi-trash3 me-1"></i> Unsend
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     DIRECT MESSAGES JAVASCRIPT LOGIC
     ============================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesArea = document.getElementById('chatMessagesArea');
    const messageForm = document.getElementById('chatMessageForm');
    const inputMessage = document.getElementById('chatInputMessage');
    const receiverIdInput = document.getElementById('chatReceiverId');
    const filterInput = document.getElementById('filterUsersInput');
    const usersList = document.getElementById('allUsersList');

    // Auto-scroll chat to bottom
    function scrollToBottom() {
        if (messagesArea) {
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }
    }
    scrollToBottom();

    // Render single message node
    function renderMessageNode(msg) {
        const isMe = !!msg.is_me;
        const isUnsent = !!msg.is_unsent;
        const isEdited = !!msg.is_edited;
        const activeUserName = "<?= !empty($activeUser['full_name']) ? htmlspecialchars($activeUser['full_name'], ENT_QUOTES) : 'Peer' ?>";

        const row = document.createElement('div');
        row.className = `message-row d-flex flex-column ${isMe ? 'align-items-end' : 'align-items-start'} mb-1`;
        row.setAttribute('data-message-id', msg.id);
        row.setAttribute('data-content', msg.content || '');
        row.setAttribute('data-edited', isEdited ? '1' : '0');
        row.setAttribute('data-unsent', isUnsent ? '1' : '0');

        if (isUnsent) {
            const unsentText = isMe ? 'You unsent a message' : (activeUserName + ' unsent a message');
            row.innerHTML = `
                <div class="p-2 px-3 rounded-4 shadow-sm border border-secondary-subtle" style="max-width: 75%; background-color: var(--bs-tertiary-bg); color: var(--bs-secondary-color);">
                    <div class="fst-italic small d-flex align-items-center gap-1">
                        <i class="bi bi-slash-circle"></i>
                        <span>${escapeHtml(unsentText)}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1 px-1 mt-1">
                    <small class="text-muted" style="font-size: 0.7rem;">${escapeHtml(msg.time)}</small>
                </div>
            `;
            return row;
        }

        let dropdownHtml = '';
        if (isMe) {
            dropdownHtml = `
                <div class="dropdown message-actions-dropdown">
                    <button class="message-action-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Message actions">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="font-size: 0.85rem; min-width: 120px;">
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-1 btn-edit-msg action-item-edit" data-id="${msg.id}">
                                <i class="bi bi-pencil"></i>
                                <span>Edit</span>
                            </button>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-1 btn-unsend-msg action-item-delete" data-id="${msg.id}">
                                <i class="bi bi-trash3"></i>
                                <span>Unsend</span>
                            </button>
                        </li>
                    </ul>
                </div>
            `;
        }

        const bubbleStyle = isMe
            ? 'background-color: #07193d; color: #fff;'
            : 'background-color: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color);';


        row.innerHTML = `
            <div class="d-flex align-items-center gap-1" style="max-width: 80%;">
                ${dropdownHtml}
                <div class="p-2 px-3 rounded-4 shadow-sm" style="${bubbleStyle}">
                    <div class="text-break message-text-content" style="white-space: pre-wrap; font-size: 0.93rem;">${escapeHtml(msg.content)}</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1 px-1 mt-1">
                <small class="text-muted" style="font-size: 0.7rem;">${escapeHtml(msg.time)}</small>
                <small class="text-muted fst-italic edited-badge ${msg.is_edited ? '' : 'd-none'}" style="font-size: 0.68rem;">• Edited</small>
            </div>
        `;

        return row;
    }

    // User Search filter in modal
    if (filterInput && usersList) {
        filterInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const items = usersList.querySelectorAll('.user-select-item');
            items.forEach(item => {
                const name = item.querySelector('.member-name')?.textContent.toLowerCase() || '';
                const user = item.querySelector('.member-user')?.textContent.toLowerCase() || '';
                if (name.includes(query) || user.includes(query)) {
                    item.classList.remove('d-none');
                } else {
                    item.classList.add('d-none');
                }
            });
        });
    }

    // Send Message via AJAX
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = inputMessage.value.trim();
            const receiverId = receiverIdInput ? receiverIdInput.value : '';

            if (!text || !receiverId) return;

            const sendBtn = document.getElementById('btnSendMessage');
            if (sendBtn) sendBtn.disabled = true;

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            const formData = new FormData(messageForm);
            formData.append('ajax', '1');

            fetch(`${window.BASE_URL}/index.php?action=send_message`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (sendBtn) sendBtn.disabled = false;
                if (data && data.success) {
                    inputMessage.value = '';
                    
                    // Remove empty state if present
                    const emptyState = document.getElementById('chatEmptyState');
                    if (emptyState) emptyState.remove();

                    // Append outgoing message node
                    const msgNode = renderMessageNode(data.message);
                    messagesArea.appendChild(msgNode);
                    scrollToBottom();
                } else if (data && data.error) {
                    alert(data.error);
                }
            })
            .catch(err => {
                if (sendBtn) sendBtn.disabled = false;
                console.error('Send message error:', err);
            });
        });
    }

    // Event delegation on messages area for Edit and Unsend
    if (messagesArea) {
        messagesArea.addEventListener('click', function(e) {
            // Edit trigger
            const editBtn = e.target.closest('.btn-edit-msg');
            if (editBtn) {
                const msgRow = editBtn.closest('.message-row');
                if (!msgRow) return;
                const msgId = msgRow.getAttribute('data-message-id');
                const content = msgRow.getAttribute('data-content');
                document.getElementById('modalEditMessageId').value = msgId;
                document.getElementById('modalEditMessageText').value = content;
                const editModalEl = document.getElementById('editMessageModal');
                const modal = bootstrap.Modal.getOrCreateInstance(editModalEl);
                modal.show();
                return;
            }

            // Unsend trigger
            const unsendBtn = e.target.closest('.btn-unsend-msg');
            if (unsendBtn) {
                const msgRow = unsendBtn.closest('.message-row');
                if (!msgRow) return;
                const msgId = msgRow.getAttribute('data-message-id');
                const content = msgRow.getAttribute('data-content');
                document.getElementById('modalUnsendMessageId').value = msgId;
                document.getElementById('modalUnsendMessagePreview').textContent = content;
                const unsendModalEl = document.getElementById('unsendMessageModal');
                const modal = bootstrap.Modal.getOrCreateInstance(unsendModalEl);
                modal.show();
                return;
            }
        });
    }

    // Handle Edit Form Submission
    const formEdit = document.getElementById('formEditMessage');
    if (formEdit) {
        formEdit.addEventListener('submit', function(e) {
            e.preventDefault();
            const msgId = document.getElementById('modalEditMessageId').value;
            const newText = document.getElementById('modalEditMessageText').value.trim();

            if (!msgId || !newText) return;

            const saveBtn = document.getElementById('btnSaveEditMessage');
            if (saveBtn) saveBtn.disabled = true;

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            const formData = new FormData();
            formData.append('message_id', msgId);
            formData.append('message', newText);
            formData.append('ajax', '1');

            fetch(`${window.BASE_URL}/index.php?action=edit_message`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (saveBtn) saveBtn.disabled = false;
                if (data && data.success) {
                    const row = messagesArea.querySelector(`.message-row[data-message-id="${msgId}"]`);
                    if (row) {
                        row.setAttribute('data-content', data.message.content);
                        row.setAttribute('data-edited', '1');
                        const textEl = row.querySelector('.message-text-content');
                        if (textEl) textEl.textContent = data.message.content;
                        const badge = row.querySelector('.edited-badge');
                        if (badge) badge.classList.remove('d-none');
                    }
                    const modalEl = document.getElementById('editMessageModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                } else {
                    alert(data.error || 'Failed to edit message.');
                }
            })
            .catch(err => {
                if (saveBtn) saveBtn.disabled = false;
                console.error('Edit message error:', err);
            });
        });
    }

    // Handle Unsend Confirmation
    const btnConfirmUnsend = document.getElementById('btnConfirmUnsend');
    if (btnConfirmUnsend) {
        btnConfirmUnsend.addEventListener('click', function() {
            const msgId = document.getElementById('modalUnsendMessageId').value;
            if (!msgId) return;

            btnConfirmUnsend.disabled = true;

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            const formData = new FormData();
            formData.append('message_id', msgId);
            formData.append('ajax', '1');

            fetch(`${window.BASE_URL}/index.php?action=unsend_message`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                btnConfirmUnsend.disabled = false;
                if (data && data.success) {
                    const modalEl = document.getElementById('unsendMessageModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();

                    const row = messagesArea.querySelector(`.message-row[data-message-id="${msgId}"]`);
                    if (row) {
                        row.setAttribute('data-unsent', '1');
                        const timeText = row.querySelector('small.text-muted')?.textContent || 'Just now';
                        row.innerHTML = `
                            <div class="p-2 px-3 rounded-4 shadow-sm border border-secondary-subtle" style="max-width: 75%; background-color: var(--bs-tertiary-bg); color: var(--bs-secondary-color);">
                                <div class="fst-italic small d-flex align-items-center gap-1">
                                    <i class="bi bi-slash-circle"></i>
                                    <span>You unsent a message</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1 px-1 mt-1">
                                <small class="text-muted" style="font-size: 0.7rem;">${escapeHtml(timeText)}</small>
                            </div>
                        `;
                    }
                } else {
                    alert(data.error || 'Failed to unsend message.');
                }
            })
            .catch(err => {
                btnConfirmUnsend.disabled = false;
                console.error('Unsend message error:', err);
            });
        });
    }

    // Live Polling for active conversation (every 4 seconds)
    if (receiverIdInput && receiverIdInput.value) {
        const activeUserId = receiverIdInput.value;
        setInterval(function() {
            fetch(`${window.BASE_URL}/index.php?action=poll_messages&user_id=${activeUserId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success && Array.isArray(data.messages)) {
                    const currentRows = messagesArea.querySelectorAll('.message-row');
                    const currentFingerprint = Array.from(currentRows)
                        .map(el => el.getAttribute('data-message-id') + ':' + el.getAttribute('data-content') + ':' + el.getAttribute('data-edited') + ':' + (el.getAttribute('data-unsent') || '0'))
                        .join('|');
                    const newFingerprint = data.messages
                        .map(m => m.id + ':' + m.content + ':' + (m.is_edited ? '1' : '0') + ':' + (m.is_unsent ? '1' : '0'))
                        .join('|');


                    if (currentFingerprint !== newFingerprint) {
                        const isNearBottom = (messagesArea.scrollHeight - messagesArea.scrollTop - messagesArea.clientHeight) < 80;

                        messagesArea.innerHTML = '';
                        if (data.messages.length === 0) {
                            messagesArea.innerHTML = `
                                <div class="my-auto text-center text-muted py-5" id="chatEmptyState">
                                    <i class="bi bi-chat-heart fs-1 text-primary mb-2 d-block opacity-75"></i>
                                    <p class="fw-semibold mb-1">Say hello!</p>
                                    <small>This is the start of your direct conversation.</small>
                                </div>
                            `;
                        } else {
                            data.messages.forEach(msg => {
                                messagesArea.appendChild(renderMessageNode(msg));
                            });
                        }

                        if (isNearBottom || currentRows.length === 0) {
                            scrollToBottom();
                        }
                    }
                }
            })
            .catch(err => console.debug('Polling messages:', err));
        }, 4000);
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
</script>


<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
