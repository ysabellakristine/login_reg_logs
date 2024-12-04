<?php 
require_once 'main/handleForms.php';
require_once 'main/models.php'; 

// Check for user login
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
} else {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

// Fetch application data for the given applicant ID
if (isset($_GET['applicant_id'])) {
    $applicant_id = $_GET['applicant_id'];
    $getApplicationsByID = getApplicationsByID($pdo, $applicant_id); // Ensure this function fetches data

    // If no application is found, handle the error
    if (!$getApplicationsByID) {
        $error_message = "Application not found.";
    }
} else {
    $error_message = "Applicant ID is missing.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Application</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Edit the Application</h1>

    <!-- Display error message if exists -->
    <?php if (isset($error_message)): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form action="main/handleForms.php?applicant_id=<?php echo $applicant_id; ?>" method="POST">
        <p>
            <label for="first_name">First Name</label> 
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($getApplicationsByID['first_name']); ?>" required>
        </p>
        <p>
            <label for="last_name">Last Name</label> 
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($getApplicationsByID['last_name']); ?>" required>
        </p>
        <p>
            <label for="email">Email</label> 
            <input type="text" name="email" value="<?php echo htmlspecialchars($getApplicationsByID['email']); ?>" required>
        </p>
        <p>
            <label for="contact_no">Contact Number</label> 
            <input type="number" name="contact_no" value="<?php echo htmlspecialchars($getApplicationsByID['contact_no']); ?>" required>
        </p>
        <p>
            <label for="gender">Gender</label>
            <select name="gender" required>
                <option value="">--Select Gender--</option>
                <?php
                $genders = ["Male", "Female", "Nonbinary", "Secret", "Helicopter","ML Player"];
                foreach ($genders as $gender) {
                    $selected = ($gender === $getApplicationsByID['gender']) ? "selected" : "";
                    echo "<option value='" . htmlspecialchars($gender) . "' $selected>" . htmlspecialchars($gender) . "</option>";
                }
                ?>
            </select>
        </p>
        <p>
            <label for="date_of_birth">Date of Birth</label> 
            <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($getApplicationsByID['date_of_birth']); ?>" required>
        </p>
        <p>
            <label for="qualification">Qualification</label>
            <select name="qualification" required>
                <?php
                $qualifications = ["Just graduated", "1-3 years", "4-6 years", "7 years and above"];
                foreach ($qualifications as $qualification) {
                    // Check if the current qualification matches the fetched qualification
                    $selected = ($qualification === $getApplicationsByID['qualification']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($qualification) . "' $selected>" . htmlspecialchars($qualification) . "</option>";
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
                    // Check if $getApplicationsByID['application_status'] matches the current option to make it selected
                    $selected = ($status == $getApplicationsByID['application_status']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($status) . "' $selected>" . htmlspecialchars($status) . "</option>";
                }
                ?>
            </select>
        </p>

        <p>
            <input type="submit" name="editApplicationBtn" value="Edit">
        </p>
    </form>
</div>
<a href="index.php">Return to home</a>
<hr>
</body>
</html>
