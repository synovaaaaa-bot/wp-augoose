<?php
/**
 * Template Name: Contact Us
 * 
 * @package WP_Augoose
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        <div class="contact-us-page">
            <h1 class="page-title">Contact Us</h1>
            
            <div class="contact-info-grid">
                <!-- Email Section -->
                <div class="contact-info-card contact-email">
                    <h2 class="contact-label">EMAIL</h2>
                    <p class="contact-value">
                        <a href="mailto:halo@augoose.co">halo@augoose.co</a>
                    </p>
                </div>
                
                <!-- WhatsApp Section -->
                <div class="contact-info-card contact-whatsapp">
                    <h2 class="contact-label">WHATSAPP</h2>
                    <p class="contact-value">
                        <a href="https://wa.me/6285128001852" target="_blank" rel="noopener noreferrer">+62 851-2800-1852</a>
                    </p>
                </div>
                
                <!-- Instagram Section -->
                <div class="contact-info-card contact-instagram">
                    <h2 class="contact-label">IG</h2>
                    <p class="contact-value">
                        <a href="https://instagram.com/augoose.co" target="_blank" rel="noopener noreferrer">@augoose.co</a>
                    </p>
                    <p class="contact-cta">CTA</p>
                </div>
                
                <!-- TikTok Section -->
                <div class="contact-info-card contact-tiktok">
                    <h2 class="contact-label">TIKTOK</h2>
                    <p class="contact-value">
                        <a href="https://tiktok.com/@augoose.co" target="_blank" rel="noopener noreferrer">@augoose.co</a>
                    </p>
                    <p class="contact-cta">CTA</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
