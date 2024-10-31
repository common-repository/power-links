<?php 
 

add_filter( 'manage_edit-keyword_replace_columns', 'wkr_keyword_replace_columns' ) ;
function wkr_keyword_replace_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		
		'title' => __( 'Title' ),
		'keyword' => __( 'Keyword' ),
		'url' => __( 'URL' ),
 
	);

	return $columns;
}

add_action( 'manage_keyword_replace_posts_custom_column', 'wkr_keyword_replace_posts_custom_column', 10, 2 );

function wkr_keyword_replace_posts_custom_column( $column, $post_id ) {
	global $post;
	switch( $column ) {
		/* If displaying the 'duration' column. */
		case 'keyword' :
			echo get_post_meta( $post->ID, 'link_text', true );
			break;

		case 'url' :
		
			echo get_post_meta( $post->ID, 'link', true );
			break;	
 	

		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}

add_action( 'the_content', 'wkr_the_content' );
function wkr_the_content( $content ){
	global $post, $wpdb;
	
	if( !class_exists('simple_html_dom_node') ){
		include_once('simple_html_dom.php');
	}
	
	if( is_Admin() ){
		return $content;
	}
	if( is_single()  	){
	// filter tags
	//find all links 
	$html = str_get_html($content);
	if( !$html ){
		return $content;
	}
	$links_array_filter = array();
	$cnt = 0;
	foreach($html->find('a') as $element){
		$links_array_filter[] = $element->outertext;
		$content =  str_replace( $element->outertext, 'link##_'.$cnt.'#',  $content );
		$cnt++;
	}
	
	$h1_filter = array();
	$cnt = 0;
	foreach($html->find('h1') as $element){
		$h1_filter[] = $element->outertext;
		$content =  str_replace( $element->outertext, 'h1##_'.$cnt.'#',  $content );
		$cnt++;
	}
	
	$h2_filter = array();
	$cnt = 0;
	foreach($html->find('h2') as $element){
		$h2_filter[] = $element->outertext;
		$content =  str_replace( $element->outertext, 'h2##_'.$cnt.'#',  $content );
		$cnt++;
	}
	
	$h3_filter = array();
	$cnt = 0;
	foreach($html->find('h3') as $element){
		$h3_filter[] = $element->outertext;
		$content =  str_replace( $element->outertext, 'h3##_'.$cnt.'#',  $content );
		$cnt++;
	}
	
	$img_filter = array();
	$cnt = 0;
	foreach($html->find('img') as $element){
		$img_filter[] = $element->outertext;
		$content =  str_replace( $element->outertext, 'img##_'.$cnt.'#',  $content );
		$cnt++;
	}
	
	
	$total_replacements = 0;
	$settings = get_option('wkr_options');
	
	$max_links = $settings['max_links'];
	if( $max_links == '' ){
		$max_links = 1;
	}
	
	$args = array( 
		'post_type' => 'keyword_replace',
		'showposts' => -1
	);
	$all_keys = get_posts( $args );
	if( count($all_keys) > 0 ){
		foreach( $all_keys as $single_key ){
			
			
			
			
			
			$link_text = trim( get_post_meta( $single_key->ID, 'link_text', true ) );
			$link = get_post_meta( $single_key->ID, 'link', true );
			$target = get_post_meta( $single_key->ID, 'target', true );
			$number_of_links = get_post_meta( $single_key->ID, 'number_of_links', true );
			$nofollow = get_post_meta( $single_key->ID, 'nofollow', true );
			$link_cat = get_post_meta( $single_key->ID, 'link_cat', true );
			$title_keyword = get_post_meta( $single_key->ID, 'title_keyword', true );
			
	 	
			$single_word_array = explode(',', $link_text);
			$single_word_array = array_map( 'trim', $single_word_array );
			
		 
			foreach( $single_word_array as $single_inner_word ){
 
				$search_count = (int)$search_count +  substr_count( $post->post_content, ' '.$single_inner_word.' ' );
			}
			 
		
			// searxh text count
			//$search_count = substr_count( strtoupper($post->post_content), ' '.strtoupper($link_text).' ' );
			
			if( $search_count >= $max_links ) {
				$max_links_amount = $max_links;
			}else{
				$max_links_amount = $search_count;
			}
			 
			$values_array = array();
			for( $z=1; $z < $search_count; $z++ ){
				$values_array[] = $z;
			}
			 
			shuffle ( $values_array );
			 
			$links_array = array_slice($values_array, 0, $max_links_amount); 
			
		 
			$process_link = 1;
			
			// form link textdomain
			$target_text = '';
			if( $target == '_blank' ){
				$target_text = ' target="_blank" ';
			}
			
			$nofollow_text = '';
			if( $nofollow == 'yes' ){
				$nofollow_text = ' rel="nofollow" ';
			}
			
			
			
			
			// check if post in category
			$terms = wp_get_post_terms( $post->ID, 'category' );
		 
			if( count($link_cat) > 0 && $link_cat != '' ){
				$process_link = 0;
				if( count($terms) > 0 ){
					
					foreach( $terms as $single_term ){
					 
						if( in_array( $single_term->term_id,  $link_cat) ){
							$process_link = 1;
						}
					}
				}
			}
			
			// AND patch
			if( $process_link == 0 ){ continue; }
		 
			// check title
			if( trim( $title_keyword ) != '' ){
				$process_link = 0;
				
			 
				$pattern = '/\b'.$title_keyword.'\b/';
		 
				preg_match( $pattern, $post->post_title, $matches);	
				
				//if( substr_count( strtoupper($post->post_title), ' '.strtoupper($title_keyword).' ' ) > 0 ){
				if( count($matches) > 0 ){
					$process_link = 1;
				}
				 
			}
			 
		  
			// AND patch
			if( $process_link == 0 ){ continue; }
			
			
			 // get total links count
			$total_links_for_key = $wpdb->get_var( "SELECT SUM( meta_value ) FROM {$wpdb->prefix}postmeta WHERE meta_key = 'wkr_key_".$single_key->ID."'  AND post_id IN ( SELECT ID FROM  {$wpdb->prefix}posts WHERE post_status = 'publish' )" );
			
			
			 
			if( $total_links_for_key >= $number_of_links ){
				$process_link = 0;
			}
			
		 
			$current_replaced_links = get_post_meta( $post->ID, 'wkr_key_'.$single_key->ID, true);
			
			 
			
			if( $current_replaced_links != 0 && $current_replaced_links != '' ){
				$process_link = 1;
			}

	 
			$max_links_inner = $settings['max_links'];
			// process filtering
			if( $process_link == 1 ){
				$inner_replacement = 0;
				foreach( $single_word_array as $single_inner_word ){
					
				 
					$link_full_text = ' <a href="'.$link.'" '.$target_text.' '.$nofollow_text.' >'.$single_inner_word.'</a> ';
					
					//if( $total_replacements >= $max_links_amount ){ break; }
					if( $inner_replacement >= $max_links_inner ){ break; }
					
					for($i=0; $i < $max_links_amount; $i++){
					
						$content_backup = $content;
						
						$content = wkr_str_replace_first( ' '.$single_inner_word.' ', ' '.$link_full_text.' ', $content);
						
						if( $content_backup != $content ){
							$total_replacements++;
							$inner_replacement++;
						}
						
						
					}
				}
				/*
				for($i=0; $i < $max_links_amount; $i++){
					
					$content_backup = $content;
					
					$content = wkr_str_replace_first( ' '.$link_text.' ', ' '.$link_full_text.' ', $content);
					
					if( $content_backup != $content ){
						$total_replacements++;
					}
					
					
				}
				*/
				
				
				// update post replacement amount
				update_post_meta( $post->ID, 'wkr_key_'.$single_key->ID,  $max_links_amount );
 
				 
			}else{
				delete_post_meta( $post->ID, 'wkr_key_'.$single_key->ID  );
			}
			
			
			
		}
		
		
		
	}
	 
	update_post_meta( $post->ID, 'total_replacements', $total_replacements );
	 
	             
		$cnt = 0;
		foreach( $links_array_filter as $element){
      
			$content =  str_replace( 'link##_'.$cnt.'#', $element,   $content );
			$cnt++;
		}
	 
		$cnt = 0;
		foreach( $h1_filter as $element){
			$content =  str_replace( 'h1##_'.$cnt.'#', $element,   $content );
			$cnt++;
		}
		
		$cnt = 0;
		foreach( $h2_filter as $element){
			$content =  str_replace( 'h2##_'.$cnt.'#', $element,   $content );
			$cnt++;
		}
		
		$cnt = 0;
		foreach( $h3_filter as $element){
			$content =  str_replace( 'h3##_'.$cnt.'#', $element,   $content );
			$cnt++;
		}
		$cnt = 0;
		foreach( $img_filter as $element){
			$content =  str_replace( 'img##_'.$cnt.'#', $element,   $content );
			$cnt++;
		}
         
	}
	return $content;
}

function wkr_str_replace_first($from, $to, $content)
{
    $from = '/'.preg_quote($from, '/').'/i';

    return preg_replace($from, $to, $content, 1);
}
 

?>