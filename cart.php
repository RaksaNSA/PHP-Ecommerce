<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += 1;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image_url' => $product['image_url'],
                'description' => $product['description'],
                'quantity' => 1
            ];
        }
    }
    header("Location: cart.php");
    exit;
}

if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
    }
    header("Location: cart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $id => $qty) {
        $qty = max(1, (int)$qty);
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] = $qty;
        }
    }
    header("Location: cart.php");
    exit;
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$delivery = 0.00;
$discount = 0.00;
$total = $subtotal + $delivery - $discount;
?>
<!DOCTYPE html>
<html lang="en">
<?php include_once __DIR__ . '/themes/header.php'; ?>
<body class="goto-here">
<?php include_once __DIR__ . '/themes/navtop.php'; ?>
<?php include_once __DIR__ . '/themes/navegation.php'; ?>

<div class="hero-wrap hero-bread" style="background-image: url('images/bg_6.jpg');">
  <div class="container">
    <div class="row no-gutters slider-text align-items-center justify-content-center">
      <div class="col-md-9 ftco-animate text-center">
        <p class="breadcrumbs"><span class="mr-2"><a href="index.php">Home</a></span> <span>Cart</span></p>
        <h1 class="mb-0 bread">My Cart</h1>
      </div>
    </div>
  </div>
</div>

<section class="ftco-section ftco-cart">
    <div class="container">
        <div class="row">
            <div class="col-md-12 ftco-animate">
                <div class="cart-list">
                    <form method="post">
                    <table class="table">
                        <thead class="thead-primary">
                            <tr class="text-center">
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($cart) > 0): ?>
                            <?php foreach ($cart as $item): ?>
                            <tr class="text-center">
                                <td class="product-remove">
                                    <a href="cart.php?remove=<?php echo $item['id']; ?>"><span class="ion-ios-close"></span></a>
                                </td>
                                <td class="image-prod">
                                    <div class="img" style="background-image:url('<?php echo htmlspecialchars($item['image_url']); ?>'); width:70px; height:70px; background-size:cover;"></div>
                                </td>
                                <td class="product-name">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                                </td>
                                <td class="price">$<?php echo number_format($item['price'], 2); ?></td>
                                <td class="quantity">
                                    <div class="input-group mb-3">
                                        <input type="number" name="quantities[<?php echo $item['id']; ?>]" class="quantity form-control input-number" value="<?php echo $item['quantity']; ?>" min="1" max="100">
                                    </div>
                                </td>
                                <td class="total">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">Your cart is empty.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="text-right">
                        <button type="submit" name="update_cart" class="btn btn-secondary py-2 px-4">Update Cart</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col col-lg-5 col-md-6 mt-5 cart-wrap ftco-animate">
                <div class="cart-total mb-3">
                    <h3>Cart Totals</h3>
                    <p class="d-flex">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </p>
                    <p class="d-flex">
                        <span>Delivery</span>
                        <span>$<?php echo number_format($delivery, 2); ?></span>
                    </p>
                    <p class="d-flex">
                        <span>Discount</span>
                        <span>$<?php echo number_format($discount, 2); ?></span>
                    </p>
                    <hr>
                    <p class="d-flex total-price">
                        <span>Total</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </p>
                </div>
                <?php if ($cart): ?>
                <p class="text-center"><a href="checkout.php" class="btn btn-primary py-3 px-4">Proceed to Checkout</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/themes/footer.php'; ?>

<div id="ftco-loader" class="show fullscreen">
  <svg class="circular" width="48px" height="48px">
    <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/>
    <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/>
  </svg>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/jquery-migrate-3.0.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.easing.1.3.js"></script>
<script src="js/jquery.waypoints.min.js"></script>
<script src="js/jquery.stellar.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="js/aos.js"></script>
<script src="js/jquery.animateNumber.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/scrollax.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
<script src="js/google-map.js"></script>
<script src="js/main.js"></script>
</body>
</html>