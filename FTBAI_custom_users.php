<?php
//añade columna tarifa en usuarios
function FTBAI_modify_user_table( $column ) {
	$column['vat_number'] = 'CIF/NIF';
    return $column;
}
add_filter( 'manage_users_columns', 'FTBAI_modify_user_table' );
function FTBAI_modify_user_table_row( $val, $column_name, $user_id ) {
    switch ($column_name) {
		case 'vat_number' :
			return get_the_author_meta( 'vat_number', $user_id );
        default:
    }
    return $val;
}
add_filter( 'manage_users_custom_column', 'FTBAI_modify_user_table_row', 10, 3 );





/**  Show VAT Number in WooCommerce Checkout  */
function FTBAI_claserama_rearrange_checkout_fields($fields){
	if (1==2){
		$city_args = wp_parse_args( array(
			'type' => 'select',
			'priority' =>  $fields['billing']['billing_last_name']['priority'] + 1,
			'required' => TRUE,
			'label' => __( 'Select type of invoice Individual / Company', 'wp-ticketbai' ),
			'options' => array('' => '', 'particular' => 'Particular - Venta Simplificada','empresa' => 'Empresa - Factura Completa'),
	//		'input_class' => array('select2-selection', 'select2-selection--single',)
		), $fields['billing']['billing_tipo_factura'] );
		$fields['billing']['billing_tipo_factura'] = $city_args; 

		$fields['billing']['billing_tipo_factura']['class'][0] = 'form-row validate-required form-row-wide';
	//	$fields['billing']['billing_tipo_factura']['label'] = __( 'Particular / Empresa', 'wp-ticketbai' );
	   // $fields['billing']['billing_tipo_factura']['priority'] = $fields['billing']['billing_last_name']['priority'] + 1;
	//	$fields['billing']['billing_tipo_factura']['required'] = TRUE;
	}
	
	$FONE_total_checkout = FONE_total_checkout();
	if (get_option('FTBAI_shownif') > 0 || $FONE_total_checkout > get_option('FTBAI_maxsimplificada')){
		$fields['billing']['billing_company']['class'][0] = 'form-row-first';
		$fields['billing']['billing_company']['label'] = __( 'Company Name', 'wp-ticketbai' );
		if (isset( $fields['billing']['billing_company']['priority'] )){
			$fields['billing']['vat_number']['priority'] = $fields['billing']['billing_company']['priority'] + 1;
		}
		$fields['billing']['vat_number']['class'][0] = 'form-row-last';
		$fields['billing']['vat_number']['placeholder'] = __( 'Enter your CIF or DNI', 'wp-ticketbai' );
		$fields['billing']['vat_number']['label'] = __( 'NIF/CIF/NIE', 'wp-ticketbai' );
		if($FONE_total_checkout > get_option('FTBAI_maxsimplificada') || get_option('FTBAI_shownif')==2){
			$fields['billing']['vat_number']['required'] = TRUE;
		}
		//si esta logeado y tiene billing_nif
		if ( is_user_logged_in() ) {
			if(get_option('FTBAI_clientesRE')==1){
				$user_id = get_current_user_id();
				$customer_nif = get_user_meta( $user_id, 'billing_nif', true );
				if($customer_nif){$fields['billing']['vat_number']['default'] = $customer_nif;}
			}
		}
	}else{
		unset($fields['billing']['billing_company']);
	}
	return $fields;
}
add_filter('woocommerce_checkout_fields','FTBAI_claserama_rearrange_checkout_fields');

function FONE_total_checkout() {
	$wc = WC();
	$session = $wc->session;
	if ($session && $session->has_session()) {
		$total = $session->get('cart_totals')['total'];
		return $total;
	} else {
		return 0;
	}
}

