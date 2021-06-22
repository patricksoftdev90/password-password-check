<?php
/*
Plugin Name: ysa-jquery-password-strength
Plugin URI: http://blog.ysatech.com/post/2011/01/13/ASPNET-Password-Strength-Indicator-using-jQuery-and-XML.aspx
Description: Password Strength Indicator with jQuery and XML
Version: 1.0
Author: Bryian Tan
Author URI: http://blog.ysatech.com
License: GPL2
*/
/*  Copyright 2013  Bryian Tan  (email : bryian.tan@ysatech.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php

function ysa_get_xml_url() {
	$file = dirname(__FILE__) . '/PasswordPolicy.xml';
	
	$plugin_url = plugin_dir_url($file);
	$xmlPath = $plugin_url . "PasswordPolicy.xml";
	return $xmlPath;
}

function ysa_password_strength_scripts() {
	$pwdPolicyText = "Password policy";

	$xmlPath = ysa_get_xml_url();

	$html = '';
	$html .='<script type="text/javascript">';
	$html .='/* <![CDATA[ */';
    $html .='	jQuery(document).ready(function() {';
	$html .='		if (jQuery("[id=\'pass-strength-result\']").length>0) {';
	$html .='setTimeout(function() {';
	$html .='jQuery("#pass-strength-result").css({\'visibility\':\'hidden\',\'display\':\'none\'});';
	$html .='jQuery("#pass-strength-result").html("");';
	$html .='}, 1000);';
	$html .='		}';
	$html .='if (jQuery(".indicator-hint").length>0) {';
	$html .='	jQuery(".indicator-hint").append(" <br/><a id=\'passwordPolicy\' href=\'#\'>'. $pwdPolicyText.'</a>");';
	$html .='}';
	$html .='jQuery("[id$=\'passwordPolicy\']").click(function(event) {';
	$html .='var width = 350, height = 300, left = (screen.width / 2) - (width / 2),';
	$html .='top = (screen.height / 2) - (height / 2);';
	$html .='window.open("' . $xmlPath . '", "'. $pwdPolicyText .'", \'width=\' + width + \',height=\' + height + \',left=\' + left + \',top=\' + top);';
	$html .='event.preventDefault();';
	$html .='return false;';
	$html .='});';
	$html .='var myPSPlugin = jQuery("[id=\'pass1\']").password_strength();';
	$html .='	});';
	$html .='/* ]]> */    ';
	$html .='</script>';
	
	echo $html;
}

function ysa_validate_password($errors, $update, $user) {
	if (isset($user->user_pass)) {
//read from XML
		$xml = simplexml_load_file(plugins_url('PasswordPolicy.xml', __FILE__)) 
       											or die("Error: Cannot create object");

		 foreach($xml->children() as $PasswordPolicy=> $data){
			  $minLength = $data->minLength;
			  $maxLength = $data->maxLength;
			  $numsLength = $data->numsLength;
			  $upperLength = $data->upperLength;
			  $specialLength = $data->specialLength;
			  $specialChars = $data->specialChars;
		}
	
		$passwordReg ="(?=^.{".$minLength.",".$maxLength."}$)(?=(?:.*?\d){".$numsLength."})(?=.*[a-z])(?=(?:.*?[A-Z]){".$upperLength."})(?=(?:.*?[".$specialChars."]){".$specialLength."})(?!.*\s)[0-9a-zA-Z".$specialChars."]*$";
		
		if (!preg_match("/$passwordReg/", $user->user_pass, $matches)) {
		  	$errors->add( 'weak-password', __( '<strong>ERROR</strong>: Password does not meet Password Policy.' ));
		}
	}
}
 
add_action('admin_footer', 'ysa_password_strength_scripts',9999,1);

	function ysa_plugin_scripts() {
		wp_enqueue_script('ysa_password_strength_scripts', plugins_url('scripts/jquery.password-strength.js', __FILE__), array('jquery'), '1.0', true);
		wp_enqueue_script('ysa-jquery-password-strength');
    }

add_action('admin_init', 'ysa_plugin_scripts',100,1);

add_action('user_profile_update_errors', 'ysa_validate_password', 999, 3);

?>