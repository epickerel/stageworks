<?php

////////////////////////////////////////////////
////// ADD GRID TERM ///////////////////////////
////////////////////////////////////////////////

function mg_add_grid_term() {
	if(!isset($_POST['grid_name'])) {die('data is missing');}
	$name = $_POST['grid_name'];
	
	$resp = wp_insert_term( $name, 'mg_grids', array( 'slug'=>sanitize_title($name)) );
	
	if(is_array($resp)) {die('success');}
	else {
		$err_mes = $resp->errors['term_exists'][0];
		die($err_mes);
	}
}
add_action('wp_ajax_mg_add_grid', 'mg_add_grid_term');


////////////////////////////////////////////////
////// LOAD GRID LIST //////////////////////////
////////////////////////////////////////////////

function mg_grid_list() {
	$grids = get_terms( 'mg_grids', 'hide_empty=0' );

	// clean term array
	$clean_grids = array();
	
	foreach ( $grids as $grid ) {
		$clean_grids[] = array('id' => $grid->term_id, 'name' => $grid->name);
	}
    
	echo json_encode($clean_grids);
	die();
}
add_action('wp_ajax_mg_get_grids', 'mg_grid_list');


////////////////////////////////////////////////
////// DELETE GRID TERM ////////////////////////
////////////////////////////////////////////////

function mg_del_grid_term() {
	if(!isset($_POST['grid_id'])) {die('data is missing');}
	$id = addslashes($_POST['grid_id']);
	
	$resp = wp_delete_term( $id, 'mg_grids');

	if($resp == '1') {die('success');}
	else {die('error during the grid deletion');}
}
add_action('wp_ajax_mg_del_grid', 'mg_del_grid_term');


////////////////////////////////////////////////
////// DISPLAY GRID  BUILDER ///////////////////
////////////////////////////////////////////////

