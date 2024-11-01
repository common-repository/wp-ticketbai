<?php
/*
* Plugin Name: WP TicketBAI Facturas
* Plugin URI: https://wp-tbai.com
* Description: Emite Facturas desde tu WooCommerce a TicketBAI con el código QR desde WordPress, gestiona fácilmente Anulaciones, Rectificatvas, Facturas PDF Tbai. Cumple con la normativa de facturación Batuz TicketBAI.
* Version: 2.96
* Author: FacturaOne
* Author URI: https://www.facturaone.com
* License: GPLv3
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
* Text Domain: wp-ticketbai
* Domain Path: /languages/
*/
require_once('FTBAI_custom_order.php');
require_once('FTBAI_custom_users.php');
require_once('FTBAI_facturas.php');
require_once('FTBAI_procesos.php');
require_once('FTBAI_wcpdf.php');
if (is_admin()){FTBAI_get_version();}

//variables generales
add_action('init', 'FTBAI_variables_generales');
function FTBAI_variables_generales(){
	if (get_option('FTBAI_copyemail')==''){update_option('FTBAI_copyemail','');}
	if (get_option('FTBAI_empresaroi')==''){update_option('FTBAI_empresaroi',0);}
	if (get_option('FTBAI_operacionextranjero')==''){update_option('FTBAI_operacionextranjero',0);}
	if (get_option('FTBAI_emitefactautomatica')==''){update_option('FTBAI_emitefactautomatica',0);}
	if (get_option('FTBAI_sendfactautomatica')==''){update_option('FTBAI_sendfactautomatica',0);}
	if (get_option('FTBAI_shownif')==''){update_option('FTBAI_shownif',1);}
	if (get_option('FTBAI_clientesRE')==''){update_option('FTBAI_clientesRE',0);}
	if (get_option('FTBAI_DNI_TEST')==''){update_option('FTBAI_DNI_TEST','B72490527');}
	if (get_option('FTBAI_CLAVE_TEST')==''){update_option('FTBAI_CLAVE_TEST','exgz7urd9v95');}
	if (get_option('FTBAI_maxsimplificada')==''){update_option('FTBAI_maxsimplificada',400);}
	if (get_option('FTBAI_canariasnoexentoiva')==''){update_option('FTBAI_canariasnoexentoiva',0);}
	if (get_option('FTBAI_permitefueraUE')==''){update_option('FTBAI_permitefueraUE',0);}
	if (get_option('FTBAI_apartirnumeropedido')==''){update_option('FTBAI_apartirnumeropedido',0);}
	if (get_option('FTBAI_posicionQR')==''){update_option('FTBAI_posicionQR',0);}
}
add_action('plugins_loaded','FTBAI_plugin_load_textdomain');
function FTBAI_plugin_load_textdomain(){
	if (function_exists('load_plugin_textdomain')) {
		load_plugin_textdomain('wp-ticketbai',false,dirname( plugin_basename( __FILE__ ) ).'/languages/');
	}
}
//carga style
function FTBAI_add_plugin_stylesheet() 
    {
		wp_enqueue_style( 'FTBAI_style', plugins_url( 'assets/FTBAI_main.css', __FILE__ ) );

		wp_enqueue_style('FTBAI_sweetalert-css', plugins_url( 'assets/sweetalert/sweetalert2.min.css', __FILE__ ),null,time(),'all');
		wp_enqueue_script('FTBAI_sweetalert-js',  plugins_url( 'assets/sweetalert/sweetalert2.all.min.js', __FILE__ ),null,time(),'all');
		wp_enqueue_script('FTBAI_procesos-js',  plugins_url( 'js/FTBAI_procesos.js', __FILE__ ),null,time(),'all');
		wp_enqueue_script('FTBAI_verpdf-js',  plugins_url( 'js/FTBAI_verpdf.js', __FILE__ ),null,time(),'all');
    }
//add_action('admin_print_styles', 'FTBAI_add_plugin_stylesheet');
add_action( 'admin_enqueue_scripts', 'FTBAI_add_plugin_stylesheet' );
add_action( 'wp_enqueue_scripts', 'FTBAI_add_plugin_stylesheet' );

//añade menu en woocoomerce
add_action('admin_menu', 'FTBAI_register_my_custom_submenu_page');
function FTBAI_register_my_custom_submenu_page() {
    add_submenu_page( 'woocommerce', __('Invoices TicketBAI','wp-ticketbai'), __('Invoices TicketBAI','wp-ticketbai'), 'manage_options', 'WPTicketBAI', 'FTBAI_pagina_de_opciones' ); 
	//add_options_page('TicketBAI','TicketBAI','read','WPTicketBAI','FTBAI_pagina_de_opciones');
}
//html con actions
function FTBAI_pagina_de_opciones(){
	require_once('FTBAI_index.php');
}
//añade ajustes dentro lista de plugins
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'FTBAI_plugin_add_settings_link' );
function FTBAI_plugin_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=WPTicketBAI">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}

