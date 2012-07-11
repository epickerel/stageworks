<?php
// SHORCODE TO DISPLAY THE GRID

// [mediagrid] 
function mg_shortcode( $atts, $content = null ) {
	require_once(MG_DIR . '/functions.php');
	
	extract( shortcode_atts( array(
		'cat' => '',
		'filter' => 1,
		'r_width' => 'auto'
	), $atts ) );

	if($cat == '') {return '';}
	
	// init
	$grid = '';
	
	// filter
	if($filter == '1') {
		$grid .= '<div id="mgf_'.$cat.'" class="mg_filter">';
			if(mg_grid_terms_data($cat)) { $grid .= mg_grid_terms_data($cat); }
		$grid .= '</div>';
	}
	
	$grid .= '
	<div class="mg_grid_wrap">
      <div id="mg_grid_'.$cat.'" class="mg_container" rel="'.$r_width.'">';
	
	/////////////////////////
	// grid contents
	
	$items_list = get_option('mg_grid_'.$cat.'_items');
	$items_w = get_option('mg_grid_'.$cat.'_items_width');
	$items_h = get_option('mg_grid_'.$cat.'_items_height');
	
	$a = 0;
	if(!is_array($items_list)) {return '';}
	foreach($items_list as $post_id) {
      	if(!$items_w) {
			$cell_width = get_post_meta($post_id, 'mg_width', true);
			$cell_height = get_post_meta($post_id, 'mg_height', true);
		}
		else {
			$cell_width = $items_w[$a];
			$cell_height = $items_h[$a];	
		}
		
		$main_type = get_post_meta($post_id, 'mg_main_type', true);
		$item_layout = get_post_meta($post_id, 'mg_layout', true);
		
		
		// calculate the thumb img size
		$tt_path = MG_URL . '/classes/timthumb.php';
		$thb_w = 960 * mg_size_to_perc($cell_width);
		$thb_h = 960 * mg_size_to_perc($cell_height);
		
		// featured image src
		$src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
		
		
		////////////////////////////
		// simple image
		if($main_type == 'simple_img') {

			$grid .= '
			<div class="mg_box col'.$cell_width.' row'.$cell_height.' '.mg_item_terms_classes($post_id).'">	
				<div class="img_wrap">';
					$grid .= '<img src="'.$tt_path.'?src='.$src[0].'&w='.$thb_w.'&h='.$thb_h.'" class="thumb" alt="" />';
					
			$grid .= '		
				</div>
			</div>';	
		}
		
		
		////////////////////////////
		// single image
		else if($main_type == 'single_img') {
			
			$grid .= '
			<div class="mg_box mg_transitions col'.$cell_width.' row'.$cell_height.' mg_image mg_closed '.mg_item_terms_classes($post_id).'" rel="pid_'.$post_id.'">	

				<div class="img_wrap">
					<div>';
				
						$grid .= '<img src="'.$tt_path.'?src='.$src[0].'&w='.$thb_w.'&h='.$thb_h.'" class="thumb" alt="" />';
					
						$grid .= '  
						<div class="overlays">
							<div class="overlay"></div>
							<div class="cell_more"><span></span></div>
							<div class="cell_type"><span class="mg_overlay_tit">'.get_the_title($post_id).'</span></div>
						</div>';
					
			$grid .= '</div>		
				</div>
			</div>';
		}
		
		
		////////////////////////////
		// image slider
		else if($main_type == 'img_gallery') {
			$slider_img = get_post_meta($post_id, 'mg_slider_img', true);
			
			$grid .= '
			<div class="mg_box mg_transitions col'.$cell_width.' row'.$cell_height.' mg_gallery mg_closed '.mg_item_terms_classes($post_id).'" rel="pid_'.$post_id.'">	
				
				<div class="img_wrap">
					 <div>';
					 	$grid .= '<img src="'.$tt_path.'?src='.$src[0].'&w='.$thb_w.'&h='.$thb_h.'" class="thumb" alt="" />';
				
						$grid .= '  
						<div class="overlays">
							<div class="overlay"></div>
							<div class="cell_more"><span></span></div>
							<div class="cell_type"><span class="mg_overlay_tit">'.get_the_title($post_id).'</span></div>
						</div>';
					
			$grid .= '</div>		
				</div>
			</div>';
		}
		
		
		////////////////////////////
		// video
		else if($main_type == 'video') {
			$video_url = get_post_meta($post_id, 'mg_video_url', true);
			
			$grid .= '
			<div class="mg_box mg_transitions col'.$cell_width.' row'.$cell_height.' mg_video mg_closed '.mg_item_terms_classes($post_id).'" rel="pid_'.$post_id.'">				
				
				<div class="img_wrap">
					<div>';
					
					$grid .= '<img src="'.$tt_path.'?src='.$src[0].'&w='.$thb_w.'&h='.$thb_h.'" class="thumb" alt="" />';
				
					$grid .= '  
					<div class="overlays">
						<div class="overlay"></div>
						<div class="cell_more"><span></span></div>
						<div class="cell_type"><span class="mg_overlay_tit">'.get_the_title($post_id).'</span></div>
					</div>';

					
			$grid .= '</div>		
				</div>
			</div>';
		}
		
		
		////////////////////////////
		// audio
		else if($main_type == 'audio') {
			$tracklist = get_post_meta($post_id, 'mg_audio_tracks', true);
			
			$grid .= '
			<div class="mg_box mg_transitions col'.$cell_width.' row'.$cell_height.' mg_audio mg_closed '.mg_item_terms_classes($post_id).'" rel="pid_'.$post_id.'">	
	
				<div class="img_wrap">
					<div>';
					
						$grid .= '<img src="'.$tt_path.'?src='.$src[0].'&w='.$thb_w.'&h='.$thb_h.'" class="thumb" alt="" />';
				
						$grid .= '  
						<div class="overlays">
							<div class="overlay"></div>
							<div class="cell_more"><span></span></div>
							<div class="cell_type"><span class="mg_overlay_tit">'.get_the_title($post_id).'</span></div>
						</div>';
					
			$grid .= '</div>		
				</div>
			</div>';
		}
		
		
		////////////////////////////
		// link 
		else if($main_type == 'link') {
			$link_url = get_post_meta($post_id, 'mg_link_url', true);
			$link_target = get_post_meta($post_id, 'mg_link_target', true);
			
			$grid .= '
			<div class="mg_box col'.$cell_width.' row'.$cell_height.' mg_link '.mg_item_terms_classes($post_id).'">	
				<div class="img_wrap">
					<div>';
	
						$grid .= '
						<a href="'.$link_url.'" target="_'.$link_target.'"><img src="'.$tt_path.'?src='.$src[0].'&w='.$thb_w.'&h='.$thb_h.'" class="thumb" alt="" />
						  
						<div class="overlays">
							<div class="overlay"></div>
							<div class="cell_more"><span></span></div>
							<div class="cell_type"><span class="mg_overlay_tit">'.get_the_title($post_id).'</span></div>
						</div>';
					
				$grid .= '</a>
					</div>';
					
			$grid .= '		
				</div>
			</div>';		
		}
		
		
		////////////////////////////
		// lightbox custom content
		else if($main_type == 'lb_text') {
			
			$grid .= '
			<div class="mg_box mg_transitions col'.$cell_width.' row'.$cell_height.' mg_lb_text mg_closed '.mg_item_terms_classes($post_id).'" rel="pid_'.$post_id.'">	

				<div class="img_wrap">
					<div>';
				
						$grid .= '<img src="'.$tt_path.'?src='.$src[0].'&w='.$thb_w.'&h='.$thb_h.'" class="thumb" alt="" />';
					
						$grid .= '  
						<div class="overlays">
							<div class="overlay"></div>
							<div class="cell_more"><span></span></div>
							<div class="cell_type"><span class="mg_overlay_tit">'.get_the_title($post_id).'</span></div>
						</div>';
					
			$grid .= '</div>		
				</div>
			</div>';	
		}
		
		
		
		////////////////////////////
		// spacer 
		else if($main_type == 'spacer') {
			$grid .= '
			<div class="mg_box col'.$cell_width.' row'.$cell_height.' mg_spacer"></div>';		
		}
	
		$a++; // counter for the sizes
	}

	////////////////////////////////

	$grid .= '</div></div>';

	return $grid;
}
add_shortcode('mediagrid', 'mg_shortcode');

?>