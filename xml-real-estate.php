<?php
/*
Plugin Name: XML Real Estate Info
Plugin URI:  https://yourwebsite.com/xml-real-estate
Description: This plugin displays real estate information from an XML feed.
Version:     1.0
Author:      Maciej Wlasniak
Author URI:  https://yourwebsite.com
*/

// Enqueue styles for the frontend
function xml_real_estate_enqueue_styles() {
    wp_enqueue_style('xml-real-estate-css', plugins_url('css/style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'xml_real_estate_enqueue_styles');

// Register settings for custom CSS
function xml_real_estate_register_settings() {
    add_option('xml_real_estate_custom_css', ''); // Default value for custom CSS
    register_setting('xmlRealEstateOptions', 'xml_real_estate_custom_css');
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
            <p><input type="submit" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
        </form>
    </div>
<?php
}

function getRealEstateTable() {
    if (false === ($output = get_transient('realestate_table'))) {
        $url = 'https://demo1.voxdeveloper.com/webservice/realestatestatuslist/api_key/ab33c2fb8240c8c7ca015d924713b5d234b7778e/investment_id/15';
        $xml = simplexml_load_file($url) or die("Error: Cannot create object");
        $output = '<table><tr><th>ID</th><th>Status</th></tr>';
        foreach ($xml->realestate as $realestate) {
            $output .= '<tr><td>' . $realestate->id . '</td><td>' . $realestate->status_name . '</td></tr>';
        }
        $output .= '</table>';
        set_transient('realestate_table', $output, HOUR_IN_SECONDS);
    }
    return $output;
}

add_shortcode('realestate_table', 'getRealEstateTable');
?>