function mg_grid_builder() {
	require_once(MG_DIR . '/functions.php');
	$tt_path = MG_URL . '/classes/timthumb.php';
	
	if(!isset($_POST['grid_id'])) {die('data is missing');}
	$grid_id = addslashes($_POST['grid_id']);

	// get grid items
	$grid_items = get_option('mg_grid_'.$grid_id.'_items');
	if(!is_array($grid_items)) {$grid_items = array();}

	// item categories list
	$item_cats = get_terms( 'mg_item_categories', 'hide_empty=0' );
	
	// grid items sizes (from v1.1)
	$items_width = get_option('mg_grid_'.$grid_id.'_items_width');
	$items_height = get_option('mg_grid_'.$grid_id.'_items_height');
	
	// cat and page selector
	?>
    <h2></h2>
    
    <div id="mg_grid_builder_cat" class="postbox">
      <h3 class="hndle">Add Grid Items</h3>
      <div class="inside">
    
        <div class="lcwp_mainbox_meta">
          <table class="widefat lcwp_table lcwp_metabox_table">
            <tr>
              <td class="lcwp_label_td"><?php _e("Item Categories"); ?></td>
              <td class="lcwp_field_td">
                  <select data-placeholder="Select item categories .." name="mh_grid_cats" id="mh_grid_cats" class="chzn-select" tabindex="2" style="width: 400px;">
                  <option value="all">All</option>
                    <?php 
                    foreach($item_cats as $cat) {
                        echo '<option value="'.$cat->term_id.'">'.$cat->name.'</option>';
                    }
                    ?>
                  </select>
              </td>     
              <td><span class="info"></span></td>
            </tr>
            
            <tr>
              <td class="lcwp_label_td"><?php _e("Select an Item"); ?></td>
              <td class="lcwp_field_td" id="terms_posts_list">
              	  <?php 
				  $post_list = mg_item_cat_posts('all'); 
				  
				  if(!$post_list) {echo '<span>No items found ..</span>';}
				  else {echo $post_list['dd'];}
				  ?>
              </td>     
              <td>
                <?php if($post_list) echo $post_list['img']; ?>
              
                <div id="add_item_btn" <?php if(!$post_list) echo 'style="display: none;"'; ?>>
                  <input type="button" name="add_item" value="Add" class="button-secondary" />
                  <div style="width: 30px; padding-left: 7px; float: right;"></div>
                </div>
              </td>
            </tr>
          </table>  
        <div>  
      </div>
	</div>
    </div>
    </div>
    
    <div class="postbox">
      <h3 class="hndle">Grid Preview</h3>
      <div class="inside">
      
		<div id="visual_builder_wrap">
        
		<ul id="mg_sortable">
          <?php
          $items_data = mg_grid_builder_items_data($grid_items);
          
          if($items_data) {
			$a = 0;  
            foreach($items_data as $item) {
			  if( get_post_status($item['id']) == 'publish' ) {
			  		
				  if(!$items_width) {	
					  $item_w = $item['width'];
					  $item_h = $item['height'];
				  }
				  else {
					  $item_w = $items_width[$a];
					  $item_h = $items_height[$a]; 
				  }	  
				  
				  // featured image
				  $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($item['id']), 'full'); 
				  $sizes = mg_sizes();
				  
				  
				  // item thumb
				  if($item['type'] == 'spacer') {
					  $item_thumb = '<img src="'.MG_URL. '/img/spacer_icon.png" height="19" width="19" class="thumb" alt="" />';	
				  }
				  else {
					 $item_thumb = '<img src="'.$tt_path.'?src='.$thumb[0].'&w=19&h=19" class="thumb" alt="" />'; 	
				  }	
				  	
				  echo '
				  <li class="mg_box col'.$item_w.' row'.$item_h.'" id="box_'.mt_rand().$item['id'].'" mg-width="'.$item_w.'" mg-height="'.$item_h.'">
					<input type="hidden" name="grid_items[]" value="'.$item['id'].'" />
					<div class="handler" id="boxlabel" name="'.$item['id'].'">
						<div class="del_item"></div>
						<h3>
							'.$item_thumb.'
							'.$item['title'].'
						</h3>
						<p style="padding-top: 6px;">'.item_slug_to_name($item['type']).'</p>
						<p>';
						
						// choose the width
						echo 'Width <select name="items_w[]" value="" class="select_w mg_items_sizes_dd">'; 
							
							foreach($sizes as $size) {
								($size == $item_w) ? $sel = 'selected="selected"' : $sel = '';
								($size == $item['width']) ? $orig = ' (original)' : $orig = '';
								
								echo '<option value="'.$size.'" '.$sel.'>'.str_replace('_', '/', $size).$orig.'</option>';	
							}
						
						echo '</select> <br/>  Height <select class="select_h mg_items_sizes_dd">';
						
							foreach($sizes as $size) {
								($size == $item_h) ? $sel = 'selected="selected"' : $sel = '';
								($size == $item['height']) ? $orig = ' (original)' : $orig = '';
								
								echo '<option value="'.$size.'" '.$sel.'>'.str_replace('_', '/', $size).$orig.'</option>';	
							}

				  echo '</select></p>
					</div>
				  </li>';
			  }
			  $a++;
            }
          }
		  else {echo '<p>No items in the grid ..</p>';}
          ?>

       </ul>
       </div> 
         
	</div>
    </div>
    </div>
    
	<?php
	die();
}
add_action('wp_ajax_mg_grid_builder', 'mg_grid_builder');


////////////////////////////////////////////////
////// GET ITEM CATEGORIES POSTS ///////////////
////////////////////////////////////////////////

function mg_item_cat_posts($fnc_cat = false) {	
	require_once(MG_DIR . '/functions.php');
	$tt_path = MG_URL . '/classes/timthumb.php';

	$cat = $fnc_cat;
	// if is not called directly
	if(!$cat) {
		if(!isset($_POST['item_cats'])) {die('data is missing');}
		$cat = $_POST['item_cats'];
	}

	$post_list = mg_get_cat_items($cat);	
	if(!$post_list) {return false;}
	
    $select = '
	<select data-placeholder="Select an item .." name="mh_grid_item" id="mh_grid_item" class="chzn-select" tabindex="2" style="width: 400px;">';
	 
	 $a = 0;
	 foreach($post_list as $post) {
		($a == 0) ? $sel = '' : $sel = 'style="display: none;"'; 
		
		// create thumbs array 
	   if($post['type'] == 'spacer') {
			$thumbs[] = '<img src="'.MG_URL. '/img/spacer_icon.png" height="23" width="23" alt="'.$post['id'].'" '.$sel.' />';	
		}
		else {
		   $thumbs[] = '<img src="'.$tt_path.'?src='.$post['img'].'&w=23&h=23" alt="'.$post['id'].'" '.$sel.' />'; 	
		}	
		 
		$select .= '<option value="'.$post['id'].'">
			'.$post['title'].' - '.item_slug_to_name($post['type']).'
		</option>'; 
		$a++;
	 }
	 
    $select .= '</select>';
	
	
	// preview thumb images
	if(isset($thumbs)) { $thumbs_block = '<div class="mg_dd_items_preview">' . implode('', $thumbs) . '</div>'; }
	else {$thumbs_block = '';}
	
	// what to return 
	$to_return = array(
		'dd' => $select,
		'img' => $thumbs_block
	);
	
	if(!$fnc_cat) {echo json_encode($to_return); die();}
	else {return $to_return;}
}
add_action('wp_ajax_mg_item_cat_posts', 'mg_item_cat_posts');


