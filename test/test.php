<?php

/*
 * WooSearch Keyword CRUD Test
 */
function test_db_keyword_crud() {
	include_once( plugin_dir_path(__FILE__) . 'woosearch-db-keywords.php' );
	$db_keyword = new WooSearch_DB_Keywords();

	$insert_data = array(
		'keyword' => 'test',
		'image' => 'image.001'
	);
	$new_keyword_id = $db_keyword->insert( $insert_data );
	error_log($new_keyword_id);

	$all_keywords = $db_keyword->get_all();
	error_log(json_encode($all_keywords));

	$update_data = array(
		'keyword' => 'update_test',
		'image' => 'image.002'
	);
	$updated_keyword = $db_keyword->update( $update_data, array( 'ID' => $new_keyword_id ) );
	error_log($updated_keyword);

	$all_keywords = $db_keyword->get_all();
	error_log(json_encode($all_keywords));

	$deleted_keyword = $db_keyword->delete( array( 'ID' => $new_keyword_id ) );
	error_log($deleted_keyword);
}
