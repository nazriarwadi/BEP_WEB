<?php
require 'function.php';
$conn = mysqli_connect("localhost", "root", "", "bep_db");

if (isset($_POST['cetak_pdf'])) {
    require('fpdf186/fpdf.php');

    class PDF extends FPDF
    {
        function Watermark($txt)
        {
            // Set the position for the watermark slightly down (15mm from the top)
            $this->SetXY(10, 15); // Adjusted to give space at the top
            $this->SetFont('Arial', 'I', 8); // Italic, size 8 for a smaller watermark
            $this->SetTextColor(100, 100, 100); // Slightly darker gray for visibility
            $this->Cell(0, 10, $txt, 0, 0, 'L'); // Left aligned
        }
    }

    $bulanDipilih = $_POST['bulan'];

    $pdf = new PDF();
    $pdf->AddPage();

    // Set the left and right margins
    $pdf->SetLeftMargin(5);
    $pdf->SetRightMargin(5);

    // Watermark (small text on the top left)
    $watermarkText = 'Dicetak pada tanggal: ' . date('Y-m-d H:i:s');
    $pdf->Watermark($watermarkText); // Call the watermark function

    $pdf->SetFont('Arial', 'B', 16); // Larger font for title

    // Center title horizontally and vertically
    $pdf->SetY($pdf->GetPageHeight() / 8 - 10); // Center vertically
    $pdf->Cell(0, 10, 'Laporan Pemasukan Bulan ' . date('F', mktime(0, 0, 0, $bulanDipilih, 10)), 0, 1, 'C');

    // Table header
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 10, 'Tanggal', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Nama Produk', 1, 0, 'C');  // Reduced width for Nama Produk
    $pdf->Cell(50, 10, 'Harga per 1kg (Rp)', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Jumlah (kg)', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Total Pemasukan (Rp)', 1, 1, 'C');

    $pdf->SetFont('Arial', '', 10);

    // Fetch data from database
    $ambilsemuadatastock = mysqli_query($conn, "SELECT * FROM keluar m, stock s WHERE s.idproduk = m.idproduk AND MONTH(m.tanggal) = '$bulanDipilih'");
    $totalPemasukan = 0;

    while ($data = mysqli_fetch_array($ambilsemuadatastock)) {
        $tanggal = $data['tanggal'];
        $namaproduk = $data['namaproduk'];
        $harga1kg = $data['harga1kg'];
        $qty = $data['qty'];
        $total = $harga1kg * $qty;
        $totalPemasukan += $total;

        // Table data rows
        $pdf->Cell(40, 10, $tanggal, 1, 0, 'C');
        $pdf->Cell(40, 10, $namaproduk, 1, 0, 'L'); // Reduced width for Nama Produk
        $pdf->Cell(50, 10, 'Rp ' . number_format($harga1kg, 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell(30, 10, $qty . ' kg', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Rp ' . number_format($total, 0, ',', '.'), 1, 1, 'R');
    }

    // Add a total row that spans multiple columns
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(160, 10, 'Total Pemasukan', 1, 0, 'R'); // Span across the appropriate columns
    $pdf->Cell(40, 10, 'Rp ' . number_format($totalPemasukan, 0, ',', '.'), 1, 1, 'R');

    // Output the PDF
    $pdf->Output();
    exit;
}
