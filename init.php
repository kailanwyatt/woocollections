<?php
if ( function_exists( 'UM' ) ) {
	require_once( WOO_COLLECTION_PATH . 'includes/um-setup.php');	
}
/**
 * WooCollection Class
 *
 * This class controls the AJAX, Post Type and functionality of the plugin
 *
 * @package    WooCollection
 * @author     SuitePlugins 
 */
class woo_collection{
	/**
	 * @var string $lang_slug Language Slug
	 * @since 1.0.0
	 */
	public $lang_slug = 'woo-collection';
	/**
	 * @var string $post_type Post Type 
	 * @since 1.0.0
	 */
	public $post_type = 'woo_collection';
	/**
	 * @var string $meta_key Meta Key used in Post meta
	 * @since 1.0.0
	 */
	public $meta_key = '_collection_items';
	/**
	 * @var string $status_message informative message for current settings tab
	 * @since 1.0.0
	 * @todo Hook Message or remove
	*/
	var $status_message;

	/**
	 * @var string $error_message error message for current settings tab
	 * @since 1.0.0
	 * @todo Hook Message or remove
	*/
	var $error_message;

	/**
	 * 	
	 */
	public function woo_collection(){
		add_action('init',array($this, 'setup_woo_collection'));
		add_action( 'widgets_init', array($this, 'setup_widgets_init') );
		add_action( 'wp_enqueue_scripts', array($this, 'setup_scripts') );
		add_action( 'woocommerce_single_product_summary', array($this, 'add_collection_button'), 12);
		add_action( 'wp_footer',array($this, 'add_modal_div'));
		//
		add_filter( 'template_include', array($this, 'setup_single_template'));
		//shortcodes
		add_shortcode( 'user_collections', array($this, 'user_collections_shortcode'));
		//
		add_action( 'wp_ajax_wooc_get_collection', array($this, 'wooc_get_collection') );
		add_action( 'wp_ajax_nopriv_wooc_get_collection', array($this, 'wooc_get_collection') );
		
		//ajax
		add_action( 'wp_ajax_wooc_create_collection', array($this, 'update_collection') );
		add_action( 'wp_ajax_nopriv_wooc_create_collection', array($this, 'update_collection') );
		
		add_action( 'wp_ajax_wooc_add_to_collection', array($this, 'wooc_add_to_collection') );
		add_action( 'wp_ajax_nopriv_wooc_add_to_collection', array($this, 'wooc_add_to_collection') );
	}
	/**
	 * @todo Add description
	 * @since 1.0.0
	 */
	public function setup_woo_collection(){
		
		//register post_type
			$labels = array(
			'name'               => _x( 'Collections', 'post type general name', $this->lang_slug ),
			'singular_name'      => _x( 'Collection', 'post type singular name', $this->lang_slug ),
			'menu_name'          => _x( 'Collections', 'admin menu', $this->lang_slug ),
			'name_admin_bar'     => _x( 'Collection', 'add new on admin bar', $this->lang_slug ),
			'add_new'            => _x( 'Add New', 'collection', $this->lang_slug ),
			'add_new_item'       => __( 'Add New Collection', $this->lang_slug ),
			'new_item'           => __( 'New Collection', $this->lang_slug ),
			'edit_item'          => __( 'Edit Collection', $this->lang_slug ),
			'view_item'          => __( 'View Collection', $this->lang_slug ),
			'all_items'          => __( 'All Collections', $this->lang_slug ),
			'search_items'       => __( 'Search Collections', $this->lang_slug ),
			'parent_item_colon'  => __( 'Parent Collections:', $this->lang_slug ),
			'not_found'          => __( 'No collections found.', $this->lang_slug ),
			'not_found_in_trash' => __( 'No collections found in Trash.', $this->lang_slug )
		);
	
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'collection' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail')
		);
	
