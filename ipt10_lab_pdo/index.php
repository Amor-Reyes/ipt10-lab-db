<?php
declare(strict_types=1);

// Load the database configuration and the shared page header
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

// Static SELECT query: newest enrolments first, ties broken by id
$sql = "SELECT id, first_name, last_name, email, enrolment_date
        FROM students
        ORDER BY enrolment_date DESC, id DESC";

// Execute query directly with PDO and fetch all records into an array
$stmt = $pdo->query($sql);
$students = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">All Student Records</h2>
    <a href="create.php" class="btn btn-primary">+ Add New Student</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Enrolled</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $row): ?>
                            <tr>
                                <td class="small text-muted"><?= htmlspecialchars((string)$row['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-nowrap"><?= htmlspecialchars((string)$row['enrolment_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center text-nowrap">
                                    <a href="view.php?id=<?= urlencode((string)$row['id']) ?>" class="btn btn-sm btn-info">View</a>
                                    <a href="edit.php?id=<?= urlencode((string)$row['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="delete.php?id=<?= urlencode((string)$row['id']) ?>" class="btn btn-sm btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Note: In PDO, no manual close function (like mysqli_close) is required.
// PHP automatically cleans up the PDO connection and statements when the script ends.
require_once __DIR__ . '/footer.php';
?>
