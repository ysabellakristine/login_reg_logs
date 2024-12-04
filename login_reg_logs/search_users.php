<?php 
require_once 'main/models.php'; 
require_once 'main/dbConfig.php';  // session_start() is already here

$searchResponse = $_SESSION['searchResponse'] ?? [
    "message" => "Enter search criteria to find users.",
    "statusCode" => 200,
    "querySet" => []
];

unset($_SESSION['searchResponse']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search for Users</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h1>Search for a User</h1>

    <form action="main/handleForms.php" method="POST">
        <p>
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" placeholder="Enter First Name">
        </p>
        <p>
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" placeholder="Enter Last Name">
        </p>
        <p>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter Email">
        </p>
        <p>
            <label for="contact_no">Contact No</label>
            <input type="text" id="contact_no" name="contact_no" placeholder="Enter Contact No">
        </p>
        <p>
            <label for="gender">Gender</label>
            <select name="gender">
                <option value="">--Select Gender--</option>
                <?php
                $genders = ["Male", "Female", "Nonbinary", "Secret", "Helicopter", "ML player"];
                foreach ($genders as $gender) {
                    echo "<option value='".htmlspecialchars($gender)."'>".htmlspecialchars($gender)."</option>";
                } ?>
            </select>
        </p>
        <p>
            <label for="qualification">Qualification</label>
            <select name="qualification">
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
                <option value="">--Application status--</option>
                <?php
                    $status = ["Pending", "Accepted", "Rejected"];
                    foreach ($status as $stat) {
                        echo "<option value='".htmlspecialchars($stat)."'>".htmlspecialchars($stat)."</option>";
                    }
                ?>
            </select>
        </p>
        
        <input type="submit" name = "searchUserBtn"value="Search for User">
    </form>

    <hr>

    <h2>Search Results</h2>
    <p><?php echo htmlspecialchars($searchResponse['message']); ?></p>
    
    <?php if (!empty($searchResponse['querySet'])): ?>
        <table border="1">
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Contact No</th>
                <th>Gender</th>
                <th>Qualification</th>
                <th>Application Status</th>
            </tr>
            <?php foreach ($searchResponse['querySet'] as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['contact_no']); ?></td>
                    <td><?php echo htmlspecialchars($user['gender']); ?></td>
                    <td><?php echo htmlspecialchars($user['qualification']); ?></td>
                    <td><?php echo htmlspecialchars($user['application_status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

</div>

<a href="index.php">Return to home</a>
<hr>

</body>
</html>