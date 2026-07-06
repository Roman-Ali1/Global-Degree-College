<?php
/**
 * Future Vision College – Public Site Footer
 * Save to: includes/templates/footer.php
 */
?>

<!-- ── Footer ──────────────────────────────────────────────── -->
<footer class="fvc-footer">

    <!-- Top footer -->
    <div class="footer-top">
        <div class="container">
            <div class="row g-5">

                <!-- Col 1: Brand & About -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="footer-brand mb-4">
                        <img src="<?= BASE_URL ?>/<?= h(setting('site_logo', 'assets/images/logo/logo.png')) ?>"
                             alt="<?= h(setting('site_name')) ?>"
                             height="56" class="mb-3"
                             onerror="this.style.display='none'">
                        <h5 class="text-white fw-bold mb-1"><?= h(setting('site_name')) ?></h5>
                        <p class="footer-tagline"><?= h(setting('site_tagline')) ?></p>
                    </div>
                    <p class="footer-text">
                        Empowering students with quality education, modern facilities, and
                        a nurturing environment since 2005. Recognized by BISE and Higher
                        Education Commission of Pakistan.
                    </p>
                    <!-- Social Icons -->
                    <div class="footer-social mt-4 d-flex gap-3">
                        <?php if ($fb = setting('social_facebook')): ?>
                        <a href="<?= h($fb) ?>" target="_blank" rel="noopener" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($ig = setting('social_instagram')): ?>
                        <a href="<?= h($ig) ?>" target="_blank" rel="noopener" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($yt = setting('social_youtube')): ?>
                        <a href="<?= h($yt) ?>" target="_blank" rel="noopener" aria-label="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($tw = setting('social_twitter')): ?>
                        <a href="<?= h($tw) ?>" target="_blank" rel="noopener" aria-label="Twitter/X">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($wa = setting('contact_whatsapp')): ?>
                        <a href="https://wa.me/<?= h($wa) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="col-lg-2 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <h6 class="footer-heading">Quick Links</h6>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>/">Home</a></li>
                        <li><a href="<?= BASE_URL ?>/about.php">About Us</a></li>
                        <li><a href="<?= BASE_URL ?>/courses.php">Programs</a></li>
                        <li><a href="<?= BASE_URL ?>/admissions.php">Admissions</a></li>
                        <li><a href="<?= BASE_URL ?>/gallery.php">Gallery</a></li>
                        <li><a href="<?= BASE_URL ?>/news.php">News</a></li>
                        <li><a href="<?= BASE_URL ?>/contact.php">Contact</a></li>
                    </ul>
                </div>

                <!-- Col 3: Programs -->
                <div class="col-lg-2 col-md-6" data-aos="fade-up" data-aos-delay="150">
                    <h6 class="footer-heading">Programs</h6>
                    <ul class="footer-links">
                        <?php
                        try {
                            $db      = Database::getInstance();
                            $courses = $db->fetchAll(
                                "SELECT title, slug FROM courses WHERE is_active = 1 ORDER BY sort_order"
                            );
                            foreach ($courses as $c): ?>
                            <li>
                                <a href="<?= BASE_URL ?>/courses.php#<?= h($c['slug']) ?>">
                                    <?= h($c['title']) ?>
                                </a>
                            </li>
                        <?php endforeach;
                        } catch (Exception $e) {
                            echo '<li><a href="' . BASE_URL . '/courses.php">View Programs</a></li>';
                        }
                        ?>
                    </ul>
                </div>

                <!-- Col 4: Contact Info -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <h6 class="footer-heading">Contact Us</h6>
                    <ul class="footer-contact-list">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= h(setting('contact_address', 'University Road, Peshawar')) ?></span>
                        </li>
                        <li>
                            <i class="fas fa-phone-alt"></i>
                            <a href="tel:<?= h(setting('contact_phone')) ?>">
                                <?= h(setting('contact_phone')) ?>
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-mobile-alt"></i>
                            <a href="tel:<?= h(setting('contact_mobile')) ?>">
                                <?= h(setting('contact_mobile')) ?>
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:<?= h(setting('contact_email')) ?>">
                                <?= h(setting('contact_email')) ?>
                            </a>
                        </li>
                    </ul>

                    <!-- Office hours -->
                    <div class="footer-hours mt-3">
                        <h6 class="footer-heading-sm">Office Hours</h6>
                        <table class="hours-table">
                            <tr><td>Mon – Fri</td><td>8:00 AM – 4:00 PM</td></tr>
                            <tr><td>Saturday</td><td>9:00 AM – 1:00 PM</td></tr>
                            <tr><td>Sunday</td><td>Closed</td></tr>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bottom footer -->
    <div class="footer-bottom">
        <div class="container d-flex flex-column flex-md-row
                    align-items-center justify-content-between gap-2">
            <p class="mb-0 footer-copy">
                &copy; <?= date('Y') ?>
                <a href="<?= BASE_URL ?>/"><?= h(setting('site_name')) ?></a>.
                All rights reserved.
            </p>
            <p class="mb-0 footer-copy">
                Designed &amp; Developed with
                <i class="fas fa-heart text-danger mx-1"></i>
                for quality education.
            </p>
        </div>
    </div>

</footer>
<!-- ── End Footer ─────────────────────────────────────────── -->

<!-- ── Back to Top Button ─────────────────────────────────── -->
<button class="back-to-top" id="backToTop" aria-label="Back to top">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<!-- Custom JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php if (!empty($extraJs)): foreach ($extraJs as $jsFile): ?>
<script src="<?= BASE_URL ?>/assets/js/<?= h($jsFile) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
