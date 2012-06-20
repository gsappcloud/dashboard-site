<?php if (!$page): ?>
  <article id="node-<?php print $node->nid; ?>" class="node<?php if ($sticky) { print ' sticky'; } ?><?php if (!$status) { print ' node-unpublished'; } ?> clearfix">
<?php endif; ?>

<?php


	// if num items is 0 then show the entire feed
	// widget type should prefetch some kind of pre-made logo etc
	// widget size is basically 1 column is 240px wide, 2 500px wide
	// height is proportional with images...


  $flickr_set = $node->field_widget_url[0]['url'];
  $callback_function_name = null;
	$callback_function_name = $_GET['callback'];
  $num_items = $node->field_widget_limit[0]['value'];
  $description = $node->field_widget_text[0]['view'];
	
	// additional styling parameters
	$cycle_duration = $node->field_widget_cycle_duration[0]['value'];
	$widget_size = $node->field_widget_size[0]['value'];
	$widget_type = $node->field_widget_type[0]['value'];


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
	
	// add cycle params
	$flickr_rsp_obj['cycle']['duration'] = $cycle_duration;
	$flickr_rsp_obj['widget']['size'] = $widget_size;
	$flickr_rsp_obj['widget']['group'] = $widget_type;
	$flickr_rsp_obj['widget']['node_type'] = $node->type;
	$flickr_rsp_obj['widget']['node_id'] = $nid;
	
	// must calculate image resized coordinates here based on the widget-size and include these numbers
	
	$target_w = null;
	$target_h = null;
	$aspect = null;
	
	
	switch ($widget_size) {
		case '1-column': 
			$target_w = 240; $target_h = 240; break;
		case '2-column':
			$target_w = 500; $target_h = 375;
	}

	foreach($flickr_rsp_obj['photoset']['photo'] as $key=>&$value) {
		// calc the dimensions
		$orig_w = $value['width_o']; $orig_h = $value['height_o'];
		$aspect = $orig_h / $orig_w;
		$value['aspect'] = round($aspect, 2);
		
		if ($aspect > 1) { // portrait size, scale to w
			$scale_factor = $target_w / $orig_w;
			$new_h = intval($orig_h * $scale_factor);
			$value['target_h'] = $new_h;
			$value['target_w'] = $target_w;
			$value['cropdist'] = intval(($new_h - $target_h) / 2);
		} else { // landscape
			$scale_factor = $target_h / $orig_h;
			$new_w = intval($orig_w * $scale_factor);
			$value['target_w'] = $new_w;
			$value['target_h'] = $target_h;
			$value['cropdist'] = intval(($new_w - $target_w) / 2);
		}
	}

	// check if valid
	if ($flickr_rsp_obj['stat'] == 'ok') {
		print $callback_function_name . '(' . json_encode($flickr_rsp_obj) . ')';
	} else {
		// error or full node
		if ($callback_function_name == null) {
			print $content;
		} else {
			print 'error w JSON output...'; //TODO 
		}
	}	
?>

<?php if (!$page): ?>
  </article> <!-- /.node -->
<?php endif;?>
