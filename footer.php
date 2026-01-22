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
                            <li><a href="#" class="footer-size-guide-link" data-guide="jackets"><?php echo esc_html__( 'Jacket size and fit guide', 'wp-augoose' ); ?></a></li>
                            <li><a href="#" class="footer-size-guide-link" data-guide="pants"><?php echo esc_html__( 'Pants size and fit guide', 'wp-augoose' ); ?></a></li>
                            <li><a href="#"><?php echo esc_html__( 'Order & shipping', 'wp-augoose' ); ?></a></li>
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
                        <span class="pay-icon pay-visa" title="Visa">
                            <svg viewBox="0 0 64 20" width="64" height="20" aria-hidden="true">
                                <rect x="0.75" y="0.75" width="62.5" height="18.5" rx="3" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="1.5"/>
                                <text x="11" y="14" font-size="11" font-weight="800" fill="#ffffff" font-family="Arial, sans-serif" letter-spacing="1">VISA</text>
                            </svg>
                        </span>
                        <span class="pay-icon pay-mc" title="Mastercard">
                            <svg viewBox="0 0 64 20" width="64" height="20" aria-hidden="true">
                                <rect x="0.75" y="0.75" width="62.5" height="18.5" rx="3" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="1.5"/>
                                <circle cx="28" cy="10" r="6" fill="#EB001B"/>
                                <circle cx="36" cy="10" r="6" fill="#F79E1B" fill-opacity="0.95"/>
                                <text x="44" y="13" font-size="7" font-weight="800" fill="#ffffff" font-family="Arial, sans-serif" letter-spacing="0.5">MC</text>
                            </svg>
                        </span>
                        <span class="pay-icon pay-applepay" title="Apple Pay">
                            <svg viewBox="0 0 64 20" width="64" height="20" aria-hidden="true">
                                <rect x="0.75" y="0.75" width="62.5" height="18.5" rx="3" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="1.5"/>
                                <text x="10" y="13" font-size="9" font-weight="800" fill="#ffffff" font-family="Arial, sans-serif" letter-spacing="0.3">Apple Pay</text>
                            </svg>
                        </span>
                        <span class="pay-icon pay-gpay" title="Google Pay">
                            <svg viewBox="0 0 64 20" width="64" height="20" aria-hidden="true">
                                <rect x="0.75" y="0.75" width="62.5" height="18.5" rx="3" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="1.5"/>
                                <text x="14" y="13" font-size="9" font-weight="800" fill="#ffffff" font-family="Arial, sans-serif" letter-spacing="0.6">G Pay</text>
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
    <div class="size-guide-modal" id="size-guide-modal" style="display: none !important; visibility: hidden;">
        <div class="size-guide-overlay"></div>
        <div class="size-guide-content-wrapper">
            <button class="size-guide-close" aria-label="Close size guide">&times;</button>
            <div class="size-guide-content">
                <h2 class="size-guide-title">AUGOOSE</h2>
                <div class="size-guide-tabs">
                    <button class="size-guide-tab active" data-guide="pants">PANTS SIZE AND FIT GUIDE</button>
                    <button class="size-guide-tab" data-guide="jackets">JACKETS SIZE AND FIT GUIDE</button>
                </div>
                
                <!-- Pants Size Guide -->
                <div class="size-guide-table-wrapper" data-guide="pants">
                    <table class="size-guide-table">
                        <thead>
                            <tr>
                                <th>SIZE</th>
                                <th>28</th>
                                <th>30</th>
                                <th>32</th>
                                <th>34</th>
                                <th>36</th>
                                <th>38</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>WAIST</td>
                                <td>78</td>
                                <td>84</td>
                                <td>89</td>
                                <td>95</td>
                                <td>99</td>
                                <td>104</td>
                            </tr>
                            <tr>
                                <td>INSEAM</td>
                                <td>31</td>
                                <td>31</td>
                                <td>31</td>
                                <td>31</td>
                                <td>31</td>
                                <td>31</td>
                            </tr>
                            <tr>
                                <td>OPEN LEG</td>
                                <td>22</td>
                                <td>22</td>
                                <td>22</td>
                                <td>23</td>
                                <td>23</td>
                                <td>23</td>
                            </tr>
                            <tr>
                                <td>FRONT RISE</td>
                                <td>31</td>
                                <td>32</td>
                                <td>33</td>
                                <td>33</td>
                                <td>34</td>
                                <td>37</td>
                            </tr>
                            <tr>
                                <td>BACK RISE</td>
                                <td>41</td>
                                <td>42</td>
                                <td>43</td>
                                <td>44</td>
                                <td>45</td>
                                <td>48</td>
                            </tr>
                            <tr>
                                <td>THIGH</td>
                                <td>62</td>
                                <td>64</td>
                                <td>66</td>
                                <td>68</td>
                                <td>72</td>
                                <td>74</td>
                            </tr>
                            <tr>
                                <td>KNEE</td>
                                <td>23.5</td>
                                <td>24.5</td>
                                <td>25.5</td>
                                <td>28</td>
                                <td>28</td>
                                <td>30</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="size-guide-disclaimer">*The garments are cut and sewn by hand, so measurement may vary slightly each pairs</p>
                </div>
                
                <!-- Jackets Size Guide -->
                <div class="size-guide-table-wrapper" data-guide="jackets" style="display: none;">
                    <table class="size-guide-table">
                        <thead>
                            <tr>
                                <th>SIZE</th>
                                <th>S</th>
                                <th>M</th>
                                <th>L</th>
                                <th>XL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>CHEST</td>
                                <td>98</td>
                                <td>104</td>
                                <td>110</td>
                                <td>116</td>
                            </tr>
                            <tr>
                                <td>LENGTH</td>
                                <td>68</td>
                                <td>70</td>
                                <td>72</td>
                                <td>74</td>
                            </tr>
                            <tr>
                                <td>SHOULDER</td>
                                <td>44</td>
                                <td>46</td>
                                <td>48</td>
                                <td>50</td>
                            </tr>
                            <tr>
                                <td>SLEEVE</td>
                                <td>62</td>
                                <td>64</td>
                                <td>66</td>
                                <td>68</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="size-guide-disclaimer">*The garments are cut and sewn by hand, so measurement may vary slightly each pairs</p>
                </div>
            </div>
        </div>
    </div>

