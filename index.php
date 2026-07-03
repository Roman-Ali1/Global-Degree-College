<?php
/**
 * Global Degree College – Home Page
 * Save to: index.php (project root)
 */

declare(strict_types=1);
require_once __DIR__ . '/includes/config/app.php';

$pageTitle       = 'Home';
$pageDescription = setting('meta_description', 'Global Degree College – Premier intermediate college in Peshawar.');
$extraCss        = ['home.css'];

$db = Database::getInstance();

// ── Fetch data needed for this page ───────────────────────────
$courses = $db->fetchAll(
    "SELECT * FROM courses WHERE is_active = 1 ORDER BY sort_order LIMIT 5"
);

$faculty = $db->fetchAll(
    "SELECT * FROM faculty WHERE is_active = 1 AND show_on_home = 1 ORDER BY sort_order LIMIT 4"
);

$news = $db->fetchAll(
    "SELECT * FROM news WHERE status = 'published'
     ORDER BY published_at DESC LIMIT 3"
);

$events = $db->fetchAll(
    "SELECT * FROM events WHERE event_date >= CURDATE() AND status = 'upcoming'
     ORDER BY event_date ASC LIMIT 3"
);

$testimonials = $db->fetchAll(
    "SELECT * FROM testimonials WHERE status = 'approved' AND show_on_home = 1
     ORDER BY sort_order LIMIT 6"
);

$galleryPreview = $db->fetchAll(
    "SELECT * FROM gallery WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8"
);

// Stats (live counts from DB, fallback to static if empty)
$totalStudents = (int)$db->fetchColumn(
    "SELECT COUNT(*) FROM students WHERE status = 'active'"
) ?: 1200;
$totalFaculty = (int)$db->fetchColumn(
    "SELECT COUNT(*) FROM faculty WHERE is_active = 1"
) ?: 45;
$totalCourses = (int)$db->fetchColumn(
    "SELECT COUNT(*) FROM courses WHERE is_active = 1"
) ?: 5;

require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- ════════════════════════════════════════════════════════════
     SECTION 1: HERO SLIDER
═══════════════════════════════════════════════════════════════ -->
<section class="hero-slider">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>

        <div class="carousel-inner">

            <!-- Slide 1 -->
            <div class="carousel-item active">
                <div class="hero-slide hero-slide-1">
                    <div class="hero-overlay"></div>
                    <div class="container hero-content">
                        <div class="row">
                            <div class="col-lg-7" data-aos="fade-up">
                                <span class="hero-badge">
                                    <i class="fas fa-graduation-cap me-2"></i>Admissions Open <?= date('Y') ?>
                                </span>
                                <h1 class="hero-title">Shaping Global Leaders Through Quality Education</h1>
                                <p class="hero-subtitle">
                                    Join Global Degree College and unlock your potential with expert faculty,
                                    modern facilities, and a track record of academic excellence.
                                </p>
                                <div class="d-flex flex-wrap gap-3 mt-4">
                                    <a href="<?= BASE_URL ?>/admissions.php" class="btn btn-fvc-gold btn-lg">
                                        Apply Now <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/courses.php" class="btn btn-outline-light btn-lg">
                                        Explore Programs
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-item">
                <div class="hero-slide hero-slide-2">
                    <div class="hero-overlay"></div>
                    <div class="container hero-content">
                        <div class="row">
                            <div class="col-lg-7" data-aos="fade-up">
                                <span class="hero-badge"><i class="fas fa-flask me-2"></i>Modern Science Labs</span>
                                <h1 class="hero-title">State-of-the-Art Facilities for Hands-On Learning</h1>
                                <p class="hero-subtitle">
                                    Fully equipped physics, chemistry, biology and computer labs designed
                                    to give students real practical experience.
                                </p>
                                <div class="d-flex flex-wrap gap-3 mt-4">
                                    <a href="<?= BASE_URL ?>/about.php" class="btn btn-fvc-gold btn-lg">
                                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-item">
                <div class="hero-slide hero-slide-3">
                    <div class="hero-overlay"></div>
                    <div class="container hero-content">
                        <div class="row">
                            <div class="col-lg-7" data-aos="fade-up">
                                <span class="hero-badge"><i class="fas fa-award me-2"></i>Scholarships Available</span>
                                <h1 class="hero-title">Merit & Need-Based Financial Support</h1>
                                <p class="hero-subtitle">
                                    We believe no deserving student should be held back. Explore our
                                    scholarship programs today.
                                </p>
                                <div class="d-flex flex-wrap gap-3 mt-4">
                                    <a href="<?= BASE_URL ?>/scholarships.php" class="btn btn-fvc-gold btn-lg">
                                        View Scholarships <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="hero-nav-icon"><i class="fas fa-chevron-left"></i></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="hero-nav-icon"><i class="fas fa-chevron-right"></i></span>
        </button>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     SECTION 2: WELCOME MESSAGE
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad bg-off-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="welcome-image-wrap">
                    <img src="<?= BASE_URL ?>/assets/images/about/principal.jpg"
                         alt="Principal - Global Degree College"
                         class="welcome-img"
                         onerror="this.src='<?= BASE_URL ?>/assets/images/defaults/placeholder.png'">
                    <div class="welcome-badge">
                        <i class="fas fa-medal"></i>
                        <div>
                            <strong>20+</strong>
                            <span>Years of Excellence</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <span class="eyebrow-tag">Welcome to FVC</span>
                <h2 class="mb-3">A Legacy of Academic Excellence Since 2005</h2>
                <p class="text-secondary mb-4">
                    Global Degree College has been a cornerstone of quality intermediate education in
                    Peshawar for over two decades. We combine rigorous academics with character
                    building, preparing students not just for exams, but for life.
                </p>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="welcome-feature">
                            <i class="fas fa-check-circle text-gold"></i>
                            <span>BISE & HEC Recognized</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="welcome-feature">
                            <i class="fas fa-check-circle text-gold"></i>
                            <span>Qualified Faculty</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="welcome-feature">
                            <i class="fas fa-check-circle text-gold"></i>
                            <span>Modern Science Labs</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="welcome-feature">
                            <i class="fas fa-check-circle text-gold"></i>
                            <span>Hostel Facility</span>
                        </div>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/about.php" class="btn btn-fvc-primary">
                    Read Our Story <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     SECTION 3: COLLEGE STATISTICS
