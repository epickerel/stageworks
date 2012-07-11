<?php

// get the current URL
function lcwp_curr_url() {
	$pageURL = 'http';
	
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://" . $_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];

	return $pageURL;
}
	

// get file extension from a filename
function lcwp_stringToExt($string) {
	$pos = strrpos($string, '.');
	$ext = strtolower(substr($string,$pos));
	return $ext;	
}


// get filename without extension
function lcwp_stringToFilename($string, $raw_name = false) {
	$pos = strrpos($string, '.');
	$name = substr($string,0 ,$pos);
	if(!$raw_name) {$name = ucwords(str_replace('_', ' ', $name));}
	return $name;	
}


// string to url format // NEW FROM v1.11 for non-latin characters 
function lcwp_stringToUrl($string){
	
	// if already exist at least an option, use the default encoding
	if(!get_option('mg_non_latin_char')) {
		$trans = array("à" => "a", "è" => "e", "é" => "e", "ò" => "o", "ì" => "i", "ù" => "u");
		$string = trim(strtr($string, $trans));
		$string = preg_replace('/[^a-zA-Z0-9-.]/', '_', $string);
		$string = preg_replace('/-+/', "_", $string);	
	}
	
	else {$string = trim(urlencode($string));}
	
	return $string;
}


// normalize a url string
function lcwp_urlToName($string) {
	$string = ucwords(str_replace('_', ' ', $string));
	return $string;	
}


// remove a folder and its contents
function lcwp_remove_folder($path) {
	if($objs = @glob($path."/*")){
		foreach($objs as $obj) {
			@is_dir($obj)? lcwp_remove_folder($obj) : @unlink($obj);
		}
	 }
	@rmdir($path);
	return true;
}


// create youtube and vimeo embed url
function lcwp_video_embed_url($raw_url) {
	if(strpos($raw_url, 'vimeo')) {
		$code = substr($raw_url, (strrpos($raw_url, '/') + 1));
		$url = 'http://player.vimeo.com/video/'.$code.'?title=0&amp;byline=0&amp;portrait=0';	
	}
	elseif(strpos($raw_url, 'youtu.be')) {
		$code = substr($raw_url, (strrpos($raw_url, '/') + 1));
		$url = 'http://www.youtube.com/embed/'.$code.'?rel=0';	
	}
	
	return $url;
}

/////////////////////////////

// get the patterns list 
function mg_patterns_list() {
	$patterns = array();
	$patterns_list = scandir(MG_DIR."/img/patterns");
	
	foreach($patterns_list as $pattern_name) {
		if($pattern_name != '.' && $pattern_name != '..') {
			$patterns[] = $pattern_name;
		}
	}
	return $patterns;	
}


// check if there is at leat one custom option
function mg_cust_opt_exists() {
	$types = array('image', 'img_gallery', 'video', 'audio');
	$exists = false;
	
	foreach($types as $type) {
		if(get_option('mg_'.$type.'_opt') && count(get_option('mg_'.$type.'_opt')) > 0) {$exists = true; break;}	
	}
	return $exists;
}


// sizes array
function mg_sizes() {
	return array(
		'1_1',
		'1_2',
		
		'1_3',
		'2_3',
		
		'1_4',
		'3_4',
		
		'1_5',
		'2_5',
		'3_5',
		'4_5',
		
		'1_6',
		'5_6'
	);
}


// sizes to percents
function mg_size_to_perc($size) {
	switch($size) {
		case '5_6': $perc = 0.83; break;
		case '1_6': $perc = 0.166; break;
		
		case '4_5': $perc = 0.80; break;
		case '3_5': $perc = 0.60; break;
		case '2_5': $perc = 0.40; break;
		case '1_5': $perc = 0.20; break;
		
		case '3_4': $perc = 0.75; break;
		case '1_4': $perc = 0.25; break;
		
		case '2_3': $perc = 0.666; break;
		case '1_3': $perc = 0.333; break;
		
		case '1_2': $perc = 0.50; break;
		default :	$perc = 1; break;
	}
	
	return $perc;
}


