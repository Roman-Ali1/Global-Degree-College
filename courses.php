<?php
/**
 * Global Degree College – Courses Page
 * Save to: courses.php (project root)
 */

declare(strict_types=1);
require_once __DIR__ . '/includes/config/app.php';

$pageTitle       = 'Programs Offered';
$pageDescription = 'Explore FSc Pre-Medical, FSc Pre-Engineering, ICS, FA and I.Com programs at Global Degree College, Peshawar.';
$extraCss        = ['courses.css'];

$db = Database::getInstance();

// Fetch all active courses ordered by sort_order
$courses = $db->fetchAll(
    "SELECT * FROM courses WHERE is_active = 1 ORDER BY sort_order"
);

// Pre-select course if coming from homepage "Apply for This Program" button
$selectedCourse = getInt('course');

require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- ════════════════════════════════════════════════════════════
     PAGE HERO
═══════════════════════════════════════════════════════════════ -->
<section class="page-hero">
    <div class="container">
        <h1>Programs We Offer</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
                <li class="breadcrumb-item active">Programs</li>
            </ol>
        </nav>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     INTRO STRIP
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad-sm bg-off-white">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7" data-aos="fade-right">
                <span class="eyebrow-tag">Intermediate Programs</span>
                <h2 class="mb-2">Find the Right Program for Your Future</h2>
                <p class="text-secondary mb-0">
                    All programs are affiliated with BISE Peshawar. Click any program
                    below to see full details — eligibility, fee structure, and available seats.
                </p>
            </div>
            <div class="col-lg-5 text-lg-end" data-aos="fade-left">
                <!-- Quick filter tabs -->
                <div class="course-filter-tabs">
                    <button type="button" class="filter-tab active" data-filter="all">All Programs</button>
                    <button type="button" class="filter-tab" data-filter="science">Science</button>
                    <button type="button" class="filter-tab" data-filter="arts">Arts</button>
                    <button type="button" class="filter-tab" data-filter="commerce">Commerce</button>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     COURSE CARDS GRID
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad">
    <div class="container">
        <?php if (empty($courses)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <p>No programs available yet. Please check back soon.</p>
            </div>
        <?php else: ?>

        <div class="row g-4" id="courseGrid">
            <?php
            foreach ($courses as $i => $course):
                $shortCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', $course['short_code'] ?? ''));
                if (str_starts_with($shortCode, 'FSC') || $shortCode === 'ICS') {
                    $filter = 'science';
                } elseif ($shortCode === 'FA') {
                    $filter = 'arts';
                } elseif (str_contains($shortCode, 'COM')) {
                    $filter = 'commerce';
                } else {
                    $filter = 'all';
                }
                $isActive  = ($selectedCourse === (int)$course['id']) ? 'course-card-active' : '';
                $icon      = $course['icon'] ?: 'fa-book-open';
            ?>
            <div class="col-md-6 col-lg-4 course-grid-item"
                 data-filter="<?= h($filter) ?>"
                 data-aos="fade-up"
                 data-aos-delay="<?= $i * 60 ?>">

                <div class="course-detail-card <?= $isActive ?>" id="course-<?= (int)$course['id'] ?>">

                    <!-- Card Header -->
                    <div class="course-card-header">
                        <div class="course-card-icon">
                            <i class="fas <?= h($icon) ?>"></i>
                        </div>
                        <div>
                            <span class="course-badge"><?= h($course['short_code']) ?></span>
                            <h4 class="mb-0"><?= h($course['title']) ?></h4>
                        </div>
                    </div>

                    <!-- Description -->
                    <p class="course-description"><?= h($course['description'] ?? '') ?></p>

                    <!-- Details Grid -->
                    <div class="course-details-grid">
                        <div class="course-detail-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <span class="detail-label">Duration</span>
                                <span class="detail-value"><?= h($course['duration']) ?></span>
                            </div>
                        </div>
                        <div class="course-detail-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <span class="detail-label">Total Seats</span>
                                <span class="detail-value"><?= (int)$course['total_seats'] ?> Seats</span>
                            </div>
                        </div>
                        <div class="course-detail-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <div>
                                <span class="detail-label">Monthly Fee</span>
                                <span class="detail-value"><?= formatCurrency((float)$course['fee_per_month']) ?></span>
                            </div>
                        </div>
                        <div class="course-detail-item">
                            <i class="fas fa-file-invoice"></i>
                            <div>
                                <span class="detail-label">Admission Fee</span>
                                <span class="detail-value"><?= formatCurrency((float)$course['admission_fee']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Eligibility -->
                    <?php if (!empty($course['eligibility'])): ?>
                    <div class="course-eligibility">
                        <i class="fas fa-check-circle text-gold me-2"></i>
                        <strong>Eligibility:</strong> <?= h($course['eligibility']) ?>
                    </div>
                    <?php endif; ?>

                    <!-- Action -->
                    <a href="<?= BASE_URL ?>/admissions.php?course=<?= (int)$course['id'] ?>"
                       class="btn btn-fvc-primary w-100 mt-3">
                        <i class="fas fa-file-alt me-2"></i>Apply for This Program
                    </a>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- No results message (shown by JS filter) -->
        <div class="text-center py-5 text-muted d-none" id="noFilterResults">
            <i class="fas fa-search fa-2x mb-3 opacity-50"></i>
            <p>No programs found in this category.</p>
        </div>

        <?php endif; ?>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     COMPARISON TABLE
═══════════════════════════════════════════════════════════════ -->
<?php if (!empty($courses)): ?>
<section class="section-pad bg-off-white">
    <div class="container">
        <div class="section-heading" data-aos="fade-up">
            <span class="eyebrow">Quick Reference</span>
            <h2>Program Comparison</h2>
            <div class="section-divider"></div>
        </div>

        <div class="table-responsive" data-aos="fade-up">
            <table class="table fvc-table">
                <thead>
                    <tr class="table-heading-row">
                        <th scope="col">Program</th>
                        <th scope="col">Code</th>
                        <th scope="col">Duration</th>
                        <th scope="col">Seats</th>
                        <th scope="col">Monthly Fee</th>
                        <th scope="col">Admission Fee</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                    <tr>
                        <td>
                            <i class="fas <?= h($course['icon'] ?: 'fa-book') ?> text-gold me-2"></i>
                            <strong><?= h($course['title']) ?></strong>
                        </td>
                        <td><span class="badge-code"><?= h($course['short_code']) ?></span></td>
                        <td><?= h($course['duration']) ?></td>
                        <td><?= (int)$course['total_seats'] ?></td>
                        <td><?= formatCurrency((float)$course['fee_per_month']) ?></td>
                        <td><?= formatCurrency((float)$course['admission_fee']) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/admissions.php?course=<?= (int)$course['id'] ?>"
                               class="btn btn-fvc-gold btn-sm">
                                Apply
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ════════════════════════════════════════════════════════════
     WHY THESE PROGRAMS
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad">
    <div class="container">
        <div class="section-heading" data-aos="fade-up">
            <span class="eyebrow">Career Pathways</span>
            <h2>Where Can These Programs Take You?</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4" data-aos="fade-up">
                <div class="pathway-card">
                    <div class="pathway-icon bg-red"><i class="fas fa-heartbeat"></i></div>
                    <h6>FSc Pre-Medical</h6>
                    <p class="text-secondary small mb-2">Opens doors to:</p>
                    <ul class="pathway-list">
                        <li>MBBS / BDS</li>
                        <li>Pharmacy (Pharm-D)</li>
                        <li>Veterinary Science</li>
                        <li>Allied Health Sciences</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="80">
                <div class="pathway-card">
                    <div class="pathway-icon bg-blue"><i class="fas fa-cogs"></i></div>
                    <h6>FSc Pre-Engineering</h6>
                    <p class="text-secondary small mb-2">Opens doors to:</p>
                    <ul class="pathway-list">
                        <li>BE / BSc Engineering</li>
                        <li>Architecture</li>
                        <li>BSc Mathematics / Physics</li>
                        <li>Computer Science</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="160">
                <div class="pathway-card">
                    <div class="pathway-icon bg-green"><i class="fas fa-laptop-code"></i></div>
                    <h6>ICS</h6>
                    <p class="text-secondary small mb-2">Opens doors to:</p>
                    <ul class="pathway-list">
                        <li>BS Computer Science</li>
                        <li>Software Engineering</li>
                        <li>IT / Cybersecurity</li>
                        <li>Data Science / AI</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="240">
                <div class="pathway-card">
                    <div class="pathway-icon bg-purple"><i class="fas fa-book-open"></i></div>
                    <h6>FA</h6>
                    <p class="text-secondary small mb-2">Opens doors to:</p>
                    <ul class="pathway-list">
                        <li>BA / BS Arts & Humanities</li>
                        <li>LLB / Law</li>
                        <li>Journalism & Media</li>
                        <li>Psychology / Social Work</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="320">
                <div class="pathway-card">
                    <div class="pathway-icon bg-gold"><i class="fas fa-chart-line"></i></div>
                    <h6>I.Com</h6>
                    <p class="text-secondary small mb-2">Opens doors to:</p>
                    <ul class="pathway-list">
                        <li>BBA / MBA</li>
                        <li>B.Com / M.Com</li>
                        <li>CA / ACCA / CMA</li>
                        <li>Banking & Finance</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     CTA
═══════════════════════════════════════════════════════════════ -->
<section class="contact-cta-section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7" data-aos="fade-right">
                <h3 class="text-white mb-2">Not Sure Which Program Is Right for You?</h3>
                <p class="text-white-50 mb-0">
                    Call our admissions office — we'll guide you to the program that fits
                    your interests and career goals.
                </p>
            </div>
            <div class="col-lg-5 text-lg-end" data-aos="fade-left">
                <a href="tel:<?= h(setting('contact_phone')) ?>" class="btn btn-outline-light me-2">
                    <i class="fas fa-phone-alt me-2"></i><?= h(setting('contact_phone')) ?>
                </a>
                <a href="<?= BASE_URL ?>/admissions.php" class="btn btn-fvc-gold">
                    Apply Now <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/templates/footer.php'; ?>