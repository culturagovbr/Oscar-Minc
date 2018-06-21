<?php
/*
Plugin Name: Piwik WordPress
Plugin URI: https://github.com/Darciro/Piwik-WordPress
Description: Add a Piwik script to track down your web traffic
Version: 1.0
Author: Ricardo Carvalho
Author URI: https://galdar.com.br
License: GNU GPLv3
*/

if ( !defined( 'WPINC' ) )
	die();

define( 'PWP_SLUG', 'pwp' );
define( 'PWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PWP_PLUGIN_PATH', dirname( __FILE__ ) );

// Include our options page
require_once( 'inc/settings.php' );

class PiwikWP {

	public function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		// Add piwik script to the head element
		add_action( 'wp_head', array( $this, 'piwik_script' ) );

	}

	/**
	 * Load the plugin text domain for translation
	 *
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'pwp_textdomain', false, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/lang/' ); 
	}

	/**
	* Register piwik script to the head of document
	* 
	*/
	public function piwik_script(){
		$options = get_option( 'pwp_options');
        
        if (!empty($options)) {
            if ($options['pwp_script'] != '') { ?>
		<!-- Piwik -->
		<script type="text/javascript">
			var _paq = _paq || [];
			_paq.push(['trackPageView']);
			_paq.push(['enableLinkTracking']);
			(function() {
				var u="//analise.cultura.gov.br/";
				_paq.push(['setTrackerUrl', u+'piwik.php']);
				_paq.push(['setSiteId', <?php echo $options['pwp_script']; ?>]);
				var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
				g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
			})();
		</script>
		<noscript><p><img src="//analise.cultura.gov.br/piwik.php?idsite=<?php echo $options['pwp_script']; ?>" style="border:0;" alt="" /></p></noscript>
		<!-- End Piwik Code -->
		<?php
             }
        }
	}

}

// Instantiate our plugin
$piwi_wp = new PiwikWP();

?>