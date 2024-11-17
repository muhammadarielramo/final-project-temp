<?php
session_start();
include 'includes/config.php';
require __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Simpan data registrasi di database
    $stmt = $pdo->prepare("INSERT INTO registrations (event_id, name, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$event_id, $name, $email, $phone]);
    $registration_id = $pdo->lastInsertId();

    // Generate QR Code untuk tiket
    $qrContent = "$registration_id:$event_id";

    $builder = new Builder(
        writer: new PngWriter(),
        data: ($qrContent),
        encoding: new Encoding('UTF-8'),
        errorCorrectionLevel: ErrorCorrectionLevel::High,
        labelText: 'Scan QR di Pintu Masuk',
        labelAlignment: LabelAlignment::Center
    );

    $result = $builder->build();

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    $qrPath = "assets/uploads/qr_$registration_id.png";
    $result->saveToFile($qrPath);

    // Kirim Email ke Peserta dengan QR Code
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'bproticdummy@gmail.com';
        $mail->Password = 'hefk xvuq srzg tqsg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('bproticdummy@gmail.com', 'BPROTIC');
        $mail->addAddress($email, $name);
        $mail->Subject = 'Tiket Event Anda';
        $mail->Body = "Terima kasih sudah mendaftar untuk event kami! Silakan temukan QR code Anda terlampir sebagai tiket masuk.";
        $mail->addAttachment($qrPath);

        $mail->send();
        echo "Registrasi berhasil! Tiket telah dikirim ke email Anda.";
    } catch (Exception $e) {
        echo "Pesan tidak dapat dikirim. Error: {$mail->ErrorInfo}";
    }

    exit;
}

$event_id = $_GET['event_id'];
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "Event tidak ditemukan!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi untuk <?= htmlspecialchars($event['title']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .navbar {
            background-color: #191970 !important;
        }
        .btn-primary {
            background-color: #a51b20;
            border-color: #a51b20;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="assets/images/logo-bprotic.png" alt="Logo" width="40" height="40" class="me-2">
                BPROTIC
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#events">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#footer">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Registration Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4" style="color: #191970;">Registrasi untuk <?= htmlspecialchars($event['title']) ?></h2>
            <form method="POST" class="w-50 mx-auto">
                <input type="hidden" name="event_id" value="<?= $event_id ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Telepon</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <button type="submit" class="btn btn-primary w-100">Daftar</button>
            </form>
        </div>
    </section>

    <!-- About and Social Media Section -->
    <div class="container-fluid py-5" style="background-color: #2C2C7C;">
        <div class="d-flex justify-content-between align-items-center mx-5">
            <div class="about-section" style="flex: 1; margin-right: 50px;">
                <h4 class="mb-3" style="color: #ffffff;">About</h4>
                <p style="line-height: 1.8; color: #FFFFFF;">
                    Event Center adalah platform penyelenggaraan acara yang menyediakan berbagai informasi terkini
                    mengenai event-event menarik yang dapat diikuti.
                </p>
            </div>
            <div class="social-media-section text-center" style="flex: 1;">
                <h4 class="mb-3" style="color: #FFFFFF;">Follow Us on Social Media</h4>
                <div class="d-flex justify-content-center mt-3">
                    <a href="https://www.facebook.com" target="_blank" class="text-decoration-none mx-3" style="color: #FFF000; font-size: 1.5rem;">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://www.x.com" target="_blank" class="text-decoration-none mx-3" style="color: #FFF000; font-size: 1.5rem;">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <a href="https://www.instagram.com" target="_blank" class="text-decoration-none mx-3" style="color: #FFF000; font-size: 1.5rem;">
                        <i class="bi bi-instagram"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4" style="background-color: #191970; color: white;">
        <p class="mb-0">&copy; 2024 Event Center. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
