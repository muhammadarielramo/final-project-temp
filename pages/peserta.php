<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/config.php';

// Edit Peserta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['participant_id'])) {
    $participant_id = $_POST['participant_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $event_id = $_POST['event_id'];

    // Update data peserta
    $stmt = $pdo->prepare("UPDATE registrations SET name = ?, email = ?, phone = ?, event_id = ? WHERE id = ?");
    $stmt->execute([$name, $email, $phone, $event_id, $participant_id]);
    $message = "Data peserta berhasil diperbarui.";
}

// Hapus Peserta
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
    $stmt->execute([$delete_id]);
    $message = "Peserta berhasil dihapus.";
}

// Pencarian Peserta
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM registrations WHERE name LIKE ?";
$stmt = $pdo->prepare($query);
$stmt->execute(['%' . $search . '%']);
$participants = $stmt->fetchAll();
?>

<div class="container-fluid">
    <h2 class="text-center mb-4">Manajemen Peserta</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <!-- Form Pencarian -->
    <div class="d-flex justify-content-between mb-3">
        <form method="GET" class="d-flex" style="flex-grow: 1;">
            <input type="text" name="search" class="form-control me-2" placeholder="Cari peserta..." value="<?= htmlspecialchars($search) ?>">
        </form>
    </div>

    <!-- Tabel Daftar Peserta -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Event ID</th>
                <th>Status Kehadiran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($participants as $participant): ?>
            <tr>
                <td><?= $participant['id'] ?></td>
                <td><?= htmlspecialchars($participant['name']) ?></td>
                <td><?= htmlspecialchars($participant['email']) ?></td>
                <td><?= htmlspecialchars($participant['phone']) ?></td>
                <td><?= htmlspecialchars($participant['event_id']) ?></td>
                <td><?= htmlspecialchars($participant['attendance_status']) ?></td>
                <td>
                    <button 
                        class="btn btn-sm btn-warning editButton" 
                        data-bs-toggle="modal" 
                        data-bs-target="#editPesertaModal" 
                        data-id="<?= $participant['id'] ?>" 
                        data-name="<?= htmlspecialchars($participant['name']) ?>" 
                        data-email="<?= htmlspecialchars($participant['email']) ?>" 
                        data-phone="<?= htmlspecialchars($participant['phone']) ?>" 
                        data-event_id="<?= $participant['event_id'] ?>">
                        Edit
                    </button>
                    <a href="peserta.php?delete_id=<?= $participant['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus peserta ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Edit Peserta -->
<div class="modal fade" id="editPesertaModal" tabindex="-1" aria-labelledby="editPesertaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" id="editPesertaForm">
            <div class="modal-header">
                <h5 class="modal-title" id="editPesertaModalLabel">Edit Peserta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="participant_id" id="edit-participant-id">
                <div class="mb-3">
                    <label class="form-label">Nama Peserta</label>
                    <input type="text" name="name" class="form-control" id="edit-participant-name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" id="edit-participant-email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="phone" class="form-control" id="edit-participant-phone" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Event ID</label>
                    <input type="number" name="event_id" class="form-control" id="edit-participant-event-id" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.editButton');

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Ambil data dari tombol
            const id = this.dataset.id;
            const name = this.dataset.name;
            const email = this.dataset.email;
            const phone = this.dataset.phone;
            const event_id = this.dataset.event_id;

            // Isi data ke dalam form modal
            document.getElementById('edit-participant-id').value = id;
            document.getElementById('edit-participant-name').value = name;
            document.getElementById('edit-participant-email').value = email;
            document.getElementById('edit-participant-phone').value = phone;
            document.getElementById('edit-participant-event-id').value = event_id;
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
