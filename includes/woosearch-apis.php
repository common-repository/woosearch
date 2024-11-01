<?php

require_once 'woosearch-db-keywords.php';

function woosearch_get_all_placeholder() {
	$db_keyword = new WooSearch_DB_Keywords();
	echo json_encode( $db_keyword->get_all() );
	die();
}
add_action( 'wp_ajax_woosearch_get_all_placeholder', 'woosearch_get_all_placeholder' );
add_action( 'wp_ajax_nopriv_woosearch_get_all_placeholder', 'woosearch_get_all_placeholder' );

function woosearch_query_product_posts() {
	$query_params = array(
		'posts_per_page' => 5,
		'post_type' => array('product'),
		'post_status' => 'publish',
		's' => sanitize_text_field($_POST['qs']),
	);

	$all_posts_list = get_posts( $query_params );
	$response = array();

	foreach ($all_posts_list as $key => $post) {
		$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' );
		$thumb_url = $thumb['0'];

		$regular_price = get_post_meta( $post->ID, '_regular_price')[0];
//		$regular_price = $regular_price[0];
		$sale_price = get_post_meta( $post->ID, '_sale_price')[0];
//		$sale_price = $sale_price[0];
		$priceHtml = '<span class="woocommerce-Price-amount amount">'.$regular_price.'</span>';
		if(class_exists('WC_Product')){
			$product = new WC_Product( $post->ID );
			$priceHtml = $product->get_price_html();
		}

		$response[] = array(
			'title' => $post->post_title,
			'description' => wp_strip_all_tags($post->post_content, true),
			'productImgUrl' => $thumb_url,
			'originalPrice' => $regular_price,
			'priceAfterReduction' => $sale_price,
			'productUrl' => get_permalink( $post->ID ),
			'priceHtml'=> $priceHtml
		);
	}
	echo json_encode( $response );

	wp_reset_postdata();
	die();
}
add_action( 'wp_ajax_woosearch_query_product_posts', 'woosearch_query_product_posts' );
add_action( 'wp_ajax_nopriv_woosearch_query_product_posts', 'woosearch_query_product_posts' );