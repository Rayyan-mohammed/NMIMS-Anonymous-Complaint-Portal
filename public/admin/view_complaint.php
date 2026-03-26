<?php
session_start();
require_once '../config/database.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Support\Csrf;
use App\Http\Controllers\Admin\ComplaintController;

$csrfToken = Csrf::generateToken($_SESSION, 'admin_actions');

$complaint_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$controller = new ComplaintController($conn);
$pageData = $controller->getComplaintPageData($_SESSION, $complaint_id);

if (($pageData['redirect'] ?? null) !== null) {
    header('Location: ' . $pageData['redirect']);
    exit();
}

$complaint = $pageData['complaint'];
$updates = $pageData['updates'];
$statusHistory = $pageData['status_history'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Complaint - NMIMS Complaint Portal</title>
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260326b">
</head>
<body class="admin-complaint-page">
    <div class="complaint-container vc-page">
        <div class="brand-logo">
            <img src="../assets/nmims_logo.jpg" alt="NMIMS University Logo">
        </div>
        <a href="dashboard.php" class="back-btn vc-back-btn">← Back to Dashboard</a>
        
        <div class="complaint-details">
            <h2>Complaint Details</h2>
            <p class="vc-subtitle">Reference <?php echo htmlspecialchars($complaint['reference_number']); ?> submitted by <?php echo htmlspecialchars($complaint['school_name']); ?>.</p>
            
            <div class="detail-row">
                <span class="detail-label">Reference Number:</span>
                <span><?php echo htmlspecialchars($complaint['reference_number']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">School:</span>
                <span><?php echo htmlspecialchars($complaint['school_name']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Type:</span>
                <span><?php echo ucwords($complaint['complaint_type']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span id="statusBadge" data-status="<?php echo htmlspecialchars((string) $complaint['status']); ?>" class="status-badge status-<?php echo $complaint['status']; ?>">
                    <?php echo ucwords(str_replace('_', ' ', $complaint['status'])); ?>
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Submitted Date:</span>
                <span><?php echo date('d M Y H:i', strtotime($complaint['created_at'])); ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Identity:</span>
                <span><?php echo ((int) ($complaint['is_anonymous'] ?? 1) === 1) ? 'Anonymous' : 'Shared with admin'; ?></span>
            </div>

            <?php if ((int) ($complaint['is_anonymous'] ?? 1) === 0): ?>
                <div class="detail-row">
                    <span class="detail-label">Student Name:</span>
                    <span><?php echo htmlspecialchars((string) ($complaint['student_name'] ?? '-')); ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">SAP ID:</span>
                    <span><?php echo htmlspecialchars((string) ($complaint['sap_id'] ?? '-')); ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Year:</span>
                    <span><?php echo htmlspecialchars((string) ($complaint['study_year'] ?? '-')); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="detail-row detail-row-block">
                <span class="detail-label">Complaint Details:</span>
                <p class="vc-complaint-text"><?php echo nl2br(htmlspecialchars($complaint['complaint_details'])); ?></p>
            </div>
        </div>

        <div class="vc-columns">

        <div class="updates-section vc-card-section">
            <h3>Updates</h3>
            
            <div id="updatesList">
                <?php if (empty($updates)): ?>
                    <p id="noUpdatesText" class="empty-state">No updates available for this complaint.</p>
                <?php else: ?>
                    <?php foreach ($updates as $update): ?>
                        <div class="update-card">
                            <div class="update-header">
                                <span>Updated by: <?php echo htmlspecialchars($update['updated_by_name']); ?></span>
                                <span><?php echo date('d M Y H:i', strtotime($update['updated_at'])); ?></span>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($update['update_text'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="quick-actions" aria-labelledby="quickActionHeading">
                <h4 id="quickActionHeading">Quick Actions</h4>
                <label for="quickUpdateText">Update note (optional for status changes):</label>
                <textarea id="quickUpdateText" rows="3" placeholder="Add context for status update or note..."></textarea>
                <div class="quick-actions-row">
                    <button type="button" class="action-progress" id="markInProgressBtn">Mark In Progress</button>
                    <button type="button" class="action-resolve" id="resolveBtn">Resolve</button>
                    <button type="button" class="action-update" id="addUpdateBtn">Add Update</button>
                </div>
                <p id="quickActionFeedback" class="action-feedback" aria-live="polite"></p>
            </div>
            
            <div class="update-form">
                <h4>Add Update</h4>
                <form action="add_update.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">
                    <label for="legacyUpdateText">Update details</label>
                    <textarea id="legacyUpdateText" name="update_text" rows="4" required placeholder="Enter your update..."></textarea>
                    <button type="submit">Submit Update</button>
                </form>
            </div>
        </div>

        <div class="history-section vc-card-section">
            <h3>Status History</h3>
            <div id="statusHistoryList">
                <?php if (empty($statusHistory)): ?>
                    <p id="noStatusHistoryText" class="empty-state">No status changes recorded yet.</p>
                <?php else: ?>
                    <?php foreach ($statusHistory as $history): ?>
                        <div class="history-card">
                            <strong><?php echo ucwords(str_replace('_', ' ', (string) $history['old_status'])); ?> -> <?php echo ucwords(str_replace('_', ' ', (string) $history['new_status'])); ?></strong>
                            <div class="history-meta">
                                <span>Changed by: <?php echo htmlspecialchars((string) $history['changed_by_name']); ?></span>
                                <span><?php echo date('d M Y H:i', strtotime((string) $history['changed_at'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>

    <script>
        (function () {
            const csrfToken = <?php echo json_encode($csrfToken); ?>;
            const complaintId = <?php echo (int) $complaint_id; ?>;
            const currentAdminName = <?php echo json_encode((string) ($_SESSION['name'] ?? 'Admin')); ?>;

            const quickUpdateText = document.getElementById('quickUpdateText');
            const markInProgressBtn = document.getElementById('markInProgressBtn');
            const resolveBtn = document.getElementById('resolveBtn');
            const addUpdateBtn = document.getElementById('addUpdateBtn');
            const quickActionFeedback = document.getElementById('quickActionFeedback');
            const statusBadge = document.getElementById('statusBadge');
            const updatesList = document.getElementById('updatesList');
            const statusHistoryList = document.getElementById('statusHistoryList');

            function formatStatus(status) {
                return status.replace(/_/g, ' ').replace(/\b\w/g, function (char) { return char.toUpperCase(); });
            }

            function formatDate(now) {
                return now.toLocaleString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            function setBusyState(isBusy) {
                [markInProgressBtn, resolveBtn, addUpdateBtn].forEach(function (button) {
                    button.disabled = isBusy;
                });
            }

            async function sendAction(payload) {
                setBusyState(true);
                quickActionFeedback.textContent = 'Saving...';

                const params = new URLSearchParams(payload);
                params.set('csrf_token', csrfToken);
                params.set('complaint_id', String(complaintId));

                try {
                    const response = await fetch('complaint_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: params.toString()
                    });

                    if (!response.ok) {
                        throw new Error('Action failed with HTTP ' + response.status);
                    }

                    const data = await response.json();
                    if (!data.success) {
                        throw new Error(data.message || 'Unable to complete action.');
                    }

                    quickActionFeedback.textContent = data.message || 'Saved successfully.';
                    return data;
                } catch (error) {
                    quickActionFeedback.textContent = error.message || 'Unable to complete action.';
                    return null;
                } finally {
                    setBusyState(false);
                }
            }

            function prependUpdateCard(updateText) {
                const noUpdates = document.getElementById('noUpdatesText');
                if (noUpdates) {
                    noUpdates.remove();
                }

                const safeText = updateText
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\n/g, '<br>');
                const now = new Date();
                const card = document.createElement('div');
                card.className = 'update-card';
                card.innerHTML =
                    '<div class="update-header">'
                    + '<span>Updated by: ' + currentAdminName + '</span>'
                    + '<span>' + formatDate(now) + '</span>'
                    + '</div>'
                    + '<p>' + safeText + '</p>';
                updatesList.prepend(card);
            }

            function prependStatusHistory(oldStatus, newStatus) {
                const noHistory = document.getElementById('noStatusHistoryText');
                if (noHistory) {
                    noHistory.remove();
                }

                const now = new Date();
                const card = document.createElement('div');
                card.className = 'history-card';
                card.innerHTML =
                    '<strong>' + formatStatus(oldStatus) + ' -> ' + formatStatus(newStatus) + '</strong>'
                    + '<div class="history-meta">'
                    + '<span>Changed by: ' + currentAdminName + '</span>'
                    + '<span>' + formatDate(now) + '</span>'
                    + '</div>';
                statusHistoryList.prepend(card);
            }

            function updateStatusBadge(newStatus) {
                if (!statusBadge) {
                    return;
                }

                statusBadge.classList.remove('status-pending', 'status-in_progress', 'status-resolved', 'status-in-progress');
                statusBadge.classList.add('status-' + newStatus);
                statusBadge.textContent = formatStatus(newStatus);
                statusBadge.setAttribute('data-status', newStatus);
            }

            markInProgressBtn.addEventListener('click', async function () {
                const currentStatus = statusBadge.getAttribute('data-status') || 'pending';
                const note = quickUpdateText.value.trim();
                const result = await sendAction({
                    action: 'mark_in_progress',
                    update_text: note
                });

                if (!result) {
                    return;
                }

                updateStatusBadge('in_progress');
                prependStatusHistory(currentStatus, 'in_progress');
                prependUpdateCard(note || 'Status updated to in progress.');
                quickUpdateText.value = '';
            });

            resolveBtn.addEventListener('click', async function () {
                const currentStatus = statusBadge.getAttribute('data-status') || 'pending';
                const note = quickUpdateText.value.trim();
                const result = await sendAction({
                    action: 'resolve',
                    update_text: note
                });

                if (!result) {
                    return;
                }

                updateStatusBadge('resolved');
                prependStatusHistory(currentStatus, 'resolved');
                prependUpdateCard(note || 'Status updated to resolved.');
                quickUpdateText.value = '';
            });

            addUpdateBtn.addEventListener('click', async function () {
                const note = quickUpdateText.value.trim();
                if (!note) {
                    quickActionFeedback.textContent = 'Please enter update text before adding an update.';
                    quickUpdateText.focus();
                    return;
                }

                const result = await sendAction({
                    action: 'add_update',
                    update_text: note
                });

                if (!result) {
                    return;
                }

                prependUpdateCard(note);
                quickUpdateText.value = '';
            });
        }());
    </script>
</body>
</html> 