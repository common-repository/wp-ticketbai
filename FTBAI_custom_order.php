<?php
//wp_enqueue_style('FTBAI_sweetalert-css', plugins_url( 'assets/sweetalert/sweetalert2.min.css', __FILE__ ),null,time(),'all');
//wp_enqueue_script('FTBAI_sweetalert-js',  plugins_url( 'assets/sweetalert/sweetalert2.all.min.js', __FILE__ ),null,time(),'all');
//wp_enqueue_script('FTBAI_procesos-js',  plugins_url( 'js/FTBAI_procesos.js', __FILE__ ),null,time(),'all');
//wp_enqueue_script('FTBAI_verpdf-js',  plugins_url( 'js/FTBAI_verpdf.js', __FILE__ ),null,time(),'all');

add_action( 'woocommerce_thankyou', 'FTBAI_verfactura_page', 20 );
function FTBAI_verfactura_page( $order_id ){  
	$results = FTBAI_getfield_facturas($order_id);
	if ($results->factura_serie!='' && $results->factura_numero!=''){
		$facnum = $results->factura_serie.' / '.$results->factura_numero;
		$verpdfcmd = "ftba_verpdf('".$order_id."');event.preventDefault();";
		echo '<h2>'.__( 'Invoice Available', 'wp-ticketbai' ).'</h2>
		<a href="#" onclick="'.$verpdfcmd.'"><u>'.__( 'Click here to download invoice', 'wp-ticketbai' ).' <b>'.sanitize_text_field($facnum).'</b></u></a>';
	}
}

