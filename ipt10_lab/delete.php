<?php
declare(strict_types=1);

// Require database connection and header template
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/header.php';

$errorMessage   = '';
$successMessage = '';
$studentName    = '';
$id             = '';
$isPost         = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST');

if ($isPost) {
    // TODO(20): Never delete on GET. On POST, run prepared DELETE and verify affected_rows === 1
    $id = trim($_POST['id'] ?? '');

    if ($id === '') {
        $errorMessage = 'No student ID provided.';
    } else {
        $sql = "DELETE FROM students WHERE id = ?";
        try {
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 's', $id);
            mysqli_stmt_execute($stmt);

            if (mysqli_stmt_affected_rows($stmt) === 1) {
                $successMessage = 'Student deleted successfully.';
            } else {
                $errorMessage = 'Failed to delete student.';
            }

            mysqli_stmt_close($stmt);
        } catch (mysqli_sql_exception $e) {
            error_log('Student DELETE failed: ' . $e->getMessage());
            $errorMessage = 'Failed to delete student.';
        }
    }
} else {
    // TODO(18): On GET, read and trim id, then verify student exists via prepared SELECT
    $id = trim($_GET['id'] ?? '');

    if ($id === '') {
        $errorMessage = 'No student ID provided.';
    } else {
        $sql = "SELECT first_name, last_name FROM students WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $student = mysqli_fetch_assoc($result);

        if (!$student) {
            $errorMessage = 'Student not found.';
        } else {
            $studentName = trim($student['first_name'] . ' ' . $student['last_name']);
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<div class="mb-4">
    <h2 class="mb-0">Delete Student</h2>
</div>

<?php if ($successMessage !== ''): ?>
    <div class="card p-4">
        <div class="alert alert-success mb-3" role="alert">
            <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div>
            <a href="index.php" class="btn btn-secondary">Back to Students</a>
        </div>
    </div>
<?php elseif ($errorMessage !== ''): ?>
    <div class="card p-4">
        <p class="text-danger mb-3"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <div>
            <a href="index.php" class="btn btn-secondary">Back to Students</a>
        </div>
    </div>
<?php else: ?>
   <!-- TODO(20): Confirmation card naming the student with POST form and Cancel link -->
    <div class="card">
        <div class="card-header site-card-header"></div>
            <h4 class="mb-0 text-white">Confirm Deletion</h4>
        </div>
        <div class="card-body p-4">
            <p class="mb-3">
                Are you sure you want to delete student
                <strong><?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?></strong>?
            </p>
            <p class="text-danger mb-4">
                This action is permanent and cannot be undone.
            </p>

            <form method="POST" action="delete.php">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-danger">Yes, delete</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php
// Close database connection
mysqli_close($conn);

// Require footer template
require_once __DIR__ . '/footer.php';
?>
