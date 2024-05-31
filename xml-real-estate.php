<?php
/*
Plugin Name: XML Real Estate Info
Plugin URI:  https://at-inwest.pl
Description: This plugin displays real estate information from an XML feed.
Version:     1.0
Relased on: 31.05.2024
Author:      Maciej Wlasniak
Author URI:  https://at-inwest.pl
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
    register_setting('xmlRealEstateOptions', 'xml_real_estate_custom_css');
    register_setting('xmlRealEstateOptions', 'xml_real_estate_xml_url');

    add_option('xml_real_estate_column_order', ''); // Default value is empty
    register_setting('xmlRealEstateOptions', 'xml_real_estate_column_order');
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

            <p><input type="submit" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
        </form>

        <!-- Refresh Data Form with Nonce -->
        <form method="post">
            <?php wp_nonce_field('refresh_data_nonce', 'refresh_data_field'); ?>
            <input type="hidden" name="action" value="refresh_data">
            <p><input type="submit" value="Refresh Data" class="button button-primary"></p>
        </form>
        <h3> To add table to website place shortcode: [realestate_table]</h3>
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

        $status_id_map = [
            '1' => 'Dostępny',
            '2' => 'Rezerwacja',
            '3' => 'Rezerwacja',
            '4' => 'Sprzedane',
            '5' => 'Sprzedane',
            '6' => 'Sprzedane',
            '7' => 'Sprzedane',
            '8' => 'Sprzedane',
            '9' => 'Sprzedane'
        ];

        $output = '<table class="xml-real-estate-table">';
        $output .= '<tr><th>Lokal</th><th>Status</th><th>Powierzchnia uż.</th><th>Cena</th><th>Karta lokalu</th></tr>';

        foreach ($xml->realestate as $realestate) {
            $price = (string)$realestate->price;  // Convert the SimpleXMLElement to string
            $price = floatval($price);  // Then convert the string to a float
            $output .= '<tr>';
            $output .= '<td>' . (!empty($realestate->name) ? $realestate->name : '-') . '</td>';
            $output .= '<td>' . (!empty($realestate->status_id) && array_key_exists((string)$realestate->status_id, $status_id_map) ? $status_id_map[(string)$realestate->status_id] : '-') . '</td>';
            $output .= '<td>' . (!empty($realestate->area) ? $realestate->area . ' m²' : '-') . '</td>';
            $output .= '<td>' . (!empty($realestate->price) ? number_format($price, 0, '.', ' ') . ' zł' : '-') . '</td>';
            $output .= '<td>' . (!empty($realestate->card_link) ? '<a href="' . $realestate->card_link . '">Karta lokalu ' . htmlspecialchars($realestate->name) . '</a>' : '-') . '</td>';
            $output .= '</tr>';
        }

        $output .= '</table>';
        set_transient('realestate_table', $output, HOUR_IN_SECONDS);
    }
    return $output;
}





add_shortcode('realestate_table', 'getRealEstateTable');
?>
