<?php
/**
 * Flexi Pages Widget class
 *
 * @package: Flexi Pages Widget
 * @since: 1.7
 */

class Flexi_Pages_Widget extends WP_Widget {

	/**
	 * Constructor. Sets up the widget name, description, etc.
	 */
	function __construct() {
		parent::__construct(
			'flexipages', // Base ID
			__('Flexi Pages Widget', 'flexipages'), // Name
			array( 'classname' => 'widget_pages widget_flexipages flexipages_widget', 'description' => __('A highly configurable widget to list pages and sub-pages.', 'flexipages'), ), // Args
			array( 'width' => '400' )
			);
	}

	/**
	 * Register the widget. Should be hooked to 'widgets_init'.
	 */
	public static function register() {
		register_widget( get_class() );
	}

	private function default_widget_options() {
		$default_widget_options = array(
			'title' => __('Pages', 'flexipages'), 
			'sort_column' => 'menu_order', 
			'sort_order' => 'ASC', 
			'exinclude_values' => '',
			'exinclude' => 'exclude', 
			'hierarchy' => 'on', 
			'depth' => 0, 
			'show_subpages_check' => 'on', 
			'show_subpages' => 2, 
			'show_home_check' => 'on',
			'show_home' => __('Home', 'flexipages'), 
			'show_date' => '',
			'date_format' => 'default',
			'dropdown' => '',
			);
		return $default_widget_options;
	}

