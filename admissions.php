<?php
/**
 * Global Degree College – Online Admission Form
 * Save to: admissions.php (project root)
 */

declare(strict_types=1);
require_once __DIR__ . '/includes/config/app.php';

$pageTitle       = 'Apply for Admission';
$pageDescription = 'Submit your online admission application to Global Degree College, Peshawar.';
$extraCss        = ['admissions.css'];

$db = Database::getInstance();

// Fetch active courses for dropdown
$courses = $db->fetchAll(
    "SELECT id, title, short_code FROM courses WHERE is_active = 1 ORDER BY sort_order"
);

// Pre-selected course from courses.php "Apply" button
$preselectedCourse = getInt('course');

// ── Form Submission Handler ───────────────────────────────────
$errors     = [];
$success    = false;
$appNumber  = '';
$formData   = []; // repopulate on error

if (isPost()) {

    // 1. Verify CSRF token
    CSRF::requireValid();

    // 2. Collect & sanitize all fields
    $formData = [
        'student_name'    => post('student_name'),
        'father_name'     => post('father_name'),
        'cnic_bform'      => cleanString($_POST['cnic_bform'] ?? ''),
        'date_of_birth'   => cleanDate($_POST['date_of_birth'] ?? ''),
        'gender'          => cleanString($_POST['gender'] ?? ''),
        'email'           => cleanEmail($_POST['email'] ?? ''),
        'mobile'          => cleanPhone($_POST['mobile'] ?? ''),
        'address'         => post('address'),
        'city'            => post('city'),
        'previous_school' => post('previous_school'),
        'previous_class'  => post('previous_class'),
        'obtained_marks'  => postInt('obtained_marks'),
        'total_marks'     => postInt('total_marks'),
        'course_id'       => postInt('course_id'),
    ];

    // 3. Validate required fields
    if (empty($formData['student_name'])) {
        $errors['student_name'] = 'Student name is required.';
    } elseif (strlen($formData['student_name']) < 3) {
        $errors['student_name'] = 'Name must be at least 3 characters.';
    }

    if (empty($formData['father_name'])) {
        $errors['father_name'] = 'Father name is required.';
    }

    if (empty($formData['cnic_bform'])) {
        $errors['cnic_bform'] = 'CNIC / B-Form number is required.';
    } elseif (!validateCNIC($formData['cnic_bform'])) {
        $errors['cnic_bform'] = 'Invalid format. Use: XXXXX-XXXXXXX-X';
    } else {
        // Check for duplicate application
        $existing = $db->fetchColumn(
            "SELECT id FROM admission_applications WHERE cnic_bform = ?",
            [$formData['cnic_bform']]
        );
        if ($existing) {
            $errors['cnic_bform'] = 'An application with this CNIC/B-Form already exists.';
        }
    }

    if (empty($formData['date_of_birth'])) {
        $errors['date_of_birth'] = 'Date of birth is required.';
    }

    if (!in_array($formData['gender'], ['male', 'female', 'other'], true)) {
        $errors['gender'] = 'Please select a gender.';
    }

    if (!empty($formData['email']) && empty(cleanEmail($_POST['email'] ?? ''))) {
        $errors['email'] = 'Invalid email address.';
    }

    if (empty($formData['mobile'])) {
        $errors['mobile'] = 'Mobile number is required.';
    } elseif (!validateMobile($formData['mobile'])) {
        $errors['mobile'] = 'Invalid Pakistani mobile number. Example: 0300-1234567';
    }

    if (empty($formData['address'])) {
        $errors['address'] = 'Address is required.';
    }

    if (empty($formData['previous_school'])) {
        $errors['previous_school'] = 'Previous school/college name is required.';
    }

    if (empty($formData['previous_class'])) {
        $errors['previous_class'] = 'Previous class is required.';
    }

    if ($formData['obtained_marks'] <= 0) {
        $errors['obtained_marks'] = 'Please enter obtained marks.';
    }

    if ($formData['total_marks'] <= 0) {
        $errors['total_marks'] = 'Please enter total marks.';
    } elseif ($formData['obtained_marks'] > $formData['total_marks']) {
        $errors['obtained_marks'] = 'Obtained marks cannot exceed total marks.';
    }

    if ($formData['course_id'] <= 0) {
        $errors['course_id'] = 'Please select a program.';
    } else {
        // Verify course exists
        $validCourse = $db->fetchColumn(
            "SELECT id FROM courses WHERE id = ? AND is_active = 1",
            [$formData['course_id']]
        );
        if (!$validCourse) {
            $errors['course_id'] = 'Selected program is not available.';
        }
    }

    // 4. Handle photo upload
    $photoFilename = null;
    if (!empty($_FILES['photo']['name'])) {
        $uploader = new Uploader(UPLOAD_ADMISSIONS);
        $uploaded = $uploader->upload($_FILES['photo'], 'student_');
        if ($uploaded === false) {
            $errors['photo'] = $uploader->getError();
        } else {
            $photoFilename = $uploaded;
        }
    }

    // 5. If no errors — insert into DB
    if (empty($errors)) {
        try {
            $appNumber = generateAppNumber();

            $db->insert(
                "INSERT INTO admission_applications
                    (app_number, student_name, father_name, cnic_bform,
                     date_of_birth, gender, email, mobile, address, city,
                     previous_school, previous_class, obtained_marks,
                     total_marks, course_id, photo, status, ip_address)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pending',?)",
                [
                    $appNumber,
                    $formData['student_name'],
                    $formData['father_name'],
                    $formData['cnic_bform'],
                    $formData['date_of_birth'],
                    $formData['gender'],
                    $formData['email'],
                    $formData['mobile'],
                    $formData['address'],
                    $formData['city'],
                    $formData['previous_school'],
                    $formData['previous_class'],
                    $formData['obtained_marks'],
                    $formData['total_marks'],
                    $formData['course_id'],
                    $photoFilename,
                    getClientIP(),
                ]
            );

            $success  = true;
            $formData = []; // clear form after success

        } catch (Exception $e) {
            $errors['general'] = 'A server error occurred. Please try again.';
            error_log('[Admission Error] ' . $e->getMessage());
        }
    }
}

