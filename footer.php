<?php
/**
 * The template for displaying the footer
 *
 * @package WP_Augoose
 */
?>

    <footer id="colophon" class="site-footer">
        <div class="container">
            <div class="footer-logo" aria-label="<?php esc_attr_e( 'Site logo', 'wp-augoose' ); ?>">
                <?php the_custom_logo(); ?>
            </div>

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
                            <li><a href="#"><?php echo esc_html__( 'About us', 'wp-augoose' ); ?></a></li>
                            <li><a href="#"><?php echo esc_html__( 'Terms of service', 'wp-augoose' ); ?></a></li>
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
                            <li><a href="#"><?php echo esc_html__( 'Contact us', 'wp-augoose' ); ?></a></li>
                            <li><a href="#"><?php echo esc_html__( 'FAQ', 'wp-augoose' ); ?></a></li>
                            <li><a href="#"><?php echo esc_html__( 'Return or refunds policy', 'wp-augoose' ); ?></a></li>
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
                            <li><a href="#"><?php echo esc_html__( 'Jacket size and fit guide', 'wp-augoose' ); ?></a></li>
                            <li><a href="#"><?php echo esc_html__( 'Pants size and fit guide', 'wp-augoose' ); ?></a></li>
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
                            <svg viewBox="0 0 64 20" width="64" height="20" aria-hidden="true"><rect width="64" height="20" rx="3" fill="#fff"/><text x="9" y="14" font-size="11" font-weight="700" fill="#1A1F71" font-family="Arial, sans-serif">VISA</text></svg>
                        </span>
                        <span class="pay-icon pay-mc" title="Mastercard">
                            <svg viewBox="0 0 64 20" width="64" height="20" aria-hidden="true"><rect width="64" height="20" rx="3" fill="#fff"/><circle cx="28" cy="10" r="6" fill="#EB001B"/><circle cx="36" cy="10" r="6" fill="#F79E1B" fill-opacity="0.95"/><text x="44" y="13" font-size="7" font-weight="700" fill="#111" font-family="Arial, sans-serif">MC</text></svg>
                        </span>
                        <span class="pay-icon pay-applepay" title="Apple Pay">
                            <svg viewBox="0 0 64 20" width="64" height="20" aria-hidden="true"><rect width="64" height="20" rx="3" fill="#fff"/><text x="8" y="13" font-size="9" font-weight="700" fill="#111" font-family="Arial, sans-serif">Apple Pay</text></svg>
                        </span>
                        <span class="pay-icon pay-gpay" title="Google Pay">
                            <svg viewBox="0 0 64 20" width="64" height="20" aria-hidden="true"><rect width="64" height="20" rx="3" fill="#fff"/><text x="10" y="13" font-size="9" font-weight="700" fill="#111" font-family="Arial, sans-serif">G Pay</text></svg>
                        </span>
                    </div>
                </div>

                <div class="site-info">
                    <p><?php echo esc_html( 'Augoose.id | ' . date( 'Y' ) . ' All Rights Reserved.' ); ?></p>
                </div>
            </div>
        </div>
    </footer>
</div>

<!-- Size Guide Modal -->
<?php if ( is_product() ) : ?>
<div id="size-guide-modal" class="size-guide-modal" style="display: none;">
    <div class="size-guide-modal-overlay"></div>
    <div class="size-guide-modal-content">
        <button class="size-guide-modal-close">&times;</button>
        <h2>SIZE GUIDE</h2>
        <table class="size-chart-full">
            <thead>
                <tr>
                    <th>Size</th>
                    <th>Chest (in)</th>
                    <th>Waist (in)</th>
                    <th>Length (in)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>XXS</td>
                    <td>32-34</td>
                    <td>26-28</td>
                    <td>26</td>
                </tr>
                <tr>
                    <td>XS</td>
                    <td>34-36</td>
                    <td>28-30</td>
                    <td>27</td>
                </tr>
                <tr>
                    <td>S</td>
                    <td>36-38</td>
                    <td>30-32</td>
                    <td>28</td>
                </tr>
                <tr>
                    <td>M</td>
                    <td>39-41</td>
                    <td>33-35</td>
                    <td>29</td>
                </tr>
                <tr>
                    <td>L</td>
                    <td>42-44</td>
                    <td>36-38</td>
                    <td>30</td>
                </tr>
                <tr>
                    <td>XL</td>
                    <td>45-47</td>
                    <td>39-41</td>
                    <td>31</td>
                </tr>
                <tr>
                    <td>2XL</td>
                    <td>48-50</td>
                    <td>42-44</td>
                    <td>32</td>
                </tr>
                <tr>
                    <td>3XL</td>
                    <td>51-53</td>
                    <td>45-47</td>
                    <td>33</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php wp_footer(); ?>

</body>
</html>
