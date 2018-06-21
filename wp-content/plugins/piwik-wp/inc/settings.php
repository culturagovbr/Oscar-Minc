<?php
/**
 * Class for the settings page
 * 
 */

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) )
	die();

if( class_exists( 'PWP_Settings' ) ) {
	$pwp_setting = new PWP_Settings();
}

class PWP_Settings {

    private $options; // holds the values to be used in the fields callbacks

    public function __construct() {

      	// only in admin mode
    	if( is_admin() ) {    
    		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
    		add_action( 'admin_init', array( $this, 'register_settings' ) );
    	}

    }

    /**
     * Add options page
     * 
     */
    public function add_plugin_page() {

    	add_options_page(
    		__( 'Piwik for WordPress', 'pwp_textdomain' ), 
    		__( 'Piwik', 'pwp_textdomain' ), 
    		'manage_options',
    		PWP_SLUG,
    		array( $this, 'create_admin_page' )
    	);

    }

    public function create_admin_page() {
    	if ( ! current_user_can( 'manage_options' ) ) {
    	    return;
    	} ?>
    	<div class="wrap">
    	    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    	    <form action="options.php" method="post">
    	        <?php
    	        // output security fields for the registered setting
    	        settings_fields( 'pwp' );
    	        // output setting sections and their fields
    	        do_settings_sections( 'pwp' );
    	        // output save settings button
    	        submit_button( __( 'Save Settings', 'pwp_textdomain' ) );
    	        ?>
    	    </form>
    	</div>
    	<?php
    }

    /**
     * Register and add settings
     * 
     */
    public function register_settings(){
    	register_setting( 'pwp', 'pwp_options' );

        // General settings section
    	add_settings_section(
            'general_setting_section',
            __( 'General settings', 'pwp_textdomain' ),
            '',
            'pwp'
        ); 

        add_settings_field(
            'pwp_script',
            __( 'Piwik site ID: ', 'pwp_textdomain' ),
            array( $this, 'pwp_script_callback' ),
            'pwp',
            'general_setting_section',
            [
                'label_for' => 'pwp_script',
                'class' => 'form-field',
            ]
        );

        register_setting(
          	'pwp',
          	'pwp_options',
          	array( $this, 'input_validate_sanitize' )
        );

    }

    /**
     * Sanitize settings fields
     * 
     */
    public function input_validate_sanitize( $input ) {
    	$output = array();

    	if( isset( $input['pwp_script'] ) ){
    		// $output['pwp_script'] = stripslashes( wp_filter_post_kses( addslashes( $input['pwp_script'] ) ) );
    		$output['pwp_script'] = $input['pwp_script'];
    	}
    	return $output;
    }

    /**
     * Input HTML
     * 
     */
    function pwp_script_callback( $args ) {
        $options = get_option( 'pwp_options' ); ?>
        <input id="<?php echo esc_attr( $args['label_for'] ); ?>" name="pwp_options[<?php echo esc_attr( $args['label_for'] ); ?>]" type="number" value="<?php echo $options['pwp_script']; ?>">
	    <p class="description">
	        <?php echo __( 'Define site Piwik unique identification.', 'pwp_textdomain' ); ?>
	    </p>
        <?php
    }

}

?>