<?php
session_start();
include('includes/db_connect.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'user'){
    header("Location: register.php?message=" . urlencode("Please register first to list your iPhone"));
    exit();
}

// Fetch iPhone specs
$iphone_specs = $conn->query("SELECT * FROM iphone_specs ORDER BY iphonemodel, ram, storage");

$error = $success = '';
$predicted_price = 0;

// Predict price helper (simplified)
function predictPrice($battery_mah, $ram, $storage, $launch_year, $used_month, $battery_health, $model) {
    $coeffs = [
        "iPhone 11" => 9812, "iPhone 11 Pro" => 9637, "iPhone 12" => 13057,
        "iPhone 12 Pro" => 3300, "iPhone 13" => 8069, "iPhone 13 Pro" => 12617,
        "iPhone 14" => 8483, "iPhone 14 Pro" => 21144, "iPhone 15" => 13888,
        "iPhone 15 Pro" => 26191
    ];
    $coeff = $coeffs[$model] ?? 0;
    $price = -2732080 + 2.69223*$battery_mah + 3037.03*$ram + 147.001*$storage + 1332.36*$launch_year - 110.408*$used_month + 513.238*$battery_health + $coeff;
    return round($price);
}

// Handle form submission
if(isset($_POST['submit'])){
    $user_id = $_SESSION['user_id'];
    $model = $_POST['iphonemodel'];
    $launch_year = intval($_POST['launch_year']);
    $ram = intval($_POST['ram']);
    $storage = intval($_POST['storage']);
    $battery_capacity = intval($_POST['battery_capacity']);
    $used_months = intval($_POST['used_months']);
    $battery_health = intval($_POST['battery_health']);
    $description = $conn->real_escape_string(trim($_POST['description']));
    $price_option = $_POST['price_option'] ?? 'predicted';
    $price = ($price_option==='predicted') ? intval($_POST['predicted_price']) : intval($_POST['price']);

    // Handle image upload
    $image_name = '';
    if(isset($_FILES['image']) && $_FILES['image']['error']==0){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','webp'];
        if(in_array(strtolower($ext), $allowed)){
            $image_name = 'iphone_'.uniqid().'.'.$ext;
            move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/'.$image_name);
        }
    }

    // Insert listing
    $stmt = $conn->prepare("INSERT INTO listings (user_id, model, launch_year, ram, storage, battery_capacity, used_months, battery_health, price, description, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available', NOW())");
    $stmt->bind_param("isiiiiiiiss", $user_id, $model, $launch_year, $ram, $storage, $battery_capacity, $used_months, $battery_health, $price, $description, $image_name);
    if($stmt->execute()) $success = "✅ iPhone listed successfully!";
    else $error = "⚠️ Error: ".$stmt->error;
}
?>

<?php include('includes/header.php'); ?>

<div class="container mt-5">
<h2 class="text-center mb-4">List Your iPhone</h2>

<?php if($error): ?><div class="alert alert-danger"><?php echo $error;?></div><?php endif; ?>
<?php if($success): ?><div class="alert alert-success"><?php echo $success;?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="sellForm">
    <div class="mb-3">
        <label>iPhone Model</label>
        <select name="iphonemodel" id="model" class="form-select" required onchange="updateSpecs()">
            <option value="">Select Model</option>
            <?php while($row=$iphone_specs->fetch_assoc()): ?>
                <option value="<?php echo $row['iphonemodel']; ?>"
                    data-launch_year="<?php echo $row['launch_year']; ?>"
                    data-ram="<?php echo $row['ram']; ?>"
                    data-storage="<?php echo $row['storage']; ?>"
                    data-battery_capacity="<?php echo $row['battery_capacity']; ?>">
                    <?php echo $row['iphonemodel'].", ".$row['ram']."GB, ".$row['storage']."GB, ".$row['battery_capacity']."mAh"; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div id="specs_section" style="display:none;">
        <input type="hidden" name="launch_year" id="launch_year">
        <input type="hidden" name="ram" id="ram">
        <input type="hidden" name="storage" id="storage">
        <input type="hidden" name="battery_capacity" id="battery_capacity">
    </div>

    <div class="mb-3">
        <label>Used Months</label>
        <input type="number" name="used_months" id="used_months" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Battery Health (%)</label>
        <input type="number" name="battery_health" id="battery_health" class="form-control" min="30" max="100" required>
    </div>

    <div class="mb-3" id="predictedPriceSection" style="display:none;">
        <p>Predicted Price: NPR <span id="predPriceDisplay">0</span></p>
        <input type="hidden" name="predicted_price" id="predicted_price">
        <div class="d-flex gap-2">
            <button type="button" id="usePredictedBtn" class="btn btn-success flex-fill">Use Predicted Price</button>
            <button type="button" id="useOwnBtn" class="btn btn-warning flex-fill">Use Own Price</button>
        </div>
    </div>

    <input type="hidden" name="price_option" id="price_option" value="predicted">

    <div class="mb-3" id="ownPriceSection" style="display:none;">
        <label>Price (NPR)</label>
        <input type="number" name="price" id="price" class="form-control">
    </div>

    <div id="finalSection" style="display:none;">
        <div class="mb-3"><label>Description</label>
        <textarea name="description" class="form-control" rows="3" required></textarea></div>

        <div class="mb-3"><label>Upload Image</label>
        <input type="file" name="image" class="form-control"></div>
    </div>

    <button type="submit" name="submit" id="listIphoneBtn" class="btn btn-success w-100 mt-3" style="display:none;">List iPhone</button>
