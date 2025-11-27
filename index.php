<?php
session_start();
if (!isset($_SESSION['user'])) {
	header("Location: login.php");
	exit;
}
include 'config.php';

// Fetch transactions for the logged-in user
$user_id = $_SESSION['user']['id'];

// Ambil parameter pencarian jika ada
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$searchQuery = $search ? "AND description LIKE '%$search%'" : '';

$transactions = $conn->query("SELECT * FROM transactions WHERE user_id = $user_id $searchQuery ORDER BY created_at DESC");



// Query untuk data grafik
$pemasukan = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE user_id = $user_id AND type = 'Pemasukan'")->fetch_assoc()['total'] ?? 0;
$pengeluaran = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE user_id = $user_id AND type = 'Pengeluaran]
'")->fetch_assoc()['total'] ?? 0;

// Ambil pesan dari sesi jika ada
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); // Hapus pesan setelah diambil
?>

<!DOCTYPE html>
<html>

<head>
	<link rel="stylesheet" href="style.css">
	<title>Saku</title>
	<style>
		.tabs {
			display: flex;
			border-bottom: 1px solid #ccc;
			margin-bottom: 10px;
		}

		.tab {
			padding: 10px 20px;
			cursor: pointer;
			border: 1px solid #ccc;
			border-bottom: none;
			background-color: #f1f1f1;
			margin-right: 5px;
			display: flex;
			align-items: center;
			gap: 5px;
		}

		.tab.active {
			background-color: #fff;
			font-weight: bold;
		}

		.tab-content {
			display: none;
		}

		.tab-content.active {
			display: block;
		}

		.chart-container {
			width: 80%;
			margin: 20px auto;
		}

		.chart-container canvas {
			max-height: 300px;
		}

		.actions a {
			margin-right: 10px;
			display: inline-block;
		}

		.actions a:last-child {
			margin-right: 0;
		}
	</style>
</head>

<body>
	<div class="container">
		<div class="header" style="text-align: right;">
			<h1>Selamat Datang, <?= $_SESSION['user']['name']; ?></h1>
			<a href="javascript:void(0);" onclick="confirmLogout()">Logout</a>
		</div>


		<div class="tabs">
			<div class="tab active" data-tab="transactions">
				Transaksi
			</div>
			<div class="tab" data-tab="profile">
				Profil
			</div>
		</div>

		<div id="transactions" class="tab-content active">
			<h2>Daftar Transaksi</h2>
			<form method="GET" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
				<input type="text" name="search" placeholder="Cari berdasarkan deskripsi..." value="<?= $search; ?>" style="padding: 10px; width: 350px;">
				<button type="submit" style="padding: 10px 10px;">Cari</button>
				<a href="?" style="padding: 10px 10px;">Refresh</a>

			</form>
			<a href="create.php" class="btn btn-success" style="margin-bottom: 20px;">
				Buat Transaksi
			</a>

			<?php if ($transactions->num_rows > 0): ?>
				<table>
					<thead>
						<tr>
							<th>Tanggal</th>
							<th>Tipe</th>
							<th>Jumlah</th>
							<th>Deskripsi</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php while ($row = $transactions->fetch_assoc()): ?>
							<tr>
								<td><?= $row['created_at']; ?></td>
								<td><?= $row['type']; ?></td>
								<td>Rp.<?= number_format($row['amount'], 2); ?></td>
								<td><?= $row['description']; ?></td>
								<td class="actions">
									<a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-primary">Ubah</a>
									<a href="delete.php?id=<?= $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">Hapus</a>
								</td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			<?php else: ?>
				<p>Tidak ada transaksi. <a href="create.php">Tambah Transaksi</a></p>
			<?php endif; ?>
			<div class="chart-container">
				<canvas id="transactionChart"></canvas>
			</div>
		</div>


		<div id="profile" class="tab-content">
			<h2>Profil Anda</h2>
			<p><strong>Username:</strong> <?= $_SESSION['user']['username']; ?></p>
			<p><strong>Nama:</strong> <?= $_SESSION['user']['name']; ?></p>
			<p><strong>Email:</strong> <?= $_SESSION['user']['email']; ?></p>
			<p><strong>Joined:</strong> <?= date('F d, Y', strtotime($_SESSION['user']['created_at'])); ?></p>
		</div>
	</div>

	<script>
		const tabs = document.querySelectorAll('.tab');
		const tabContents = document.querySelectorAll('.tab-content');

		tabs.forEach(tab => {
			tab.addEventListener('click', () => {
				document.querySelector('.tab.active').classList.remove('active');
				tab.classList.add('active');

				document.querySelector('.tab-content.active').classList.remove('active');
				const tabContent = document.getElementById(tab.dataset.tab);
				tabContent.classList.add('active');
			});
		});

		function showModal(src) {
			const modal = document.getElementById("imageModal");
			const modalImage = document.getElementById("modalImage");
			modal.style.display = "block";
			modalImage.src = src;
		}

		function closeModal() {
			const modal = document.getElementById("imageModal");
			modal.style.display = "none";
		}

		function confirmLogout() {
			const confirmLogout = confirm("Yakin ingin log out?");
			if (confirmLogout) {
				window.location.href = "logout.php"; // Redirect to logout page
			}
		}

		// Show login success message if it exists
		<?php if ($message): ?>
			alert("<?= $message; ?>");
		<?php endif; ?>
	</script>
</body>

</html>