<?php
/**
 * Flexi Pages class
 *
 * @package: Flexi Pages Widget
 * @since: 1.7
 */

class Flexi_Pages {

	private $args = array();

	private $pages = array();

	/** Constructor **/
	public function __construct( $args = array() ) {
		$this->accept_args( $args );

		$this->pages = $this->get_pages( $this->args );

		return $this->pages;
	}

	private function default_args() {
		return array(
			'sort_column'            => 'post_title', 
			'sort_order'             => 'ASC', 
			'exclude'                => '',
			'include'                => '',
			'child_of'               => 0,
			'parent'                 => -1, 
			'show_subpages'          => 2, 
			'hierarchy'              => 1,
			'depth'                  => 0, 
			'show_home'              => '',
			'show_date'              => 0,
			'date_format'            => '',
		);
	}

	private function accept_args( $args ) {

		$default_args = $this->default_args();

		$args = array_merge( $default_args, $args );

		if( ! in_array( $args['sort_column'], array( 'post_title', 'menu_order', 'post_date', 'post_modified', 'ID', 'post_author', 'post_name' ) ) ) {
			$args['sort_column'] = $default_args['sort_column'];
		}

		$args['sort_order'] = strtolower( $args['sort_order'] );
		if( 'asc' != $args['sort_order'] && 'desc' != $args['sort_order'] ) {
			$args['sort_order'] = $default_args['sort_order'];
		}

		if($args['include'] && $args['hierarchy']) {
			$inc_array = explode(',', $args['include']);
			if($args['exclude']) $exc_array = explode(',', $args['exclude']); else $exc_array = array();
			$page_ids = $this->pageids();
			foreach($page_ids as $page_id) {
				if(!in_array($page_id, $inc_array) && !in_array($page_id, $exc_array))
					$exc_array[] = $page_id;
			}
			$args['exclude'] = implode(',', $exc_array);
			$args['include'] = '';		
		}

		if( !is_numeric( $args['child_of'] ) ) {
			$args['child_of'] = $default_args['child_of'];
		}

		if($args['show_subpages'] == 0)
			$args['depth'] = 1;

		$this->args = $args;
	}


	public function get_list() {
		return $this->list_items( $this->pages );
	}

	public function get_dropdown() {
		$dropdown = "<form action=\"". get_bloginfo('url') ."\" method=\"get\">\n<select name=\"page_id\" id=\"page_id\" onchange=\"top.location.href='".get_bloginfo('url')."?page_id='+this.value\">";
		$dropdown .= $this->dropdown_items($this->pages);
		$dropdown .= "</select><noscript><input type=\"submit\" name=\"submit\" value=\"".__('Go', 'flexipages')."\" /></noscript></form>";
		return $dropdown;
	}

	private function list_items( $pages, $level = 0 ) {
		if(!$pages)
			return;
		
		$list_items = "";
		
		foreach($pages as $page) {
			
			$date = "";			
			if(isset($page['date']) && $page['date']) $date = " ".$page['date'];
			
			$list_items .= str_repeat("\t", $level+1).'<li class="'.$page['class'].'"><a href="'.$page['link'].'" title="'.$page['title'].'">'.$page['title'].'</a>'.$date;
			if($page['children'])
				$list_items .= $this->list_items($page['children'], $level+1);
			$list_items.= "</li>\n";
		}
		if($list_items) {
			$ul_class = $level? ' class="children"': "";
			$list_items = str_repeat("\t", $level)."<ul{$ul_class}>\n{$list_items}".str_repeat("\t", $level)."</ul>";
		}
		return $list_items;
	}

