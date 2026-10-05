<?php
declare(strict_types=1);

// Load the database connection and the shared page header
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/header.php';

// No user input here, so no placeholders are needed.
$sql = "SELECT id, first_name, last_name, email, enrolment_date
        FROM students
        ORDER BY enrolment_date DESC, id DESC";
$result = mysqli_query($conn, $sql);
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
                    <?php

                    // as ['column' => value], and false when there are no rows left.
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)):
                    ?>
                        <tr>
                            <td class="small text-muted"><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($row['enrolment_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-center text-nowrap">
                                <a href="view.php?id=<?= urlencode((string)$row['id']) ?>" class="btn btn-sm btn-info">View</a>
                                <a href="edit.php?id=<?= urlencode((string)$row['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete.php?id=<?= urlencode((string)$row['id']) ?>" class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
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

mysqli_free_result($result);
mysqli_close($conn);

require_once __DIR__ . '/footer.php';
?>