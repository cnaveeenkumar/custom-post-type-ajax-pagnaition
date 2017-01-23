<?php
/*
Plugin Name:CPTA Pagination 
Description:It's a simple custom post type ajax pagination plugin.
Version:1.0
Author:Naveenkumar C
License:GPL2

Copyright 2014-2016 Naveenkumar C (email: cnaveen777 at gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// Exit you access directly
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function cpta_pagination_enqueue() {
	wp_enqueue_style( 'cptapagination' ,  plugin_dir_url( __FILE__ ) . 'css/cptapagination-style.css' );
	wp_register_script( 'cpta-pagination-custom-js', plugin_dir_url( __FILE__ ) .'/js/cptapagination.js');
	wp_localize_script( 'cpta-pagination-custom-js', 'ajax_params', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script( 'cpta-pagination-custom-js' );
}
add_action('wp_enqueue_scripts', 'cpta_pagination_enqueue');

add_action( 'wp_ajax_cptapagination', 'cptapagination_callback' );
add_action( 'wp_ajax_nopriv_cptapagination', 'cptapagination_callback' );					

function cptapagination_callback() {
	global $wpdb;
	$cptaNumber = absint($_POST['number']);
	$cptaLimit  = absint($_POST['limit']);
	$cptaType = sanitize_text_field($_POST['cptapost']);
	if( $cptaNumber == "1" ){
		$cptaOffsetValue = "0";
		$args = array('posts_per_page' => $cptaLimit,'post_type' => $cptaType,'post_status' => 'publish');	
	}else{
		$cptaOffsetValue = ($cptaNumber-1)*$cptaLimit;
		$args = array('posts_per_page' => $cptaLimit,'post_type' => $cptaType,'offset' => $cptaOffsetValue,'post_status' => 'publish');
	}
	$cptaQuery = new WP_Query( $args );
		if( $cptaQuery->have_posts() ){
			while( $cptaQuery->have_posts() ){ $cptaQuery->the_post();
				 echo "<div class='cpta-Section'>
					<h1>".get_the_title()."</h1>
					<p>".get_the_excerpt()."</p>
					<a href=".get_the_permalink()." class='btn-cptapagi'>Read More</a>
				</div>";
			} wp_reset_postdata();
		}
		$cpta_args = array('posts_per_page' => -1,'post_type' => $cptaType,'post_status' => 'publish');	
		$cpta_Query = new WP_Query( $cpta_args );
		$cpta_Count = count($cpta_Query->posts);
		$cpta_Paginationlist = $cpta_Count/$cptaLimit;
		$last = ceil( $cpta_Paginationlist );
		if( $cptaNumber>1 ){ $cptaprev = $cptaNumber-1;	}
		if( $cptaNumber < $last ){ $cptanext = $cptaNumber+1; }
		echo "<ul class='list-cptapagination'>";
		echo "<li class='pagitext'><a href='javascript:void(0);' onclick='javascript:cptaajaxPagination($cptaprev,$cptaLimit)'>Prev</a></li>";
		for( $cpta=1; $cpta<=ceil($cpta_Paginationlist); $cpta++){
			if( $cptaNumber ==  $cpta ){ $active="active"; }else{ $active=""; }
			echo "<li><a href='javascript:void(0);' id='post' class='$active' data-posttype='$cptaType' onclick='cptaajaxPagination($cpta,$cptaLimit);'>$cpta</a></li>";
		}
		echo "<li class='pagitext'><a href='javascript:void(0);' onclick='javascript:cptaajaxPagination($cptanext,$cptaLimit)'>Next</a></li>";
		echo "</ul>";
	wp_die();
}

function cptapagination_default($atts) {
	global $wpdb;
	$atts = shortcode_atts(	array('custom_post_type' => '',	'post_limit' => ''),$atts,'cptapagination');
	
	if($atts['custom_post_type'] !="" ){
		$cptaType = sanitize_text_field($atts['custom_post_type']);	
	}else{
		$cptaType ="post";	
	}
	if( $atts['post_limit'] !="" ){
		$cptaLimit= absint($atts['post_limit']);	
	}else{
		$cptaLimit=5;
	}
	
	$args = array('posts_per_page' => $cptaLimit,'post_type' => $cptaType,'post_status' => 'publish');
	$cptaQuery = new WP_Query( $args );
	echo "<div id='cptapagination-content'>";
		if( $cptaQuery->have_posts() ){
			while( $cptaQuery->have_posts() ){ $cptaQuery->the_post();
				 echo "<div class='cpta-Section'>
					<h1>".get_the_title()."</h1>
					<p>".get_the_excerpt()."</p>
					<a href=".get_the_permalink()." class='btn-cptapagi'>Read More</a>
				</div>";
			} wp_reset_postdata();
		}
		$cpta_args = array('posts_per_page' => -1,'post_type' => $cptaType,'post_status' => 'publish');	
		$cpta_Query = new WP_Query( $cpta_args );
		$cpta_Count = count($cpta_Query->posts);
		$cpta_Paginationlist = $cpta_Count/$cptaLimit;
		echo "<ul class='list-cptapagination'>";
		echo "<li class='pagitext'><a href='javascript:void(0);' onclick='javascript:cptaajaxPagination(1,$cptaLimit)'>Prev</a></li>";
		for( $cpta=1; $cpta<=ceil($cpta_Paginationlist); $cpta++){ 
			if( $cpta ==  0 || $cpta ==  1 ){ $active="active"; }else{ $active=""; }
			echo "<li><a href='javascript:void(0);' id='post' class='$active' data-posttype='$cptaType' onclick='cptaajaxPagination($cpta,$cptaLimit);'>$cpta</a></li>";
		}
		echo "<li class='pagitext'><a href='javascript:void(0);' onclick='javascript:cptaajaxPagination(2,$cptaLimit)'>Next</a></li>";
		echo "</ul>";
	echo "</div>";
}
add_shortcode( 'cptapagination', 'cptapagination_default' );
?>