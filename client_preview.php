<?php
/*
Plugin Name: Client Preview
Plugin URI: http://wordpress.bryanmurdaugh.com/client-preview
Description: This plugin allows you to close off a subset of your sites public pages so that you can give links to specific pages to clients or to the public for preview. 
1) Activate the plugin.
2) Enable Client Preview Mode (when you do this, non-logged-in users will not be able to access any pages on your site).
3) Click the pages you’d like non-logged-in users to have access to.
Version: 1.0
Author: Bryan Murdaugh
Author URI: http://wordpress.bryanmurdaugh.com
License: MIT
*/



add_action('admin_menu', 'client_preview_menu');

function client_preview_menu() {
	add_options_page('Client Preview Options', 'Client Preview', 'manage_options', 'client-preview-bjm', 'client_preview_options');
}

function client_preview_options() {

	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	$old_preview_pages = get_option('client_preview_pages');
	$old_preview_enabled = get_option('client_preview_enabled');

	if ($_POST['enable_client_preview'] == 'on') {
		update_option('client_preview_enabled',true);
		$enabled_pages = $_POST['enable_page'];
		if ($old_preview_enabled) update_option('client_preview_pages',$_POST['enable_page']);
		if ($_POST['client_preview_text']) update_option('client_preview_text',$_POST['client_preview_text']);
		update_option('client_preview_home',($_POST['client_preview_home'] == 'on' ? true : false));	
		update_option('client_preview_cat',($_POST['client_preview_cat'] == 'on' ? true : false));	
		update_option('client_preview_single',($_POST['client_preview_single'] == 'on' ? true : false));	
		update_option('client_preview_archive',($_POST['client_preview_archive'] == 'on' ? true : false));	
		update_option('client_preview_tag',($_POST['client_preview_tag'] == 'on' ? true : false));	
		echo '<div class="updated"><p><strong>';
		_e('Settings Saved.', 'client-preview-bjm' );
		echo '</strong></p></div>';
	}
		
	if ($_POST['enable_client_preview'] == 'on' && empty($_POST['enable_page']) && empty($old_preview_pages)) {
		update_option('client_preview_pages',array());
	}
	
	elseif (($_POST) && ($_POST['enable_client_preview'] != 'on')) {
		update_option('client_preview_enabled',false);		
	}
	
	$enabled = get_option('client_preview_enabled');
	
	echo '<div class="wrap"><form method="post">';
	echo '<h3>Client Preview Options</h3>';
	echo '<p><input name="enable_client_preview" type="checkbox" ' . ( $enabled == true ? 'checked' : '') . '/> Enable Client Preview Mode</p>';
	echo '<p>Enabling preview mode without selecting any pages to allow will close down the entire site.</p>';
	
	if ($enabled == true) {		
	
		$enabled_page_array = get_option('client_preview_pages') or array();
		
		$all_pages_array = get_pages();
		
		foreach($all_pages_array as $page_array) {
			if(in_array((string)$page_array->ID,$enabled_page_array)) {
				$this_page_enabled = true;
			}
			echo '<p>';
			echo '<input name="enable_page[]" value="' . $page_array->ID . '" type="checkbox" ' . ($this_page_enabled == true ? 'checked' : '') . '/> ' . $page_array->post_title;
			echo '</p>';		
		}
		echo '<p><input name="client_preview_home" type="checkbox"' . (get_option('client_preview_home') ? 'checked' : '') . '/> Allow <strong>Blog</strong> Home Preview</p>';
		echo '<p><input name="client_preview_cat" type="checkbox"' . (get_option('client_preview_cat') ? 'checked' : '') . '/> Allow <strong>Category</strong> Preview</p>';
		echo '<p><input name="client_preview_single" type="checkbox"' . (get_option('client_preview_single') ? 'checked' : '') . '/> Allow <strong>Single Post</strong> Preview</p>';
		echo '<p><input name="client_preview_archive" type="checkbox"' . (get_option('client_preview_archive') ? 'checked' : '') . '/> Allow <strong>Archive</strong> Preview</p>';
		echo '<p><input name="client_preview_tag" type="checkbox"' . (get_option('client_preview_tag') ? 'checked' : '') . '/> Allow <strong>Tag Archive</strong> Preview</p>';
		echo '<p><input type="text" name="client_preview_text" value="' . get_option('client_preview_text') . '" /> Client preview banner text.</p>';
	}
	echo '<input type="submit" name="submit" value="submit" /></form></div>';

}

