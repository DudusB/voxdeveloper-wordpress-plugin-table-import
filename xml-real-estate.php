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
    add_option('xml_real_estate_custom_css', ''); // Default value for custom CSS
    add_option('xml_real_estate_xml_url', ''); // Default value for the XML URL
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
                <p><input type="submit" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
            </form>
            <form method="post">
                <input type="hidden" name="action" value="refresh_data">
                <p><input type="submit" value="Refresh Data" class="button button-primary"></p>
            </form>
        </div>
    <?php
    }
    
// Admin initialization to handle the refresh data action
function xml_real_estate_admin_init() {
    if (isset($_POST['action']) && $_POST['action'] == 'refresh_data') {
        if (current_user_can('manage_options')) {
            delete_transient('realestate_table');
            add_action('admin_notices', function() {
                echo '<div class="updated"><p>Data refreshed successfully.</p></div>';
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

        // Define table headers for all fields
        $output = '<table class="xml-real-estate-table">';
        $output .= '<tr><th>ID</th><th>Investment ID</th><th>Investment Name</th><th>Building</th><th>Name</th><th>Local Number</th>
                    <th>Status ID</th><th>Status Name</th><th>Area</th><th>Floor</th><th>Completion Date</th><th>Asking For Price</th>
                    <th>City</th><th>Date Modified</th><th>Type</th><th>Promotion</th><th>Plan Link</th><th>Sold Status</th></tr>';

        // Parse each real estate entry and add rows
        foreach ($xml->realestate as $realestate) {
            $output .= '<tr>';
            $output .= '<td>' . $realestate->id . '</td>';
            $output .= '<td>' . $realestate->investment_id . '</td>';
            $output .= '<td>' . $realestate->investment_name . '</td>';
            $output .= '<td>' . $realestate->building . '</td>';
            $output .= '<td>' . $realestate->name . '</td>';
            $output .= '<td>' . $realestate->local_number . '</td>';
            $output .= '<td>' . $realestate->status_id . '</td>';
            $output .= '<td>' . $realestate->status_name . '</td>';
            $output .= '<td>' . $realestate->area . '</td>';
            $output .= '<td>' . $realestate->floor . '</td>';
            $output .= '<td>' . $realestate->completion_date . '</td>';
            $output .= '<td>' . ($realestate->ask_for_price == '1' ? 'Yes' : 'No') . '</td>';
            $output .= '<td>' . $realestate->city . '</td>';
            $output .= '<td>' . $realestate->date_modified . '</td>';
            $output .= '<td>' . $realestate->type . '</td>';
            $output .= '<td>' . ($realestate->promotion == '1' ? 'Yes' : 'No') . '</td>';
            $output .= '<td>';
            if (isset($realestate->plan_link)) {
                foreach ($realestate->plan_link as $link) {
                    $output .= '<a href="' . $link . '">' . $link . '</a><br>';
                }
            }
            $output .= '</td>';
            $output .= '<td>' . ($realestate->sold_status == '1' ? 'Sold' : 'Available') . '</td>';
            $output .= '</tr>';
        }

        $output .= '</table>';

        // Cache the generated table for 1 hour
        set_transient('realestate_table', $output, HOUR_IN_SECONDS);
    }

    return $output;
}


add_shortcode('realestate_table', 'getRealEstateTable');
?>