////////////////////////////////////////////////
////// ADD AN ITEM TO THE VISUAL BUILDER ///////
////////////////////////////////////////////////

function mg_add_item_to_builder() {	
	require_once(MG_DIR . '/functions.php');
	$tt_path = MG_URL . '/classes/timthumb.php';
	
	if(!isset($_POST['item_id'])) {die('data is missing');}
	$item_id = addslashes($_POST['item_id']);
	
	$sizes = mg_sizes();
	
	$items_data = mg_grid_builder_items_data( array($item_id) );         
	foreach($items_data as $item) {
		$item_w = $item['width'];
		$item_h = $item['height'];
		
		// featured image
		$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($item_id), 'full');
		
		// featured image
		$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($item_id), 'full');
		
		// item thumb
		if($item['type'] == 'spacer') {
		   $item_thumb = '<img src="'.MG_URL. '/img/spacer_icon.png" height="19" width="19" class="thumb" alt="" />';	
		}
		else {
		   $item_thumb = '<img src="'.$tt_path.'?src='.$thumb[0].'&w=19&h=19" class="thumb" alt="" />';	
		}	
		
		
		echo '
		<li class="mg_box col'.$item['width'].' row'.$item['height'].'" id="box_'.mt_rand().$item['id'].'" mg-width="'.$item_w.'" mg-height="'.$item_h.'">
		  <input type="hidden" name="grid_items[]" value="'.$item['id'].'" />
		  <div class="handler" id="boxlabel" name="'.$item['id'].'">
		  	  <div class="del_item"></div>
			  <h3>
			    '.$item_thumb.'
			  	'.$item['title'].'
			  </h3>
			  <p style="padding-top: 6px;">'.item_slug_to_name($item['type']).'</p>
			  <p>';
						
			// choose the width
			echo 'Width <select name="items_w[]" value="" class="select_w mg_items_sizes_dd">'; 
				
				foreach($sizes as $size) {
					($size == $item_w) ? $sel = 'selected="selected"' : $sel = '';
					($size == $item_w) ? $orig = ' (original)' : $orig = '';
					
					echo '<option value="'.$size.'" '.$sel.'>'.str_replace('_', '/', $size).$orig.'</option>';	
				}
			
			echo '</select> <br/>  Height <select class="select_h mg_items_sizes_dd">';
			
				foreach($sizes as $size) {
					($size == $item_h) ? $sel = 'selected="selected"' : $sel = '';
					($size == $item_h) ? $orig = ' (original)' : $orig = '';
					
					echo '<option value="'.$size.'" '.$sel.'>'.str_replace('_', '/', $size).$orig.'</option>';	
				}
	
	  echo '</select></p>
		  </div>
		</li>';	
			
	}
	
	die();	
}
add_action('wp_ajax_mg_add_item_to_builder', 'mg_add_item_to_builder');



////////////////////////////////////////////////
////// SAVE THE GRID ITEMS /////////////////////
////////////////////////////////////////////////