//https://rudrastyh.com/woocommerce/columns.html
add_filter( 'manage_edit-shop_order_columns', 'FTBAI_edit_shop_order_columns' );
function FTBAI_edit_shop_order_columns( $columns ){
	$columns['facnum'] = __( 'Tbai / Invoice PDF', 'wp-ticketbai' );
    return $columns;
}
add_action( 'manage_shop_order_posts_custom_column' , 'FTBAI_order_items_column_cnt' );
function FTBAI_order_items_column_cnt( $colname ) {
	if( $colname == 'facnum' ) {
		global $post;
		$results = FTBAI_getfield_facturas($post->ID);
		$factura_serie = "";
		$factura_numero = "";
		$tbai_num = "";
		$tbaiqr = "";	
		$id_transaccion = "";	
		if($results!=null){
			$factura_serie = $results->factura_serie;
			$factura_numero = $results->factura_numero;
			$tbai_num = $results->tbai_num;
			$tbaiqr = $results->tbai_qr;	
			$id_transaccion = $results->id_transaccion;	
		}
		$facnum = $factura_serie.' / '.$factura_numero;
		if($factura_numero!='' && $tbai_num!='' && $tbaiqr!=''){
			echo '<div style="display:inline-flex;">';
			if ($tbai_num!='' && $tbaiqr!=''){
				$verqrcmd = "ftba_swaliframe('".esc_url($tbaiqr)."');event.preventDefault();";
				echo '<a title="'.esc_textarea(sanitize_text_field($tbai_num)).'" href="#" onclick="'.$verqrcmd.'" target="_blank"><img loading="lazy" height="40" src="'.plugins_url( 'assets/tbai.png', __FILE__ ).'"></a>';
			}
			$verpdfcmd = "ftba_verpdf('".$post->ID."');event.preventDefault();";
			echo '<button title="'.__( 'Download Invoice', 'wp-ticketbai' ).'" class="button-primary" style="margin-left:5px;background:#2a91e5;color:#fff;border-width:0px;width:120px;height: 40px;" onclick="'.$verpdfcmd.'"><span style="margin-top:5px;margin-left:-5px" class="dashicons dashicons-download"></span> '.sanitize_text_field($facnum).'</button>';
			echo '</div>';
		}else if($id_transaccion>0 && ($post->post_status=='wc-processing' || $post->post_status=='wc-completed')){
			echo '<div style="display:inline-flex;right:0px;">';
			echo '<a style="filter: blur(2px);"><img loading="lazy" height="40" src="'.plugins_url( 'assets/tbai.png', __FILE__ ).'"></a>';
			$verpdfcmd = "ftbai_emitir('".$post->ID."','".FTBAI_modopruebas()."');event.preventDefault();";
			echo '<button title="'.__('Generate Invoice','wp-ticketbai').'" class="button-primary" style="margin-left:5px;background: #f17171;color:#fff;border-width:0px;width:120px;height: 40px;" onclick="'.$verpdfcmd.'">'.__('Generate Invoice','wp-ticketbai').'</button>';
			echo '</div>';
		}	
	}
}
add_filter( 'woocommerce_account_orders_columns', 'FTBAI_add_account_orders_column', 10, 1 );
function FTBAI_add_account_orders_column( $columns ){
    $columns['custom-column'] = __( 'Tbai / Invoice PDF', 'wp-ticketbai' );
	$columns['custom-column-estado'] = __( 'Invoice Status', 'wp-ticketbai' );
    return $columns;
}
add_action( 'woocommerce_my_account_my_orders_column_custom-column', 'FTBAI_add_account_orders_column_rows' );
function FTBAI_add_account_orders_column_rows( $order ) {
	$results = FTBAI_getfield_facturas($order->ID);
	$factura_serie = "";
	$factura_numero = "";
	$tbai_num = "";
	$tbaiqr = "";	
	$id_transaccion = "";	
	if($results!=null){
		$factura_serie = $results->factura_serie;
		$factura_numero = $results->factura_numero;
		$tbai_num = $results->tbai_num;
		$tbaiqr = $results->tbai_qr;	
		$id_transaccion = $results->id_transaccion;	
	}
	$facnum = $factura_serie.' / '.$factura_numero;	
	if($factura_numero!='' && $tbai_num!='' && $tbaiqr!=''){
		echo '<div style="display:inline-flex;">';
		if ($tbai_num!='' && $tbaiqr!=''){
			echo '<a title="'.esc_textarea(sanitize_text_field($tbai_num)).'" href="'.esc_url($tbaiqr).'" target="_blank"><img loading="lazy" height="50" width="50" src="'.plugins_url( 'assets/tbai.png', __FILE__ ).'" style="width:50px;height:50px;"></a>';
		}
		$verpdfcmd = "ftba_verpdf('".$order->ID."');event.preventDefault();";
		echo '<button title="Descargar Factura" class="button-primary" style="margin-left:5px;background:#2a91e5;color:#fff;border-width:0px;width:150px;height:50px;padding:15px;" onclick="'.$verpdfcmd.'"><span style="margin-left:-5px" class="dashicons dashicons-download"></span> '.sanitize_text_field($facnum).'</button>';
		echo '</div>';
	}	
}
add_action( 'woocommerce_my_account_my_orders_column_custom-column-estado', 'FTBAI_estado_add_account_orders_column_rows' );
function FTBAI_estado_add_account_orders_column_rows( $order ) {
	$results = FTBAI_getfield_facturas($order->ID);
	$factura_estado = $results->factura_estado;
	if($factura_estado=='Anulada'){
		echo "<button class='button-primary' style='background:#f96565;color:#fff;border-width:0px;width:100px;height:50px;padding: 3px 12px 3px 12px;' onclick='event.preventDefault()'>".esc_textarea(sanitize_text_field($factura_estado))."</button>";
	}else if($factura_estado=='Pendiente'){
		echo "<button class='button-primary' style='background:#f8dda7;color:#94660c;border-width:0px;width:100px;height:50px;padding: 3px 12px 3px 12px;' onclick='event.preventDefault()'>".esc_textarea(sanitize_text_field($factura_estado))."</button>";
	}else if($factura_estado=='Emitida'){
		echo "<button class='button-primary' style='background:#359738;color:#fff;border-width:0px;width:100px;height:50px;padding: 3px 12px 3px 12px;' onclick='event.preventDefault()'>".esc_textarea(sanitize_text_field($factura_estado))."</button>";
	}
}

add_filter( 'woocommerce_account_menu_items', function($items) {
    $items['orders'] = __('Orders and Invoices', 'wp-ticketbai'); // Changing label for orders
    return $items;
}, 99, 1 );

add_action( 'save_post', 'FTBAI_save_custom_code_after_order_details', 10, 1 );
function FTBAI_save_custom_code_after_order_details( $post_id ) {

    // We need to verify this with the proper authorization (security stuff).

    // Check if our nonce is set.
    if ( ! isset( $_POST[ 'custom_select_field_nonce' ] ) ) {
        return $post_id;
    }
    $nonce = $_REQUEST[ 'custom_select_field_nonce' ];

    //Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $nonce ) ) {
        return $post_id;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    // Check the user's permissions.
    if ( 'page' == sanitize_text_field($_POST[ 'post_type' ]) ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        }
    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
    }

	$author_id = get_post_field( 'post_author', $post_id );
    // Update the meta field in the database.
    update_post_meta( $post_id, '_vat_number', sanitize_text_field( $_POST[ 'vat_number' ] ) );
	update_user_meta( $author_id, 'vat_number', sanitize_text_field( $_POST['vat_number'] ) );
}