// print type options fields
function mg_get_type_opt_fields($type, $post) {
	if(!get_option('mg_'.$type.'_opt')) {return false;}
	
	$copt = '
	<h4>Custom Options</h4>
	<table class="widefat lcwp_table lcwp_metabox_table mg_user_opt_table">';	
	
	foreach(get_option('mg_'.$type.'_opt') as $opt) {
		$val = get_post_meta($post->ID, 'mg_'.$type.'_'.strtolower(lcwp_stringToUrl($opt)), true);
		$copt .= '
		<tr>
          <td class="lcwp_label_td">'.$opt.'</td>
          <td class="lcwp_field_td">
		  	<input type="text" name="mg_'.$type.'_'.strtolower(lcwp_stringToUrl($opt)).'" value="'.$val.'" />
          </td>     
          <td><span class="info"></span></td>
        </tr>
		';
	}
	
	$copt .= '</table>';
	return $copt;
}


// metabox types options
function mg_types_meta_opt($type) {
	
	// img slider
	if($type == 'img_gallery') {
		$opt_arr = array(
			array(
				'validate'	=> array('index'=>'mg_slider_img', 'label'=>'Slider Images')
			)
		);
	}
	
	
	// video
	elseif($type == 'video') {
		$opt_arr = array(
			array(
				'label' 	=> 'Video URL',
				'name'		=> 'mg_video_url',
				'descr'		=> 'Insert the Youtube or Vimeo clean video url',
				'type' 		=> 'text',
				'validate'	=> array('index'=>'mg_video_url', 'label'=>'Video URL')
			)
		);
	}
	
	// audio
	elseif($type == 'audio') {
		$opt_arr = array(
				 array(
					'validate'	=> array('index'=>'mg_audio_tracks', 'label'=>'Tracklist')
			)
		);	
	}
		
	// link
	elseif($type == 'link') {
		$opt_arr = array(
			array(
				'label' 	=> 'Link URL',
				'name'		=> 'mg_link_url',
				'descr'		=> '',
				'type' 		=> 'text',
				'validate'	=> array('index'=>'mg_link_url', 'label'=>'Link URL')
			),
			array(
				'label' 	=> 'Link Target',
				'name'		=> 'mg_link_target',
				'descr'		=> 'where the link will be opened',
				'type' 		=> 'select',
				'options'	=> array('top' => 'In the same page', 'blank' => 'In a new page'),
				'validate'	=> array('index'=>'mg_link_target', 'label'=>'Link target')
			)
		);
	}
	
	else {return false;}
	
	return $opt_arr;	
}


// metabox option generator 
function mg_meta_opt_generator($type, $post) {
	$opt_arr = mg_types_meta_opt($type);
	$opt_data = '<table class="widefat lcwp_table lcwp_metabox_table">';
	
	foreach($opt_arr as $opt) {
		$val = get_post_meta($post->ID, $opt['name'], true);
	
		$opt_data .= '
		<tr>
          <td class="lcwp_label_td">'.$opt['label'].'</td>
          <td class="lcwp_field_td">';
		  
		if($opt['type'] == 'text') {  
			$opt_data .= '<input type="text" name="'.$opt['name'].'" value="'.$val.'" />';
		}
		
		elseif($opt['type'] == 'select') {
			$opt_data .= '<select data-placeholder="Select an option .." name="'.$opt['name'].'" class="chzn-select" tabindex="2">';
			
			foreach($opt['options'] as $key=>$name) {
				($key == $val) ? $sel = 'selected="selected"' : $sel = '';
				$opt_data .= '<option value="'.$key.'" '.$sel.'>'.$name.'</option>';	
			}
			
			$opt_data .= '</select>';
		} 
		
		elseif($opt['type'] == 'checkbox') {
			($val) ? $sel = 'checked="checked"' : $sel = '';
			$opt_data .= '<input type="checkbox" name="'.$opt['name'].'" value="1" class="ip-checkbox" '.$sel.' />';	
		}
		  
		$opt_data .= ' 
		  </td>     
          <td><span class="info">'.$opt['descr'].'</span></td>
        </tr>
		';
	}
	
	return $opt_data . '</table>';
}


// get type options indexes from the main type
function mg_get_type_opt_indexes($type) {
	if($type == 'simple_img' || $type == 'link') {return false;}
	
	if($type == 'single_img') {$copt_id = 'image';}
	else {$copt_id = $type;}

	if(!get_option('mg_'.$copt_id.'_opt')) {return false;}
	
	$indexes = array();
	foreach(get_option('mg_'.$copt_id.'_opt') as $opt) {
		$indexes[] = 'mg_'.$copt_id.'_'.strtolower(lcwp_stringToUrl($opt));
	}
	
	return $indexes;	
}


