<?php
/**
 * The template for displaying the footer
 *
 * @package WP_Augoose
 */
?>

    <footer id="colophon" class="site-footer">
        <div class="container">
            <?php
            $page_url = static function ( $slug ) {
                $p = get_page_by_path( (string) $slug );
                if ( $p instanceof WP_Post ) {
                    $url = get_permalink( $p );
                    if ( $url ) {
                        return $url;
                    }
                }
                return '#';
            };
            $url_about = $page_url( 'about-us' );
            $url_terms = $page_url( 'terms-of-service' );
            $url_faq   = $page_url( 'faq' );
            $url_contact = $page_url( 'contact-us' );
            ?>
            <div class="footer-columns">
                <div class="footer-col">
                    <div class="footer-col-title"><?php echo esc_html__( 'About Augoose', 'wp-augoose' ); ?></div>
                    <?php if ( has_nav_menu( 'footer_about' ) ) : ?>
                        <?php
                        wp_nav_menu(
                            array(
                                'theme_location' => 'footer_about',
                                'menu_id'        => 'footer-about-menu',
                                'container'      => false,
                                'fallback_cb'    => false,
                                'depth'          => 1,
                            )
                        );
                        ?>
                    <?php else : ?>
                        <ul class="footer-links">
                            <li><a href="<?php echo esc_url( $url_about ); ?>"><?php echo esc_html__( 'About us', 'wp-augoose' ); ?></a></li>
                            <li><a href="<?php echo esc_url( $url_terms ); ?>"><?php echo esc_html__( 'Terms of service', 'wp-augoose' ); ?></a></li>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="footer-col">
                    <div class="footer-col-title"><?php echo esc_html__( 'Help', 'wp-augoose' ); ?></div>
                    <?php if ( has_nav_menu( 'footer_help' ) ) : ?>
                        <?php
                        wp_nav_menu(
                            array(
                                'theme_location' => 'footer_help',
                                'menu_id'        => 'footer-help-menu',
                                'container'      => false,
                                'fallback_cb'    => false,
                                'depth'          => 1,
                            )
                        );
                        ?>
                    <?php else : ?>
                        <ul class="footer-links">
                            <li><a href="<?php echo esc_url( $url_contact ); ?>">Contact us</a></li>
                            <li><a href="<?php echo esc_url( $url_faq ); ?>">FAQ</a></li>
                            <li><a href="<?php echo esc_url( $url_terms . '#return-refund-policy' ); ?>">Return or refunds policy</a></li>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="footer-col">
                    <div class="footer-col-title"><?php echo esc_html__( 'Shop', 'wp-augoose' ); ?></div>
                    <?php if ( has_nav_menu( 'footer_shop' ) ) : ?>
                        <?php
                        wp_nav_menu(
                            array(
                                'theme_location' => 'footer_shop',
                                'menu_id'        => 'footer-shop-menu',
                                'container'      => false,
                                'fallback_cb'    => false,
                                'depth'          => 1,
                            )
                        );
                        ?>
                    <?php else : ?>
                        <ul class="footer-links">
                            <li><a href="#" class="footer-size-guide-link" data-guide="jacket-regular"><?php echo esc_html__( 'Jacket size and fit guide', 'wp-augoose' ); ?></a></li>
                            <li><a href="#" class="footer-size-guide-link" data-guide="pants-regular"><?php echo esc_html__( 'Pants size and fit guide', 'wp-augoose' ); ?></a></li>
                            <li><a href="#" class="order-shipping-link"><?php echo esc_html__( 'Order & shipping', 'wp-augoose' ); ?></a></li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="footer-bottom-bar">
            <div class="container">
                <div class="footer-payments" aria-label="<?php esc_attr_e( 'Payment methods', 'wp-augoose' ); ?>">
                    <div class="footer-payments-title"><?php echo esc_html__( 'Payment Methods', 'wp-augoose' ); ?></div>
                    <div class="footer-payment-icons" aria-hidden="true">
                        <!-- PayPal Official Logo - White text on dark background -->
                        <span class="pay-icon pay-paypal" title="PayPal" aria-label="PayPal">
                            <svg viewBox="0 0 120 32" width="120" height="32" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                                <text x="60" y="22" font-size="20" font-weight="700" fill="#ffffff" font-family="Arial, sans-serif" text-anchor="middle" letter-spacing="0.5">PayPal</text>
                            </svg>
                        </span>
                        <!-- Visa Official Logo - White text on dark background -->
                        <span class="pay-icon pay-visa" title="Visa" aria-label="Visa">
                            <svg viewBox="0 0 120 32" width="120" height="32" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                                <text x="60" y="22" font-size="22" font-weight="900" fill="#ffffff" font-family="Arial, sans-serif" text-anchor="middle" letter-spacing="2">VISA</text>
                            </svg>
                        </span>
                        <!-- Mastercard Official Logo - Two overlapping circles -->
                        <span class="pay-icon pay-mastercard" title="Mastercard" aria-label="Mastercard">
                            <svg viewBox="0 0 120 32" width="120" height="32" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="38" cy="16" r="10" fill="#EB001B"/>
                                <circle cx="48" cy="16" r="10" fill="#F79E1B"/>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="footer-contact-info">
                    <div class="footer-contact-item">
                        <span class="footer-contact-label">Email:</span>
                        <a href="mailto:halo@augoose.co" class="footer-contact-link">halo@augoose.co</a>
                    </div>
                    <div class="footer-contact-item">
                        <span class="footer-contact-label">WhatsApp:</span>
                        <a href="https://wa.me/6285128001852" target="_blank" rel="noopener noreferrer" class="footer-contact-link">+62 851-2800-1852</a>
                    </div>
                    <div class="footer-contact-item">
                        <span class="footer-contact-label">Instagram:</span>
                        <a href="https://www.instagram.com/augoose.co" target="_blank" rel="noopener noreferrer" class="footer-contact-link">@augoose.co</a>
                    </div>
                    <div class="footer-contact-item">
                        <span class="footer-contact-label">TikTok:</span>
                        <a href="https://www.tiktok.com/@augoose.co" target="_blank" rel="noopener noreferrer" class="footer-contact-link">@augoose.co</a>
                    </div>
                </div>

                <div class="site-info">
                    <p><?php echo esc_html( 'Augoose.id | ' . date( 'Y' ) . ' All Rights Reserved.' ); ?></p>
                </div>
            </div>
        </div>
    </footer>
