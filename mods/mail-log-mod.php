<?php
add_filter('wp_mail', 'fxwp_log_outgoing_mail');

function fxwp_log_outgoing_mail($args)
{
    // Mask the email for privacy
    $to = $args['to'];
    $at_position = strpos($to, '@');
    if ($at_position !== false) {
        $to = substr_replace($to, '***', 2, $at_position - 2);
    }

    // Prepare the email content
    $email_content = array(
        'to_email' => $to,
        'subject' => $args['subject'],
        'message' => $args['message'],
        'headers' => $args['headers'],
        'attachments' => $args['attachments'],
        'timestamp' => current_time('mysql'),
    );

    // Save to the database
    global $wpdb;
    $table_name = $wpdb->prefix . "email_logs";
    $wpdb->insert($table_name, $email_content);

    echo '<div class="notice notice-success"><p>' . esc_html('Die E-Mail wurde erfolgreich geloggt.') . '</p></div>';

    // don't forget to return the args to ensure the email is sent
    return $args;
}

function fxwp_create_email_log_table()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'email_logs';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        to_email text NOT NULL,
        subject text NOT NULL,
        message longtext NOT NULL,
        headers text NOT NULL,
        attachments text,
        timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// have a corn that deletes email logs older than 30 days
function fxwp_delete_old_email_logs()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "email_logs";
    $wpdb->query("DELETE FROM $table_name WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)");
}

// add cron
//function fxwp_add_cron_interval($schedules)
//{
//    $schedules['fxwp_30_days'] = array(
//        'interval' => 30 * 24 * 60 * 60,
//        'display' => esc_html__('Once every 30 days'),
//    );
//    return $schedules;
//}

function fxwp_schedule_cron()
{
    if (!wp_next_scheduled('fxwp_delete_old_email_logs')) {
        wp_schedule_event(time(), 'daily', 'fxwp_delete_old_email_logs');
    }
}

add_filter('cron_schedules', 'fxwp_add_cron_interval');