// prepare the array of custom options not empty for an item
function mg_item_copts_array($type, $post_id) {
	if($type == 'single_img') {$type = 'image';}
	$copts = get_option('mg_'.$type.'_opt');
	
	$arr = array();
	if(is_array($copts)) {
		foreach($copts as $copt) {
			$val = get_post_meta($post_id, 'mg_'.$type.'_'.strtolower(lcwp_stringToUrl($copt)), true);
			
			if($val && $val != '') {
				$arr[$copt] = $val;	
			}
		}
	}
	return $arr;
}


// given the item main type slug - return the name
function item_slug_to_name($slug) {
	$vals = array(
		'simple_img' 	=> 'Single Image (static)',
		'single_img' 	=> 'Single Image',
		'img_gallery' 	=> 'Multiple Images (slider)',
		'video' 		=> 'Youtube/Vimeo Video',
		'audio'			=> 'Audio',
		'link'			=> 'Link',
		'lb_text'		=> 'Custom Content',
		'spacer'		=> 'Spacer'
	);
	return $vals[$slug];	
}


// giving an array of items categories, return the published items
function mg_get_cat_items($cat) {
	if(!$cat) {return false;}
	
	if($cat == 'all') { $args = array('post_type' => 'mg_items', 'posts_per_page' => -1); }
	else {
		$term_data = get_term_by( 'id', $cat, 'mg_item_categories');	
		$args = array('post_type' => 'mg_items', 'mg_item_categories' => $term_data->slug, 'posts_per_page' => -1);		
	}	
	
	$loop = new WP_Query( $args );
	if($loop->have_posts() ) {
		
	  $post_list = array();	
	  while ($loop->have_posts()) {  $loop->the_post();
	  
	  	$post_id = get_the_ID();	
		$featured_img = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
		
		$post_list[] = array(
			'id'	=> $post_id, 
			'title'	=> get_the_title(), 
			'type' 	=> get_post_meta($post_id, 'mg_main_type', true),
			'width'	=> get_post_meta($post_id, 'mg_width', true),
			'height'=> get_post_meta($post_id, 'mg_height', true),
			'img' => $featured_img[0]
		);
	  }
	  
	  return $post_list;
	}
	else { return false; }
}


// given an array of post_id, retrieve the data for the builder
function mg_grid_builder_items_data($items) {
	if(!is_array($items) || count($items) == 0) {return false;}
	
	$items_data = array();
	foreach($items as $item_id) {	
		$items_data[] = array(
			'id'	=> $item_id, 
			'title'	=> get_the_title($item_id), 
			'type' 	=> get_post_meta($item_id, 'mg_main_type', true),
			'width'	=> get_post_meta($item_id, 'mg_width', true),
			'height'=> get_post_meta($item_id, 'mg_height', true)
		);
	}
	
	return $items_data;
}


// get the images from the WP library
function mg_library_images($page = 1) {
	$per_page = 15;
	
	$query_images_args = array(
		'post_type' => 'attachment', 'post_mime_type' =>'image', 'post_status' => 'inherit', 'posts_per_page' => $per_page, 'paged' => $page
	);
	
	$query_images = new WP_Query( $query_images_args );
	$images = array();
	
	foreach ( $query_images->posts as $image) { $images[] = $image->guid; }
	
	// global images number
	$img_num = $query_images->found_posts;
	
	// calculate the total
	$tot_pag = ceil($img_num / $per_page);
	
	// can show more?
	$shown = $per_page * $page;
	($shown >= $img_num) ? $more = false : $more = true; 
	
	return array('img' => $images, 'pag' => $page, 'tot_pag' =>$tot_pag, 'more' => $more, 'tot' => $img_num);
}


// given an array of selected images - returns only existing ones
function mg_existing_sel_img($images) {
	if(is_array($images)) {
		$new_array = array();
		
		foreach($images as $img) {
			$img_path = str_replace(get_bloginfo('wpurl').'/', ABSPATH, $img);
			if( file_exists($img_path)) {	
				$new_array[] = $img;
			}
		}
		
		if(count($new_array) == 0) {return false;}
		else {return $new_array;}
	}
	else {return false;}
}


