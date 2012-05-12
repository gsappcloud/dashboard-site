<?php if (!$page): ?>
  <article id="node-<?php print $node->nid; ?>" class="node<?php if ($sticky) { print ' sticky'; } ?><?php if (!$status) { print ' node-unpublished'; } ?> clearfix">
<?php endif; ?>


  <div class="content">
  <?php
  
    $display_type = $node->field_x_display[0]['value'];
    //Full screen

		$flickr_set = $node->field_x_flickr_set[0]['url'];
		$twitter_feed = $node->field_x_twitter_feed[0]['url'];
		
		$flickr_links = $node->field_x_link; //[index]url
		$flickr_links_split = $node->field_x_link_f_split;
		$twitter_links = $node->field_x_link_twitter;
		$twitter_links_split = $node->field_x_link_t_split;
		
		$location = $node->field_x_location[0]['safe'];

                            
		// figure out if flickr vs twitter
		$flickr = FALSE; // twitter
		$set = FALSE; // do we have a set/feed
		if (strlen($flickr_set) > 3) {
			$flickr = TRUE;
			$set = TRUE;
		} elseif (strlen($flickr_links[0]['url']) > 3) {
			$flickr = TRUE;
		}
		if (strlen($twitter_feed) > 3) {
			$set = TRUE;
			$twitter_feed = $node->field_x_twitter_feed[0]['fragment'];
		}
		//TODO remove for live
		//print "flickr: " . $flickr . " set? " . $set . "<br/><br/>";
		
		$photo_links = array();
		$twitter_items = array(); //  must also store images
		
