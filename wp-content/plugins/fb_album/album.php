<?php
/* 
Plugin Name: Facebook album
Plugin URI:  
Description:  
Author: Emmett Pickerel 
Version: 1.0 
Author URI: http://docsplendid.com 
*/


function fbalbum( $atts ) {
    extract( shortcode_atts( array(
		'album_id' => '000000000'
	), $atts ) );
	
	$str = "";
	$str .= "<div id='fbcontent'></div>\n";
    $str .= "<script>// <![CDATA[\n";
    $str .= "(function(){\n";
    $str .= "var q = window.jQuery.pageQuery();\n";
    $str .= "window.jQuery('#fbcontent').fbAlbum('" . $album_id . "');\n";
    $str .= "}());\n";
    $str .= "// ]]></script>\n";
    return $str;
}

add_shortcode('fbalbum', 'fbalbum');


?>
