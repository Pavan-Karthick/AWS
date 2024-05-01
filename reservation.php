<?php
// Database connection parameters
$tns = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=mydatabase.cdomewoo8zqw.us-east-1.rds.amazonaws.com)(PORT=1521))(CONNECT_DATA=(SERVER=DEDICATED)(SERVICE_NAME=mydatabase)))";
$username = "admin";
$password = "Pavan123";

// Function to display error message
function displayErrorMessage($message) {
    echo '<div style="color: red;">' . $message . '</div>';
}

try {
    // Establish connection
    $conn = oci_connect($username, $password, $tns);

    if (!$conn) {
        $e = oci_error();
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }

    // Handle form submission (booking)
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["book"])) {
        // Sanitize and validate input
        $passengerName = htmlspecialchars($_POST["passenger_name"]);
        $departureDate = $_POST["departure_date"];
        $returnDate = $_POST["return_date"];
        $destination = htmlspecialchars($_POST["destination"]);
        // Add validation checks as needed
        
        // Prepare and execute SQL statement to insert booking
        $sql = "INSERT INTO BOOKING (PASSENGER_ID, BOOKING_DATE, DEPARTURE_DATE, ARRIVAL_DATE, DESTINATION, STATUS)
                VALUES (:passenger_id, SYSDATE, TO_DATE(:departure_date, 'YYYY-MM-DD'), TO_DATE(:return_date, 'YYYY-MM-DD'), :destination, 'Pending')";
        $stmt = oci_parse($conn, $sql);
        // Assuming you have a PASSENGER table with PASSENGER_ID and NAME columns
        $passengerId = null; // You need to retrieve the passenger ID based on the name
        oci_bind_by_name($stmt, ':passenger_id', $passengerId);
        oci_bind_by_name($stmt, ':departure_date', $departureDate);
        oci_bind_by_name($stmt, ':return_date', $returnDate);
        oci_bind_by_name($stmt, ':destination', $destination);
        // Execute query and handle success or error
        
        // Display success or error message
    }

    // Handle form submission (update booking)
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
        // Sanitize and validate input
        $bookingId = htmlspecialchars($_POST["booking_id"]);
        $newDepartureDate = $_POST["new_departure_date"];
        $newReturnDate = $_POST["new_return_date"];
        // Add validation checks as needed
        
        // Prepare and execute SQL statement to update booking
        $sql = "UPDATE BOOKING SET DEPARTURE_DATE = TO_DATE(:new_departure_date, 'YYYY-MM-DD'), ARRIVAL_DATE = TO_DATE(:new_return_date, 'YYYY-MM-DD') WHERE BOOKING_ID = :booking_id";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':new_departure_date', $newDepartureDate);
        oci_bind_by_name($stmt, ':new_return_date', $newReturnDate);
        oci_bind_by_name($stmt, ':booking_id', $bookingId);
        // Execute query and handle success or error
        
        // Display success or error message
    }

    // Retrieve booking information
    $stmt = oci_parse($conn, "SELECT * FROM BOOKING");
    oci_execute($stmt);
    $bookings = [];
    while ($row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {
        $bookings[] = $row;
    }

    // Display available buses and routes
    $stmt = oci_parse($conn, "SELECT * FROM BUS");
    oci_execute($stmt);
    $buses = [];
    while ($row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {
        $buses[] = $row;
    }

    $stmt = oci_parse($conn, "SELECT * FROM ROUTE");
    oci_execute($stmt);
    $routes = [];
    while ($row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {
        $routes[] = $row;
    }

    // User authentication
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];

        // Query to check if username and password match
        // Example: $sql = "SELECT * FROM REGISTER WHERE USERNAME = :username AND PASSWORD = :password";
        // Bind parameters and execute query

        // If user exists, redirect to booking page
        // Example: header("Location: booking.php");
        // else, display error message
    }

    // Close connection
    oci_close($conn);
} catch(Exception $e) {
    // Display error message
    displayErrorMessage("Connection failed: " . $e->getMessage());
}
?>

<!-- HTML code for login form -->
<form method="post">
    Username: <input type="text" name="username"><br>
    Password: <input type="password" name="password"><br>
    <input type="submit" name="login" value="Login">
</form>

<!-- HTML code for booking form -->
<form method="post">
    Passenger Name: <input type="text" name="passenger_name"><br>
    Departure Date: <input type="date" name="departure_date"><br>
    Return Date: <input type="date" name="return_date"><br>
    Destination: <input type="text" name="destination"><br>
    <!-- Add more fields as needed -->
    <input type="submit" name="book" value="Book Ticket">
</form>

<!-- HTML code for updating booking form -->
<form method="post">
    Booking ID to Update: <input type="text" name="booking_id"><br>
    New Departure Date: <input type="date" name="new_departure_date"><br>
    New Return Date: <input type="date" name="new_return_date"><br>
    <!-- Add more fields as needed -->
    <input type="submit" name="update" value="Update Booking">
</form>

<!-- Display booking information -->
<?php if (!empty($bookings)): ?>
    <h2>Bookings</h2>
    <ul>
        <?php foreach ($bookings as $booking): ?>
            <li><?php echo $booking['BOOKING_ID']; ?></li>
            <!-- Display other booking details -->
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- Display available buses -->
<?php if (!empty($buses)): ?>
    <h2>Available Buses</h2>
    <ul>
        <?php foreach ($buses as $bus): ?>
            <li><?php echo $bus['BUS_NUMBER']; ?></li>
            <!-- Display other bus details -->
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- Display available routes -->
<?php if (!empty($routes)): ?>
    <h2>Available Routes</h2>
    <ul>
        <?php foreach ($routes as $route): ?>
            <li><?php echo $route['SOURCE_LOCATION'] . ' to ' . $route['DESTINATION_LOCATION']; ?></li>
            <!-- Display other route details -->
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
