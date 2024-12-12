<?php
session_start();
include 'function.php';

// Get specific letter details
$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM surat_masuk WHERE id = '$id'";
$result = mysqli_query($conn, $query);
$surat = mysqli_fetch_assoc($result);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    
    $update_query = "UPDATE surat_masuk SET status = '$status', catatan = '$catatan' WHERE id = '$id'";
    mysqli_query($conn, $update_query);
    
    header("Location: admin_surat.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Surat</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Detail Surat</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($surat['judul']); ?></h5>
                <p><strong>Nama Karyawan:</strong> <?php echo htmlspecialchars($surat['nama_karyawan']); ?></p>
                <p><strong>Jenis Surat:</strong> <?php echo htmlspecialchars($surat['jenis_surat']); ?></p>
                <p><strong>Deskripsi:</strong> <?php echo htmlspecialchars($surat['deskripsi']); ?></p>
                <p><strong>Tanggal:</strong> <?php echo htmlspecialchars($surat['tanggal']); ?></p>
                
                <?php if($surat['file_path']): ?>
                <p><strong>File:</strong> 
                    <a href="<?php echo htmlspecialchars($surat['file_path']); ?>" target="_blank">Lihat File</a>
                </p>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="Pending" <?php echo $surat['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Disetujui" <?php echo $surat['status'] == 'Disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                            <option value="Ditolak" <?php echo $surat['status'] == 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="catatan" class="form-control"><?php echo htmlspecialchars($surat['catatan'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Perbarui Status</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>