<?php
declare(strict_types=1);

// Load the database configuration and the shared page header
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

$errorMessage   = '';
$successMessage = '';
$studentName    = '';
$id             = '';
$isPost         = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST');

if ($isPost) {
    // Process deletion via POST request only (never on GET)
    $id = trim($_POST['id'] ?? '');

    if ($id === '') {
        $errorMessage = 'No student ID provided.';
    } else {
        $sql = "DELETE FROM students WHERE id = ?";
        try {
            // Transaction pattern for DELETE with autocommit disabled
            $pdo->beginTransaction();

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $count = $stmt->rowCount();

            // Commit the transaction to disk
            $pdo->commit();

            if ($count === 1) {
                $successMessage = 'Student deleted successfully.';
            } else {
                $errorMessage = 'Failed to delete student.';
            }
        } catch (PDOException $e) {
            // Roll back changes if an exception occurs
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Student DELETE failed: ' . $e->getMessage());
            $errorMessage = 'Failed to delete student.';
        }
    }
} else {
    // On GET, read and trim id, then verify student exists via prepared SELECT
    $id = trim($_GET['id'] ?? '');

    if ($id === '') {
        $errorMessage = 'No student ID provided.';
    } else {
        $sql = "SELECT first_name, last_name FROM students WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $student = $stmt->fetch();

        if (!$student) {
            $errorMessage = 'Student not found.';
        } else {
            $studentName = trim($student['first_name'] . ' ' . $student['last_name']);
        }
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
    <!-- Confirmation card naming the student with POST form and Cancel link -->
    <div class="card">
        <div class="card-header bg-dark text-white">
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
require_once __DIR__ . '/footer.php';
?>