</form>
</div>

<script>
function updateSpecs(){
    const sel = document.getElementById('model');
    const opt = sel.options[sel.selectedIndex];
    if(sel.value!==""){
        document.getElementById('specs_section').style.display='block';
        document.getElementById('launch_year').value=opt.getAttribute('data-launch_year');
        document.getElementById('ram').value=opt.getAttribute('data-ram');
        document.getElementById('storage').value=opt.getAttribute('data-storage');
        document.getElementById('battery_capacity').value=opt.getAttribute('data-battery_capacity');

        // Auto calculate predicted price
        calculatePredictedPrice();
        document.getElementById('predictedPriceSection').style.display='block';
        document.getElementById('usePredictedBtn').style.display='inline-block';
        document.getElementById('useOwnBtn').style.display='inline-block';
    } else {
        document.getElementById('specs_section').style.display='none';
        document.getElementById('predictedPriceSection').style.display='none';
        document.getElementById('ownPriceSection').style.display='none';
        document.getElementById('finalSection').style.display='none';
        document.getElementById('listIphoneBtn').style.display='none';
    }
}

function calculatePredictedPrice(){
    const sel=document.getElementById('model');
    const opt=sel.options[sel.selectedIndex];
    const battery=parseInt(opt.getAttribute('data-battery_capacity'))||0;
    const ram=parseInt(opt.getAttribute('data-ram'))||0;
    const storage=parseInt(opt.getAttribute('data-storage'))||0;
    const launch_year=parseInt(opt.getAttribute('data-launch_year'))||0;
    const used_months=parseInt(document.getElementById('used_months').value)||0;
    const battery_health=parseInt(document.getElementById('battery_health').value)||100;
    const model=sel.value;

    const coeffs={"iPhone 11":9812,"iPhone 11 Pro":9637,"iPhone 12":13057,"iPhone 12 Pro":3300,"iPhone 13":8069,"iPhone 13 Pro":12617,"iPhone 14":8483,"iPhone 14 Pro":21144,"iPhone 15":13888,"iPhone 15 Pro":26191};
    const coeff=coeffs[model]||0;
    let price=Math.round(-2732080+2.69223*battery+3037.03*ram+147.001*storage+1332.36*launch_year-110.408*used_months+513.238*battery_health+coeff);
    document.getElementById('predPriceDisplay').textContent=price.toLocaleString();
    document.getElementById('predicted_price').value=price;
}

document.getElementById('usePredictedBtn').addEventListener('click', function(){
    document.getElementById('price_option').value='predicted';
    document.getElementById('finalSection').style.display='block';
    document.getElementById('listIphoneBtn').style.display='block';
    this.style.display='none';
    document.getElementById('useOwnBtn').style.display='none';
});

document.getElementById('useOwnBtn').addEventListener('click', function(){
    document.getElementById('price_option').value='own';
    document.getElementById('ownPriceSection').style.display='block';
    document.getElementById('finalSection').style.display='block';
    document.getElementById('listIphoneBtn').style.display='block';
    this.style.display='none';
    document.getElementById('usePredictedBtn').style.display='none';
});
</script>

<?php include('includes/footer.php'); ?>
