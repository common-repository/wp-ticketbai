<?php
if (function_exists('is_plugin_active')){
	if(is_plugin_active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php') || is_plugin_active('wpo_wcpdf')){
		add_filter( 'wpo_wcpdf_external_invoice_number_enabled', '__return_true' );
		add_filter( 'wpo_wcpdf_external_invoice_number', 'FTBAI_wpo_wcpdf_ticketbai_invoice_number', 10, 2 );
		function FTBAI_wpo_wcpdf_ticketbai_invoice_number( $number, $document ) {
			if($document->type=='invoice' && $document->order_id>0){
				$results = FTBAI_getfield_facturas($document->order_id); 
				if($results->factura_numero!=''){
					$numfac = $results->factura_serie.'/'.$results->factura_numero;
					return $numfac;
				}else{
					return '';
				}
			}else{
				return '';
			}
			return '';
			//return json_encode($document);
			//return $number;
		}

		function FTBAI_custom_wpo_wcpdf_billing_custom_field( $custom_field, $document ){ 
			$results = FTBAI_getfield_facturas($document->order_id); 
			$tbai_num = $results->tbai_num;
			if($tbai_num!='' && $document->get_type() === 'invoice'){
				$array = explode("<br/>",$custom_field);
				$txt = $results->cliente_nombre.'<br/>';
				if($results->cliente_nif!=''){
					$txt.=$results->cliente_nif.'<br/>';
					for($i = 1; $i < count($array); $i++) {
						$txt = $txt.$array[$i].'<br/>';
					}
					$order = $document->order;
					if ($order) {
						$txt.=$order->get_billing_address_1().' '.$order->get_billing_address_2();
						if($order->get_billing_postcode()!=''){$txt.='<br>'.$order->get_billing_postcode();}
						if($order->get_billing_city()!=''){$txt.='<br>'.$order->get_billing_city();}
					}
					return $txt;
				}else{
					return $custom_field;
				}
			}else{
				return $custom_field;
			}
		}
		add_filter('wpo_wcpdf_billing_address', 'FTBAI_custom_wpo_wcpdf_billing_custom_field', 10, 2);
		
		function FTBAI_custom_invoice_title( $title, $document ) {
			if ( $document->get_type() === 'invoice' ) {
				$order = $document->order;
				if ($order) {
					$order_id = $order->get_id();
					$results = FTBAI_getfield_facturas($order_id); 
					if($results->tbai_num!='' && $results->cliente_nif==''){
						return 'FACTURA SIMPLIFICADA';
					}
				}
			}
			return $title;
		}
		add_filter('wpo_wcpdf_invoice_title','FTBAI_custom_invoice_title', 10, 2 );
		
		add_filter( 'wpo_wcpdf_get_html', 'FTBAI_custom_pdf_html', 10, 2 );
		function FTBAI_custom_pdf_html($html, $document) {
			// Verificar si el documento es una factura
			if ( $document->get_type() === 'invoice' ) {
				$order = $document->order;
				if ($order) {
					$order_id = $order->get_id();
					$results = FTBAI_getfield_facturas($order_id); 
					if($results->tbai_num!=''){
						$numfac = $results->factura_serie.'/'.$results->factura_numero;
						$html = preg_replace('/<tr class="invoice-number">.*?<\/tr>/s', '<tr class="invoice-number"><th>' . __( 'Número de Factura:', 'woocommerce-pdf-invoices-packing-slips' ) . '</th><td>'.$numfac.'</td></tr>', $html);
						$fecha = date("d-m-Y", strtotime($results->factura_fecha) );
						$html = preg_replace('/<tr class="invoice-date">.*?<\/tr>/s', '<tr class="invoice-date"><th>' . __( 'Fecha de factura:', 'woocommerce-pdf-invoices-packing-slips' ) . '</th><td>'.$fecha.'</td></tr>', $html);
                        //codigo qr
//                        $tbai_num = $results->tbai_num;
//                        $tbai_qr = $results->tbai_qr;
//                        $qr_html = '<div style="position:fixed;bottom:10px;left:10px;text-align:left;">
//                                        <img alt="QR Code" src="https://api.wptbai.com/phpqrcode/showqr.php?codeqr=' . base64_encode($tbai_qr) . '"/>
//                                        <p style="margin: 5px 0 0 0;">'.htmlspecialchars($tbai_num).'</p>
//                                    </div>';
//                        $html = str_replace('</body>', $qr_html . '</body>', $html);
					}
				}
			}
			return $html;
		}
		
        if(get_option('FTBAI_posicionQR')==1){
            add_action('wpo_wcpdf_after_order_details', 'FTBAI_wpo_wcpdf_ticketbai_details', 10, 2);
        }else{
            add_action('wpo_wcpdf_after_footer', 'FTBAI_custom_after_footer', 10, 2);    
        }
        function FTBAI_custom_after_footer( $document_type, $document ) {
		if ( $document_type === 'invoice' ) {
				$order_id=$document->get_id();
				if ($order_id>0) {
					$results = FTBAI_getfield_facturas($order_id); 
					if ($results->tbai_num != '') {
						$tbai_num = $results->tbai_num;
						$tbai_qr = $results->tbai_qr;
						$qr_url = 'https://api.wptbai.com/phpqrcode/showqr.php?codeqr=' . base64_encode($tbai_qr);
						echo '
								<div style="float:left;width:25%;display:block;position:relative;bottom:170px;margin-left:-20px;">
									<div style="text-align:center;position:relative;z-index:1000;background-color:white;">
										<img alt="QR Code" src="'.$qr_url.'" style="width: 150px; height: 150px;" />
										<p style="margin: -5px 0 0 0;">' . htmlspecialchars($tbai_num) . '</p>
									</div>
								</div>';
					}
				}
			}
		}
		function FTBAI_wpo_wcpdf_ticketbai_details( $document_type, $order ){
			$order_id = $order->get_id(); 
			$results = FTBAI_getfield_facturas($order_id); 
			$tbai_num = $results->tbai_num;
			$tbai_qr = $results->tbai_qr;
			if($tbai_num!='' && $document_type!='packing-slip'){
				//echo '<div style="display:block;width:300px;">'.$tbai_num.'<img src="https://api.wptbai.com/phpqrcode/showqr.php?codeqr='.base64_encode($tbai_qr).'"/></div>';
				echo '<table width="100%" border="0" style="page-break-inside:avoid;"><tbody>
						<tr><td>'.$tbai_num.'</td></tr>
						<tr><td><img src="https://api.wptbai.com/phpqrcode/showqr.php?codeqr='.base64_encode($tbai_qr).'"/></td></tr>
					  </tbody></table>';
			}
		}		
		
		function FTBAI_shapeSpace_disable_scripts_styles_admin_area() {
			if(is_admin()){
				$orderid = get_the_ID();
				if($orderid>0){
					$results = FTBAI_getfield_facturas($orderid);
					$tbai_num = $results->tbai_num;
					if($tbai_num!=''){
						echo '<style> .form-field._wcpdf_invoice_number_field {display: none;} .form-field.form-field-wide {display: none;} </style>';
					}
					//$factura_estado = $results->factura_estado;
					//if($factura_estado!='Emitida'){echo '<style> #wpo_wcpdf-box {display: none;} </style>';}
					//echo '<style> #wpo_wcpdf-data-input-box {display: none;} </style>';
					//echo '<style> .column-wc_actions {display: none;} </style>';
				}
			} 
		}
		add_action('admin_enqueue_scripts', 'FTBAI_shapeSpace_disable_scripts_styles_admin_area', 100);

		add_filter( 'wpo_wcpdf_document_is_allowed', 'FTBAI_wpo_wcpdf_invoice_attachment_condition', 10, 2 );
		function FTBAI_wpo_wcpdf_invoice_attachment_condition( $allowed, $document ) {
		   //if ( $order = $document->order ) {
		   if ($document->order_id>0) {
			   if ($document->type == 'invoice') {
					$results = FTBAI_getfield_facturas($document->order_id); 
					if($results!=null && $results->id_transaccion>0){
						$tbai_num = $results->tbai_num;
						if($tbai_num==''){$allowed=false;}else{$allowed=true;}
						//$allowed = empty( $order->get_meta( 'ticketbai_number' ) ) ? false : true;
						//$allowed=false;
					}
				}
			}
			return $allowed;
		} 
	}
}
?>