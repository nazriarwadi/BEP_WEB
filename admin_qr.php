<?php
require_once 'function.php'; // Hubungkan ke file fungsi

// Pastikan user yang login adalah admin
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fungsi untuk memperbarui status sesi menjadi 'inactive'
function updateSessionToInactive()
{
    global $conn;

    $query = "UPDATE sesi_absensi SET status = 'inactive' WHERE status = 'active'";
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = 'Sesi berhasil diubah menjadi inactive';
        return true;
    } else {
        $_SESSION['message'] = 'Gagal memperbarui sesi';
        return false;
    }
}

// Fungsi untuk mendapatkan sesi aktif
function getActiveSession()
{
    global $conn;
    $query = "SELECT * FROM sesi_absensi WHERE status = 'active' ORDER BY created_at DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk membuat sesi baru
function createAttendanceSession($type)
{
    global $conn;

    // Nonaktifkan semua sesi yang aktif
    updateSessionToInactive();

    // Generate id_sesi secara manual
    $randomId = rand(1000, 9999); // Angka acak 4 digit, dapat disesuaikan

    // Buat sesi baru
    $query = "INSERT INTO sesi_absensi (id_sesi, tipe, status, created_at) VALUES (?, ?, 'active', NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $randomId, $type); // 'i' untuk integer (id_sesi), 's' untuk string (tipe)
    return $stmt->execute();
}

// Variabel untuk pesan dan sesi aktif
$message = '';
$activeSession = getActiveSession();

// Tangani form pengiriman data untuk membuat sesi baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['attendance_type'] ?? '';
    if ($type && in_array($type, ['masuk', 'pulang'])) {
        if (createAttendanceSession($type)) {
            // Set session flash message
            $_SESSION['message'] = "Sesi absensi $type berhasil dibuat!";
            // Redirect to avoid duplicate POST
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $_SESSION['message'] = "Gagal membuat sesi absensi!";
        }
    }
}

// Ambil pesan dari session jika ada
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Hapus pesan setelah diambil
}
?>

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin QR Code Absensi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --primary-color: #001f3f;
            /* Navy */
            --secondary-color: #f8f9fa;
            --text-color: #333;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            color: white;
            padding: 10px 15px;
            display: block;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #0056b3;
            color: white;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            flex: 1;
        }

        .sb-topnav {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
        }

        .sb-topnav .navbar-brand {
            color: white;
        }

        .qr-container {
            text-align: center;
            margin: 20px auto;
            padding: 20px;
            max-width: 500px;
        }

        .countdown {
            font-size: 1.2em;
            margin: 15px 0;
        }

        .status-active {
            color: green;
            font-weight: bold;
        }

        .status-inactive {
            color: red;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                position: absolute;
                z-index: 999;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <h3 class="text-center py-3">BEP Dashboard</h3>
        <ul class="nav flex-column px-3">
            <li class="nav-item">
                <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="admin_qr.php"><i class="fas fa-qrcode"></i> QR Absensi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="kelola_karyawan.php"><i class="fas fa-users"></i> Kelola Karyawan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="jadwal_kerja.php"><i class="fas fa-calendar-alt"></i> Jadwal Kerja
                    Karyawan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>

    <div class="content">
        <nav class="sb-topnav navbar navbar-expand navbar-light">
            <a class="navbar-brand" href="index.php">BEP Dashboard</a>
            <button class="btn btn-link btn-sm" id="toggleSidebar"><i class="fas fa-bars"></i></button>
        </nav>

        <div class="container mt-5">
            <h2 class="text-center mb-4">Admin QR Code Absensi</h2>

            <!-- Menampilkan pesan -->
            <?php if ($message): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="row">
                <!-- Kolom untuk membuat sesi baru -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Buat Sesi Baru</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="attendance_type" class="form-label">Tipe Absensi</label>
                                    <select name="attendance_type" id="attendance_type" class="form-select" required>
                                        <option value="">Pilih</option>
                                        <option value="masuk">Absen Masuk</option>
                                        <option value="pulang">Absen Pulang</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Buat Sesi</button>
                            </form>
                        </div>
                    </div>

                    <!-- Status sesi aktif -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">Status Sesi Aktif</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($activeSession): ?>
                                <p>Tipe: <strong><?php echo ucfirst($activeSession['tipe']); ?></strong></p>
                                <p>Status: <span class="status-active">Aktif</span></p>
                                <p>Dibuat: <?php echo date('d/m/Y H:i:s', strtotime($activeSession['created_at'])); ?></p>
                            <?php else: ?>
                                <p class="status-inactive">Tidak ada sesi aktif</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Kolom untuk QR Code -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">QR Code Absensi</h5>
                        </div>
                        <div class="card-body qr-container">
                            <?php if ($activeSession): ?>
                                <div id="qrcode"></div>
                                <p class="countdown">Berlaku untuk: <span id="timer">5:00</span></p>
                            <?php else: ?>
                                <p class="text-center">Buat sesi absensi terlebih dahulu</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleSidebar = document.getElementById('toggleSidebar');

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        <?php if ($activeSession): ?>
            const qrcode = new QRCode(document.getElementById("qrcode"), { width: 256, height: 256 });

            // Encode session data as JSON
            const sessionData = JSON.stringify({
                id: "<?php echo $activeSession['id']; ?>",
                id_sesi: "<?php echo $activeSession['id_sesi']; ?>",
                tipe: "<?php echo $activeSession['tipe']; ?>",
                status: "<?php echo $activeSession['status']; ?>",
                created_at: "<?php echo $activeSession['created_at']; ?>",
                updated_at: "<?php echo $activeSession['updated_at']; ?>"
            });

            // Generate QR Code with session data
            qrcode.makeCode(sessionData);

            // Countdown timer (5 minutes)
            let timer = 300;
            const timerElement = document.getElementById('timer');
            if (timerElement) {
                const interval = setInterval(() => {
                    if (timer > 0) {
                        timer--;
                        const minutes = Math.floor(timer / 60).toString().padStart(2, '0');
                        const seconds = (timer % 60).toString().padStart(2, '0');
                        timerElement.textContent = `${minutes}:${seconds}`;
                    } else {
                        clearInterval(interval); // Stop timer
                        timerElement.textContent = "Expired"; // Display expired message

                        // Kirim permintaan ke server untuk mengubah status menjadi inactive
                        fetch('expire_session.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    console.log('Sesi berhasil diubah menjadi inactive');
                                    alert('Sesi telah kadaluarsa dan dinonaktifkan.');
                                    // Reload halaman untuk memperbarui status sesi
                                    window.location.reload();
                                } else {
                                    console.error(data.error);
                                    alert('Gagal memperbarui status sesi.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan saat memperbarui status sesi.');
                            });
                    }
                }, 1000);
            } else {
                console.error("Timer element not found.");
            }

        <?php else: ?>
            document.getElementById("qrcode").textContent = "No active session available.";
        <?php endif; ?>
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
</body>

</html>