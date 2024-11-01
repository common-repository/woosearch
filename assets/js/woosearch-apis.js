var wsApis;
(function($) {
	wsApis = function(ajaxUrl, callback) {
	
		var methods = {
			getAllPlaceholder: getAllPlaceholder,
			searchProducts: searchProducts,
			submitToSearchPage: submitToSearchPage
		};

		var dataset = {};

		function init(parent) {
			if (!ajaxUrl) {
				return false;
			}

			get_all_placeholder(function(res) {
				var placeholders = [];
				$.each(res, function(key, placeholder) {
					if (placeholder.placeholder != '') placeholders.push(placeholder);
				});
				dataset.initPlaceholders = placeholders;

				if (callback) callback(methods);
				else return methods;
			});
		}

		function get_all_placeholder(callback) {
			var query = {
				action: 'woosearch_get_all_placeholder',
				data: ''
			};
			$.post(ajaxUrl, query)
				.done(function(result) {
					if (callback) callback(JSON.parse(result));
					else return JSON.parse(result);
				});
		}

		function getAllPlaceholder() {
			return dataset.initPlaceholders;
		}

		function searchProducts(qs) {
			var action = {
				action: 'woosearch_query_product_posts',
				qs: qs
			};
			return new Promise(function (fulfill, reject) {
        $.post(ajaxUrl, action).done(function(previewItems) {
        	fulfill(JSON.parse(previewItems));
        });
    	});
		}

		function submitToSearchPage(query) {
			window.location.href = '?s=' + query + '&post_type=product';
		}

		init(this);
	};
})(jQuery);
