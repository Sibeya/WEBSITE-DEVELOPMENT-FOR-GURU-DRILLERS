<?php
// ============================================================
// GRD - Products API
// ============================================================
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ---- GET: Fetch products or single product ----
if ($method === 'GET') {
    $conn = getDB();

    // Dashboard stats
    if (isset($_GET['stats'])) {
        $total = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
        $active = $conn->query("SELECT COUNT(*) as c FROM products WHERE is_active=1")->fetch_assoc()['c'];
        $enq = $conn->query("SELECT COUNT(*) as c FROM enquiries")->fetch_assoc()['c'];
        echo json_encode(['success' => true, 'stats' => ['total' => $total, 'active' => $active, 'enquiries' => $enq]]);
        exit;
    }

    // Single product
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        if ($product) {
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
        $conn->close();
        exit;
    }

    // All active products (for website)
    $sql = "SELECT * FROM products WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC";
    // Admin gets all
    if (isset($_GET['admin'])) {
        $sql = "SELECT * FROM products ORDER BY sort_order ASC, created_at DESC";
    }
    $result = $conn->query($sql);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode(['success' => true, 'products' => $products]);
    $conn->close();
    exit;
}

// ---- POST: Create / Update / Delete ----
if ($method === 'POST') {
    $conn = getDB();

    // Delete
    if (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        // Remove image file if exists
        $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row && $row['image']) {
            $imgPath = '../uploads/products/' . $row['image'];
            if (file_exists($imgPath)) unlink($imgPath);
        }
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete product.']);
        }
        $conn->close();
        exit;
    }

    // Create / Update
    $id       = intval($_POST['product_id'] ?? 0);
    $name     = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $desc     = trim($_POST['description'] ?? '');
    $specs    = trim($_POST['specifications'] ?? '{}');
    $active   = intval($_POST['is_active'] ?? 1);

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Product name is required.']);
        exit;
    }

    // Handle image upload
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $mime = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($mime, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, WebP images allowed.']);
            exit;
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid('prod_') . '.' . strtolower($ext);
        $dest = '../uploads/products/' . $imageName;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            echo json_encode(['success' => false, 'message' => 'Image upload failed.']);
            exit;
        }
    }

    if ($id > 0) {
        // Update
        if ($imageName) {
            // Delete old image
            $stmt = $conn->prepare("SELECT image FROM products WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $old = $stmt->get_result()->fetch_assoc();
            if ($old && $old['image'] && file_exists('../uploads/products/' . $old['image'])) {
                unlink('../uploads/products/' . $old['image']);
            }
            $stmt = $conn->prepare("UPDATE products SET name=?,category=?,description=?,specifications=?,image=?,is_active=?,updated_at=NOW() WHERE id=?");
            $stmt->bind_param('sssssii', $name, $category, $desc, $specs, $imageName, $active, $id);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name=?,category=?,description=?,specifications=?,is_active=?,updated_at=NOW() WHERE id=?");
            $stmt->bind_param('ssssii', $name, $category, $desc, $specs, $active, $id);
        }
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Product updated successfully!' : 'Update failed.']);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO products (name, category, description, specifications, image, is_active) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('sssssi', $name, $category, $desc, $specs, $imageName, $active);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Product added successfully!' : 'Insert failed.']);
    }
    $conn->close();
    exit;
}
?>
