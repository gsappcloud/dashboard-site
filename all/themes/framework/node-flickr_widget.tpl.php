<?php if (!$page): ?>
  <article id="node-<?php print $node->nid; ?>" class="node<?php if ($sticky) { print ' sticky'; } ?><?php if (!$status) { print ' node-unpublished'; } ?> clearfix">
<?php endif; ?>

<?php
	// must calculate image resized coordinates here based on the widget-size and include these numbers
	$widget_size = $node->field_widget_size[0]['value'];
	$widget_padding = 10; // default padding is 10 pixels
	$widget_padding *= 2; // multiply by 2 for correct scaling
	$target_w = null;	$target_h = null;
	
	switch ($widget_size) {
		case 'one-column':
		// TODO move to top of file
			$target_w = (240 - $widget_padding);
			$target_h = (240 - $widget_padding); break;
		case 'two-column':
			$target_w = (500 - $widget_padding);
			$target_h = (375 - $widget_padding);
	}

  $flickr_set = $node->field_widget_url[0]['url'];
  $callback_function_name = null;
	$callback_function_name = $_GET['callback'];
  $num_items = $node->field_widget_limit[0]['value'];
  $description = $node->field_widget_text[0]['view'];
	
	// additional styling parameters
	$cycle_duration = $node->field_widget_cycle_duration[0]['value'];
	$cycle_autostart = $node->field_widget_cycle_autostart[0]['value'];
	$widget_type = $node->field_widget_type[0]['value'];

	// get the set id from the flickr URL string
	$set_str_pos = strpos($flickr_set, 'sets');
	$set_id = substr($flickr_set, ($set_str_pos + 5));
	if (strpos($set_id, '/') !== FALSE) {
		// TODO refactor later - seems brittle...
		$set_id = str_replace('/', '', $set_id);
	}

	// parameters for the flickr API call
	// NOTE: leave this set as 'json' even though
	// we are decoding the array - json_decode/encode
	// is faster in PHP than serialization
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
	// make the flickr API request
	$flickr_rsp = file_get_contents($flickr_url);
	// strip out the callback function and decoded it to array
	$flickr_rsp2 = substr($flickr_rsp, (strlen($callback_function_name)+1), -1);
	$flickr_rsp_obj = json_decode($flickr_rsp2, TRUE);
	
	// add cycle params and other custom data to our array
	$flickr_rsp_obj['cycle']['duration'] = $cycle_duration;
	$flickr_rsp_obj['cycle']['autostart'] = $cycle_autostart;
	$flickr_rsp_obj['widget']['size'] = $widget_size;
	$flickr_rsp_obj['widget']['group'] = $widget_type;
	$flickr_rsp_obj['widget']['node_type'] = $node->type;
	$flickr_rsp_obj['widget']['node_id'] = $nid;
	$flickr_rsp_obj['widget']['node_title'] = $node->title;
	$flickr_rsp_obj['widget']['node_description'] = $node->field_widget_text[0]['value'];
	
	
	$aspect = null;

	// for each image, calculate target pixel dimensions based on 
	// the widget size
	foreach($flickr_rsp_obj['photoset']['photo'] as $key=>&$value) {
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

	// go back and see if the response was valid
	// if so re-encoded it to json with our custom data added
	// and print it to the page
	if ($flickr_rsp_obj['stat'] == 'ok') {
		print $callback_function_name . '(' . json_encode($flickr_rsp_obj) . ')';
	} else {
		// TODO better error handling: need to design this
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
