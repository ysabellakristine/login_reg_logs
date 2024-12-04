<?php 
require_once 'main/models.php'; 
require_once 'main/dbConfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");

    exit; // Ensure no further code is executed
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application Form</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Welcome to Job Application for Nurses! <br> Apply Below</h1>
    <form action="main/handleForms.php" method="POST">
        <p>
            <label for="first_name">First Name</label> 
            <input type="text" name="first_name" required>
        </p>
        <p>
            <label for="last_name">Last Name</label> 
            <input type="text" name="last_name" required>
        </p>
        <p>
            <label for="email">Email</label> 
            <input type="text" name="email" required>
        </p>
        <p>
            <label for="contact_no">Contact Number</label>
            <input type="text" name="contact_no" required>
        </p>
        <p>
            <label for="gender">Gender</label>
            <select name="gender" required>
                <option value="">--Select Gender--</option>
                <?php
                $genders = ["Male", "Female", "Nonbinary", "Secret", "Helicopter", "ML player"];
                foreach ($genders as $gender) {
                    echo "<option value='".htmlspecialchars($gender)."'>".htmlspecialchars($gender)."</option>";
                }
                ?>
            </select>
        </p>
        <p>
            <label for="date_of_birth">Date of Birth</label> 
            <input type="date" name="date_of_birth" required>
        </p>
        <p>
            <label for="qualification">Qualification</label>
            <select name="qualification" required>
                <option value="">--Qualification--</option>
                <?php
                $qualifications = ["Just graduated", "1-3 years", "4-6 years", "7 years and above"];
                foreach ($qualifications as $qualification) {
                    echo "<option value='".htmlspecialchars($qualification)."'>".htmlspecialchars($qualification)."</option>";
                }
                ?>
            </select>
        </p>
        <p>
            <label for="application_status">Application Status</label>
            <select id="application_status" name="application_status">
                <?php 
                
                $statuses = ["Pending", "Accepted", "Rejected"];
                foreach ($statuses as $status) {
                    // Check if $application_status matches the current option to make it selected
                    $selected = ($status == $application_status) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($status) . "' $selected>" . htmlspecialchars($status) . "</option>";
                }
                ?>
            </select>
        </p>
        <p>
            <input type="submit" name="insertApplicationBtn" value="Insert Application"> </p>
            
    </form>
</div>
<hr><hr>
<hr>
<div class = "container">
    <h1> Want to search for known users? <br> Press the button bellow!</h1>
<a href="search_users.php" class="button">Search Users</a> 
<hr> 
<h1><br> Audit Logs</h1>
<a href="view_audit_logs.php" class = "button">View Audit Logs</a> <br>
<hr>
<h1> <br> Tired? Log out now 😘</h1>
<a href="logout.php" class="button">LOGOUT</a>
</div>
<hr>
<div class="table_container">
    <h2>Applications</h2>
    <table>
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Contact_no</th>
                <th>Gender</th>
                <th>Date of Birth</th>
                <th>Qualification</th>
                <th>Application Status</th>
                <th>Date Applied</th>
                <th>Last Applied</th>
                <th>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $getAllApplications = getAllApplications($pdo); 
            if (empty($getAllApplications)) {
                echo "<tr><td colspan='10'>No Applications found.</td></tr>";
            } else {
                foreach ($getAllApplications as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_of_birth']); ?></td>
                        <td><?php echo htmlspecialchars($row['qualification']); ?></td>
                        <td><?php echo htmlspecialchars($row['application_status']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_applied']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_updated']); ?></td>
                        <td>
                            <div class="editAndDelete">
                                <a href="view_application.php?applicant_id=<?php echo $row['applicant_id']; ?>">Edit</a>
                                <a href="deleteapplication.php?applicant_id=<?php echo $row['applicant_id']; ?>">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php }
            } ?>
        </tbody>
    </table>
</div>
</body>
</html>
