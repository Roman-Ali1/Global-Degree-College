<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config/app.php';

$pageTitle       = 'Contact Us';
$pageDescription = 'Get in touch with Global Degree College. Visit us on University Road, Peshawar, or send us a message online.';
$extraCss        = ['contact.css'];

$db = Database::getInstance();

$errors   = [];
$success  = false;
$formData = [];

if (isPost()) {
    CSRF::requireValid();

    $formData = [
        'name'    => post('name'),
        'email'   => cleanEmail($_POST['email'] ?? ''),
        'phone'   => cleanPhone($_POST['phone'] ?? ''),
        'subject' => post('subject'),
        'message' => post('message'),
    ];

    // Validation
    if (strlen($formData['name']) < 2) {
        $errors['name'] = 'Please enter your full name.';
    }
    if (empty($formData['email'])) {
        $errors['email'] = 'A valid email address is required.';
    }
    if (empty($formData['subject'])) {
        $errors['subject'] = 'Please enter a subject.';
    }
    if (strlen($formData['message']) < 10) {
        $errors['message'] = 'Message must be at least 10 characters.';
    }

    // Rate limit: same IP, last 10 minutes
    if (empty($errors)) {
        $recentCount = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM contact_messages
             WHERE ip_address = ? AND created_at > NOW() - INTERVAL 10 MINUTE",
            [getClientIP()]
        );
        if ($recentCount >= 3) {
            $errors['general'] = 'Too many messages sent. Please wait 10 minutes before trying again.';
        }
    }

    if (empty($errors)) {
        $db->insert(
            "INSERT INTO contact_messages (name, email, phone, subject, message, ip_address)
             VALUES (?,?,?,?,?,?)",
            [
                $formData['name'],
                $formData['email'],
                $formData['phone'],
                $formData['subject'],
                $formData['message'],
                getClientIP(),
            ]
        );
        $success  = true;
        $formData = [];
    }
}

require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- PAGE HERO -->
<section class="page-hero">
    <div class="container">
        <h1>Contact Us</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
                <li class="breadcrumb-item active">Contact</li>
            </ol>
        </nav>
    </div>
</section>


<!-- CONTACT INFO STRIP -->
<section class="contact-strip">
    <div class="container">
        <div class="row g-4">
            <div class="col-sm-6 col-lg-3" data-aos="fade-up">
                <div class="contact-strip-item">
                    <div class="contact-strip-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h6>Address</h6>
                        <p><?= h(setting('contact_address', 'University Road, Peshawar')) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="80">
                <div class="contact-strip-item">
                    <div class="contact-strip-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div>
                        <h6>Phone</h6>
                        <p>
                            <a href="tel:<?= h(setting('contact_phone')) ?>">
                                <?= h(setting('contact_phone')) ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="160">
                <div class="contact-strip-item">
                    <div class="contact-strip-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h6>Email</h6>
                        <p>
                            <a href="mailto:<?= h(setting('contact_email')) ?>">
                                <?= h(setting('contact_email')) ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="240">
                <div class="contact-strip-item">
                    <div class="contact-strip-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h6>Office Hours</h6>
                        <p>Mon–Fri: 8AM–4PM<br>Sat: 9AM–1PM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- FORM + MAP -->
