<?php
/**
 * The Template for displaying all single collections.
 *
 * Override this template by copying it to yourtheme/single-woo_collections.php
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header('collection'); 
$template = get_option( 'template' );

switch( $template ) {
	case 'twentyeleven' :
		echo '<div id="primary"><div id="content" role="main">';
		break;
	case 'twentytwelve' :
		echo '<div id="primary" class="site-content"><div id="content" role="main">';
		break;
	case 'twentythirteen' :
		echo '<div id="primary" class="site-content"><div id="content" role="main" class="entry-content twentythirteen">';
		break;
	case 'twentyfourteen' :
		echo '<div id="primary" class="content-area"><div id="content" role="main" class="site-content twentyfourteen"><div class="entry-content">';
		break;
	default :
		echo '<div id="container"><div id="content" role="main">';
		break;
}
?>
<div class="woo-collection-entry">
	<?php 
	global $post;
	$collection_items = get_post_meta($post->ID, '_collection_items', true);
	if(empty($collection_items)){
		$collection_items = array();	
	}
	?>
    <div id="wooc_collection_items" class="woocommerce">
    	<header class="entry-header"><h1 class="entry-title"><?php echo $post->post_title; ?></h1></header>
        	<?php woocommerce_product_loop_start(); ?>
        	<?php
			
			$args =  array( 'post_type' => 'product', 'post__in' => $collection_items );
			$query = new WP_Query( $args );		
			?>
            <?php if ( $query->have_posts() && !empty($collection_items) ) : ?>
                <?php while( $query->have_posts() ) : $query->the_post(); ?>
					<?php wc_get_template_part( 'content', 'product' ); ?>
                <?php endwhile; ?>
            <?php else : ?>
                <p><?php _e( 'No Products Found', 'woo-collections' ); ?></p>
            <?php endif; ?>
            <?php woocommerce_product_loop_end(); ?>
	</div>
</div>
<?php //get_sidebar('collection'); ?>
<?php
switch( $template ) {
	case 'twentyeleven' :
		echo '</div></div>';
		break;
	case 'twentytwelve' :
		echo '</div></div>';
		break;
	case 'twentythirteen' :
		echo '</div></div>';
		break;
	case 'twentyfourteen' :
		echo '</div></div></div>';
		get_sidebar(  );
		break;
	default :
		echo '</div></div>';
		break;
}
get_footer('collection'); ?>