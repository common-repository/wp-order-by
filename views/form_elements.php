<?php //if( !is_array( $args['options'] ) ) $args['options'] = array( $args['options'] ); var_dump($args['options']); ?>
<div class="wpob_option">
	
	<input id="order_date_desc" name="wpob-options-<?php echo $args['post_type']; ?>" type="radio" value="datedesc" <?php if($args['options'][0]=='datedesc') echo ' checked'; ?> />
	<label for="order_date_desc">Date Created</label>
</div>
<div class="wpob_option">
	
	<input id="order_modified" name="wpob-options-<?php echo $args['post_type']; ?>" type="radio" value="modified" <?php if($args['options'][0]=='modified') echo ' checked'; ?> />
	<label for="order_modified">Last Modified Date </label>
</div>
<div class="wpob_option">
	
	<input id="order_title" name="wpob-options-<?php echo $args['post_type']; ?>" type="radio" value="title" <?php if($args['options'][0]=='title') echo ' checked'; ?> />
	<label for="order_title">Title</label>
</div>

<div class="wpob_option">
	
	<input id="order_author" name="wpob-options-<?php echo $args['post_type']; ?>" type="radio" value="author" <?php if($args['options'][0]=='author') echo ' checked'; ?>  />
	<label for="order_author">Author</label>
</div>
<div class="wpob_option">
	
	<input id="order_post_id" name="wpob-options-<?php echo $args['post_type']; ?>" type="radio" value="ID" <?php if($args['options'][0]=='ID') echo ' checked'; ?>  />
	<label for="order_post_id">Post/Page Id</label>
</div>
<div class="wpob_option">
	
	<input id="order_parent" name="wpob-options-<?php echo $args['post_type']; ?>" type="radio" value="parent" <?php if($args['options'][0]=='parent') echo ' checked'; ?>  />
	<label for="order_parent">Post/Page Parent Id</label>
</div>
<div class="wpob_option">
	
	<input id="order_menu_order" name="wpob-options-<?php echo $args['post_type']; ?>" type="radio" value="menu_order" <?php if($args['options'][0]=='menu_order') echo ' checked'; ?>  />
	<label for="order_menu_order">Menu Order - the order value in the Attributes box. usually on the buttom-right of the page/post-type.</label>
</div>
<div class="wpob_option">
	
	<input id="order_rand" name="wpob-options-<?php echo $args['post_type']; ?>" type="radio" value="rand" <?php if($args['options'][0]=='rand') echo ' checked'; ?>  />
	<label for="order_rand">Random</label>
</div>
<div class="wpob_option">
	
	<input id="order_comments" name="wpob-options-<?php echo $args['post_type']; ?>" type="radio" value="comment_count" <?php if($args['options'][0]=='comment_count') echo ' checked'; ?>  />
	<label for="order_comments">Number of Comments</label>
</div>
<div class="wpob_option">
	<label for="order_custom_fields">By value of a custom field</label>
	<select id="order_custom_fields" name="wpob-options-<?php echo $args['post_type']; ?>">
		<option value=""><?php echo __('Select Custom Field','wp-order-by-plugin'); ?></option>
		<?php
			global $wpdb, $table_prefix;
			$sql = 'SELECT DISTINCT meta_key FROM '.$table_prefix.'postmeta WHERE meta_key NOT LIKE "\_%"';
			$custom_fields= $wpdb->get_results($sql);
			$checked='';
			$cfhtml = array();
			foreach ( $custom_fields as $field_key ) {
				if($args['options'][0] == $field_key->meta_key) { $checked = ' selected '; }
				else {$checked = ''; }
				$cfhtml[] = '<option value="'.$field_key->meta_key.'"'.$checked.'>'.$field_key->meta_key.'</option>';
			}
			echo implode("",$cfhtml);
		?>
	</select>
	<div class="custom_fields_type_container">
		<div class="description">What type of values this custom field contains:</div>
		<input id="custom_field_numeric" name="wpob-cf-type" type="radio" value="numeric" <?php if(is_array($args['options']) && $args['options'][1]=='numeric') echo ' checked'; ?>  /> <label for="custom_field_numeric">Numeric values</label> &nbsp;&nbsp;
		<input id="custom_field_string" name="wpob-cf-type" type="radio" value="string" <?php if(is_array($args['options']) && $args['options'][1]=='string') echo ' checked'; ?>  /> <label for="custom_field_string">String values</label>
		<br><br>
		<input id="wpob_other_posts" name="wpob_other_posts" type="checkbox" value="checked" <?php if( is_array($args['options']) && !empty($args['options'][2]) ) echo ' checked'; ?>  /> <label for="wpob_other_posts">Also display posts/pages that don't have this custom field</label>
		<p class="warning">Warning: if your custom field contains both numeric and string values this order option may give an unpredictable results </p>
	</div>
	<?php $order = end($args['options']); //var_dump($args['options']); ?>
	<br>
	<h3>Order Direction:</h3>
	<input type="radio" value="ASC" name="wpob_cf_order" id="cf_asc" <?php if(is_array($args['options']) && !empty($order) && $order=='ASC') echo ' checked'; ?>><label for="cf_asc">&nbsp;Ascending&nbsp;&nbsp;</label>
	<input type="radio" value="DESC" name="wpob_cf_order" id="cf_desc" <?php if(is_array($args['options']) && !empty($order) && $order=='DESC') echo ' checked'; ?> <?php if( empty($order) || ($order!='ASC' && $order!='DESC') ) echo ' checked '; ?>><label for="cf_desc">&nbsp;Descending</label>

	
</div>