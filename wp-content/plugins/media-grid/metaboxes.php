<?php
// METABOXES FOR THE ITEMS

// register
function mg_register_metaboxes() {
	add_meta_box('mg_item_size_box', 'Item Sizes (optional)', 'mg_item_size_box', 'mg_items', 'side', 'low');
	add_meta_box('mg_item_opt_box', 'Item Options', 'mg_item_opt_box', 'mg_items', 'normal', 'default');
}
add_action('admin_init', 'mg_register_metaboxes');


//////////////////////////
// ITEM SIZE

function mg_item_size_box() {
	require_once(MG_DIR . '/functions.php');	
	global $post;
	
	$width = get_post_meta($post->ID, 'mg_width', true);
	$height = get_post_meta($post->ID, 'mg_height', true);
	
	if(!$width) {
		$width = '1_4';
		$height = '1_4';	
	}
	
	// array of sizes 
	$vals = mg_sizes();
	?>
    <div class="lcwp_sidebox_meta">
        <div class="misc-pub-section">
          <label>Item Width</label>
          <select data-placeholder="Select a size .." name="mg_width" class="chzn-select" tabindex="2">
            <?php 
			foreach($vals as $val) {
				($val == $width) ? $sel = 'selected="selected"' : $sel = '';
				echo '<option value="'.$val.'" '.$sel.'>'. str_replace('_', '/', $val) .' of the container width</option>'; 
			}
			?>
          </select> 
        </div>
        
        <div class="misc-pub-section-last">
          <label>Item Height</label>
          <select data-placeholder="Select a size .." name="mg_height" class="chzn-select" tabindex="2">
            <?php 
			foreach($vals as $val) {
				($val == $height) ? $sel = 'selected="selected"' : $sel = '';
				echo '<option value="'.$val.'" '.$sel.'>'. str_replace('_', '/', $val) .' of the container width</option>'; 
			}
			?>
          </select> 
        </div>
    </div>   
    <?php	
	// create a custom nonce for submit verification later
    echo '<input type="hidden" name="mg_item_noncename" value="' . wp_create_nonce(__FILE__) . '" />';
	
	return true;	
}


//////////////////////////
// ITEM OPTIONS