function mg_save_grid() {	
	require_once(MG_DIR . '/functions.php');
	
	if(!isset($_POST['grid_id'])) {die('data is missing');}
	$grid_id = addslashes($_POST['grid_id']);
	
	if(!isset($_POST['items_list'])) {die('data is missing');}
	$items_list = $_POST['items_list'];
	
	if(!isset($_POST['items_width'])) {die('data is missing');}
	$items_width = $_POST['items_width'];
	
	if(!isset($_POST['items_height'])) {die('data is missing');}
	$items_height = $_POST['items_height'];
	
	
	// save the items
	$key = 'mg_grid_'.$grid_id.'_items';
	if(!get_option($key)) { add_option($key, '255', '', 'yes'); }
	update_option($key, $items_list);	
	
	
	// save the sizes
	$key = 'mg_grid_'.$grid_id.'_items_width';
	if(!get_option($key)) { add_option($key, '255', '', 'yes'); }
	update_option($key, $items_width);	
	
	$key = 'mg_grid_'.$grid_id.'_items_height';
	if(!get_option($key)) { add_option($key, '255', '', 'yes'); }
	update_option($key, $items_height);	
	
	
	// save the terms list for the posts
	$terms_array = array();
	foreach($items_list as $post_id) {
		$pid_terms = wp_get_post_terms($post_id, 'mg_item_categories', array("fields" => "ids"));
		foreach($pid_terms as $pid_term) { $terms_array[] = $pid_term; }	
	}
	$terms_array = array_unique($terms_array);
	
	if(!get_option('mg_grid_'.$grid_id.'_cats')) { add_option('mg_grid_'.$grid_id.'_cats', '255', '', 'yes'); }
	update_option('mg_grid_'.$grid_id.'_cats', $terms_array);	
							
	echo 'success';
	die();				
}
add_action('wp_ajax_mg_save_grid', 'mg_save_grid');


//////////////////////////////

////////////////////////////////////////////////
////// MEDIA IMAGE PICKER FOR SLIDERS //////////
////////////////////////////////////////////////

function mg_img_picker() {	
	require_once(MG_DIR . '/functions.php');
	$tt_path = MG_URL . '/classes/timthumb.php'; 
	
	if(!isset($_POST['page'])) {$page = 1;}
	$page = (int)addslashes($_POST['page']);
	$img_data = mg_library_images($page);
	
	echo '<ul>';
	
	if($img_data['tot'] == 0) {echo '<p>No images found .. </p>';}
	else {
		foreach($img_data['img'] as $img) {
			echo '<li><img src="'.$tt_path.'?src='.$img.'&w=90&h=90" id="'.$img.'" /></li>';	
		}
	}
	
	echo '
	</ul>
	<br class="lcwp_clear" />
	<table cellspacing="0" cellpadding="5" border="0" width="100%">
		<tr>
			<td style="width: 40%;">';			
			if($page > 1)  {
				echo '<input type="button" class="mg_img_pick_back button-secondary" id="slp_'. ($page - 1) .'" name="mgslp_p" value="&laquo; Previous images" />';
			}
			
		echo '</td><td style="width: 20%; text-align: center;">';
		
			if($img_data['tot'] > 0 && $img_data['tot_pag'] > 1) {
				echo '<em>page '.$img_data['pag'].' of '.$img_data['tot_pag'].'</em>';	
			}
			
		echo '</td><td style="width: 40%; text-align: right;">';
			if($img_data['more'] != false)  {
				echo '<input type="button" class="mg_img_pick_next button-secondary" id="slp_'. ($page + 1) .'" name="mgslp_n" value="Next images &raquo;" />';
			}
		echo '</td>
		</tr>
	</table>
	';

	die();
}
add_action('wp_ajax_mg_img_picker', 'mg_img_picker');


///////////////////////////////////////////////////
////// MEDIA IMAGE PICKER - SELECTED RELOAD ///////
///////////////////////////////////////////////////
function mg_sel_img_reload() {	
	require_once(MG_DIR . '/functions.php');
	$tt_path = MG_URL . '/classes/timthumb.php'; 
	
	if(!isset($_POST['images'])) { $images = array();}
	else { $images = $_POST['images'];}
	
	// get the titles and recreate tracks
	$images = mg_existing_sel_img($images);
	$new_img = '';
	
	if(!$images) {$new_img = '<p>No images selected .. </p>';}
	else {
		foreach($images as $img) {
			$new_img .= '
			<li>
				<input type="hidden" name="mg_slider_img[]" value="'.$img.'" />
				<img src="'.$tt_path.'?src='.$img.'&w=90&h=90" />
				<span title="remove image"></span>
			</li>
			';	
		}
	}
	
	echo $new_img;
	die();
}
add_action('wp_ajax_mg_sel_img_reload', 'mg_sel_img_reload');


////////////////////////////////////////////////
////// MEDIA AUDIO PICKER  /////////////////////
////////////////////////////////////////////////

