<?php
function FTBAI_dias(){
	//valida datos
	$user_nif = get_option('FTBAI_DNI');
	$tbaicode = get_option('FTBAI_CLAVE');
	if($user_nif=='' || $tbaicode==''){
		$user_nif=get_option('FTBAI_DNI_TEST');
		$tbaicode=get_option('FTBAI_CLAVE_TEST');	
	}
	$main = json_encode( array( "titular"=> array( "nif"=>$user_nif, "codigo"=>$tbaicode ) ) );
	$result = FTBAI_curl('dias',$main);
	return json_decode($result['body']);
}
function FTBAI_validardatos(){
	FTBAI_checkexists_tabla();
	//valida datos
	$user_nif = get_option('FTBAI_DNI');
	$tbaicode = get_option('FTBAI_CLAVE');
	if($user_nif=='' || $tbaicode==''){
		$user_nif=get_option('FTBAI_DNI_TEST');
		$tbaicode=get_option('FTBAI_CLAVE_TEST');	
	}
	$main = json_encode( array( "titular"=> array( "nif"=>$user_nif, "codigo"=>$tbaicode ) ) );
	$result = FTBAI_curl('verificacion_presencial',$main);
	$resultado = $result['body'];
	if ($resultado!=''){
		//graba todos los pedidos del user_nif y tbaicode en tabla facturas al validar
		if(get_option('FTBAI_DNI')=='' && get_option('FTBAI_CLAVE')==''){
			$customer_orders = get_posts( array(
				'numberposts' => -1,
				'post_type'   => 'shop_order',
				'post_status' => array_keys( wc_get_order_statuses() )
			) );
			foreach ($customer_orders as $order){
				if($order->ID > 0){
					FTBAI_inserta_factura($order->ID,FALSE);
				}
			}	
		}
		//muestra datos presenciales
		$resultado=json_decode($resultado);
		$titular=$resultado->titular;
		$nombre=$titular->nombre;
		$nif=$titular->nif;
		$software=$resultado->software;
		$nombre_desarrollador=$software->nombre_desarrollador;
		$nif_desarrollador=$software->nif_desarrollador;
		$nombre_software=$software->nombre_software;
		$version_software=$software->version_software;
		update_option('FTBAI_NOMBRE',$nombre);
		$text='<b><u>INFORMACIÓN</u></b><br>Nombre: <b>'.$nombre.'</b><br>Nif: <b>'.$nif.'</b><br>Nombre Desarrollador: <b>'.$nombre_desarrollador.'</b><br>Nif Desarrollador: <b>'.$nif_desarrollador.'</b><br>Nombre del Software: <b>'.$nombre_software.'</b><br>Version del Software: <b>'.$version_software.'</b>';
		//detalles de la cuenta
		$resulta_detalles = FTBAI_curl('detallescuenta',$main);
		$datosusuario = $resulta_detalles['body'];
		$text.=$datosusuario;
		//$text.=print_r($resulta_detalles['result'], true);
		$resultcampo = $resulta_detalles['result'];
		if ($resultcampo!=''){
			$resultcampo=json_decode($resultcampo);
			update_option('FTBAI_EMAIL',$resultcampo->email);
		}
		return $text;
	}else{
		update_option('FTBAI_DNI','');
		update_option('FTBAI_CLAVE','');
		update_option('FTBAI_NOMBRE','');
		update_option('FTBAI_EMAIL','');
		echo("<div class='error message' style='padding:10px;margin:16px 4px;margin-right: 19px;'>Error identificación no válida. ".esc_textarea(sanitize_text_field($resultado))."</div>");
	}
}
function FTBAI_informesiva($FTBAI_informe_ejercicio,$FTBAI_informe_trimestre){
	//valida datos
	$user_nif = get_option('FTBAI_DNI');
	$tbaicode = get_option('FTBAI_CLAVE');
	if($user_nif=='' || $tbaicode==''){
		$user_nif=get_option('FTBAI_DNI_TEST');
		$tbaicode=get_option('FTBAI_CLAVE_TEST');	
	}
	
	if ($FTBAI_informe_trimestre==0){
		$fechaini='01-01-'.$FTBAI_informe_ejercicio;
		$fechafin='31-12-'.$FTBAI_informe_ejercicio;
	}else if ($FTBAI_informe_trimestre==1){
		$fechaini='01-01-'.$FTBAI_informe_ejercicio;
		$fechafin='31-03-'.$FTBAI_informe_ejercicio;
	}else if ($FTBAI_informe_trimestre==2){
		$fechaini='01-04-'.$FTBAI_informe_ejercicio;
		$fechafin='30-06-'.$FTBAI_informe_ejercicio;
	}else if ($FTBAI_informe_trimestre==3){
		$fechaini='01-07-'.$FTBAI_informe_ejercicio;
		$fechafin='30-09-'.$FTBAI_informe_ejercicio;
	}else if ($FTBAI_informe_trimestre==4){
		$fechaini='01-10-'.$FTBAI_informe_ejercicio;
		$fechafin='31-12-'.$FTBAI_informe_ejercicio;
	}else{
		if ($FTBAI_informe_trimestre==101){ $fechaini='01-01-'.$FTBAI_informe_ejercicio; $fechafin='31-01-'.$FTBAI_informe_ejercicio;}
		if ($FTBAI_informe_trimestre==102){ $fechaini='01-02-'.$FTBAI_informe_ejercicio; $fechafin=cal_days_in_month(CAL_GREGORIAN, 2, $FTBAI_informe_ejercicio).'-02-'.$FTBAI_informe_ejercicio;}
		if ($FTBAI_informe_trimestre==103){ $fechaini='01-03-'.$FTBAI_informe_ejercicio; $fechafin='31-03-'.$FTBAI_informe_ejercicio;}
		if ($FTBAI_informe_trimestre==104){ $fechaini='01-04-'.$FTBAI_informe_ejercicio; $fechafin='30-04-'.$FTBAI_informe_ejercicio;}
		if ($FTBAI_informe_trimestre==105){ $fechaini='01-05-'.$FTBAI_informe_ejercicio; $fechafin='31-05-'.$FTBAI_informe_ejercicio;}
		if ($FTBAI_informe_trimestre==106){ $fechaini='01-06-'.$FTBAI_informe_ejercicio; $fechafin='30-06-'.$FTBAI_informe_ejercicio;}
		if ($FTBAI_informe_trimestre==107){ $fechaini='01-07-'.$FTBAI_informe_ejercicio; $fechafin='31-07-'.$FTBAI_informe_ejercicio;}
		if ($FTBAI_informe_trimestre==108){ $fechaini='01-08-'.$FTBAI_informe_ejercicio; $fechafin='31-08-'.$FTBAI_informe_ejercicio;}
		if ($FTBAI_informe_trimestre==109){ $fechaini='01-09-'.$FTBAI_informe_ejercicio; $fechafin='30-09-'.$FTBAI_informe_ejercicio;}
		if ($FTBAI_informe_trimestre==110){ $fechaini='01-10-'.$FTBAI_informe_ejercicio; $fechafin='31-10-'.$FTBAI_informe_ejercicio;}
		if ($FTBAI_informe_trimestre==111){ $fechaini='01-11-'.$FTBAI_informe_ejercicio; $fechafin='30-11-'.$FTBAI_informe_ejercicio;}
		if ($FTBAI_informe_trimestre==112){ $fechaini='01-12-'.$FTBAI_informe_ejercicio; $fechafin='31-12-'.$FTBAI_informe_ejercicio;}		
	}
	
	$main = json_encode( 	array( 	"titular"=> array( "nif"=>$user_nif, "codigo"=>$tbaicode ),
								 	"facturas"=> array( "fecha_inicio"=>$fechaini, "fecha_fin"=>$fechafin ) 
								 ) 
					   );
	//$result = FTBAI_curl('informesiva',$main);
	$result = FTBAI_curl('informesfacturas',$main);
	$resultado = $result['body'];
	
	//graba fichero
	$upload_dir = wp_upload_dir();
	$basedir = $upload_dir['basedir'];
	$baseurl = $upload_dir['baseurl'];
	if (is_ssl() && strpos($baseurl, 'http://') === 0) {$baseurl = str_replace('http://', 'https://', $baseurl);}
	
	if (!file_exists($basedir.'/wp-factubai')) {mkdir($basedir.'/wp-factubai', 0777, true);}
	$filename=$basedir."/wp-factubai/informe.csv";
	file_put_contents($filename,$resultado);
	
	//enlace descarga
	$txt='<h2><a href="'.$baseurl."/wp-factubai/informe.csv?".time().'" target="_blank">'.__( 'CLICK HERE TO DOWNLOAD CSV FILE', 'wp-ticketbai' ).'</a></h2><br>';
	
	//genera tabla facturas
    $txt.= '<table border="1">';
	$f = fopen($filename, "r");
	$inicio=0;
    while (($data = fgetcsv($f, 1000, ";")) !== FALSE) {
		//cabecera
		if($inicio==0){
			$row = strtoupper($data[0]);  
			if (strpos($row, 'INFORME') !== false) {$txt.= '<h3><b><u>'.$row.'</u></b></h3>';continue;}
			if (strpos($row, 'NOMBRE') !== false) {$txt.= $row.'<b>'.$data[1].'</b><br>';continue;}
			if (strpos($row, 'NIF') !== false) {$txt.= $row.'<b>'.$data[1].'</b><br>';continue;}
			if (strpos($row, 'INICIO') !== false) {$txt.= $row.'<b>'.$data[1].'</b><br>';continue;}
			if (strpos($row, 'FIN') !== false) {$txt.= $row.'<b>'.$data[1].'</b><br>';continue;}
			if (strpos($row, 'SERIE') !== false){
				$inicio=1;
			}else{
				continue;
			}
		}
        $num = count($data);
        if ($row == 1) {
            $txt.= '<thead><tr>';
        }else{
            $txt.= '<tr>';
        }
        for ($c=0; $c < $num; $c++) {
            //$txt.= $data[$c] . "<br />\n";
            if(empty($data[$c])) {
               $value = "&nbsp;";
            }else{
               $value = $data[$c];
            }
            if ($row == 1) {
                $txt.= '<th>'.$value.'</th>';
            }else{
                $txt.= '<td>'.$value.'</td>';
            }
        }
        if ($row == 1) {
            $txt.= '</tr></thead><tbody>';
        }else{
            $txt.= '</tr>';
        }
        $row++;
    }
    $txt.= '</tbody></table>';
    fclose($f);
	
	
	
	
	
	
	
	
	//$result = FTBAI_curl('informesiva',$main);
	$result = FTBAI_curl('informesiva',$main);
	$resultado = $result['body'];
	
	//graba fichero
	$upload_dir = wp_upload_dir();
	$basedir = $upload_dir['basedir'];
	if (!file_exists($basedir.'/wp-factubai')) {mkdir($basedir.'/wp-factubai', 0777, true);}
	$filename=$basedir."/wp-factubai/informeiva.csv";
	file_put_contents($filename,$resultado);
	
	//genera tabla resumen iva
    $txt.= '<table border="1">';
	$f = fopen($filename, "r");
	$inicio=0;
    while (($data = fgetcsv($f, 1000, ";")) !== FALSE) {
		//cabecera
		if($inicio==0){
			$row = strtoupper($data[0]);  
			if (strpos($row, 'RESUMEN') !== false) {$txt.= '<h3><b><u>'.$row.'</u></b></h3>';continue;}
			if (strpos($row, 'NOMBRE') !== false) {continue;}
			if (strpos($row, 'NIF') !== false) {continue;}
			if (strpos($row, 'INICIO') !== false) {continue;}
			if (strpos($row, 'FIN') !== false) {continue;}
			if (strpos($row, 'FACTURAS') !== false) {$txt.= $row.'<b>'.$data[1].'</b><br>';continue;}
			if (strpos($row, 'BASE') !== false){
				$inicio=1;
			}else{
				continue;
			}
		}
        $num = count($data);
        if ($row == 1) {
            $txt.= '<thead><tr>';
        }else{
            $txt.= '<tr>';
        }
        for ($c=0; $c < $num; $c++) {
            //$txt.= $data[$c] . "<br />\n";
            if(empty($data[$c])) {
               $value = "&nbsp;";
            }else{
               $value = $data[$c];
            }
            if ($row == 1) {
                $txt.= '<th>'.$value.'</th>';
            }else{
                $txt.= '<td>'.$value.'</td>';
            }
        }
        if ($row == 1) {
            $txt.= '</tr></thead><tbody>';
        }else{
            $txt.= '</tr>';
        }
        $row++;
    }
    $txt.= '</tbody></table>';
    fclose($f);
	
	
	
	
	return $txt;
} 

