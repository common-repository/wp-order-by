<?php

class WpobPlugin {
 
	public function __construct() { 
		  
		if ( is_admin() ){
			load_plugin_textdomain('wp-order-by', false, basename( dirname( __FILE__ ) ) . '/lang' );
			//register and enqueue plugin scripts and styles
			add_action( 'admin_init', array( $this, 'register_plugin_styles' ) );
			add_action( 'admin_init', array( $this, 'register_plugin_scripts' ) );
			add_action('admin_enqueue_scripts', array( $this, 'load_admin_scripts'));
			
			//init plugin class
			add_action('admin_menu', array($this, 'wpob_options_panels'));
			add_action( 'admin_init', array($this, 'register_wpob_settings' ));
			add_action( 'admin_init', array($this, 'checkPost'), 9 );
			
			add_action( 'init', array($this,'process_form_vals') );

			//update and overide all settings if update was from general settings page
			if(!empty($_POST['_wpnonce']) && !empty($_POST['_wp_http_referer']) && strpos($_POST['_wp_http_referer'], 'options-general.php?page=wpob-settings-wpob_general') > 0) {
				add_action('admin_menu', array($this, 'update_all_settings'));
			}
		}
		
    }
	
	private $current_post_type = null;
	private $page_hook_suffix = array();	    
	// Default string values
	private $data = array(
		'err_message' => 'Settings did not saved. please provide a valid data',
		'success_message' => 'Settings successfully saved'
	);
	
	private static function get_all_public_post_types_names() {  
		$args = array(
		   'public'   => true
		);
		return get_post_types( $args, 'names' ); 
	}
	
	//prints the panels
	private function view($name, $args) {
		$file = WPOB__PLUGIN_DIR . '/views/'. $name . '.php';
		include( $file );
	}
	
	//messages after save
	private function notice() {
		if( isset($_GET["settings-updated"]) ) {
			if( $_GET["settings-updated"] == 'true' ) echo '<div id="user_message" class="updated"><p><strong>'.$this->data["success_message"].'</strong></p></div>'; 
			else echo '<div id="user_message" class="error"><p><strong>'.$this->data["err_message"].'</strong></p></div>';
		}
	}
	
	private static function get_admin_screen_current_post_type() {
		$screen = get_current_screen();
		$pt = $screen->post_type;
		if($pt=='') {
			if($screen->parent_file == 'edit.php')
				$pt = 'post';
		}
		return $pt;
	}
	
	private static function draw_posts_select_box($type = '', $select_default_text='', $post_ids) {
		//var_dump($post_ids);
		$post_type = WpobPlugin::get_admin_screen_current_post_type();
		$posts = WpobPlugin::get_all_posts_of_current_post_type();
		$selected_default = '';
		if( WpobPlugin::is_no_exclude_ids_of_post_type($post_type, $post_ids) ) $selected_default = ' selected ';
		echo '<select id="posts_select_box" name="wpob-exclude-posts[]" class="posts_select_box" '.$type.'>';
		if( !empty($select_default_text) ) echo '<option value="" '.$selected_default.' >'.$select_default_text.'</option>';
			foreach($posts as $p) { //iterate posts
				$selected = '';
				
				foreach($post_ids as $pid) { //iterate selected posts
					if($p->ID == $pid && !empty( $post_ids[0] ) ) {
						$selected = ' selected ';
					}
				}
				echo '<option value="'.$p->ID.'"'.$selected.'>'.$p->post_title.'</option>';
			}
		echo '</select>';
	}
	
	private static function get_all_posts_of_current_post_type() {
		$post_type = WpobPlugin::get_admin_screen_current_post_type();
		$post_types = $post_type;
		if($post_types == '') $post_types = WpobPlugin::get_all_public_post_types_names();
		$pargs = array( 'posts_per_page'   => 9999, 'orderby' => 'title', 'order' => 'asc', 'post_type' => $post_types );
		
		return get_posts($pargs);
	}
	
	private static function is_no_exclude_ids_of_post_type($post_type, $db_ids ) {
		if( empty( $db_ids[0] ) ) return true; //no exclude ids
		if( empty( $post_type ) ) return false; //general page - no specific post type
		$posts = WpobPlugin::get_all_posts_of_current_post_type();
		foreach($posts as $p) { //iterate found posts
			foreach($db_ids as $pid) { //iterate selected posts
				if($p->ID == $pid ) {
					return false;
				}
			}
		}
		return true;
	}
	
