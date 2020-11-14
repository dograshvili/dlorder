<?php
/**
 * Plugin Name: DLORDER
 * Plugin URI:
 * Description: A simple plugin to fetch orders. This plugin uses WooCommerce API for CRUD operations
 * Version: 1.0.0
 * Author: D. Lev.
 * Author URI: http://e-cv.dograshvili.com/
 * Text Domain:
 *
 *
 */


 /**
  * Base hook action for plugin
  */
add_action('init','dlorder');


/*
 * Base hook action for api
 */
add_action('rest_api_init', function() {
	register_rest_route('dlorder/', 'fetch/', [
		'methods'  => 'POST',
        'callback' => 'get_orders'
	]);
});


/**
 * @function dlorder
 * 		base plugin function init
 */
function dlorder() {
	// TODO: Store this info to db in a new version
	define('DL_TOKEN', '63457f23b072ad4c5b768b38273e668708f4b87a9cf76577167a8b0a00aa33ad');
	define('DL_MAX_VALID_TIME', 30);
}


/*
 * @function handle_vlogin
 * 		function to handle the vlogin action
 * @param $req MIXED the request to handle
 */
function get_orders($req) {
// 	global $wpdb;
	$params = $req->get_params();
	$ret = ['success' => false, 'msg' => 'GEN_ERR', 'data' => []];
	try {
		if ($params['token'] === DL_TOKEN) {
			if (isset($params['id'])) {
				if (class_exists('WC_Order')) {
					$order = new WC_Order($params['id']);
					$o_products = [];
					foreach ($order->get_items() as $key => $value) {
						$product = $value->get_product();
						$o_products[] = $product->get_data();
					}
					$ret = ['success' => true, 'msg' => '', 'data' => [
						'order' => [
							'main' => $order->get_data(),
							'products' => $o_products
						]
					]];
				} else {
					$ret['msg'] = 'WC_NOT_INSTALLED';
				}
			} else {
				$ret['msg'] = 'INVALID_ID';
			}
		} else {
			$ret['msg'] = 'INVALID_TOKEN';
		}
	} catch (\Exception $e) {
		$ret = [
			'success' => false,
			'msg' => 'FATAL',
			'data' => [
				'fatal_msg' => $e->getMessage()
			]
		];
	}
	$response = new WP_REST_Response($ret);
	$response->set_status(200);
	return $response;
}
