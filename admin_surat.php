<?php
// Add authentication check here
include 'function.php';

// Fetch all submitted letters
$query = "SELECT * FROM surat_masuk ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="BEP Dashboard - Daftar Surat Masuk" />
    <meta name="author" content="Your Company Name" />
    <title>Admin - Surat Masuk</title>
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
        }

        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            min-height: 100vh;
            position: fixed;
        }

        .sidebar .nav-link {
            color: white;
            font-size: 1rem;
        }

        .sidebar .nav-link:hover {
            background-color: #0056b3;
            color: white;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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
                <a class="nav-link active" href="surat_masuk.php"><i class="fas fa-envelope"></i> Surat Masuk</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="kelola_karyawan.php"><i class="fas fa-users"></i> Kelola Karyawan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="jadwal_kerja.php"><i class="fas fa-calendar-alt"></i> Jadwal Kerja</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>

    <div class="content">
        <h1 class="text-center mb-4">Daftar Surat Masuk</h1>

        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">Surat Masuk</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Nama Karyawan</th>
                                <th>Jenis Surat</th>
                                <th>Judul</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nama_karyawan']); ?></td>
                                <td><?php echo htmlspecialchars($row['jenis_surat']); ?></td>
                                <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        echo $row['status'] == 'Pending' ? 'badge-warning' : 
                                             ($row['status'] == 'Disetujui' ? 'badge-success' : 'badge-danger');
                                        ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="detail_surat.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Detail</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