add_action( 'save_post', function( $order_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! isset( $_POST['post_type'] ) ) return;
	if ( 'shop_order' != $_POST['post_type'] ) return;
	if ( ! current_user_can( 'edit_post', $order_id ) ) return;
	$nif=trim(get_post_meta( $order_id, '_vat_number', true ));
	$order = new WC_Order($order_id);
	$results = FTBAI_getfield_facturas($order_id); 
	if($results!=null && $results->tbai_num!=''){
		$order->add_order_note("Los datos del cliente no se modifican en la factura, porque ya esta emitida a TicketBai.");
	}else{
		$nombre=$order->get_billing_first_name().' '.$order->get_billing_last_name();
		$nombre_empresa=trim($order->get_billing_company());
		if($nombre_empresa!='' && $nif!=''){$nombre=$nombre_empresa;}
		FTBAI_update($order_id,'cliente_nombre',$nombre);
		FTBAI_update($order_id,'cliente_nif',$nif);
		FTBAI_update($order_id,'factura_total',$order->total);
	}
});
add_action( 'woocommerce_order_status_changed', 'FTBAI_order_status_changed', 99, 3 );
function FTBAI_order_status_changed( $order_id, $old_status, $new_status ){
	$order = new WC_Order( $order_id );
	$order_total = $order->get_total();
	if($order_total==0){return;}
	//pending -> pendiente pago
	//processing -> procesando
	//on-hold -> en espera (transferencia bancaria)
	//completed -> completado
	//cancelled -> cancelado
	//refunded -> reembolsado
	//failed -> fallido
	//se genera cuando es contrareembolso o visa
	
	// Verifica si el pedido tiene el metadato '_wcpdf_invoice_number'
	//$wcpdf_invoice_number = $order->get_meta('_wcpdf_invoice_number');
	//if($wcpdf_invoice_number){return;}
	$FTBAI_apartirnumeropedido = get_option('FTBAI_apartirnumeropedido');
	if($FTBAI_apartirnumeropedido>0 && $order_id<$FTBAI_apartirnumeropedido){return;}

	if($new_status=="cancelled"){
		//si no facturado y cancelado -> pedido cancelado
		$results = FTBAI_getfield_facturas($order_id);
		$factura_tipo = $results->factura_tipo;
		if($factura_tipo==''){FTBAI_update($order_id,'factura_estado','Pedido Cancelado');}
	}else{
		FTBAI_inserta_factura($order_id,FALSE); //FALSE = INSERT IGONRE
	}
		
//	if($new_status=="cancelled"){
//		//si no facturado y cancelado -> pedido cancelado
//		$results = FTBAI_getfield_facturas($order_id);
//		$factura_tipo = $results->factura_tipo;
//		if($factura_tipo==''){FTBAI_update($order_id,'factura_estado','Pedido Cancelado');}
//	}else if( $new_status == "on-hold" || $new_status == "pending" || get_option('FTBAI_emitefactautomatica')==0 ) {
//		//se graba linea de factura PENDIENTE confirmar pago
//		FTBAI_inserta_factura($order_id,FALSE);
//	}else if( $new_status == "processing" && get_option('FTBAI_emitefactautomatica')==2 ) {
//		//se graba linea de factura PENDIENTE confirmar pago
//		FTBAI_inserta_factura($order_id,FALSE);
//	}else 
	
	if( ($new_status=="processing" || $new_status=="completed") && get_option('FTBAI_emitefactautomatica')==1 ) {
		if(!is_admin() && strpos( $_SERVER['REQUEST_URI'], 'wp-admin/post.php') === false) { 
			//si ha llegado pago,o se ha cambiado a procesando
			$resultado = FTBAI_emitirfactura($order_id);
			if ($resultado==''){
				FTBAI_inserta_factura($order_id,TRUE);
				//echo json_encode(array());die; //no hay respuesta servidor
			}else{
				$response=json_decode($resultado);
				if($response->success==0){
					FTBAI_inserta_factura($order_id,TRUE);
				} 
			}
		}
	}else if( $new_status=="completed" && get_option('FTBAI_emitefactautomatica')==2 ) {
		//&& strpos( $_SERVER['REQUEST_URI'], 'wp-admin/post.php') !== false
		if(is_admin()) { 
			//si ha llegado pago,o se ha cambiado a completado
			$resultado = FTBAI_emitirfactura($order_id);
			$response=json_decode($resultado);
			if ($response->success!=0){
				//$order = new WC_Order($order_id);
				$order->add_order_note("Se ha emitido y enviado la factura a TicketBai.");
			}
		}
//	}else if(($new_status=="on-hold" || $new_status=="processing" || $new_status=="pending") && get_option('FTBAI_emitefactautomatica')==3){
//		//se graba linea de factura PENDIENTE confirmar pago
//		FTBAI_inserta_factura($order_id,FALSE);
	}else if($new_status=="completed" && get_option('FTBAI_emitefactautomatica')==3) {
		//si ha llegado pago,o se ha cambiado a procesando
		$resultado = FTBAI_emitirfactura($order_id);
		if ($resultado==''){
			FTBAI_inserta_factura($order_id,TRUE);
			//echo json_encode(array());die; //no hay respuesta servidor
		}else{
			$response=json_decode($resultado);
			if($response->success==0){
				FTBAI_inserta_factura($order_id,TRUE);
			} 
		}
	}
}
function FTBAI_registrarse(){
	$to = 'info@facturaone.com';
	$subject = 'Registro usuario WPTicketBai';
	$body = '<h4>Registro de usuario del plugin WPTicketBai</h4><br>';
	$body.= 'Nombre: '. get_option('FTBAI_REG_NOMBRE').'<br>';
	$body.= 'Dni: '. get_option('FTBAI_REG_DNI').'<br>';
	$body.= 'Email: '. get_option('FTBAI_REG_EMAIL').'<br>';
	$body.= 'Telefono: '. get_option('FTBAI_REG_TELEFONO').'<br><br>';
	$body.= 'Mensaje:<br> '. nl2br(get_option('FTBAI_REG_MENSAJE')).'<br>';
	$headers = array('Content-Type: text/html; charset=UTF-8');
	wp_mail( $to, $subject, $body, $headers );
	update_option('FTBAI_REG_MENSAJE','');
}