// get the audio files from the WP library
function mg_library_audio($page = 1) {
	$per_page = 15;
	
	$query_audio_args = array(
		'post_type' => 'attachment', 'post_mime_type' =>'audio', 'post_status' => 'inherit', 'posts_per_page' => $per_page, 'paged' => $page
	);
	
	$query_audio = new WP_Query( $query_audio_args );
	$tracks = array();
	
	foreach ( $query_audio->posts as $audio) { 
		$tracks[] = array(
			'id'	=> $audio->ID,
			'url' 	=> $audio->guid, 
			'title' => $audio->post_title
		);
	}
	
	// global images number
	$track_num = $query_audio->found_posts;
	
	// calculate the total
	$tot_pag = ceil($track_num / $per_page);
	
	// can show more?
	$shown = $per_page * $page;
	($shown >= $track_num) ? $more = false : $more = true; 
	
	return array('tracks' => $tracks, 'pag' => $page, 'tot_pag' =>$tot_pag  ,'more' => $more, 'tot' => $track_num);
}


// given an array of selected tracks - returns only existing ones
function mg_existing_sel_tracks($tracks) {
	if(is_array($tracks)) {
		$new_array = array();
		
		foreach($tracks as $track_id) {
			if( get_the_title($track_id)) {	
				$new_array[] = $track_id;
			}
		}
		
		if(count($new_array) == 0) {return false;}
		else {return $new_array;}
	}
	else {return false;}
}


// get the grid terms data
function mg_grid_terms_data($grid_id, $return = 'html') {
	$terms = get_option('mg_grid_'.$grid_id.'_cats');
	
	if(!$terms) { return false; }
	else {
		$terms_data = array();
		
		$a = 0;
		foreach($terms as $term) {
			$term_data = get_term_by('id', $term, 'mg_item_categories');
			$terms_data[$a] = array('id' => $term, 'name' => $term_data->name, 'slug' => $term_data->slug); 	
			
			$a++;
		}
		
		if($return != 'html') {return $terms_data;}
		else {
			$grid_terms_list = '<a class="mg_cats_selected" rel="all">'.__('All').'</a>';
			
			foreach($terms_data as $term) {
				$grid_terms_list .= '<span>/</span><a rel="'.$term['id'].'">'.$term['name'].'</a>';	
			}
			
			return $grid_terms_list;
		}
	}
}


// get the terms of a grid item - return the CSS class
function mg_item_terms_classes($post_id) {
	$pid_classes = array();
	
	$pid_terms = wp_get_post_terms($post_id, 'mg_item_categories', array("fields" => "ids"));
	foreach($pid_terms as $pid_term) { $pid_classes[] = 'mgc_'.$pid_term; }	
	
	return implode(' ', $pid_classes);	
}


// create the frontend css and js
function mg_create_frontend_css() {	
	ob_start();
	require(MG_DIR.'/frontend_css.php');
	
	$css = ob_get_clean();
	if(trim($css) != '') {
		if(!file_put_contents(MG_DIR.'/css/custom.css', $css, LOCK_EX)) {$error = true;}
	}
	else {
		if(file_exists(MG_DIR.'/css/custom.css'))	{ unlink(MG_DIR.'/css/custom.css'); }
	}
	
	if(isset($error)) {return false;}
	else {return true;}
}


// custom excerpt
function mg_excerpt($string, $max) {
	$num = strlen($string);
	
	if($num > $max) {
		$string = substr($string, 0, $max) . '..';
	}
	
	return $string;
}


///////////////////////////////////////////////////////////////////

