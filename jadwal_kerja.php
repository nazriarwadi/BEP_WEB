<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "bep_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Hapus otomatis jadwal yang sudah kadaluarsa
$deleteExpiredSchedulesQuery = "
    DELETE FROM jadwal_kerja 
    WHERE tanggal_selesai < CURDATE()
";
$conn->query($deleteExpiredSchedulesQuery);

// Ambil daftar grup karyawan
$groupQuery = "SELECT id_group, nama_group FROM `groups`";
$groupResult = $conn->query($groupQuery);
$groupOptions = [];
if ($groupResult) {
    while ($row = $groupResult->fetch_assoc()) {
        $groupOptions[] = $row;
    }
} else {
    die("Error fetching groups: " . $conn->error);
}

// Proses tambah jadwal untuk grup
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_jadwal'])) {
    $id_group = $conn->real_escape_string($_POST['group_id']);
    $tanggal_mulai = $conn->real_escape_string($_POST['tanggal_mulai']);
    $tanggal_selesai = $conn->real_escape_string($_POST['tanggal_selesai']);
    $jam_mulai = $conn->real_escape_string($_POST['jam_mulai']);
    $jam_selesai = $conn->real_escape_string($_POST['jam_selesai']);
    $lokasi = $conn->real_escape_string($_POST['lokasi']);

    $insertQuery = "
        INSERT INTO jadwal_kerja (id_karyawan, tanggal_mulai, tanggal_selesai, jam_mulai, jam_selesai, lokasi)
        SELECT k.id_karyawan, ?, ?, ?, ?, ?
        FROM karyawan k
        WHERE k.id_group = ?
    ";
    if ($stmt = $conn->prepare($insertQuery)) {
        $stmt->bind_param("sssssi", $tanggal_mulai, $tanggal_selesai, $jam_mulai, $jam_selesai, $lokasi, $id_group);
        if ($stmt->execute() === TRUE) {
            echo "<script>alert('Jadwal berhasil ditambahkan.');</script>";
            echo "<script>window.location.href='jadwal_kerja.php';</script>";
        } else {
            echo "Error: " . $insertQuery . "<br>" . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Proses edit jadwal untuk grup
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_jadwal'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $id_group = $conn->real_escape_string($_POST['group_id']);
    $tanggal_mulai = $conn->real_escape_string($_POST['tanggal_mulai']);
    $tanggal_selesai = $conn->real_escape_string($_POST['tanggal_selesai']);
    $jam_mulai = $conn->real_escape_string($_POST['jam_mulai']);
    $jam_selesai = $conn->real_escape_string($_POST['jam_selesai']);
    $lokasi = $conn->real_escape_string($_POST['lokasi']);

    $updateQuery = "
        UPDATE jadwal_kerja
        SET tanggal_mulai = ?, tanggal_selesai = ?, jam_mulai = ?, jam_selesai = ?, lokasi = ?
        WHERE id = ?
    ";
    if ($stmt = $conn->prepare($updateQuery)) {
        $stmt->bind_param("sssssi", $tanggal_mulai, $tanggal_selesai, $jam_mulai, $jam_selesai, $lokasi, $id);
        if ($stmt->execute() === TRUE) {
            echo "<script>alert('Jadwal berhasil diperbarui.');</script>";
            echo "<script>window.location.href='jadwal_kerja.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Proses hapus jadwal untuk grup
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hapus_jadwal'])) {
    $id_group = $conn->real_escape_string($_POST['group_id']);

    $deleteQuery = "
        DELETE FROM jadwal_kerja
        WHERE id_karyawan IN (
            SELECT id_karyawan
            FROM karyawan
            WHERE id_group = ?
        )
    ";
    if ($stmt = $conn->prepare($deleteQuery)) {
        $stmt->bind_param("i", $id_group);
        if ($stmt->execute() === TRUE) {
            echo "<script>alert('Semua jadwal untuk grup ini berhasil dihapus.');</script>";
            echo "<script>window.location.href='jadwal_kerja.php';</script>";
        } else {
            echo "Error: " . $deleteQuery . "<br>" . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Query jadwal kerja berdasarkan grup, hanya menampilkan satu nama grup
$jadwalQuery = "
    SELECT 
        g.id_group, 
        g.nama_group, 
        MAX(jk.tanggal_mulai) AS tanggal_mulai, 
        MAX(jk.tanggal_selesai) AS tanggal_selesai, 
        MAX(jk.jam_mulai) AS jam_mulai, 
        MAX(jk.jam_selesai) AS jam_selesai, 
        MAX(jk.lokasi) AS lokasi, 
        MAX(jk.id) AS id_jadwal
    FROM `groups` g
    LEFT JOIN karyawan k ON g.id_group = k.id_group
    LEFT JOIN jadwal_kerja jk ON k.id_karyawan = jk.id_karyawan
    GROUP BY g.id_group, g.nama_group
";

$jadwalResult = $conn->query($jadwalQuery);

// Cek apakah query berhasil
if (!$jadwalResult) {
    die("Query Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="BEP Dashboard - Daftar Jadwal Kerja Karyawan" />
    <meta name="author" content="Your Company Name" />
    <title>Admin - Jadwal kerja Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --primary-color: #001f3f; /* Navy */
            --secondary-color: #f8f9fa;
            --text-color: #333;
        }

        body {
            display: flex;
            margin: 0;
            font-family: Arial, sans-serif;
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
            font-size: 1rem;
            padding: 10px 15px;
            display: block;
        }

        .sidebar .nav-link:hover {
            background-color: #0056b3;
            color: white;
        }

        .sidebar .nav-link.active {
            background-color: #0056b3;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            box-sizing: border-box;
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                position: absolute;
                width: 200px;
                z-index: 999;
                transition: transform 0.3s ease-in-out;
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .content {
                margin-left: 0;
                width: 100%;
            }

            .btn-primary.d-block.d-md-none {
                display: block;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar">
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
                <a class="nav-link" href="jadwal_kerja.php"><i class="fas fa-calendar-alt"></i> Jadwal Kerja Karyawan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>

    <div class="content">
        <button class="btn btn-primary d-block d-md-none mb-3" id="toggleSidebar">Toggle Menu</button>
        <h2 class="mb-4">Tambah Jadwal Kerja</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="group_id" class="form-label">Grup Karyawan:</label>
                <select name="group_id" id="group_id" class="form-select" required>
                    <?php foreach ($groupOptions as $group): ?>
                        <option value="<?php echo $group['id_group']; ?>"><?php echo $group['nama_group']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="tanggal_mulai" class="form-label">Tanggal Mulai:</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="tanggal_selesai" class="form-label">Tanggal Selesai:</label>
                <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="jam_mulai" class="form-label">Jam Mulai:</label>
                <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="jam_selesai" class="form-label">Jam Selesai:</label>
                <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="lokasi" class="form-label">Lokasi:</label>
                <input type="text" name="lokasi" id="lokasi" class="form-control" required>
            </div>

            <button type="submit" name="tambah_jadwal" class="btn btn-primary">Tambah Jadwal</button>
        </form>

        <h2 class="mt-5">Daftar Jadwal Kerja</h2>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Grup</th>
                    <th>Periode Kerja</th>
                    <th>Jam Kerja</th>
                    <th>Lokasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
    <?php if ($jadwalResult->num_rows > 0): ?>
        <?php while ($row = $jadwalResult->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['nama_group']; ?></td>
                <td><?php echo $row['tanggal_mulai'] . " - " . $row['tanggal_selesai']; ?></td>
                <td><?php echo $row['jam_mulai'] . " - " . $row['jam_selesai']; ?></td>
                <td><?php echo $row['lokasi']; ?></td>
                <td>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $row['id_jadwal']; ?>" data-group="<?php echo $row['id_group']; ?>" data-start="<?php echo $row['tanggal_mulai']; ?>" data-end="<?php echo $row['tanggal_selesai']; ?>" data-start-time="<?php echo $row['jam_mulai']; ?>" data-end-time="<?php echo $row['jam_selesai']; ?>" data-location="<?php echo $row['lokasi']; ?>">
                        Edit
                    </button>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-group="<?php echo $row['id_group']; ?>">
                        Hapus
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" class="text-center">Tidak ada jadwal</td>
        </tr>
    <?php endif; ?>
</tbody>

        </table>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Jadwal Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="id_jadwal">
                        <input type="hidden" name="group_id" id="group_id">
                        <div class="mb-3">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai:</label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai:</label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="jam_mulai" class="form-label">Jam Mulai:</label>
                            <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="jam_selesai" class="form-label">Jam Selesai:</label>
                            <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi:</label>
                            <input type="text" name="lokasi" id="lokasi" class="form-control" required>
                        </div>
                        <button type="submit" name="edit_jadwal" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Hapus Semua Jadwal Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus semua jadwal untuk grup ini?</p>
                    <form method="POST" action="">
                        <input type="hidden" name="group_id" id="delete_group_id">
                        <button type="submit" name="hapus_jadwal" class="btn btn-danger">Hapus Semua</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleSidebar = document.getElementById('toggleSidebar');
        const sidebar = document.querySelector('.sidebar');

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        const editButtons = document.querySelectorAll('button[data-bs-target="#editModal"]');
        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                const id_jadwal = button.dataset.id;
                const groupId = button.dataset.group;
                const startDate = button.dataset.start;
                const endDate = button.dataset.end;
                const startTime = button.dataset.startTime;
                const endTime = button.dataset.endTime;
                const location = button.dataset.location;

                document.getElementById('id_jadwal').value = id_jadwal;
                document.getElementById('group_id').value = groupId;
                document.getElementById('tanggal_mulai').value = startDate;
                document.getElementById('tanggal_selesai').value = endDate;
                document.getElementById('jam_mulai').value = startTime;
                document.getElementById('jam_selesai').value = endTime;
                document.getElementById('lokasi').value = location;
            });
        });

        const deleteButtons = document.querySelectorAll('button[data-bs-target="#deleteModal"]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                const groupId = button.dataset.group;
                document.getElementById('delete_group_id').value = groupId;
            });
        });
    </script>
</body>

</html>
