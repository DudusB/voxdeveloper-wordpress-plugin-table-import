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

$fieldsXml = [ 'name' , 'status_id' , 'area_usable', 'price' , 'card_link'];
$fields_no_status_id = [ 'Lokal' , 'Status', 'Powierzchnia' , 'Cena' , 'Karta lokalu'];
$fieldsXml2 = [
    'id', 'investment_id', 'investment_name', 'stage', 'building', 'name', 'local_number', 'status_id', 'status_name',
    'area', 'area_usable', 'rooms', 'floor', 'administrative_number', 'plot_area', 'completion_date', 'ask_for_price',
    'city', 'price', 'minimal_price_gross', 'promotion', 'promotion_price', 'pricemkw', 'price_net', 'pricemkw_net',
    'promotion_price_net', 'promotion_date_from', 'promotion_date_to', 'date_modified', 'type_id', 'type', 'staircase',
    'description', 'direction', 'balkon', 'balcony_count', 'taras', 'ogrod', 'loggia', 'antresole_area', 'area_attic',
    'card_link', 'plan_link', 'two_levels', 'virtual_walk', 'dont_send_to_www', 'sold_status', 'turnkey_conditon',
    'unit_amount_of_co2_emission', 'energy_class', 'share_of_renewable_energy_sources', 'annual_demand_indicator_for_final_energy',
    'annual_demand_indicator_for_usable_energy', 'annual_demand_indicator_for_non_renewable_primary_energy'
];

$fieldsDescription = [
    'ID', 'Investment ID', 'Investment Name', 'Investment Stage', 'Building', 'Name', 'Local Number', 'Status ID', 'Status Name',
    'Area', 'Usable Area', 'Rooms', 'Floor', 'Administrative Number', 'Plot Area', 'Completion Date', 'Ask for Price',
    'City', 'Total Price Gross', 'Minimal Price Gross', 'Promotion', 'Promotion Price Gross', 'Price per m² Gross', 'Net Price',
    'Price per m² Net', 'Promotion Price Net', 'Promotion Start Date', 'Promotion End Date', 'Date Modified', 'Type ID',
    'Type', 'Staircase', 'Description', 'Direction', 'Balcony Area', 'Balcony Count', 'Terrace Area', 'Garden Area',
    'Loggia Area', 'Mezzanine Area', 'Attic Area', 'Property Card Link', 'Floor Plan Link', 'Two Levels',
    'Virtual Walk Link', 'Do Not Send to WWW', 'Sold Status', 'Turnkey Condition', 'CO2 Emission per Unit',
    'Energy Class', 'Share of Renewable Energy Sources', 'Annual Final Energy Demand', 'Annual Usable Energy Demand',
    'Annual Non-renewable Primary Energy Demand'
];

$fields_no_status_id2 = [
    'id', 'investment_id', 'investment_name', 'stage', 'building', 'name', 'local_number', 'status_name',
    'area', 'area_usable', 'rooms', 'floor', 'administrative_number', 'plot_area', 'completion_date', 'ask_for_price',
    'city', 'price', 'minimal_price_gross', 'promotion', 'promotion_price', 'pricemkw', 'price_net', 'pricemkw_net',
    'promotion_price_net', 'promotion_date_from', 'promotion_date_to', 'date_modified', 'type_id', 'type', 'staircase',
    'description', 'direction', 'balkon', 'balcony_count', 'taras', 'ogrod', 'loggia', 'antresole_area', 'area_attic',
    'card_link', 'plan_link', 'two_levels', 'virtual_walk', 'dont_send_to_www', 'sold_status', 'turnkey_conditon',
    'unit_amount_of_co2_emission', 'energy_class', 'share_of_renewable_energy_sources', 'annual_demand_indicator_for_final_energy',
    'annual_demand_indicator_for_usable_energy', 'annual_demand_indicator_for_non_renewable_primary_energy'
];

// Register settings for custom CSS and XML URL
function xml_real_estate_register_settings() {
    add_option('xml_real_estate_custom_css', '');
    add_option('xml_real_estate_xml_url', '');
    global $fieldsXml;
    $fields = $fieldsXml;
    foreach ($fields as $field) {
        add_option($field, '0');  // Default value is '0' (unchecked)
        register_setting('xmlRealEstateOptions', $field);
    }
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
    global $fieldsXml;
    $url = get_option('xml_real_estate_xml_url', 'default-URL-if-not-set');
    $column_order = get_option('xml_real_estate_column_order');
    $order = array_map('trim', explode(',', $column_order));

    if (false === ($output = get_transient('realestate_table'))) {
        if (empty($url)) {
            return 'Please configure the XML URL in the plugin settings.';
        }
        $xml = simplexml_load_file($url) or die("Error: Cannot create object");

        // Define the status_id mapping array within function scope
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

        // Define table headers for all fields
        $output = '<table class="xml-real-estate-table">';
        $output .= '<tr><th>Lokal</th><th>Status</th><th>Powierzchnia uż.</th><th>Cena</th><th>Karta lokalu</th></tr>';

        // Parse each real estate entry and add rows only for type_id 1 or 16
        foreach ($xml->realestate as $realestate) {
            if ((string)$realestate->type_id === '1' || (string)$realestate->type_id === '16') {  // Check type_id
                $output .= '<tr>';
                $output .= '<td>' . (!empty($realestate->name) ? $realestate->name : '-') . '</td>';
                $output .= '<td>' . (!empty($realestate->status_id) && array_key_exists((string)$realestate->status_id, $status_id_map) ? $status_id_map[(string)$realestate->status_id] : '-') . '</td>';
                $output .= '<td>' . (!empty($realestate->area) ? $realestate->area : '-') . '</td>';
                $output .= '<td>' . (!empty($realestate->price) ? $realestate->price : '-') . '</td>';
                $output .= '<td>' . (!empty($realestate->card_link) ? '<a href="' . $realestate->card_link . '">Karta lokalu ' . htmlspecialchars($realestate->name) . '</a>' : '-') . '</td>';
                $output .= '</tr>';
            }
        }
        $output .= '</table>';
        set_transient('realestate_table', $output, HOUR_IN_SECONDS);
    }
    return $output;
}




add_shortcode('realestate_table', 'getRealEstateTable');
?>
