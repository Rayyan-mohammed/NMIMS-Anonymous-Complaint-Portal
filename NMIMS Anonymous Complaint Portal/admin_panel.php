<?php
// admin_panel.php - Generic template for handling complaints

$host = "localhost";
$user = "root";
$password = "";
$dbname = $_GET['database']; // Database name passed dynamically
$table = $_GET['table']; // Table name passed dynamically

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $update_sql = "UPDATE $table SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
}

$sql = "SELECT * FROM $table";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
</head>
<body>
    <h2>Admin Panel for <?php echo $dbname; ?></h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Reference Number</th>
            <th>Complaint Details</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['reference_number']; ?></td>
            <td><?php echo $row['complaint_details']; ?></td>
            <td><?php echo $row['status']; ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <select name="status">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                    <input type="submit" value="Update">
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
<?php $conn->close(); ?>