	public function process_form_vals() {
		if(!empty($_POST['_wpnonce']) && !empty($_POST['_wp_http_referer']) ) {
			$cf_arr = array();
			$ptype = !empty($_POST["post_type"]) ? $_POST["post_type"] : '';
			$cf_arr[] = $_POST["wpob-options-".$ptype];
			if(!empty($_POST["wpob-cf-type"])) {
				$cf_arr[] = $_POST["wpob-cf-type"];
				$cf_arr[] = !empty($_POST["wpob_other_posts"]);
				//$cf_arr[] = $_POST["wpob_cf_order"];
				//$_POST["wpob-options-".$ptype] = $cf_arr;
			}
			$cf_arr[] = $_POST["wpob_cf_order"];
			//var_dump($cf_arr);
			$_POST["wpob-options-".$ptype] = $cf_arr;
		}
	}
				
	public function register_plugin_styles() {
			wp_register_style( 'wpob-plugin-stylesheets', WPOB__PLUGIN_URL . '/css/wpob.css' );			
	}
	 
	public function register_plugin_scripts() {
		wp_register_script(  'wpob-plugin-scripts', WPOB__PLUGIN_URL . '/js/wpob.js'  );
	}
	
	// loads admin scripts on plugin admin pages only
	public function load_admin_scripts($hook) {

		wp_enqueue_script('jQuery');
		//Link registered script and styles to a page
		foreach($this->page_hook_suffix as $suff) {
			if( $hook == $suff ) {
				wp_enqueue_script( 'wpob-plugin-scripts' );
				wp_enqueue_style('wpob-plugin-stylesheets');
				return;
			}
		}
    }
 
	//add all submenus
	public function wpob_options_panels(){
		//submenu for general settings page
		$this->page_hook_suffix[] = add_submenu_page( 'options-general.php', 'wpob general settings', 'WP Order By Settings', 'manage_options', 'wpob-settings-wpob_general', array($this,'wpob_draw_general_settings')); 
		
		$post_types = WpobPlugin::get_all_public_post_types_names();
		// add submenu for all public post types
		foreach ( $post_types as $name ) {
			$page_str = ($name=='post') ? 'edit.php' : 'edit.php?post_type='.$name;
			$this->page_hook_suffix[] = add_submenu_page( $page_str, 'wpob '.$name.' settings', 'Order By', 'manage_options', 'wpob-settings-'.$name, array($this,'wpob_draw_settings')); 
		}
	}
	
	//register all panels 
	public function register_wpob_settings() {
		$post_types = WpobPlugin::get_all_public_post_types_names();
		foreach ( $post_types as $name ) {
			$this->register_wpob_settings_for_post_type($name);
		}
	}
	
	public function register_wpob_settings_for_post_type($post_type_name) {

		$plural_pname = !empty( $this->current_post_type->labels->name ) ? $this->current_post_type->labels->name : '';
		register_setting( 'wpob-options-'.$post_type_name, 'wpob-options-'.$post_type_name );
		add_settings_section('wpob_main_'.$post_type_name, '', array($this,'plugin_section_text'), 'wpob-menu-'.$post_type_name);		
		add_settings_field('wpob-fields-'.$post_type_name, __('Choose Order','wp-order-by-plugin'), array($this, 'draw_settings_page'), 'wpob-menu-'.$post_type_name, 'wpob_main_'.$post_type_name, array('post_type' => $post_type_name) );
		
		register_setting( 'wpob-options-'.$post_type_name, 'wpob-exclude-posts',  array( $this, 'merge_excluded' ) );
		add_settings_section('wpob_main_'.$post_type_name, '', array($this,'plugin_section_text'), 'wpob-extra-'.$post_type_name);	
		add_settings_field('wpob-fields-exclude', __('Choose Excluded '.$plural_pname,'wp-order-by-plugin'), array($this, 'draw_settings_page_extra'), 'wpob-extra-'.$post_type_name, 'wpob_main_'.$post_type_name, array('post_type' => $post_type_name) );
	}
	
	public function merge_excluded($exclude) {

		$pt = !empty( $_POST['post_type'] ) ? $_POST['post_type'] : '';
		$db_exclude = get_option('wpob-exclude-posts');
		//var_dump($exclude);
		
		if( !is_array( $db_exclude ) ) $db_exclude = array();
		$tmp_exclude = array();
		foreach ( $db_exclude as $key => $val ) { 
		
			$tmp_post = get_post( $val );	
			$tmp_pt = $tmp_post->post_type;
			
			if($tmp_pt == $pt) {
				$tmp_exclude[] = $db_exclude[$key];
			//var_dump( key ($db_exclude)); 
				//unset($db_exclude[$key]);
				//$db_exclude = array_values($db_exclude);
			}
			
		}
		
		$new_db_exclude = array_diff( $db_exclude, $tmp_exclude );
		if( empty( $exclude[0] ) ) {
			$exclude = array();
		}
		$db_exclude = array_unique( array_merge( $exclude, $new_db_exclude ) );
		
		
		return $db_exclude;
	}
	
