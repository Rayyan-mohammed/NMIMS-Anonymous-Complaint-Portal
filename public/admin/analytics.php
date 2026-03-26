<?php
session_start();
require_once '../config/database.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Support\AuthGuard;
use App\Services\ComplaintService;

$redirect = AuthGuard::requireRole($_SESSION, ['program_chair', 'deputy_registrar', 'hostel_authority', 'campus_director']);
if ($redirect !== null) {
    header('Location: ' . $redirect);
    exit();
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$role = (string) ($_SESSION['role'] ?? '');
$schoolId = isset($_SESSION['school_id']) ? (int) $_SESSION['school_id'] : null;

$complaintService = new ComplaintService($conn);
$analytics = $complaintService->getAssignedAnalytics($userId, $role, $schoolId);
$schoolName = $complaintService->getSchoolName($schoolId);

$totalAssigned = max(1, (int) ($analytics['total_assigned'] ?? 0));
$resolutionRate = (int) round((((int) ($analytics['resolved_count'] ?? 0)) / $totalAssigned) * 100);
$pendingRate = (int) round((((int) ($analytics['pending_count'] ?? 0)) / $totalAssigned) * 100);
$inProgressRate = (int) round((((int) ($analytics['in_progress_count'] ?? 0)) / $totalAssigned) * 100);

$monthlyTrend = $analytics['monthly_trend'] ?? [];
$trendLabels = array_map(static fn($item) => (string) ($item['month_label'] ?? ''), $monthlyTrend);
$trendCounts = array_map(static fn($item) => (int) ($item['count'] ?? 0), $monthlyTrend);

$schoolBreakdown = $analytics['school_breakdown'] ?? [];
$schoolLabels = array_map(static fn($item) => (string) ($item['school_name'] ?? 'Unknown'), $schoolBreakdown);
$schoolCounts = array_map(static fn($item) => (int) ($item['total_count'] ?? 0), $schoolBreakdown);

$subtypeBreakdown = $analytics['subtype_breakdown'] ?? [];
$subtypeLabels = array_map(
    static fn($item) => ucwords(str_replace('_', ' ', (string) ($item['subtype_name'] ?? 'Not Specified'))),
    $subtypeBreakdown
);
$subtypeCounts = array_map(static fn($item) => (int) ($item['total_count'] ?? 0), $subtypeBreakdown);

$statusValues = [
    (int) ($analytics['pending_count'] ?? 0),
    (int) ($analytics['in_progress_count'] ?? 0),
    (int) ($analytics['resolved_count'] ?? 0),
];

$typeValues = [
    (int) ($analytics['academic_count'] ?? 0),
    (int) ($analytics['hostel_count'] ?? 0),
];

$identityValues = [
    (int) ($analytics['anonymous_count'] ?? 0),
    (int) ($analytics['identified_count'] ?? 0),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - NMIMS Complaint Portal</title>
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260326i">
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body class="admin-analytics-page">
    <div class="dashboard-container analytics-page-shell">
        <header class="dashboard-topbar">
            <div class="dashboard-topbar-left">
                <div class="brand-logo dashboard-brand-logo">
                    <img src="../assets/nmims_logo.jpg" alt="NMIMS University Logo">
                </div>
                <div class="dashboard-headline">
                    <h1>Admin Analytics</h1>
                    <p>
                        <?php echo ucwords(str_replace('_', ' ', $role)); ?>
                        <?php if ($schoolName !== ''): ?>
                            • <?php echo htmlspecialchars($schoolName); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="header-actions dashboard-topbar-actions">
                <a class="analytics-btn" href="analytics.php">Analytics</a>
                <a class="profile-btn" href="profile.php">Profile</a>
                <a class="back-btn" href="dashboard.php">Dashboard</a>
                <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
            </div>
        </header>

        <section class="analytics-section analytics-section-page" aria-labelledby="analyticsPageHeading">
            <div class="analytics-head">
                <h2 id="analyticsPageHeading">Complete Assignment Analytics</h2>
                <p>Performance, workload, and complaint flow trends for your assigned queue.</p>
            </div>

            <div class="analytics-meta-strip" aria-label="Queue insights">
                <article>
                    <span>Open Queue</span>
                    <strong><?php echo ((int) ($analytics['pending_count'] ?? 0) + (int) ($analytics['in_progress_count'] ?? 0)); ?></strong>
                    <small>Pending + In Progress</small>
                </article>
                <article>
                    <span>Avg Open Age</span>
                    <strong><?php echo (int) ($analytics['avg_open_days'] ?? 0); ?> days</strong>
                    <small>For unresolved complaints</small>
                </article>
                <article>
                    <span>Weekly Intake</span>
                    <strong><?php echo (int) ($analytics['created_this_week'] ?? 0); ?></strong>
                    <small>New complaints this week</small>
                </article>
                <article>
                    <span>30-Day Velocity</span>
                    <strong><?php echo (int) round(((int) ($analytics['created_last_30_days'] ?? 0)) / 30); ?>/day</strong>
                    <small>Average daily creation</small>
                </article>
            </div>

            <div class="analytics-kpis analytics-kpis-page">
                <article class="analytics-kpi-card"><span>Total Assigned</span><strong><?php echo (int) ($analytics['total_assigned'] ?? 0); ?></strong></article>
                <article class="analytics-kpi-card"><span>Resolved</span><strong><?php echo (int) ($analytics['resolved_count'] ?? 0); ?></strong></article>
                <article class="analytics-kpi-card"><span>Resolution Rate</span><strong><?php echo $resolutionRate; ?>%</strong></article>
                <article class="analytics-kpi-card"><span>Avg Resolution (hrs)</span><strong><?php echo (int) ($analytics['avg_resolution_hours'] ?? 0); ?></strong></article>
                <article class="analytics-kpi-card"><span>Created Today</span><strong><?php echo (int) ($analytics['created_today'] ?? 0); ?></strong></article>
                <article class="analytics-kpi-card"><span>Created This Week</span><strong><?php echo (int) ($analytics['created_this_week'] ?? 0); ?></strong></article>
            </div>

            <div class="analytics-charts-grid" aria-label="Visual analytics charts">
                <article class="analytics-chart-card analytics-chart-wide">
                    <div class="analytics-chart-head">
                        <h4>Monthly Assignment Trend</h4>
                        <p>Complaint assignment volume over the last 6 months.</p>
                    </div>
                    <div class="chart-wrap"><canvas id="monthlyTrendChart" aria-label="Monthly assignment trend chart"></canvas></div>
                </article>

                <article class="analytics-chart-card">
                    <div class="analytics-chart-head">
                        <h4>Status Distribution</h4>
                        <p>Current load split by processing stage.</p>
                    </div>
                    <div class="chart-wrap"><canvas id="statusChart" aria-label="Status distribution chart"></canvas></div>
                </article>

                <article class="analytics-chart-card">
                    <div class="analytics-chart-head">
                        <h4>Complaint Categories</h4>
                        <p>Academic versus hostel complaint share.</p>
                    </div>
                    <div class="chart-wrap"><canvas id="typeChart" aria-label="Complaint type chart"></canvas></div>
                </article>

                <article class="analytics-chart-card">
                    <div class="analytics-chart-head">
                        <h4>Identity Pattern</h4>
                        <p>Anonymous and identified complaint balance.</p>
                    </div>
                    <div class="chart-wrap"><canvas id="identityChart" aria-label="Identity pattern chart"></canvas></div>
                </article>

                <article class="analytics-chart-card">
                    <div class="analytics-chart-head">
                        <h4>Top Schools (Assigned)</h4>
                        <p>School-wise complaint concentration in your queue.</p>
                    </div>
                    <div class="chart-wrap"><canvas id="schoolChart" aria-label="School distribution chart"></canvas></div>
                </article>

                <article class="analytics-chart-card">
                    <div class="analytics-chart-head">
                        <h4>Top Complaint Subtypes</h4>
                        <p>Most frequent issue subtypes across assignments.</p>
                    </div>
                    <div class="chart-wrap"><canvas id="subtypeChart" aria-label="Complaint subtype chart"></canvas></div>
                </article>
            </div>

            <div class="analytics-grid analytics-grid-page">
                <article class="analytics-card">
                    <h4>Status Distribution</h4>
                    <ul>
                        <li><span>Pending</span><strong><?php echo (int) ($analytics['pending_count'] ?? 0); ?> (<?php echo $pendingRate; ?>%)</strong></li>
                        <li><span>In Progress</span><strong><?php echo (int) ($analytics['in_progress_count'] ?? 0); ?> (<?php echo $inProgressRate; ?>%)</strong></li>
                        <li><span>Resolved</span><strong><?php echo (int) ($analytics['resolved_count'] ?? 0); ?> (<?php echo $resolutionRate; ?>%)</strong></li>
                    </ul>
                </article>

                <article class="analytics-card">
                    <h4>Complaint Mix</h4>
                    <ul>
                        <li><span>Academic</span><strong><?php echo (int) ($analytics['academic_count'] ?? 0); ?></strong></li>
                        <li><span>Hostel</span><strong><?php echo (int) ($analytics['hostel_count'] ?? 0); ?></strong></li>
                        <li><span>Anonymous</span><strong><?php echo (int) ($analytics['anonymous_count'] ?? 0); ?></strong></li>
                        <li><span>Identified</span><strong><?php echo (int) ($analytics['identified_count'] ?? 0); ?></strong></li>
                    </ul>
                </article>

                <article class="analytics-card">
                    <h4>Operational Window</h4>
                    <ul>
                        <li><span>Created Last 30 Days</span><strong><?php echo (int) ($analytics['created_last_30_days'] ?? 0); ?></strong></li>
                        <li><span>Total Active (Pending + In Progress)</span><strong><?php echo ((int) ($analytics['pending_count'] ?? 0) + (int) ($analytics['in_progress_count'] ?? 0)); ?></strong></li>
                        <li><span>Average Daily Intake (30 Days)</span><strong><?php echo (int) round(((int) ($analytics['created_last_30_days'] ?? 0)) / 30); ?></strong></li>
                        <li><span>Average Open Days</span><strong><?php echo (int) ($analytics['avg_open_days'] ?? 0); ?></strong></li>
                    </ul>
                </article>

                <article class="analytics-card analytics-card-trend">
                    <h4>Monthly Trend (Last 6 Months)</h4>
                    <table class="analytics-trend-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Assigned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthlyTrend as $month): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string) ($month['month_label'] ?? '')); ?></td>
                                    <td><?php echo (int) ($month['count'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>

                <article class="analytics-card analytics-card-trend">
                    <h4>Top School Breakdown</h4>
                    <table class="analytics-trend-table">
                        <thead>
                            <tr>
                                <th>School</th>
                                <th>Assigned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schoolBreakdown as $school): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string) ($school['school_name'] ?? 'Unknown')); ?></td>
                                    <td><?php echo (int) ($school['total_count'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>

                <article class="analytics-card analytics-card-trend">
                    <h4>Top Subtype Breakdown</h4>
                    <table class="analytics-trend-table">
                        <thead>
                            <tr>
                                <th>Subtype</th>
                                <th>Assigned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subtypeBreakdown as $subtype): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($subtype['subtype_name'] ?? 'Not Specified')))); ?></td>
                                    <td><?php echo (int) ($subtype['total_count'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>
            </div>
        </section>
    </div>
    <script>
        const chartPalette = {
            red: '#ba0c2f',
            rose: '#e11d48',
            coral: '#fb7185',
            blue: '#1d4ed8',
            cyan: '#0e7490',
            teal: '#0f766e',
            amber: '#d97706',
            slate: '#475569'
        };

        const monthlyTrendData = {
            labels: <?php echo json_encode($trendLabels, JSON_UNESCAPED_UNICODE); ?>,
            datasets: [{
                label: 'Assigned Complaints',
                data: <?php echo json_encode($trendCounts, JSON_UNESCAPED_UNICODE); ?>,
                borderColor: chartPalette.red,
                backgroundColor: 'rgba(186, 12, 47, 0.12)',
                fill: true,
                tension: 0.34,
                pointRadius: 4,
                pointBackgroundColor: chartPalette.red
            }]
        };

        const statusData = {
            labels: ['Pending', 'In Progress', 'Resolved'],
            datasets: [{
                data: <?php echo json_encode($statusValues, JSON_UNESCAPED_UNICODE); ?>,
                backgroundColor: [chartPalette.amber, chartPalette.rose, chartPalette.teal],
                borderWidth: 0
            }]
        };

        const typeData = {
            labels: ['Academic', 'Hostel'],
            datasets: [{
                data: <?php echo json_encode($typeValues, JSON_UNESCAPED_UNICODE); ?>,
                backgroundColor: [chartPalette.blue, chartPalette.cyan],
                borderWidth: 0
            }]
        };

        const identityData = {
            labels: ['Anonymous', 'Identified'],
            datasets: [{
                data: <?php echo json_encode($identityValues, JSON_UNESCAPED_UNICODE); ?>,
                backgroundColor: [chartPalette.slate, chartPalette.red],
                borderWidth: 0
            }]
        };

        const schoolData = {
            labels: <?php echo json_encode($schoolLabels, JSON_UNESCAPED_UNICODE); ?>,
            datasets: [{
                label: 'Assigned',
                data: <?php echo json_encode($schoolCounts, JSON_UNESCAPED_UNICODE); ?>,
                backgroundColor: 'rgba(29, 78, 216, 0.84)',
                borderRadius: 8,
                maxBarThickness: 34
            }]
        };

        const subtypeData = {
            labels: <?php echo json_encode($subtypeLabels, JSON_UNESCAPED_UNICODE); ?>,
            datasets: [{
                label: 'Assigned',
                data: <?php echo json_encode($subtypeCounts, JSON_UNESCAPED_UNICODE); ?>,
                backgroundColor: 'rgba(14, 116, 144, 0.85)',
                borderRadius: 8,
                maxBarThickness: 34
            }]
        };

        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        boxWidth: 12,
                        color: '#334155',
                        font: { size: 12, weight: '600' }
                    }
                }
            }
        };

        function renderFallbackBars(canvasId, labels, values, color) {
            const canvas = document.getElementById(canvasId);
            if (!canvas || !canvas.parentElement) {
                return;
            }

            const maxValue = Math.max(...values, 1);
            const fallback = document.createElement('div');
            fallback.className = 'chart-fallback';

            labels.forEach((label, index) => {
                const value = Number(values[index] ?? 0);
                const row = document.createElement('div');
                row.className = 'chart-fallback-row';

                const labelEl = document.createElement('span');
                labelEl.className = 'chart-fallback-label';
                labelEl.textContent = String(label);

                const trackEl = document.createElement('div');
                trackEl.className = 'chart-fallback-track';

                const barEl = document.createElement('i');
                barEl.style.width = `${Math.max((value / maxValue) * 100, value > 0 ? 10 : 0)}%`;
                barEl.style.background = color;
                trackEl.appendChild(barEl);

                const valueEl = document.createElement('strong');
                valueEl.className = 'chart-fallback-value';
                valueEl.textContent = String(value);

                row.appendChild(labelEl);
                row.appendChild(trackEl);
                row.appendChild(valueEl);
                fallback.appendChild(row);
            });

            canvas.style.display = 'none';
            canvas.parentElement.appendChild(fallback);
        }

        if (typeof window.Chart === 'undefined') {
            renderFallbackBars('monthlyTrendChart', monthlyTrendData.labels, monthlyTrendData.datasets[0].data, chartPalette.red);
            renderFallbackBars('statusChart', statusData.labels, statusData.datasets[0].data, chartPalette.rose);
            renderFallbackBars('typeChart', typeData.labels, typeData.datasets[0].data, chartPalette.blue);
            renderFallbackBars('identityChart', identityData.labels, identityData.datasets[0].data, chartPalette.slate);
            renderFallbackBars('schoolChart', schoolData.labels, schoolData.datasets[0].data, chartPalette.blue);
            renderFallbackBars('subtypeChart', subtypeData.labels, subtypeData.datasets[0].data, chartPalette.cyan);
        } else {
            new Chart(document.getElementById('monthlyTrendChart'), {
                type: 'line',
                data: monthlyTrendData,
                options: {
                    ...baseOptions,
                    plugins: {
                        ...baseOptions.plugins,
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#475569' },
                            grid: { color: 'rgba(148, 163, 184, 0.2)' }
                        },
                        x: {
                            ticks: { color: '#475569' },
                            grid: { display: false }
                        }
                    }
                }
            });

            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: statusData,
                options: {
                    ...baseOptions,
                    cutout: '62%'
                }
            });

            new Chart(document.getElementById('typeChart'), {
                type: 'pie',
                data: typeData,
                options: baseOptions
            });

            new Chart(document.getElementById('identityChart'), {
                type: 'doughnut',
                data: identityData,
                options: {
                    ...baseOptions,
                    cutout: '58%'
                }
            });

            new Chart(document.getElementById('schoolChart'), {
                type: 'bar',
                data: schoolData,
                options: {
                    ...baseOptions,
                    indexAxis: 'y',
                    plugins: {
                        ...baseOptions.plugins,
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#475569' },
                            grid: { color: 'rgba(148, 163, 184, 0.2)' }
                        },
                        y: {
                            ticks: { color: '#334155' },
                            grid: { display: false }
                        }
                    }
                }
            });

            new Chart(document.getElementById('subtypeChart'), {
                type: 'bar',
                data: subtypeData,
                options: {
                    ...baseOptions,
                    indexAxis: 'y',
                    plugins: {
                        ...baseOptions.plugins,
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#475569' },
                            grid: { color: 'rgba(148, 163, 184, 0.2)' }
                        },
                        y: {
                            ticks: { color: '#334155' },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
