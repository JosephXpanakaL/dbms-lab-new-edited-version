<?php
include "db.php";

$selected_customer = isset($_GET["customer_id"]) ? $_GET["customer_id"] : "all";
$from_date         = isset($_GET["from_date"]) ? trim($_GET["from_date"]) : "";
$to_date           = isset($_GET["to_date"]) ? trim($_GET["to_date"]) : "";

$conditions = [];
$params     = [];
$types      = "";

if ($selected_customer !== "all" && is_numeric($selected_customer)) {
    $conditions[] = "customer.customer_id = ?";
    $params[]     = intval($selected_customer);
    $types       .= "i";
}
if (!empty($from_date)) {
    $conditions[] = "rental.start_date >= ?";
    $params[]     = $from_date;
    $types       .= "s";
}
if (!empty($to_date)) {
    $conditions[] = "rental.end_date <= ?";
    $params[]     = $to_date;
    $types       .= "s";
}

$where_sql = count($conditions) > 0 ? " WHERE " . implode(" AND ", $conditions) : "";

$sql = "SELECT rental.rental_id, customer.customer_id, customer.name, customer.licence_no, customer.phone,
               car.model, car.reg_no, car.status, rental.start_date, rental.end_date,
               (DATEDIFF(rental.end_date, rental.start_date) + 1) AS total_days
        FROM rental
        INNER JOIN customer ON rental.customer_id = customer.customer_id
        INNER JOIN car ON rental.car_id = car.car_id"
        . $where_sql .
        " ORDER BY rental.rental_id ASC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$customers = mysqli_query($conn, "SELECT customer_id, name FROM customer ORDER BY customer_id ASC");
?>
<!DOCTYPE html>
<html>
<head><title>Rental Report</title></head>
<body>
<h2>Car Rental Report</h2>

<form method="GET" action="">
    Select Report:
    <select name="customer_id" onchange="this.form.submit()">
        <option value="all" <?php if ($selected_customer === "all") echo "selected"; ?>>All Customers Report</option>
        <?php while ($c = mysqli_fetch_assoc($customers)) { ?>
            <option value="<?php echo $c["customer_id"]; ?>" <?php if ($selected_customer == $c["customer_id"]) echo "selected"; ?>>
                <?php echo $c["customer_id"] . " - " . htmlspecialchars($c["name"]); ?>
            </option>
        <?php } ?>
    </select>
    &nbsp;&nbsp;
    From: <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>">
    To: <input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>">
    <input type="submit" value="Filter">
    <a href="report.php">Reset</a>
</form>
<br>

Search: <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Type to search...">
<br><br>

<table border="1" cellpadding="8" id="reportTable">
<tr>
    <th>Rental ID</th>
    <th>Customer ID</th>
    <th>Customer Name</th>
    <th>Licence No</th>
    <th>Phone</th>
    <th>Car Model</th>
    <th>Registration No</th>
    <th>Status</th>
    <th>Start Date</th>
    <th>End Date</th>
    <th>Days</th>
</tr>
<?php if (mysqli_num_rows($result) > 0) { ?>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row["rental_id"]); ?></td>
        <td><?php echo htmlspecialchars($row["customer_id"]); ?></td>
        <td><?php echo htmlspecialchars($row["name"]); ?></td>
        <td><?php echo htmlspecialchars($row["licence_no"]); ?></td>
        <td><?php echo htmlspecialchars($row["phone"]); ?></td>
        <td><?php echo htmlspecialchars($row["model"]); ?></td>
        <td><?php echo htmlspecialchars($row["reg_no"]); ?></td>
        <td><?php echo htmlspecialchars($row["status"]); ?></td>
        <td><?php echo htmlspecialchars($row["start_date"]); ?></td>
        <td><?php echo htmlspecialchars($row["end_date"]); ?></td>
        <td><?php echo htmlspecialchars($row["total_days"]); ?></td>
    </tr>
    <?php } ?>
<?php } else { ?>
    <tr><td colspan="11">No records found.</td></tr>
<?php } ?>
</table>

<br><a href="rental.php">Back to Rental Form</a>

<script>
function filterTable() {
    const query = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll("#reportTable tr");
    for (let i = 1; i < rows.length; i++) {
        rows[i].style.display = rows[i].textContent.toLowerCase().includes(query) ? "" : "none";
    }
}
</script>
</body>
</html>
