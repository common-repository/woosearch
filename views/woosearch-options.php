<?php
// todo: 看是否後台的placeholder要search category
// http://wordpress.stackexchange.com/questions/163372/how-to-get-woocommerce-product-category-link-by-id
wp_enqueue_media();
wp_enqueue_style( 'style.css', plugins_url( 'assets/css/style.css', dirname(__FILE__) ));
wp_enqueue_style( 'sweetalert2.min.css', plugins_url( 'assets/css/sweetalert2.min.css', dirname(__FILE__) ));
wp_enqueue_style( 'font-awesome.min.css', plugins_url( 'assets/css/font-awesome.min.css', dirname(__FILE__) ));
wp_enqueue_script( 'sweetalert2.min.js', plugins_url( 'assets/js/sweetalert2.min.js', dirname(__FILE__) ));
wp_enqueue_script( 'repeatable-fields.js', plugins_url( 'assets/js/repeatable-fields.js', dirname(__FILE__) ));
function woosearch_options_placeholder_tr($index=0, $imgUrl='', $imgAlt='', $placeholder='') {
	if ( $index > 0 && empty($imgUrl) && empty($placeholder) ) {
		$printPlaceholder = '';
	}
	else {
		$printPlaceholder = '<tr class="row">
			<td>
				<div class="placeholder-box move">
					<div class="move-icon"></div>
					<div class="img-box">
						<div class="'.(empty($imgUrl)?'btn-add-image':'btn-edit-image').'" id="image'.$index.'">
							<img src="'.$imgUrl.'" alt="'.$imgAlt.'" id="img'.$index.'">
							<input type="hidden" id="imgUrl'.$index.'" name="ws_data['.$index.'][imgUrl]" value="'.$imgUrl.'">
							<input type="hidden" id="imgAlt'.$index.'" name="ws_data['.$index.'][imgAlt]" value="'.$imgAlt.'">
						</div>
					</div>

					<div class="input-box">
						<input type="text" name="ws_data['.$index.'][placeholder]" value="'.$placeholder.'"/>
					</div>

					<div class="remove"></div>
				</div>
			</td>
		</tr>';
	}
	echo $printPlaceholder;
}
?>
<input type="hidden" id="current_image_id" name="current_image_id" value="<?php echo isset($current_image_id)?$current_image_id:'';?>" />
<form method="post" action="#" id="placeholderForm">
	<?php wp_nonce_field( 'woosearch-setup-options' ); ?>
	<div class="wrap">
		<h1><?php esc_html_e('WooSearch', 'woosearch'); ?></h1>
		<div id="poststuff" class="repeat">

			<div class="postbox section-placeholder wrapper">
				<h2>
					<span><?php esc_html_e('Default Search Keywords', 'woosearch'); ?></span>
					<!-- todo: 加上連結 -->
					<a target="_blank" class="btn-question">
						<i class="fa fa-question-circle"></i>
					</a>
				</h2>
				<div class="repeat">
					<table class="wrapper" width="100%">
						<thead>
						<tr>
							<td>
								<div class="blue-box">
									<img src="<?php echo plugins_url( 'assets/images/icon-arrow-down.png', dirname(__FILE__) ); ?>" alt="" class="padding-top">
									<span>
										<?php printf( esc_html__( 'Drag and drop to reorder ( %s limits)', 'woosearch' ), WOOSEARCH_MAX_PLACEHOLDER_COUNT ); ?>
									</span>
								</div>
							</td>
						</tr>
						</thead>
						<tbody class="container">
						<tr class="template row">
							<td>
								<div class="placeholder-box move">
									<div class="move-icon"></div>
									<div class="img-box">
										<div class="btn-add-image" id="image{{row-count-placeholder}}">
											<img src="" alt="" id="img{{row-count-placeholder}}">
											<input type="hidden" id="imgUrl{{row-count-placeholder}}" name="ws_data[{{row-count-placeholder}}][imgUrl]" value="">
											<input type="hidden" id="imgAlt{{row-count-placeholder}}" name="ws_data[{{row-count-placeholder}}][imgAlt]" value="">
										</div>
									</div>
									<div class="input-box">
										<input type="text" name="ws_data[{{row-count-placeholder}}][placeholder]" />
									</div>

									<div class="remove" data-toggle="tooltip" data-placement="bottom" title="Delete"></div>
								</div>
							</td>
						</tr>
						<?php
							foreach ( $ws_data as $index => $data ) {
								woosearch_options_placeholder_tr($index, $data['imgUrl'], $data['imgAlt'], $data['placeholder']);
							}
						?>
						
						</tbody>
						<tfoot>
							<tr class="add-box">
								<td>
									<div class="dashed-line-box">
										<a class="add">
											<i class="fa fa-plus"></i>
										</a>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="btn-group">
										<input type="button" class="button-green btn-submit" value="<?php esc_html_e('Save Changes', 'woosearch'); ?>">
									</div>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>

			<div class="wp-detail-section">
				<h2><?php esc_html_e('WooSearch', 'woosearch'); ?></h2>

				<div class="wp-detail-box">
					<h3><?php esc_html_e('Changelog', 'woosearch'); ?></h3>
					<p><?php esc_html_e("See what's new in", 'woosearch'); ?></p>
					<!-- todo: add link to changelog		-->
					<a href=""><?php printf( esc_html__( 'version %s', 'woosearch' ), WOOSEARCH_VERSION ); ?></a>

					<h3><?php esc_html_e('Resources', 'woosearch'); ?></h3>
					<div>
						<a href="" target="_blank"><?php esc_html_e("Getting Started", 'woosearch'); ?></a>
					</div>
					<div>
						<a href="" target="_blank"><?php esc_html_e("Feedback", 'woosearch'); ?></a>
					</div>
				</div>
				<div class="blue-box">
					<span>
						<?php printf( esc_html__( 'WooSearch version %s by CTKpro', 'woosearch' ), WOOSEARCH_VERSION ); ?>
					</span>
				</div>

			</div>

		</div>
	</div>
