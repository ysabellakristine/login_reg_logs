<?php

function logAction($pdo, $user_id, $action_type, $action_details) { // for audit logs
    $sql = "INSERT INTO audit_logs (user_id, action_type, action_details) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$user_id, $action_type, $action_details]);

    return $executeQuery; // Returns true on success, false otherwise
}
function login($pdo, $login_input, $password) {
    // Prepare the query to check if the user exists using username or email
    $query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$login_input, $login_input]); // Using the same input for both placeholders

    if ($stmt->rowCount() == 1) {
        // Returns associative array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get values from the fetched row
        $uid = $row['user_id'];
        $passHash = $row['password'];

        // Validate password 
        if (password_verify($password, $passHash)) {
            // Store user info as session variables
            $_SESSION['user_id'] = $uid;
            $_SESSION['username'] = $row['username']; // Access username from the fetched row
            $_SESSION['email'] = $row['email']; // Access email from the fetched row
            $_SESSION['userLoginStatus'] = 1; // Set login status to true
            return true; // Successful login
        } else {
            // Incorrect password
            return false;
        }
    } else {
        // User not found
        return false;
    }
}

function addUser($conn, $username, $password) {
    // Check if username already exists
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);

    if ($stmt->rowCount() == 0) {
        // Hash the password before storing it
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user into the database
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$username, $hashedPassword]);
    } else {
        // Handle the case where the username already exists
        return false; // Optionally, you can throw an exception or return a specific error code
    }
}

function insertApplication($pdo, $first_name, $last_name, $gender, $email,$contact_no, $qualification, $date_of_birth, $application_status, $user_id) {

    $sql = "INSERT INTO applicants (first_name, last_name, gender, email, contact_no, date_of_birth, qualification, application_status) VALUES(?,?,?,?,?,?,?,?)";
    $applicant_name = $first_name . ' ' . $last_name;
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$first_name, $last_name, $gender, $email,$contact_no, $date_of_birth, $qualification, $application_status]);

    if ($executeQuery) {
        logAction($pdo, $user_id, 'CREATE', "Created applicant: $applicant_name");
        return true;
    }
}
function updateApplication($pdo, $first_name, $last_name, $gender, $email, $date_of_birth, $qualification, $contact_no, $application_status, $applicant_id, $user_id) {
    $applicant_name = $first_name . ' ' . $last_name;

    // SQL query to update the application record
    $sql = "UPDATE applicants
            SET first_name = ?,
                last_name = ?,
                email = ?,
                contact_no = ?,
                gender = ?,
                date_of_birth = ?, 
                qualification = ?,
                application_status = ?,
                last_updated = CURRENT_TIMESTAMP
            WHERE applicant_id = ?";

    // Prepare the query
    $stmt = $pdo->prepare($sql);

    // Execute the query with the correct parameters
    $executeQuery = $stmt->execute([$first_name, $last_name, $email, $contact_no, $gender, $date_of_birth, $qualification, $application_status, $applicant_id]);

    // If the query was successful, log the action and return true
    if ($executeQuery) {
        logAction($pdo, $user_id, 'UPDATE', "Updated application: $applicant_name");
        return true;
    }

    // If the query failed, return false
    return false;
}


function deleteApplication($pdo, $applicant_id, $user_id) {
    try {
        // Log the action
        logAction($pdo, $user_id, 'DELETE', "Deleted applicant id: $applicant_id");

        // Prepare and execute the delete query
        $sql = "DELETE FROM applicants WHERE applicant_id = ?";
        $stmt = $pdo->prepare($sql);
        $executeDeleteQuery = $stmt->execute([$applicant_id]);

        // Return true if the deletion was successful
        return $executeDeleteQuery;
    } catch (PDOException $e) {
        // Handle any errors (optional: log the error)
        error_log("Error deleting applicant: " . $e->getMessage());
        return false;
    }
}

function getAllApplications($pdo) {
	$sql = "SELECT * FROM applicants";
	$stmt = $pdo->prepare($sql);
	$executeQuery = $stmt->execute();

	if ($executeQuery) {
		return $stmt->fetchAll();
	}
}
function getApplicationsByID($pdo, $applicant_id) {
	$sql = "SELECT * FROM applicants WHERE applicant_id = ?"; // updated selected method to only include toys that aren't deleted
	$stmt = $pdo->prepare($sql);
	$executeQuery = $stmt->execute([$applicant_id]);

	if ($executeQuery) {
        // Fetch the result
        $result = $stmt->fetch();
        
        // Return the result if found, otherwise return null
        return $result !== false ? $result : null; // Explicitly return null if no record is found
    }

    return null; // Return null if the query fails
}