// predefined grid styles 
function mg_predefined_styles($style = '') {
	$styles = array(
		// LIGHTS
		'Light - Standard' => array(
			'mg_cells_margin' => 5,
			'mg_cells_img_border' => 4,
			'mg_cells_radius' => 1,
			'mg_cells_border' => 0,
			'mg_cells_shadow' => 1,
			'mg_item_radius' => 4,
			
			'mg_cells_border_color' => '#CCCCCC',
			'mg_img_border_color' => 'rgb(255, 255, 255)',
			'mg_img_border_opacity' => 100,
			'mg_main_overlay_color' => '#FFFFFF',
			'mg_main_overlay_opacity' => 80,
			'mg_second_overlay_color' => '#555555',
			'mg_icons_col' => 'w',
			'mg_overlay_title_color' => '#222222',
			
			'mg_item_overlay_color' => '#FFFFFF',
			'mg_item_overlay_opacity' => 80,
			'mg_item_bg_color' => '#FFFFFF',
			'mg_item_txt_color' => '#222222',
			'mg_item_icons' => 'dark',
			'preview' => 'light_standard.jpg'
		),
	
		'Light - Minimal' => array(
			'mg_cells_margin' => 5,
			'mg_cells_img_border' => 3,
			'mg_cells_radius' => 2,
			'mg_cells_border' => 1,
			'mg_cells_shadow' => 0,
			'mg_item_radius' => 2,
			
			'mg_cells_border_color' => '#CECECE',
			'mg_img_border_color' => 'rgb(255, 255, 255)',
			'mg_img_border_opacity' => 0,
			'mg_main_overlay_color' => '#FFFFFF',
			'mg_main_overlay_opacity' => 80,
			'mg_second_overlay_color' => '#555555',
			'mg_icons_col' => 'w',
			'mg_overlay_title_color' => '#222222',
			
			'mg_item_overlay_color' => '#FFFFFF',
			'mg_item_overlay_opacity' => 80,
			'mg_item_bg_color' => '#FFFFFF',
			'mg_item_txt_color' => '#222222',
			'mg_item_icons' => 'dark',
			'preview' => 'light_minimal.jpg'
		),
		
		'Light - No Border' => array(
			'mg_cells_margin' => 5,
			'mg_cells_img_border' => 0,
			'mg_cells_radius' => 2,
			'mg_cells_border' => 0,
			'mg_cells_shadow' => 1,
			'mg_item_radius' => 4,
			
			'mg_cells_border_color' => '#CCCCCC',
			'mg_img_border_color' => 'rgb(255, 255, 255)',
			'mg_img_border_opacity' => 0,
			'mg_main_overlay_color' => '#FFFFFF',
			'mg_main_overlay_opacity' => 80,
			'mg_second_overlay_color' => '#555555',
			'mg_icons_col' => 'tw',
			'mg_overlay_title_color' => '#222222',
			
			'mg_item_overlay_color' => '#FFFFFF',
			'mg_item_overlay_opacity' => 80,
			'mg_item_bg_color' => '#FFFFFF',
			'mg_item_txt_color' => '#222222',
			'mg_item_icons' => 'dark',
			'preview' => 'light_noborder.jpg'
		),
		
		'Light - Photo Wall' => array(
			'mg_cells_margin' => 0,
			'mg_cells_img_border' => 0,
			'mg_cells_radius' => 0,
			'mg_cells_border' => 0,
			'mg_cells_shadow' => 1,
			'mg_item_radius' => 4,
			
			'mg_cells_border_color' => '#CCCCCC',
			'mg_img_border_color' => 'rgb(255, 255, 255)',
			'mg_img_border_opacity' => 0,
			'mg_main_overlay_color' => '#FFFFFF',
			'mg_main_overlay_opacity' => 80,
			'mg_second_overlay_color' => '#555555',
			'mg_icons_col' => 'tw',
			'mg_overlay_title_color' => '#222222',
			
			'mg_item_overlay_color' => '#FFFFFF',
			'mg_item_overlay_opacity' => 80,
			'mg_item_bg_color' => '#FFFFFF',
			'mg_item_txt_color' => '#222222',
			'mg_item_icons' => 'dark',
			'preview' => 'light_photowall.jpg'
		),
	
		// DARKS
		'Dark - Standard' => array(
			'mg_cells_margin' => 5,
			'mg_cells_img_border' => 4,
			'mg_cells_radius' => 1,
			'mg_cells_border' => 0,
			'mg_cells_shadow' => 1,
			'mg_item_radius' => 4,
			
			'mg_cells_border_color' => '#999999',
			'mg_img_border_color' => 'rgb(55, 55, 55)',
			'mg_img_border_opacity' => 80,
			'mg_main_overlay_color' => '#222222',
			'mg_main_overlay_opacity' => 90,
			'mg_second_overlay_color' => '#bbbbbb',
			'mg_icons_col' => 'g',
			'mg_overlay_title_color' => '#ffffff',
			
			'mg_item_overlay_color' => '#222222',
			'mg_item_overlay_opacity' => 80,
			'mg_item_bg_color' => '#343434',
			'mg_item_txt_color' => '#ffffff',
			'mg_item_icons' => 'light',
			'preview' => 'dark_standard.jpg'
		),
	
		'Dark - Minimal' => array(
			'mg_cells_margin' => 5,
			'mg_cells_img_border' => 4,
			'mg_cells_radius' => 2,
			'mg_cells_border' => 1,
			'mg_cells_shadow' => 0,
			'mg_item_radius' => 2,
			
			'mg_cells_border_color' => '#555555',
			'mg_img_border_color' => 'rgb(55, 55, 55)',
			'mg_img_border_opacity' => 0,
			'mg_main_overlay_color' => '#222222',
			'mg_main_overlay_opacity' => 90,
			'mg_second_overlay_color' => '#bbbbbb',
			'mg_icons_col' => 'g',
			'mg_overlay_title_color' => '#ffffff',
			
			'mg_item_overlay_color' => '#222222',
			'mg_item_overlay_opacity' => 80,
			'mg_item_bg_color' => '#343434',
			'mg_item_txt_color' => '#ffffff',
			'mg_item_icons' => 'light',
			'preview' => 'dark_minimal.jpg'
		),
		
		'Dark - No Border' => array(
			'mg_cells_margin' => 5,
			'mg_cells_img_border' => 0,
			'mg_cells_radius' => 2,
			'mg_cells_border' => 0,
			'mg_cells_shadow' => 1,
			'mg_item_radius' => 4,
			
			'mg_cells_border_color' => '#999999',
			'mg_img_border_color' => 'rgb(55, 55, 55)',
			'mg_img_border_opacity' => 80,
			'mg_main_overlay_color' => '#222222',
			'mg_main_overlay_opacity' => 90,
			'mg_second_overlay_color' => '#bbbbbb',
			'mg_icons_col' => 'g',
			'mg_overlay_title_color' => '#ffffff',
			
			'mg_item_overlay_color' => '#222222',
			'mg_item_overlay_opacity' => 80,
			'mg_item_bg_color' => '#343434',
			'mg_item_txt_color' => '#ffffff',
			'mg_item_icons' => 'light',
			'preview' => 'dark_noborder.jpg'
		),
		
		'Dark - Photo Wall' => array(
			'mg_cells_margin' => 0,
			'mg_cells_img_border' => 0,
			'mg_cells_radius' => 0,
			'mg_cells_border' => 0,
			'mg_cells_shadow' => 1,
			'mg_item_radius' => 4,
			
			'mg_cells_border_color' => '#999999',
			'mg_img_border_color' => 'rgb(55, 55, 55)',
			'mg_img_border_opacity' => 80,
			'mg_main_overlay_color' => '#222222',
			'mg_main_overlay_opacity' => 90,
			'mg_second_overlay_color' => '#bbbbbb',
			'mg_icons_col' => 'g',
			'mg_overlay_title_color' => '#ffffff',
			
			'mg_item_overlay_color' => '#222222',
			'mg_item_overlay_opacity' => 80,
			'mg_item_bg_color' => '#343434',
			'mg_item_txt_color' => '#ffffff',
			'mg_item_icons' => 'light',
			'preview' => 'dark_photowall.jpg'
		),
	);
		
		
	if($style == '') {return $styles;}
	else {return $styles[$style];}	
}
 



