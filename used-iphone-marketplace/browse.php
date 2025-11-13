<?php
include('includes/db_connect.php');

// Fetch distinct filter values
$models = $conn->query("SELECT DISTINCT iphonemodel FROM iphone_specs ORDER BY iphonemodel");
$rams = $conn->query("SELECT DISTINCT ram FROM iphone_specs ORDER BY ram");
$storages = $conn->query("SELECT DISTINCT storage FROM iphone_specs ORDER BY storage");

// Handle filters
$where = "WHERE l.status='Available'";
if(isset($_GET['model']) && $_GET['model'] !== ''){
    $model = $conn->real_escape_string($_GET['model']);
    $where .= " AND l.model='$model'";
}
if(isset($_GET['ram']) && $_GET['ram'] !== ''){
    $ram = intval($_GET['ram']);
    $where .= " AND l.ram=$ram";
}
if(isset($_GET['storage']) && $_GET['storage'] !== ''){
    $storage = intval($_GET['storage']);
    $where .= " AND l.storage=$storage";
}
if(isset($_GET['battery_health']) && $_GET['battery_health'] !== ''){
    list($min_bat, $max_bat) = explode('-', $_GET['battery_health']);
    $where .= " AND l.battery_health BETWEEN $min_bat AND $max_bat";
}
if(isset($_GET['trust_score']) && $_GET['trust_score'] !== ''){
    $trust_score = intval($_GET['trust_score']);
    $where .= " AND u.trust_score >= $trust_score";
}

// Fetch filtered listings
$listings = $conn->query("
    SELECT l.*, u.name AS seller_name, u.trust_score
    FROM listings l
    JOIN users u ON l.user_id = u.id
    $where
    ORDER BY l.created_at DESC
");
?>

<?php include('includes/header.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Browse iPhones</h2>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <select name="model" class="form-select">
                <option value="">All Models</option>
                <?php while($row = $models->fetch_assoc()): ?>
                    <option value="<?php echo $row['iphonemodel']; ?>" <?php if(isset($_GET['model']) && $_GET['model']==$row['iphonemodel']) echo 'selected'; ?>>
                        <?php echo $row['iphonemodel']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="ram" class="form-select">
                <option value="">All RAM</option>
                <?php while($row = $rams->fetch_assoc()): ?>
                    <option value="<?php echo $row['ram']; ?>" <?php if(isset($_GET['ram']) && $_GET['ram']==$row['ram']) echo 'selected'; ?>>
                        <?php echo $row['ram']; ?> GB
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="storage" class="form-select">
                <option value="">All Storage</option>
                <?php while($row = $storages->fetch_assoc()): ?>
                    <option value="<?php echo $row['storage']; ?>" <?php if(isset($_GET['storage']) && $_GET['storage']==$row['storage']) echo 'selected'; ?>>
                        <?php echo $row['storage']; ?> GB
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="battery_health" class="form-select">
                <option value="">Battery Health</option>
                <?php
                for($i=50; $i<100; $i+=10){
                    $range = $i.'-'.($i+10);
                    $selected = (isset($_GET['battery_health']) && $_GET['battery_health']==$range) ? 'selected' : '';
                    echo "<option value='$range' $selected>$range%</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="trust_score" class="form-select">
                <option value="">Trust Score</option>
                <?php for($i=1; $i<=5; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php if(isset($_GET['trust_score']) && $_GET['trust_score']==$i) echo 'selected'; ?>>
                        <?php echo str_repeat('⭐',$i); ?> & Up
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <div class="row">
        <?php if($listings->num_rows > 0): ?>
            <?php while($row = $listings->fetch_assoc()): ?>
                <div class="col-md-3 mb-4">
                    <div class="card shadow-sm h-100">
                        <?php if($row['image'] && file_exists('uploads/'.$row['image'])): ?>
                            <img src="uploads/<?php echo $row['image']; ?>" class="card-img-top" alt="iPhone Image">
                        <?php else: ?>
                            <img src="assets/img/placeholder.png" class="card-img-top" alt="No Image">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['model']); ?></h5>
                            <p class="card-text mb-2">
                                <strong>Price:</strong> NPR <?php echo number_format($row['price']); ?><br>
                                <strong>Battery:</strong> <?php echo $row['battery_health']; ?>%<br>
                                <strong>Seller:</strong> <?php echo htmlspecialchars($row['seller_name']); ?> 
                                <?php if($row['trust_score'] >= 3) echo "⭐"; ?><br>
                                <strong>Description:</strong> <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                            </p>
                            <a href="view_listing.php?id=<?php echo $row['id']; ?>" class="btn btn-primary mt-auto w-100">View</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">No iPhones listed matching your filters.</p>
        <?php endif; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>