</div>


    <!-- Size Guide Modal (Global - accessible from all pages) -->
    <div class="size-guide-modal" id="size-guide-modal" style="display: none;">
        <div class="size-guide-overlay"></div>
        <div class="size-guide-content-wrapper">
            <button class="size-guide-close" aria-label="Close size guide">&times;</button>
            <div class="size-guide-content">
                <h2 class="size-guide-title">SIZE GUIDE</h2>
                <div class="size-guide-tabs">
                    <button class="size-guide-tab active" data-guide="jacket-regular">JACKET REGULAR (SERVICE) SIZE GUIDE</button>
                    <button class="size-guide-tab" data-guide="jacket-vintage">JACKET VINTAGE BOXY FIT SIZE GUIDE</button>
                    <button class="size-guide-tab" data-guide="pants-regular">PANTS REGULAR FIT (DOUBLE KNEE, CARPENTER, UTILITY) SIZE GUIDE</button>
                    <button class="size-guide-tab" data-guide="pants-straight">PANTS STRAIGHT FIT (SENTINEL, FATIGUE) SIZE GUIDE</button>
                    <button class="size-guide-tab" data-guide="workshirt-vest">WORKSHIRT AND VEST SIZE GUIDE</button>
                </div>
                
                <!-- Jacket Regular (Service) Size Guide -->
                <div class="size-guide-image-wrapper" data-guide="jacket-regular">
                    <img src="https://augoose.co/wp-content/uploads/2026/01/Jacket-Regular-Service_Size-Chart.jpg" alt="Jacket Regular Service Size Chart" class="size-guide-image" />
                </div>
                
                <!-- Jacket Vintage Boxy Fit Size Guide -->
                <div class="size-guide-image-wrapper" data-guide="jacket-vintage" style="display: none;">
                    <img src="https://augoose.co/wp-content/uploads/2026/01/Jacket-Vintage-Boxy-Fit_Size-Chart.jpg" alt="Jacket Vintage Boxy Fit Size Chart" class="size-guide-image" />
                </div>
                
                <!-- Pants Regular Fit Size Guide -->
                <div class="size-guide-image-wrapper" data-guide="pants-regular" style="display: none;">
                    <img src="https://augoose.co/wp-content/uploads/2026/01/Pants-Regular-Fit-Double-Knee-Carpenter-Utility_-Size-CHart.jpg" alt="Pants Regular Fit Size Chart" class="size-guide-image" />
                </div>
                
                <!-- Pants Straight Fit Size Guide -->
                <div class="size-guide-image-wrapper" data-guide="pants-straight" style="display: none;">
                    <img src="https://augoose.co/wp-content/uploads/2026/01/Pants-Straight-Fit-Sentinel-Fatigue_Size-Chart.jpg" alt="Pants Straight Fit Size Chart" class="size-guide-image" />
                </div>
                
                <!-- Workshirt and Vest Size Guide -->
                <div class="size-guide-image-wrapper" data-guide="workshirt-vest" style="display: none;">
                    <img src="https://augoose.co/wp-content/uploads/2026/01/Workshirt-and-Vest_Size-Chart.jpg" alt="Workshirt and Vest Size Chart" class="size-guide-image" />
                </div>
            </div>
        </div>
    </div>

    <!-- Order & Shipping Modal -->
    <div class="order-shipping-modal" id="order-shipping-modal" style="display: none;">
        <div class="order-shipping-overlay"></div>
        <div class="order-shipping-content-wrapper">
            <button class="order-shipping-close" aria-label="Close order & shipping">&times;</button>
            <div class="order-shipping-content">
                <h2 class="order-shipping-title">ORDER & SHIPPING</h2>
                
                <div class="shipping-table-wrapper">
                    <table class="shipping-table">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th>Est. Min. Weight (kg)</th>
                                <th>Price starts at (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Afghanistan</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Albania</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Algeria</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>American Samoa</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Argentina</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Austria</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Australia</td><td>1</td><td>$36.00</td></tr>
                            <tr><td>Azerbaijan</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Bahrain</td><td>1</td><td>$47.00</td></tr>
                            <tr><td>Belgium</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Brazil</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Brunei</td><td>1</td><td>$29.00</td></tr>
                            <tr><td>Bulgaria</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Cambodia</td><td>1</td><td>$29.00</td></tr>
                            <tr><td>Canada</td><td>1</td><td>$44.00</td></tr>
                            <tr><td>Chile</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>China1</td><td>1</td><td>$32.00</td></tr>
                            <tr><td>China2</td><td>1</td><td>$36.00</td></tr>
                            <tr><td>Colombia</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Costa Rica</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Croatia</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Czech</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Denmark</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Egypt</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Finland</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>France</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Germany</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Greece</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Hong Kong</td><td>1</td><td>$29.00</td></tr>
                            <tr><td>Hungary</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>India</td><td>1</td><td>$47.00</td></tr>
                            <tr><td>Iran</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Ireland</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Italy</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Japan</td><td>1</td><td>$32.00</td></tr>
                            <tr><td>Kazakhstan</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Korea</td><td>1</td><td>$36.00</td></tr>
                            <tr><td>Kuwait</td><td>1</td><td>$47.00</td></tr>
                            <tr><td>Laos</td><td>1</td><td>$29.00</td></tr>
                            <tr><td>Luxembourg</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Macau</td><td>1</td><td>$29.00</td></tr>
                            <tr><td>Maldives</td><td>1</td><td>$47.00</td></tr>
                            <tr><td>Mexico</td><td>1</td><td>$44.00</td></tr>
                            <tr><td>Monaco</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Mongolia</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Morocco</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Myanmar</td><td>1</td><td>$29.00</td></tr>
                            <tr><td>Nepal</td><td>1</td><td>$47.00</td></tr>
                            <tr><td>Netherlands</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>New Zealand</td><td>1</td><td>$36.00</td></tr>
                            <tr><td>Paraguay</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Peru</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Philippines</td><td>1</td><td>$29.00</td></tr>
                            <tr><td>Poland</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Portugal</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Puerto Rico</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Saudi Arabia</td><td>1</td><td>$47.00</td></tr>
                            <tr><td>Spain</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Sweden</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Switzerland</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Taiwan</td><td>1</td><td>$36.00</td></tr>
                            <tr><td>Thailand</td><td>1</td><td>$29.00</td></tr>
                            <tr><td>Tunisia</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Turkey</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Ukraine</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>United Arab Emirates</td><td>1</td><td>$47.00</td></tr>
                            <tr><td>UK</td><td>1</td><td>$52.00</td></tr>
                            <tr><td>Uruguay</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>USA</td><td>1</td><td>$44.00</td></tr>
                            <tr><td>Uzbekistan</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Venezuela</td><td>1</td><td>$68.00</td></tr>
                            <tr><td>Vietnam</td><td>1</td><td>$29.00</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script>
// Size Guide Modal - Simple inline script to ensure it works
(function() {
    function openSizeGuide(guide) {
        guide = guide || 'jacket-regular';
        var modal = document.getElementById('size-guide-modal');
        if (!modal) {
            console.error('Size guide modal not found in DOM');
            return;
        }
        
        console.log('Opening size guide:', guide);
        
        // Show correct guide
        var tabs = modal.querySelectorAll('.size-guide-tab');
        var wrappers = modal.querySelectorAll('.size-guide-image-wrapper');
        
        for (var i = 0; i < tabs.length; i++) {
            if (tabs[i].getAttribute('data-guide') === guide) {
                tabs[i].classList.add('active');
            } else {
                tabs[i].classList.remove('active');
            }
        }
        
        for (var i = 0; i < wrappers.length; i++) {
            if (wrappers[i].getAttribute('data-guide') === guide) {
                wrappers[i].style.display = 'block';
                wrappers[i].style.visibility = 'visible';
            } else {
                wrappers[i].style.display = 'none';
            }
        }
        
        // Force show modal
        modal.setAttribute('style', 'display: flex !important; visibility: visible !important; opacity: 1 !important;');
        document.body.classList.add('size-guide-open');
        
        console.log('Modal should be visible now', modal.style.display);
    }
    
    function closeSizeGuide() {
        var modal = document.getElementById('size-guide-modal');
        if (modal) {
            modal.setAttribute('style', 'display: none !important; visibility: hidden !important;');
            document.body.classList.remove('size-guide-open');
        }
    }
    
    // Wait for DOM ready
    function init() {
        console.log('Initializing size guide...');
        
        // Footer links - open modal with appropriate tab
        var footerLinks = document.querySelectorAll('.footer-size-guide-link');
        for (var i = 0; i < footerLinks.length; i++) {
            footerLinks[i].addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var guide = this.getAttribute('data-guide') || 'jacket-vintage';
                openSizeGuide(guide);
            });
        }
        
        // Product page SIZE GUIDE link - open modal with product-specific tab
        var sizeGuideLinks = document.querySelectorAll('.size-guide-link');
        for (var i = 0; i < sizeGuideLinks.length; i++) {
            sizeGuideLinks[i].addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                // Determine guide based on product or default to jacket-vintage
                var guide = 'jacket-vintage';
                var url = window.location.href.toLowerCase();
                var title = document.title.toLowerCase();
                if (url.includes('jacket') || title.includes('jacket')) {
                    if (url.includes('vintage') || url.includes('boxy') || title.includes('vintage') || title.includes('boxy')) {
                        guide = 'jacket-vintage';
                    } else {
                        guide = 'jacket-regular';
                    }
                } else if (url.includes('pants') || title.includes('pants')) {
                    if (url.includes('straight') || url.includes('sentinel') || url.includes('fatigue') || title.includes('straight') || title.includes('sentinel') || title.includes('fatigue')) {
                        guide = 'pants-straight';
                    } else {
                        guide = 'pants-regular';
                    }
                } else if (url.includes('shirt') || url.includes('vest') || title.includes('shirt') || title.includes('vest')) {
                    guide = 'workshirt-vest';
                }
                openSizeGuide(guide);
            });
        }
        
        // Close buttons
        var closeBtns = document.querySelectorAll('.size-guide-close, .size-guide-overlay');
        console.log('Found close buttons:', closeBtns.length);
        for (var i = 0; i < closeBtns.length; i++) {
            closeBtns[i].addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Close button clicked');
                closeSizeGuide();
            });
        }
        
        // Tab switching
        var tabs = document.querySelectorAll('.size-guide-tab');
        console.log('Found tabs:', tabs.length);
        for (var i = 0; i < tabs.length; i++) {
            tabs[i].addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var guide = this.getAttribute('data-guide');
                if (guide) {
                    console.log('Tab clicked:', guide);
                    openSizeGuide(guide);
                }
            });
        }
        
        // ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                closeSizeGuide();
            }
        });
        
        console.log('Size guide initialized');
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Also try after a short delay to ensure everything is loaded
    setTimeout(init, 500);
})();

