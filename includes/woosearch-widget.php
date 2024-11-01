<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ref http://www.mrmu.com.tw/2013/06/19/wordpress-widget-customization/
class WooSearch_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'side_woosearch', 'description' => 'WooSearch Widget');
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'side_woosearch-widget' );
		parent::__construct( 'side_woosearch-widget', esc_html__('WooSearch', 'woosearch'), $widget_ops, $control_ops);
	}

	// 描述widget顯示於前台時的外觀
	function widget( $args, $instance ) {
		// $args裡可以拿到所在版位的相關資訊，如before_widget、after_widget..等
//		extract( $args );
		echo $args['before_widget'];

		// 這一段可以參考woocommerce的wc-template-functions.php裡的get_product_search_form
//		$placeholder = $instance['placeholder'];
//		echo "<h2>$placeholder</h2>";
		wp_enqueue_script( 'react.min.js', plugins_url( 'assets/js/react.min.js', dirname(__FILE__) ));
		wp_enqueue_script( 'react-dom.min.js', plugins_url( 'assets/js/react-dom.min.js', dirname(__FILE__) ));
		wp_enqueue_script( 'browser.min.js', plugins_url( 'assets/js/browser.min.js', dirname(__FILE__) ));
		wp_enqueue_script( 'remarkable.min.js', plugins_url( 'assets/js/remarkable.min.js', dirname(__FILE__) ));
		wp_enqueue_script( 'woosearch.js', plugins_url( 'assets/js/woosearch.js', dirname(__FILE__) ));
		wp_enqueue_script( 'woosearch-apis.js', plugins_url( 'assets/js/woosearch-apis.js', dirname(__FILE__) ));
		wp_enqueue_style( 'woosearch-searchbar.css', plugins_url( 'assets/css/woosearch.css', dirname(__FILE__) ));
		?>

		
		<div id="woosearch-content"></div>
		<!-- below is for dev purpose -->
		<!-- <script type="text/babel" src="<?php echo plugins_url( 'assets/js/woosearch.js', dirname(__FILE__) ) ?>"></script> -->
		<!-- -->

		<script>
			var woosearchAPIs;
			document.onreadystatechange = function () {
				if (document.readyState == "complete") {
					wsApis("<?php echo admin_url( 'admin-ajax.php' ); ?>", function(methods) {
						woosearch({
							initPlaceholders: methods.getAllPlaceholder,
							loadPreview: methods.searchProducts,
							onSearch: function(query){
								window.location.href = ('<?php echo get_site_url(); ?>'+'?s=' + query + '<?php echo $instance['isSearchBothPostAndProduct']?'':'&post_type=product';?>');
							},
							inputPlaceholder: "<?php echo $instance['placeholder']; ?>"
						});
					});
				}
			}
		</script>
		<?php

		echo $args['after_widget'];
	}

	// 於後台更新Widget時會做的事
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		// 簡單幫設定內容作一下Strip tags，擋掉html tag
		$instance['placeholder'] = strip_tags( $new_instance['placeholder'] );
		$instance['isSearchBothPostAndProduct'] =  $new_instance['isSearchBothPostAndProduct'];
		return $instance;
	}

	// Widget在後台模組頁的外觀
	function form( $instance ) {
		// 可以設定預設值
		$defaults = array(
			'placeholder'=> esc_html__('Click here to search', 'woosearch'),
			'isSearchBothPostAndProduct'=>false
		);
		$instance = wp_parse_args($instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'placeholder' ); ?>"><?php echo esc_html__('Set placeholder', 'woosearch'); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'placeholder' ); ?>" value="<?php echo $instance['placeholder']; ?>">
			<br/>
			<label for="<?php echo $this->get_field_id( 'isSearchBothPostAndProduct' ); ?>"><?php echo esc_html__('Search both Posts and Products (Uncheck for search product only)', 'woosearch'); ?></label>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'isSearchBothPostAndProduct' ); ?>" value="isSearchBothPostAndProduct" <?php echo $instance['isSearchBothPostAndProduct']?'checked':''; ?>>
		</p>
		<?php
	}
}
