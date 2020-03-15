<?php

/**
 * Plugin Name: BuddyForms Contact the Author
 * Plugin URI: https://themekraft.com/products/contact-the-author/
 * Description: Add a button to contact the author to your post listings and post single pages
 * Version: 1.0.0
 * Author: ThemeKraft
 * Author URI: https://themekraft.com/
 * License: GPLv2 or later
 * Network: false
 * Text Domain: buddyforms-contact-author
 * Domain Path: /languages
 *
 *****************************************************************************
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ****************************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BuddyFormsContactAuthor {

	public static $include_assets = array();
	public static $version = '1.0.0';
	public static $slug = 'buddyforms-contact-author';
	/**
	 * Instance of this class
	 *
	 * @var $instance BuddyFormsFrontendTable
	 */
	protected static $instance = null;

	/**
	 * Initiate the class
	 *
	 * @package buddyforms pods
	 * @since 0.1
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		$this->load_constants();
	}

	/**
	 * Defines constants needed throughout the plugin.
	 *
	 *
	 * @package buddyforms_pods
	 * @since 1.0
	 */
	public function load_constants() {
		if ( ! defined( 'BUDDYFORMS_CONTACT_AUTHOR_PLUGIN_URL' ) ) {
			define( 'BUDDYFORMS_CONTACT_AUTHOR_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
		}
		if ( ! defined( 'BUDDYFORMS_CONTACT_AUTHOR_INSTALL_PATH' ) ) {
			define( 'BUDDYFORMS_CONTACT_AUTHOR_INSTALL_PATH', dirname( __FILE__ ) . '/' );
		}
		if ( ! defined( 'BUDDYFORMS_CONTACT_AUTHOR_INCLUDES_PATH' ) ) {
			define( 'BUDDYFORMS_CONTACT_AUTHOR_INCLUDES_PATH', BUDDYFORMS_CONTACT_AUTHOR_INSTALL_PATH . 'includes/' );
		}
		if ( ! defined( 'BUDDYFORMS_CONTACT_AUTHOR_ASSETS' ) ) {
			define( 'BUDDYFORMS_CONTACT_AUTHOR_ASSETS', BUDDYFORMS_CONTACT_AUTHOR_PLUGIN_URL . 'assets/' );
		}
	}

	public static function load_plugins_dependency() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	public static function is_buddy_form_active() {
		self::load_plugins_dependency();

		return is_plugin_active( 'buddyforms-premium/BuddyForms.php' );
	}


	/**
	 * Include files needed by BuddyForms
	 *
	 * @package buddyforms_pods
	 * @since 1.0
	 */
	public function includes() {
		$freemius = self::get_freemius();
		if ( self::is_buddy_form_active() ) {
			if ( ! empty( $freemius ) && $freemius->is_paying_or_trial() ) {
				require_once BUDDYFORMS_CONTACT_AUTHOR_INCLUDES_PATH . 'form-elements.php';
				require_once BUDDYFORMS_CONTACT_AUTHOR_INCLUDES_PATH . 'functions.php';
				require_once BUDDYFORMS_CONTACT_AUTHOR_INCLUDES_PATH . 'contact-author-mail.php';
				require_once BUDDYFORMS_CONTACT_AUTHOR_INCLUDES_PATH . 'shortcode.php';
			}
		} else {
			add_action( 'admin_notices', array( $this, 'need_buddyforms' ) );
		}
	}

	/**
	 * @return Freemius
	 */
	public static function get_freemius() {
		global $buddyforms_contact_author_fs;

		return $buddyforms_contact_author_fs;
	}

	public function need_buddyforms() {
		self::admin_notice();
	}

	/**
	 * Load the textdomain for the plugin
	 *
	 * @package buddyforms_pods
	 * @since 1.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'buddyforms-contact-author', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public static function error_log( $message ) {
		if ( ! empty( $message ) ) {
			error_log( self::getSlug() . ' -- ' . $message );
		}
	}

	/**
	 * @return string
	 */
	public static function getNeedAssets() {
		if ( empty( self::$include_assets ) ) {
			return false;
		}

		return in_array( true, self::$include_assets, true );
	}

	/**
	 * @param string $include_assets
	 * @param string $form_slug
	 */
	public static function setNeedAssets( $include_assets, $form_slug ) {
		self::$include_assets[ $form_slug ] = $include_assets;
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	static function getVersion() {
		return self::$version;
	}

	/**
	 * Get plugins slug
	 *
	 * @return string
	 */
	static function getSlug() {
		return self::$slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function admin_notice( $html = '' ) {
		if ( empty( $html ) ) {
			$html = '<b>Oops...</b> BuddyForms Contact the Author cannot run without <a target="_blank" href="https://themekraft.com/buddyforms/">BuddyForms</a>.';
		}
		?>
		<style>
			.buddyforms-notice label.buddyforms-title {
				background: rgba(0, 0, 0, 0.3);
				color: #fff;
				padding: 2px 10px;
				position: absolute;
				top: 100%;
				bottom: auto;
				right: auto;
				-moz-border-radius: 0 0 3px 3px;
				-webkit-border-radius: 0 0 3px 3px;
				border-radius: 0 0 3px 3px;
				left: 10px;
				font-size: 12px;
				font-weight: bold;
				cursor: auto;
			}

			.buddyforms-notice .buddyforms-notice-body {
				margin: .5em 0;
				padding: 2px;
			}

			.buddyforms-notice.buddyforms-title {
				margin-bottom: 30px !important;
			}

			.buddyforms-notice {
				position: relative;
			}
		</style>
		<div class="error buddyforms-notice buddyforms-title">
			<label class="buddyforms-title">BuddyForms Contact the Author</label>
			<div class="buddyforms-notice-body">
				<?php echo $html; ?>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'buddyforms_contact_author_fs' ) ) {
	// Create a helper function for easy SDK access.
	function buddyforms_contact_author_fs() {
		global $buddyforms_contact_author_fs;

		if ( ! isset( $buddyforms_contact_author_fs ) ) {
			// Include Freemius SDK.
			if ( file_exists( dirname( dirname( __FILE__ ) ) . '/buddyforms/includes/resources/freemius/start.php' ) ) {
				// Try to load SDK from parent plugin folder.
				require_once dirname( dirname( __FILE__ ) ) . '/buddyforms/includes/resources/freemius/start.php';
			} else if ( file_exists( dirname( dirname( __FILE__ ) ) . '/buddyforms-premium/includes/resources/freemius/start.php' ) ) {
				// Try to load SDK from premium parent plugin folder.
				require_once dirname( dirname( __FILE__ ) ) . '/buddyforms-premium/includes/resources/freemius/start.php';
			}

			try {
				$buddyforms_contact_author_fs = fs_dynamic_init( array(
					'id'               => '5721',
					'slug'             => 'buddyforms-contact-author',
					'type'             => 'plugin',
					'public_key'       => 'pk_da0e77a7b45511c1564b13ea3a12d',
					'is_premium'       => true,
					'is_premium_only'  => true,
					'has_paid_plans'   => true,
					'is_org_compliant' => false,
					'trial'            => array(
						'days'               => 14,
						'is_require_payment' => false,
					),
					'parent'           => array(
						'id'         => '391',
						'slug'       => 'buddyforms',
						'public_key' => 'pk_dea3d8c1c831caf06cfea10c7114c',
						'name'       => 'BuddyForms',
					),
					'menu'             => array(
						'first-path' => 'plugins.php',
						'support'    => false,
					),
					// Set the SDK to work in a sandbox mode (for development & testing).
					// IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
					'secret_key'       => 'sk_6R-TDRD<nF5dK?ai5(qoGEhb0x~Io',
				) );
			} catch ( Freemius_Exception $e ) {
				return false;
			}
		}

		return $buddyforms_contact_author_fs;
	}
}

function buddyforms_contact_author_fs_is_parent_active_and_loaded() {
	// Check if the parent's init SDK method exists.
	return function_exists( 'buddyforms_core_fs' );
}

function buddyforms_contact_author_fs_is_parent_active() {
	$active_plugins = get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$active_plugins         = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
	}

	foreach ( $active_plugins as $basename ) {
		if ( 0 === strpos( $basename, 'buddyforms/' ) ||
		     0 === strpos( $basename, 'buddyforms-premium/' )
		) {
			return true;
		}
	}

	return false;
}

function buddyforms_contact_author_need_buddyforms() {
	BuddyFormsContactAuthor::admin_notice();
}

function buddyforms_contact_author_fs_init() {
	if ( buddyforms_contact_author_fs_is_parent_active_and_loaded() ) {
		// Init Freemius.
		buddyforms_contact_author_fs();
		// Signal that the add-on's SDK was initiated.
		do_action( 'buddyforms_contact_author_fs_loaded' );
		// Parent is active, add your init code here.
		$GLOBALS['BuddyFormsContactAuthor'] = BuddyFormsContactAuthor::get_instance();
	} else {
		// Parent is inactive, add your error handling here.
		add_action( 'admin_notices', 'buddyforms_contact_author_need_buddyforms' );
	}
}

if ( buddyforms_contact_author_fs_is_parent_active_and_loaded() ) {
	// If parent already included, init add-on.
	buddyforms_contact_author_fs_init();
} else if ( buddyforms_contact_author_fs_is_parent_active() ) {
	// Init add-on only after the parent is loaded.
	add_action( 'buddyforms_core_fs_loaded', 'buddyforms_contact_author_fs_init' );
} else {
	// Even though the parent is not activated, execute add-on for activation / uninstall hooks.
	buddyforms_contact_author_fs_init();
}



