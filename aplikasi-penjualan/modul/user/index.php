<?php
// Proteksi 1: pengguna harus memiliki session login
if (!isset($_SESSION['id_user'])) {
exit;
}

// Proteksi 2: hanya Admin yang boleh mengelola pengguna
if ($_SESSION['role'] !== 'Admin') {

echo "<div class='alert alert-danger shadow-sm text-center mt-4 border-0'>
<i class='bi bi-shield-lock-fill display-4 d-block mb-3 text-danger'></i>
<h5>Akses Ditolak!</h5>
<p class='mb-0'>Hanya pengguna dengan hak akses
<b>Admin</b> yang dapat mengelola data sistem ini.</p>
</div>";
exit;
}

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : 'view';

switch ($aksi) {
    case 'view':
        // tampilkan daftar pengguna
        $keyword = isset($_GET['cari']) ? trim($_GET['cari']) : '';
        ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0 text-dark">Data Pengguna Sistem</h5>
            <a href="index.php?page=user&aksi=tambah"
                class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah User
            </a>
        </div>

        <form action="index.php" method="GET" class="mb-4">
            <input type="hidden" name="page" value="user">
            <div class="input-group shadow-sm">
                <input type="text" name="cari" class="form-control"
                       placeholder="Cari nama atau username..."
                       value="<?= htmlspecialchars($keyword); ?>">
                <button class="btn btn-primary " type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
        <?php
        if ($keyword != '') {
        $param = "%" . $keyword . "%";
        $stmt = $koneksi->prepare(
            "SELECT id_user, nama, username, role
             FROM user
             WHERE nama LIKE ? OR username LIKE ?
             ORDER BY id_user DESC"
        );
        $stmt->bind_param("ss", $param, $param);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $query = "SELECT * FROM user ORDER BY id_user DESC";
        $result = $koneksi->query($query);
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bg_role = $row['role'] == 'Admin'
                ? 'bg-danger'
                : ($row['role'] == 'Kasir' ? 'bg-primary': 'bg-success');
            ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-8 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-bold mb-6">
                            <?= htmlspecialchars($row['nama']); ?>
                        </h6>
                        <span class="badge <?= $bg_role; ?>">
                            <?= htmlspecialchars($row['role']); ?>
                        </span>
                        <div class="text-muted small mb-3">
                            Username:
                            <b><?= htmlspecialchars($row['username']); ?></b>
                        </div>
                        <a href="index.php?page=user&aksi=edit&id=<?= $row['id_user']; ?>"
                            class="btn btn-outline-warning btn-sm">Edit</a>
                        <a href="index.php?page=user&aksi=hapus&id=<?= $row['id_user']; ?>"
                            class="btn btn-outline-danger btn-sm" 
                            onclick="return confirm('Yakin ingin menghapus pengguna ini?');">
                            Hapus
                        </a>
                    </div>
                </div>
            </div>
            <?php
        }    
    } else {
        echo "<div class='alert alert-light text-center'>
                Data pengguna tidak ditemukan.</div>";
    }
    if (isset($stmt)) {
        $stmt->close();
    }
    break;

    case 'tambah':
        // tampilkan dan proses form tambah

    if (isset($_POST['simpan'])) {

        $nama     = trim($_POST['nama']);
        $username = trim($_POST['username']);
        $password = md5($_POST['password']);
        $role     = $_POST['role'];

        // Periksa apakah username sudah digunakan
        $cek = $koneksi->prepare(
            "SELECT username FROM user WHERE username = ?"
        );

        $cek->bind_param("s", $username);
        $cek->execute();

        if ($cek->get_result()->num_rows > 0) {

            echo "<script>
                alert('Username sudah digunakan.');
            </script>";

        } else {

            $stmt = $koneksi->prepare(
                "INSERT INTO user (nama, username, password, role)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $nama,
                $username,
                $password,
                $role
            );

            if ($stmt->execute()) {

                echo "<script>
                    alert('Pengguna baru berhasil ditambahkan!');
                    window.location='index.php?page=user';
                </script>";

            } else {

                echo "<div class='alert alert-danger'>
                    Gagal menyimpan data.
                </div>";

            }

            $stmt->close();
        }

        $cek->close();
    }
    ?>
        <h5 class="fw-bold mb-3">Tambah Pengguna Baru</h5>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger mb-3"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="index.php?page=user&aksi=tambah" method="POST">

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control"
                            maxlength="100" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control"
                            maxlength="30" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password"
                            class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Hak Akses (Role)</label>
                        <select name="role" class="form-select" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="Admin">Admin</option>
                            <option value="Kasir">Kasir</option>
                            <option value="Owner">Owner</option>
                        </select>
                    </div>

                    <button type="submit" name="simpan"
                            class="btn btn-primary w-100">Simpan Pengguna</button>

                    <a href="index.php?page=user"
                    class="btn btn-light w-100 mt-2">Batal</a>
                </form>
            </div>
        </div>
        <?php
        break;

    case 'edit':
        // tampilkan dan proses form edit
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (isset($_POST['update'])) {
            $nama = trim($_POST['nama']);
            $username = trim($_POST['username']);
            $role = $_POST['role'];
            $pass_input = trim($_POST['password']);

            if ($pass_input != '') {
                $password = md5($pass_input);
                $stmt = $koneksi->prepare(
                    "UPDATE user SET nama=?, username=?, password=?, role=? WHERE id_user=?"
                );
                $stmt->bind_param("ssssi", $nama, $username, $password, $role, $id);
            } else {
                $stmt = $koneksi->prepare(
                    "UPDATE user SET nama=?, username=?, role=? WHERE id_user=?"
                );
                $stmt->bind_param("sssi", $nama, $username, $role, $id);
            }

            if ($stmt->execute()) {
                echo "<script>
                    alert('User data successfully updated!');
                    window.location='index.php?page=user';
                </script>";
            } else {
                echo "<div class='alert alert-danger'>Update failed.</div>";
            }
            $stmt->close();
        }

        $stmt = $koneksi->prepare("SELECT id_user, nama, username, role FROM user WHERE id_user=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$data) {
            echo "<div class='alert alert-danger'>Data pengguna tidak ditemukan.</div>";
            break;
        }
        ?>
        <h5 class="fw-bold mb-3">Edit Pengguna</h5>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="index.php?page=user&aksi=edit&id=<?= $id ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control"
                            maxlength="100" value="<?= htmlspecialchars($data['nama']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control"
                            maxlength="30" value="<?= htmlspecialchars($data['username']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control"
                            placeholder="Kosongkan jika tidak ingin mengubah password">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Hak Akses (Role)</label>
                        <select name="role" class="form-select" required>
                            <option value="Admin" <?= $data['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="Kasir" <?= $data['role'] === 'Kasir' ? 'selected' : '' ?>>Kasir</option>
                            <option value="Owner" <?= $data['role'] === 'Owner' ? 'selected' : '' ?>>Owner</option>
                        </select>
                    </div>
                    <button type="submit" name="update" class="btn btn-primary w-100">Simpan Perubahan</button>
                    <a href="index.php?page=user" class="btn btn-light w-100 mt-2">Batal</a>
                </form>
            </div>
        </div>
        <?php
        break;

    case 'hapus':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0 && $id !== (int)$_SESSION['id_user']) {
            $stmt = $koneksi->prepare("DELETE FROM user WHERE id_user=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
        echo "<script>window.location='index.php?page=user';</script>";
        break;
}