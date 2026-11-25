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
               car.model, car.reg_no, car.status, car.image_path, rental.start_date, rental.end_date,
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
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>DriveLux | Rental Reports</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/app.css"></head>
<body><header class="topbar"><a class="brand" href="rental.php"><span class="brand-mark">D</span><span>DRIVE<span class="brand-light">LUX</span><small>RENTAL STUDIO</small></span></a><nav><a href="rental.php">New booking</a><a class="active" href="report.php">Rental reports</a></nav><span class="topbar-tag"><i></i> SYSTEM ONLINE</span></header><main class="page-shell report-shell"><div class="report-heading"><div><span class="eyebrow">FLEET OVERVIEW / 02</span><h1>Rental <em style="color:#8da832">insights.</em></h1><p>Review bookings, customer details and vehicle activity in one place.</p></div><a class="back-link" href="rental.php">＋ New booking</a></div>

<form method="GET" action="" class="filter-card">
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

<div class="search-wrap"><input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search customers, cars or registration..."></div>
<br><br>

<div class="table-card"><table class="report-table" id="reportTable">
<tr>
    <th>Rental ID</th>
    <th>Customer ID</th>
    <th>Customer Name</th>
    <th>Licence No</th>
    <th>Phone</th>
    <th>Vehicle</th>
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
        <td><?php if (!empty($row["image_path"]) && file_exists(__DIR__ . "/" . $row["image_path"])) { ?><img src="<?php echo htmlspecialchars($row["image_path"]); ?>" alt="Car"><?php } else { ?><span class="status-pill">NO PHOTO</span><?php } ?></td>
        <td><?php echo htmlspecialchars($row["model"]); ?></td>
        <td><?php echo htmlspecialchars($row["reg_no"]); ?></td>
        <td><?php echo htmlspecialchars($row["status"]); ?></td>
        <td><?php echo htmlspecialchars($row["start_date"]); ?></td>
        <td><?php echo htmlspecialchars($row["end_date"]); ?></td>
        <td><?php echo htmlspecialchars($row["total_days"]); ?></td>
    </tr>
    <?php } ?>
<?php } else { ?>
    <tr><td colspan="12">No records found.</td></tr>
<?php } ?>
</table></div>
<footer class="report-footer"><span>DRIVELUX © 2026</span><a href="rental.php">← Back to booking desk</a></footer></main>

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