/** Save VAT Number in the order meta */
function FTBAI_woocommerce_checkout_vat_number_update_order_meta( $order_id ) {
  if ( ! empty( $_POST['vat_number'] ) ) {
	  if ( is_user_logged_in() ) {
		 $user_id = get_current_user_id();
		 update_user_meta( $user_id, 'vat_number', sanitize_text_field( $_POST['vat_number'] ) );
		 update_user_meta( $user_id, 'billing_nif', sanitize_text_field( $_POST['vat_number'] ) );
		 update_post_meta( $order_id, '_vat_number', sanitize_text_field( $_POST['vat_number'] ) );
	  }else{
		 update_post_meta( $order_id, '_vat_number', sanitize_text_field( $_POST['vat_number'] ) );
	  }
  }
}
add_action( 'woocommerce_checkout_update_order_meta', 'FTBAI_woocommerce_checkout_vat_number_update_order_meta' ); 
//check field order
add_action('woocommerce_checkout_process', 'FTBAI_checkout_field_process');
function FTBAI_checkout_field_process() {
	// Check if set, if its not set add an error.
	$billing_company = sanitize_text_field($_POST['billing_company']);
	$vat_number = sanitize_text_field($_POST['vat_number']);
	$billing_country = sanitize_text_field($_POST['billing_country']);
	if($billing_company && $vat_number==''){
		wc_add_notice( __('We need your <b>tax number</b> to be able to invoice your company','wp-ticketbai'),'error');
	}
	
	if($vat_number=='' && get_option('FTBAI_shownif')==2){
		//identificación obligatorio
		wc_add_notice( __( 'You must enter document number NIF/CIF/NIE', 'wp-ticketbai' ), 'error' );
	}else{
		//si es español comprueba identificacion correcta
		if($vat_number!='' && $billing_country=='ES'){
			$resultado = FTBAI_validDniCifNie($vat_number);
			if($billing_company && $resultado==1){
				wc_add_notice( __( 'The <b>NIF/CIF/NIE</b> belongs to a person, it is not a company. Leave company name blank', 'wp-ticketbai' ), 'error' );
			}else if($billing_company=='' && $resultado==2){
				wc_add_notice( __( 'The <b>NIF/CIF/NIE</b> is from a company. You need write the name of the company.', 'wp-ticketbai' ), 'error' );
			}else if ( $resultado==0 ){
				wc_add_notice( __( 'The <b>NIF/CIF/NIE</b> is not correct', 'wp-ticketbai' ), 'error' );
			}
		}
		//si es EU-Ceuta-Melilla-Canarias requiere identificacion
		if($billing_country){
			if($billing_country=='ES'){
				$countries_obj = new WC_Countries();
				$country_states_array = $countries_obj->get_states();
				$state_name = $country_states_array[$billing_country][$_POST['billing_state']];
				if($state_name=='Ceuta' || $state_name=='Melilla' || $state_name=='Santa Cruz de Tenerife' || $state_name=='Las Palmas'){
					if($vat_number==''){
						wc_add_notice( __( 'You must enter document number NIF/CIF/NIE', 'wp-ticketbai' ), 'error' );
					}
	//				wc_add_notice( $state_name .'... '. __( 'not available for sale', 'wp-ticketbai' ), 'error' );
				}
			}else if (FTBAI_isEU($billing_country)==TRUE){
				if($vat_number==''){
					wc_add_notice( __( 'You must enter document number NIF/CIF/NIE', 'wp-ticketbai' ), 'error' );
				}
	//			wc_add_notice( __( 'Country not available for sale', 'wp-ticketbai' ), 'error' );
			}
		}	
	}
	//control RE
	if($vat_number==''){
		$FTBAI_clientesRE=get_option('FTBAI_clientesRE');
		if($FTBAI_clientesRE==1){
			$billing_recargo_de_equivalencia = sanitize_text_field($_POST['billing_recargo_de_equivalencia']);
			if($billing_recargo_de_equivalencia==1){
				wc_add_notice( __('Para seleccionar Recargo Equivalencia debe introducir su NIF/CIF/NIE', 'wp-ticketbai' ), 'error' );
			}
		}
	}
	//wc_add_notice( __( 'aaa', 'wp-ticketbai' ), 'error' );
	//$var = print_r($_POST, true);wc_add_notice( $var, 'error' );
}
/** Display VAT Number in order edit screen */
function FTBAI_woocommerce_vat_number_display_admin_order_meta( $order ) {
	echo '<strong>' . __( 'CIF', 'wp-ticketbai' ) . ':</strong> ';
	$value = get_post_meta( $order->get_id(), '_vat_number', true );
	//<style> ::placeholder{color:red;opacity:1;} :-ms-input-placeholder{color:red;} ::-ms-input-placeholder{color:red;}</style>
	echo '<input type="text" placeholder="Introduzca NIF del cliente" type="text" class="input-text" name="vat_number" value="'.$value.'" cols="20" rows="5"><input type="hidden" name="custom_select_field_nonce" value="'.wp_create_nonce().'">';
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'FTBAI_woocommerce_vat_number_display_admin_order_meta', 10, 1 );



// Add the custom field "vatnumber"
add_action( 'woocommerce_edit_account_form', 'FTBAI_vatnumber' );
function FTBAI_vatnumber() {
    $user = wp_get_current_user();
    ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="vat_number"><?php __( 'Fiscal Number NIF/CIF/NIE', 'wp-ticketbai' ); ?></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="vat_number" id="vat_number" value="<?php echo esc_attr( $user->vat_number ); ?>" />
    </p>
    <script type="text/javascript">
		var passwordField = document.getElementById('password_current');
		if (passwordField) {
			passwordField.setAttribute('autocomplete', 'new-password');
		}
    </script>
    <?php
}
// Save the custom field 'vatnumber' 
add_action( 'woocommerce_save_account_details', 'FTBAI_save_vatnumber', 12, 1 );
function FTBAI_save_vatnumber( $user_id ) {
    if( isset( $_POST['vat_number'] ) ) {
        update_user_meta( $user_id, 'vat_number', sanitize_text_field( $_POST['vat_number'] ) );
		update_user_meta( $user_id, 'billing_nif', sanitize_text_field( $_POST['vat_number'] ) );
	}
}
?>