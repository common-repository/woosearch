<?php

const WOOSEARCH_KEYWORDS_TABLE_NAME="woosearch_keywords";
/**
 * WooSearch_DB_Keywords
 */
class WooSearch_DB_Keywords {

	private $table_name = false;
	
	function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . WOOSEARCH_KEYWORDS_TABLE_NAME;
	}

	public function insert(array $data) {
		global $wpdb;
		$timestamp = date('Y-m-d H:i:s', time());
		$data += array(
			'modified' => $timestamp,
			'created' => $timestamp,
		);
		$wpdb->insert($this->table_name, $data);
		return $wpdb->insert_id;
	}

	public function get_all($order_by='sort') {
		global $wpdb;
		$sql = 'SELECT * FROM `'.$this->table_name.'`';
		if(!empty($order_by)) {
			$sql .= ' ORDER BY ' . $order_by;
		}
		$all = $wpdb->get_results($sql);
		return $all;
	}

	public function get_by(array $condition_value, $condition = '=') {
		global $wpdb;
		$sql = 'SELECT * FROM `'.$this->table_name.'` WHERE ';
		foreach ($condition_value as $field => $value) {
			switch(strtolower($condition)) {
				case 'in':
					if(!is_array($value)) {
						throw new Exception("Values for IN query must be an array.", 1);
					}
					$sql .= $wpdb->prepare('`%s` IN (%s)', $field, implode(',', $value));
				break;
				default:
					$sql .= $wpdb->prepare('`'.$field.'` '.$condition.' %s', $value);
				break;
			}
		}
		$result = $wpdb->get_results($sql);
		return $result;
	}

	public function update(array $data, array $condition_value) {
		global $wpdb;
		if(empty($data)) {
			return false;
		}
		$data['modified'] = date('Y-m-d H:i:s', time());
		$updated = $wpdb->update( $this->table_name, $data, $condition_value);
		return $updated;
	}

	public function delete(array $condition_value) {
		global $wpdb;
		$deleted = $wpdb->delete( $this->table_name, $condition_value );
		return $deleted;
	}

}

function woosearch_table_schema() {
	global $wpdb;

	$collate = '';

	if ( $wpdb->has_cap( 'collation' ) ) {
		$collate = $wpdb->get_charset_collate();
	}

	$tables = "
CREATE TABLE {$wpdb->prefix}".WOOSEARCH_KEYWORDS_TABLE_NAME." (
id BIGINT(20) NOT NULL AUTO_INCREMENT ,
placeholder TEXT NOT NULL ,
imgUrl TEXT NULL ,
imgAlt TEXT NULL ,
sort INT(11) NOT NULL DEFAULT '0' ,
modified TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL ,
created TIMESTAMP NOT NULL ,
PRIMARY KEY (id)
) $collate;";

	return $tables;
}