function FTBAI_verpdf($order_id){
	$user_nif = get_option('FTBAI_DNI');
	$tbaicode = get_option('FTBAI_CLAVE');
	if($user_nif=='' || $tbaicode==''){
		$user_nif=get_option('FTBAI_DNI_TEST');
		$tbaicode=get_option('FTBAI_CLAVE_TEST');		
	}
	$results = FTBAI_getfield_facturas($order_id);
	$id_transaccion = $results->id_transaccion;
	$serie = $results->factura_serie;
	$numero = $results->factura_numero;
	$fecha = date("d-m-Y", strtotime($results->factura_fecha) );	
	$main = json_encode( array( 	"titular"=> array( 	"id_transaccion"	=> $id_transaccion,
														"nif"				=> $user_nif, 
														"codigo"			=> $tbaicode ),
									"factura"=> array( 	"serie"				=> $serie, 
														"numero"			=> $numero,
														"fecha"				=> $fecha ),
								 ) 
						  );
	$result = FTBAI_curl('pdf',$main);
	$upload_dir = wp_upload_dir();
	$basedir = $upload_dir['basedir'];
	$baseurl = $upload_dir['baseurl'];
	if (is_ssl() && strpos($baseurl, 'http://') === 0) {$baseurl = str_replace('http://', 'https://', $baseurl);}
	if (!file_exists($basedir.'/wp-factubai')) {mkdir($basedir.'/wp-factubai', 0777, true);}
	$timestamp=time();
	$filename=$basedir."/wp-factubai/factura_".$serie.$numero."_".$timestamp.".pdf";
	file_put_contents($filename,$result);
	return "/wp-factubai/factura_".$serie.$numero."_".$timestamp.".pdf";
}

