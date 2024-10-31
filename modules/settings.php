<?php 


class vooSettingsClassMainPowerLinks{
	
	var $setttings_parameters;
	var $settings_prefix;
	var $message;
	
	function __construct( $prefix ){
		$this->setttings_prefix = $prefix;	
		
		if(  wp_verify_nonce($_POST['save_settings_field'], 'save_settings_action') ){
			$options = array();
			foreach( $_POST as $key=>$value ){
				$options[$key] = sanitize_text_field( $value );
			}
			update_option( $this->setttings_prefix.'_options', $options );
			
			$this->message = '<div class="alert alert-success">Settings saved</div>';
			
		}
	}
	
	function get_setting( $setting_name ){
		$inner_option = get_option( $this->setttings_prefix.'_options');
		return $inner_option[$setting_name];
	}
	
	function create_menu( $parameters ){
		$this->setttings_parameters = $parameters;		
			
		add_action('admin_menu', array( $this, 'add_menu_item') );
		
	}
	
	 
	
	
	function add_menu_item(){
		
		foreach( $this->setttings_parameters as $single_option ){
			if( $single_option['type'] == 'menu' ){
				add_menu_page(  			 
				$single_option['page_title'], 
				$single_option['menu_title'], 
				$single_option['capability'], 
				$single_option['menu_slug'], 
				array( $this, 'show_settings' ) 
				);
			}
			if( $single_option['type'] == 'submenu' ){
				add_submenu_page(  
				$single_option['parent_slug'],  
				$single_option['page_title'], 
				$single_option['menu_title'], 
				$single_option['capability'], 
				$single_option['menu_slug'], 
				array( $this, 'show_settings' ) 
				);
			}
			if( $single_option['type'] == 'option' ){
				add_options_page(  				  
				$single_option['page_title'], 
				$single_option['menu_title'], 
				$single_option['capability'], 
				$single_option['menu_slug'], 
				array( $this, 'show_settings' ) 
				);
			}
		}
		 
	}
	
