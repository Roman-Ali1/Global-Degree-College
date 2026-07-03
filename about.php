<?php
/**
 * Global Degree College – About Page
 * Save to: about.php (project root)
 */

declare(strict_types=1);
require_once __DIR__ . '/includes/config/app.php';

$pageTitle       = 'About Us';
$pageDescription = 'Learn about Global Degree College, its mission, vision, and the leadership serving students in Peshawar.';
$extraCss        = ['about.css'];

require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- ════════════════════════════════════════════════════════════
     PAGE HERO
═══════════════════════════════════════════════════════════════ -->
<section class="page-hero">
    <div class="container">
        <h1>About Global Degree College</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">About Us</li>
            </ol>
        </nav>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     SECTION: COLLEGE HISTORY
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <span class="eyebrow-tag">Our Story</span>
                <h2 class="mb-3">Committed to Career-Oriented Education</h2>
                <p class="text-secondary mb-3">
                    Global Degree College is located on University Road, Peshawar, and focuses on providing students with an environment where academic study, personal growth, and practical preparation can move together.
                </p>
                <p class="text-secondary mb-3">
                    What began as a single building with three classrooms has grown into a full campus
                    featuring modern science laboratories, a computer center, a library housing over
                    8,000 volumes, dedicated hostel facilities, and sports grounds — all built around
                    one principle: every student deserves an environment where they can excel.
                </p>
                <p class="text-secondary">
                    Under the leadership of Engr. Muhammad Saleem Khan and Adv. Muhammad Waseem, Global Degree College aims to provide a friendly, accessible, and disciplined learning atmosphere for its students.
                </p>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="history-image-grid">
                    <img src="<?= BASE_URL ?>/assets/images/about/campus-1.jpg"
                         alt="Campus building" class="history-img-main"
                         onerror="this.src='<?= BASE_URL ?>/assets/images/defaults/placeholder.png'">
                    <img src="<?= BASE_URL ?>/assets/images/about/campus-2.jpg"
                         alt="Students on campus" class="history-img-sub"
                         onerror="this.src='<?= BASE_URL ?>/assets/images/defaults/placeholder.png'">
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     SECTION: MISSION & VISION
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad bg-light-blue">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6" data-aos="fade-up">
                <div class="mv-card h-100">
                    <div class="mv-icon"><i class="fas fa-bullseye"></i></div>
                    <h3>Our Mission</h3>
                    <p class="text-secondary">
                        To provide students with balanced, career-oriented education through strong academics, supportive teaching, practical exposure, and an environment that encourages confidence, discipline, and personal responsibility.
                    </p>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="mv-card h-100">
                    <div class="mv-icon"><i class="fas fa-eye"></i></div>
                    <h3>Our Vision</h3>
                    <p class="text-secondary">
                        To develop capable, employable, and responsible students who are prepared for higher education, professional life, and meaningful contribution to society.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     SECTION: CHAIRMAN & PRINCIPAL MESSAGES
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad">
    <div class="container">
        <div class="section-heading" data-aos="fade-up">
            <span class="eyebrow">Leadership</span>
            <h2>A Message From Our Leadership</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row g-4">
            <!-- Chairman -->
            <div class="col-lg-6" data-aos="fade-up">
                <div class="leader-card">
                    <div class="leader-quote-icon"><i class="fas fa-quote-left"></i></div>
                    <p class="leader-message">
                        Studying at Global Degree College is both challenging and enjoyable. The college aims to help students balance their studies with healthy personal development in a friendly and accessible environment on University Road, Peshawar. With committed administration and carefully selected teaching faculty, we work to provide academic excellence, practical awareness, and a positive student experience.
                    </p>
                    <div class="leader-profile">
                        <img src="<?= BASE_URL ?>/assets/images/about/chairman.jpg"
                             alt="Chairman" class="leader-photo"
                             onerror="this.src='<?= BASE_URL ?>/assets/images/defaults/avatar-placeholder.png'">
                        <div>
                            <h6 class="mb-0">Engr. Muhammad Saleem Khan</h6>
                            <span class="text-gold small">Chairman | B.E Civil Engineering</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Principal -->
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <div class="leader-card">
                    <div class="leader-quote-icon"><i class="fas fa-quote-left"></i></div>
                    <p class="leader-message">
                        A good and competitive college should offer thorough curricula and encourage young students to take interest in modern scientific advancement, books, and wider national and international developments. At Global Degree College, we hope our work becomes a positive model for students and for others in the field of education.
                    </p>
                    <div class="leader-profile">
                        <img src="<?= BASE_URL ?>/assets/images/about/principal.jpg"
                             alt="Principal" class="leader-photo"
                             onerror="this.src='<?= BASE_URL ?>/assets/images/defaults/avatar-placeholder.png'">
                        <div>
                            <h6 class="mb-0">Adv. Muhammad Waseem</h6>
                            <span class="text-gold small">Principal | MA / LLB</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     SECTION: CORE VALUES
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad bg-off-white">
    <div class="container">
        <div class="section-heading" data-aos="fade-up">
            <span class="eyebrow">What Drives Us</span>
            <h2>Our Core Values</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row g-4">
            <div class="col-sm-6 col-lg-3" data-aos="fade-up">
                <div class="value-card text-center">
                    <div class="value-icon"><i class="fas fa-graduation-cap"></i></div>
                    <h6>Academic Excellence</h6>
                    <p class="text-secondary small">Uncompromising standards in teaching, assessment, and student support.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                <div class="value-card text-center">
                    <div class="value-icon"><i class="fas fa-handshake"></i></div>
                    <h6>Integrity</h6>
                    <p class="text-secondary small">Honesty and ethical conduct in every interaction, on and off campus.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                <div class="value-card text-center">
                    <div class="value-icon"><i class="fas fa-users"></i></div>
                    <h6>Inclusivity</h6>
                    <p class="text-secondary small">Quality education accessible to students from every background.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                <div class="value-card text-center">
                    <div class="value-icon"><i class="fas fa-lightbulb"></i></div>
                    <h6>Innovation</h6>
                    <p class="text-secondary small">Modern teaching methods and continuous improvement in how we educate.</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     SECTION: TIMELINE
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad">
    <div class="container">
        <div class="section-heading" data-aos="fade-up">
            <span class="eyebrow">Our Journey</span>
            <h2>Institutional Focus</h2>
            <div class="section-divider"></div>
        </div>

        <div class="timeline">
            <div class="timeline-item" data-aos="fade-up">
                <div class="timeline-year">2005</div>
                <div class="timeline-content">
                    <h6>Founding Year</h6>
                    <p class="text-secondary small mb-0">
                        The college was founded in 2005 by Engr. Muhammad Saleem Khan and Led by Principal Adv. Muhammad Waseem.
                    </p>
                </div>
            </div>

            <div class="timeline-item" data-aos="fade-up" data-aos-delay="100">
                <div class="timeline-year">2014</div>
                <div class="timeline-content">
                    <h6>Academic Environment</h6>
                    <p class="text-secondary small mb-0">
                        The college focuses on a disciplined, supportive, and accessible learning environment for students. The college established its online web copyright system and structured digital identity.
                    </p>
                </div>
            </div>

            <div class="timeline-item" data-aos="fade-up" data-aos-delay="200">
                <div class="timeline-year">2017</div>
                <div class="timeline-content">
                    <h6>Career-Oriented Approach</h6>
                    <p class="text-secondary small mb-0">
                        Global Degree College emphasizes employability, vocational awareness, and practical preparation alongside academics. A notable timeline event highlighted by the institution includes the major "Welcome Party 2017" featured in their historical gallery records.
                    </p>
                </div>
            </div>

            <div class="timeline-item" data-aos="fade-up" data-aos-delay="300">
                <div class="timeline-year">2021 – 2022</div>
                <div class="timeline-content">
                    <h6>Student Development</h6>
                    <p class="text-secondary small mb-0">
                        Students are encouraged to build confidence, responsibility, and awareness through academic and co-curricular engagement. The college ran admissions for its major 1st-year class sessions during this period.
                    </p>
                </div>
            </div>

            <div class="timeline-item" data-aos="fade-up" data-aos-delay="400">
                <div class="timeline-year">2026</div>
                <div class="timeline-content">
                    <h6>Continued Growth</h6>
                    <p class="text-secondary small mb-0">
                        The institution continues working to provide students with academic excellence and useful life experience. The college continues active operations, recently announcing board exam schedules (BISE Peshawar) and offering free tuition classes for 9th and 10th-grade students.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     CTA STRIP
═══════════════════════════════════════════════════════════════ -->
<section class="contact-cta-section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7" data-aos="fade-right">
                <h3 class="text-white mb-2">Ready to Begin Your Journey With Us?</h3>
                <p class="text-white-50 mb-0">
                    Explore our programs or get in touch with our admissions team today.
                </p>
            </div>
            <div class="col-lg-5 text-lg-end" data-aos="fade-left">
                <a href="<?= BASE_URL ?>/courses.php" class="btn btn-outline-light me-2">
                    View Programs
                </a>
                <a href="<?= BASE_URL ?>/admissions.php" class="btn btn-fvc-gold">
                    Apply Now <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/templates/footer.php'; ?>