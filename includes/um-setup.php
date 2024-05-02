<?php
class WC_Collections_Ultimate_Member {
	public function __construct() {
		add_filter( 'um_profile_tabs', array( $this, 'custom_tab' ), 1000, 1 );
		add_action( 'um_profile_content_collections_default', array( $this, 'um_profile_content_collections_default' ) );
	}

	public function custom_tab( $tabs = array() ) {
		$tabs['collections'] = array(
			'name' => __( 'Collections', 'woocollections-for-woocommerce' ),
			'icon' => 'um-faicon-comments',
			'custom'            => true,
			'subnav_default'    => 0,
		);
		return $tabs;
	}

	public function um_profile_content_collections_default() {
		$user_id = um_get_requested_user();
		echo do_shortcode( '[user_collections user_id="' . absint( $user_id ) . '"]' );	
	}
}

new WC_Collections_Ultimate_Member();