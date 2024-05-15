<?php
/*
Plugin Name: XML Real Estate Info
Plugin URI:  https://yourwebsite.com/xml-real-estate
Description: This plugin displays real estate information from an XML feed.
Version:     0.3
Author:      Maciej Wlasniak
Author URI:  https://yourwebsite.com
*/

// Enqueue styles for the frontend
function xml_real_estate_enqueue_styles() {
    wp_enqueue_style('xml-real-estate-css', plugins_url('css/style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'xml_real_estate_enqueue_styles');

// Register settings for custom CSS and XML URL
function xml_real_estate_register_settings() {
    add_option('xml_real_estate_custom_css', '');
    add_option('xml_real_estate_xml_url', '');
    $fields = ['id', 'investment_id', 'investment_name', 'building', 'name', 'local_number', 'status_id', 'status_name', 'area', 'floor', 'completion_date', 'ask_for_price', 'city', 'date_modified', 'type', 'promotion', 'plan_link', 'sold_status'];
    foreach ($fields as $field) {
        add_option($field, '0');  // Default value is '0' (unchecked)
        register_setting('xmlRealEstateOptions', $field);
    }
    register_setting('xmlRealEstateOptions', 'xml_real_estate_custom_css');
    register_setting('xmlRealEstateOptions', 'xml_real_estate_xml_url');
}
add_action('admin_init', 'xml_real_estate_register_settings');


// Admin page for settings
function xml_real_estate_settings_page() {
    add_options_page('XML Real Estate Settings', 'XML Real Estate', 'manage_options', 'xmlrealestate', 'xml_real_estate_options_page');
}
add_action('admin_menu', 'xml_real_estate_settings_page');

// HTML for settings page
function xml_real_estate_options_page() {
    ?>
    <div>
        <h2>XML Real Estate Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('xmlRealEstateOptions'); ?>
            <h3>Custom CSS</h3>
            <textarea name="xml_real_estate_custom_css" style="width:400px;height:150px;"><?php echo get_option('xml_real_estate_custom_css'); ?></textarea>
            <h3>XML URL</h3>
            <input type="text" name="xml_real_estate_xml_url" style="width:400px;" value="<?php echo esc_attr(get_option('xml_real_estate_xml_url')); ?>" />
            <h3>Select Fields to Display</h3>
            <?php
            $fields = ['ID', 'Investment ID', 'Investment Name', 'Building', 'Name', 'Local Number', 'Status ID', 'Status Name', 'Area', 'Floor', 'Completion Date', 'Asking For Price', 'City', 'Date Modified', 'Type', 'Promotion', 'Plan Link', 'Sold Status'];
            foreach ($fields as $field) {
                $field_slug = strtolower(str_replace(' ', '_', $field));
                ?>
                <div>
                    <input type="checkbox" id="<?php echo $field_slug; ?>" name="<?php echo $field_slug; ?>" value="1" <?php checked(1, get_option($field_slug), true); ?>>
                    <label for="<?php echo $field_slug; ?>"><?php echo $field; ?></label>
                </div>
                <?php
            }
            ?>
            <p><input type="submit" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
        </form>
        <!-- Refresh Data Form with Nonce -->
        <form method="post">
            <?php wp_nonce_field('refresh_data_nonce', 'refresh_data_field'); ?>
            <input type="hidden" name="action" value="refresh_data">
            <p><input type="submit" value="Refresh Data" class="button button-primary"></p>
        </form>
    </div>
    <?php
}

    
// Admin initialization to handle the refresh data action
function xml_real_estate_admin_init() {
    if (isset($_POST['action']) && $_POST['action'] == 'refresh_data') {
        check_admin_referer('refresh_data_nonce', 'refresh_data_field');
        if (current_user_can('manage_options')) {
            delete_transient('realestate_table');
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>Data refreshed successfully.</p></div>';
            });
        }
    }
}

add_action('admin_init', 'xml_real_estate_admin_init');


function getRealEstateTable() {
    $url = get_option('xml_real_estate_xml_url', 'default-URL-if-not-set');
    if (false === ($output = get_transient('realestate_table'))) {
        if (empty($url)) {
            return 'Please configure the XML URL in the plugin settings.';
        }
        $xml = simplexml_load_file($url) or die("Error: Cannot create object");
        $output = '<table class="xml-real-estate-table"><tr>';
        $fields = ['id', 'investment_id', 'investment_name', 'building', 'name', 'local_number', 'status_id', 'status_name', 'area', 'floor', 'completion_date', 'ask_for_price', 'city', 'date_modified', 'type', 'promotion', 'plan_link', 'sold_status'];
        foreach ($fields as $field) {
            if (get_option($field)) { // Only add header if option is checked
                $output .= '<th>' . ucwords(str_replace('_', ' ', $field)) . '</th>';
            }
        }
        $output .= '</tr>';

        foreach ($xml->realestate as $realestate) {
            $output .= '<tr>';
            foreach ($fields as $field) {
                if (get_option($field)) { // Only add data if option is checked
                    $output .= '<td>' . (isset($realestate->$field) ? $realestate->$field : '') . '</td>';
                }
            }
            $output .= '</tr>';
        }
        $output .= '</table>';
        set_transient('realestate_table', $output, HOUR_IN_SECONDS);
    }
    return $output;
}



add_shortcode('realestate_table', 'getRealEstateTable');
?>
