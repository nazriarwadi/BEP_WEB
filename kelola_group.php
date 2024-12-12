<?php
session_start();
require_once 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Proses Tambah Grup
if (isset($_POST['tambah_grup'])) {
    $nama_group = mysqli_real_escape_string($koneksi, $_POST['nama_group']);

    $query = "INSERT INTO groups (nama_group) VALUES ('$nama_group')";
    
    if (mysqli_query($koneksi, $query)) {
        $pesan_sukses = "Grup berhasil ditambahkan";
    } else {
        $pesan_error = "Error: " . mysqli_error($koneksi);
    }
}

// Proses Update Grup
if (isset($_POST['update_grup'])) {
    $id_group = mysqli_real_escape_string($koneksi, $_POST['id_group']);
    $nama_group = mysqli_real_escape_string($koneksi, $_POST['nama_group']);

    $query = "UPDATE groups SET nama_group = '$nama_group' WHERE id_group = $id_group";
    
    if (mysqli_query($koneksi, $query)) {
        $pesan_sukses = "Data grup berhasil diupdate";
    } else {
        $pesan_error = "Error: " . mysqli_error($koneksi);
    }
}

// Proses Hapus Grup
if (isset($_GET['hapus'])) {
    $id_group = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $query = "DELETE FROM groups WHERE id_group = $id_group";
    
    if (mysqli_query($koneksi, $query)) {
        $pesan_sukses = "Grup berhasil dihapus";
    } else {
        $pesan_error = "Error: " . mysqli_error($koneksi);
    }
}

// Ambil data grup
$query_groups = "SELECT * FROM groups ORDER BY nama_group";
$result_groups = mysqli_query($koneksi, $query_groups);

if (!$result_groups) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Kelola Grup Karyawan - BEP Dashboard</title>
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

        .pesan-sukses { 
            color: green; 
            margin: 15px 0;
        }

        .pesan-error { 
            color: red; 
            margin: 15px 0;
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
                <a class="nav-link" href="admin_qr.php"><i class="fas fa-qrcode"></i> QR Absensi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="kelola_karyawan.php"><i class="fas fa-users"></i> Kelola Karyawan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="kelola_grup.php"><i class="fas fa-layer-group"></i> Kelola Grup</a>
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
        <div class="container-fluid">
            <h1 class="mt-4">Kelola Grup Karyawan</h1>
            
            <?php 
            if (isset($pesan_sukses)) {
                echo "<div class='alert alert-success'>$pesan_sukses</div>";
            }
            if (isset($pesan_error)) {
                echo "<div class='alert alert-danger'>$pesan_error</div>";
            }
            ?>

            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="mb-0"><?php echo isset($_GET['edit']) ? 'Update Grup' : 'Tambah Grup'; ?></h2>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <?php 
                        // Jika mode edit, ambil data grup yang akan diedit
                        if (isset($_GET['edit'])) {
                            $id_edit = mysqli_real_escape_string($koneksi, $_GET['edit']);
                            $query_edit = "SELECT * FROM groups WHERE id_group = $id_edit";
                            $result_edit = mysqli_query($koneksi, $query_edit);
                            $data_edit = mysqli_fetch_assoc($result_edit);
                        }
                        ?>
                        <input type="hidden" name="id_group" value="<?php echo isset($data_edit) ? $data_edit['id_group'] : ''; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Grup:</label>
                            <input type="text" class="form-control" name="nama_group" required value="<?php echo isset($data_edit) ? $data_edit['nama_group'] : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <input type="submit" class="btn btn-primary" name="<?php echo isset($_GET['edit']) ? 'update_grup' : 'tambah_grup'; ?>" 
                                   value="<?php echo isset($_GET['edit']) ? 'Update' : 'Tambah'; ?> Grup">
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Daftar Grup Karyawan</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Grup</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_groups)): ?>
                                <tr>
                                    <td><?php echo $row['id_group']; ?></td>
                                    <td><?php echo $row['nama_group']; ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $row['id_group']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="?hapus=<?php echo $row['id_group']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?');"><i class="fas fa-trash"></i> Hapus</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
mysqli_close($koneksi);
?>
