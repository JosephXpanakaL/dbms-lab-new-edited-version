<?php
include "db.php";
$message = "";

// Sync car availability status for today
$today = date("Y-m-d");
mysqli_query($conn, "UPDATE car SET status = 'Available'");
mysqli_query($conn, "UPDATE car SET status = 'Rented' WHERE car_id IN (SELECT DISTINCT car_id FROM rental WHERE start_date <= '$today' AND end_date >= '$today')");

if (isset($_POST["submit"])) {
    mysqli_begin_transaction($conn);
    try {
        $new_cust_name = trim($_POST["new_cust_name"] ?? "");
        $new_car_model = trim($_POST["new_car_model"] ?? "");
        // Single combined Licence / Registration No input used for both customer and car
        $shared_no     = strtoupper(trim($_POST["new_licence_reg"] ?? ""));

        if (!empty($new_cust_name)) {
            $new_phone = trim($_POST["new_phone"] ?? "");

            if (empty($shared_no) || empty($new_phone)) {
                throw new Exception("Please enter Licence / Registration No and Phone for the new customer.");
            }

            $stmt_c = mysqli_prepare($conn, "INSERT INTO customer (name, licence_no, phone) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt_c, "sss", $new_cust_name, $shared_no, $new_phone);
            mysqli_stmt_execute($stmt_c);
            $customer_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt_c);
        } else {
            $customer_id = intval($_POST["customer_id"] ?? 0);
        }

        if (!empty($new_car_model)) {
            if (empty($shared_no)) {
                throw new Exception("Please enter Licence / Registration No for the new car.");
            }

            $stmt_car = mysqli_prepare($conn, "INSERT INTO car (model, reg_no, status) VALUES (?, ?, 'Available')");
            mysqli_stmt_bind_param($stmt_car, "ss", $new_car_model, $shared_no);
            mysqli_stmt_execute($stmt_car);
            $car_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt_car);
        } else {
            $car_id = intval($_POST["car_id"] ?? 0);
        }

        $start_date = $_POST["start_date"];
        $end_date   = $_POST["end_date"];

        if (empty($customer_id) || empty($car_id)) {
            throw new Exception("Please select or enter both Customer and Car details.");
        }
        if ($end_date < $start_date) {
            throw new Exception("End Date cannot be earlier than Start Date.");
        }

        // Double-booking prevention check
        $stmt_chk = mysqli_prepare($conn, "SELECT rental_id, start_date, end_date FROM rental WHERE car_id = ? AND start_date <= ? AND end_date >= ?");
        mysqli_stmt_bind_param($stmt_chk, "iss", $car_id, $end_date, $start_date);
        mysqli_stmt_execute($stmt_chk);
        $conflict_res = mysqli_stmt_get_result($stmt_chk);
        if ($conflict = mysqli_fetch_assoc($conflict_res)) {
            throw new Exception("Error: Car is already booked from " . $conflict["start_date"] . " to " . $conflict["end_date"] . ".");
        }
        mysqli_stmt_close($stmt_chk);

        $stmt = mysqli_prepare($conn, "INSERT INTO rental (customer_id, car_id, start_date, end_date) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiss", $customer_id, $car_id, $start_date, $end_date);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($start_date <= $today && $end_date >= $today) {
            $stmt_st = mysqli_prepare($conn, "UPDATE car SET status = 'Rented' WHERE car_id = ?");
            mysqli_stmt_bind_param($stmt_st, "i", $car_id);
            mysqli_stmt_execute($stmt_st);
            mysqli_stmt_close($stmt_st);
        }

        mysqli_commit($conn);
        $message = "Rental saved successfully! (Customer ID: $customer_id, Car ID: $car_id)";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message = $e->getMessage();
    }
}

$customers = mysqli_query($conn, "SELECT * FROM customer ORDER BY customer_id ASC");
$cars      = mysqli_query($conn, "SELECT * FROM car ORDER BY car_id ASC");

// Next ascending IDs for live preview
$next_cust_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(MAX(customer_id), 0) + 1 AS next_id FROM customer"))["next_id"];
$next_car_id  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(MAX(car_id), 0) + 1 AS next_id FROM car"))["next_id"];