═══════════════════════════════════════════════════════════════ -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="stat-item" data-aos="fade-up">
                    <div class="stat-number" data-counter="<?= $totalStudents ?>">0</div>
                    <div class="stat-label">Students Enrolled</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-number" data-counter="<?= $totalFaculty ?>">0</div>
                    <div class="stat-label">Expert Faculty</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-number" data-counter="<?= $totalCourses ?>">0</div>
                    <div class="stat-label">Programs Offered</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-number" data-counter="98">0</div>
                    <div class="stat-label">% Success Rate</div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     SECTION 4: OFFERED PROGRAMS
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad">
    <div class="container">
        <div class="section-heading" data-aos="fade-up">
            <span class="eyebrow">Academic Programs</span>
            <h2>Programs We Offer</h2>
            <div class="section-divider"></div>
            <p>Choose from our range of intermediate programs designed to set you on the path to your career goals.</p>
        </div>

        <div class="row g-4">
            <?php if (empty($courses)): ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="fas fa-info-circle me-2"></i>No programs available yet. Check back soon.
                </div>
            <?php else: foreach ($courses as $i => $course): ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $i * 50 ?>">
                <div class="fvc-card course-card h-100 p-4">
                    <div class="course-icon">
                        <i class="fas <?= h($course['icon'] ?: 'fa-book') ?>"></i>
                    </div>
                    <h4 class="mt-3 mb-2"><?= h($course['title']) ?></h4>
                    <p class="text-secondary small mb-3"><?= h(truncate($course['description'] ?? '', 90)) ?></p>
                    <ul class="course-meta-list">
                        <li><i class="fas fa-clock text-gold"></i> Duration: <?= h($course['duration']) ?></li>
                        <li><i class="fas fa-chair text-gold"></i> Seats: <?= (int)$course['total_seats'] ?></li>
                        <li><i class="fas fa-tag text-gold"></i> Fee: <?= formatCurrency((float)$course['fee_per_month']) ?>/mo</li>
                    </ul>
                    <a href="<?= BASE_URL ?>/admissions.php?course=<?= (int)$course['id'] ?>"
                       class="btn btn-fvc-outline btn-sm w-100 mt-3">
                        Apply for This Program
                    </a>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <div class="text-center mt-5" data-aos="fade-up">
            <a href="<?= BASE_URL ?>/courses.php" class="btn btn-fvc-primary">
                View All Programs <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     SECTION 5: WHY CHOOSE US
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad bg-light-blue">
    <div class="container">
        <div class="section-heading" data-aos="fade-up">
            <span class="eyebrow">Our Advantage</span>
            <h2>Why Choose Future Vision College</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3" data-aos="fade-up">
                <div class="why-card text-center">
                    <div class="why-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h5>Expert Faculty</h5>
                    <p class="text-secondary small">Highly qualified and experienced teachers committed to student success.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                <div class="why-card text-center">
                    <div class="why-icon"><i class="fas fa-flask"></i></div>
                    <h5>Modern Labs</h5>
                    <p class="text-secondary small">Fully equipped science and computer labs for hands-on learning.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                <div class="why-card text-center">
                    <div class="why-icon"><i class="fas fa-shield-alt"></i></div>
                    <h5>Safe Campus</h5>
                    <p class="text-secondary small">CCTV-monitored secure campus with dedicated supervision.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                <div class="why-card text-center">
                    <div class="why-icon"><i class="fas fa-hand-holding-usd"></i></div>
                    <h5>Affordable Fees</h5>
                    <p class="text-secondary small">Quality education at accessible rates, with scholarships available.</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     SECTION 6: ADMISSION OPEN BANNER