function mg_audio_picker() {	
	require_once(MG_DIR . '/functions.php');
	$tt_path = MG_URL . '/classes/timthumb.php'; 
	
	if(!isset($_POST['page'])) {$page = 1;}
	$page = (int)addslashes($_POST['page']);
	$audio_data = mg_library_audio($page);
	
	echo '<ul>';
	
	if($audio_data['tot'] == 0) {echo '<p>No audio files found .. </p>';}
	else {
		foreach($audio_data['tracks'] as $track) {
			echo '
			<li>
				<img src="'. MG_URL .'/img/audio_icon.png" id="'.$track['id'].'" />
				<p title="'.$track['title'].'">'.mg_excerpt($track['title'], 25).'</p>
			</li>';
		}
	}
	
	echo '
	</ul>
	<br class="lcwp_clear" />
	<table cellspacing="0" cellpadding="5" border="0" width="100%">
		<tr>
			<td style="width: 40%;">';			
			if($page > 1)  {
				echo '<input type="button" class="mg_audio_pick_back button-secondary" id="slp_'. ($page - 1) .'" name="mgslp_p" value="&laquo; Previous tracks" />';
			}
			
		echo '</td><td style="width: 20%; text-align: center;">';
		
			if($audio_data['tot'] > 0 && $audio_data['tot_pag'] > 1) {
				echo '<em>page '.$audio_data['pag'].' of '.$audio_data['tot_pag'].'</em>';	
			}
			
		echo '</td><td style="width: 40%; text-align: right;">';
			if($audio_data['more'] != false)  {
				echo '<input type="button" class="mg_audio_pick_next button-secondary" id="slp_'. ($page + 1) .'" name="mgslp_n" value="Next tracks &raquo;" />';
			}
		echo '</td>
		</tr>
	</table>
	';

	die();
}
add_action('wp_ajax_mg_audio_picker', 'mg_audio_picker');


///////////////////////////////////////////////////
////// MEDIA AUDIO PICKER - SELECTED RELOAD ///////
///////////////////////////////////////////////////
function mg_sel_audio_reload() {	
	require_once(MG_DIR . '/functions.php');
	
	if(!isset($_POST['tracks'])) { $tracks = array();}
	else { $tracks = $_POST['tracks'];}
	
	$tracks = mg_existing_sel_tracks($tracks);
	
	// get the titles and recreate tracks
	$new_tracks = '';
	if(!$tracks) {$new_tracks = '<p>No tracks selected .. </p>';}
	else {
		foreach($tracks as $track_id) {
			$title = html_entity_decode(get_the_title($track_id), ENT_NOQUOTES, 'UTF-8');
			
			if($title) {
				$new_tracks .= '
				<li>
					<input type="hidden" name="mg_audio_tracks[]" value="'.$track_id.'" />
					<img src="'.MG_URL.'/img/audio_icon.png" />
					<span title="remove track"></span>
					<p>'.$title.'</p>
				</li>
				';
			}
		}
	}
	
	echo $new_tracks;
	die();
}
add_action('wp_ajax_mg_sel_audio_reload', 'mg_sel_audio_reload');


////////////////////////////////////////////////////////////

////////////////////////////////////////////////
////// SET PREDEFINED GRID STYLES //////////////
////////////////////////////////////////////////

function mg_set_predefined_style() {
	if(!isset($_POST['style'])) {die('data is missing');}
	$style = $_POST['style'];
	
	require_once(MG_DIR . '/functions.php');
	
	$style_data = mg_predefined_styles($style);
	
	// additive settings if is a fresh installation
	if(!get_option('mg_item_width')) {
		$style_data['mg_item_width'] = 70;
		$style_data['mg_item_maxwidth'] = 960;	
	}
	
	// set option values
	foreach($style_data as $opt => $val) {
		if($opt != 'preview') {
			if(!get_option($opt)) { add_option($opt, '255', '', 'yes'); }
			update_option($opt, $val);				
		}
	}
	
	mg_create_frontend_css();
	
	die();
}
add_action('wp_ajax_mg_set_predefined_style', 'mg_set_predefined_style');


////////////////////////////////////////////////////////////

////////////////////////////////////////////////
////// FRONTEND OVERLAY LAYOUT AND CONTENT /////
////////////////////////////////////////////////

function mg_overlay_layout() {
	if(isset($_POST['mg_type']) && $_POST['mg_type'] == 'mg_overlay_layout') {
		require_once(MG_DIR . '/functions.php');
	
		if(!isset($_POST['pid']) || !filter_var($_POST['pid'], FILTER_VALIDATE_INT)) {die('data is missing');}
		$pid = addslashes($_POST['pid']);
		
		mg_frontend_layout($pid);
		die();
	}
}
add_action('init', 'mg_overlay_layout');
?>