require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- ════════════════════════════════════════════════════════════
     PAGE HERO
═══════════════════════════════════════════════════════════════ -->
<section class="page-hero">
    <div class="container">
        <h1>Online Admission Form</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
                <li class="breadcrumb-item active">Admissions</li>
            </ol>
        </nav>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     SUCCESS MESSAGE
═══════════════════════════════════════════════════════════════ -->
<?php if ($success): ?>
<section class="section-pad">
    <div class="container">
        <div class="admission-success text-center">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="mt-4 mb-2">Application Submitted Successfully!</h2>
            <p class="text-secondary mb-4">
                Your application has been received. Please save your application number for future reference.
            </p>
            <div class="app-number-display">
                <span class="app-number-label">Your Application Number</span>
                <span class="app-number-value"><?= h($appNumber) ?></span>
            </div>
            <p class="text-muted small mt-4 mb-4">
                Our admissions team will review your application and contact you on the
                mobile number you provided within <strong>3–5 working days</strong>.
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="<?= BASE_URL ?>/" class="btn btn-fvc-outline">Back to Home</a>
                <a href="<?= BASE_URL ?>/courses.php" class="btn btn-fvc-primary">Explore Programs</a>
            </div>
        </div>
    </div>
</section>

<?php else: ?>

<!-- ════════════════════════════════════════════════════════════
     ADMISSION FORM
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad">
    <div class="container">

        <!-- General error -->
        <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle me-2"></i><?= h($errors['general']) ?>
        </div>
        <?php endif; ?>

        <div class="row g-5">

            <!-- ── LEFT: Info sidebar ─────────────────────── -->
            <div class="col-lg-4 order-lg-2">
                <div class="admission-sidebar">

                    <div class="sidebar-block">
                        <h6><i class="fas fa-info-circle text-gold me-2"></i>Before You Apply</h6>
                        <ul class="sidebar-checklist">
                            <li><i class="fas fa-check"></i> Matric/equivalent result card ready</li>
                            <li><i class="fas fa-check"></i> CNIC / B-Form number available</li>
                            <li><i class="fas fa-check"></i> Recent passport-size photo (JPG/PNG, max 5MB)</li>
                            <li><i class="fas fa-check"></i> Valid Pakistani mobile number</li>
                        </ul>
                    </div>

                    <div class="sidebar-block">
                        <h6><i class="fas fa-calendar-alt text-gold me-2"></i>Important Dates</h6>
                        <ul class="sidebar-dates">
                            <li>
                                <span>Admissions Open</span>
                                <strong>1st June <?= date('Y') ?></strong>
                            </li>
                            <li>
                                <span>Last Date</span>
                                <strong>31st August <?= date('Y') ?></strong>
                            </li>
                            <li>
                                <span>Classes Begin</span>
                                <strong>1st September <?= date('Y') ?></strong>
                            </li>
                        </ul>
                    </div>

                    <div class="sidebar-block">
                        <h6><i class="fas fa-phone-alt text-gold me-2"></i>Need Help?</h6>
                        <p class="text-secondary small mb-2">
                            Contact our admissions office for assistance.
                        </p>
                        <a href="tel:<?= h(setting('contact_phone')) ?>"
                           class="btn btn-fvc-outline btn-sm w-100 mb-2">
                            <i class="fas fa-phone me-2"></i><?= h(setting('contact_phone')) ?>
                        </a>
                        <?php if ($wa = setting('contact_whatsapp')): ?>
                        <a href="https://wa.me/<?= h($wa) ?>"
                           target="_blank" class="btn btn-success btn-sm w-100">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp Us
                        </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- ── RIGHT: The actual form ─────────────────── -->
            <div class="col-lg-8 order-lg-1">
                <form method="POST" action="<?= BASE_URL ?>/admissions.php"
                      enctype="multipart/form-data"
                      class="admission-form fvc-form"
                      novalidate
                      id="admissionForm">

                    <?= CSRF::field() ?>

                    <!-- ── SECTION A: Personal Information ── -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <span class="form-section-number">A</span>
                            <h5>Personal Information</h5>
                        </div>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="student_name" class="form-label">
                                    Student Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="student_name" name="student_name"
                                       class="form-control <?= isset($errors['student_name']) ? 'is-invalid' : '' ?>"
                                       value="<?= h($formData['student_name'] ?? '') ?>"
                                       placeholder="As per B-Form / CNIC" maxlength="100">
                                <?php if (isset($errors['student_name'])): ?>
                                    <div class="invalid-feedback"><?= h($errors['student_name']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="father_name" class="form-label">
                                    Father's Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="father_name" name="father_name"
                                       class="form-control <?= isset($errors['father_name']) ? 'is-invalid' : '' ?>"
                                       value="<?= h($formData['father_name'] ?? '') ?>"
                                       placeholder="Father's name" maxlength="100">
                                <?php if (isset($errors['father_name'])): ?>
                                    <div class="invalid-feedback"><?= h($errors['father_name']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="cnic_bform" class="form-label">
                                    CNIC / B-Form Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="cnic_bform" name="cnic_bform"
                                       class="form-control <?= isset($errors['cnic_bform']) ? 'is-invalid' : '' ?>"
                                       value="<?= h($formData['cnic_bform'] ?? '') ?>"
                                       placeholder="XXXXX-XXXXXXX-X" maxlength="15">
                                <div class="form-text">Format: 42101-1234567-9</div>
                                <?php if (isset($errors['cnic_bform'])): ?>
                                    <div class="invalid-feedback"><?= h($errors['cnic_bform']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">
                                    Date of Birth <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="date_of_birth" name="date_of_birth"
                                       class="form-control <?= isset($errors['date_of_birth']) ? 'is-invalid' : '' ?>"
                                       value="<?= h($formData['date_of_birth'] ?? '') ?>"
                                       max="<?= date('Y-m-d', strtotime('-12 years')) ?>">
                                <?php if (isset($errors['date_of_birth'])): ?>
                                    <div class="invalid-feedback"><?= h($errors['date_of_birth']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Gender <span class="text-danger">*</span>
                                </label>
                                <div class="gender-options">
                                    <?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $label): ?>
                                    <label class="gender-option <?= ($formData['gender'] ?? '') === $val ? 'selected' : '' ?>">
                                        <input type="radio" name="gender" value="<?= $val ?>"
                                               <?= ($formData['gender'] ?? '') === $val ? 'checked' : '' ?>>
                                        <?= $label ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (isset($errors['gender'])): ?>
                                    <div class="text-danger small mt-1"><?= h($errors['gender']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="photo" class="form-label">
                                    Passport Size Photo
                                </label>
                                <input type="file" id="photo" name="photo"
                                       class="form-control <?= isset($errors['photo']) ? 'is-invalid' : '' ?>"
                                       accept="image/jpeg,image/png,image/webp">
                                <div class="form-text">JPG/PNG/WEBP, max 5MB</div>
                                <?php if (isset($errors['photo'])): ?>
                                    <div class="invalid-feedback"><?= h($errors['photo']) ?></div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>

                    <!-- ── SECTION B: Contact Information ─── -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <span class="form-section-number">B</span>
                            <h5>Contact Information</h5>
                        </div>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="mobile" class="form-label">
                                    Mobile Number <span class="text-danger">*</span>
                                </label>
                                <input type="tel" id="mobile" name="mobile"
                                       class="form-control <?= isset($errors['mobile']) ? 'is-invalid' : '' ?>"
                                       value="<?= h($formData['mobile'] ?? '') ?>"
                                       placeholder="0300-1234567">
                                <?php if (isset($errors['mobile'])): ?>
                                    <div class="invalid-feedback"><?= h($errors['mobile']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email"
                                       class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                       value="<?= h($formData['email'] ?? '') ?>"
                                       placeholder="student@example.com">
                                <div class="form-text">Optional but recommended</div>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= h($errors['email']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">
                                    Full Address <span class="text-danger">*</span>
                                </label>
                                <textarea id="address" name="address" rows="2"
                                          class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>"
                                          placeholder="House no, Street, Area, City"
                                          maxlength="300"><?= h($formData['address'] ?? '') ?></textarea>
                                <?php if (isset($errors['address'])): ?>
                                    <div class="invalid-feedback"><?= h($errors['address']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" id="city" name="city"
                                       class="form-control"
                                       value="<?= h($formData['city'] ?? '') ?>"
                                       placeholder="e.g. Peshawar" maxlength="80">
                            </div>

                        </div>
                    </div>

                    <!-- ── SECTION C: Academic Background ─── -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <span class="form-section-number">C</span>
                            <h5>Academic Background</h5>
                        </div>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="previous_school" class="form-label">
                                    Previous School / College <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="previous_school" name="previous_school"
                                       class="form-control <?= isset($errors['previous_school']) ? 'is-invalid' : '' ?>"
                                       value="<?= h($formData['previous_school'] ?? '') ?>"
                                       placeholder="School or college name" maxlength="200">
                                <?php if (isset($errors['previous_school'])): ?>
                                    <div class="invalid-feedback"><?= h($errors['previous_school']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="previous_class" class="form-label">
                                    Last Class Passed <span class="text-danger">*</span>
                                </label>
                                <select id="previous_class" name="previous_class"
                                        class="form-select <?= isset($errors['previous_class']) ? 'is-invalid' : '' ?>">
                                    <option value="">— Select —</option>
                                    <?php foreach (['Matric (Science)', 'Matric (Arts)', 'Matric (General)', 'O-Level', 'Other'] as $cls): ?>
                                    <option value="<?= $cls ?>"
                                        <?= ($formData['previous_class'] ?? '') === $cls ? 'selected' : '' ?>>
                                        <?= $cls ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['previous_class'])): ?>
                                    <div class="invalid-feedback"><?= h($errors['previous_class']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4">
                                <label for="obtained_marks" class="form-label">
                                    Marks Obtained <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="obtained_marks" name="obtained_marks"
                                       class="form-control <?= isset($errors['obtained_marks']) ? 'is-invalid' : '' ?>"
                                       value="<?= h((string)($formData['obtained_marks'] ?? '')) ?>"
                                       min="0" max="1500" placeholder="e.g. 820">
                                <?php if (isset($errors['obtained_marks'])): ?>
                                    <div class="invalid-feedback"><?= h($errors['obtained_marks']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4">
                                <label for="total_marks" class="form-label">
                                    Total Marks <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="total_marks" name="total_marks"
                                       class="form-control <?= isset($errors['total_marks']) ? 'is-invalid' : '' ?>"
                                       value="<?= h((string)($formData['total_marks'] ?? '1100')) ?>"
                                       min="0" max="1500" placeholder="e.g. 1100">
                                <?php if (isset($errors['total_marks'])): ?>
                                    <div class="invalid-feedback"><?= h($errors['total_marks']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Percentage</label>
                                <div class="percentage-display" id="percentageDisplay">—</div>
                            </div>

                        </div>
                    </div>

                    <!-- ── SECTION D: Program Selection ───── -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <span class="form-section-number">D</span>
                            <h5>Program Selection</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">
                                    Select Program <span class="text-danger">*</span>
                                </label>
                                <div class="program-select-grid">
                                    <?php foreach ($courses as $course):
                                        $checked = ((int)($formData['course_id'] ?? $preselectedCourse) === (int)$course['id']);
                                    ?>
                                    <label class="program-option <?= $checked ? 'selected' : '' ?>">
                                        <input type="radio" name="course_id"
                                               value="<?= (int)$course['id'] ?>"
                                               <?= $checked ? 'checked' : '' ?>>
                                        <strong><?= h($course['title']) ?></strong>
                                        <span><?= h($course['short_code']) ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (isset($errors['course_id'])): ?>
                                    <div class="text-danger small mt-1"><?= h($errors['course_id']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- ── Declaration & Submit ────────────── -->
                    <div class="form-section">
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox"
                                   id="declaration" name="declaration" required>
                            <label class="form-check-label small text-secondary" for="declaration">
                                I hereby declare that all information provided in this form is
                                true and correct to the best of my knowledge. I understand that
                                providing false information may result in cancellation of my admission.
                            </label>
                        </div>

                        <button type="submit" class="btn btn-fvc-primary btn-lg w-100" id="submitBtn">
                            <i class="fas fa-paper-plane me-2"></i>Submit Application
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/templates/footer.php'; ?>