═══════════════════════════════════════════════════════════════ -->
<?php if (setting('admission_open', '1') === '1'): ?>
<section class="admission-banner">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h2 class="text-navy mb-2">Admissions Open for <?= date('Y') ?>–<?= date('y') + 1 ?></h2>
                <p class="text-navy mb-0 opacity-75">
                    Limited seats available. Apply now to secure your spot for the upcoming academic session.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0" data-aos="fade-left">
                <a href="<?= BASE_URL ?>/admissions.php" class="btn btn-fvc-primary btn-lg">
                    Apply Now <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ════════════════════════════════════════════════════════════
     SECTION 7: FACULTY HIGHLIGHTS
═══════════════════════════════════════════════════════════════ -->
<?php if (!empty($faculty)): ?>
<section class="section-pad">
    <div class="container">
        <div class="section-heading" data-aos="fade-up">
            <span class="eyebrow">Meet The Team</span>
            <h2>Our Distinguished Faculty</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row g-4">
            <?php foreach ($faculty as $i => $member): ?>
            <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $i * 50 ?>">
                <div class="faculty-card text-center">
                    <div class="faculty-photo-wrap">
                        <img src="<?= uploadUrl('faculty', $member['photo']) ?>"
                             alt="<?= h($member['full_name']) ?>" class="faculty-photo">
                    </div>
                    <h6 class="mt-3 mb-1"><?= h($member['full_name']) ?></h6>
                    <p class="text-gold small mb-0"><?= h($member['designation']) ?></p>
                    <p class="text-muted small"><?= h($member['qualification'] ?? '') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ════════════════════════════════════════════════════════════
     SECTION 8: NEWS & EVENTS
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad bg-off-white">
    <div class="container">
        <div class="section-heading" data-aos="fade-up">
            <span class="eyebrow">Stay Updated</span>
            <h2>Latest News & Events</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row g-4">
            <!-- News column -->
            <div class="col-lg-8">
                <div class="row g-4">
                    <?php if (empty($news)): ?>
                        <div class="col-12 text-muted">No news published yet.</div>
                    <?php else: foreach ($news as $i => $item): ?>
                    <div class="col-md-6" data-aos="fade-up" data-aos-delay="<?= $i * 50 ?>">
                        <div class="fvc-card news-card h-100">
                            <div class="news-img-wrap">
                                <img src="<?= uploadUrl('news', $item['featured_image']) ?>" alt="<?= h($item['title']) ?>">
                                <?php if ($item['category']): ?>
                                <span class="news-category"><?= h($item['category']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="p-3">
                                <span class="text-muted small">
                                    <i class="far fa-calendar me-1"></i><?= formatDate($item['published_at'] ?? $item['created_at']) ?>
                                </span>
                                <h6 class="mt-2 mb-2">
                                    <a href="<?= BASE_URL ?>/news-detail.php?slug=<?= h($item['slug']) ?>" class="text-dark stretched-link">
                                        <?= h($item['title']) ?>
                                    </a>
                                </h6>
                                <p class="text-secondary small mb-0"><?= h(truncate($item['excerpt'] ?? strip_tags($item['body']), 80)) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Events sidebar -->
            <div class="col-lg-4" data-aos="fade-left">
                <div class="fvc-card p-4">
                    <h5 class="mb-3"><i class="fas fa-calendar-check text-gold me-2"></i>Upcoming Events</h5>
                    <?php if (empty($events)): ?>
                        <p class="text-muted small mb-0">No upcoming events scheduled.</p>
                    <?php else: foreach ($events as $event): ?>
                    <div class="event-mini-item">
                        <div class="event-date-box">
                            <span class="event-day"><?= date('d', strtotime($event['event_date'])) ?></span>
                            <span class="event-month"><?= date('M', strtotime($event['event_date'])) ?></span>
                        </div>
                        <div>
                            <h6 class="mb-1">
                                <a href="<?= BASE_URL ?>/events-detail.php?slug=<?= h($event['slug']) ?>" class="text-dark">
                                    <?= h($event['title']) ?>
                                </a>
                            </h6>
                            <span class="text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i><?= h($event['venue'] ?? 'Main Campus') ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                    <a href="<?= BASE_URL ?>/events.php" class="btn btn-fvc-outline btn-sm w-100 mt-3">
                        View All Events
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     SECTION 9: STUDENT TESTIMONIALS
═══════════════════════════════════════════════════════════════ -->
<?php if (!empty($testimonials)): ?>
<section class="section-pad">
    <div class="container">
        <div class="section-heading" data-aos="fade-up">
            <span class="eyebrow">Testimonials</span>
            <h2>What Our Students Say</h2>
            <div class="section-divider"></div>
        </div>

        <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach (array_chunk($testimonials, 3) as $i => $group): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <div class="row g-4">
                        <?php foreach ($group as $t): ?>
                        <div class="col-md-4">
                            <div class="testimonial-card">
                                <div class="testimonial-rating">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <i class="fas fa-star <?= $s <= $t['rating'] ? 'text-gold' : 'text-muted opacity-25' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="testimonial-text">"<?= h(truncate($t['message'], 150)) ?>"</p>
                                <div class="d-flex align-items-center gap-3 mt-3">
                                    <img src="<?= uploadUrl('faculty', $t['photo']) ?>" class="testimonial-avatar" alt="<?= h($t['name']) ?>">
                                    <div>
                                        <h6 class="mb-0"><?= h($t['name']) ?></h6>
                                        <span class="text-muted small"><?= h($t['designation'] ?? '') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($testimonials) > 3): ?>
            <div class="carousel-controls-custom text-center mt-4">
                <button class="carousel-btn" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-btn" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ════════════════════════════════════════════════════════════
     SECTION 10: GALLERY PREVIEW
