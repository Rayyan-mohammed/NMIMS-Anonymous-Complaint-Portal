<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Director Complaints Dashboard</title>
    <link rel="stylesheet" href="cd.css">
</head>
<body>
    <!-- Loader -->
    <div class="loader" id="loader">
        <div class="logo-container">
            <img src="https://iili.io/3K16L6F.md.jpg" alt="NMIMS Logo" class="logo-img"> <!-- Replace with actual logo path -->
            <div class="logo-text">NMIMS</div>
            <div class="logo-subtext">Anonymous Complaint Portal</div>
        </div>
    </div>

    <!-- Glowing Cursor -->
    <div class="cursor-glow" id="cursorGlow"></div>

    <!-- Navigation -->
    <div class="navbar">
        <a href="#" class="navbar-brand">
            <img src="https://iili.io/3K16L6F.md.jpg" alt="NMIMS Logo"> <!-- Replace with actual logo path -->
        </a>
    </div>

    <div class="container">
        <h1>Campus Director Complaints Dashboard</h1>

        <!-- Direct Complaints Section -->
        <div class="section" id="directComplaints">
            <h2 class="section-title">Direct Complaints</h2>
            <div class="table-container">
                <?php
                $host = "localhost";
                $user = "root";
                $password = "";
                $database = "campus_director";

                $conn = new mysqli($host, $user, $password, $database);
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT reference_number, complaint_details, status, submitted_at, comment FROM campus_director_complaints";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<table><tr><th>Reference</th><th>Details</th><th>Status</th><th>Comment</th><th>Submitted At</th><th>Update Status</th></tr>";
                    while ($row = $result->fetch_assoc()) {
                        $statusClass = "status-" . strtolower(str_replace(" ", "-", $row['status']));
                        echo "<tr>";
                        echo "<td>" . $row['reference_number'] . "</td>";
                        echo "<td>" . $row['complaint_details'] . "</td>";
                        echo "<td><span class='status $statusClass'>" . $row['status'] . "</span></td>";
                        echo "<td><textarea class='comment-textarea' data-ref='" . $row['reference_number'] . "' data-table='campus_director_complaints' data-database='campus_director'>" . htmlspecialchars($row['comment'] ?? '') . "</textarea></td>";
                        echo "<td>" . $row['submitted_at'] . "</td>";
                        echo "<td>";
                        echo "<select onchange=\"updateStatus('campus_director_complaints', '" . $row['reference_number'] . "', this.value, 'campus_director')\">";
                        echo "<option value='Pending'" . ($row['status'] === "Pending" ? " selected" : "") . ">Pending</option>";
                        echo "<option value='In Progress'" . ($row['status'] === "In Progress" ? " selected" : "") . ">In Progress</option>";
                        echo "<option value='Resolved'" . ($row['status'] === "Resolved" ? " selected" : "") . ">Resolved</option>";
                        echo "</select>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No direct complaints found.</p>";
                }
                $conn->close();
                ?>
            </div>
        </div>

        <!-- All School Complaints Section -->
        <div class="section" id="schoolComplaints">
            <h2 class="section-title">All School Complaints</h2>
            <div class="table-container">
                <?php
                $schools = ["stme", "sbm", "sol", "sptm"];
                $authorities = ["program_chair_complaints" => "Program Chair", "deputy_registrar_complaints" => "Deputy Registrar"];

                echo "<table><tr><th>School</th><th>Authority</th><th>Reference</th><th>Details</th><th>Status</th><th>Comment</th><th>Submitted At</th><th>Update Status</th></tr>";
                foreach ($schools as $school) {
                    $conn = new mysqli($host, $user, $password, $school);
                    if ($conn->connect_error) continue;

                    foreach ($authorities as $table => $authority) {
                        $sql = "SELECT reference_number, complaint_details, status, submitted_at, comment FROM $table";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $statusClass = "status-" . strtolower(str_replace(" ", "-", $row['status']));
                                echo "<tr>";
                                echo "<td>" . strtoupper($school) . "</td>";
                                echo "<td>" . $authority . "</td>";
                                echo "<td>" . $row['reference_number'] . "</td>";
                                echo "<td>" . $row['complaint_details'] . "</td>";
                                echo "<td><span class='status $statusClass'>" . $row['status'] . "</span></td>";
                                echo "<td><textarea class='comment-textarea' data-ref='" . $row['reference_number'] . "' data-table='$table' data-database='$school'>" . htmlspecialchars($row['comment'] ?? '') . "</textarea></td>";
                                echo "<td>" . $row['submitted_at'] . "</td>";
                                echo "<td>";
                                echo "<select onchange=\"updateStatus('$table', '" . $row['reference_number'] . "', this.value, '$school')\">";
                                echo "<option value='Pending'" . ($row['status'] === "Pending" ? " selected" : "") . ">Pending</option>";
                                echo "<option value='In Progress'" . ($row['status'] === "In Progress" ? " selected" : "") . ">In Progress</option>";
                                echo "<option value='Resolved'" . ($row['status'] === "Resolved" ? " selected" : "") . ">Resolved</option>";
                                echo "</select>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        }
                    }
                    $conn->close();
                }
                echo "</table>";
                ?>
            </div>
        </div>
    </div>

    <!-- Visualizations Section -->
    <div class="section" id="visualizations">
        <h2 class="section-title">Complaints Analytics</h2>
        <div class="charts-container">
            <div class="chart-wrapper">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="chart-wrapper">
                <canvas id="schoolChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-container">
        <p>2025 &copy; STME All rights reserved.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Loader Hide
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader').classList.add('hidden');
                document.querySelectorAll('.section').forEach(section => {
                    section.classList.add('visible');
                });
                initializeCharts();
            }, 2000);
        });

        // Glowing Cursor
        const cursorGlow = document.getElementById('cursorGlow');
        document.addEventListener('mousemove', (e) => {
            cursorGlow.style.left = e.pageX + 'px';
            cursorGlow.style.top = e.pageY + 'px';
        });

        // Status Update Function
        function updateStatus(table, ref, newStatus, database) {
            const textarea = document.querySelector(`textarea[data-ref="${ref}"]`);
            if (!textarea) {
                console.error('Could not find comment textarea');
                return;
            }
            const comment = textarea.value;

            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `table=${encodeURIComponent(table)}&reference_number=${encodeURIComponent(ref)}&status=${encodeURIComponent(newStatus)}&database=${encodeURIComponent(database)}&comment=${encodeURIComponent(comment)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    throw new Error(data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Error updating status: " + error.message);
            });
        }

        // Comment update handler
        document.querySelectorAll('.comment-textarea').forEach(textarea => {
            textarea.addEventListener('change', function() {
                const ref = this.getAttribute('data-ref');
                const table = this.getAttribute('data-table');
                const database = this.getAttribute('data-database');
                const row = this.closest('tr');
                const statusSelect = row ? row.querySelector('select') : null;
                const currentStatus = statusSelect ? statusSelect.value : 'Pending';
                
                updateStatus(table, ref, currentStatus, database);
            });
        });

        // Visualization Functions
        function initializeCharts() {
            <?php
            // Collect data for status distribution
            $statusData = [
                'Pending' => 0,
                'In Progress' => 0,
                'Resolved' => 0
            ];

            // Collect data for school distribution
            $schoolData = [
                'STME' => 0,
                'SBM' => 0,
                'SOL' => 0,
                'SPTM' => 0,
                'Campus Director' => 0
            ];

            // Database connection
            $host = "localhost";
            $user = "root";
            $password = "";
            
            // Count direct complaints
            $conn = new mysqli($host, $user, $password, "campus_director");
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT status FROM campus_director_complaints";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $statusData[$row['status']]++;
                }
            }
            $conn->close();

            // Function to count complaints
            function countComplaints($conn, $table) {
                $sql = "SELECT COUNT(*) as count FROM $table";
                $result = $conn->query($sql);
                return $result ? $result->fetch_assoc()['count'] : 0;
            }

            // Count school complaints
            $schools = ["stme", "sbm", "sol", "sptm"];
            $authorities = ["program_chair_complaints", "deputy_registrar_complaints"];

            foreach ($schools as $school) {
                $conn = new mysqli($host, $user, $password, $school);
                if ($conn->connect_error) continue;

                foreach ($authorities as $table) {
                    $count = countComplaints($conn, $table);
                    $schoolData[strtoupper($school)] += $count;
                    
                    // Count status
                    $sql = "SELECT status, COUNT(*) as count FROM $table GROUP BY status";
                    $result = $conn->query($sql);
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            if (isset($statusData[$row['status']])) {
                                $statusData[$row['status']] += $row['count'];
                            }
                        }
                    }
                }
                $conn->close();
            }

            // Count campus director complaints
            $conn = new mysqli($host, $user, $password, "campus_director");
            if (!$conn->connect_error) {
                $sql = "SELECT reference_number, status FROM campus_director_complaints";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $statusData[$row['status']]++;
                        $prefix = substr($row['reference_number'], 0, 3);
                        switch ($prefix) {
                            case 'STM':
                                $schoolData['STME']++;
                                break;
                            case 'SBM':
                                $schoolData['SBM']++;
                                break;
                            case 'SOL':
                                $schoolData['SOL']++;
                                break;
                            case 'SPT':
                                $schoolData['SPTM']++;
                                break;
                            case 'CAM': // Campus Director specific complaints
                                $schoolData['Campus Director']++;
                                break;
                        }
                    }
                }
                $conn->close();
            }

            // Debug output
            error_log("Final School Counts: " . print_r($schoolData, true));
            error_log("Final Status Counts: " . print_r($statusData, true));

            // Convert PHP arrays to JavaScript arrays
            echo "const statusCounts = [" . 
                $statusData['Pending'] . ", " . 
                $statusData['In Progress'] . ", " . 
                $statusData['Resolved'] . "];\n";
            
            echo "const schoolCounts = [" . 
                $schoolData['STME'] . ", " . 
                $schoolData['SBM'] . ", " . 
                $schoolData['SOL'] . ", " . 
                $schoolData['SPTM'] . ", " .
                $schoolData['Campus Director'] . "];\n";

            // Add debug output to console
            echo "console.log('School Counts:', " . json_encode($schoolData) . ");\n";
            echo "console.log('Status Counts:', " . json_encode($statusData) . ");\n";
            ?>

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusData = {
                labels: ['Pending', 'In Progress', 'Resolved'],
                datasets: [{
                    data: statusCounts,
                    backgroundColor: ['#ff6384', '#36a2eb', '#4bc0c0']
                }]
            };
            new Chart(statusCtx, {
                type: 'pie',
                data: statusData,
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Complaints by Status'
                        }
                    }
                }
            });

            // School Distribution Chart
            const schoolCtx = document.getElementById('schoolChart').getContext('2d');
            const schoolData = {
                labels: ['STME', 'SBM', 'SOL', 'SPTM', 'Campus Director'],
                datasets: [{
                    data: schoolCounts,
                    backgroundColor: ['#ff9f40', '#ffcd56', '#4bc0c0', '#9966ff', '#ff6384']
                }]
            };
            new Chart(schoolCtx, {
                type: 'bar',
                data: schoolData,
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Complaints by School'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>