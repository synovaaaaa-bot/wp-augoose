/**
 * Product Tabs functionality
 * Handles tab switching on single product pages
 */
jQuery(document).ready(function($) {
    
    // Tab switching
    $('.tabs-nav li a').on('click', function(e) {
        e.preventDefault();
        
        var $tab = $(this);
        var $parent = $tab.closest('li');
        var targetId = $tab.attr('href');
        
        // Remove active class from all tabs and panels
        $('.tabs-nav li').removeClass('active');
        $('.tab-panel').removeClass('active');
        
        // Add active class to clicked tab
        $parent.addClass('active');
        
        // Show corresponding panel
        $(targetId).addClass('active');
    });
    
    // Make sure first tab is active on page load
    if ($('.tabs-nav li.active').length === 0) {
        $('.tabs-nav li:first').addClass('active');
        $('.tab-panel:first').addClass('active');
    }
});