	public function plugin_section_text($arg) {
	
	}
	
	//make sure we are on existing post-type and set $this->current_post_type with the post type object
	 public function checkPost() {
		if ( isset($_GET['page']) && substr($_GET['page'], 0, 14) == 'wpob-settings-' &&  $_GET['page'] != 'wpob-settings-wpob_general') {
				$this->current_post_type = get_post_type_object(str_replace( 'wpob-settings-', '', $_GET['page'] ));
				if ( $this->current_post_type == null) {
						wp_die('Invalid post type');
				}
		}
	}

	//update all settings if update was from the general settings page
	public function update_all_settings() {
	 
		$post_types = WpobPlugin::get_all_public_post_types_names();
		$updated = 'true';
		
		if(!empty($_POST['wpob-options-']) && $_POST['wpob-options-'] != get_option( 'wpob-options-' )) {
			$is_update_general = update_option( 'wpob-options-', $_POST['wpob-options-'] ); 
			if(!$is_update_general) $updated = 'false';
		}
		$exclude = $_POST["wpob-exclude-posts"];
		if(empty($exclude)) $exclude = array('');
		if($exclude != get_option( 'wpob-exclude-posts' )) {
			if( empty( $exclude[0] ) ) {
				$exclude_empty = array( $exclude[0] );
				$is_update_general = update_option( 'wpob-exclude-posts', $exclude_empty ); 
			} else {
				$is_update_general = update_option( 'wpob-exclude-posts', $exclude ); 
			}
			if(!$is_update_general) $updated = 'false';
		}
		foreach ( $post_types as $name ) {
			if(!empty($_POST['wpob-options-']) && $_POST['wpob-options-'] != get_option( 'wpob-options-'.$name )) {
				$is_update = update_option( 'wpob-options-'.$name, $_POST['wpob-options-'] );
				if(!$is_update) $updated = 'false';
			}
		}
		wp_redirect( 'options-general.php?page=wpob-settings-wpob_general&settings-updated='.$updated );
		exit;
		
	}
	
	//call the actual drawing function for all settings panel
	public function draw_settings_page($args) {
	 
		$options = get_option('wpob-options-'.$args['post_type']);
		if(!empty($_POST['wpob-options-']) ) $options = $_POST['wpob-options-']; //global settings for all post types
		if($options=='' || !$options) $options = array('datedesc', 'DESC');
		
		if( !is_array($options) ) $options = array( $options ); // support older versions
		if( end($options) != 'ASC' && end($options) != 'DESC' ) { // support older versions
			$options[] = 'DESC';
		}
		if($options[0] == 'abcdesc' || $options[0] == 'abcasc') {
			$options[0] = 'title';
		}
		$args['options'] = $options;
		$this->view('form_elements',$args);
	}
	
	//call the actual drawing function for all settings panel
	public function draw_settings_page_extra($args) {
		$db_exclude = get_option('wpob-exclude-posts');
		$plural_pname = !empty( $this->current_post_type->labels->name ) ? $this->current_post_type->labels->name : '';
		if( !is_array( $db_exclude ) ) $db_exclude = array();
		$args['wpob-exclude-posts'] = $db_exclude;
		$args['wpob-post-type-label'] = $plural_pname;
		if( empty( $args['wpob-post-type-label'] ) ) $args['wpob-post-type-label'] = 'ANY';
		$this->view('form_extra',$args);
	}
	
	//init all panels except general settings
	public function wpob_draw_settings(){
		?>
		<div class="wpob_settings_container">
			<h2>WP Order-by Settings for <?php echo $this->current_post_type->labels->menu_name; ?></h2>
			<?php $this->notice(); ?>
			<p class="description">Choose the way you want <b><?php echo $this->current_post_type->labels->name; ?></b> to be ordered on site</p>
			<form method="post" action="options.php">
			
				<?php wp_nonce_field('wpob-options-nonce'); ?>
				
				<?php settings_fields( 'wpob-options-'.$this->current_post_type->name ); ?>
				<?php do_settings_sections( 'wpob-menu-'.$this->current_post_type->name ); ?>
				<?php do_settings_sections( 'wpob-extra-'.$this->current_post_type->name ); ?>
				
				<?php $pt = (!empty($_GET["post_type"])) ? $_GET["post_type"] : 'post'; ?>
				<input type="hidden" name="post_type" value="<?php echo $pt; ?>" />
				<p>
					<input type="submit" value="<?php echo __('Save Changes','wp-order-by-plugin'); ?>" class="button button-primary" />
				</p>
			
			</form>
		</div>
		<?php
	}
	
