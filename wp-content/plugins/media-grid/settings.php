<?php 
require_once(MG_DIR . '/functions.php');

// item types array
$types = array('image', 'img_gallery', 'video', 'audio');
?>

<div class="wrap lcwp_form">  
	<div class="icon32"><img src="<?php echo MG_URL.'/img/mg_icon.png'; ?>" alt="mediagrid" /><br/></div>
    <?php echo '<h2 class="lcwp_page_title" style="border: none;">' . __( 'Media Grid Settings', 'lcwp_ml') . "</h2>"; ?>  

    <?php
	// HANDLE DATA
	if(isset($_POST['lcwp_admin_submit'])) { 
		include(MG_DIR . '/classes/simple_form_validator.php');		
		
		$validator = new simple_fv;
		$indexes = array();
		
		$indexes[] = array('index'=>'mg_cells_margin', 'label'=>__( 'Cells Margin', 'lcwp_ml' ), 'type'=>'int');
		$indexes[] = array('index'=>'mg_cells_img_border', 'label'=>__( 'Image Border', 'lcwp_ml' ), 'type'=>'int');
		$indexes[] = array('index'=>'mg_cells_radius', 'label'=>__( 'Cells Border Radius', 'lcwp_ml' ), 'type'=>'int');
		$indexes[] = array('index'=>'mg_cells_border', 'label'=>__( 'Cells Outer Border', 'lcwp_ml' ));
		$indexes[] = array('index'=>'mg_cells_shadow', 'label'=>__( 'Cells Shadow', 'lcwp_ml' ));
		$indexes[] = array('index'=>'mg_item_width', 'label'=>__( 'Item percentage width', 'lcwp_ml' ), 'type'=>'int');
		$indexes[] = array('index'=>'mg_item_maxwidth', 'label'=>__( 'Item maximum width', 'lcwp_ml' ), 'type'=>'int');
		$indexes[] = array('index'=>'mg_item_radius', 'label'=>__( 'Item Border Radius', 'lcwp_ml' ), 'type'=>'int');		
		$indexes[] = array('index'=>'mg_facebook', 'label'=>__( 'Facebook Button', 'lcwp_ml' ));
		$indexes[] = array('index'=>'mg_twitter', 'label'=>__( 'Twitter Button', 'lcwp_ml' ));
		$indexes[] = array('index'=>'mg_pinterest', 'label'=>__( 'Pinterest Button', 'lcwp_ml' ));
		$indexes[] = array('index'=>'mg_js_head', 'label'=>__( 'Javascript in Header', 'lcwp_ml' ));

		$indexes[] = array('index'=>'mg_cells_border_color', 'label'=>__( 'Cells border color', 'lcwp_ml' ), 'type'=>'hex');
		$indexes[] = array('index'=>'mg_img_border_color', 'label'=>__( 'Image Border Color', 'lcwp_ml' ));
		$indexes[] = array('index'=>'mg_img_border_opacity', 'label'=>__( 'Image Border Opacity', 'lcwp_ml' ), 'type'=>'int');
		$indexes[] = array('index'=>'mg_main_overlay_color', 'label'=>__( 'Main Overlay Color', 'lcwp_ml' ), 'type'=>'hex');
		$indexes[] = array('index'=>'mg_main_overlay_opacity', 'label'=>__( 'Main Overlay Opacity', 'lcwp_ml' ), 'type'=>'int');
		$indexes[] = array('index'=>'mg_second_overlay_color', 'label'=>__( 'Second Overlay Color', 'lcwp_ml' ), 'type'=>'hex');
		$indexes[] = array('index'=>'mg_icons_col', 'label'=>__( 'Icons Color', 'lcwp_ml' ));
		
		$indexes[] = array('index'=>'mg_overlay_title_color', 'label'=>__( 'Second Overlay Color', 'lcwp_ml' ), 'type'=>'hex');
		$indexes[] = array('index'=>'mg_item_overlay_color', 'label'=>__( 'Item Title Color', 'lcwp_ml' ));
		$indexes[] = array('index'=>'mg_item_overlay_opacity', 'label'=>__( 'Item Overlay Opacity', 'lcwp_ml' ), 'type'=>'int');
		$indexes[] = array('index'=>'mg_item_overlay_pattern', 'label'=>__( 'Item Overlay Pattern', 'lcwp_ml' ));
		$indexes[] = array('index'=>'mg_item_bg_color', 'label'=>__( 'Item Color', 'lcwp_ml' ), 'type'=>'hex');
		$indexes[] = array('index'=>'mg_item_txt_color', 'label'=>__( 'Item Text Color', 'lcwp_ml' ), 'type'=>'hex');
		$indexes[] = array('index'=>'mg_item_icons', 'label'=>__( 'Item Icons', 'lcwp_ml' ));
		
		foreach($types as $type) {
			$indexes[] = array('index'=>'mg_'.$type.'_opt', 'label'=>__( ucfirst($type).' Options', 'lcwp_ml' ), 'max_len'=>150);	
		}
		
		$indexes[] = array('index'=>'mg_custom_css', 'label'=>__( 'Custom CSS', 'lcwp_ml' ));
		
		$validator->formHandle($indexes);
		$fdata = $validator->form_val;
		
		
		// opt builder custom validation
		foreach($types as $type) {
			if($fdata['mg_'.$type.'_opt']) {
				$a = 0;
				foreach($fdata['mg_'.$type.'_opt'] as $opt_val) {
					if(trim($opt_val) == '') {unset($fdata['mg_'.$type.'_opt'][$a]);}
					$a++;
				}
				
				if( count(array_unique($fdata['mg_'.$type.'_opt'])) < count($fdata['mg_'.$type.'_opt']) ) {
					$validator->custom_error[ucfirst($type).' Options'] = 'There are duplicates values';
				}
			}
		}
		
		$error = $validator->getErrors();
		
		if($error) {echo '<div class="error"><p>'.$error.'</p></div>';}
		else {
			// clean data and save options
			foreach($fdata as $key=>$val) {
				if(!is_array($val)) {
					$fdata[$key] = stripslashes($val);
				}
				else {
					$fdata[$key] = array();
					foreach($val as $arr_val) {$fdata[$key][] = stripslashes($arr_val);}
				}
				
				if(!$fdata[$key]) {delete_option($key);}
				else {
					if(!get_option($key)) { add_option($key, '255', '', 'yes'); }
					update_option($key, $fdata[$key]);	
				}
			}
			
			// create frontend.css else print error
			if(!mg_create_frontend_css()) {
			  echo '<div class="error"><p>An error occurred during dynamic CSS creation. The code will be used inline anyway</p></div>';
			 
			  if(!get_option('mg_inline_css')) { add_option('mg_inline_css', '255', '', 'yes'); }
			  update_option('mg_inline_css', 1);	
			}
			else {delete_option('mg_inline_css');}
			
			
			echo '<div class="updated"><p><strong>'. __('Options saved.' ) .'</strong></p></div>';
		}
	}
	
	else {  
		// Normal page display
		$fdata['mg_cells_margin'] = get_option('mg_cells_margin');  
		$fdata['mg_cells_img_border'] = get_option('mg_cells_img_border');  
		$fdata['mg_cells_radius'] = get_option('mg_cells_radius'); 
		$fdata['mg_cells_border'] = get_option('mg_cells_border'); 
		$fdata['mg_cells_shadow'] = get_option('mg_cells_shadow'); 
		$fdata['mg_item_width'] = get_option('mg_item_width'); 
		$fdata['mg_item_maxwidth'] = get_option('mg_item_maxwidth');
		$fdata['mg_item_radius'] = get_option('mg_item_radius');
		$fdata['mg_facebook'] = get_option('mg_facebook');
		$fdata['mg_twitter'] = get_option('mg_twitter');  
		$fdata['mg_pinterest'] = get_option('mg_pinterest'); 
		$fdata['mg_js_head'] = get_option('mg_js_head'); 
		
		$fdata['mg_cells_border_color'] = get_option('mg_cells_border_color'); 
		$fdata['mg_img_border_color'] = get_option('mg_img_border_color');  
		$fdata['mg_img_border_opacity'] = get_option('mg_img_border_opacity'); 
		$fdata['mg_main_overlay_color'] = get_option('mg_main_overlay_color'); 
		$fdata['mg_main_overlay_opacity'] = get_option('mg_main_overlay_opacity'); 
		$fdata['mg_second_overlay_color'] = get_option('mg_second_overlay_color');
		$fdata['mg_icons_col'] = get_option('mg_icons_col'); 
		
		$fdata['mg_overlay_title_color'] = get_option('mg_overlay_title_color');
		$fdata['mg_item_overlay_color'] = get_option('mg_item_overlay_color'); 
		$fdata['mg_item_overlay_opacity'] = get_option('mg_item_overlay_opacity'); 
		$fdata['mg_item_overlay_pattern'] = get_option('mg_item_overlay_pattern'); 
		$fdata['mg_item_bg_color'] = get_option('mg_item_bg_color'); 
		$fdata['mg_item_txt_color'] = get_option('mg_item_txt_color');
		$fdata['mg_item_icons'] = get_option('mg_item_icons');
		
		$fdata['mg_custom_css'] = get_option('mg_custom_css'); 
		
		foreach($types as $type) {
			$fdata['mg_'.$type.'_opt'] = get_option('mg_'.$type.'_opt'); 
		}
	}  
	?>


	<br/>
    <div id="tabs">
    <form name="lcwp_admin" method="post" class="form-wrap" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    	
    <ul class="tabNavigation">
    	<li><a href="#layout_opt"><?php _e('Main Options', 'mg_ml') ?></a></li>
        <li><a href="#color_opt"><?php _e('Colors', 'mg_ml') ?></a></li>
        <li><a href="#opt_builder"><?php _e('Items Options', 'mg_ml') ?></a></li>
        <li><a href="#advanced"><?php _e('Custom CSS', 'mg_ml') ?></a></li>
    </ul>    
        
    
    <div id="layout_opt"> 
    	<h3><?php _e("Predefined Styles", 'lcwp_ml'); ?></h3>
        
        <table class="widefat lcwp_table">
          <tr>
            <td class="lcwp_label_td"><?php _e("Choose a style" ); ?></td>
            <td class="lcwp_field_td">
                <select data-placeholder="Select a style .." name="mg_pred_styles" id="mg_pred_styles" class="chzn-select" tabindex="2">
                	<option value="" selected="selected"></option>
                  <?php 
                  $styles = mg_predefined_styles();
                  foreach($styles as $style => $val) { 
				  	echo '<option value="'.$style.'">'.$style.'</option>'; 
				  }
                  ?>
                </select>
            </td>
            <td>
            	<input type="button" name="mg_set_style" id="mg_set_style" value="Set" class="button-secondary" />
            </td>
          </tr> 
          <tr>
            <td class="lcwp_label_td"><?php _e("Preview" ); ?></td>
            <td class="lcwp_field_td" colspan="2">
            	<?php
				$styles = mg_predefined_styles();
                foreach($styles as $style => $val) { 
				  echo '<img src="'.MG_URL.'/img/pred_styles_demo/'.$val['preview'].'" class="mg_styles_preview" alt="'.$style.'" style="display: none;" />';	
				}
				?>
            </td>
          </tr>
        </table>
        
       
        <h3><?php _e("Grid Layout", 'lcwp_ml'); ?></h3>
        <table class="widefat lcwp_table">
          <tr>
            <td class="lcwp_label_td"><?php _e("Grid Cells Margin" ); ?></td>
            <td class="lcwp_field_td">
                <div class="lcwp_slider" step="1" max="25" min="0"></div>
                <input type="text" value="<?php echo (int)$fdata['mg_cells_margin']; ?>" name="mg_cells_margin" class="lcwp_slider_input" />
                <span>px</span>
            </td>
            <td><span class="info">Set the space between the cells</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Image Border Size" ); ?></td>
            <td class="lcwp_field_td">
                <div class="lcwp_slider" step="1" max="20" min="0"></div>
                <input type="text" value="<?php echo (int)$fdata['mg_cells_img_border']; ?>" name="mg_cells_img_border" class="lcwp_slider_input" />
                <span>px</span>
            </td>
            <td><span class="info">Set the border size for the cells</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Cells Border Radius" ); ?></td>
            <td class="lcwp_field_td">
            	<div class="lcwp_slider" step="1" max="25" min="0"></div>
                <input type="text" value="<?php echo (int)$fdata['mg_cells_radius']; ?>" name="mg_cells_radius" class="lcwp_slider_input" />
                <span>px</span>
            </td>
            <td><span class="info">Set the border radius for the cells</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Display the Outer Cell Border?" ); ?></td>
            <td class="lcwp_field_td">
                <?php ($fdata['mg_cells_border'] == 1) ? $sel = 'checked="checked"' : $sel = ''; ?>
                <input type="checkbox" value="1" name="mg_cells_border" class="ip-checkbox" <?php echo $sel; ?> />
            </td>
            <td><span class="info">If checked displays the cells external border</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Display the Cell Shadow?" ); ?></td>
            <td class="lcwp_field_td">
                <?php ($fdata['mg_cells_shadow'] == 1) ? $sel = 'checked="checked"' : $sel = ''; ?>
                <input type="checkbox" value="1" name="mg_cells_shadow" class="ip-checkbox" <?php echo $sel; ?> />
            </td>
            <td><span class="info">If checked displays a soft shadow around the cells</span></td>
          </tr> 
        </table> 
        
        <h3><?php _e("Item Layout", 'lcwp_ml'); ?></h3>
        <table class="widefat lcwp_table">
          <tr>
            <td class="lcwp_label_td"><?php _e("Item Container Width" ); ?></td>
            <td class="lcwp_field_td">
                <div class="lcwp_slider" step="1" max="100" min="30"></div>
                <input type="text" value="<?php echo (int)$fdata['mg_item_width']; ?>" name="mg_item_width" class="lcwp_slider_input" />
                <span>%</span>
            </td>
            <td><span class="info">Width percentage of the opened items in relation to the screen (default: 70)</span></td>
          </tr> 
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Item Container Maximum Width" ); ?></td>
            <td class="lcwp_field_td">
                <input type="text" value="<?php echo (int)$fdata['mg_item_maxwidth']; ?>" name="mg_item_maxwidth" class="lcwp_slider_input" />
                <span>px</span>
            </td>
            <td><span class="info">Maximum width in pixels of the opened items (default: 960)</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Item Container Border Radius" ); ?></td>
            <td class="lcwp_field_td">
                <div class="lcwp_slider" step="1" max="20" min="0"></div>
                <input type="text" value="<?php echo (int)$fdata['mg_item_radius']; ?>" name="mg_item_radius" class="lcwp_slider_input" />
                <span>px</span>
            </td>
            <td><span class="info">Set the border radius for the item container</span></td>
          </tr> 
        </table> 
        
        <h3><?php _e("Socials", 'lcwp_ml'); ?></h3>
        <table class="widefat lcwp_table">
          <tr>
            <td class="lcwp_label_td"><?php _e("Display the Facebook button?" ); ?></td>
            <td class="lcwp_field_td">
                <?php ($fdata['mg_facebook'] == 1) ? $sel = 'checked="checked"' : $sel = ''; ?>
                <input type="checkbox" value="1" name="mg_facebook" class="ip-checkbox" <?php echo $sel; ?> />
            </td>
            <td><span class="info">If checked displays the Facebook button in opened items</span></td>
          </tr> 
          <tr>
            <td class="lcwp_label_td"><?php _e("Display the Twitter button?" ); ?></td>
            <td class="lcwp_field_td">
                <?php ($fdata['mg_twitter'] == 1) ? $sel = 'checked="checked"' : $sel = ''; ?>
                <input type="checkbox" value="1" name="mg_twitter" class="ip-checkbox" <?php echo $sel; ?> />
            </td>
            <td><span class="info">If checked displays the Twitter button in opened items</span></td>
          </tr> 
          <tr>
            <td class="lcwp_label_td"><?php _e("Display the Pinterest button?" ); ?></td>
            <td class="lcwp_field_td">
                <?php ($fdata['mg_pinterest'] == 1) ? $sel = 'checked="checked"' : $sel = ''; ?>
                <input type="checkbox" value="1" name="mg_pinterest" class="ip-checkbox" <?php echo $sel; ?> />
            </td>
            <td><span class="info">If checked displays the Pinterest button in opened items</span></td>
          </tr> 
        </table> 
        
        <h3><?php _e("Advanced", 'lcwp_ml'); ?></h3>
        <table class="widefat lcwp_table">
          <tr>
            <td class="lcwp_label_td"><?php _e("Use javascript in the header?" ); ?></td>
            <td class="lcwp_field_td">
                <?php ($fdata['mg_js_head'] == 1) ? $sel = 'checked="checked"' : $sel = ''; ?>
                <input type="checkbox" value="1" name="mg_js_head" class="ip-checkbox" <?php echo $sel; ?> />
            </td>
            <td>
            	<span class="info">Put javascript in the website header, check it ONLY IF you notice some incompatibilities</span>
            </td>
          </tr> 
        </table> 
    </div>

	<div id="color_opt">
    	<h3><?php _e("Grid Items", 'lcwp_ml'); ?></h3>
        <table class="widefat lcwp_table">
          <tr>
            <td class="lcwp_label_td"><?php _e("Cells Outer Border Color" ); ?></td>
            <td class="lcwp_field_td">
                <input type="color" value="<?php echo $fdata['mg_cells_border_color']; ?>" name="mg_cells_border_color" data-hex="true" />
            </td>
            <td><span class="info">The cells outer border color</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Image Border Color" ); ?></td>
            <td class="lcwp_field_td">
                <input type="color" value="<?php echo $fdata['mg_img_border_color']; ?>" name="mg_img_border_color" />
            </td>
            <td><span class="info">The cells image border color</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Image Border Opacity" ); ?></td>
            <td class="lcwp_field_td">
                <div class="lcwp_slider" step="10" max="100" min="0"></div>
                <input type="text" value="<?php echo (int)$fdata['mg_img_border_opacity']; ?>" name="mg_img_border_opacity" class="lcwp_slider_input" />
                <span>%</span>
            </td>
            <td><span class="info">Set the CSS3 image border opacity</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Main Overlay Color" ); ?></td>
            <td class="lcwp_field_td">
                <input type="color" value="<?php echo $fdata['mg_main_overlay_color']; ?>" name="mg_main_overlay_color" data-hex="true" />
            </td>
            <td><span class="info">Color of the main overlay that appears on item mouseover</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Main Overlay Opacity" ); ?></td>
            <td class="lcwp_field_td">
                <div class="lcwp_slider" step="10" max="100" min="0"></div>
                <input type="text" value="<?php echo (int)$fdata['mg_main_overlay_opacity']; ?>" name="mg_main_overlay_opacity" class="lcwp_slider_input" />
                <span>%</span>
            </td>
            <td><span class="info">Opacity of the main overlay that appears on item mouseover</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Secondary Overlay Color" ); ?></td>
            <td class="lcwp_field_td">
                <input type="color" value="<?php echo $fdata['mg_second_overlay_color']; ?>" name="mg_second_overlay_color" data-hex="true" />
            </td>
            <td><span class="info">Color of the secondary overlay that appears on item mouseover</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td"><?php _e("Secondary Overlay Icons"); ?></td>
            <td class="lcwp_field_td">
            	<select data-placeholder="Select a style .." name="mg_icons_col" class="chzn-select" tabindex="2">
                  <option value="w" <?php if($fdata['mg_icons_col'] == 'w') {echo 'selected="selected"';} ?>>White icons</option>
                  <option value="b" <?php if($fdata['mg_icons_col'] == 'b') {echo 'selected="selected"';} ?>>Black icons</option>
                  <option value="tw" <?php if($fdata['mg_icons_col'] == 'tw') {echo 'selected="selected"';} ?>>Transparent white icons</option>
                  <option value="g" <?php if($fdata['mg_icons_col'] == 'g') {echo 'selected="selected"';} ?>>Transparent black icons</option>
                </select>
            </td>
            <td><span class="info">Color of the icons in the secondary overlay</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Overlay Title Color" ); ?></td>
            <td class="lcwp_field_td">
                <input type="color" value="<?php echo $fdata['mg_overlay_title_color']; ?>" name="mg_overlay_title_color" data-hex="true" />
            </td>
            <td><span class="info">Color of the item title that appear on the main overlay</span></td>
          </tr>
        </table> 

       <h3><?php _e("Opened Item", 'lcwp_ml'); ?></h3>
        <table class="widefat lcwp_table">
          <tr>
            <td class="lcwp_label_td"><?php _e("Overlay Color" ); ?></td>
            <td class="lcwp_field_td">
                <input type="color" value="<?php echo $fdata['mg_item_overlay_color']; ?>" name="mg_item_overlay_color" data-hex="true" />
            </td>
            <td><span class="info">Color of the fullpage overlay when an item is opened</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Overlay Opacity" ); ?></td>
            <td class="lcwp_field_td">
                <div class="lcwp_slider" step="10" max="100" min="0"></div>
                <input type="text" value="<?php echo (int)$fdata['mg_item_overlay_opacity']; ?>" name="mg_item_overlay_opacity" class="lcwp_slider_input" />
                <span>%</span>
            </td>
            <td><span class="info">Opacity of the fullpage overlay when an item is opened</span></td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Overlay Pattern" ); ?></td>
            <td class="lcwp_field_td" colspan="2">
            	<input type="hidden" value="<?php echo $fdata['mg_item_overlay_pattern']; ?>" name="mg_item_overlay_pattern" id="mg_item_overlay_pattern" />
            
            	<div class="mg_setting_pattern <?php if(!$fdata['mg_item_overlay_pattern'] || $fdata['mg_item_overlay_pattern'] == 'none') {echo 'mg_pattern_sel';} ?>" id="mgp_none"> no pattern </div>
                
                <?php 
				foreach(mg_patterns_list() as $pattern) {
					($fdata['mg_item_overlay_pattern'] == $pattern) ? $sel = 'mg_pattern_sel' : $sel = '';  
					echo '<div class="mg_setting_pattern '.$sel.'" id="mgp_'.$pattern.'" style="background: url('.MG_URL.'/img/patterns/'.$pattern.') repeat top left transparent;"></div>';		
				}
				?>
            </td>
          </tr>
          
          <tr>
            <td class="lcwp_label_td"><?php _e("Item Container Background" ); ?></td>
            <td class="lcwp_field_td">
                <input type="color" value="<?php echo $fdata['mg_item_bg_color']; ?>" name="mg_item_bg_color" data-hex="true" />
            </td>
            <td><span class="info">Color of the item background (default: #FFFFFF)</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td"><?php _e("Item Container Text Color"); ?></td>
            <td class="lcwp_field_td">
                <input type="color" value="<?php echo $fdata['mg_item_txt_color']; ?>" name="mg_item_txt_color" data-hex="true" />
            </td>
            <td><span class="info">Text color of the item (default: #222222)</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td"><?php _e("Item Container Icons"); ?></td>
            <td class="lcwp_field_td">
            	<select data-placeholder="Select a style .." name="mg_item_icons" class="chzn-select" tabindex="2">
                  <option value="dark" <?php if($fdata['mg_item_icons'] == 'dark') {echo 'selected="selected"';} ?>>Dark icons</option>
                  <option value="light" <?php if($fdata['mg_item_icons'] == 'light') {echo 'selected="selected"';} ?>>Light Icons</option>
                </select>
            </td>
            <td><span class="info">Color of the item icons</span></td>
          </tr>
          
        </table>  
    </div>
    
    <div id="opt_builder">
    <?php 
	
	
	foreach($types as $type) :
		($type == 'img_gallery') ? $typename = 'Image Gallery' : $typename = ucfirst($type);
	?>
		<h3 style="border: none;">
			<?php echo $typename ?> Options
            <a id="opt_<?php echo $type; ?>" class="add_option add-opt-h3">Add option</a>
        </h3>
        <table class="widefat lcwp_table" id="<?php echo $type; ?>_opt_table" style="width: 400px !important;">
          <thead>
          <tr>
          	<th>Option Name</th>
            <th></th>
          	<th style="width: 20px;"></th>
            <th style="width: 20px;"></th>
          </tr>
          </thead>
          <tbody>
          	<?php
			if(is_array($fdata['mg_'.$type.'_opt'])) {
				foreach($fdata['mg_'.$type.'_opt'] as $type_opt) {
					echo '
					<tr>
						<td class="lcwp_field_td">
							<input type="text" name="mg_'.$type.'_opt[]" value="'.$type_opt.'" maxlenght="150" />
						</td>
						<td></td>
						<td><span class="lcwp_move_row"></span></td>
						<td><span class="lcwp_del_row"></span></td>
					</tr>
					';	
				}
			}
			?>
          </tbody>
        </table>

	<?php endforeach; ?>
    
    </div>
    
    <div id="advanced">    
        <h3><?php _e("Custom CSS", 'lcwp_ml'); ?></h3>
        <table class="widefat lcwp_table">
          <tr>
            <td class="lcwp_field_td">
            	<textarea name="mg_custom_css" style="width: 100%" rows="6"><?php echo $fdata['mg_custom_css']; ?></textarea>
            </td>
          </tr>
        </table>
        
        <h3><?php _e("Elements Legend", 'lcwp_ml'); ?></h3> 
        <table class="widefat lcwp_table">  
          <tr>
            <td class="lcwp_label_td">.mg_filter</td>
            <td><span class="info">Grid filter container (each filter is a <xmp><a></xmp> element, each separator is a <xmp><span></xmp> element)</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td">.mg_grid_wrap</td>
            <td><span class="info">Grid container</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td">.mg_box</td>
            <td><span class="info">Single item box</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td">.mg_overlay_tit</td>
            <td><span class="info">Main overlay title</span></td>
          </tr>
          		
          <tr>
            <td class="lcwp_label_td">#mg_full_overlay_wrap</td>
            <td><span class="info">Opened Item - Full page overlay</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td">.mg_item_load</td>
            <td><span class="info">Opened Item - Item loader during the opening</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td">#mg_overlay_content</td>
            <td><span class="info">Opened Item - Item body</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td">#mg_close</td>
            <td><span class="info">Opened Item - Close item command</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td">#mg_nav</td>
            <td><span class="info">Opened Item - Item navigator container</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td">.mg_item_title</td>
            <td><span class="info">Opened Item - Item title</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td">.mg_item_text</td>
            <td><span class="info">Opened Item - Item text</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td">.mg_cust_options</td>
            <td><span class="info">Opened Item - Item options container (each option is a <xmp><li></xmp> element)</span></td>
          </tr>
          <tr>
            <td class="lcwp_label_td">.mg_socials</td>
            <td><span class="info">Opened Item - Item socials container (each social is a <xmp><li></xmp> element)</span></td>
          </tr>
          
        </table> 
    </div> 
   
    <input type="submit" name="lcwp_admin_submit" value="<?php _e('Update Options', 'lcwp_ml' ) ?>" class="button-primary" />  
    
	</form>
    </div>
</div>  

<?php // SCRIPTS ?>
<script src="<?php echo MG_URL; ?>/js/functions.js" type="text/javascript"></script>
<script src="<?php echo MG_URL; ?>/js/chosen/chosen.jquery.min.js" type="text/javascript"></script>
<script src="<?php echo MG_URL; ?>/js/iphone_checkbox/iphone-style-checkboxes.js" type="text/javascript"></script>
<script src="<?php echo MG_URL; ?>/js/colorpicker/js/mColorPicker_small.js" type="text/javascript"></script>


<script type="text/javascript" charset="utf8" >
jQuery(document).ready(function($) {
	// set a predefined style 
	jQuery('#mg_set_style').live('click', function() {
		var sel_style = jQuery('#mg_pred_styles').val();
		
		if(confirm('It will overwrite your current settings, continue?') && sel_style != '') {
			var data = {
				action: 'mg_set_predefined_style',
				style: sel_style
			};
			
			jQuery(this).parent().html('<div style="width: 30px; height: 30px;" class="lcwp_loading"></div>');
			
			jQuery.post(ajaxurl, data, function(response) {
				window.location.href = jQuery(location).attr('href');
			});	
		}
	});
	
	// predefined style  preview toggle
	jQuery('#mg_pred_styles').live("change", function() {
		var sel = jQuery('#mg_pred_styles').val();
		
		jQuery('.mg_styles_preview').hide();
		jQuery('.mg_styles_preview').each(function() {
			if( jQuery(this).attr('alt') == sel ) {jQuery(this).fadeIn();}
		});
	});
	
	
	// select a pattern
	jQuery('.mg_setting_pattern').live('click', function() {
		var pattern = jQuery(this).attr('id').substr(4);
		
		jQuery('.mg_setting_pattern').removeClass('mg_pattern_sel');
		jQuery(this).addClass('mg_pattern_sel'); 
		
		jQuery('#mg_item_overlay_pattern').val(pattern);
	});
	
	// add options
	jQuery('.add_option').click(function(){
		var type_subj = jQuery(this).attr('id').substr(4);
		
		var optblock = '<tr>\
			<td class="lcwp_field_td"><input type="text" name="mg_'+type_subj+'_opt[]" maxlenght="150" /></td>\
			<td></td>\
		    <td><span class="lcwp_move_row"></span></td>\
			<td><span class="lcwp_del_row"></span></td>\
		</tr>';

		jQuery('#'+type_subj + '_opt_table tbody').append(optblock);
	});
	
	/*** remove opt ***/
	jQuery('.lcwp_del_row').live("click", function() {
		if(confirm('<?php _e('Delete the option', 'mg_ml') ?>?')) {
			jQuery(this).parent().parent().slideUp(function() {
				jQuery(this).remove();
			});	
		}
	});
	
	/*** sort opt ***/
	jQuery('#opt_builder table').each(function() {
        jQuery(this).children('tbody').sortable({ handle: '.lcwp_move_row' });
		jQuery(this).find('.lcwp_move_row').disableSelection();
    });

	
	// tabs
	jQuery("#tabs").tabs();
});
</script>