add_action('init', function() {
	//$order = new WC_Order(5105); echo $order->get_shipping_total(); echo $order->get_shipping_tax(); print_r($order);die;
	if( isset( $_POST['action'] ) ){$action = sanitize_text_field($_POST['action']);}else{$action="";}
	if( isset( $_POST['postid'] ) ){$postid = sanitize_text_field($_POST['postid']);}else{$postid="";}
	if($action=='verpdf' && $postid>0){
		$fileurl = FTBAI_verpdf($postid);
		$upload_dir = wp_upload_dir();
		$basepdf = $upload_dir['basedir'].$fileurl;
		$urlpdf = $upload_dir['baseurl'].$fileurl;
		if (is_ssl() && strpos($urlpdf, 'http://') === 0) {$urlpdf = str_replace('http://', 'https://', $urlpdf);}
		echo json_encode(array('success'=>1,'urlpdf'=>$urlpdf,'basepdf'=>$basepdf));die;
	}else if($action=='verxml' && $postid>0){
		$results = FTBAI_getfield_facturas($postid);
		$namefile = $results->factura_serie.'-'.$results->factura_numero;
		$fileurl = FTBAI_verxml($postid);
		$upload_dir = wp_upload_dir();
		$basexml = $upload_dir['basedir'].$fileurl;
		$urlxml = $upload_dir['baseurl'].$fileurl;	
		if (is_ssl() && strpos($urlxml, 'http://') === 0) {$urlxml = str_replace('http://', 'https://', $urlxml);}
		echo json_encode(array('success'=>1,'urlxml'=>$urlxml,'basexml'=>$basexml,'namefile'=>$namefile));die;		
	}else if($action=='delpdf' && isset($_POST['basepdf'])){
		$basepdf = sanitize_text_field($_POST['basepdf']);
		unlink($basepdf);
	}else if (is_user_logged_in()) {
		if($action!='' && $postid>0) {
			if($postid>0){
				if($action=='emitir'){
					$resultado = FTBAI_emitirfactura($postid);
					global $wpdb;
					//$order = new WC_Order($postid);
					//if (!empty($order)) {$order->update_status('wc-processing');}
					//$updatesql = $wpdb->get_results(" UPDATE `{$wpdb->prefix}posts` SET `post_status`='wc-processing' WHERE  `ID`=".$postid."; ");
					$res = json_decode($resultado);
					$res->enviaremail = get_option('FTBAI_sendfactautomatica');
					echo json_encode($res);die;
				}else if($action=='eliminar'){
					global $wpdb;
					FTBAI_update($postid,'factura_estado','Pedido Cancelado');
					$order = new WC_Order($postid);
					if (!empty($order)) {$order->update_status('wc-cancelled');}
					echo json_encode(array('success'=>1));die;
				}else if($action=='recuperapedido'){
					global $wpdb;
					FTBAI_update($postid,'factura_estado','Pendiente');
					$order = new WC_Order($postid);
					if (!empty($order)) {$order->update_status('wc-on-hold');}
					echo json_encode(array('success'=>1));die;
				}else if($action=='enviaremail'){
					if( isset( $_POST['email'] ) ){$email = sanitize_text_field($_POST['email']);}else{$email="";}
					$enviado = FONE_enviaremail($postid,$email);
					if($enviado){
						echo json_encode(array('success'=>1));die;					
					}else{
						echo json_encode(array('success'=>0));die;					
					}
				}else if($action=='get_billing_email'){
					$results = FTBAI_getfield_facturas($postid);
					$factura_rectificativa_de = $results->factura_rectificativa_de;
					if($factura_rectificativa_de>0){
						$order = new WC_Order($factura_rectificativa_de);
						$email=$order->get_billing_email();
						echo $email;
					}else{
						$order = new WC_Order($postid);
						echo $order->get_billing_email();
					}
					die;
				}else if($action=='datosfactura'){
					$results = FTBAI_getfield_facturas($postid); 
					$cliente_nif = $results->cliente_nif;
					$cliente_nombre = $results->cliente_nombre;
					$order = new WC_Order($postid);
					$domicilio=$order->get_billing_address_1().' '.$order->get_billing_address_2();
					$codigo_postal=$order->get_billing_postcode();
					$localidad=$order->get_billing_city();
					$datosfactura='<b><u>DATOS DEL CLIENTE</u></b><br>'.$cliente_nombre.'<br>';
					if($cliente_nif!=''){$datosfactura.=$cliente_nif.'<br>';}
					$datosfactura.=$domicilio.'<br>'.$codigo_postal.' '.$localidad.'<br>Pedido: #'.$postid.'<br>Tipo Factura: ';
					if($cliente_nif==''){$datosfactura.='Factura Simplificada';}else{$datosfactura.='Factura Completa';}
					//echo json_encode(array('success'=>1,'datosfactura'=>$datosfactura));die;
					echo json_encode(array('success'=>1, 'datosfactura'=>$datosfactura, 'order_data'=>$order->get_data() ));die;
				}else if($action=='rectificativa'){
					//selecciona items
					$results = FTBAI_getfield_facturas($postid); 
					$cliente_nif = $results->cliente_nif;
					$cliente_nombre = $results->cliente_nombre;
					$numfac =  $results->factura_serie.'/'.$results->factura_numero;
					$order = new WC_Order($postid);
					//$cliente_nombre=$order->get_billing_first_name().' '.$order->get_billing_last_name();
					//$cliente_nif = get_post_meta( $postid, '_vat_number', true );
					$domicilio=$order->get_billing_address_1().' '.$order->get_billing_address_2();
					$codigo_postal=$order->get_billing_postcode();
					$localidad=$order->get_billing_city();
					$datosfactura='<b><u>DATOS DEL CLIENTE</u></b><br>'.$cliente_nombre.'<br>';
					if($cliente_nif!=''){$datosfactura.=$cliente_nif.'<br>';}
					$datosfactura.=$domicilio.'<br>'.$codigo_postal.' '.$localidad.'<br>Pedido: #'.$postid.'<br>Tipo Factura: ';
					if($cliente_nif==''){$datosfactura.='Factura Simplificada';}else{$datosfactura.='Factura Completa';}
					//https://www.businessbloomer.com/woocommerce-easily-get-order-info-total-items-etc-from-order-object/
					$detalles=array();
					
					//carga lineas detalles
					foreach ( $order->get_items() as $item_id => $item ) {
						//añade itemlinea
						$precio_unidad = ($item['total']+$item['total_tax'])/$item['quantity'];
						$totalitem = $item['total']+$item['total_tax'];
						//$precio_unidad = ($item['subtotal']+$item['subtotal_tax'])/$item['quantity'];
						//$totalitem = $item['subtotal']+$item['subtotal_tax'];
						array_push($detalles,array( 	"item_id"				=>  $item_id,
														"nombre"				=>	mb_strtoupper($item['name']).'...', 
														"precio_unidad"			=>	number_format($precio_unidad,2).' €',
														"cantidad"				=>	$item['quantity'],
												   		"total"					=>	number_format($totalitem,2)
												   )
						);		
					}
					
					//linea de envio transporte
					if($order->get_shipping_total()){
						foreach( $order->get_items('shipping') as $item_id => $item_ship ){
							$totalitem=$item_ship->get_total()+$item_ship->get_total_tax();
							array_push($detalles,array( 	"item_id"				=>	-1,
															"nombre"				=>	mb_strtoupper($item_ship->get_name()).'...', 
															"precio_unidad"			=>	number_format($totalitem,2).' €',
															"cantidad"				=>	1,
															"total"					=>	number_format($totalitem,2)
													  )
							);
						}						
					}
					
					//lineas de cuota
					$get_fees = $order->get_fees();
					if(count($get_fees)>0){
						foreach( $order->get_items('fee') as $item_id => $item_fee ){
							$totalitem=$item_fee->get_total()+$item_fee->get_total_tax();
							array_push($detalles,array( 	"item_id"				=>	$item_id,
															"nombre"				=>	mb_strtoupper($item_fee->get_name()).'...', 
															"precio_unidad"			=>	number_format($totalitem,2).' €',
															"cantidad"				=>	1,
															"total"					=>	number_format($totalitem,2)
													  )
									  );								
						}
					}
					
					//cupones
//					$get_discount_total = $order->get_discount_total();
//					if($get_discount_total!=0){
//						$get_discount_tax = $order->get_discount_tax();
//						$totalitemcupon = $get_discount_total+$get_discount_tax;
//						array_push($detalles,array( 	"item_id"				=>	-2,
//														"nombre"				=>	'DESCUENTO', 
//														"precio_unidad"			=>	number_format($totalitemcupon*-1,2).' €',
//														"cantidad"				=>	1,
//														"descuento_porcentual"	=>	0,
//														"total"					=>	$totalitemcupon*-1,
//														"re"					=>	0)
//						);		
//					}		
					
					echo json_encode(array('success'=>1,'numfac'=>$numfac,'datosfactura'=>$datosfactura,'detalles'=>$detalles));die;
				}else if($action=='crearectificativa'){
					//crear factura rectificativa
					if( isset( $_POST['total'] ) ){$total = sanitize_text_field($_POST['total']);}else{$total="";}
					if( isset( $_POST['arrlineas'] ) ){$arrlineas = sanitize_text_field($_POST['arrlineas']);}else{$arrlineas="";}
					global $wpdb;
					// Create post object rectificativa
					$my_post = array(
					  'post_title'    => 'Rectificativa',
					  'post_content'  => '',
					  'post_status'   => 'wc-rectificativa',
					  'post_author'   => 1
					);
					$rectificativaid = wp_insert_post( $my_post );
					//cancela pedido
					$order = new WC_Order($postid);
					if (!empty($order)) {$order->update_status('wc-cancelled');}
					//crea linea factura rectificativa con ref al $rectificativaid
					$results = FTBAI_getfield_facturas($postid); 
					$nombre = $results->cliente_nombre;
					$cliente_nif = $results->cliente_nif;
					$total = $total*-1; // ($results->factura_total)*-1;
					$user_nif = get_option('FTBAI_DNI');
					$tbaicode = get_option('FTBAI_CLAVE');
					if($user_nif=='' || $tbaicode==''){
						$user_nif=get_option('FTBAI_DNI_TEST');
						$tbaicode=get_option('FTBAI_CLAVE_TEST');
					}
					$insertsql = $wpdb->get_results("
						REPLACE INTO `{$wpdb->prefix}ftbai_facturas` 
							(`id_transaccion`, `user_dni`, `user_apikey`, `fecha_transaccion`, `pedido_numero`, `pedido_fecha`, `factura_estado`, `factura_tipo`, `factura_rectificativa_de`, `factura_vista`, `factura_serie`, `factura_numero`, `factura_fecha`, `factura_total`, `cliente_nombre`, `cliente_nif`, `tbai_num`, `tbai_qr`)
						VALUES
							('".$rectificativaid."', '".$user_nif."', '".$tbaicode."', NOW(), '".$rectificativaid."', NOW(), 'Pendiente', 'RECTIFICATIVA', '".$postid."', '0000-00-00 00:00:00', '', '', NOW(), '".$total."', '".$nombre."', '".$cliente_nif."', '', '')
					" );
					FTBAI_update($postid,'factura_rectificada_en',$rectificativaid);
					//registra en tbai la nueva rectificativa con valores del postid original en el postid_rectificativa
					$resultado = FTBAI_registrarectificativa($postid,$rectificativaid,$arrlineas);
					if ($resultado==''){
						echo json_encode(array());die; //no hay respuesta servidor 
					}else{
						$response=json_decode($resultado);
						if($response->success==0){
							echo json_encode(array());die; //si success = 0
						} 
					}
					echo json_encode(array('success'=>1));die; //si success = 0
				}else if($action=='creafacmanual'){
					//crear factura manual
					if( isset( $_POST['total'] ) ){$total = sanitize_text_field($_POST['total']);}else{$total="";}
					global $wpdb;
					// Create post object facmanual
					$my_post = array(
					  'post_title'    => 'facmanual',
					  'post_content'  => '',
					  'post_status'   => 'wc-facmanual',
					  'post_author'   => 1
					);
					$facmanualid = wp_insert_post( $my_post );
					$order = new WC_Order($postid);

					//crea linea factura nueva fac con ref al $facmanualid
					$results = FTBAI_getfield_facturas($postid); 
					$nombre = $results->cliente_nombre;
					$cliente_nif = $results->cliente_nif;
					$user_nif = get_option('FTBAI_DNI');
					$tbaicode = get_option('FTBAI_CLAVE');
					if($user_nif=='' || $tbaicode==''){
						$user_nif=get_option('FTBAI_DNI_TEST');
						$tbaicode=get_option('FTBAI_CLAVE_TEST');
					}
					$insertsql = $wpdb->get_results("
						REPLACE INTO `{$wpdb->prefix}ftbai_facturas` 
							(`id_transaccion`, `user_dni`, `user_apikey`, `fecha_transaccion`, `pedido_numero`, `pedido_fecha`, `factura_estado`, `factura_tipo`, `factura_rectificativa_de`, `factura_vista`, `factura_serie`, `factura_numero`, `factura_fecha`, `factura_total`, `cliente_nombre`, `cliente_nif`, `tbai_num`, `tbai_qr`)
						VALUES
							('".$facmanualid."', '".$user_nif."', '".$tbaicode."', NOW(), '', NOW(), 'Pendiente', '', '".$postid."', '0000-00-00 00:00:00', '', '', NOW(), '".$total."', '".$nombre."', '".$cliente_nif."', '', '')
					" );
					//registra en tbai la nueva factura
					$resultado = FTBAI_creafacmanual($postid,$facmanualid);
					if ($resultado==''){
						echo json_encode(array());die; //no hay respuesta servidor 
					}else{
						$response=json_decode($resultado);
						if($response->success==0){
							echo json_encode(array());die; //si success = 0
						} 
					}
					echo json_encode(array('success'=>1));die; //si success = 0

				}else if($action=='anular'){
				  if( isset( $_POST['manual'] ) ){$manual = sanitize_text_field($_POST['manual']);}else{$manual=0;}
				  FTBAI_anular($postid,$manual);
				}
			}
		}
	}
});

function FTBAI_get_version(){
	if( ! function_exists( 'get_plugin_data' ) ) { require_once( ABSPATH . 'wp-admin/includes/plugin.php' ); }
    $plugin_data = get_plugin_data( __FILE__ );
	update_option('FTBAI_version',$plugin_data['Version']);
}

function FTBAI_inserta_factura($order_id,$replace=FALSE){
	$order = new WC_Order($order_id);
	$total = $order->get_total();
	//$nombre = $order->get_shipping_first_name().' '.$order->get_shipping_last_name();
	$nombre = get_post_meta( $order_id, '_billing_first_name', true ).' '.get_post_meta( $order_id, '_billing_last_name', true );
	$cliente_nif = get_post_meta( $order_id, '_vat_number', true ); 
	//graba transaccion
	global $wpdb;
	$user_nif = get_option('FTBAI_DNI');
	$tbaicode = get_option('FTBAI_CLAVE');
	if($user_nif=='' || $tbaicode==''){
		$user_nif=get_option('FTBAI_DNI_TEST');
		$tbaicode=get_option('FTBAI_CLAVE_TEST');
	}	
	if($replace==TRUE){$modo='REPLACE';}else{$modo='INSERT IGNORE';}
	$tbai_num='';
	$tbai_qr='';
	$factura_estado='Pendiente';
	$factura_tipo='';
	$factura_serie='';
	$factura_numero='';
	if($order_id>0){
		$results = FTBAI_getfield_facturas($order_id); 
		if($results!=null){
			$tbai_num=$results->tbai_num;
			$tbai_qr=$results->tbai_qr;
			$factura_estado=$results->factura_estado;
			$factura_tipo=$results->factura_tipo;
			$factura_serie=$results->factura_serie;
			$factura_numero=$results->factura_numero;
		}
	}
	$insertsql = $wpdb->get_results("
		".$modo." INTO `{$wpdb->prefix}ftbai_facturas` 
			(`id_transaccion`, `user_dni`, `user_apikey`, `fecha_transaccion`, `pedido_numero`, `pedido_fecha`, `factura_estado`, `factura_tipo`, `factura_vista`, `factura_serie`, `factura_numero`, `factura_fecha`, `factura_total`, `cliente_nombre`, `cliente_nif`, `tbai_num`, `tbai_qr`)
		VALUES
			('".$order_id."', '".$user_nif."', '".$tbaicode."', NOW(), '".$order_id."', NOW(), '".$factura_estado."', '".$factura_tipo."', '0000-00-00 00:00:00', '".$factura_serie."', '".$factura_numero."', NOW(), '".$total."', '".$nombre."', '".$cliente_nif."', '".$tbai_num."', '".$tbai_qr."')
	" );
}

function FTBAI_validDniCifNie($dni){
  $dni = str_replace(' ', '', $dni);
  $dni = str_replace('-', '', $dni);
  $dni = str_replace('.', '', $dni);
  $cif = strtoupper($dni);
  for ($i = 0; $i < 9; $i ++){
    $num[$i] = substr($cif, $i, 1);
  }
  // Si no tiene un formato valido devuelve error
  if (!preg_match('/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/', $cif)){
    return 0;
  }
  // Comprobacion de NIFs estandar
  if (preg_match('/(^[0-9]{8}[A-Z]{1}$)/', $cif)){
    if ($num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr($cif, 0, 8) % 23, 1)){
      return 1; //NIF ESTANDAR
    }else{
      return 0;
    }
  }
  // Algoritmo para comprobacion de codigos tipo CIF
  $suma = $num[2] + $num[4] + $num[6];
  for ($i = 1; $i < 8; $i += 2){
    $suma += (int)substr((2 * $num[$i]),0,1) + (int)substr((2 * $num[$i]), 1, 1);
  }
  $n = 10 - substr($suma, strlen($suma) - 1, 1);
  // Comprobacion de NIFs especiales (se calculan como CIFs o como NIFs)
  if (preg_match('/^[KLM]{1}/', $cif)){
    if ($num[8] == chr(64 + $n) || $num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr($cif, 1, 8) % 23, 1)){
      return 1; //NIF ESPECIAL
    }else{
      return 0;
    }
  }
  // Comprobacion de CIFs
  if (preg_match('/^[ABCDEFGHJNPQRSUVW]{1}/', $cif)){
    if ($num[8] == chr(64 + $n) || $num[8] == substr($n, strlen($n) - 1, 1)){
      return 2; //CIF EMPRESA
    }else{
      return 0;
    }
  }
  // Comprobacion de NIEs
  // T
  if (preg_match('/^[T]{1}/', $cif)){
    if ($num[8] == preg_match('/^[T]{1}[A-Z0-9]{8}$/', $cif)){
      return 1; //NIE
    }else{
      return 0;
    }
  }
  // XYZ
  if (preg_match('/^[XYZ]{1}/', $cif)){
    if ($num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr(str_replace(array('X','Y','Z'), array('0','1','2'), $cif), 0, 8) % 23, 1)){
      return 1; //NIE XYZ
    }else{
      return 0;
    }
  }
  // Si todavía no se ha verificado devuelve error
  return 0;
}
function FTBAI_isEU($countrycode) {
    $eu_countrycodes = array(
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL',
        'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV',
        'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'
    );
    return (in_array($countrycode, $eu_countrycodes));
}