<script>
// Size Guide Modal - Simple inline script to ensure it works
(function() {
    function openSizeGuide(guide) {
        guide = guide || 'pants';
        var modal = document.getElementById('size-guide-modal');
        if (!modal) {
            console.error('Size guide modal not found in DOM');
            return;
        }
        
        console.log('Opening size guide:', guide);
        
        // Show correct guide
        var tabs = modal.querySelectorAll('.size-guide-tab');
        var wrappers = modal.querySelectorAll('.size-guide-table-wrapper');
        
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
        
        // Footer links
        var footerLinks = document.querySelectorAll('.footer-size-guide-link');
        console.log('Found footer links:', footerLinks.length);
        for (var i = 0; i < footerLinks.length; i++) {
            footerLinks[i].addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var guide = this.getAttribute('data-guide') || 'pants';
                console.log('Footer link clicked:', guide);
                openSizeGuide(guide);
            });
        }
        
        // Product page SIZE GUIDE link
        var sizeGuideLinks = document.querySelectorAll('.size-guide-link');
        console.log('Found size guide links:', sizeGuideLinks.length);
        for (var i = 0; i < sizeGuideLinks.length; i++) {
            sizeGuideLinks[i].addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var guide = 'pants';
                var url = window.location.href.toLowerCase();
                var title = document.title.toLowerCase();
                if (url.includes('jacket') || url.includes('shirt') || title.includes('jacket') || title.includes('shirt')) {
                    guide = 'jackets';
                }
                console.log('Size guide link clicked:', guide);
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
</script>

<?php wp_footer(); ?>

</body>
</html>
