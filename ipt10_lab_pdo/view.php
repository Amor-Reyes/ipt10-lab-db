<?php
declare(strict_types=1);

// Load the database configuration and the shared page header
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

$id = trim($_GET['id'] ?? '');
$student = null;
$errorMessage = '';

if ($id === '') {
    $errorMessage = 'No student ID provided.';
} else {
    // Prepare and execute SELECT query with positional parameter in execute()
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if ($student === false) {
        $errorMessage = 'Student not found.';
    }
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
    // Build full name, including middle name only if present
    $fullName = $student['first_name'];
    if (!empty($student['middle_name'])) {
        $fullName .= ' ' . $student['middle_name'];
    }
    $fullName .= ' ' . $student['last_name'];
    ?>
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-white"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></h4>
            <span class="badge bg-secondary"><?= htmlspecialchars((string)$student['student_number'], ENT_QUOTES, 'UTF-8') ?></span>
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
require_once __DIR__ . '/footer.php';
?>
