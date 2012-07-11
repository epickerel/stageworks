<?php
// implement tinymce button

add_action('media_buttons_context', 'mg_editor_btn', 1);
add_action('admin_footer', 'mg_editor_btn_content');


//action to add a custom button to the content editor
function mg_editor_btn($context) {
	$img = MG_URL . '/img/mg_icon_small.png';
  
	//the id of the container I want to show in the popup
	$container_id = 'mg_popup_container';
  
	//our popup's title
	$title = 'Media Grid';
  
	//append the icon
	$context .= '
	<a class="thickbox" id="mg_editor_btn" title="'.$title.'" style="cursor: pointer;" >
	  <img src="'.$img.'" />
	</a>';
  
	return $context;
}


function mg_editor_btn_content() {
	if(strpos($_SERVER['REQUEST_URI'], 'post.php') || strpos($_SERVER['REQUEST_URI'], 'post-new.php')) :
?>

    <div id="mg_popup_container" style="display:none;">
      <?php 
	  // get the grids
	  $grids = get_terms( 'mg_grids', 'hide_empty=0' );
	  
	  if(!is_array($grids)) {echo '<span>No grids found ..</span>';}
	  else {
	  ?>
      	<table id="lcwp_tinymce_table" class="lcwp_form lcwp_table" cellspacing="0" style="width: 530px">
          <tr>
            <td style="width: 35%;">Grid</td>
      		<td colspan="2">
            	<select id="mg_grid_choose" data-placeholder="Select a grid .." name="mg_grid" class="chzn-select" tabindex="2" style="width: 370px;">
				<?php 
                foreach ( $grids as $grid ) {
                    echo '<option value="'.$grid->term_id.'">'.$grid->name.'</option>';
                }
                ?>
              </select>
            </td>
          </tr>
          
          <tr>
            <td>Allow filter</td>
      		<td style="width: 30%;" class="lcwp_form">
            	<input type="checkbox" name="filter_grid" value="1" class="mg_popup_ip" id="mg_filter_grid" />
            </td>
            <td><span class="info">Allow items filtering by category</span></td>
          </tr>  
          
          <tr>
            <td>Relative Width</td>
      		<td><input type="text" name="mg_grid_w" id="mg_grid_w" class="lcwp_slider_input" maxlength="4" /> px</td>
            <td><span class="info">Relative with to calculate cells size. Leave empty to auto-calculate</span></td>
          </tr> 
            
          <tr class="tbl_last">
          	<td colspan="2">
            	<input type="button" value="Insert Grid" name="mg_insert_grid" id="mg_insert_grid" class="button-primary" />
            </td>    
          </tr>
        </table>   
      <?php } ?>
    </div>
	
    <?php // SCRIPTS ?>
    <script src="<?php echo MG_URL; ?>/js/functions.js" type="text/javascript"></script>
	<script src="<?php echo MG_URL; ?>/js/chosen/chosen.jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo MG_URL; ?>/js/iphone_checkbox/iphone-style-checkboxes.js" type="text/javascript"></script>
<?php
	endif;
	return true;
}

?>