<?php
////////////////////////////////////
// DYNAMICALLY CREATE THE CSS //////
////////////////////////////////////
require_once('functions.php');
?>

@import url("<?php echo MG_URL; ?>/css/frontend.css");

@import url("<?php echo MG_URL; ?>/js/wmuSlider/css/style.css");
@import url("<?php echo MG_URL; ?>/js/jPlayer/skin/media.grid/jplayer.media.grid.css");


/* cell shadow & border */
.mg_box { 
  <?php if(get_option('mg_cells_shadow')) echo 'box-shadow: 0px 0px 3px rgba(25,25,25,0.6);'; ?>
  margin: <?php echo (int)get_option('mg_cells_margin'); ?>px; 
  
  <?php
  if(get_option('mg_cells_border')) {
	(get_option('mg_cells_border_color')) ? $col = get_option('mg_cells_border_color') : $col = '#444';
	echo 'border: 1px solid '.$col.';';   
  }
  ?> 
}

/* image border */
.img_wrap {
	padding: <?php echo (int)get_option('mg_cells_img_border'); ?>px;
	border-width: 0px; 
	
	<?php
	if(get_option('mg_img_border_color') && get_option('mg_img_border_color') != '' && get_option('mg_img_border_opacity')) {
	  $alpha_val = (int)get_option('mg_img_border_opacity') / 100;  
	  
	  echo 'background-color: '.get_option('mg_img_border_color').';
	  ';  
	
	  if($alpha_val != 0) {
		  $rgba = str_replace(array('rgb', ')'), array('rgba', ', '.$alpha_val.')'), get_option('mg_img_border_color'));
		  echo 'background-color: '.$rgba.';'; 
	  }
	}
	?>
}

/* overlay colors */
.img_wrap .overlays .overlay {
	<?php
	if(get_option('mg_main_overlay_color') && get_option('mg_main_overlay_color') != '') {
		$main_ol_col = get_option('mg_main_overlay_color');
	}
	else {$main_ol_col = 'rgb(255,255,255)';}
	
	echo 'background: '.$main_ol_col.';';
	?>
}
.img_wrap:hover .overlays .overlay {
   <?php
	// alpha
	$alpha_val = (int)get_option('mg_main_overlay_opacity') / 100;  
	
	echo '
	filter: alpha(opacity='.(int)get_option('mg_main_overlay_opacity').');
	opacity: '.$alpha_val.';
	';
	?> 
}

.img_wrap .overlays .cell_more {
	<?php
	if(get_option('mg_second_overlay_color') && get_option('mg_second_overlay_color') != '') {
		$sec_ol_col = get_option('mg_second_overlay_color');
	}
	else {$sec_ol_col = '#474747';}
	
	echo 'background-color: '.$sec_ol_col.';';
	?>
}

span.mg_overlay_tit {
	<?php 
	(get_option('mg_overlay_title_color')) ? $col = get_option('mg_overlay_title_color') : $col = '#222';
	echo 'color: '.$col;
	?>	  
}

/* icons color */
<?php (!get_option('mg_icons_col')) ? $icol = 'b' : $icol = get_option('mg_icons_col'); ?>

/* img */
.mg_image .img_wrap .overlays .cell_more span {
  background: url(<?php echo MG_URL ?>/img/overlay/img_<?php echo $icol ?>.png) no-repeat top left transparent;  
}

/* gallery */
.mg_gallery .img_wrap .overlays .cell_more span {
  background: url(<?php echo MG_URL ?>/img/overlay/gallery_<?php echo $icol ?>.png) no-repeat top left transparent;  
}

/* video */
.mg_video .img_wrap .overlays .cell_more span {
  background: url(<?php echo MG_URL ?>/img/overlay/video_<?php echo $icol ?>.png) no-repeat top left transparent;  
}

/* audio */
.mg_audio .img_wrap .overlays .cell_more span {
  background: url(<?php echo MG_URL ?>/img/overlay/audio_<?php echo $icol ?>.png) no-repeat top left transparent;  
}

/* link */
.mg_link .img_wrap .overlays .cell_more span {
  background: url(<?php echo MG_URL ?>/img/overlay/link_<?php echo $icol ?>.png) no-repeat top left transparent;  
}

/* custom content */
.mg_lb_text .img_wrap .overlays .cell_more span {
  background: url(<?php echo MG_URL ?>/img/overlay/lb_text_<?php echo $icol ?>.png) no-repeat top left transparent;  
}


/* border radius */
.mg_box, .img_wrap, .img_wrap .thumb, .img_wrap > div {
  border-radius: <?php echo (int)get_option('mg_cells_radius'); ?>px;
}


/* opened item */
#mg_full_overlay_wrap {
  <?php  
  // color
  if(get_option('mg_item_overlay_color') && get_option('mg_item_overlay_color') != '') {
	  $item_ol_col = get_option('mg_item_overlay_color');
  }
  else {$item_ol_col = '#fff';}  
  
  // alpha
  $alpha_val = (int)get_option('mg_item_overlay_opacity') / 100;  
  
  // pattern
  if(get_option('mg_item_overlay_pattern') && get_option('mg_item_overlay_pattern') != 'none') {
	  $pat = 'url('.MG_URL.'/img/patterns/'.get_option('mg_item_overlay_pattern').') repeat top left';
  }
  else {$pat = '';}
  
  echo '
  background: '.$pat.' '.$item_ol_col.';	
  
  filter: alpha(opacity='.(int)get_option('mg_item_overlay_opacity').');
  opacity: '.$alpha_val.';
  ';  
  ?>  
}
#mg_overlay_content {
  <?php 
  (get_option('mg_item_width')) ? $w = get_option('mg_item_width') : $w = 70;
  echo 'width: '.$w.'%;';
  
  (get_option('mg_item_maxwidth')) ? $w = get_option('mg_item_maxwidth') : $w = 960;
  echo 'max-width: '.$w.'px;';
  ?>
}

/* colors */
#mg_overlay_content,
.mg_item_load {
	<?php 
	(get_option('mg_item_bg_color')) ? $col = get_option('mg_item_bg_color') : $col = '#fff';
	echo 'background-color: '.$col.';
';
	?>
    <?php 
	(get_option('mg_item_txt_color')) ? $col = get_option('mg_item_txt_color') : $col = '#222';
	echo 'color: '.$col.';
';
	?>	
}

/* icons */
<?php if(get_option('mg_item_icons') == 'light') : ?>
#mg_full_overlay .mg_item_load,
#mg_full_overlay .mg_item_featured {
	background-image: url(../img/loader_w.gif);
}	
	
#mg_close {
	background-image: url(../img/item_close_w.png);
}	
#mg_nav .mg_nav_prev span {
	background-image: url(../img/item_prev_w.png);		
}
#mg_nav .mg_nav_next span {
	background-image: url(../img/item_next_w.png);	
}
<?php endif; ?>

/* border radius */
#mg_overlay_content {
	border-radius: <?php echo (int)get_option('mg_item_radius'); ?>px;
}

<?php 
// custom CSS
echo get_option('mg_custom_css');
?>