function FTBAI_verxml($order_id){
	$results = FTBAI_getfield_facturas($order_id);
	$result = FTBAI_curl('xml', $results->tbai_num);
	$namefile = $results->factura_serie.'-'.$results->factura_numero;
	$xml = base64_decode( $result['result'] );
	$upload_dir = wp_upload_dir();
	$basedir = $upload_dir['basedir'];
	$baseurl = $upload_dir['baseurl'];
	if (is_ssl() && strpos($baseurl, 'http://') === 0) {$baseurl = str_replace('http://', 'https://', $baseurl);}
	if (!file_exists($basedir.'/wp-factubai')) {mkdir($basedir.'/wp-factubai', 0777, true);}
	$filename=$basedir."/wp-factubai/".$namefile.".xml";
	if (!file_exists($filename)) { 
		file_put_contents($filename,$xml); //if (!file_exists($filename)) {   
	}
	return "/wp-factubai/".$namefile.".xml";
}

function FTBAI_creafacmanual($postid,$facmanualid){
	$user_nif = get_option('FTBAI_DNI');
	$tbaicode = get_option('FTBAI_CLAVE');
	if($user_nif=='' || $tbaicode==''){
		$user_nif=get_option('FTBAI_DNI_TEST');
		$tbaicode=get_option('FTBAI_CLAVE_TEST');
	}
	//$results = FTBAI_getfield_facturas($postid); 
	
	//carga order
	$nif=trim(get_post_meta( $postid, '_vat_number', true ));
	$order = new WC_Order($postid);
	$nombre=$order->get_billing_first_name().' '.$order->get_billing_last_name();
	$nombre_empresa=trim($order->get_billing_company());
	$domicilio=$order->get_billing_address_1().' '.$order->get_billing_address_2();
	$codigo_postal=$order->get_billing_postcode();
	$localidad=$order->get_billing_city();
	if($nombre_empresa!='' && $nif!=''){$nombre=$nombre_empresa;}
	
	//ciudad
	$country_code = $order->get_billing_country();
	$state_code   = $order->get_billing_state();
	$countries = new WC_Countries(); // Get an instance of the WC_Countries Object
	$country_states = $countries->get_states($country_code); // Get the states array from a country code
	//$provincia     = $country_states[$state_code]; // get the state name
	$provincia = isset($country_states[$state_code]) ? $country_states[$state_code] : '';
	if (empty($provincia)) {
		$all_countries = $countries->get_countries();
		$provincia = isset($all_countries[$country_code]) ? $all_countries[$country_code] : $country_code;
	}
	
	//carga lineas detalles
	$arrlineas = base64_decode(sanitize_text_field($_POST['arrlineas']));
	$decolineas = json_decode($arrlineas,true);
	$detalles=array();
	foreach ($decolineas as $key => $value) {
		//añade itemlinea
		$precio = $value["precio"];
		if ($precio>0){
			$precio_unidad=$precio;
			$cantidad=1;
		}else{
			$precio_unidad=$precio*-1;
			$cantidad=-1;
		}
		array_push($detalles,array( 	"nombre"				=>	$value["producto"], 
										"precio_unidad"			=>	$precio_unidad, //$item['subtotal']/$item['quantity'],
										"cantidad"				=>	$cantidad,
										"descuento_porcentual"	=>	0,
										"iva"					=>	$value["iva"],
										"re"					=>	0)
		);		
	}

	if($nif==''){
		//SIMPLIFICADA
		$main = json_encode( array( 	"titular"		=> array( 	"id_transaccion"	=>  $facmanualid,
																	"nif"				=>	$user_nif,
																	"codigo"			=>	$tbaicode
																),
										"factura"		=> array( 	"tipo"				=>	"Simplificada",
																 	"detalles_con_iva"	=> 	0,
																	"retorno_xml"		=>  1
																),
										"detalles"		=> $detalles
									 )
							  );	
	}else{
		//FACTURA COMPLETA
		if($country_code=='ES'){
			$destinatario = array( 	"nombre"					=>	$nombre,
									"nif"						=>  $nif,
									"domicilio"					=>	$domicilio,
									"codigo_postal"				=>	$codigo_postal,
									"localidad"					=>  $localidad,
									"provincia"					=>	$provincia,
								);
		}else{
			$destinatario = array( 	"nombre"					=>	$nombre,
									"documento_extranjero"		=>  $nif,
								  	"tipo_documento_extranjero"	=>  "06",
									"domicilio"					=>	$domicilio,
									"codigo_postal"				=>	$codigo_postal,
									"localidad"					=>  $localidad,
									"provincia"					=>	$provincia,
									"codigo_pais_extranjero"	=>  $country_code
								);
		}
		$main = json_encode( array( 	"titular"		=> array( 	"id_transaccion"	=>  $facmanualid,
																	"nif"				=>	$user_nif, 
																	"codigo"			=>	$tbaicode
																),
										"destinatario"	=> $destinatario,
										"factura"		=> array( 	"tipo"				=>	"Completa",
																 	"detalles_con_iva"	=> 	0,
																	"retorno_xml"		=>  1
																),
										"detalles"		=> $detalles
									 )
							  );	
	}

	$result = FTBAI_curl('alta',$main);
	//$result = array($result,'header'=>$header,'body'=>$body);
	
	//graba factura
	$body = json_decode($result['body'],true);
	$destinatario = $body['destinatario'];
	if($destinatario['nombre']){$nombre=$destinatario['nombre'];}
	if($destinatario['nif']){$nif=$destinatario['nif'];}
	$factura = $body['factura'];
	$estado=$factura['estado'];
	$serie=$factura['serie'];
	$numero=$factura['numero'];
	$tipo=$factura['tipo'];
	$fecha=$factura['fecha'];
	$hora=$factura['hora'];
	$fechasql=date("Y-m-d", strtotime($fecha) );
	$fechahora=date("Y-m-d H:i:s", strtotime($fecha.' '.$hora));
	$tbai=$factura['tbai'];
	$qr=$factura['qr'];
	$descripcion=$factura['descripcion'];
	$total_final=$factura['total_final'];
	//$xml=$body['xml'];

	//if ($estado=='Grabada' || $estado=='Rectificada' || $estado=='Anulada'){
	if ($estado!=''){
		$status='Emitida';
		if($estado=='Anulada'){$status='Anulada';}
		//graba transaccion
		global $wpdb;
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
				('".$facmanualid."', '".$user_nif."', '".$tbaicode."', '".$fechahora."', '".$facmanualid."', '".$fechahora."', '".$status."', '".$tipo."', '".$postid."', '0000-00-00 00:00:00', '".$serie."', '".$numero."', '".$fechahora."', '".$total_final."', '".$nombre."', '".$nif."', '".$tbai."', '".$qr."')
		" );		
		$response = array('success'=>1, 'status'=>'ejecutado');
		if(get_option('FTBAI_sendfactautomatica')==1){
			$enviado = FONE_enviaremail($facmanualid);
		}
	}else{
		$header = $result['header'];
		$headers_arr = explode("\r\n", $header); // The separator used in the Response Header is CRLF (Aka. \r\n)
		$resultado = str_replace("HTTP/1.1 400","",$headers_arr[0]);
		$response = array('success'=>0, 'resultado'=>trim($resultado));
		
		//devuelve error pedido no procesado
		$errornoprocesado = print_r($main, true); //$var = print_r($result, true); 
		$headers = array('Content-Type: text/html; charset=UTF-8');
		wp_mail( get_option('admin_email'), 'ERROR: Pedido no procesado', 'El pedido no ha podido ser procesado por el siguiente motivo:<br>'.$resultado.'<br><br>'.$errornoprocesado, $headers );
	}
	return json_encode($response);
}