	/**
	 * Front end output
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		if( $instance ) {
			$options = $instance;
		}
		else {  // Default options
			$options = $this->default_widget_options();
		}

		$flexi_pages_args = array(
			'sort_column' => $options['sort_column'],
			'sort_order' => $options['sort_order'],
			'show_date' => $options['show_date'],
			'date_format' => $options['date_format'],
			);

		if( $options['exinclude_values'] ) {
			if( 'include' == $options['exinclude'] ) {
				$flexi_pages_args['include'] = $options['exinclude_values'];
			}
			else {
				$flexi_pages_args['exclude'] = $options['exinclude_values'];
			}
		}

		if( $options['show_subpages_check'] ) {
			if( 
				2 == intval( $options['show_subpages'] )
				|| -2 == intval( $options['show_subpages'] )
				|| -2 == intval( $options['depth'] )
				) {
				$flexi_pages_args['show_subpages'] = 2;
			}
			else if( 
				3 == intval( $options['show_subpages'] )
				|| -3 == intval( $options['show_subpages'] )
				|| -3 == intval( $options['depth'] )
				) {
				$flexi_pages_args['show_subpages'] = 3;
			}
			else {
				$flexi_pages_args['show_subpages'] = 1;
			}
		}
		else {
			$flexi_pages_args['show_subpages'] = 0;
		}

		if( $options['hierarchy'] ) {
			$flexi_pages_args['hierarchy'] = 1;
			$flexi_pages_args['depth'] = intval( $options['depth'] );
		}
		else {
			$flexi_pages_args['hierarchy'] = 0;
			$flexi_pages_args['depth'] = 0;
		}

		if( $options['show_home_check'] ) {
			$flexi_pages_args['show_home'] = $options['show_home']?$options['show_home']:__('Home', 'flexipages');
		}		

		// To-do frame the options to be passed to the Flexi_Pages constructor

		// echo "<pre>"; print_r($flexi_pages_args); echo "</pre>";

		$flexipages = new Flexi_Pages($flexi_pages_args);
		if( $options['dropdown'] == 'on' ) {
			$flexipages_display = $flexipages->get_dropdown();
		}
		else {
			$flexipages_display = $flexipages->get_list();
		}

		if($flexipages_display) {
			extract( $args );
			echo $before_widget;
			if($options['title']) echo $before_title . apply_filters('the_title', $options['title']) . $after_title . "\n";
			echo $flexipages_display;
			echo $after_widget;
		}

	}


	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$options = $this->default_widget_options();

		if( $instance ) {
			$options = array_merge( $options, $instance );
		}

		$title = esc_attr($options['title']);

		$sort_column_options = array(
			'post_title' => array( 'name' => __('Page title', 'flexipages'), 'select' => false ),
			'menu_order' => array( 'name' => __('Menu order', 'flexipages'), 'select' => false ),
			'post_date' => array( 'name' => __('Date created', 'flexipages'), 'select' => false ),
			'post_modified' => array( 'name' => __('Date modified', 'flexipages'), 'select' => false ),
			'ID' => array( 'name' => __('Page ID', 'flexipages'), 'select' => false, ),
			'post_author' => array( 'name' => __('Page author ID', 'flexipages'), 'select' => false ),
			'post_name' => array( 'name' => __('Page slug', 'flexipages'), 'select' => false ),
			);
		if( $options['sort_column'] )
			$sort_column_options[$options['sort_column']]['select'] = true;

		$sort_order_options = array(
			'ASC' => array( 'name' => __('ASC', 'flexipages'), 'select' => false ),
			'DESC' => array( 'name' => __('DESC', 'flexipages'), 'select' => false ),
			);
		if( $options['sort_order'])
			$sort_order_options[$options['sort_order']]['select'] = true;

		$exinclude_options = array(
			'exclude' => array( 'name' => __('Exclude', 'flexipages'), 'select' => false ),
			'include' => array( 'name' => __('Include', 'flexipages'), 'select' => false),
			);
		$exinclude_options[$options['exinclude']]['select'] = true;

		$show_subpages_check_check = ($options['show_subpages_check'] == 'on')?' checked="checked"':'';
		
		$show_subpages_options = array(
			'1' => array( 'name' => __('Show all sub-pages', 'flexipages'), 'select' => false),
			'2' => array( 'name' => __('Only related sub-pages', 'flexipages'), 'select' => false),
			'3' => array( 'name' => __('Only strictly related sub-pages', 'flexipages'), 'select' => false),
			);
		if( 1 == intval($options['show_subpages']) || !$options['show_subpages'])
			$show_subpages_options['1']['select'] = true;
		else if( -2 == intval($options['depth']) || -2 == intval($options['show_subpages']) || 2 == intval($options['show_subpages']) )
			$show_subpages_options['2']['select'] = true;
		else if( -3 == intval($options['depth']) || -3 == intval($options['show_subpages']) || 3 == intval($options['show_subpages']) )
			$show_subpages_options['3']['select'] = true;

		$show_subpages_display = $show_subpages_check_check?'':' style="display:none;"';
		
		$hierarchy_check = ($options['hierarchy'] == 'on')?' checked="checked"':'';
		
		$depth_options = array (
			'2' => array( 'name' => sprintf( __('%d levels deep', 'flexipages'), 2 ),'select' => false ),
			'3' => array( 'name' => sprintf( __('%d levels deep', 'flexipages'), 3 ),'select' => false ),
			'4' => array( 'name' => sprintf( __('%d levels deep', 'flexipages'), 4 ),'select' => false ),
			'5' => array( 'name' => sprintf( __('%d levels deep', 'flexipages'), 5 ),'select' => false ),
			'0' => array( 'name' => __('Unlimited depth', 'flexipages'), 'select' => false ),
			);
		if(in_array(intval($options['depth']), array(0, 2, 3, 4, 5)))
			$depth_options[$options['depth']]['select'] = true;
		else
			$depth_options[0]['select'] = true;
		
		$depth_display = $hierarchy_check?'':' style="display:none;"';
		
		$show_home_check_check = ((isset($options['home_link']) && $options['home_link']) || $options['show_home_check'] == 'on')?' checked="checked"':'';
		$show_home_display = $show_home_check_check?'':' style="display:none;"';
		$show_home = isset($options['home_link'])?esc_attr($options['home_link']):esc_attr($options['show_home']);
		if( !$show_home ) $show_home = __('Home', 'flexipages');
		$show_date_check = ($options['show_date'] == 'on')?' checked="checked"':'';
		$date_format_display = $show_date_check?'':' style="display:none;"';
		$date_format_options = array(
			'default' => array( 'name' => __('Choose Format', 'flexipages'), 'select' => false ),
			'j F Y' => array( 'name' => 'j F Y', 'select' => false ),
			'F j, Y' => array( 'name' => 'F j, Y', 'select' => false ),
			'Y/m/d' => array( 'name' => 'Y/m/d', 'select' => false ),
			'd/m/Y' => array( 'name' => 'd/m/Y', 'select' => false ),
			'm/d/Y' => array( 'name' => 'm/d/Y', 'select' => false ),
			);
		if( !$options['date_format'] ) {
			$date_format_options['default']['select'] = true;
		} else {
			$date_format_options[$options['date_format']]['select'] = true;
		}
		$dropdown_check = ($options['dropdown'] == 'on')?' checked="checked"':'';


				?>
		<table style="border-collapse: collapse; width: 100%; margin: 5px 0;">
			<tr>
				<td>
					<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'flexipages'); ?></label></p><p>
				</td>
				<td style="padding-left: 20px;">
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
				</td>
			</tr>
			
			<tr>
				<td valign="top">
					<p><label for="<?php echo $this->get_field_id('sort_column'); ?>"><?php _e('Sort by', 'flexipages'); ?></label></p>
				</td>
				<td style="padding-left: 20px;">
					<select class="widefat" style="display:inline;width:auto;" name="<?php echo $this->get_field_name('sort_column'); ?>" id="<?php echo $this->get_field_id('sort_column'); ?>">
						<?php $this->print_select_options( $sort_column_options ); ?>
					</select>
					<select class="widefat" style="display:inline;width:auto;" name="<?php echo $this->get_field_name('sort_order'); ?>" id="<?php echo $this->get_field_id('sort_order'); ?>">
						<?php $this->print_select_options( $sort_order_options ); ?>
					</select>
				</td>
			</tr>
			<tr>		
				<td valign="top"><p>
					<select class="widefat" style="display:inline;width:auto;" name="<?php echo $this->get_field_name('exinclude'); ?>" id="<?php echo $this->get_field_id('exinclude'); ?>">
						<?php $this->print_select_options( $exinclude_options ); ?>
					</select>
					<?php _e('pages', 'flexipages'); ?>
				</p></td>
				<td style="padding-left: 20px;"><p>
					<select name="<?php echo $this->get_field_name('exinclude_values'); ?>[]" id="<?php echo $this->get_field_id('exinclude_values'); ?>" class="widefat" style="height:auto;max-height:6em" multiple="multiple" size="4">
						<?php
							$this->exinclude_options(
								$options['sort_column'],
								$options['sort_order'],
								explode(',', $options['exinclude_values']),
								0,
								0 );
						 ?>
					</select>
					<br/><small class="setting-description">
						<?php _e('use &lt;Ctrl&gt; key to select multiple pages', 'flexipages'); ?>
					</small>
				</p></td>
			</tr>
			<tr>
				<td><p>
					<label for="<?php echo $this->get_field_id('show_subpages_check'); ?>">
						<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_subpages_check'); ?>" name="<?php echo $this->get_field_name('show_subpages_check'); ?>" onchange="if(this.checked) { getElementById('<?php echo $this->get_field_id('show_subpages'); ?>').style.display='block'; } else { getElementById('<?php echo $this->get_field_id('show_subpages'); ?>').style.display='none'; }"<?php echo $show_subpages_check_check; ?> /> 
						<?php _e('Show sub-pages', 'flexipages'); ?>
					</label>
				</p></td>
				<td style="padding-left: 20px;">
					<select<?php echo $show_subpages_display; ?> class="widefat" id="<?php echo $this->get_field_id('show_subpages'); ?>" name="<?php echo $this->get_field_name('show_subpages'); ?>">
						<?php $this->print_select_options( $show_subpages_options ); ?>
					</select>
				</td>
			</tr>	
			<tr>
				<td><p>
					<label for="<?php echo $this->get_field_id('hierarchy'); ?>">
						<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchy'); ?>" name="<?php echo $this->get_field_name('hierarchy'); ?>" onchange="if(this.checked) { getElementById('<?php echo $this->get_field_id('depth'); ?>').style.display='block'; } else { getElementById('<?php echo $this->get_field_id('depth'); ?>').style.display='none'; }"<?php echo $hierarchy_check; ?> /> 
						<?php _e('Show hierarchy', 'flexipages'); ?>
					</label>
				</p></td>
				<td style="padding-left: 20px;">
					<select<?php echo $depth_display; ?> class="widefat" id="<?php echo $this->get_field_id('depth'); ?>" name="<?php echo $this->get_field_name('depth'); ?>">
						<?php $this->print_select_options( $depth_options ); ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><p>
					<label for="<?php echo $this->get_field_id('show_home_check'); ?>">
						<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_home_check'); ?>" name="<?php echo $this->get_field_name('show_home_check'); ?>" onchange="if(this.checked) { getElementById('<?php echo $this->get_field_id('show_home'); ?>').style.display='block'; } else { getElementById('<?php echo $this->get_field_id('show_home'); ?>').style.display='none'; }"<?php echo $show_home_check_check; ?> /> 
						<?php _e('Show home page', 'flexipages'); ?>
					</label>
				</p></td>
				<td style="padding-left: 20px;">
					<input<?php echo $show_home_display; ?> class="widefat" type="text" name="<?php echo $this->get_field_name('show_home'); ?>" id ="<?php echo $this->get_field_id('show_home'); ?>" value="<?php echo htmlspecialchars($show_home, ENT_QUOTES); ?>" />
				</td>
			</tr>
			<tr>
				<td><p>
					<label for="<?php echo $this->get_field_id('show_date'); ?>">
						<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" onchange="if(this.checked) { getElementById('<?php echo $this->get_field_id('date_format'); ?>').style.display='block'; } else { getElementById('<?php echo $this->get_field_id('date_format'); ?>').style.display='none'; }"<?php echo $show_date_check; ?> /> <?php _e('Show date', 'flexipages'); ?>
					</label>
				</p></td>
				<td style="padding-left: 20px;">
					<select<?php echo $date_format_display; ?> class="widefat" id="<?php echo $this->get_field_id('date_format'); ?>" name="<?php echo $this->get_field_name('date_format'); ?>" text="Select format">
						<?php $this->print_select_options( $date_format_options ); ?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2"><p>
					<input name="<?php echo $this->get_field_name('dropdown'); ?>" id="<?php echo $this->get_field_id('dropdown'); ?>" type="checkbox"<?php echo $dropdown_check; ?> />
					<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e('Show as dropdown', 'flexipages'); ?></label>
				</p></td>
			</tr>			
		</table>
		<?php

	}


	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$title = strip_tags(stripslashes($new_instance['title']));
		$sort_column = strip_tags(stripslashes($new_instance['sort_column']));
		$sort_order = strip_tags(stripslashes($new_instance['sort_order']));
		$exinclude = strip_tags(stripslashes($new_instance['exinclude']));
		$exinclude_values = $new_instance['exinclude_values']?implode(',', $new_instance['exinclude_values']):'';
		$show_subpages_check = strip_tags(stripslashes($new_instance['show_subpages_check']));
		$show_subpages = strip_tags(stripslashes($new_instance['show_subpages']));
		$hierarchy = strip_tags(stripslashes($new_instance['hierarchy']));
		$depth = strip_tags(stripslashes($new_instance['depth']));
		$show_home_check = strip_tags(stripslashes($new_instance['show_home_check']));
		$show_home = strip_tags(stripslashes($new_instance['show_home']));
		$show_date = strip_tags(stripslashes($new_instance['show_date']));
		$date_format = strip_tags(stripslashes($new_instance['date_format']));
		$dropdown = strip_tags(stripslashes($new_instance['dropdown']));
		
		return compact('title', 'sort_column', 'sort_order', 'exinclude', 'exinclude_values', 'show_subpages_check', 'show_subpages', 'hierarchy', 'depth', 'show_home_check', 'show_home', 'show_date', 'date_format', 'dropdown');

			
	}


	private function print_select_options( $options = array() ) {
		foreach($options as $key => $option) {
			echo '<option value="'.$key.'"';
			if( $option['select'] ) echo ' selected="selected"';
			echo '>'.$option['name'].'</option>'."\n";
		}
	}


	private function exinclude_options(
		$sort_column = "menu_order",
		$sort_order = "ASC",
		$selected = array(),
		$parent = 0,
		$level = 0 ) {
		
		global $wpdb;

		$get_pages_args = array( 
			'sort_column' => $sort_column,
			'sort_order' => $sort_order,
			'child_of' => $parent,
			'parent' => $parent,
			);

		$items = get_pages( $get_pages_args );
		
		if ( $items ) {
			foreach ( $items as $item ) {
				$pad = str_repeat( '&nbsp;', $level * 3 );
				if ( in_array($item->ID, $selected))
					$current = ' selected="selected"';
				else
					$current = '';
		
				echo "\n\t<option value='$item->ID'$current>$pad $item->post_title</option>";
				$this->exinclude_options( $sort_column, $sort_order, $selected, $item->ID,  $level +1 );
			}
		} else {
			return false;
		}
	}


	
}

?>