// Order & Shipping Modal
(function() {
    function openOrderShipping() {
        var modal = document.getElementById('order-shipping-modal');
        if (!modal) {
            console.error('Order & shipping modal not found in DOM');
            return;
        }
        
        modal.setAttribute('style', 'display: flex !important; visibility: visible !important; opacity: 1 !important;');
        document.body.classList.add('order-shipping-open');
    }
    
    function closeOrderShipping() {
        var modal = document.getElementById('order-shipping-modal');
        if (modal) {
            modal.setAttribute('style', 'display: none !important; visibility: hidden !important;');
            document.body.classList.remove('order-shipping-open');
        }
    }
    
    function initOrderShipping() {
        // Footer link
        var orderShippingLinks = document.querySelectorAll('.order-shipping-link');
        for (var i = 0; i < orderShippingLinks.length; i++) {
            orderShippingLinks[i].addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openOrderShipping();
            });
        }
        
        // Close buttons
        var closeBtns = document.querySelectorAll('.order-shipping-close, .order-shipping-overlay');
        for (var i = 0; i < closeBtns.length; i++) {
            closeBtns[i].addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeOrderShipping();
            });
        }
        
        // ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                closeOrderShipping();
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initOrderShipping);
    } else {
        initOrderShipping();
    }
    
    setTimeout(initOrderShipping, 500);
})();
</script>

<?php wp_footer(); ?>

</body>
</html>
