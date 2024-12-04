<?php 
require_once 'main/models.php'; 
require_once 'main/dbConfig.php'; 

// Check for user login
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
} else {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

// Sanitizing input
$applicant_id = isset($_GET['applicant_id']) ? intval($_GET['applicant_id']) : 0;

// Get toy reseller information
$getApplicationsByID = getApplicationsByID($pdo, $applicant_id);

// Check if the toy reseller exists
if (!$getApplicationsByID) {
    echo "Application not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Confirm Deletion</title>
	<link rel="stylesheet" href="styles.css">
</head>
<body>
	<h1>Are you sure you want to delete this user?</h1>
	<div class="container">
		<h2>First Name: <?php echo htmlspecialchars($getApplicationsByID['first_name']); ?></h2>
		<h2>Last Name: <?php echo htmlspecialchars($getApplicationsByID['last_name']); ?></h2>
		<h2>Email: <?php echo htmlspecialchars($getApplicationsByID['email']); ?></h2>
		<h2>Contact number: <?php echo htmlspecialchars($getApplicationsByID['contact_no']); ?></h2>
		<h2>Gender: <?php echo htmlspecialchars($getApplicationsByID['gender']); ?></h2>
		<h2>Date Of Birth: <?php echo htmlspecialchars($getApplicationsByID['date_of_birth']); ?></h2>
		<h2>Qualification: <?php echo htmlspecialchars($getApplicationsByID['qualification']); ?></h2>
		<h2>Application Status: <?php echo htmlspecialchars($getApplicationsByID['application_status']); ?></h2>
		<h2>Date Applied: <?php echo htmlspecialchars($getApplicationsByID['date_applied']); ?></h2>

		<div class="deleteBtn" style="float: right; margin-right: 10px;">
			<form action="main/handleForms.php?applicant_id=<?php echo $applicant_id; ?>" method="POST">
				<input type="submit" name="deleteApplicationBtn" value="Delete">
			</form>			
		</div>	
	</div>
</body>
</html>
