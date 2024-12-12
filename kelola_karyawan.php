<?php
session_start();
require_once 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Proses Tambah Karyawan
if (isset($_POST['tambah_karyawan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $id_kerja = mysqli_real_escape_string($koneksi, $_POST['id_kerja']);
    $id_group = mysqli_real_escape_string($koneksi, $_POST['id_group']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $tempat_lahir = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);

    $query = "INSERT INTO karyawan (nama, id_kerja, id_group, password, tempat_lahir, tanggal_lahir, alamat, no_hp, jabatan) 
              VALUES ('$nama', '$id_kerja', '$id_group', '$password', '$tempat_lahir', '$tanggal_lahir', '$alamat', '$no_hp', '$jabatan')";
    
    if (mysqli_query($koneksi, $query)) {
        $pesan_sukses = "Karyawan berhasil ditambahkan";
    } else {
        $pesan_error = "Error: " . mysqli_error($koneksi);
    }
}

// Proses Update Karyawan
if (isset($_POST['update_karyawan'])) {
    $id_karyawan = mysqli_real_escape_string($koneksi, $_POST['id_karyawan']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $id_kerja = mysqli_real_escape_string($koneksi, $_POST['id_kerja']);
    $id_group = mysqli_real_escape_string($koneksi, $_POST['id_group']);
    $tempat_lahir = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);

    $query = "UPDATE karyawan SET 
              nama = '$nama', 
              id_kerja = '$id_kerja',
              id_group = '$id_group',
              tempat_lahir = '$tempat_lahir', 
              tanggal_lahir = '$tanggal_lahir', 
              alamat = '$alamat', 
              no_hp = '$no_hp',
              jabatan = '$jabatan'";
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query .= ", password = '$password'";
    }
    
    $query .= " WHERE id_karyawan = $id_karyawan";
    
    if (mysqli_query($koneksi, $query)) {
        $pesan_sukses = "Data karyawan berhasil diupdate";
    } else {
        $pesan_error = "Error: " . mysqli_error($koneksi);
    }
}

// Proses Hapus Karyawan
if (isset($_GET['hapus'])) {
    $id_karyawan = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $query = "DELETE FROM karyawan WHERE id_karyawan = $id_karyawan";
    
    if (mysqli_query($koneksi, $query)) {
        $pesan_sukses = "Karyawan berhasil dihapus";
    } else {
        $pesan_error = "Error: " . mysqli_error($koneksi);
    }
}

// Ambil data karyawan
$query_karyawan = "SELECT k.*, g.nama_group AS nama_group 
                   FROM karyawan k
                   LEFT JOIN `groups` g ON k.id_group = g.id_group
                   ORDER BY k.nama";

$result_karyawan = mysqli_query($koneksi, $query_karyawan);

// Ambil data grup karyawan
$query_groups = "SELECT * FROM `groups`";
$result_groups = mysqli_query($koneksi, $query_groups);
$groups_options = [];
while ($row = mysqli_fetch_assoc($result_groups)) {
    $groups_options[$row['id_group']] = $row['nama_group'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Kelola Karyawan - BEP Dashboard</title>
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
                <a class="nav-link active" href="kelola_karyawan.php"><i class="fas fa-users"></i> Kelola Karyawan</a>
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
            <h1 class="mt-4">Kelola Karyawan</h1>
            
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
                    <h2 class="mb-0"><?php echo isset($_GET['edit']) ? 'Update Karyawan' : 'Tambah Karyawan'; ?></h2>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <?php 
                        // Jika mode edit, ambil data karyawan yang akan diedit
                        if (isset($_GET['edit'])) {
                            $id_edit = mysqli_real_escape_string($koneksi, $_GET['edit']);
                            $query_edit = "SELECT * FROM karyawan WHERE id_karyawan = $id_edit";
                            $result_edit = mysqli_query($koneksi, $query_edit);
                            $data_edit = mysqli_fetch_assoc($result_edit);
                        }
                        ?>
                        <input type="hidden" name="id_karyawan" value="<?php echo isset($data_edit) ? $data_edit['id_karyawan'] : ''; ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama:</label>
                                <input type="text" class="form-control" name="nama" required value="<?php echo isset($data_edit) ? $data_edit['nama'] : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ID Kerja:</label>
                                <input type="text" class="form-control" name="id_kerja" required value="<?php echo isset($data_edit) ? $data_edit['id_kerja'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Grup Karyawan:</label>
                                <select name="id_group" class="form-select" required>
                                    <option value="">Pilih Grup</option>
                                    <?php foreach ($groups_options as $group_id => $group_name): ?>
                                        <option value="<?php echo $group_id; ?>" <?php echo isset($data_edit) && $data_edit['id_group'] == $group_id ? 'selected' : ''; ?>><?php echo $group_name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <?php echo isset($_GET['edit']) ? '(kosongkan jika tidak diubah)' : ''; ?>:</label>
                                <input type="password" class="form-control" name="password" <?php echo isset($_GET['edit']) ? '' : 'required'; ?>>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tempat Lahir:</label>
                                <input type="text" class="form-control" name="tempat_lahir" value="<?php echo isset($data_edit) ? $data_edit['tempat_lahir'] : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Lahir:</label>
                                <input type="date" class="form-control" name="tanggal_lahir" value="<?php echo isset($data_edit) ? $data_edit['tanggal_lahir'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">No HP:</label>
                                <input type="text" class="form-control" name="no_hp" value="<?php echo isset($data_edit) ? $data_edit['no_hp'] : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jabatan:</label>
                                <input type="text" class="form-control" name="jabatan" value="<?php echo isset($data_edit) ? $data_edit['jabatan'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat:</label>
                            <textarea class="form-control" name="alamat" rows="3"><?php echo isset($data_edit) ? $data_edit['alamat'] : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <input type="submit" class="btn btn-primary" name="<?php echo isset($_GET['edit']) ? 'update_karyawan' : 'tambah_karyawan'; ?>" 
                                   value="<?php echo isset($_GET['edit']) ? 'Update' : 'Tambah'; ?> Karyawan">
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Daftar Karyawan</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>ID Kerja</th>
                                    <th>Grup</th>
                                    <th>Tempat Lahir</th>
                                    <th>Tanggal Lahir</th>
                                    <th>No HP</th>
                                    <th>Jabatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_karyawan)): ?>
                                <tr>
                                    <td><?php echo $row['id_karyawan']; ?></td>
                                    <td><?php echo $row['nama']; ?></td>
                                    <td><?php echo $row['id_kerja']; ?></td>
                                    <td><?php echo $row['nama_group']; ?></td>
                                    <td><?php echo $row['tempat_lahir']; ?></td>
                                    <td><?php echo $row['tanggal_lahir']; ?></td>
                                    <td><?php echo $row['no_hp']; ?></td>
                                    <td><?php echo $row['jabatan']; ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $row['id_karyawan']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="?hapus=<?php echo $row['id_karyawan']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?');"><i class="fas fa-trash"></i> Hapus</a>
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