	//init general settings panel
	public function wpob_draw_general_settings() {
		?>
		<div class="wpob_settings_container">
			<h2>WP Order-by General Settings</h2>
			<?php $this->notice(); ?>
			<p class="description">Choose global settings for all <b>post</b>, <b>post-types</b> and <b>pages</b> to be ordered on site. (will overrides post/page/custom post-type settings you defined on theirs specific <code>WP order-by</code> settings page)</p>
			<br>			
			
			<h3>
					Choose order
			</h3>
			<form method="post" action="options-general.php?page=wpob-settings-wpob_general">
				<?php wp_nonce_field('wpob-options-nonce'); ?>
				<?php $this->draw_settings_page( array('post_type' => '')); ?>
				<?php $this->draw_settings_page_extra( array('post_type' => '')); ?>
				<p>
					<input type="submit" value="<?php echo __('Save Changes','wp-order-by-plugin'); ?>" class="button button-primary" />
				</p>				
			</form>
		</div>
		<?php
	}

} //end class WpobPlugin

$wpob = new WpobPlugin();

//for order by custom field. get rest of posts, that doesn't have the specific custom type, 
//to be added to the end of the list
function wpob_get_rest_of_posts($clauses) { 
	// change the inner join to a left join, 
	// and change the where so it is applied to the join, not the results of the query
	$clauses['join'] = str_replace('INNER JOIN', 'LEFT JOIN', $clauses['join']).$clauses['where'];
	$clauses['where'] = '';
    return $clauses;
}

add_action( 'pre_get_posts', 'get_ordered_posts' );
// filter the query and return the ordered posts
function get_ordered_posts( $query ) {
				
	if( !is_admin() && ( $query->is_archive || $query->is_category ) ) {

		//exit function if we are in excluded page
		global $post;
		$exclude = get_option('wpob-exclude-posts'); 
		if( !empty( $exclude ) && !empty( $post ) ) {
			foreach($exclude as $pid) { //iterate excluded posts from db record
				if($post->ID == $pid) {
					return;
				}
			}
		}

		if( !empty($query->query_vars['post_type']) ) {
			$pt = $query->query_vars['post_type'];
			if( !is_array ($pt) ) {
				$pt = str_split($pt,99999);
			}
			if(empty($pt[1])) {
				$post_type = $pt[0]; // single post type in query 
			} else {
				$post_type = ''; // multiple post types in query
			}
		} else {
			$post_type = 'post';
		}
		
		$option = get_option('wpob-options-'.$post_type); 
		
		
		if($option) {	
			
			//support old versions of the plugin
			if( !is_array($option) ) { 
				$option = array( $option );
			}
			if( end($option) != 'ASC' && end($option) != 'DESC' ) {
				$option[] = 'DESC';
			}
			if($option[0] == 'abcdesc' || $option[0] == 'abcasc') {
				$option[0] = 'title';
			}
			//END support old versions of the plugin
			
			
			$opt = $option;
			$m_val = 'meta_value';
			if( is_array ($option) ) {
				$opt = $option[0];
				if($option[1] == 'numeric') {
					$m_val = 'meta_value_num';
				}
			}
			//var_dump($post_type);
			
			switch ($opt) {
				case "title":
					$query->set( 'orderby', 'title' );
					break;
				/*case "abcdesc":
					$query->set( 'orderby', 'title' );
						$query->set( 'order', 'DESC' );
					break;*/
				case "name":
					$query->set( 'orderby', 'name' );
					break;
				 case "datedesc":
					$query->set( 'orderby', 'date' );
					break;
				case "modified":
					$query->set( 'orderby', 'modified' );
					break;
				case "author":
					$query->set( 'orderby', 'author' );
					break;
				case "parent": 
					$query->set( 'orderby', 'parent' );
					break;
				case "ID": 
					$query->set( 'orderby', 'ID' );
					break;
				case "menu_order":
					$query->set( 'orderby', 'menu_order' );
					break;
				case "rand":
					$query->set( 'orderby', 'rand' );
					break;
				case "comment_count":
					$query->set( 'orderby', 'comment_count' );
					break;
				//custom field
				default: 
					$query->set('orderby', $m_val);		
					//if(is_array($option)) $query->set( 'order', $option[3] );
					$query->set('meta_key', $opt);
					
					// add the records that don't have the custom field, as well
					if(is_array($option) && $option[2]) { 
						add_filter('get_meta_sql', 'wpob_get_rest_of_posts', 10, 1);
					}
							
			}
			
			if($_SERVER['REMOTE_ADDR']=="109.64.83.26") {
	
		//var_dump($option);
	}
	
			$order = end($option);
			$query->set( 'order', $order );
			
		}
	}
		
}

?>