///////////////////////////////////////////////////////////////////


// layout builder for the frontend
function mg_frontend_layout($post_id) {
	// post type and layout
	$type = get_post_meta($post_id, 'mg_main_type', true);
	$layout = get_post_meta($post_id, 'mg_layout', true);
	$lb_max_w = (int)get_option('mg_item_maxwidth');
	$res_method = get_post_meta($post_id, 'mg_img_res_method', true);

	// timthumb
	$tt_path = MG_URL . '/classes/timthumb.php';
	
	// canvas color for TT
	(get_option('mg_item_bg_color')) ? $tt_canvas = substr(get_option('mg_item_bg_color'), 1) : $tt_canvas = 'ffffff';
	
	// TT res method fix for old plugin vesrions
	if(!$res_method) {$res_method == 1;}
	
	// maxwidth control
	if($lb_max_w == 0) {$lb_max_w = 960;}
	
	// TT width
	($layout == 'full') ? $tt_w = $lb_max_w : $tt_w = ($lb_max_w * 0.675);
	
	// custom opt
	$cust_opt = mg_item_copts_array($type, $post_id); 

	///////////////////////////
	// types
	
	if($type == 'single_img') {
		$src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
		$max_h = (int)get_post_meta($post_id, 'mg_img_maxheight', true);
		
		if($max_h > 0 && $src[2] > $max_h) {$img_url = $tt_path.'?src='.$src[0].'&w='.$tt_w.'&h='.$max_h.'&cc='.$tt_canvas.'&zc='.$res_method.'&q=100';}
		else {$img_url = $src[0];}

		$featured = '<img src="'.$img_url.'" alt="" />';
	}
	
	elseif($type == 'img_gallery') {
		$src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
		$slider_img = get_post_meta($post_id, 'mg_slider_img', true);
		
		$featured = '
		<div id="mg_slider" class="wmuSlider">
			<div class="wmuSliderWrapper">
		';
		  
		  if(is_array($slider_img)) {
			  foreach($slider_img as $img) {
				  $max_h = (int)get_post_meta($post_id, 'mg_img_maxheight', true);
				  if($max_h > 0) {$s_img_sizes = getimagesize($img);}
				  

				  if(is_array($s_img_sizes) && $max_h > 0 && $s_img_sizes[1] > $max_h) {$img_url = $tt_path.'?src='.$img.'&w='.$tt_w.'&h='.$max_h.'&cc='.$tt_canvas.'&zc='.$res_method.'&q=100';}
				  else {$img_url = $img;}
				  
				  $featured .= '
				  <article>	
					<img src="'.$img_url.'" alt="" />
				  </article>';	
			  }
		  }

		  $featured .= '</div></div>'; 	
	}
		
	elseif($type == 'video') {
		$src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
		$video_url = get_post_meta($post_id, 'mg_video_url', true);
		
		($layout == 'full') ? $w = 960 : $w = (960 * 0.675);
		$h = $w * 0.56;
		
		$featured = '<iframe width="'.$w.'" height="'.$h.'" src="'.lcwp_video_embed_url($video_url).'" frameborder="0" allowfullscreen></iframe>';
	}
	
	elseif($type == 'audio') {
		$src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
		$max_h = (int)get_post_meta($post_id, 'mg_img_maxheight', true);
		
		if($max_h > 0 && $src[2] > $max_h) {$img_url = $tt_path.'?src='.$src[0].'&w='.$tt_w.'&h='.$max_h.'&cc='.$tt_canvas.'&a=t&zc='.$res_method.'&q=100';}
		else {$img_url = $src[0];}
		
		$tracklist = get_post_meta($post_id, 'mg_audio_tracks', true);
		$tot = count($tracklist);
		
		$featured = '<img src="'.$img_url.'" alt="" />';
		
		$featured .= '
		<div id="mg_audio_player_'.$post_id.'" class="jp-jplayer"></div>
	
		<div id="mg_audio_wrap_'.$post_id.'" class="jp-audio" style="display: none;">
			<div class="jp-type-playlist">
				<div class="jp-gui jp-interface">
					<div class="jp-cmd-wrap">';
					
						if($tot > 1) {$featured .= '<a href="javascript:;" class="jp-previous">previous</a>';}
					
						$featured .= '
						<a href="javascript:;" class="jp-play">play</a>
						<a href="javascript:;" class="jp-pause">pause</a>';
						
						if($tot > 1) {$featured .= '<a href="javascript:;" class="jp-next">next</a>';}

						$featured .= '
						<div class="jp-time-holder">
							<div class="jp-current-time"></div> 
							<span>/</span> 
							<div class="jp-duration"></div>
						</div>
						
						<div class="jp-progress">
							<div class="jp-seek-bar">
								<div class="jp-play-bar"></div>
							</div>
						</div>';	
						
						
						$featured .= '
						<div class="jp-volume-group">
							<a href="javascript:;" class="jp-mute" title="mute">mute</a>
							<a href="javascript:;" class="jp-unmute" title="unmute">unmute</a>
							
							<div class="jp-volume-bar">
								<div class="jp-volume-bar-value"></div>
							</div>
						</div>
					</div>';	

				$featured .= '
					<div class="jp-track-title">
						<div class="jp-playlist">
							<ul>
								<li></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>';
		
		// js code
		$featured .= '
		<script type="text/javascript">
		jQuery(function(){
			new jPlayerPlaylist({
				jPlayer: "#mg_audio_player_'.$post_id.'",
				cssSelectorAncestor: "#mg_audio_wrap_'.$post_id.'",
				displayTime: 0
			}, [';
			
			$a = 1;
			foreach($tracklist as $track) {
				$track_data = get_post($track);
				
				($tot > 1) ? $counter = '<em>'.$a.'/'.$tot.'</em>) ' : $counter = ''; 
				
				$track_json[] = '
				{
					title:"'.$counter.addslashes($track_data->post_title).'",
					mp3:"'.$track_data->guid.'"
				}
				';
				
				$a++;
			}

			$featured .= implode(',', $track_json) . '
			], {
				swfPath: "'.MG_URL.'/js/jPlayer/",
				supplied: "mp3"
			});
		});
		</script>
		';
	}
	
	else {
		$src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
		$featured = '';
	}
	
	
	// force the layout for the lightbox custom contents
	if($type == 'lb_text') {$layout = 'full';}
	
	
	///////////////////////////
	// builder	
	?>
    <div id="mg_close"></div>
    <div id="mg_nav"></div>
    
	<div class="mg_layout_<?php echo $layout; ?>">
      <div>
      	<?php if($type != 'lb_text') : ?>
		<div class="mg_item_featured">
			<?php echo $featured; ?>
		</div>
        <?php endif; ?>
		
		<div class="mg_item_content">
			<?php if($layout == 'full' && count($cust_opt) > 0) {echo '<div class="mg_content_left">';} ?>
		
				<h1 class="mg_item_title"><?php echo get_the_title($post_id); ?></h1>
				
				<?php 
				// custom options
				if(count($cust_opt) > 0) {
					echo '<ul class="mg_cust_options">';
					foreach($cust_opt as $copt => $val) {
						echo '<li><span>'.$copt.':</span> '.$val.'</li>';
					}
					echo '</ul>';
				} ?>

			<?php if($layout == 'full' && count($cust_opt) > 0) {echo '</div>';} ?>
			
			<div class="mg_item_text <?php if($layout == 'full' && count($cust_opt) == 0) {echo 'mg_widetext';} ?>">
				<?php echo do_shortcode( wpautop(get_post_field('post_content', $post_id)) ); ?>
            </div>
            
            <?php 
			// SOCIALS
			if(get_option('mg_facebook') || get_option('mg_twitter') || get_option('mg_pinterest')): ?>
              <div id="mg_socials">
            	<ul>
                  <?php if(get_option('mg_facebook')): ?>
                  <li id="mg_fb_share">
					<a onClick="window.open('http://www.facebook.com/sharer.php?s=100&amp;p[title]=<?php echo urlencode(get_the_title($post_id)); ?>&amp;p[summary]=<?php echo urlencode(strip_shortcodes(get_post_field('post_content', $post_id))); ?>&amp;p[url]=<?php echo urlencode(lcwp_curr_url()); ?>&amp;&amp;p[images][0]=<?php echo urlencode($src[0]); ?>','sharer','toolbar=0,status=0,width=548,height=325');" href="javascript: void(0)"><span title="Share it!"></span></a>
                  </li>
                  <?php endif; ?>
                  
                  <?php if(get_option('mg_twitter')): ?>
                  <li id="mg_tw_share">
					<a onClick="window.open('https://twitter.com/share?text=<?php echo urlencode('Check out "'.get_the_title($post_id).'" on '.get_bloginfo('name')); ?>&url=<?php echo urlencode(lcwp_curr_url()); ?>','sharer','toolbar=0,status=0,width=548,height=325');" href="javascript: void(0)"><span title="Tweet it!"></span></a>
                  </li>
                  <?php endif; ?>
                  
                  <?php if(get_option('mg_pinterest')): ?>
                  <li id="mg_pn_share">
                  	<a onClick="window.open('http://pinterest.com/pin/create/button/?url=<?php echo urlencode(lcwp_curr_url()); ?>&media=<?php echo urlencode($src[0]); ?>&description=<?php echo urlencode(get_the_title($post_id)); ?>','sharer','toolbar=0,status=0,width=575,height=330');" href="javascript: void(0)"><span title="Pin it!"></span></a>
                  </li>
                  <?php endif; ?>
                </ul>
                
              </div>
            <?php endif;; ?>
            
			<br style="clear: both;" />
		</div>
      </div>
	</div> 
	<?php
}

?>