<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deputy Registrar Dashboard - SBM</title>
    <link rel="stylesheet" href="pc.css">
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
        <h1>Deputy Registrar Dashboard - SBM</h1>

        <!-- Deputy Registrar Complaints Section -->
        <div class="section" id="deputyRegistrarComplaints">
            <h2 class="section-title">Deputy Registrar Complaints</h2>
            <div class="table-container">
                <?php
                $host = "localhost";
                $user = "root";
                $password = "";
                $database = "sbm"; // Change this for other schools (sbm, sol, sptm)

                $conn = new mysqli($host, $user, $password, $database);
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT reference_number, complaint_details, status, submitted_at, comment FROM deputy_registrar_complaints";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<table><tr><th>Reference</th><th>Details</th><th>Status</th><th>Comment</th><th>Submitted At</th><th>Update Status</th></tr>";
                    while ($row = $result->fetch_assoc()) {
                        $statusClass = "status-" . strtolower(str_replace(" ", "-", $row['status']));
                        echo "<tr>";
                        echo "<td>" . $row['reference_number'] . "</td>";
                        echo "<td>" . $row['complaint_details'] . "</td>";
                        echo "<td><span class='status $statusClass'>" . $row['status'] . "</span></td>";
                        echo "<td><textarea class='comment-textarea' data-ref='" . $row['reference_number'] . "' data-table='deputy_registrar_complaints' data-database='$database'>" . htmlspecialchars($row['comment'] ?? '') . "</textarea></td>";
                        echo "<td>" . $row['submitted_at'] . "</td>";
                        echo "<td>";
                        echo "<select onchange=\"updateStatus('deputy_registrar_complaints', '" . $row['reference_number'] . "', this.value, '$database')\">";
                        echo "<option value='Pending'" . ($row['status'] === "Pending" ? " selected" : "") . ">Pending</option>";
                        echo "<option value='In Progress'" . ($row['status'] === "In Progress" ? " selected" : "") . ">In Progress</option>";
                        echo "<option value='Resolved'" . ($row['status'] === "Resolved" ? " selected" : "") . ">Resolved</option>";
                        echo "</select>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No complaints found.</p>";
                }
                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-container">
        <<p>2025 &copy; STME All rights reserved.</p>
    </div>

    <script>
        // Loader Hide
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader').classList.add('hidden');
                document.querySelectorAll('.section').forEach(section => {
                    section.classList.add('visible');
                });
            }, 2000); // Delay to show loader
        });

        // Glowing Cursor
        const cursorGlow = document.getElementById('cursorGlow');
        document.addEventListener('mousemove', (e) => {
            cursorGlow.style.left = e.pageX + 'px';
            cursorGlow.style.top = e.pageY + 'px';
        });

        // Comment update functionality
        document.querySelectorAll('.comment-textarea').forEach(textarea => {
            textarea.addEventListener('change', function() {
                const ref = this.getAttribute('data-ref');
                const table = this.getAttribute('data-table');
                const database = this.getAttribute('data-database');
                const comment = this.value;

                fetch('update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `table=${table}&reference_number=${ref}&status=${document.querySelector(`select[data-ref="${ref}"]`).value}&database=${database}&comment=${encodeURIComponent(comment)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert("Error updating comment: " + data.message);
                    }
                });
            });
        });

        // Status Update Function
        function updateStatus(table, ref, newStatus, database) {
            const comment = document.querySelector(`textarea[data-ref="${ref}"]`).value;
            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `table=${table}&reference_number=${ref}&status=${newStatus}&database=${database}&comment=${encodeURIComponent(comment)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Error updating status: " + data.message);
                }
            });
        }
    </script>
</body>
</html>