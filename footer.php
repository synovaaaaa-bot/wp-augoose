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

            <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
                <div class="footer-widgets">
                    <?php dynamic_sidebar( 'footer-1' ); ?>
                </div>
            <?php elseif ( has_nav_menu( 'footer' ) ) : ?>
                <nav class="footer-fallback" aria-label="<?php esc_attr_e( 'Footer menu', 'wp-augoose' ); ?>">
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'footer',
                            'menu_id'        => 'footer-menu',
                            'container'      => false,
                            'fallback_cb'    => false,
                            'depth'          => 1,
                        )
                    );
                    ?>
                </nav>
            <?php endif; ?>
        </div>

        <div class="footer-bottom-bar">
            <div class="container">
                <div class="footer-payments" aria-label="<?php esc_attr_e( 'Payment methods', 'wp-augoose' ); ?>">
                    <div class="footer-payments-title"><?php echo esc_html__( 'Payment Methods', 'wp-augoose' ); ?></div>
                    <div class="footer-payment-icons" aria-hidden="true">
                        <span class="pay-icon">VISA</span>
                        <span class="pay-icon">MC</span>
                        <span class="pay-icon">Apple&nbsp;Pay</span>
                        <span class="pay-icon">G&nbsp;Pay</span>
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