function FTBAI_modopruebas(){
	global $wpdb;
	$user_nif = get_option('FTBAI_DNI');
	if($user_nif==''){$pruebas=1;}else{$pruebas=0;}
	return $pruebas;
}

//al activar plugin crea tablas y rellena info
register_activation_hook(__FILE__, 'FTBAI_plugin_activation');
function FTBAI_plugin_activation(){
	return FTBAI_checkexists_tabla();
}
function FTBAI_checkexists_tabla(){
	if ( !class_exists( 'WooCommerce' ) ) {return;}
	global $wpdb;
	$table_name = $wpdb->prefix.'ftbai_facturas';
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		//si no existe la tabla la crea
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "	CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ftbai_facturas` (
						`id_transaccion` INT(11) NOT NULL AUTO_INCREMENT,
						`user_dni` VARCHAR(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
						`user_apikey` VARCHAR(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
						`fecha_transaccion` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
						`pedido_numero` INT(11) NOT NULL DEFAULT '0',
						`pedido_fecha` DATETIME NOT NULL,
						`factura_estado` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_general_ci',
						`factura_tipo` VARCHAR(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
						`factura_rectificada_en` INT(11) NOT NULL DEFAULT '0',
						`factura_rectificativa_de` INT(11) NOT NULL DEFAULT '0',
						`factura_vista` DATETIME NOT NULL,
						`factura_serie` VARCHAR(50) NULL DEFAULT '' COLLATE 'utf8_general_ci',
						`factura_numero` VARCHAR(50) NULL DEFAULT '' COLLATE 'utf8_general_ci',
						`factura_fecha` DATETIME NOT NULL,
						`factura_total` DECIMAL(20,4) NOT NULL DEFAULT '0.0000',
						`cliente_nombre` VARCHAR(50) NULL DEFAULT '' COLLATE 'utf8_general_ci',
						`cliente_nif` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_general_ci',
						`tbai_num` VARCHAR(50) NULL DEFAULT '' COLLATE 'utf8_general_ci',
						`tbai_qr` VARCHAR(250) NULL DEFAULT '' COLLATE 'utf8_general_ci',
						PRIMARY KEY (`id_transaccion`, `user_dni`, `user_apikey`) USING BTREE
					)
					".$charset_collate."
					ENGINE=InnoDB
					AUTO_INCREMENT=1; ";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		$is_error = empty( $wpdb->last_error );
		//graba todos los pedidos en tabla facturas cuando es version demo
		if(get_option('FTBAI_DNI')=='' && get_option('FTBAI_CLAVE')==''){
			$customer_orders = get_posts( array(
				'numberposts' => -1,
				'post_type'   => 'shop_order',
				'post_status' => array_keys( wc_get_order_statuses() )
			) );
			foreach ($customer_orders as $order){
				if($order->ID > 0){
					FTBAI_inserta_factura($order->ID,TRUE);
				}
			}
		}
		return $is_error;
	}
	return;
}
function FTBAI_mycode_table_column_exists($table_name, $column_name)
{
    global $wpdb;
	//if ( FTBAI_mycode_table_column_exists( $wpdb->prefix.ftbai_facturas , 'user_dni') ){echo 1;}
    $column = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
        DB_NAME,
        $table_name,
        $column_name
    ));
    if (!empty($column)) {
        return true;
    }
    return false;
}
function FTBAI_getfield_facturas($id_transaccion){
	global $wpdb;
	$user_nif = get_option('FTBAI_DNI');
	$tbaicode = get_option('FTBAI_CLAVE');
	if($user_nif=='' || $tbaicode==''){
		$user_nif=get_option('FTBAI_DNI_TEST');
		$tbaicode=get_option('FTBAI_CLAVE_TEST');	
	}
	$resultado = $wpdb->get_results(" 	SELECT * FROM `{$wpdb->prefix}ftbai_facturas` 
										WHERE id_transaccion=".$id_transaccion."
										AND user_dni='".$user_nif."' AND user_apikey='".$tbaicode."'
										limit 1;");
	//return $resultado[0];
	return isset($resultado[0]) ? $resultado[0] : null;
}
function FTBAI_update($id_transaccion,$campo,$valor){
	global $wpdb;
	$user_nif = get_option('FTBAI_DNI');
	$tbaicode = get_option('FTBAI_CLAVE');
	if($user_nif=='' || $tbaicode==''){
		$user_nif=get_option('FTBAI_DNI_TEST');
		$tbaicode=get_option('FTBAI_CLAVE_TEST');
	}
	$insertsql = $wpdb->get_results(" 	UPDATE `{$wpdb->prefix}ftbai_facturas`
										SET `".$campo."` = '".$valor."'
										WHERE id_transaccion=".$id_transaccion."
										AND user_dni='".$user_nif."' AND user_apikey='".$tbaicode."'; ");
}

//FTBAI_delete(1111);
//function FTBAI_delete($id_transaccion){
//	global $wpdb;
//	$user_nif = get_option('FTBAI_DNI');
//	$tbaicode = get_option('FTBAI_CLAVE');
//	if($user_nif=='1111'){
//		$delsql = $wpdb->get_results(" 	DELETE FROM `{$wpdb->prefix}ftbai_facturas`
//										WHERE `id_transaccion` = ".$id_transaccion."
//										AND user_dni='".$user_nif."' AND user_apikey='".$tbaicode."'; ");
//	}
//}
//FTBAI_setpedidonum();
//function FTBAI_setpedidonum(){
//	global $wpdb;
//	$tbaicode = get_option('FTBAI_CLAVE');    
//    if($tbaicode=='abcde'){
//        $autoincrement_next_num = $wpdb->get_results(" ALTER TABLE {$wpdb->prefix}posts AUTO_INCREMENT = 160000; ");
//        echo "posts establecido 160000";
//    }
//}

//add_filter('plugins_api', 'FTBAI_plugin_name_check_for_updates', 20, 3);
function FTBAI_plugin_name_check_for_updates($false, $action, $args) {
	$plugin_slug = 'wp-ticketbai';
    if ($action === 'plugin_information' && isset($args->slug) && $args->slug === $plugin_slug) {
        $plugin_info = get_plugin_data(__FILE__);
        $current_version = $plugin_info['Version'];
        $api_url = 'https://api.wordpress.org/plugins/info/1.0/' . $plugin_slug . '.json';
        $response = wp_remote_get($api_url);
        if (!is_wp_error($response)) {
            $response_body = json_decode(wp_remote_retrieve_body($response));
            if ($response_body && version_compare($current_version, $response_body->version, '<')) {
                $update_info = array('slug' => $plugin_slug,'new_version' => $response_body->version,'url' => $response_body->homepage,'package' => $response_body->download_link,);
                //return (object) $update_info;
				FTBAI_upgrade_plugin($plugin_slug);
            }
        }
    }
    return false;
}
function FTBAI_upgrade_plugin($plugin_slug){
	require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	//include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
    //include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	wp_cache_flush();
    //$upgrader = new Plugin_Upgrader();
    //$upgraded = $upgrader->upgrade( $plugin_slug );
    //return $upgraded;
	$upgrader = new Plugin_Upgrader();
	$result = $upgrader->bulk_upgrade(array($plugin_slug));
	if (is_array($result) && isset($result[$plugin_slug])) {
		$plugin_info = $result[$plugin_slug]; // Información del plugin actualizado
		if ($plugin_info instanceof WP_Error) {
			$error_message = $plugin_info->get_error_message();
		} else {
			activate_plugin($plugin_info['plugin']); // Activa el plugin actualizado
		}
	}
}
?>