═══════════════════════════════════════════════════════════════ -->
<?php if (!empty($galleryPreview)): ?>
<section class="section-pad bg-off-white">
    <div class="container">
        <div class="section-heading" data-aos="fade-up">
            <span class="eyebrow">Campus Life</span>
            <h2>Gallery Preview</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row g-3">
            <?php foreach ($galleryPreview as $i => $img): ?>
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="<?= $i * 30 ?>">
                <a href="<?= uploadUrl('gallery', $img['filename']) ?>"
                   class="gallery-preview-item" data-lightbox="home-gallery"
                   data-title="<?= h($img['title']) ?>">
                    <img src="<?= uploadUrl('gallery', $img['filename']) ?>" alt="<?= h($img['title']) ?>" loading="lazy">
                    <span class="gallery-overlay"><i class="fas fa-search-plus"></i></span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4" data-aos="fade-up">
            <a href="<?= BASE_URL ?>/gallery.php" class="btn btn-fvc-outline">
                View Full Gallery <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ════════════════════════════════════════════════════════════
     SECTION 11: CONTACT CTA
═══════════════════════════════════════════════════════════════ -->
<section class="contact-cta-section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7" data-aos="fade-right">
                <h3 class="text-white mb-2">Have Questions? We're Here to Help.</h3>
                <p class="text-white-50 mb-0">
                    Reach out to our admissions office for guidance on programs, fees, and the application process.
                </p>
            </div>
            <div class="col-lg-5 text-lg-end" data-aos="fade-left">
                <a href="tel:<?= h(setting('contact_phone')) ?>" class="btn btn-outline-light me-2">
                    <i class="fas fa-phone-alt me-2"></i><?= h(setting('contact_phone')) ?>
                </a>
                <a href="<?= BASE_URL ?>/contact.php" class="btn btn-fvc-gold">
                    Contact Us <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<?php
// ── SECTION 12: FOOTER (shared template) ──────────────────────
require_once __DIR__ . '/includes/templates/footer.php';