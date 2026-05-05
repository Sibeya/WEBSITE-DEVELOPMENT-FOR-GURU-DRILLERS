<?php
// ============================================================
// GRD - Enquiry API (with Email Integration)
// ============================================================
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ---- GET: List enquiries or single ----
if ($method === 'GET') {
    $conn = getDB();

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM enquiries WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        // Mark as read
        if ($row) {
            $conn->query("UPDATE enquiries SET is_read=1 WHERE id=$id");
            echo json_encode(['success' => true, 'enquiry' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Not found']);
        }
        $conn->close(); exit;
    }

    if (isset($_GET['list'])) {
        $result = $conn->query("SELECT * FROM enquiries ORDER BY created_at DESC LIMIT 100");
        $rows = [];
        while ($r = $result->fetch_assoc()) $rows[] = $r;
        echo json_encode(['success' => true, 'enquiries' => $rows]);
        $conn->close(); exit;
    }
    echo json_encode(['success' => false, 'message' => 'Invalid request']); exit;
}

// ---- POST: Submit new enquiry ----
if ($method === 'POST') {
    $conn = getDB();

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $company  = trim($_POST['company'] ?? '');
    $interest = trim($_POST['product_interest'] ?? '');
    $message  = trim($_POST['message'] ?? '');

    // Validate
    if (empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Name and email are required.']); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']); exit;
    }

    // Sanitize
    $name     = htmlspecialchars($name);
    $email    = htmlspecialchars($email);
    $phone    = htmlspecialchars($phone);
    $company  = htmlspecialchars($company);
    $interest = htmlspecialchars($interest);
    $message  = htmlspecialchars($message);

    // Save to DB
    $stmt = $conn->prepare("INSERT INTO enquiries (name, email, phone, company, product_interest, message) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param('ssssss', $name, $email, $phone, $company, $interest, $message);
    $saved = $stmt->execute();

    if (!$saved) {
        echo json_encode(['success' => false, 'message' => 'Failed to save enquiry. Please try again.']); exit;
    }

    // Send Email Notification
    $emailSent = sendEnquiryEmail($name, $email, $phone, $company, $interest, $message);

    // Also send confirmation to user
    sendConfirmationEmail($name, $email, $interest);

    echo json_encode([
        'success' => true,
        'message' => '✅ Thank you, ' . $name . '! Your enquiry has been received. Our team will contact you within 24 hours.'
    ]);
    $conn->close();
    exit;
}

function sendEnquiryEmail($name, $email, $phone, $company, $interest, $message) {
    $to = SMTP_TO;
    $subject = '[GRD Enquiry] ' . ($interest ?: 'General Enquiry') . ' - ' . $name;

    $body = "
    <html><body style='font-family:Arial,sans-serif;background:#0A1628;color:#C0CDD9;padding:20px'>
    <div style='max-width:600px;margin:0 auto;background:#0E1F3D;border:1px solid rgba(35,86,200,0.3);padding:30px'>
      <div style='background:#0A1628;padding:15px;margin-bottom:20px;border-left:4px solid #C8291A'>
        <h2 style='color:#F4F7FA;margin:0;font-family:Arial;letter-spacing:2px'>NEW ENQUIRY RECEIVED</h2>
        <p style='color:#8FA3B8;margin:4px 0 0;font-size:13px'>GURUROCDRILLINGTOOL Website</p>
      </div>
      <table style='width:100%;border-collapse:collapse'>
        <tr><td style='padding:10px 0;border-bottom:1px solid rgba(35,86,200,0.15);color:#8FA3B8;width:140px;font-size:13px'>Name</td><td style='padding:10px 0;border-bottom:1px solid rgba(35,86,200,0.15);color:#F4F7FA'><strong>$name</strong></td></tr>
        <tr><td style='padding:10px 0;border-bottom:1px solid rgba(35,86,200,0.15);color:#8FA3B8;font-size:13px'>Email</td><td style='padding:10px 0;border-bottom:1px solid rgba(35,86,200,0.15);color:#2E6BE6'>$email</td></tr>
        <tr><td style='padding:10px 0;border-bottom:1px solid rgba(35,86,200,0.15);color:#8FA3B8;font-size:13px'>Phone</td><td style='padding:10px 0;border-bottom:1px solid rgba(35,86,200,0.15);color:#F4F7FA'>$phone</td></tr>
        <tr><td style='padding:10px 0;border-bottom:1px solid rgba(35,86,200,0.15);color:#8FA3B8;font-size:13px'>Company</td><td style='padding:10px 0;border-bottom:1px solid rgba(35,86,200,0.15);color:#F4F7FA'>$company</td></tr>
        <tr><td style='padding:10px 0;border-bottom:1px solid rgba(35,86,200,0.15);color:#8FA3B8;font-size:13px'>Product Interest</td><td style='padding:10px 0;border-bottom:1px solid rgba(35,86,200,0.15);color:#C8291A'><strong>$interest</strong></td></tr>
      </table>
      <div style='margin-top:20px;background:#0A1628;padding:16px;border-left:3px solid #2356C8'>
        <p style='color:#8FA3B8;font-size:12px;margin:0 0 8px;text-transform:uppercase;letter-spacing:1px'>Message</p>
        <p style='color:#F4F7FA;line-height:1.7;margin:0'>$message</p>
      </div>
      <p style='color:#8FA3B8;font-size:11px;margin-top:20px'>Sent from GRD Website Enquiry Form</p>
    </div></body></html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SITE_NAME . " <" . SMTP_FROM . ">\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    return mail($to, $subject, $body, $headers);
}

