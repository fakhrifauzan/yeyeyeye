<?php
/**
 * Created by PhpStorm.
 * User: Fakhri Fauzan
 * Date: 15/09/2017
 * Time: 7:21
 */

    function getDataTagihanMahasiswa($connect) {
        $nim = $_SESSION['nim'];
        $sql = "SELECT * FROM registrasi WHERE nim='$nim'";
        $registrasi = mysqli_query($connect, $sql);
        if(mysqli_num_rows($registrasi) == 0){
            //echo '<tr><td colspan="4"><center>Data Tidak Tersedia</center></td></tr>';
        } else {
            foreach ($registrasi as $value) {
                echo "
                        <tr>
                            <td>".$value['nim']."</td>
                            <td>".$value['semester']."</td>
                            <td>".$value['tagihan']."</td>
                            <td>".$value['status']."</td>";
                if ($value['status'] == 'Lunas') {
                    echo "<td>
                            <a href='../function.php?print=bukti&id=$value[id_registrasi]'>
                                <button type='button' class='btn btn-default'>Cetak Kwitansi</button>
                            </a>
                        </td>";
                } else {
                    echo "<td>
                            <a href='upload.php?upload=bukti&id=$value[id_registrasi]'>
                                <button type='button' class='btn btn-primary'>Upload Bukti Pembayaran</button>
                            </a>
                        </td>";

                }
                echo "</tr>";
            }
        }
    }

    if (isset($_GET["print"]) && $_GET['print'] == 'bukti') {
        echo"<script>alert('Fitur belum Tersedia!');window.location.href = 'registrasi/tagihan.php';</script>";
    }

    if (isset($_POST["uploadBukti"])) {
        include_once '../koneksi.php';
        uploadBuktiPembayaran($connect);
        echo"<script>alert('Bukti telah ditambahkan!');window.location.href = 'registrasi/index.php';</script>";
    }

    function uploadBuktiPembayaran($connect) {
        $id_registrasi = $_POST['id_registrasi'];
        $tanggal = $_POST['tgl_transfer'];
        $bank = $_POST['bank'];
        $jumlah = $_POST['jumlah'];
        $pemilik_norek = $_POST['pemilik_rek'];

        $sql = "INSERT INTO bukti_pembayaran VALUES (NULL, '$id_registrasi', '$tanggal', '$bank', '$jumlah', '$pemilik_norek')";
        mysqli_query($connect,$sql);
    }

    function getDataRegistrasiSmtIni($connect) {
        include_once "../../admin/function.php";

        $fakultas = $_SESSION['fakultas'];
        $nim = $_SESSION['nim'];

        $status_reg = getStatusRegistrasi($connect);
        $tahun_ajaran = getTahunAjaran($connect);

        if ($status_reg == 'Tidak Aktif') {
            echo "<tr><td colspan='8'>Maaf, Waktu Registrasi belum Dimulai!</td></tr>";
        } else {
            $sql = "SELECT * FROM jadwal JOIN matkul USING (kode_matkul) WHERE semester='$tahun_ajaran' AND fakultas='$fakultas'";
            $matkul = mysqli_query($connect, $sql);

            $list = "SELECT id_jadwal FROM reg_matkul JOIN reg_matkul_detail USING (id_reg_matkul) WHERE semester='$tahun_ajaran' AND nim='$nim'";
            $pilihan = mysqli_query($connect, $list);

            if(mysqli_num_rows($matkul) == 0){
                //echo '<tr><td colspan="4"><center>Data Tidak Tersedia</center></td></tr>';
            } else {
                foreach ($matkul as $value) {
                    echo "
                    <tr>
                        <td>".$value['kode_matkul']."</td>
                        <td>".$value['kode_kelas']."</td>
                        <td>".$value['hari']."</td>
                        <td>".$value['jam']."</td>
                        <td>".$value['ruangan']."</td>
                        <td>".$value['sks']."</td>
                        <td><input type='checkbox' class='form-control' name='jadwal[]' value='".$value['id_jadwal']."'";
                    foreach ($pilihan as $pilih) {
                        if ($pilih['id_jadwal'] == $value['id_jadwal']) {
                            echo "checked";
                        }
                    }
                    echo "></td></tr>";
                }
            }
        }
    }

    function cekSiapAcc($connect) {
        $nim = $_SESSION['nim'];

        $sql = "SELECT * FROM reg_matkul WHERE nim = '$nim'";
        $query = mysqli_query($connect, $sql);
        $registrasi = mysqli_fetch_array($query);

        return $registrasi['status'];
    }

    if (isset($_POST["simpan"])) {
        include_once '../koneksi.php';
        simpanKrs($connect);
    }

    if (isset($_POST["siapAcc"])) {
        include_once '../koneksi.php';
        siapAcc($connect);
        echo"<script>alert('Saya siap ACC!');window.location.href = 'registrasi/index.php';</script>";
    }

    function simpanKrs($connect) {
        session_start();
        $id_jadwal = $_POST['jadwal'];
        $nim = $_SESSION['nim'];

        include_once '../admin/function.php';

        $semester = getTahunAjaran($connect);

        $check = "SELECT * FROM reg_matkul WHERE nim='$nim' AND semester='$semester'";
        $cari = mysqli_query($connect, $check);
        if (mysqli_num_rows($cari) >= 1) {
            $lala = mysqli_fetch_array($cari);
            $id = $lala['id'];

            $sql = "DELETE FROM reg_matkul_detail WHERE id_reg_matkul='$id'";
            $delete = mysqli_query($connect, $sql);
            foreach ($id_jadwal as $jadwal) {
                $sql_data = "INSERT INTO reg_matkul_detail(id_reg_matkul, id_jadwal) VALUES ('$id', '$jadwal')";
                $query_data = mysqli_query($connect,$sql_data);
            }

            if ($query_data) {
                echo '<script>alert("Data Berhasil disimpan");window.location.href=\'registrasi\';</script>';
            } else {
                echo '<script>alert("Data Gagal disimpan");window.location.href=\'registrasi\';</script>';
            }
        } else {
            $sql = "INSERT INTO reg_matkul VALUES(NULL, '$nim', '$semester', 'simpan')";
            $query = mysqli_query($connect, $sql);
            $id_reg_matkul = mysqli_insert_id($connect);

            //insertMatkulkeDetail
            foreach ($id_jadwal as $jadwal) {
                $sql_data = "INSERT INTO reg_matkul_detail(id_reg_matkul, id_jadwal) VALUES ('$id_reg_matkul', '$jadwal')";
                $query_data = mysqli_query($connect,$sql_data);
            }

            if ($query) {
                echo '<script>alert("Data Berhasil disimpan");window.location.href=\'registrasi\';</script>';
            } else {
                echo '<script>alert("Data Gagal disimpan");window.location.href=\'registrasi\';</script>';
            }
        }
    }

    function siapAcc($connect) {

    }

?>