function FTBAI_emitirfactura($postid){
	$user_nif = get_option('FTBAI_DNI');
	$tbaicode = get_option('FTBAI_CLAVE');
	if($user_nif=='' || $tbaicode==''){
		$user_nif=get_option('FTBAI_DNI_TEST');
		$tbaicode=get_option('FTBAI_CLAVE_TEST');
	}
	//comprueba si ha sido emitida previamente
	$results = FTBAI_getfield_facturas($postid);
	if($results!=null && $results->tbai_num!=''){
		$response = array('success'=>0, 'resultado'=>trim('No puede emitir esta factura, porque ya ha sido emitida previamente'));
		return json_encode($response); 
	}
	
	//carga order
	$nif=trim(get_post_meta( $postid, '_vat_number', true ));
	$order = new WC_Order($postid);
	$nombre=$order->get_billing_first_name().' '.$order->get_billing_last_name();
	$nombre_empresa=trim($order->get_billing_company());
	$domicilio=$order->get_billing_address_1().' '.$order->get_billing_address_2();
	$codigo_postal=$order->get_billing_postcode();
	$localidad=$order->get_billing_city();
	
	//ciudad
	$country_code = $order->get_billing_country();
	$state_code   = $order->get_billing_state();
	$countries = new WC_Countries(); // Get an instance of the WC_Countries Object
	$country_states = $countries->get_states($country_code); // Get the states array from a country code
	//$provincia     = $country_states[$state_code]; // get the state name
	$provincia = isset($country_states[$state_code]) ? $country_states[$state_code] : '';
	if (empty($provincia)) {
		$all_countries = $countries->get_countries();
		$provincia = isset($all_countries[$country_code]) ? $all_countries[$country_code] : $country_code;
	}

	//fuera españa y ce
	$FTBAI_operacionextranjero=get_option('FTBAI_operacionextranjero');
	$exentaiva='';
	if($country_code){
		if($country_code=='ES'){
			$FTBAI_canariasnoexentoiva=get_option('FTBAI_canariasnoexentoiva');
			if($FTBAI_canariasnoexentoiva==0){
				if($provincia=='Ceuta' || $provincia=='Melilla' || $provincia=='Santa Cruz de Tenerife' || $provincia=='Las Palmas'){
					//si es servicio para canarias-melilla no disponible actualmente
					$exentaiva='E2'; //Exportación a extranjero fuera de la UE, ventas a Canarias, Ceuta y Melilla (artículo 21 de la Ley de IVA)
					if($FTBAI_operacionextranjero==1){
						$response = array('success'=>0, 'resultado'=> $provincia .'... '. __( 'not available for sale', 'wp-ticketbai' ) );
						return json_encode($response); 
					}
				}
			}
		}else if (FTBAI_isEU($country_code)==TRUE){
			//si es servicio para UE no disponible actualmente
//			if(get_option('FTBAI_empresaroi')==1){
//				$exentaiva='E5'; //Oper.intracomunitarias entre negocios dados alta para tales operaciones (artículo 25 de la Ley de IVA)
//			}else{
//				$exentaiva=''; //al no estar en el ROI, es con IVA
//			}
			//if($country_code!='ES'){$nif='';} //en el caso de empresa UE fuera ESPAÑA hace simplificada
			if($FTBAI_operacionextranjero==1){
				$response = array('success'=>0, 'resultado'=> $provincia .'... '. __( 'not available for sale', 'wp-ticketbai' ) );
				return json_encode($response); 
			}
		}else if (FTBAI_isEU($country_code)==FALSE && get_option('FTBAI_permitefueraUE')==0){
			//fuera de UE - USA etc
			$response = array('success'=>0, 'resultado'=> __( 'Country not available for sale', 'wp-ticketbai' ) );
			return json_encode($response);
		}
	}
	
	//si llega con nombre_empresa asume nif empresa
	if($nombre_empresa!='' && $nif!=''){
		$nombre = $nombre_empresa;
	}

	//recargo_equivalencia
	//$requ=0;
	//if($exentaiva==''){
	//	$requ=trim(get_post_meta($postid, '_billing_recargo_de_equivalencia', true));
	//}	
	$FTBAI_clientesRE=get_option('FTBAI_clientesRE');
	
	// Initializing variables tipo iva
	$tax_rate_items = array(); // The tax labels by $rate Ids
	foreach ( $order->get_items('tax') as $tax_item ) {
		$tax_rate_items[$tax_item->get_rate_id()] = $tax_item->get_rate_percent();
	}
	//carga lineas detalles
	$detalles=array();
	foreach ( $order->get_items() as $item_id => $item ) {
		//$custom_field = wc_get_order_item_meta( $item_id, '_line_tax_data', true );
		//foreach ( $custom_field['total'] as $current_rate_id => $value ) { }
		
		$taxes = $item->get_taxes();
		$rate_tax=0; //si no es exenta o no sujeta a iva no puede llevar iva 0
		//foreach( $taxes['subtotal'] as $rate_id => $tax ){
		foreach( $taxes['total'] as $rate_id => $tax ){
			//$rate_tax = $tax_rate_items[$rate_id];
			if($tax){ $rate_tax = $tax_rate_items[$rate_id]; }
		}
		if($item['total']!=0){
			$cantidad = $item['quantity'];
			$precio_unidad = $item['total']/$cantidad;
			if ($cantidad>0 && $precio_unidad<0){
				$cantidad=$cantidad*-1;
				$precio_unidad=$precio_unidad*-1;
			}
			
			if($exentaiva==''){
				$requ=0;
				if($FTBAI_clientesRE==1){
					if ($rate_tax==0.5){$rate_tax=4; $requ=1;}
					if ($rate_tax==1.4){$rate_tax=10; $requ=1;}
					if ($rate_tax==5.2){$rate_tax=21; $requ=1;}
					if ($nif=='' && $requ==1){
						$resultado = 'No se puede emitir una factura con Recargo Equivalencia RE sin el CIF-NIF del cliente';
						$response = array('success'=>0, 'resultado'=>$resultado);
						//devuelve error pedido no procesado
						$headers = array('Content-Type: text/html; charset=UTF-8');
						wp_mail( get_option('admin_email'), 'ERROR: Factura no procesada', 'PEDIDO:'.$postid.'<br>'.$resultado, $headers );
						return json_encode($response);
					}
				}
				//añade itemlinea
				array_push($detalles,array( "nombre"				=>	$item['name'], 
											"precio_unidad"			=>	$precio_unidad, //$item['subtotal']/$item[' quantity'],
											"cantidad"				=>	$cantidad,
											"descuento_porcentual"	=>	0,
											"iva"					=>	$rate_tax,
											"re"					=>	$requ)
				);		
			}else{
				if($rate_tax>0){
					$response = array('success'=>0, 'resultado'=> $exentaiva.' '.__( 'The sale is EXEMPT from VAT and has products with VAT', 'wp-ticketbai' ) );
					return json_encode($response); 	
				}
				//añade itemlinea no aplica iva
				array_push($detalles,array( "nombre"					=>	$item['name'], 
											"precio_unidad"				=>	$precio_unidad, //$item['subtotal']/$item[' quantity'],
											"cantidad"					=>	$cantidad,
											"descuento_porcentual"		=>	0,
										   	"exenta"					=>  1,
										   	"causa_exenta"				=>  $exentaiva,
										   	"tipo_operacion_extranjero" =>  "Entrega",
											"re"						=>	0)
				);					
			}
		}
	}

	//linea de envio transporte
	if($order->get_shipping_total()){
		foreach( $order->get_items('shipping') as $item_id => $item_ship ){
			$taxes = $item_ship->get_taxes();
			$rate_tax=0; //si no es exenta o no sujeta a iva no puede llevar iva 0
			//foreach( $taxes['subtotal'] as $rate_id => $tax ){
			foreach( $taxes['total'] as $rate_id => $tax ){
				//$rate_tax = $tax_rate_items[$rate_id];
				if($tax){ $rate_tax = $tax_rate_items[$rate_id]; }
			}
			if($exentaiva==''){
				//añade itemlinea cuota
				array_push($detalles,array( 	"nombre"				=>	$item_ship->get_name(), 
												"precio_unidad"			=>	$item_ship->get_total(),
												"cantidad"				=>	1,
												"descuento_porcentual"	=>	0,
												"iva"					=>	$rate_tax,
												"re"					=>	0)
				);
			}else{
				if($rate_tax>0){
					$response = array('success'=>0, 'resultado'=> $exentaiva.' '.__( 'The sale is EXEMPT from VAT and has products with VAT', 'wp-ticketbai' ) );
					return json_encode($response); 	
				}
				//añade itemlinea cuota no aplica iva
				array_push($detalles,array( 	"nombre"					=>	$item_ship->get_name(), 
												"precio_unidad"				=>	$item_ship->get_total(),
												"cantidad"					=>	1,
												"descuento_porcentual"		=>	0,
										   		"exenta"					=>  1,
										   		"causa_exenta"				=>  $exentaiva,
										   		"tipo_operacion_extranjero" =>  "Entrega",
												"re"						=>	0)
				);				
			}
		}	
	}
	
	//lineas de cuota
	$get_fees = $order->get_fees();
	if(count($get_fees)>0){
		foreach( $order->get_items('fee') as $item_id => $item_fee ){
			$taxes = $item_fee->get_taxes();
			$rate_tax=0; //si no es exenta o no sujeta a iva no puede llevar iva 0
			//foreach( $taxes['subtotal'] as $rate_id => $tax ){
			foreach( $taxes['total'] as $rate_id => $tax ){
				//$rate_tax = $tax_rate_items[$rate_id];
				if($tax){ $rate_tax = $tax_rate_items[$rate_id]; }
			}
			if($exentaiva==''){
				//añade itemlinea cuota
				array_push($detalles,array( 	"nombre"				=>	$item_fee->get_name(), 
												"precio_unidad"			=>	$item_fee->get_total(),
												"cantidad"				=>	1,
												"descuento_porcentual"	=>	0,
												"iva"					=>	$rate_tax,
												"re"					=>	0)
				);
			}else{
				if($rate_tax>0){
					$response = array('success'=>0, 'resultado'=> $exentaiva.' '.__( 'The sale is EXEMPT from VAT and has products with VAT', 'wp-ticketbai' ) );
					return json_encode($response); 	
				}
				//añade itemlinea cuota no aplica iva
				array_push($detalles,array( 	"nombre"					=>	$item_fee->get_name(), 
												"precio_unidad"				=>	$item_fee->get_total(),
												"cantidad"					=>	1,
												"descuento_porcentual"		=>	0,
										   		"exenta"					=>  1,
										   		"causa_exenta"				=>  $exentaiva,
										   		"tipo_operacion_extranjero" =>  "Entrega",
												"re"						=>	0)
				);				
			}
		}
	}
	
	//cupones
//	$get_discount_total = $order->get_discount_total();
//	if($get_discount_total!=0){
//		$get_discount_tax = $order->get_discount_tax();
//		$discount_tax = round(($get_discount_tax/$get_discount_total)*100);
//		array_push($detalles,array( 	"nombre"				=>	'DESCUENTO', 
//										"precio_unidad"			=>	$get_discount_total,
//										"cantidad"				=>	-1,
//										"descuento_porcentual"	=>	0,
//										"iva"					=>	$discount_tax,
//										"re"					=>	0)
//		);		
//	}	
	
	if($nif==''){
		//SIMPLIFICADA
		$main = json_encode( array( 	"titular"		=> array( 	"id_transaccion"	=>  $postid,
																	"nif"				=>	$user_nif,
																	"codigo"			=>	$tbaicode 
																),
										"factura"		=> array( 	"tipo"				=>	"Simplificada",
																 	"detalles_con_iva"	=> 	0,
																	"retorno_xml"		=>  1
																),
										"detalles"		=> $detalles
									 )
							  );	
	}else{
		//FACTURA COMPLETA
		if($country_code=='ES'){
			$destinatario = array( 	"nombre"					=>	$nombre,
									"nif"						=>  $nif,
									"domicilio"					=>	$domicilio,
									"codigo_postal"				=>	$codigo_postal,
									"localidad"					=>  $localidad,
									"provincia"					=>	$provincia
								);
		}else{
			$destinatario = array( 	"nombre"					=>	$nombre,
									"documento_extranjero"		=>  $nif,
								  	"tipo_documento_extranjero"	=>  "06",
								    "operacion_extranjero"		=>  "Entrega",
									"domicilio"					=>	$domicilio,
									"codigo_postal"				=>	$codigo_postal,
									"localidad"					=>  $localidad,
									"provincia"					=>	$provincia,
									"codigo_pais_extranjero"	=>  $country_code
								);
		}
		$main = json_encode( array( 	"titular"		=> array( 	"id_transaccion"	=>  $postid,
																	"nif"				=>	$user_nif, 
																	"codigo"			=>	$tbaicode
																),
										"destinatario"	=> $destinatario,
										"factura"		=> array( 	"tipo"				=>	"Completa",
																 	"detalles_con_iva"	=> 	0,
																	"retorno_xml"		=>  1
																),
										"detalles"		=> $detalles
									 )
							  );	
	}

	
	$result = FTBAI_curl('alta',$main);
	//$result = array($result,'header'=>$header,'body'=>$body);
	
	//graba factura
	$body = json_decode($result['body'],true);
	$destinatario = $body['destinatario'];
	if($destinatario['nombre']){$nombre=$destinatario['nombre'];}
	if($destinatario['nif']){$nif=$destinatario['nif'];}
	$factura = $body['factura'];
	$estado=$factura['estado'];
	$serie=$factura['serie'];
	$numero=$factura['numero'];
	$tipo=$factura['tipo'];
	$fecha=$factura['fecha'];
	$hora=$factura['hora'];
	$fechasql=date("Y-m-d", strtotime($fecha) );
	$fechahora=date("Y-m-d H:i:s", strtotime($fecha.' '.$hora));
	$tbai=$factura['tbai'];
	$qr=$factura['qr'];
	$descripcion=$factura['descripcion'];
	$total_final=$factura['total_final'];
	//$xml=$body['xml'];

	//if ($estado=='Grabada' || $estado=='Rectificada' || $estado=='Anulada'){
	if ($estado!=''){
		$status='Emitida';
		if($estado=='Anulada'){$status='Anulada';}
		//graba transaccion
		global $wpdb;
		$user_nif = get_option('FTBAI_DNI');
		$tbaicode = get_option('FTBAI_CLAVE');
		if($user_nif=='' || $tbaicode==''){
			$user_nif=get_option('FTBAI_DNI_TEST');
			$tbaicode=get_option('FTBAI_CLAVE_TEST');
		}		
		$insertsql = $wpdb->get_results("
			REPLACE INTO `{$wpdb->prefix}ftbai_facturas` 
				(`id_transaccion`, `user_dni`, `user_apikey`, `fecha_transaccion`, `pedido_numero`, `pedido_fecha`, `factura_estado`, `factura_tipo`, `factura_vista`, `factura_serie`, `factura_numero`, `factura_fecha`, `factura_total`, `cliente_nombre`, `cliente_nif`, `tbai_num`, `tbai_qr`)
			VALUES
				('".$postid."', '".$user_nif."', '".$tbaicode."', '".$fechahora."', '".$postid."', '".$fechahora."', '".$status."', '".$tipo."', '0000-00-00 00:00:00', '".$serie."', '".$numero."', '".$fechahora."', '".$total_final."', '".$nombre."', '".$nif."', '".$tbai."', '".$qr."')
		" );		
		$response = array('success'=>1, 'status'=>'ejecutado', 'postid'=>$postid);
		if(get_option('FTBAI_sendfactautomatica')==1){
			$enviado = FONE_enviaremail($postid);
		}
	}else{
		$header = $result['header'];
		$headers_arr = explode("\r\n", $header); // The separator used in the Response Header is CRLF (Aka. \r\n)
		$resultado = str_replace("HTTP/1.1 400","",$headers_arr[0]);
		$response = array('success'=>0, 'resultado'=>trim($resultado));
		
		//devuelve error pedido no procesado
		$errornoprocesado = print_r($main, true); //$var = print_r($result, true); 
		$headers = array('Content-Type: text/html; charset=UTF-8');
		wp_mail( get_option('admin_email'), 'ERROR: Pedido no procesado', 'El pedido no ha podido ser procesado por el siguiente motivo:<br>'.$resultado.'<br><br>'.$errornoprocesado, $headers );
	}
	return json_encode($response); 
}

function FONE_enviaremail($order_id,$email=''){
	$user_nif = get_option('FTBAI_DNI');
	$tbaicode = get_option('FTBAI_CLAVE');
	if($user_nif=='' || $tbaicode==''){ 
		return FALSE; //en demo no envia email
		//$user_nif=get_option('FTBAI_DNI_TEST');
		//$tbaicode=get_option('FTBAI_CLAVE_TEST');
	}
	
	$results = FTBAI_getfield_facturas($order_id);
	$id_transaccion = $results->id_transaccion;
	$serie = $results->factura_serie;
	$numero = $results->factura_numero;
	$tbai_num = $results->tbai_num;
	$tbai_qr = $results->tbai_qr;
	$factura_rectificativa_de = $results->factura_rectificativa_de;
	$factura_estado = $results->factura_estado;
	$fecha = date("d-m-Y", strtotime($results->factura_fecha) );	
	$main = json_encode( array( 	"titular"=> array( 	"id_transaccion"	=> $id_transaccion,
														"nif"				=> $user_nif, 
														"codigo"			=> $tbaicode ),
									"factura"=> array( 	"serie"				=> $serie, 
														"numero"			=> $numero,
														"fecha"				=> $fecha ),
								 ) 
						  );
	$result = FTBAI_curl('pdf',$main);
	//comprueba pdf creado
	$resulpdf = print_r($result, true);
	if ( $resulpdf=='' || strpos($resulpdf,'filename="factura.pdf"')===false || strpos($resulpdf,'Internal Server Error')!==false ){
		$headers = array('Content-Type: text/html; charset=UTF-8');
		wp_mail( get_option('admin_email'), 'ERROR: PDF Factura no generada', $resulpdf, $headers );
		return false;
	}
	$upload_dir = wp_upload_dir();
	$basedir = $upload_dir['basedir'];
	if (!file_exists($basedir.'/wp-factubai')) {mkdir($basedir.'/wp-factubai', 0777, true);}
	$filename=$basedir."/wp-factubai/factura_".$serie.$numero."_".time().".pdf";
	file_put_contents($filename,$result);
	
	if($factura_rectificativa_de>0){
		$order = new WC_Order($factura_rectificativa_de);
		$nombre=$order->get_billing_first_name().' '.$order->get_billing_last_name();	
		if( trim($email)=='' ){ $email=$order->get_billing_email();	}
		$to = $email;
		$subject = get_option('blogname').' - Factura '.$serie.'/'.$numero.' del pedido '.$order_id.' con fecha '.$fecha;
		$body = 'Estimado cliente '.$nombre.'<br>';
		$body.= '<h4>Le adjuntamos su factura '.$serie.'/'.$numero.' del pedido numero #'.$order_id.'</h4>';
	}else{
		$order = new WC_Order($order_id);
		$nombre=$order->get_billing_first_name().' '.$order->get_billing_last_name();
		if( trim($email)=='' ){	$email=$order->get_billing_email();	}
		$to = $email;
		$subject = get_option('blogname').' - Factura '.$serie.'/'.$numero.' del pedido '.$order_id.' con fecha '.$fecha;
		$body = 'Estimado cliente '.$nombre.'<br>';
		$body.= '<h4>Le adjuntamos su factura '.$serie.'/'.$numero.' del pedido numero #'.$order_id.'</h4>';
	}
	$body.= $tbai_num.'<br>';
	$body.= $tbai_qr.'<br>';
	
	//pdfinvoice
	if (function_exists('is_plugin_active')){
		if(is_plugin_active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php') || is_plugin_active('wpo_wcpdf')){
			$document = wcpdf_get_document( 'invoice', $order, true );
			if($document && $factura_rectificativa_de==0 && $factura_estado=='Emitida'){
				if ( has_action( 'wpo_wcpdf_created_manually' ) ) {
						do_action( 'wpo_wcpdf_created_manually', $document->get_pdf(), $document->get_filename() );
				}
				//inline $output_mode = WPO_WCPDF()->settings->get_output_mode( $document_type );
				//$document->output_pdf( 'download' );
				//$body.=$document->get_html();
				$result = $document->get_pdf();
				file_put_contents($filename,$result);
			}
		}	
	}

	//$body.= get_option('blogname');
	$headers = array('Content-Type: text/html; charset=UTF-8;');
	//envia copia
	$FTBAI_copyemail = get_option('FTBAI_copyemail');
	if(trim($FTBAI_copyemail)!=''){ $headers[] = 'Bcc: '.$FTBAI_copyemail; }
	//envia email
	$attachments = array($filename);
    $enviado = wp_mail($to, $subject, $body, $headers, $attachments);
	if($enviado){ FTBAI_update($order_id,'factura_vista', wp_date(DATE_RFC3339) ); }
	//elimina temporal
	unlink($filename);
	return $enviado;
}


function FTBAI_anular($postid,$manual){
	//anular factura
	$user_nif = get_option('FTBAI_DNI');
	$tbaicode = get_option('FTBAI_CLAVE');
	if($user_nif=='' || $tbaicode==''){
		$user_nif=get_option('FTBAI_DNI_TEST');
		$tbaicode=get_option('FTBAI_CLAVE_TEST');
	}
	if($postid>0){
		$results = FTBAI_getfield_facturas($postid);
		$id_transaccion = $results->id_transaccion;
		$serie = $results->factura_serie;
		$numero = $results->factura_numero;
		$factura_rectificativa_de = $results->factura_rectificativa_de;
		$fecha = date("d-m-Y", strtotime($results->factura_fecha) );
		$numfac = $serie.' / '.$numero;
		if($id_transaccion>0){
			$main = json_encode( array( 	"titular"=> array( 	"id_transaccion"=>$id_transaccion,
																"nif"			=>$user_nif, 
																"codigo"		=>$tbaicode),
											"factura"=> array( 	"serie"			=>$serie, 
																"numero"		=>$numero,
																"fecha"			=>$fecha)
										 ));
			$result = FTBAI_curl('anulacion',$main);
			$body = json_decode($result['body'],true);
			$factura = $body['factura'];
			$estado=$factura['estado'];			
			//comprueba si esta anulada
			if($estado=='Anulada'){
				global $wpdb;
				FTBAI_update($id_transaccion,'factura_estado','Anulada');
				if($factura_rectificativa_de>0){FTBAI_update($factura_rectificativa_de,'factura_rectificada_en','0');}
				if($manual==0){
					$order = new WC_Order($postid);
					if (!empty($order)) {$order->update_status('wc-cancelled');}
				}
				//envio email
				//$to = 'email@email.com';
				//$subject = 'Factura '.$numfac.' Anulada';
				//$body = '<h4>Anulación de su factura</h4><br>';
				//$body.= 'Hemos procedido a anular su factura numero: '.$numfac;
				//$headers = array('Content-Type: text/html; charset=UTF-8');
				//wp_mail($to,$subject,$body,$headers );
				echo json_encode(array('success'=>1));die;
			}else{
				$header = $result['header'];
				$headers_arr = explode("\r\n", $header); // The separator used in the Response Header is CRLF (Aka. \r\n)
				$resultado = str_replace("HTTP/1.1 400","",$headers_arr[0]);
				echo json_encode(array('success'=>0, 'result'=> trim($resultado) ));die;
			}
		}else{
			echo json_encode(array('success'=>0));die;
		}
	}
}

function FTBAI_registrarectificativa($postid, $rectificativaid, $arrlineas){
	$result = FTBAI_rectificativa($postid, $rectificativaid, $arrlineas); //////////////////////envia factura

	$body = json_decode($result['body'],true);
	//
	$destinatario = $body['destinatario'];
	$nombre=$destinatario['nombre'];
	$nif=$destinatario['nif'];
	//
	$factura = $body['factura'];
	$estado=$factura['estado'];
	//print_r($factura);
	$serie=$factura['serie'];
	$numero=$factura['numero'];
	$tipo=$factura['tipo'];
	$fecha=$factura['fecha'];
	$hora=$factura['hora'];
	$fechasql=date("Y-m-d", strtotime($fecha) );
	$fechahora=date("Y-m-d H:i:s", strtotime($fecha.' '.$hora));
	$tbai=$factura['tbai'];
	$qr=$factura['qr'];
	$descripcion=$factura['descripcion'];
	$total_final=$factura['total_final'];
	//$xml=$body['xml'];

	//graba transaccion
	global $wpdb;
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
			('".$rectificativaid."', '".$user_nif."', '".$tbaicode."', '".$fechahora."', '".$rectificativaid."', '".$fechahora."', 'Emitida', 'RECTIFICATIVA', '".$postid."', '0000-00-00 00:00:00', '".$serie."', '".$numero."', '".$fechahora."', '".$total_final."', '".$nombre."', '".$nif."', '".$tbai."', '".$qr."')
	" );
	//comprueba resultado
	$header = $result['header'];
	$headers_arr = explode("\r\n", $header); // The separator used in the Response Header is CRLF (Aka. \r\n)
	$resultado = $headers_arr[0];
	if ($estado=='Grabada'){
		$response = array('success'=>1, 'status'=>'ejecutado', 'postid'=>$postid);
	}else{
		$response = array('success'=>0, 'resultado'=>$resultado);
	}
	return json_encode($response); 
}

function FTBAI_rectificativa($postid, $rectificativaid, $arrlineas){
	$arrlineas = json_decode($arrlineas);
	$user_nif = get_option('FTBAI_DNI');
	$tbaicode = get_option('FTBAI_CLAVE');
	if($user_nif=='' || $tbaicode==''){
		$user_nif=get_option('FTBAI_DNI_TEST');
		$tbaicode=get_option('FTBAI_CLAVE_TEST');
	}

	//DATOS FACTURA ORIGEN
	$results = FTBAI_getfield_facturas($postid); 
	$seriefac = $results->factura_serie;
	$numfac = $results->factura_numero;
	$fecha = date("d-m-Y", strtotime($results->factura_fecha) );																	

	//carga order
	$nif=get_post_meta( $postid, '_vat_number', true );
	$order = new WC_Order($postid);

	$nombre=$order->get_billing_first_name().' '.$order->get_billing_last_name();
	$domicilio=$order->get_billing_address_1().' '.$order->get_billing_address_2();
	$codigo_postal=$order->get_billing_postcode();
	$localidad=$order->get_billing_city();
	
	//ciudad
	$country_code = $order->get_billing_country();
	$state_code   = $order->get_billing_state();
	$countries = new WC_Countries(); // Get an instance of the WC_Countries Object
	$country_states = $countries->get_states($country_code); // Get the states array from a country code
	//$provincia     = $country_states[$state_code]; // get the state name
	$provincia = isset($country_states[$state_code]) ? $country_states[$state_code] : '';
	if (empty($provincia)) {
		$all_countries = $countries->get_countries();
		$provincia = isset($all_countries[$country_code]) ? $all_countries[$country_code] : $country_code;
	}

	//recargo_equivalencia
	//$requ=0;
	//$requ=trim(get_post_meta($postid, '_billing_recargo_de_equivalencia', true));
	$FTBAI_clientesRE=get_option('FTBAI_clientesRE');
	
	// Initializing variables tipo iva
	$tax_rate_items = array(); // The tax labels by $rate Ids
	foreach ( $order->get_items('tax') as $tax_item ) {
		$tax_rate_items[$tax_item->get_rate_id()] = $tax_item->get_rate_percent();
	}
	//carga lineas detalles
	$detalles=array();
	foreach ( $order->get_items() as $item_id => $item ) {
		if (in_array($item_id, $arrlineas)){
			$taxes = $item->get_taxes();
			$rate_tax=0; //si no es exenta o no sujeta a iva no puede llevar iva 0
			//foreach( $taxes['subtotal'] as $rate_id => $tax ){
			foreach( $taxes['total'] as $rate_id => $tax ){
				//$rate_tax = $tax_rate_items[$rate_id];
				if($tax){ $rate_tax = $tax_rate_items[$rate_id]; }
			}
			if($item['total']!=0){
				$requ=0;
				if($FTBAI_clientesRE==1){
					if ($rate_tax==0.5){$rate_tax=4;$requ=1;}
					if ($rate_tax==1.4){$rate_tax=10;$requ=1;}
					if ($rate_tax==5.2){$rate_tax=21;$requ=1;}
				}
				//añade itemlinea
				array_push($detalles,array( "nombre"				=>	$item['name'], 
											"precio_unidad"			=>	$item['total']/$item['quantity'], //$item['subtotal']/$item['quantity'],
											"cantidad"				=>	($item['quantity']*-1),
											"descuento_porcentual"	=>	0,
											"iva"					=>	$rate_tax,
											"re"					=>	$requ)
				);		
			}
		}		
	}
	
	//linea de envio transporte
	if( in_array(-1, $arrlineas) && $order->get_shipping_total() ){
		foreach( $order->get_items('shipping') as $item_id => $item_ship ){
			$taxes = $item_ship->get_taxes();
			$rate_tax=0; //si no es exenta o no sujeta a iva no puede llevar iva 0
			//foreach( $taxes['subtotal'] as $rate_id => $tax ){
			foreach( $taxes['total'] as $rate_id => $tax ){
				//$rate_tax = $tax_rate_items[$rate_id];
				if($tax){ $rate_tax = $tax_rate_items[$rate_id]; }
			}
			//añade itemlinea cuota
			array_push($detalles,array( 	"nombre"				=>	$item_ship->get_name(), 
											"precio_unidad"			=>	$item_ship->get_total(),
											"cantidad"				=>	-1,
											"descuento_porcentual"	=>	0,
											"iva"					=>	$rate_tax,
											"re"					=>	0)
			);
		}	
	}
	
	//lineas de cuota
	$get_fees = $order->get_fees();
	if(count($get_fees)>0){
		foreach( $order->get_items('fee') as $item_id => $item_fee ){
			if (in_array($item_id, $arrlineas)){
				$taxes = $item_fee->get_taxes();
				$rate_tax=0; //si no es exenta o no sujeta a iva no puede llevar iva 0
				//foreach( $taxes['subtotal'] as $rate_id => $tax ){
				foreach( $taxes['total'] as $rate_id => $tax ){
					//$rate_tax = $tax_rate_items[$rate_id];
					if($tax){ $rate_tax = $tax_rate_items[$rate_id]; }
				}
				//añade itemlinea
				array_push($detalles,array( "nombre"				=>	$item_fee->get_name(), 
											"precio_unidad"			=>	$item_fee->get_total(),
											"cantidad"				=>	-1,
											"descuento_porcentual"	=>	0,
											"iva"					=>	$rate_tax,
											"re"					=>	0)
				);		
			}								
		}
	}	
	
	//linea de descuento
//	$get_discount_total = $order->get_discount_total();
//	if( in_array(-2, $arrlineas) && $get_discount_total ){
//		$get_discount_tax = $order->get_discount_tax();
//		$discount_tax = round(($get_discount_tax/$get_discount_total)*100);
//		array_push($detalles,array( 	"nombre"				=>	'DESCUENTO', 
//										"precio_unidad"			=>	$get_discount_total,
//										"cantidad"				=>	+1,
//										"descuento_porcentual"	=>	0,
//										"iva"					=>	$discount_tax,
//										"re"					=>	0)
//		);		
//	}
	
	//RECTIFICATIVA
	if($nif==''){
		//RECTIFICATIVA DE SIMPLIFICADA
		$tipo_rectificativa="I"; //por diferencias
		$tipo="Simplificada Rectificativa";
		$codigo_rectificativa="R5"; // DE SIMPLIFICADA
		$main = json_encode( array( 	"titular"		=> array( 	"id_transaccion"		=>  $rectificativaid,
																	"nif"					=>	$user_nif, 
																	"codigo"				=>	$tbaicode
																),
										"factura"		=> array( 	"tipo"					=>	$tipo,
																	"tipo_rectificativa"	=> 	$tipo_rectificativa,
																	"codigo_rectificativa"	=> 	$codigo_rectificativa,
																 	"detalles_con_iva"		=> 	0,
																	"retorno_xml"			=>  1
																),
										"detalles"		=> $detalles,
										"rectificadas"	=> [array(	"serie"			=>	$seriefac,
																	"numero"		=>	$numfac,
																	"fecha"			=>	$fecha
															)]

									 )
							  );		
	}else{
		//RECTIFICATIVA DE COMPLETA
		if($country_code=='ES'){
			$destinatario = array( 	"nombre"		=>	$nombre,
									"nif"			=>  $nif,
									"domicilio"		=>	$domicilio,
									"codigo_postal"	=>	$codigo_postal,
									"localidad"		=>  $localidad,
									"provincia"		=>	$provincia
								);
		}else{
			$destinatario = array( 	"nombre"					=>	$nombre,
									"documento_extranjero"		=>  $nif,
								  	"tipo_documento_extranjero"	=>  "06",
									"domicilio"					=>	$domicilio,
									"codigo_postal"				=>	$codigo_postal,
									"localidad"					=>  $localidad,
									"provincia"					=>	$provincia,
									"codigo_pais_extranjero"	=>  $country_code
								);
		}		
		$tipo_rectificativa="I"; //por diferencias
		$tipo="Completa Rectificativa";
		$codigo_rectificativa="R1"; //Rappel, descuento, bonificación, abono, etc
		$main = json_encode( array( 	"titular"		=> array( 	"id_transaccion"		=>  $rectificativaid,
																	"nif"					=>	$user_nif, 
																	"codigo"				=>	$tbaicode
																),
										"destinatario"	=> $destinatario,
										"factura"		=> array( 	"tipo"					=>	$tipo,
																	"tipo_rectificativa"	=> 	$tipo_rectificativa,
																	"codigo_rectificativa"	=> 	$codigo_rectificativa,
																 	"detalles_con_iva"		=> 	0,
																 	"retorno_xml"			=>  1
																),
										"detalles"		=> $detalles,
										"rectificadas"	=> [array(	"serie"			=>	$seriefac,
																	"numero"		=>	$numfac,
																	"fecha"			=>	$fecha
															)]

									 )
							  );
	}

	$result = FTBAI_curl('alta',$main);
	return $result;
}

function FTBAI_curl($tipo,$post){
    $response = wp_safe_remote_post("https://api.wptbai.com/apirest.php", array('method' => 'POST', 'timeout' => 90, 'redirection' => 5, 'httpversion' => '1.0', 'blocking' => true, 'headers' => array(), 'body' => array( 'tipo' => base64_encode($tipo), 'post' => base64_encode($post), 'version' => get_option('FTBAI_version') ), 'cookies' => array()) );	
	if (is_wp_error($response)){
		$error = 'error '.$response->get_error_message();
		return array('result'=>$error,'header'=>$error,'body'=>$error);
	}else{
		$result = json_decode(wp_remote_retrieve_body($response),true);
		if( isset( $result['result'] ) ){$resultado = $result['result'];}else{$resultado="";}
		if( isset( $result['header'] ) ){$header = $result['header'];}else{$header="";}
		if( isset( $result['body'] ) ){$body = $result['body'];}else{$body="";}
		return array(	'result' 	=> base64_decode($resultado),
					 	'header' 	=> base64_decode($header),
					 	'body' 		=> base64_decode($body)
					);
	}
}
?>