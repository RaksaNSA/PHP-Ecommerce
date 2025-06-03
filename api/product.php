<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requeted-With");

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getProduct();
        break;
    case 'POST':
        if (isset($_GET['id'])) {
            updateProduct();
        } else {
            createProduct();
        }
        break;
    case 'PUT':
        deleteProduct();
        break;
    default:
        echo json_encode(["message" => "Invalid request method"]);
        break;
}

function getProduct()
{
    global $pdo;
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("
            select
                product.*,
                category.name as category_name,
                concat('http://localhost/PHP-Project/php/uploads/', product.image) as image_url
            from product
            left join category on product.category_id = category.id
            where product.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode(["message" => "Product not found"]);
        }
    } else {
        $stmt = $pdo->query("
            select
                product.*,
                category.name as category_name,
                concat('http://localhost/php-ecommerce/uploads/', product.image) as image_url
            from product
            left join category on product.category_id = category.id
        ");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode(["message" => "Product not found"]);
        }
    }
}

function createProduct()
{
    global $pdo;

    $uploadDir = "../uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $fileName = basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $fileName;
    move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile);

    $stmtOrder = $pdo->prepare("select max(p_order) as max_order from product");
    $stmtOrder->execute();
    $order = ($stmtOrder->fetch(PDO::FETCH_ASSOC)['max_order'] ?? 0) + 1;

    $stmt = $pdo->prepare("insert into product (name, description, price, stock, image, active, p_order, display, category_id) values (:name, :description, :price, :stock, :image, :active, :p_order, :display, :category_id)");
    if ($stmt->execute([
        ':name' => $_POST['name'],
        ':description' => $_POST['description'],
        ':price' => $_POST['price'],
        ':stock' => $_POST['stock'],
        ':image' => $fileName,
        ':active' => 1,
        ':p_order' => $order,
        ':display' => 1,
        ':category_id' => $_POST['category_id']
    ])) {
        echo json_encode(['message' => "Product created successfully"]);
    } else {
        echo json_encode(['message' => "Unable to create product"]);
    }
}

function updateProduct()
{
    global $pdo;
    $id = $_GET['id'];

    $uploadDir = "../uploads/";

    $fileName = basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $fileName;
    move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile);

    $stmtOrder = $pdo->prepare("select `order` as max_order from product");
    $stmtOrder->execute();
    $order = ($stmtOrder->fetch(PDO::FETCH_ASSOC)['max_order'] ?? 0) + 1;

    $stmt = $pdo->prepare(
        "update product set
                            name = :name,
                            image = :image,
                            active = :active,
                            `order` = :order,
                            category_id = :category_id
                            where id = :id"
    );
    if ($stmt->execute([
        ':name' => $_POST['name'],
        ':image' => $fileName,
        ':active' => 1,
        ':order' => $order,
        ':category_id' => $_POST['category_id'],
        ':id' => $id,
    ])) {
        echo json_encode(['message' => "Product updated successfully"]);
    } else {
        echo json_encode(['message' => "Unable to update product"]);
    }
}

function deleteProduct()
{
    global $pdo;
    $id = $_GET['id'];

    $stmt = $pdo->prepare("update product set active = 0 where id = :id");
    if ($stmt->execute([':id' => $id])) {
        echo json_encode(['message' => "Product deleted successfully"]);
    } else {
        echo json_encode(['message' => "Unable to delete product"]);
    }
}
