<?php if (!$page): ?>
  <article id="node-<?php print $node->nid; ?>" class="node<?php if ($sticky) { print ' sticky'; } ?><?php if (!$status) { print ' node-unpublished'; } ?> clearfix">
<?php endif; ?>

<?php
  	
  $flickr_set = $node->field_widget_url[0]['url'];
	$callback_function_name = $_GET['callback'];
  $num_items = $node->field_widget_limit[0]['value'];
	
	$set_str_pos = strpos($flickr_set, 'sets');
	$set_id = substr($flickr_set, ($set_str_pos + 5));
	if (strpos($set_id, '/') !== FALSE) {
		// TODO refactor later - seems brittle...
		$set_id = str_replace('/', '', $set_id);
	}

	$flickr_params = array(
		'api_key'	=> 'e3ae977319974d2c4a777e0224b9dcbf',
		'method'	=> 'flickr.photosets.getPhotos',
		'format'	=> 'json',
		'jsoncallback' => $callback_function_name,
		'per_page' => $num_items,
		'photoset_id'	=> $set_id,
		'secret' => '843a8efd1cf8ef13',
		'extras' => 'url_o',
	);
	$flickr_encoded_params = array();
	foreach ($flickr_params as $k => $v) {
		$flickr_encoded_params[] = urlencode($k).'='.urlencode($v);
	}
	$flickr_url = "http://api.flickr.com/services/rest/?".
		implode('&', $flickr_encoded_params);
	$flickr_rsp = file_get_contents($flickr_url);
	// strip out the callback function and decoded it to test
	$flickr_rsp2 = substr($flickr_rsp, (strlen($callback_function_name)+1), -1);
	$flickr_rsp_obj = json_decode($flickr_rsp2, TRUE);
	// check if valid
	if ($flickr_rsp_obj['stat'] == 'ok') {
		print $flickr_rsp; 
	} else {
		print 'error';
	}	
?>

<?php if (!$page): ?>
  </article> <!-- /.node -->
<?php endif;?>