// Map customer_id to their associated/last rented car_id
$cust_car_map = [];
$map_res = mysqli_query($conn, "SELECT customer_id, car_id FROM rental ORDER BY rental_id ASC");
while ($m = mysqli_fetch_assoc($map_res)) {
    $cust_car_map[$m["customer_id"]] = $m["car_id"];
}
?>
<!DOCTYPE html>
<html>
<head><title>Car Rental</title></head>
<body>
<h2>Car Rental Form</h2>
<p><?php echo htmlspecialchars($message); ?></p>

<form method="POST" action="">
    <h3>Select Existing Customer &amp; Car</h3>
    Customer:
    <select name="customer_id" id="customer_select">
        <option value="">Select Customer</option>
        <?php while ($row = mysqli_fetch_assoc($customers)) { ?>
            <option value="<?php echo $row["customer_id"]; ?>">
                <?php echo $row["customer_id"] . " - " . htmlspecialchars($row["name"]); ?>
            </option>
        <?php } ?>
    </select>
    <br><br>

    Car:
    <select name="car_id" id="car_select">
        <option value="">Select Car</option>
        <?php while ($row = mysqli_fetch_assoc($cars)) { ?>
            <option value="<?php echo $row["car_id"]; ?>">
                <?php echo $row["car_id"] . " - " . htmlspecialchars($row["model"] . " - " . $row["reg_no"] . " (" . $row["status"] . ")"); ?>
            </option>
        <?php } ?>
    </select>
    <span id="car_note"></span>
    <br><br>

    <h3>Or Add New Customer &amp; Car</h3>
    Customer Name:
    <input type="text" name="new_cust_name" id="new_cust_name">
    <span id="cust_id_preview"></span>
    <br><br>
    Phone:
    <input type="text" name="new_phone" id="new_phone">
    <br><br>
    Car Model:
    <input type="text" name="new_car_model" id="new_car_model">
    <span id="car_id_preview"></span>
    <br><br>
    Licence / Registration No:
    <input type="text" name="new_licence_reg" id="new_licence_reg" style="text-transform: uppercase;">
    <br><br>

    <h3>Rental Dates</h3>
    Start Date: <input type="date" name="start_date" required><br><br>
    End Date: <input type="date" name="end_date" required><br><br>

    <input type="submit" name="submit" value="Submit">
</form>

<p>Choose existing members from the dropdown OR enter new details above to insert automatically.</p>
<br><a href="report.php">View Rental Report</a>

<script>
const customerCarMap = <?php echo json_encode($cust_car_map); ?>;
const nextCustId = <?php echo $next_cust_id; ?>;
const nextCarId = <?php echo $next_car_id; ?>;

const custSelect     = document.getElementById("customer_select");
const carSelect      = document.getElementById("car_select");
const carNote        = document.getElementById("car_note");
const newCustName    = document.getElementById("new_cust_name");
const custIdPreview  = document.getElementById("cust_id_preview");
const newCarModel    = document.getElementById("new_car_model");
const carIdPreview   = document.getElementById("car_id_preview");
const newLicenceReg  = document.getElementById("new_licence_reg");

// Automatically capitalize Licence / Registration No while typing
newLicenceReg.addEventListener("input", function() {
    this.value = this.value.toUpperCase();
});

// Auto-select customer's car when customer is selected from dropdown
custSelect.addEventListener("change", function() {
    const cid = this.value;
    if (cid) {
        newCustName.value = "";
        custIdPreview.textContent = "";
        if (customerCarMap[cid]) {
            carSelect.value = customerCarMap[cid];
            newCarModel.value = "";
            carIdPreview.textContent = "";
            carNote.textContent = " (Auto-selected customer's car)";
        } else {
            carNote.textContent = "";
        }
    } else {
        carNote.textContent = "";
    }
});

// Auto-generate ascending Customer ID preview when typing customer name
newCustName.addEventListener("input", function() {
    if (this.value.trim().length > 0) {
        custSelect.value = "";
        custIdPreview.textContent = " (Auto Customer ID: " + nextCustId + ")";
    } else {
        custIdPreview.textContent = "";
    }
});

// Auto-generate ascending Car ID preview when typing car model
newCarModel.addEventListener("input", function() {
    if (this.value.trim().length > 0) {
        carSelect.value = "";
        carNote.textContent = "";
        carIdPreview.textContent = " (Auto Car ID: " + nextCarId + ")";
    } else {
        carIdPreview.textContent = "";
    }
});

carSelect.addEventListener("change", function() {
    if (this.value) {
        newCarModel.value = "";
        carIdPreview.textContent = "";
    }
});
</script>
</body>
</html>
