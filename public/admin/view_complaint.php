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
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260325b">
    <style>
        .complaint-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid rgba(14, 90, 102, 0.22);
            background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(247,250,253,0.9));
        }
        .complaint-details {
            background: rgba(14, 90, 102, 0.06);
            border: 1px solid rgba(14, 90, 102, 0.15);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .detail-row {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            margin-right: 10px;
        }
        .updates-section {
            margin-top: 30px;
        }
        .update-card {
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(31, 41, 51, 0.1);
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 12px;
        }
        .update-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #5f6d80;
        }
        .back-btn {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(180deg, #0e6d82, #0b5768);
            color: white;
            text-decoration: none;
            border-radius: 999px;
            margin-bottom: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            color: white;
            font-weight: 700;
        }
        .status-pending {
            background: linear-gradient(180deg, #d99906, #b47803);
        }
        .status-in_progress {
            background: linear-gradient(180deg, #1a8095, #0d6577);
        }
        .status-in-progress {
            background: linear-gradient(180deg, #1a8095, #0d6577);
        }
        .status-resolved {
            background: linear-gradient(180deg, #2f9b63, #1f7d4d);
        }
        .brand-logo {
            margin-bottom: 10px;
        }
        .brand-logo img {
            max-width: 220px;
            width: 100%;
            height: auto;
            filter: drop-shadow(0 8px 18px rgba(24, 42, 62, 0.2));
        }
        .update-form {
            margin-top: 12px;
            padding: 14px;
            border-radius: 12px;
            background: rgba(179, 27, 52, 0.06);
            border: 1px solid rgba(179, 27, 52, 0.16);
        }
        .quick-actions {
            margin-top: 16px;
            padding: 14px;
            border-radius: 12px;
            background: rgba(14, 90, 102, 0.06);
            border: 1px solid rgba(14, 90, 102, 0.16);
        }
        .quick-actions-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .quick-actions-row button {
            border: none;
            border-radius: 999px;
            padding: 10px 14px;
            color: #fff;
            cursor: pointer;
            font-weight: 700;
        }
        .action-progress {
            background: linear-gradient(180deg, #0e6d82, #0b5768);
        }
        .action-resolve {
            background: linear-gradient(180deg, #2f9b63, #1f7d4d);
        }
        .action-update {
            background: linear-gradient(180deg, #b31b34, #8f162a);
        }
        .quick-actions textarea,
        .update-form textarea {
            width: 100%;
            border-radius: 10px;
            border: 1px solid rgba(31, 41, 51, 0.15);
            padding: 10px;
        }
        .action-feedback {
            min-height: 24px;
            margin-top: 10px;
            font-weight: 700;
            color: #174e2d;
        }
        .history-section {
            margin-top: 26px;
        }
        .history-card {
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(31, 41, 51, 0.1);
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 10px;
        }
        .history-meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            color: #5f6d80;
            margin-top: 6px;
            flex-wrap: wrap;
        }
        button:focus-visible,
        a:focus-visible,
        textarea:focus-visible {
            outline: 3px solid #0e5a66;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="complaint-container">
        <div class="brand-logo">
            <img src="../assets/nmims_logo.jpg" alt="NMIMS University Logo">
        </div>
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="complaint-details">
            <h2>Complaint Details</h2>
            
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
            
            <div class="detail-row">
                <span class="detail-label">Complaint Details:</span>
                <p><?php echo nl2br(htmlspecialchars($complaint['complaint_details'])); ?></p>
            </div>
        </div>

        <div class="updates-section">
            <h3>Updates</h3>
            
            <div id="updatesList">
                <?php if (empty($updates)): ?>
                    <p id="noUpdatesText">No updates available for this complaint.</p>
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

        <div class="history-section">
            <h3>Status History</h3>
            <div id="statusHistoryList">
                <?php if (empty($statusHistory)): ?>
                    <p id="noStatusHistoryText">No status changes recorded yet.</p>
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