if ($display_type == 'Full screen') {
		// ------------------------------------------------------
		// begin full screen display

		
		if ($flickr == TRUE) {
			// api calls here		
			if ($set == TRUE) {
				// get top 10 items from set
				$set_str_pos = strpos($flickr_set, 'sets');
				$set_id = substr($flickr_set, ($set_str_pos + 5));
				if (strpos($set_id, '/') !== FALSE) {
					// TODO refactor later - seems brittle...
					$set_id = str_replace('/', '', $set_id);
				}
				// TODO is json faster??
				$flickr_params = array(
					'api_key'	=> 'e3ae977319974d2c4a777e0224b9dcbf',
					'method'	=> 'flickr.photosets.getPhotos',
					'format'	=> 'php_serial',
					'per_page' => 10, // only want the first 10
					'photoset_id'	=> $set_id,
					'secret' => '843a8efd1cf8ef13',
					'extras' => 'url_o',
				);
				$flickr_encoded_params = array();
				foreach ($flickr_params as $k => $v){
					$flickr_encoded_params[] = urlencode($k).'='.urlencode($v);
				}
				$flickr_url = "http://api.flickr.com/services/rest/?".
						implode('&', $flickr_encoded_params);
				$flickr_rsp = file_get_contents($flickr_url);
				$flickr_rsp_obj = unserialize($flickr_rsp);
				$flickr_set_title = null; // must grab title from first image
				
				// check if valid
				if ($flickr_rsp_obj['stat'] == 'ok') {
					$c = 0; // counter, since we only want the first 10
					print '<div class="inner-slideshow-content">';
					foreach($flickr_rsp_obj['photoset']['photo'] as $key => $flickr_photo) {
						/* TODO 
						the below is template code to get the image descriptions, yet somehow the API does not return them... saving for v2
						$flickr_params = null;
						$flickr_params = array(
							'api_key'	=> 'e3ae977319974d2c4a777e0224b9dcbf',
							'secret' => '843a8efd1cf8ef13',
							'method'	=> 'flickr.photos.getInfo',
							'format'	=> 'php_serial',
							'photo_id'	=> $flickr_photo['id'],
						);
						$flickr_encoded_params = array();
						foreach ($flickr_params as $k => $v){
							$flickr_encoded_params[] = urlencode($k).'='.urlencode($v);
						}
						$flickr_url = "http://api.flickr.com/services/rest/?".
							implode('&', $flickr_encoded_params);
						print "URL inside " . $flickr_url . "<br/>";
						$flickr_rsp2 = file_get_contents($flickr_url);
						$flickr_rsp_obj2 = unserialize($flickr_rsp);
						if ($flickr_rsp_obj2['stat'] == 'ok') {
							// got the full image with data now
							foreach($flickr_rsp_obj['photoset']['photo'] as $key => $flickr_photo_full) {
								print "<br/>------<br/>FULL PHOTO<br/>";
								print_r($flickr_photo_full);
								print "<br/>------<br/>END FULL PHOTO<br/>";
								//print '<div class="inner-slideshow-photo">' . 
								//			'<img src="' . $flickr_photo_full['url_m'] . '" />' .
								//			'</div>';
							}
						} else {
							// TODO implement something if we cant get the full image...
						}
						*/
						$img_title = $flickr_photo['title'];
						$img_w =		 $flickr_photo['width_o'];
						$img_h = 		 $flickr_photo['height_o'];
						$img_url = 			 $flickr_photo['url_o'];
						// make sure we can get to the URL of the full size image before we do anything
						if (strlen($img_url) > 10) {
							$img_ratio = $img_w / $img_h;
							$scale_to_width = 1306;
							$scale_to_height = 614;
							$img_scale_down_w = $img_w / $scale_to_width;
							$img_scale_down_h = $img_h / $scale_to_height;
							$scaled_height = intval($img_h / $img_scale_down_w);
							$crop_dist = intval(($scaled_height - 614) / 2);
							// full screen from mockup is 1306 x 614 to scale
							if ($img_ratio > 1.0) {
								// horizontal
								print '<div class="inner-slideshow-image"><img src="' . $img_url . '" style="width: ' . $scale_to_width 	
									. 'px; height: ' . $scaled_height . 'px;" />' . 
									'<p class="flickr-slide-caption">' . $img_title . '</p>' .
									'<br/>';
							} else {
								// vertical - crop at midpoint
								print '<div class="inner-slideshow-image"><img src="' . $img_url . '" style="width: ' . $scale_to_width 	. 'px; height: ' . $scaled_height . 'px; margin-top: -' . $crop_dist . 'px;" />' . 
								'<p class="flickr-slide-caption">' . $img_title . '</p>' .
								'<br/>';
							}
							print '</div>'; // end image - cant add anything else here because of the overflow clipping needed
							
							
						}
					} // end img foreach
					print '</div>'; // end inner-slideshow-content
					print '<div id="slideshow-caption-line">' .
									'<div id="caption-left">' .
										'<div id="studio-x-location">Studio-X<br/>' . $location . '</div>' .
									'</div>' . // end left
									'<div id="caption-right">' .
										'<div id="title-line"></div>' .
									'</div>' .
								'</div>';


				} else {
					// TODO implement something if not found or invalid set...
				}
			} else {
				// get individual items
				print '<div class="inner-slideshow-content">';
				foreach($flickr_links as $key=>$value) {
					$url_set = $value['url'];
					if (strlen($url_set) > 5) {
						// get the id
						$offset = strlen('http://www.flickr.com/photos/studio_x_new_york_columbia_gsapp/');
						$in_pos = strpos($url_set, '/in/photostream');
						$flickr_id = trim(substr($url_set, $offset, ($in_pos - $offset)));

						// make API call
						$flickr_params = array(
							'api_key'	=> 'e3ae977319974d2c4a777e0224b9dcbf',
							'photo_id' => $flickr_id,
							'method'	=> 'flickr.photos.getSizes',
							'format'	=> 'php_serial',
							'secret' => '843a8efd1cf8ef13',
						);
						$flickr_encoded_params = array();
						foreach ($flickr_params as $k => $v){
							$flickr_encoded_params[] = urlencode($k).'='.urlencode($v);
						}
						$flickr_url = "http://api.flickr.com/services/rest/?".
								implode('&', $flickr_encoded_params);
						$flickr_rsp = file_get_contents($flickr_url);
						$flickr_rsp_obj = unserialize($flickr_rsp);

						// check if valid
						if ($flickr_rsp_obj['stat'] == 'ok') {
							// handle photo display
							foreach($flickr_rsp_obj['sizes']['size'] as $key => $photo_size) {
								if ($photo_size['label'] == 'Original') {
								
									$img_w = $photo_size['width'];
									$img_h = $photo_size['height'];
									$img_url = $photo_size['source'];
									$img_ratio = $img_w / $img_h;
									$scale_to_width = 1306;
									$scale_to_height = 614;
									$img_scale_down_w = $img_w / $scale_to_width;
									$img_scale_down_h = $img_h / $scale_to_height;
									$scaled_height = intval($img_h / $img_scale_down_w);
									$crop_dist = intval(($scaled_height - 614) / 2);
									// TODO double check sizes
									// full screen from mockup is 1306 x 614 to scale
									if ($img_ratio > 1.0) {
										// horizontal
										print '<div class="inner-slideshow-image"><img src="' . $img_url . '" style="width: ' . $scale_to_width 	
											. 'px; height: ' . $scaled_height . 'px;" /></div>';
									} else {
										// vertical - crop at midpoint
										print '<div class="inner-slideshow-image"><img src="' . $img_url . '" style="width: ' . $scale_to_width 	. 'px; height: ' . $scaled_height . 'px; margin-top: -' . $crop_dist . 'px;" /></div>';
									}
								} // else no original size found, so skip
							} // end foreach response loop
						} // end valid response check					
					} // end strlen < 5: ignore
				} // end photo loop
				print '</div>'; // end slideshow inner
				print '<div id="slideshow-caption-line">' .
					'<div id="caption-left">' .
						'<div id="studio-x-location">Studio-X<br/>' . $location . '</div>' .
					'</div>' . // end left
					'<div id="caption-right">' .
						'<div id="title-line">Studio-X / ' . $location . '</div>' . // stock title b/c the sizes API callback does not provide photo titles
					'</div>' .
					'</div>';
			}
		} else {
			// TWITTER
		
			
		
		
			// twitter calls here
			if ($set == TRUE) {
				// get top 10 items from feed
				//http://twitter.com/#!/studioxnyc
				// get handle from feed url
				$handle = substr($twitter_feed, 2);
			/*	$f = file_get_contents('https://api.twitter.com/1/statuses/user_timeline.json?include_entities=true&include_rts=true&screen_name=' . $handle . '&count=10');
			*/
				$f = file_get_contents('https://api.twitter.com/1/statuses/user_timeline.json?include_entities=true&include_rts=true&screen_name=troytherrien&count=5');
				$twitter_data = json_decode($f);
				$profile_image_url = null;
				$profile_name = null;
				
				
				if ((empty($twitter_data) !== TRUE) && 
						(is_array($twitter_data) == TRUE)) {


					print '<div class="inner-slideshow-content">';
					foreach($twitter_data as $key=>$tweet) {
					
						if ($profile_name == null) {
							$profile_name = $tweet->user->name;
						}
					
						$tweet_created = $tweet->created_at;
						$pos_colon = strpos($tweet_created, ':');
						$tweet_created = substr($tweet_created, 0, ($pos_colon - 2));
						
						
						$tweet_text = $tweet->text;
						$media = null;
						if ($profile_image_url == null) {
							$profile_image_url = $tweet->user->profile_image_url;
						}
						$media = $tweet->entities->media;
						if (($media != null) && ($media[0]->type == 'photo')) {
							// only display photos
							// image 500x 500
							// margin-right: 55px;
							//TODO test that these elements are always present!!!
							$img_url = $media[0]->media_url;
							if (strlen($img_url) > 10) {
								
								$sizes = $media[0]->sizes;
								$large = $sizes->large;
								$img_w = $large->w;
								$img_h = $large->h;
								$img_ratio = $img_w / $img_h;
								$scale_factor = null;
								$scale_to_width = 500;
								$scale_to_height = 500;
								$img_scale_down_w = $img_w / $scale_to_width;
								$img_scale_down_h = $img_h / $scale_to_height;
								$scaled_height = intval($img_h / $img_scale_down_w);
							
							
								if ($img_ratio > 1.0) {
									// horizontal
									// scale by h
									$h_f = $img_h / 500;
									//print "HF $h_f<br/>";
									// so what is the width then
									$new_w = intval($img_w / $h_f);
									//print "NW $new_w<br/>";
									$crop_dist = intval(($new_w - 500) / 2);
									print '<div class="tweet-outer"><div class="tweet-inner-img"><div class="twitter-img"><img src="' . $img_url . '" style="width: ' . 		
										$new_w 	
										. 'px; height: 500px; margin-left: -' . $crop_dist . 'px;" /></div><div class="tweet-inner-img-text"><p class="tweet-date">' . $tweet_created . '</p>' . $tweet_text . '<br/></div></div></div>';
								} else {
									// TODO this one!
									// vertical - crop at midpoint
									print '<img src="' . $img_url . '" style="width: ' . $scale_to_width 	. 'px; height: ' . $scaled_height . 'px; margin-top: -' . $crop_dist . 'px;" />';
								}
								
							} else {
								// no img src found so just display as text tweet
								print '<div class="tweet-outer full-size"><div class="tweet-inner"><p class="tweet-date">' . $tweet_created . '</p>' . $tweet_text . '<br/></div></div>';		
							}
								
						} else {
							// just a text tweet
							print '<div class="tweet-outer full-size"><div class="tweet-inner"><p class="tweet-date">' . $tweet_created . '</p>' . $tweet_text . '<br/></div></div>';
						}
					}

					
					print '</div>'; // end inner slideshow
					print '<div id="slideshow-caption-line">' .
					'<div id="caption-left">' .
						'<div id="studio-x-location">Studio-X<br/>' . $location . '</div>' .
					'</div>' . // end left
					'<div id="caption-right">' .
						'<div id="title-line">' . $profile_name . '</div>' . 
					'</div>' .
					'</div>';
				} else {
					// maybe an API issue? TODO what to do if APIs are down. for now: ignore
				}
				
		
			
				
				/*
				
			AmmanLab
			Studio-X-Beijing
			Studio-X-Mumbai
			StudioXNYC
			Studio-X-Rio
			
			https://api.twitter.com/1/statuses/user_timeline.json?include_entities=true&include_rts=true&screen_name=studioxnyc&count=10
			
				*/
				
				
			} else {
				// individual twitter updates
			}
		}


// ------------------------------------------------------
// end full screen display
} else {
// ------------------------------------------------------
// begin split screen display

			print "SPLIT SCREEN ";
			


// ------------------------------------------------------
// end split screen display
}	
		
		
		
    
    
    
    
    
	 ?>
  </div>

  <?php if (!empty($terms) || !empty($links)): ?>
    <footer>
      <?php if ($terms): ?>
        <div class="terms">
          <span><?php print t('Tags: ') ?></span><?php print $terms ?>
        </div>
      <?php endif;?>
      <?php if ($links): ?>
        <div class="links">
          <?php print $links; ?>
        </div>
      <?php endif; ?>
    </footer>
  <?php endif;?>

<?php if (!$page): ?>
  </article> <!-- /.node -->
<?php endif;?>
