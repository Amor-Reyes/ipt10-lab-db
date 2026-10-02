<?php
declare(strict_types=1);

// Load the database configuration and the shared page header
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

// Read and trim student ID from POST or GET
$id = trim($_POST['id'] ?? $_GET['id'] ?? '');

$first_name     = '';
$middle_name    = '';
$last_name      = '';
$birthday       = '';
$sex            = '';
$email          = '';
$student_number = '';
$program        = '';
$enrolment_date = '';

$errors        = [];
$statusMessage = '';
$statusType    = '';
$errorMessage  = '';

if ($id === '') {
    $errorMessage = 'No student ID provided.';
} else {
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        // Collect and trim all submitted fields
        $first_name     = trim($_POST['first_name'] ?? '');
        $middle_name    = trim($_POST['middle_name'] ?? '');
        $last_name      = trim($_POST['last_name'] ?? '');
        $birthday       = trim($_POST['birthday'] ?? '');
        $sex            = trim($_POST['sex'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $student_number = trim($_POST['student_number'] ?? '');
        $program        = trim($_POST['program'] ?? '');
        $enrolment_date = trim($_POST['enrolment_date'] ?? '');

        // Server-side validation (same rules as create.php)

        // first_name: required, 2-100 chars, letters and spaces only
        $fnLen = mb_strlen($first_name);
        if ($first_name === '') {
            $errors['first_name'] = 'First name is required.';
        } elseif ($fnLen < 2 || $fnLen > 100) {
            $errors['first_name'] = 'First name must be between 2 and 100 characters.';
        } elseif (!preg_match('/^[A-Za-z\s]+$/', $first_name)) {
            $errors['first_name'] = 'First name may only contain letters and spaces.';
        }

        // middle_name: optional, max 100 chars, letters and spaces only
        $mnLen = mb_strlen($middle_name);
        if ($middle_name !== '') {
            if ($mnLen > 100) {
                $errors['middle_name'] = 'Middle name must not exceed 100 characters.';
            } elseif (!preg_match('/^[A-Za-z\s]+$/', $middle_name)) {
                $errors['middle_name'] = 'Middle name may only contain letters and spaces.';
            }
        }

        // last_name: required, 2-100 chars, letters and spaces only
        $lnLen = mb_strlen($last_name);
        if ($last_name === '') {
            $errors['last_name'] = 'Last name is required.';
        } elseif ($lnLen < 2 || $lnLen > 100) {
            $errors['last_name'] = 'Last name must be between 2 and 100 characters.';
        } elseif (!preg_match('/^[A-Za-z\s]+$/', $last_name)) {
            $errors['last_name'] = 'Last name may only contain letters and spaces.';
        }

        // email: required, filter_var FILTER_VALIDATE_EMAIL, max 150 chars
        $emLen = mb_strlen($email);
        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif ($emLen > 150) {
            $errors['email'] = 'Email must not exceed 150 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        // birthday: required, YYYY-MM-DD, valid calendar date via checkdate, not in future
        if ($birthday === '') {
            $errors['birthday'] = 'Birthday is required.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
            $errors['birthday'] = 'Birthday must be in YYYY-MM-DD format.';
        } else {
            [$bYear, $bMonth, $bDay] = explode('-', $birthday);
            if (!checkdate((int)$bMonth, (int)$bDay, (int)$bYear)) {
                $errors['birthday'] = 'Birthday must be a valid calendar date.';
            } elseif ($birthday > date('Y-m-d')) {
                $errors['birthday'] = 'Birthday cannot be in the future.';
            }
        }

        // sex: required, in_array strict against ['Male', 'Female']
        if ($sex === '') {
            $errors['sex'] = 'Sex is required.';
        } elseif (!in_array($sex, ['Male', 'Female'], true)) {
            $errors['sex'] = 'Please select a valid option (Male or Female).';
        }

        // student_number: required (column is NOT NULL), alphanumeric, max 50 chars
        // Note: Required validation deviates from the lab PDF's "optional" description
        // because the database schema explicitly defines student_number as NOT NULL.
        $snLen = mb_strlen($student_number);
        if ($student_number === '') {
            $errors['student_number'] = 'Student number is required.';
        } elseif ($snLen > 50) {
            $errors['student_number'] = 'Student number must not exceed 50 characters.';
        } elseif (!preg_match('/^[A-Za-z0-9]+$/', $student_number)) {
            $errors['student_number'] = 'Student number must contain letters and numbers only.';
        }

        // program: required (column is NOT NULL), max 200 chars
        $prLen = mb_strlen($program);
        if ($program === '') {
            $errors['program'] = 'Program is required.';
        } elseif ($prLen > 200) {
            $errors['program'] = 'Program must not exceed 200 characters.';
        }

        // enrolment_date: required, YYYY-MM-DD, valid calendar date via checkdate
        if ($enrolment_date === '') {
            $errors['enrolment_date'] = 'Enrolment date is required.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $enrolment_date)) {
            $errors['enrolment_date'] = 'Enrolment date must be in YYYY-MM-DD format.';
        } else {
            [$eYear, $eMonth, $eDay] = explode('-', $enrolment_date);
            if (!checkdate((int)$eMonth, (int)$eDay, (int)$eYear)) {
                $errors['enrolment_date'] = 'Enrolment date must be a valid calendar date.';
            }
        }

        // If validation passed, execute UPDATE using PDO transaction pattern
        if (empty($errors)) {
            $middle_name_param = ($middle_name === '') ? null : $middle_name;

            // $data array matches placeholders in order, with $id as the LAST element
            $data = [
                $first_name,
                $middle_name_param,
                $last_name,
                $birthday,
                $sex,
                $email,
                $student_number,
                $program,
                $enrolment_date,
                $id,
            ];

            $sqlUpdate = "UPDATE students
                          SET first_name = ?,
                              middle_name = ?,
                              last_name = ?,
                              birthday = ?,
                              sex = ?,
                              email = ?,
                              student_number = ?,
                              program = ?,
                              enrolment_date = ?
                          WHERE id = ?";

            try {
                // Begin transaction
                $pdo->beginTransaction();

                $stmt = $pdo->prepare($sqlUpdate);
                $stmt->execute($data);
                $count = $stmt->rowCount();

                // Commit transaction to finalize updates
                $pdo->commit();

                // In MySQL, rowCount() returning 0 on an UPDATE has two distinct meanings:
                // 1) The student record was found and matched, but the submitted form values were completely
                //    identical to what was already stored in the database, so MySQL made zero changes.
                // 2) No record in the table matched the WHERE id = ? clause.
                if ($count === 1) {
                    $statusType    = 'success';
                    $statusMessage = 'Student updated successfully!';
                } else {
                    $statusType    = 'info';
                    $statusMessage = 'No changes were made (the values were identical, or no student matched this id).';
                }
            } catch (PDOException $e) {
                // Roll back any uncommitted changes
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                // Driver error code 1062 indicates duplicate entry
                $driverCode = $e->errorInfo[1] ?? 0;
                if ($driverCode === 1062 || (int)$e->getCode() === 1062 || str_contains($e->getMessage(), '1062')) {
                    $msg = $e->getMessage();
                    if (stripos($msg, 'email') !== false) {
                        $errors['email'] = 'This email address is already registered.';
                    } elseif (stripos($msg, 'student_number') !== false) {
                        $errors['student_number'] = 'This student number is already registered.';
                    } else {
                        $errors['general'] = 'A duplicate entry was detected for this student.';
                    }
                } else {
                    error_log('Student UPDATE failed: ' . $e->getMessage());
                    $errors['general'] = 'An unexpected database error occurred. Please try again.';
                }
            }
        }
    } else {
        // Initial GET request: pre-fill form from database
        $sqlSelect = "SELECT id, first_name, middle_name, last_name, birthday, sex, email, student_number, program, enrolment_date
                      FROM students
                      WHERE id = ?";
        $stmt = $pdo->prepare($sqlSelect);
        $stmt->execute([$id]);
        $student = $stmt->fetch();

        if (!$student) {
            $errorMessage = 'Student not found.';
        } else {
            $first_name     = (string)$student['first_name'];
            $middle_name    = (string)($student['middle_name'] ?? '');
            $last_name      = (string)$student['last_name'];
            $birthday       = (string)$student['birthday'];
            $sex            = (string)$student['sex'];
            $email          = (string)$student['email'];
            $student_number = (string)$student['student_number'];
            $program        = (string)$student['program'];
            $enrolment_date = (string)$student['enrolment_date'];
        }
    }
}
?>

<div class="mb-4">
    <h2 class="mb-0">Edit Student</h2>
</div>

<?php if ($statusMessage !== ''): ?>
    <div class="alert alert-<?= $statusType === 'success' ? 'success' : 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger" role="alert">
        <p class="text-danger mb-0"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
<?php endif; ?>

<?php if ($errorMessage !== ''): ?>
    <div class="card p-4">
        <p class="text-danger mb-3"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <div>
            <a href="index.php" class="btn btn-secondary">Back to Students</a>
        </div>
    </div>
<?php else: ?>
    <!-- Edit Form with sticky pre-filled values and field-level red errors -->
    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="edit.php?id=<?= urlencode($id) ?>" novalidate>
                <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">

                <div class="row g-3">
                    <!-- First Name -->
                    <div class="col-md-4">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text"
                               class="form-control"
                               id="first_name"
                               name="first_name"
                               value="<?= htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['first_name'])): ?>
                            <div class="text-danger"><?= htmlspecialchars($errors['first_name'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Middle Name -->
                    <div class="col-md-4">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text"
                               class="form-control"
                               id="middle_name"
                               name="middle_name"
                               value="<?= htmlspecialchars($middle_name, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['middle_name'])): ?>
                            <div class="text-danger"><?= htmlspecialchars($errors['middle_name'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Last Name -->
                    <div class="col-md-4">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text"
                               class="form-control"
                               id="last_name"
                               name="last_name"
                               value="<?= htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['last_name'])): ?>
                            <div class="text-danger"><?= htmlspecialchars($errors['last_name'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Birthday -->
                    <div class="col-md-6">
                        <label for="birthday" class="form-label">Birthday *</label>
                        <input type="date"
                               class="form-control"
                               id="birthday"
                               name="birthday"
                               value="<?= htmlspecialchars($birthday, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['birthday'])): ?>
                            <div class="text-danger"><?= htmlspecialchars($errors['birthday'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Sex -->
                    <div class="col-md-6">
                        <label for="sex" class="form-label">Sex *</label>
                        <select class="form-select" id="sex" name="sex">
                            <option value="">-- Select Sex --</option>
                            <option value="Male" <?= $sex === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $sex === 'Female' ? 'selected' : '' ?>>Female</option>
                        </select>
                        <?php if (isset($errors['sex'])): ?>
                            <div class="text-danger"><?= htmlspecialchars($errors['sex'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email"
                               class="form-control"
                               id="email"
                               name="email"
                               value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="text-danger"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Student Number -->
                    <div class="col-md-6">
                        <label for="student_number" class="form-label">Student Number *</label>
                        <input type="text"
                               class="form-control"
                               id="student_number"
                               name="student_number"
                               value="<?= htmlspecialchars($student_number, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['student_number'])): ?>
                            <div class="text-danger"><?= htmlspecialchars($errors['student_number'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Program -->
                    <div class="col-md-6">
                        <label for="program" class="form-label">Program *</label>
                        <input type="text"
                               class="form-control"
                               id="program"
                               name="program"
                               value="<?= htmlspecialchars($program, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['program'])): ?>
                            <div class="text-danger"><?= htmlspecialchars($errors['program'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Enrolment Date -->
                    <div class="col-md-6">
                        <label for="enrolment_date" class="form-label">Enrolment Date *</label>
                        <input type="date"
                               class="form-control"
                               id="enrolment_date"
                               name="enrolment_date"
                               value="<?= htmlspecialchars($enrolment_date, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['enrolment_date'])): ?>
                            <div class="text-danger"><?= htmlspecialchars($errors['enrolment_date'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-12 mt-4 d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Student</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/footer.php';
?>