<section class="section-pad">
    <div class="container">
        <div class="row g-5 align-items-start">

            <!-- Form -->
            <div class="col-lg-7" data-aos="fade-right">

                <?php if ($success): ?>
                <!-- Success state -->
                <div class="contact-success">
                    <div class="contact-success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Message Sent!</h3>
                    <p class="text-secondary">
                        Thank you for reaching out. Our team will get back to you
                        within <strong>1–2 working days</strong>.
                    </p>
                    <a href="<?= BASE_URL ?>/contact.php" class="btn btn-fvc-outline btn-sm">
                        Send Another Message
                    </a>
                </div>

                <?php else: ?>

                <div class="section-heading text-start mb-4">
                    <span class="eyebrow">Get In Touch</span>
                    <h2>Send Us a Message</h2>
                    <div class="section-divider" style="margin:12px 0;"></div>
                </div>

                <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger py-2 small mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i><?= h($errors['general']) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="" class="fvc-form contact-form" id="contactForm">
                    <?= CSRF::field() ?>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="name" class="form-label">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="name" name="name"
                                   class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                   value="<?= h($formData['name'] ?? '') ?>"
                                   placeholder="Your full name" maxlength="100">
                            <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= h($errors['name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" id="email" name="email"
                                   class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   value="<?= h($formData['email'] ?? '') ?>"
                                   placeholder="you@example.com">
                            <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= h($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" id="phone" name="phone"
                                   class="form-control"
                                   value="<?= h($formData['phone'] ?? '') ?>"
                                   placeholder="0300-1234567">
                        </div>

                        <div class="col-md-6">
                            <label for="subject" class="form-label">
                                Subject <span class="text-danger">*</span>
                            </label>
                            <select id="subject" name="subject"
                                    class="form-select <?= isset($errors['subject']) ? 'is-invalid' : '' ?>">
                                <option value="">— Select subject —</option>
                                <?php
                                $subjects = [
                                    'Admission Inquiry',
                                    'Fee Information',
                                    'Course Information',
                                    'Hostel Inquiry',
                                    'Scholarship Inquiry',
                                    'Result / Exam Query',
                                    'General Inquiry',
                                    'Other',
                                ];
                                foreach ($subjects as $s):
                                ?>
                                <option value="<?= h($s) ?>"
                                    <?= ($formData['subject'] ?? '') === $s ? 'selected' : '' ?>>
                                    <?= h($s) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['subject'])): ?>
                            <div class="invalid-feedback"><?= h($errors['subject']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label for="message" class="form-label">
                                Message <span class="text-danger">*</span>
                            </label>
                            <textarea id="message" name="message" rows="5"
                                      class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>"
                                      placeholder="Write your message here..."
                                      maxlength="2000"><?= h($formData['message'] ?? '') ?></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <?php if (isset($errors['message'])): ?>
                                <div class="text-danger small"><?= h($errors['message']) ?></div>
                                <?php else: ?>
                                <div></div>
                                <?php endif; ?>
                                <span class="text-muted small" id="charCount">0 / 2000</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-fvc-primary" id="contactSubmitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>

                    </div>
                </form>
                <?php endif; ?>
            </div>

            <!-- Sidebar info -->
            <div class="col-lg-5" data-aos="fade-left">

                <!-- Map embed -->
                <?php
                $mapValue    = trim(setting('map_embed_url'));
                $mapEmbedUrl = '';
                if ($mapValue) {
                    if (preg_match('#<iframe[^>]+src=["\']([^"\']+)["\']#i', $mapValue, $matches)) {
                        $mapEmbedUrl = $matches[1];
                    } elseif (preg_match('#^https?://#i', $mapValue)) {
                        $mapEmbedUrl = $mapValue;
                    }
                }
                if (!$mapEmbedUrl && $mapValue) {
                    $address = trim(setting('contact_address', 'University Road, Peshawar'));
                    $query   = rawurlencode($address ?: 'Global Degree College Peshawar');
                    $mapEmbedUrl = "https://www.google.com/maps?q={$query}&output=embed";
                }
                ?>
                <?php if ($mapEmbedUrl): ?>
                <div class="contact-map-wrap mb-4">
                    <iframe src="<?= h($mapEmbedUrl) ?>"
                            width="100%" height="280"
                            style="border:0; border-radius: var(--radius-md);"
                            allowfullscreen loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <?php else: ?>
                <div class="contact-map-placeholder mb-4">
                    <i class="fas fa-map-marked-alt"></i>
                    <p>University Road, Peshawar</p>
                    <a href="https://maps.google.com/?q=University+Road+Peshawar"
                       target="_blank" rel="noopener" class="btn btn-fvc-outline btn-sm">
                        Open in Google Maps
                    </a>
                </div>
                <?php endif; ?>

                <!-- WhatsApp CTA -->
                <?php if ($wa = setting('contact_whatsapp')): ?>
                <a href="https://wa.me/<?= h($wa) ?>?text=<?= urlencode('Hello, I have an inquiry about admissions at Global Degree College.') ?>"
                   target="_blank" rel="noopener"
                   class="contact-whatsapp-btn">
                    <i class="fab fa-whatsapp"></i>
                    <div>
                        <strong>Chat on WhatsApp</strong>
                        <span>Usually replies within an hour</span>
                    </div>
                    <i class="fas fa-arrow-right ms-auto"></i>
                </a>
                <?php endif; ?>

                <!-- Social links -->
                <div class="contact-social-block mt-4">
                    <h6 class="mb-3">Follow Us</h6>
                    <div class="contact-social-links">
                        <?php if ($fb = setting('social_facebook')): ?>
                        <a href="<?= h($fb) ?>" target="_blank" rel="noopener" class="csocial-btn csocial-fb">
                            <i class="fab fa-facebook-f"></i>
                            <span>Facebook</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($ig = setting('social_instagram')): ?>
                        <a href="<?= h($ig) ?>" target="_blank" rel="noopener" class="csocial-btn csocial-ig">
                            <i class="fab fa-instagram"></i>
                            <span>Instagram</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($yt = setting('social_youtube')): ?>
                        <a href="<?= h($yt) ?>" target="_blank" rel="noopener" class="csocial-btn csocial-yt">
                            <i class="fab fa-youtube"></i>
                            <span>YouTube</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Character counter + submit guard JS -->
<script>
(function () {
    const msg     = document.getElementById('message');
    const counter = document.getElementById('charCount');
    const btn     = document.getElementById('contactSubmitBtn');

    if (msg && counter) {
        msg.addEventListener('input', () => {
            const len = msg.value.length;
            counter.textContent = len + ' / 2000';
            counter.style.color = len > 1800 ? '#e53e3e' : '';
        });
    }

    const form = document.getElementById('contactForm');
    if (form && btn) {
        form.addEventListener('submit', () => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        });
    }
})();
</script>

<?php require_once __DIR__ . '/includes/templates/footer.php'; ?>