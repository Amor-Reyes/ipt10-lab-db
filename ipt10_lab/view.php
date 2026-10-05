<?php
declare(strict_types=1);

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/header.php';

$id = trim($_GET['id'] ?? '');
$student = null;
$errorMessage = '';

if ($id === '') {
    $errorMessage = 'No student ID provided.';
} else {
    // Prepared statement: the id is a UUID string, so we bind it using 's'
    $sql = "SELECT id, first_name, middle_name, last_name, birthday, sex, email, student_number, program, enrolment_date, created_at, updated_at
            FROM students
            WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result);

    if (!$student) {
        $errorMessage = 'Student not found.';
    }

    mysqli_stmt_close($stmt);
}
?>

<div class="mb-4">
    <h2 class="mb-0">Student Profile</h2>
</div>

<?php if ($errorMessage !== ''): ?>
    <div class="card p-4">
        <p class="text-danger mb-3"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <div>
            <a href="index.php" class="btn btn-secondary">Back to Students</a>
        </div>
    </div>
<?php else: ?>
    <?php

    $fullName = $student['first_name'];
    if (!empty($student['middle_name'])) {
        $fullName .= ' ' . $student['middle_name'];
    }
    $fullName .= ' ' . $student['last_name'];
    ?>
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-white"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></h4>
            <span class="badge bg-secondary"><?= htmlspecialchars($student['student_number'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-sm-3 fw-bold">ID:</div>
                <div class="col-sm-9 text-muted small"><?= htmlspecialchars((string)$student['id'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 fw-bold">Full Name:</div>
                <div class="col-sm-9"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 fw-bold">Birthday:</div>
                <div class="col-sm-9"><?= htmlspecialchars((string)$student['birthday'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 fw-bold">Sex:</div>
                <div class="col-sm-9"><?= htmlspecialchars((string)$student['sex'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 fw-bold">Email:</div>
                <div class="col-sm-9"><?= htmlspecialchars((string)$student['email'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 fw-bold">Student Number:</div>
                <div class="col-sm-9"><?= htmlspecialchars((string)$student['student_number'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 fw-bold">Program:</div>
                <div class="col-sm-9"><?= htmlspecialchars((string)$student['program'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 fw-bold">Enrolment Date:</div>
                <div class="col-sm-9"><?= htmlspecialchars((string)$student['enrolment_date'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 fw-bold">Created At:</div>
                <div class="col-sm-9 text-muted small"><?= htmlspecialchars((string)$student['created_at'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 fw-bold">Updated At:</div>
                <div class="col-sm-9 text-muted small"><?= htmlspecialchars((string)$student['updated_at'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">Back</a>
            <a href="edit.php?id=<?= urlencode((string)$student['id']) ?>" class="btn btn-warning">Edit</a>
        </div>
    </div>
<?php endif; ?>

<?php

mysqli_close($conn);
require_once __DIR__ . '/footer.php';
?>