function client_preview() {
	if (get_option('client_preview_enabled') == true) {
	  if (!is_user_logged_in()) {
	    echo "<div style='color: #ddd; position: absolute; top: 0; left: 0; 
   padding: 5px 10px;
   margin: 10px;
   background: #555;
   color: #fff;
   font-size: 21px;
   font-weight: bold;
   line-height: 1.3em;
   border: 2px dashed #fff;
   border-top-left-radius: 3px;
   -moz-border-radius-topleft: 3px;
   -webkit-border-top-left-radius: 3px;
   border-bottom-right-radius: 3px;
   -moz-border-radius-bottomright: 3px;
   -webkit-border-bottom-right-radius: 3px;
   border-top-right-radius: 3px;
   -moz-border-radius-topright: 3px;
   -webkit-border-top-right-radius: 3px;
   -moz-box-shadow: 0 0 0 4px #555, 2px 1px 4px 4px rgba(10,10,0,.5);
   -webkit-box-shadow: 0 0 0 4px #555, 2px 1px 4px 4px rgba(10,10,0,.5);
   box-shadow: 0 0 0 4px #555, 2px 1px 6px 4px rgba(10,10,0,.5);
   text-shadow: -1px -1px #333;
   font-weight: normal; '>" . (get_option('client_preview_text') ? get_option('client_preview_text') : 'Client Preview Only' ) . "</div>";
	    if (!(is_page(get_option('client_preview_pages')) || (get_option('client_preview_home') && is_home()) || (get_option('client_preview_cat') && is_category()) || (get_option('client_preview_single') && is_single()) || (get_option('client_preview_archive') && is_archive()) || (get_option('client_preview_tag') && is_tag()))) {
	    	die();
	    }
		}
	}
}
 
add_action('wp_head','client_preview');

function pn($elem,$max_level=10,$pn_stack=array()){ 
    if(is_array($elem) || is_object($elem)){ 
        if(in_array(&$elem,$pn_stack,true)){ 
            echo "<font color=red>RECURSION</font>"; 
            return; 
        } 
        $pn_stack[]=&$elem; 
        if($max_level<1){ 
            echo "<font color=red>nivel maximo alcanzado</font>"; 
            return; 
        } 
        $max_level--; 
        echo "<table border=1 cellspacing=0 cellpadding=3 width=100%>"; 
        if(is_array($elem)){ 
            echo '<tr><td colspan=2 style="background-color:#333333;"><strong><font color=white>ARRAY</font></strong></td></tr>'; 
        }else{ 
            echo '<tr><td colspan=2 style="background-color:#333333;"><strong>'; 
            echo '<font color=white>OBJECT Type: '.get_class($elem).'</font></strong></td></tr>'; 
        } 
        $color=0; 
        foreach($elem as $k => $v){ 
            if($max_level%2){ 
                $rgb=($color++%2)?"#888888":"#BBBBBB"; 
            }else{ 
                $rgb=($color++%2)?"#8888BB":"#BBBBFF"; 
            } 
            echo '<tr><td valign="top" style="width:40px;background-color:'.$rgb.';">'; 
            echo '<strong>'.$k."</strong></td><td>"; 
            pn($v,$max_level,$pn_stack); 
            echo "</td></tr>"; 
        } 
        echo "</table>"; 
        return; 
    } 
    if($elem === null){ 
        echo "<font color=green>NULL</font>"; 
    }elseif($elem === 0){ 
        echo "0"; 
    }elseif($elem === true){ 
        echo "<font color=green>TRUE</font>"; 
    }elseif($elem === false){ 
        echo "<font color=green>FALSE</font>"; 
    }elseif($elem === ""){ 
        echo "<font color=green>EMPTY STRING</font>"; 
    }else{ 
        echo str_replace("\n","<strong><font color=red>*</font></strong><br>\n",$elem); 
    } 
} 


?>