function sendConfirmationEmail($name, $toEmail, $interest) {
    $subject = 'Thank you for contacting GURUROCDRILLINGTOOL';
    $body = "
    <html><body style='font-family:Arial,sans-serif;background:#f5f5f5;padding:20px'>
    <div style='max-width:600px;margin:0 auto;background:#ffffff;border-top:4px solid #C8291A'>
      <div style='background:#0A1628;padding:24px;text-align:center'>
        <h1 style='color:#F4F7FA;margin:0;font-size:1.5rem;letter-spacing:3px'>GURUROCDRILLINGTOOL</h1>
        <p style='color:#2E6BE6;margin:6px 0 0;font-size:12px;letter-spacing:2px'>GRD — PRECISION ROCK DRILLING SOLUTIONS</p>
      </div>
      <div style='padding:30px'>
        <h2 style='color:#0A1628;font-size:1.2rem'>Dear $name,</h2>
        <p style='color:#555;line-height:1.7'>Thank you for reaching out to us! We have received your enquiry" . ($interest ? " regarding <strong>$interest</strong>" : "") . " and our technical team will get back to you within <strong>24 business hours</strong>.</p>
        <div style='background:#f8f8f8;border-left:4px solid #2356C8;padding:16px;margin:20px 0'>
          <p style='color:#0A1628;margin:0;font-weight:600'>What happens next?</p>
          <ul style='color:#555;margin:10px 0 0;padding-left:20px;line-height:2'>
            <li>Our team reviews your requirements</li>
            <li>We prepare technical specifications</li>
            <li>You receive a detailed quote</li>
          </ul>
        </div>
        <p style='color:#555;line-height:1.7'>For urgent requirements, call us at <strong>+91 XXXXX XXXXX</strong></p>
        <div style='text-align:center;margin-top:24px'>
          <a href='http://gururocdrillingtool.com' style='background:#C8291A;color:#fff;padding:12px 28px;text-decoration:none;font-weight:700;font-size:13px;letter-spacing:1px'>VISIT OUR WEBSITE</a>
        </div>
      </div>
      <div style='background:#0A1628;padding:16px;text-align:center'>
        <p style='color:#8FA3B8;font-size:11px;margin:0'>© " . date('Y') . " GURUROCDRILLINGTOOL. All Rights Reserved.</p>
      </div>
    </div></body></html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SITE_NAME . " <" . SMTP_FROM . ">\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    mail($toEmail, $subject, $body, $headers);
}
?>
