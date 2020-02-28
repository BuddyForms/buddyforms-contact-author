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
		if ( self::is_buddy_form_active() ) {
			require_once BUDDYFORMS_CONTACT_AUTHOR_INCLUDES_PATH . 'form-elements.php';
			require_once BUDDYFORMS_CONTACT_AUTHOR_INCLUDES_PATH . 'functions.php';
			require_once BUDDYFORMS_CONTACT_AUTHOR_INCLUDES_PATH . 'contact-author-mail.php';
		} else {
			add_action( 'admin_notices', array( $this, 'need_buddyforms' ) );
		}
	}

	public function need_buddyforms() {
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
				<b>Oops...</b> BuddyForms Contact the Author cannot run without <a target="_blank" href="https://themekraft.com/buddyforms/">BuddyForms</a>.
			</div>
		</div>
		<?php
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
}

$GLOBALS['BuddyFormsContactAuthor'] = BuddyFormsContactAuthor::get_instance();

