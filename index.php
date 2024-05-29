<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <h2>Prediksi Produk Terlaris</h2>
    <form method="post" class="container mt-5">
        <div class="form-group">
            <label for="bulan">Bulan:</label>
            <select id="bulan" name="bulan" class="form-control">
                <option value="Januari">Januari</option>
                <option value="Februari">Februari</option>
                <option value="Maret">Maret</option>
                <option value="April">April</option>
                <option value="Mei">Mei</option>
                <option value="Juni">Juni</option>
                <option value="Juli">Juli</option>
                <option value="Agustus">Agustus</option>
                <option value="September">September</option>
                <option value="Oktober">Oktober</option>
                <option value="November">November</option>
                <option value="Desember">Desember</option>
            </select>
        </div>
        <div class="form-group">
            <label for="tahun">Tahun:</label>
            <input type="number" id="tahun" name="tahun" value="" min="1900" max="2100" class="form-control">
        </div>
        <div class="form-group">
            <label for="nama_produk">Nama Produk:</label>
            <select id="nama_produk" name="nama_produk" class="form-control">
                <?php
                $url_produk = 'http://localhost:5000/get_produk'; // Ganti dengan URL API yang sesuai
                $produk_json = file_get_contents($url_produk);
                if ($produk_json === false) {
                    echo "<option value=''>Gagal memuat produk</option>";
                } else {
                    $produk = json_decode($produk_json, true);
                    if (empty($produk['produk'])) {
                        echo "<option value=''>Tidak ada produk</option>";
                    } else {
                        foreach ($produk['produk'] as $item) {
                            echo "<option value='" . htmlspecialchars($item) . "'>" . htmlspecialchars($item) . "</option>";
                        }
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="harga">Harga:</label>
            <input type="number" id="harga" name="harga" value="" min="0" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Kirim</button>
    </form>
</body>
</html>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = array(
        "bulan" => intval($_POST['bulan']),
        "tahun" => intval($_POST['tahun']),
        "nama_produk" => $_POST['nama_produk'],
        "harga" => floatval($_POST['harga'])
    );

    $url = 'http://127.0.0.1:5000/predict'; // Ganti dengan URL API tujuan
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) {
        echo "<script>Swal.fire('Error', 'Terjadi kesalahan dalam mengambil data dari API', 'error');</script>";
    } else {
        $response = json_decode($result, true);
        if (isset($response['prediksi_jumlah'])) {
            $message = 
                       "Diperkirakan Produk: " . htmlspecialchars($_POST['nama_produk']) . "<br>" .
                       
                       "Pada :" . htmlspecialchars($_POST['bulan']) .htmlspecialchars($_POST['tahun']) . "<br>" .
                       "dijual dengan Harga: <b>" . htmlspecialchars(number_format($_POST['harga'])). "</b><br>".
                       "akan terjual sebanyak : <b>" . $response['prediksi_jumlah']. "</b> Item";
            echo "<script>Swal.fire('Hasil Prediksi', '" . $message . "', 'success');</script>";
        } else {
            echo "<script>Swal.fire('Error', 'Hasil prediksi tidak ditemukan dalam respons API', 'error');</script>";
        }
    }
}
?>

