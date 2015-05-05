<?php
/**
 * Plugin Name: Flexi Pages Widget
 * Plugin URI: http://srinig.com/wordpress/plugins/flexi-pages/
 * Description: A highly configurable WordPress sidebar widget to list pages and sub-pages. User friendly widget control comes with various options. 
 * Version: 1.7.1
 * Author: Srini G
 * Author URI: http://srinig.com/wordpress
 * Text Domain: flexipages
 * Domain Path: /languages/
 * License: GPL2
 */

/*  Copyright 2007-2015 Srini G (email : srinig.com@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


include_once( 'inc/class-flexi-pages.php' );
include_once( 'inc/class-flexi-pages-widget.php' );

define( 'FLEXIPAGES_VERSION', '1.7.1' );

function flexipages_init()
{
	if( $old_widget_options = get_option( 'flexipages_widget') ) {
		if( get_option( 'widget_flexipages') ) {
			update_option( 'widget_flexipages', $old_widget_options );
		} else {
			add_option( 'widget_flexipages', $old_widget_options );
		}
		delete_option( 'flexipages_widget' );
	}

	$plugin_version_stored = get_option( 'flexipages_version' );
	if( $plugin_version_stored != FLEXIPAGES_VERSION ) {
		add_option( 'flexipages_version', FLEXIPAGES_VERSION );
	}

	if(function_exists('load_plugin_textdomain'))
		load_plugin_textdomain('flexipages', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );



	/**
	 * The flexipages() template function
	 */
	function flexipages( $args = array() ) {

		$options = array();
		if( is_string( $args ) ) {
			$key_value = explode('&', $args);
			foreach($key_value as $value) {
				$x = explode('=', $value);
				$options[$x[0]] = $x[1]; // $options['key'] = 'value';
			}
		}
		else if( is_array( $args) ) {
			$options = $args;
		}


		$flexipages = new Flexi_Pages( $options );
	
		if( isset( $options['dropdown'] ) && $options['dropdown'] ) {
			$display = $flexipages->get_dropdown();
		}
		else {
			$display = $flexipages->get_list();
		}
		
		if( isset( $options['echo'] ) && !$options['echo'] ) {
			return $display;
		}
		else {
			echo $display;
		}

	}

	/** Alias of flexipages() function */
	function flexi_pages( $args = array() ) {
		return flexipages( $args );
	}
	
}

add_action( 'plugins_loaded', 'flexipages_init' );

add_action( 'widgets_init', array('Flexi_Pages_Widget', 'register') );

?>
