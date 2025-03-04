<?php
function fxwp_render_view_option()
{
    // Code to display in Erweiterte Ansicht or for users with erweiterte_ansicht capability
    // Admins
    echo '<style>
        #wp-admin-bar-comments,
        #menu-comments, .hide_simple,#dashboard_activity, #dashboard_right_now, #dashboard_site_health,#dashboard_primary, #contextual-help-link-wrap ,.wp-menu-name>.update-plugins:not(.important),.rm-menu-new.update-plugins
        {
            display:none !important;
        }
        </style>';
    // ...
    if (get_option('fxwp_view_option', 'einfach') !== 'erweitert' && !current_user_can('administrator')) {
        // Code to display in Einfache Ansicht
        // Customers
        echo '<style>
        .hide_simple, .hide_advanced, #menu-plugins, #menu-users, .plugin-title .row-actions.visible, a[href$="plugin-install.php"], 
        #menu-users,
        #menu-appearance a[href="themes.php"].wp-first-item, a[href$="theme-install.php"], #menu-tools
        {
            display:none !important;
        }
        </style>';
    }
}


// only in the backend
if (is_admin()) {
    add_action('admin_head', 'fxwp_render_view_option');
}