		register_post_type( 'woo_collection', $args );
		//	
	}
	/**
	 * @todo Add description
	 * @since 1.0.0
	 */
	public function add_collection_button(){
		if ( is_user_logged_in() ) {
			global $post;
			$html='';
			$html.='<div class="woo-collection-button">';
				$html.='<a href="#wooc_modal" id="woo-collection-button-a" product-id="'. esc_attr( $post->ID ) .'">'.__('Add to collection',$this->lang_slug).'</a>';
			$html.='</div>';
			echo apply_filters('woo_collection_button', $html, $post);	
		}
	}
	/**
	 * @todo Add description
	 * @since 1.0.0
	 */
	public function setup_scripts(){
		wp_enqueue_style(
			'wooc-fa',
			'//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'
		);
		wp_enqueue_style(
			'woocollections-style',
			WOO_COLLECTION_URL . '/assets/css/woocollections.css'
		);
		wp_enqueue_script(
			'woocollections-js',
			WOO_COLLECTION_URL . '/assets/js/woocollections.js',
			array( 'jquery' ),
			'1.0.2',
			true
		);
		global $post;
		wp_localize_script( 'woocollections-js', 'obj',
	        array( 
	        	'ajaxurl'      => admin_url( 'admin-ajax.php' ),
	        	'empty_msg'    => __( 'Collection name can\'t be empty', 'woocollections-for-woocommerce' ),
	        	'updated_msg'  => __( 'Collection Updated', 'woocollections-for-woocommerce' ),
	        	'product_id'   => ( ! empty( $post ) ? $post->ID : '' ) 
	        )
	    );
	}
	/**
	 * @todo Add description
	 * @since 1.0.0
	 */
	public function wooc_add_to_collection(){
		if(!empty($_POST)){
			$user_id = get_current_user_id(); 
			$collection_id = (int)$_POST['collection_id'];
			$product_id = (int)$_POST['product_id'];
			$key = '_collection_items';
			$collection_items = get_post_meta($collection_id,$this->meta_key, true);
			$act = (int)$_POST['act'];
			$entry = array();
			$response = 0;
			//if product is new to collection. add it
			if(!in_array($product_id, $collection_items)){
				$entry[] = $product_id;
			}
			if(!empty($collection_items)){
				foreach($collection_items as $key=>$value){
					//if we are removing item, skip
					if($act == 1 && $product_id == $value){
						$response = 1;
						continue;
					}
					$entry[] = $value;
				}
			}else{
				$entry[] = $product_id;
			}
			echo $response;
			update_post_meta($collection_id,$this->meta_key, $entry);
			
		}
		exit();	
	}
	/**
	 * @todo Add description
	 * @since 1.0.0
	 */
	public function update_collection(){
		$user_id = (int)$_POST['user_id'];
		if(empty($_POST['id'])){
			// Create collection object
			$post = array(
			  'post_title'    => sanitize_text_field($_POST['woo_collection_name']),
			  'post_status'   => 'publish',
			  'post_author'   => $user_id,
			  'post_type'	  => $this->post_type
			);
			
			// Insert the post into the database
			$post_id = wp_insert_post( $post );
			if($post_id){
				update_post_meta($post_id, '_collection_owner', $user_id);	
				echo '<li><a href="#" collection-id="'.$post_id.'" class="add-to-collection not-selected">'.sanitize_text_field($_POST['woo_collection_name']).'</a></li>';
			}
		}else{
			$my_post = array(
				  'ID'           => (int)$_POST['id'],
				  'post_title'    => sanitize_text_field($_POST['woo_collection_name'])
			  );
			
			  // Update the post into the database
			  wp_update_post( $my_post );	
		}
		exit();
	}
	/**
	 * @todo Add description
	 * @since 1.0.0
	 */
	public function add_modal_div(){
		//echo '<div id="lean_overlay"></div>';
		?>
        	<div id="wooc_modal"></div>
        <?php	
	}
	/**
	 * @todo Add description
	 * @since 1.0.0
	 */
	public function get_user_collections($user_id = ''){
		global $wpdb;
		$query = "
		SELECT {$wpdb->prefix}posts.ID, {$wpdb->prefix}posts.post_title
		FROM {$wpdb->prefix}posts
		LEFT JOIN {$wpdb->prefix}postmeta m2
		  ON ( {$wpdb->prefix}posts.ID = m2.post_id )
		WHERE
		{$wpdb->prefix}posts.post_type = '{$this->post_type}'
		AND {$wpdb->prefix}posts.post_status = 'publish'
		AND ( m2.meta_key = '_collection_owner' AND m2.meta_value = '{$user_id}' )
		GROUP BY {$wpdb->prefix}posts.ID
		ORDER BY {$wpdb->prefix}posts.post_title 
		ASC;
		";
		$results = $wpdb->get_results($query);
		echo $wpdb->last_error;
		return $results;
	}
	/**
	 * @todo Add description
	 * @since 1.0.0
	 */
	public function wooc_get_collection(){
		global $post;
		$user_id     = get_current_user_id(); 
		$collections = $this->get_user_collections($user_id);
		$product_id  = (int) $_POST['product_id'];
		?>
        	<div class="wooc_modal_title">
            	<h3><?php echo __('Add this to a Collection', $this->lang_slug); ?></h3>
            </div>
            <div class="wooc_modal_content">
            	<?php
				if(!empty($collections)):
					echo '<ul>';
					foreach($collections as $item){
						$collection_items = get_post_meta($item->ID, $this->meta_key, true);
						if(!empty($collection_items) && in_array($product_id, $collection_items)){
							$class = "selected";
						}else{
							$class = "not-selected";	
						}
						echo '<li><a href="" class="add-to-collection '.$class.'" collection-id="'.$item->ID.'">'.$item->post_title.'</a></li>';
					}
					echo '</ul>';	
				else:
					echo '<div class="wooc_modal_empty">'.__('No collections found', $this->lang_slug).'</div>';
				endif;
				?>	
            </div>
            <div class="wooc_modal_form">
            	<h3><?php echo __( 'Create a Collection', $this->lang_slug ); ?></h3>
                <div class="wooc_error"></div>
                <form id="woo_collection_form">
                	<input type="text" name="woo_collection_name" id="woo_collection_name" value="" style="width:100%; margin:6px 0px;" placeholder="<?php echo __('Collection Name', $this->lang_slug); ?>" />
                    <div class="woo_collection_button">
                    	<input type="button" id="woo_collection_button" style="width:100%" class="button btn btn-primary" value="<?php echo __('Save Collection', $this->lang_slug); ?>">
                    </div>
                    <input type="hidden" name="action" value="wooc_create_collection" />
                    <input type="hidden" name="user_id" value="<?php echo absint( $user_id ); ?>" />
                </form>
            </div>
        <?php
		exit();	
	}
	/**
	 * @todo Add description
	 * @since 1.0.0
	 */
	public function user_collections_shortcode( $atts = array() ){
		$html='';
		global $bp;
		$user_id = get_current_user_id(); 
		$user_id = 0;

		// Get BuddyPress user ID.
		if ( function_exists('buddypress' ) ) {
			$user_id = ! empty( $bp->displayed_user->id ) ? (int) $bp->displayed_user->id : $user_id;	
		}

		// Get Ultimate member user ID.
		if ( function_exists( 'UM' ) ) {
			$um_user_id = um_get_requested_user();
			$user_id = ! empty( $um_user_id ) ? (int) $um_user_id : $user_id;	
		}
		
		$fx = get_option('wooc_settings_collections');
		if(!empty($fx)){
			$fx = $fx['thumbnail-fx'];
		}else{
			$fx = 'slideshow';
		}
		$atts = shortcode_atts(
		array(
			'user_id' => $user_id,
			'fx' => $fx,
		), $atts, 'user_collections' );
		$collections = $this->get_user_collections($atts['user_id']);
		if(!empty($collections)){
			foreach($collections as $item){
				$html.='<div class="woo-collection-slideshow">';
					$collection_items = get_post_meta($item->ID, $this->meta_key, true);
					//print_r($collection_items);
					$html.='<a href="'.get_permalink($item->ID).'" class="wooc-item"><div class="cycle-slideshow cycle-paused" data-cycle-speed="300" data-cycle-manual-speed="300"  data-cycle-timeout="1200"  data-cycle-log="false" data-cycle-manual-fx="'.$atts['fx'].'" data-cycle-fx="'.$atts['fx'].'">';
						if(!empty($collection_items)){
							foreach($collection_items as $key=>$value){
								if ( has_post_thumbnail($value) ) {
									$html.= get_the_post_thumbnail( $value, $size='thumbnail' );
								} elseif ( wc_placeholder_img_src() ) {
									$html.= wc_placeholder_img( $size='thumbnail' );
								}
							}
						}else{
							$html.='<img src="'. wc_placeholder_img_src() .'" />';	
						}
				$html.= '</div><div class="woo-collection-title">'.$item->post_title.'</div></a>';	
				$html.= '</div>';	
			}
		}else{
				$html.='<div class="no-collections">'.__('No Collections found', $this->lang_slug).'</div>';	
			}
		return apply_filters('wooc_collection_display', $html, $collections);	
	}
	/**
	 * @todo Add description
	 * @since 1.0.0
	 */
	public function setup_widgets_init(){
		register_sidebar( array(
        'name'         => __( 'Main Sidebar', $this->lang_slug ),
        'id'           => 'collection',
        'description'  => __( 'Widgets in this area will be shown on the Collection page.', $this->lang_slug ),
        'before_title' => '<h1>',
        'after_title'  => '</h1>',
    ) );
	}
	public function setup_single_template($template){
		if(is_singular($this->post_type)){
			$template = WOO_COLLECTION_PATH . 'templates/single-'. $this->post_type . '.php';	
		}
		return $template;	
	}
	/**
	 * @todo Add description
	 * @since 1.0.0
	 */
	public function product_options(){
		if ( is_user_logged_in() ) {
			$collection_id     = get_queried_object_id();
			global $wp_query;
			if($wp_query->queried_object->post_type == $this->post_type){
				global $post;
				$collection_items = get_post_meta($collection_id, $this->meta_key, true);
				if(!empty($collection_items) && in_array($post->ID, $collection_items)){
					$class = "selected";
				}else{
					$class = "not-selected";	
				}
				echo '<div class="woo-collection-production"><a href="#" class="woo-collection-production-a '.$class.'" collection-id="'. esc_attr( $collection_id ).'" product-id="'.esc_attr( $post->ID ) . '">'.__('Remove from Collection',$this->lang).'</a></div>';
			}
		}
	}
}
$woo_collection = new woo_collection();
