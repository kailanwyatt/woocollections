<?php
/**
 * @todo Add description
 * @since 1.0.0
 */
function wooc_bp_setup_nav() {
    global $bp;
	bp_core_new_nav_item( 
    array( 
         'name' => __('Collections','woo-collections'),
        'slug' => 'collections',
        'position' => 50,  
        'screen_function' => 'woo_collections_bp', 
        'default_subnav_slug' => 'user-collections',
		'item_css_id' => 'collections'
    ));
}
add_action( 'bp_setup_nav', 'wooc_bp_setup_nav', 100 );
/**
 * @todo Add description
 * @since 1.0.0
 */
function woo_collections_bp(){
	add_action( 'bp_template_title', 'woo_collections_screen_title' );
    add_action( 'bp_template_content', 'woo_collections_screen_content' );
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
/**
 * @todo Add description
 * @since 1.0.0
 */
function woo_collections_screen_title(){
	echo __('Collections', 'woo-collections');	
}
/**
 * @todo Add description
 * @since 1.0.0
 */
function woo_collections_screen_content(){
	global $bp;
	$user_id = $bp->displayed_user->id;
	echo do_shortcode( '[user_collections user_id="' . absint( $user_id ) . '"]' );	
}