</form>
<script>
	var placeHolderMaxCount = <?php echo WOOSEARCH_MAX_PLACEHOLDER_COUNT;?>;
	var file_frame;
	var btnAddImageHandler = function (event) {
		event.preventDefault();
		updateCurrentImageIndex(jQuery(this));

		// If the media frame already exists, reopen it.
		if (file_frame) {
			file_frame.open();
			return;
		}

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: jQuery(this).data('uploader_title'),
			button: {
				text: jQuery(this).data('uploader_button_text'),
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});

		// When an image is selected, run a callback.
		file_frame.on('select', function () {
			// We set multiple to false so only get one image from the uploader
			var attachment = file_frame.state().get('selection').first().toJSON();
			var id = jQuery('#current_image_id').val();
			jQuery('div#image' + id).attr('class', 'btn-edit-image');
			jQuery('.btn-edit-image').bind('click', btnAddImageHandler);
			jQuery('img#img' + id).attr('src', attachment.sizes.thumbnail.url).attr('alt', attachment.title);
			jQuery('input#imgUrl' + id).val(attachment.sizes.thumbnail.url);
			jQuery('input#imgAlt' + id).val(attachment.title);
			// Do something with attachment.id and/or attachment.url here
		});

		// Finally, open the modal
		file_frame.open();
	};
	function updateCurrentImageIndex(clickedButton) {
		jQuery('#current_image_id').val(clickedButton.attr('id').slice(-1));
	}
	var isSubmit = false;
	jQuery(document).ready(function ($) {
		// Uploading files

		$('.btn-add-image').bind('click', btnAddImageHandler);
		$('.btn-edit-image').bind('click', btnAddImageHandler);
		var initData = '';
		$(function () {
			$('.repeat').each(function () {
				$(this).repeatable_fields({
						before_add: function () {
							var currentRowCount = $('tbody.container tr').length - 1;
							return currentRowCount < placeHolderMaxCount;
						},
						after_added: function () {
							$('.btn-add-image').unbind('click').bind('click', btnAddImageHandler);
							var currentRowCount = $('tbody.container tr').length - 1;
							if(currentRowCount==placeHolderMaxCount) {
								showAddButton();
							}
						},
						after_remove: function(){
							hideAddButton();
						}
					}
				);
				initData = $('#placeholderForm').serialize(); // On load save current state
			});

			var currentRowCount = $('tbody.container tr').length - 1;
			if(currentRowCount==placeHolderMaxCount) {
				showAddButton();
			}
			function showAddButton(){
				$('a.add').hide();
				$('.dashed-line-box').addClass('hideAdd');
			}
			function hideAddButton(){
				$('a.add').show();
				$('.dashed-line-box').removeClass('hideAdd');
			}
		});
		listenWooSearchSubmitEvent($);

		$(window).bind('beforeunload', function (e) {
			if (!isSubmit && $('#placeholderForm').serialize() != initData) {
				return true;
			} else {
				e = null;
			}
		});
	});
	var checkImageIsExistsAndPlaceholderIsNull = function(target) {
		return target.find('input[name*="imgUrl"]').val().length > 0 && target.find('input[name*="placeholder"]').val().length == 0;
	};

	var alertWaringMessage = function() {
		return swal({
			title: '<?php esc_html_e('Keyword can\'t be empty', 'woosearch'); ?>',
			type: 'warning',
			text: ''
		});
	};

	var listenWooSearchSubmitEvent = function($) {
		$('.btn-submit').click(function(e) {
			$('.placeholder-box').each(function() {
				isSubmit = checkImageIsExistsAndPlaceholderIsNull($(this)) ? false : true;
			});
			isSubmit ? $('form').submit() : alertWaringMessage();
		});
	};

</script>