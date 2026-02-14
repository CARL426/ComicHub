<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

// Check login
if(!isset($_SESSION['user_id'])){
    echo "<p style='color:red;'>Please login to view or purchase comics.</p>";
    include 'includes/footer.php';
    exit;
}

$user_id = $_SESSION['user_id'];
$mode = 'preview'; // default mode

// ===============================
// Case 1: POST from comic_detail.php (Buy Now)
// ===============================
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comic_id'])){
    $comic_id = intval($_POST['comic_id']);
    $amount = floatval($_POST['amount']);

    // Confirm purchase clicked
    if(isset($_POST['confirm_purchase'])){
        $mode = 'confirm';

        // Insert purchase into database
        $stmt = $conn->prepare("INSERT INTO purchases (user_id, comic_id, amount_paid, purchase_date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iid", $user_id, $comic_id, $amount);
        $stmt->execute();

        $purchase_id = $stmt->insert_id;

        // Fetch inserted purchase info
        $stmt2 = $conn->prepare("SELECT purchases.*, users.full_name, users.email, comics.title, comics.price, comics.author
                                FROM purchases
                                JOIN users ON purchases.user_id = users.user_id
                                JOIN comics ON purchases.comic_id = comics.comic_id
                                WHERE purchases.purchase_id=?");
        $stmt2->bind_param("i", $purchase_id);
        $stmt2->execute();
        $purchase = $stmt2->get_result()->fetch_assoc();
    } else {
        // Preview mode: fetch comic info
        $stmt = $conn->prepare("SELECT * FROM comics WHERE comic_id=?");
        $stmt->bind_param("i", $comic_id);
        $stmt->execute();
        $comic = $stmt->get_result()->fetch_assoc();

        // Fetch user info
        $stmt2 = $conn->prepare("SELECT * FROM users WHERE user_id=?");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $user = $stmt2->get_result()->fetch_assoc();
    }
}

// ===============================
// Case 2: GET from my_purchases.php (View Invoice)
// ===============================
elseif(isset($_GET['id'])){
    $purchase_id = intval($_GET['id']);

    // Fetch purchase info
    $stmt = $conn->prepare("SELECT purchases.*, users.full_name, users.email, comics.title, comics.price, comics.author
                            FROM purchases
                            JOIN users ON purchases.user_id = users.user_id
                            JOIN comics ON purchases.comic_id = comics.comic_id
                            WHERE purchases.purchase_id=? AND purchases.user_id=?");
    $stmt->bind_param("ii", $purchase_id, $user_id);
    $stmt->execute();
    $purchase = $stmt->get_result()->fetch_assoc();

    if(!$purchase){
        echo "<p style='color:red;'>Invoice not found.</p>";
        include 'includes/footer.php';
        exit;
    }

    $mode = 'confirm'; // already purchased, show confirmed invoice
}
else{
    echo "<p style='color:red;'>No purchase data provided.</p>";
    include 'includes/footer.php';
    exit;
}
?>

<h2>Purchase Invoice</h2>

<?php if($mode === 'preview'): ?>
    <div class="invoice-card">
        <h3>Invoice Preview</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>

        <h4>Purchased Item</h4>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Comic</th>
                <th>Author</th>
                <th>Price</th>
            </tr>
            <tr>
                <td><?php echo htmlspecialchars($comic['title']); ?></td>
                <td><?php echo htmlspecialchars($comic['author']); ?></td>
                <td>$<?php echo number_format($comic['price'],2); ?></td>
            </tr>
        </table>
        <h3>Total: $<?php echo number_format($comic['price'],2); ?></h3>

        <!-- Confirm Purchase Form -->
        <form method="POST">
            <input type="hidden" name="comic_id" value="<?php echo $comic['comic_id']; ?>">
            <input type="hidden" name="amount" value="<?php echo $comic['price']; ?>">
            <button type="submit" name="confirm_purchase" class="btn-confirm">
                ✅ Confirm Purchase
            </button>
        </form>
        <p><a href="comics.php">← Back to Comics</a></p>
    </div>

<?php elseif($mode === 'confirm'): ?>
    <div class="invoice-card">
        <h3>Purchase Confirmed! ✅</h3>
        <p><strong>Invoice #<?php echo $purchase['purchase_id']; ?></strong></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($purchase['full_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($purchase['email']); ?></p>
        <p><strong>Date:</strong> <?php echo $purchase['purchase_date']; ?></p>

        <h4>Purchased Item</h4>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Comic</th>
                <th>Author</th>
                <th>Price</th>
            </tr>
            <tr>
                <td><?php echo htmlspecialchars($purchase['title']); ?></td>
                <td><?php echo htmlspecialchars($purchase['author']); ?></td>
                <td>$<?php echo number_format($purchase['amount_paid'],2); ?></td>
            </tr>
        </table>
        <h3>Total: $<?php echo number_format($purchase['amount_paid'],2); ?></h3>

        <button onclick="downloadPDF()">⬇️ Download PDF</button>
        <p><a href="comics.php">← Back to Comics</a></p>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script>
    function downloadPDF(){
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(16);
        doc.text("Invoice #<?php echo $purchase['purchase_id']; ?>", 14, 20);
        doc.setFontSize(12);
        doc.text("Name: <?php echo $purchase['full_name']; ?>", 14, 30);
        doc.text("Email: <?php echo $purchase['email']; ?>", 14, 38);
        doc.text("Date: <?php echo $purchase['purchase_date']; ?>", 14, 46);

        doc.autoTable({
            startY: 55,
            head: [['Comic', 'Author', 'Price']],
            body: [
                ['<?php echo $purchase['title']; ?>', '<?php echo $purchase['author']; ?>', '$<?php echo number_format($purchase['amount_paid'],2); ?>']
            ]
        });

        doc.text("Total: $<?php echo number_format($purchase['amount_paid'],2); ?>", 14, doc.lastAutoTable.finalY + 10);

        doc.save("Invoice_<?php echo $purchase['purchase_id']; ?>.pdf");
    }
    </script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>