function mg_item_opt_box() {
	require_once(MG_DIR . '/functions.php');
	global $post;
	
	$main_type = get_post_meta($post->ID, 'mg_main_type', true);
	
	$item_layout = get_post_meta($post->ID, 'mg_layout', true);
	$img_maxheight = get_post_meta($post->ID, 'mg_img_maxheight', true);
	$resize_method = get_post_meta($post->ID, 'mg_img_res_method', true);
	
	$data_array = get_post_meta($post->ID, 'mg_data_array', true);

	// array of types
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
	?>
    <div class="lcwp_mainbox_meta">
      <table class="widefat lcwp_table lcwp_metabox_table">
        <tr>
          <td class="lcwp_label_td"><?php _e("Item Type" ); ?></td>
          <td class="lcwp_field_td">
              <select data-placeholder="Select a size .." name="mg_main_type" id="mg_main_type" class="chzn-select" tabindex="2">
                <?php 
                foreach($vals as $key => $val) {
                    ($key == $main_type) ? $sel = 'selected="selected"' : $sel = '';
                    echo '<option value="'.$key.'" '.$sel.'>'.$val.'</option>'; 
                }
                ?>
              </select>
          </td>     
          <td><span class="info">Choose the item type</span></td>
        </tr>
      </table>  
      
      <div id="mg_layout_wrap" 
	  <?php if(!$main_type || $main_type=='simple_img' || $main_type=='link') echo 'style="display: none;"' ?>>
        <table class="widefat lcwp_table lcwp_metabox_table">
          <tr>
            <td class="lcwp_label_td"><?php _e("Item Layout" ); ?></td>
            <td class="lcwp_field_td">
                <select data-placeholder="Select a layout .." name="mg_layout" id="mg_layout" class="chzn-select" tabindex="2">
                  <option value="full" <?php if($item_layout == 'full') echo 'selected="selected"';?>>Full Width</option>
                  <option value="side" <?php if($item_layout == 'side') echo 'selected="selected"';?>>Text on side</option>
                </select>
            </td>     
            <td><span class="info">Set the item layout when it's active</span></td>
          </tr>
        </table>  
      </div>
      
      
      <div id="mg_img_maxheight_wrap" 
	  <?php if(!$main_type || ($main_type!='single_img' && $main_type!='img_gallery' && $main_type!='audio')) echo 'style="display: none;"' ?>>
      	<h4>Full-size image control</h4>
        <table class="widefat lcwp_table lcwp_metabox_table">
          <tr>
            <td class="lcwp_label_td"><?php _e("Full image max-height" ); ?></td>
            <td class="lcwp_field_td"><input type="text" name="mg_img_maxheight" value="<?php echo ((int)$img_maxheight == 0) ? '' : $img_maxheight; ?>" style="width: 50px;" /> px</td>     
            <td><span class="info">Leave blank to use the full-size image</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td"><?php _e("Full image resize method" ); ?></td>
            <td class="lcwp_field_td">
                <select data-placeholder="Select a method .." name="mg_img_res_method" id="mg_img_res_method" class="chzn-select" tabindex="2">
                  <option value="1" <?php if($resize_method == '1') echo 'selected="selected"';?>>Resize and crop</option>
                  <option value="2" <?php if($resize_method == '2') echo 'selected="selected"';?>>Standard resize</option>
                </select>
            </td>     
            <td><span class="info">Set the resize method for the full size image</span></td>
          </tr>
        </table>  
      </div>
      
      
     <?php // USER CUSTOM OPTIONS ////////// ?> 
      <div id="mg_cust_opt_wrap">
      	<div id="mg_cust_opt_img" 
		<?php if($main_type!='single_img') {echo 'style="display: none;"';} ?>>
        	<?php echo mg_get_type_opt_fields('image', $post); ?>
        </div>
        
        <div id="mg_cust_opt_img_gallery" 
		<?php if($main_type!='img_gallery') {echo 'style="display: none;"';} ?>>
        	<?php echo mg_get_type_opt_fields('img_gallery', $post); ?>
        </div>
        
        <div id="mg_cust_opt_video" <?php if($main_type!='video') {echo 'style="display: none;"';} ?>>
        	<?php echo mg_get_type_opt_fields('video', $post); ?>
        </div>
        
        <div id="mg_cust_opt_audio" <?php if($main_type!='audio') {echo 'style="display: none;"';} ?>>
        	<?php echo mg_get_type_opt_fields('audio', $post); ?>
        </div>
      </div>
      
      
      <?php // TYPE OPTIONS ////////// ?> 
      <div id="mg_builder_wrap">
          <?php // image gallery builder ?>
          <div id="mg_builder_img_gallery" <?php if(!$main_type || $main_type != 'img_gallery') {echo 'style="display: none;"';} ?>>	
            <?php 
			$tt_path = MG_URL . '/classes/timthumb.php'; 
			$images = mg_existing_sel_img(get_post_meta($post->ID, 'mg_slider_img', true)); 
			?>
            
            <h4>Slider Images</h4>
            <div id="gallery_img_wrap">
            	<ul>
            	<?php 
				if(is_array($images)) {
					foreach($images as $img) {
						echo '
						<li>
							<input type="hidden" name="mg_slider_img[]" value="'.$img.'" />
							<img src="'.$tt_path.'?src='.$img.'&w=90&h=90" />
							<span title="remove image"></span>
						</li>';			
					}
				}
				else {echo '<p>No images selected .. </p>';}
				?>
            	</ul>	
            	<br class="lcwp_clear">
            </div>
            <div style="clear: both; height: 20px;"></div>
            
            <h4>Choose the images <span class="mg_TB mg_upload_img add-new-h2">Manage Images</span></h4>
            <div id="gallery_img_picker"></div>	
          </div>
          
          <?php // video builder ?>
          <div id="mg_builder_video" <?php if(!$main_type || $main_type != 'video') {echo 'style="display: none;"';} ?>>
              <h4>Video Options</h4>
              <?php echo mg_meta_opt_generator('video', $post); ?>
          </div>
          
          <?php // audio builder ?>
          <div id="mg_builder_audio" <?php if(!$main_type || $main_type != 'audio') {echo 'style="display: none;"';} ?>>
			 <?php 
              $tt_path = MG_URL . '/classes/timthumb.php'; 
              $tracks = mg_existing_sel_tracks(get_post_meta($post->ID, 'mg_audio_tracks', true)); 
              ?>
              
              <h4>Tracklist</h4>
              <div id="audio_tracks_wrap">
                  <ul>
                  <?php
                  if(is_array($tracks)) {
                      foreach($tracks as $track_id) {
						  $track_title =  html_entity_decode(get_the_title($track_id), ENT_NOQUOTES, 'UTF-8');
                          echo '
						  <li>
							  <input type="hidden" name="mg_audio_tracks[]" value="'. $track_id .'" />
							  <img src="'. MG_URL .'/img/audio_icon.png" />
							  <span title="remove track"></span>
							  <p title="'.$track_title.'">'.mg_excerpt($track_title, 25).'</p>
						  </li>';			
                      }
                  }
				  else {echo '<p>No tracks selected .. </p>';}
				  
				  
                  ?>
                  </ul>	
                  <br class="lcwp_clear" />
              </div>
              <div style="clear: both; height: 20px;"></div>
              
              <h4>Choose the tracks <span class="mg_TB mg_upload_audio add-new-h2">Manage Tracks</span></h4>
              <div id="audio_tracks_picker"></div>	
          </div>
          
          <?php // link builder ?>
          <div id="mg_builder_link" <?php if(!$main_type || $main_type != 'link') {echo 'style="display: none;"';} ?>>
              <h4>Link Options</h4>
              <?php echo mg_meta_opt_generator('link', $post); ?>
          </div>
      </div>
    </div>
    
    <?php // ////////////////////// ?>
    
    <?php // SCRIPTS ?>
	<script src="<?php echo MG_URL; ?>/js/functions.js" type="text/javascript"></script>
    <script src="<?php echo MG_URL; ?>/js/chosen/chosen.jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo MG_URL; ?>/js/iphone_checkbox/iphone-style-checkboxes.js" type="text/javascript"></script>
    
    <script type="text/javascript">
	jQuery(document).ready(function($) {
		////////////////////////
		// custom file uploader for gallery and audio
		mg_TB = 0;
		
		// open tb and hide tabs
		jQuery('.mg_TB').live('click', function() {
			mg_TB = 1;
			
			if( jQuery(this).hasClass('mg_upload_img') ) {mg_TB_type = 'img';}
			else {mg_TB_type = 'audio';}
			
			post_id = jQuery('#post_ID').val();
			
			if(mg_TB_type == 'img') {
				tb_show('', '<?php echo admin_url(); ?>media-upload.php?post_id='+post_id+'&amp;type=image&amp;TB_iframe=true');
			}
			else {
				tb_show('', '<?php echo admin_url(); ?>media-upload.php?post_id='+post_id+'&amp;type=audio&amp;TB_iframe=true');	
			}
			
			setInterval(function() {
				if(mg_TB == 1) {
					if( jQuery('#TB_iframeContent').contents().find('#tab-type_url').is('hidden') ) { return false;	}
					
					jQuery('#TB_iframeContent').contents().find('#tab-type_url').hide();
					jQuery('#TB_iframeContent').contents().find('#tab-gallery').hide();
				}
			}, 1);
		});

		jQuery(window).bind('tb_unload', function() {
			if(mg_TB == 1) {
				if(mg_TB_type == 'img') { 
					mg_load_img_picker(1); 
					mg_sel_img_reload();
				}
				else {
					mg_load_audio_picker(1);	
					mg_sel_tracks_reload();
				}
				
				mg_TB = 0;		
			}
		});

		
		////////////////////////
		// audio
		mg_load_audio_picker(1);
		
		// reload the selected tracks to refresh their titles
		function mg_sel_tracks_reload() {
			var sel_tracks = jQuery.makeArray();	
			
			jQuery('#audio_tracks_wrap li').each(function() {
                var track_id = jQuery(this).children('input').val();
           		sel_tracks.push(track_id);
			});
			
			jQuery('#audio_tracks_wrap ul').html('<div style="height: 30px;" class="lcwp_loading"></div>');
			
			var data = {
				action: 'mg_sel_audio_reload',
				tracks: sel_tracks
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#audio_tracks_wrap ul').html(response);
			});	
		}
		
		
		// change tracks picker page
		jQuery('.mg_audio_pick_back, .mg_audio_pick_next').live('click', function() {
			var page = jQuery(this).attr('id').substr(4);
			mg_load_audio_picker(page);
		});
		
		// load audio tracks picker
		function mg_load_audio_picker(page) {
			var data = {
				action: 'mg_audio_picker',
				page: page
			};
			
			jQuery('#audio_tracks_picker').html('<div style="height: 30px;" class="lcwp_loading"></div>');
			
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#audio_tracks_picker').html(response);
			});	
			
			return true;
		}
		
		// add audio track
		jQuery('#audio_tracks_picker li').live('click', function() {
			var track_id = jQuery(this).children('img').attr('id');
			var track_tit = jQuery(this).children('p').text();	
			
			if( jQuery('#audio_tracks_wrap ul > p').size() > 0 ) {jQuery('#audio_tracks_wrap ul').empty();}
			
			jQuery('#audio_tracks_wrap ul').append('\
			<li>\
				<input type="hidden" name="mg_audio_tracks[]" value="'+ track_id +'" />\
				<img src="<?php echo MG_URL . '/img/audio_icon.png'; ?>" />\
				<span title="remove track"></span>\
				<p>'+ track_tit +'</p>\
			</li>');
			
			mg_sort();
		});
		

		////////////////////////
		// images
		mg_load_img_picker(1);
		
		// reload the selected images to check changes
		function mg_sel_img_reload() {
			var sel_img = jQuery.makeArray();	
			
			jQuery('#gallery_img_wrap li').each(function() {
                var img_id = jQuery(this).children('input').val();
           		sel_img.push(img_id);
			});
			
			jQuery('#gallery_img_wrap ul').html('<div style="height: 30px;" class="lcwp_loading"></div>');
			
			var data = {
				action: 'mg_sel_img_reload',
				images: sel_img
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#gallery_img_wrap ul').html(response);
			});	
		}
		
		
		// change slider imges picker page
		jQuery('.mg_img_pick_back, .mg_img_pick_next').live('click', function() {
			var page = jQuery(this).attr('id').substr(4);
			mg_load_img_picker(page);
		});
		
		// load slider images picker
		function mg_load_img_picker(page) {
			var data = {
				action: 'mg_img_picker',
				page: page
			};
			
			jQuery('#gallery_img_picker').html('<div style="height: 30px;" class="lcwp_loading"></div>');
			
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#gallery_img_picker').html(response);
			});	
			
			return true;
		}
		
		// add slider images
		jQuery('#gallery_img_picker li').live('click', function() {
			var img_url = jQuery(this).children('img').attr('id');
			
			if( jQuery('#gallery_img_wrap ul > p').size() > 0 ) {jQuery('#gallery_img_wrap ul').empty();}
			
			jQuery('#gallery_img_wrap ul').append('\
			<li>\
				<input type="hidden" name="mg_slider_img[]" value="'+ img_url +'" />\
				<img src="<?php echo $tt_path; ?>?src='+ img_url +'&w=90&h=90" />\
				<span title="remove image"></span>\
			</li>');
			
			mg_sort();
		});

		
		////////////////////////
		// images & audio
		// remove
		jQuery('#gallery_img_wrap ul li span, , #audio_tracks_wrap ul li span').live('click', function() {
			jQuery(this).parent().remove();	
			
			if( jQuery('#gallery_img_wrap ul li').size() == 0 ) {jQuery('#gallery_img_wrap ul').html('<p>No images selected .. </p>');}
			if( jQuery('#audio_tracks_wrap ul li').size() == 0 ) {jQuery('#audio_tracks_wrap ul').html('<p>No tracks selected .. </p>');}
		});
		
		
		// sort
		function mg_sort() { 
			jQuery( "#gallery_img_wrap ul, #audio_tracks_wrap ul" ).sortable();
			jQuery( "#gallery_img_wrap ul, #audio_tracks_wrap ul" ).disableSelection();
		}
		mg_sort();
		
		
		////////////////////////
		// toggle
		jQuery('#mg_main_type').live("change", function() {
			var main_type = jQuery(this).val();
			
			// layout toggle
			if(main_type != 'simple_img' && main_type != 'link' && main_type != 'text') { jQuery('#mg_layout_wrap').slideDown(); }
			else { jQuery('#mg_layout_wrap').slideUp(); }
			
			// full img maxheiht toggle
			if(main_type != 'single_img' && main_type != 'img_gallery' && main_type != 'audio' && main_type != 'spacer') { jQuery('#mg_img_maxheight_wrap').slideUp(); }
			else { jQuery('#mg_img_maxheight_wrap').slideDown(); }
			
			// main opt toggle
			jQuery('#mg_cust_opt_wrap > div').each(function() {
				if(main_type == 'single_img') {var copt_id = 'img';}
				else {var copt_id = main_type;}

				if( jQuery(this).attr('id') == 'mg_cust_opt_' + copt_id) { jQuery(this).slideDown(); }
				else { jQuery(this).slideUp(); }
			});
				
			
			// type builder toggle
			jQuery('#mg_builder_wrap > div').each(function() {
                if( jQuery(this).attr('id') == 'mg_builder_' + main_type) { jQuery(this).slideDown(); }
				else { jQuery(this).slideUp(); }
            });
			
			// fix for chosen overflow
			jQuery('#wpbody-content').css('overflow', 'auto');
		});
	});
	</script>
       
    <?php	
	return true;	
}