	private function dropdown_items( $pages, $level = 0 ) {
		if( ! $pages)
			return;

		$dropdown_items = "";
		$depth = 0;
		
		foreach($pages as $page) {
			$date = "";
			if(isset($page['date']) && $page['date']) $date = " ".$page['date'];
			if(is_page($page['ID'])) $selected = ' selected="selected"';
			else $selected = '';
			$dropdown_items .= str_repeat("\t", $depth+1).'<option class="level-'.$level.'" value="'.$page['ID'].'"'.$selected.'>'.str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $level).$page['title'].$date.'</option>'."\n";
			if($page['children'])
				$dropdown_items .= $this->dropdown_items($page['children'], $level+1);
		}
		return $dropdown_items;
	}

	private function get_pages( $args = array(), $level = 1 ) {
		$page_array = array();

		if( isset( $args['show_home'] ) && $args['show_home'] ) {
			$class = "home_page";
			$class .= is_home()?" current_page_item":"";			
			$page_array[] = array(
				'ID'       => 'home', 
				'title'    => $args['show_home'], 
				'link'     => get_bloginfo('url'), 
				'children' => array(),
				'class'    => $class,
			);
		}	
			
		if($args['show_subpages'] == -2) $args['show_subpages'] = 2;
		if($args['show_subpages'] == -3) $args['show_subpages'] = 3;
		if($args['show_subpages'] == 0) $args['depth'] = 1;

			
		if(isset($args['hierarchy']) && ($args['hierarchy'] == '0' || $args['hierarchy'] == 'off'))
			$args['depth'] = -1;

		$args['parent'] = ($args['depth'] == -1)?"-1":$args['child_of'];		
		
		$pages = get_pages( array(
			'child_of' => $args['child_of'],
			'parent' => $args['parent'],
			'exclude' => $args['exclude'],
			'include' => $args['include'],
			'sort_column' => $args['sort_column'],
			'sort_order' => $args['sort_order'],
			) );
		

		$currpage_hierarchy = $this->get_currpage_hierarchy();
		
		
		if( $args['show_date'] && ( !$args['date_format'] || 'default' == $args['date_format'] ) )
			$args['date_format'] = get_option('date_format');
		
		if($pages) {
			foreach($pages as $page) {
				if(
					$args['show_subpages'] == 3 
					&& !in_array($page->ID, $currpage_hierarchy) 
					&& ( !isset($currpage_hierarchy[0]) || $page->post_parent != $currpage_hierarchy[0] )
					&& ( !isset($currpage_hierarchy[1]) || $page->post_parent != $currpage_hierarchy[1] )
					&& $page->post_parent != 0
					)
					continue;
				
					
				$children = array();

				if( !($args['depth'] == -1 || $args['depth'] == $level)  &&
					!($args['show_subpages'] == 2 && !in_array($page->ID, $currpage_hierarchy)) &&
					!$args['include']) {

					$children = $this->get_pages(
						array(
							'child_of' => $page->ID,
							'parent' => $page->ID,
							'sort_column' => $args['sort_column'],
							'sort_order' => $args['sort_order'],
							'exclude' => $args['exclude'],
							'include' => $args['include'],
							'show_subpages' => $args['show_subpages'],
							'depth' => $args['depth'],
							'show_date' => $args['show_date'],
							'date_format' => $args['date_format'],
							),
						$level+1
						);
				}
					
				
				$date = '';
				if($args['show_date']) {
					$x = explode(" ", $page->post_date);
					$y = explode("-", $x[0]);
					$date = date($args['date_format'], mktime(0, 0, 0, $y[1], $y[2], $y[0]));
				}
				$class = "page_item page-item-".$page->ID;
				if(is_page($page->ID))
					$class .= " current_page_item";
				else if(isset($currpage_hierarchy[1]) && $page->ID == $currpage_hierarchy[1])
					$class .= " current_page_ancestor current_page_parent";
				else if(in_array($page->ID, $currpage_hierarchy))
					$class .= " current_page_ancestor";
					
				$page_array[] = array (
					'ID' => $page->ID,
					'title' => $page->post_title,
					'link' => get_page_link($page->ID),
					'date' => $date,
					'children' => $children,
					'class' => $class
				);
			}
		}

		return $page_array;

	}


	private function get_currpage_hierarchy()
	{
		if(is_home() && !is_front_page()) {
			if($curr_page_id = get_option('page_for_posts'))
				$curr_page = &get_post($curr_page_id);
			else return array();
		}
		else if( is_page() ) {
			global $wp_query;
			if($curr_page_id = $wp_query->get_queried_object_id())
				$curr_page = get_post($curr_page_id);
				$curr_page = &$curr_page;
		}
		else
			return array();


		// get parents, grandparents of the current page
		$hierarchy[] = $curr_page->ID;
	
		while($curr_page->post_parent) {
			$curr_page = get_post($curr_page->post_parent);
			$curr_page = &$curr_page;
			$hierarchy[] = $curr_page->ID;
		}
		return $hierarchy;
	}

	private function pageids()
	{	
		global $wpdb;
		$page_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish'" );
		return $page_ids;
	}



}

?>