	function show_settings(){
		?>
		<div class="wrap tw-bs4">
		
		
		
		<h2><?php _e('Settings', 'sc'); ?></h2>
		<hr/>
		<?php 
			echo $this->message;
		?>
		
		<form class="form-horizontal" method="post" action="">
		<?php 
		wp_nonce_field( 'save_settings_action', 'save_settings_field'  );  
		$config = get_option( $this->setttings_prefix.'_options'); 
		?>  
		<fieldset>

			<?php 
		foreach( $this->setttings_parameters as $single_page ){	
			foreach( $single_page['parameters'] as $key=>$value ){
				switch( $value['type'] ){
					case "separator":
						$out .= '
						<div class="lead">'.$value['title'].'</div> 
						';
					break;
					case "links_reporting":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
							
							  <table class="table">
								  <thead>
									<tr>
									  <th scope="col">Page</th>
									  <th scope="col">Links</th>
								 
									</tr>
								  </thead>
								  <tbody>';
								  
						global 		 $wpdb;
						$all_posts = $wpdb->get_results("SELECT post_id, meta_value FROM  {$wpdb->prefix}postmeta WHERE meta_key = 'total_replacements' ORDER BY meta_value DESC  LIMIT 50");
						if( count($all_posts) > 0 ){
							foreach( $all_posts as $single_id ){
								$out .= '
									<tr>
									  <th scope="row"><a target="_blank" href="'.get_permalink( $single_id->post_id ).'">'.get_permalink( $single_id->post_id ) .'</a></th>
									  <td>'.$single_id->meta_value.'</td>
					 
									</tr>';
							}
						}
								  
								  
						$out .= '
								  </tbody>
								</table> 
							
						  </div> 
						';
					break;
					case "text":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
							
							  <input type="text"  class="form-control '.$value['class'].'"  name="'.$value['name'].'" id="'.$value['id'].'" placeholder="'.$value['placeholder'].'" value="'.esc_html( stripslashes( $config[$value['name']] ) ).'">  
							  <p class="help-block">'.$value['sub_text'].'</p>  
							
						  </div> 
						';
					break;
					case "button":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="">&nbsp;</label>  
							
							  <a class="btn btn-success" href="'.$value['href'].'"   >'.$value['title'].'</a>  
							  
							
						</div> 
						';
					break;
					case "select":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
							 
							  <select  style="'.$value['style'].'" class="form-control '.$value['class'].'" name="'.$value['name'].'" id="'.$value['id'].'">' ; 
							  if( count( $value['value'] ) > 0 )
							  foreach( $value['value'] as $k => $v ){
								  $out .= '<option value="'.$k.'" '.( $config[$value['name']]  == $k ? ' selected ' : ' ' ).' >'.$v.'</option> ';
							  }
						$out .= '		
							  </select>  
							  <p class="help-block">'.$value['sub_text'].'</p> 
							</div>  
						 
						';
					break;
					case "checkbox":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
						
							  <label class="checkbox">  
								<input  class="'.$value['class'].'" type="checkbox" name="'.$value['name'].'" id="'.$value['id'].'" value="on" '.( $config[$value['name']] == 'on' ? ' checked ' : '' ).' > &nbsp; 
								'.$value['text'].'  
								<p class="help-block">'.$value['sub_text'].'</p> 
							  </label>  
							 
						  </div>  
						';
					break;
					case "radio":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>';
								foreach( $value['value'] as $k => $v ){
									$out .= '
									<label class="radio">  
										<input  class="'.$value['class'].'" type="radio" name="'.$value['name'].'" id="'.$value['id'].'" value="'.$k.'" '.( $config[$value['name']] == $k ? ' checked ' : '' ).' >&nbsp;  
										'.$v.'  
										<p class="help-block">'.$value['sub_text'].'</p> 
									  </label> ';
								}
							$out .= '
							
						  </div>  
						';
					break;
					case "textarea":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
						
							  <textarea style="'.$value['style'].'" class="form-control '.$value['class'].'" name="'.$value['name'].'" id="'.$value['id'].'" rows="'.$value['rows'].'">'.esc_html( stripslashes( $config[$value['name']] ) ).'</textarea>  
							  <p class="help-block">'.$value['sub_text'].'</p> 
						 
						  </div> 
						';
					break;
					case "multiselect":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
							 
							  <select  multiple="multiple" style="'.$value['style'].'" class="form-control '.$value['class'].'" name="'.$value['name'].'[]" id="'.$value['id'].'">' ; 
							  foreach( $value['value'] as $k => $v ){
								  $out .= '<option value="'.$k.'" '.( @in_array( $k, $config[$value['name']] )   ? ' selected ' : ' ' ).' >'.$v.'</option> ';
							  }
						$out .= '		
							  </select>  
							  <p class="help-block">'.$value['sub_text'].'</p> 
							 
						  </div>  
						';
					break;
					case "wide_editor":
					$out .= '<div class="form-group">  
						<label class="control-label" for="input01">'.$value['title'].'</label>
						<div class="form-control1">
						';  
						 
						ob_start();
						wp_editor( $config[$value['name']], $value['name'] );
						$editor_contents = ob_get_clean();	
					 
						$out .= $editor_contents;  
					$out .= '
						</div>
					  </div> ';	 
					 
					break;
					case "file":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
				 
							<input type="file" class="form-control-file '.$value['class'].'" name="'.$value['name'].''.( $value['multi'] ? '[]' : '' ).'" id="'.$value['id'].'" '.( $value['multi'] ? ' multiple ' : '' ).' >
							  
							  <p class="help-block">'.$value['sub_text'].'</p> 
						 
						  </div> 
						';
					break;
					case "save":
						$out .= '
						<div class="form-actions">  
							<button type="submit" class="btn btn-primary">Save Settings</button>  
						  </div>  
						';
					break;
				}
			}
		}
			echo $out;
			?>

				
				  
				</fieldset>  

		</form>

		</div>
		<?php
	}
}	

	
	
add_Action('init',  function (){
	$config_big = 
	array(

		array(
			'type' => 'submenu',
			'parent_slug' => 'edit.php?post_type=keyword_replace',
			'page_title' => __('Settings', $locale_taro),
			'menu_title' => __('Settings', $locale_taro),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'wwkr_settings',

			'parameters' => array(
 
				array(
					'type' => 'text',
					'title' => 'Max Links',
					'name' => 'max_links',
					'sub_text' => 'Max links number of links in single page/post for a single keyword'
				),
				array(
					'type' => 'save',
					 
				),
				 
			)
		)
	); 
	global $settings;

	$settings = new vooSettingsClassMainPowerLinks( 'wkr' ); 
	$settings->create_menu(  $config_big   );
	
	
	$config_big = 
	array(

		array(
			'type' => 'submenu',
			'parent_slug' => 'edit.php?post_type=keyword_replace',
			'page_title' => __('Reporting', $locale_taro),
			'menu_title' => __('Reporting', $locale_taro),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'wwkr_reporting',

			'parameters' => array(
 
		 
				array(
					'type' => 'links_reporting',
					'title' => '',
					 
					'sub_text' => 'Max links number of links in single page/post for a single keyword'
				), 
				 
			)
		)
	); 
	global $settings;

	$settings = new vooSettingsClassMainPowerLinks( 'wkr' ); 
	$settings->create_menu(  $config_big   );
	
	
} );
	
 

?>