//////////////////////////
// SAVING METABOXES

function mg_items_meta_save($post_id) {
	if(isset($_POST['mg_item_noncename'])) {
		// authentication checks
		if (!wp_verify_nonce($_POST['mg_item_noncename'], __FILE__)) return $post_id;

		// check user permissions
		if ($_POST['post_type'] == 'page') {
			if (!current_user_can('edit_page', $post_id)) return $post_id;
		}
		else {
			if (!current_user_can('edit_post', $post_id)) return $post_id;
		}
		
		require_once(MG_DIR.'/functions.php');
		require_once(MG_DIR.'/classes/simple_form_validator.php');
				
		$validator = new simple_fv;
		$indexes = array();
		
		// sizes
		$indexes[] = array('index'=>'mg_width', 'label'=>'Item Width');
		$indexes[] = array('index'=>'mg_height', 'label'=>'Item Height');
		
		// main type and layout
		$indexes[] = array('index'=>'mg_main_type', 'label'=>'Item Type');
		$indexes[] = array('index'=>'mg_layout', 'label'=>'Display Mode');
		$indexes[] = array('index'=>'mg_img_maxheight', 'label'=>'Full size image max-height');
		$indexes[] = array('index'=>'mg_img_res_method', 'label'=>'Full size resize method');

		// user custom options
		if(is_array(mg_get_type_opt_indexes($_POST['mg_main_type']))) {
			foreach(mg_get_type_opt_indexes($_POST['mg_main_type']) as $copt) {
				$indexes[] = array('index'=>$copt, 'label'=>$copt);
			}
		}

		// types options
		$type_opt = mg_types_meta_opt($_POST['mg_main_type']);
		if($type_opt) {
			foreach($type_opt as $opt) {
			$indexes[] = $opt['validate'];	
			}
		}
		
		$validator->formHandle($indexes);
		
		$fdata = $validator->form_val;
		$error = $validator->getErrors();

		// clean data
		foreach($fdata as $key=>$val) {
			if(!is_array($val)) {
				$fdata[$key] = stripslashes($val);
			}
			else {
				$fdata[$key] = array();
				foreach($val as $arr_val) {$fdata[$key][] = stripslashes($arr_val);}
			}
		}

		// save data
		foreach($fdata as $key=>$val) {
			delete_post_meta($post_id, $key);
			add_post_meta($post_id, $key, $fdata[$key], true); 
		}
	}
 
    return $post_id;
}
add_action('save_post','mg_items_meta_save');





	

?>