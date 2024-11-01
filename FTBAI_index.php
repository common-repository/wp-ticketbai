<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	if(!is_admin() || strpos( $_SERVER['REQUEST_URI'], 'admin.php?page=WPTicketBAI') === false) { return; } 
	//wp_enqueue_style('FTBAI_sweetalert-css', plugins_url( 'assets/sweetalert/sweetalert2.min.css', __FILE__ ),null,time(),'all');
	//wp_enqueue_script('FTBAI_sweetalert-js',  plugins_url( 'assets/sweetalert/sweetalert2.all.min.js', __FILE__ ),null,time(),'all');
	//wp_enqueue_script('FTBAI_procesos-js',  plugins_url( 'js/FTBAI_procesos.js', __FILE__ ),null,time(),'all');
	//wp_enqueue_script('FTBAI_verpdf-js',  plugins_url( 'js/FTBAI_verpdf.js', __FILE__ ),null,time(),'all');
	if ( !class_exists( 'WooCommerce' ) ) {
		echo("<div class='error message' style='padding:10px;'>El plugin <b>TicketBai para WooCommerce</b> requiere tener instalado y activado el plugin WooCommerce.</div>");
		return;
	}
?>
<style>
	#FTBAI-header-upgrade-message p .dashicons {
		color: #f2a64c;
		margin-right: 5px;
	}
	#FTBAI-header-upgrade-message {
		text-align: center;
		background-color: #f5f0c0;
		color: #222;
		padding: 10px;
		margin-left: -20px;
		-webkit-box-shadow: 0 0 3px rgb(0 0 0 / 20%);
		box-shadow: 0 0 3px rgb(0 0 0 / 20%);
	}
	#FTBAI-header-ok-message {
		text-align: center;
		background-color: #e5fdff;
		color: #222;
		padding: 10px;
		margin-left: -20px;
		-webkit-box-shadow: 0 0 3px rgb(0 0 0 / 20%);
		box-shadow: 0 0 3px rgb(0 0 0 / 20%);
	}	
	#FTBAI-p-message{
		margin: 0;
	}
	.swal2-popup {
	  font-size: 0.9rem !important;
	  /*font-family: Georgia, serif;*/
	}	
	#wpfooter{
		display:none !important;
	}	
	#fone_snackbar {
	  visibility: hidden;
	  min-width: 250px;
	  margin-left: -125px;
	  background-color: #333;
	  color: #fff;
	  text-align: center;
	  border-radius: 2px;
	  padding: 16px;
	  position: fixed;
	  z-index: 1;
	  left: 50%;
	  bottom: 30px;
	  font-size: 17px;
	}
	#fone_snackbar.show {
	  visibility: visible;
	  -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
	  animation: fadein 0.5s, fadeout 0.5s 2.5s;
	}
	@-webkit-keyframes fadein {
	  from {bottom: 0; opacity: 0;} 
	  to {bottom: 30px; opacity: 1;}
	}
	@keyframes fadein {
	  from {bottom: 0; opacity: 0;}
	  to {bottom: 30px; opacity: 1;}
	}
	@-webkit-keyframes fadeout {
	  from {bottom: 30px; opacity: 1;} 
	  to {bottom: 0; opacity: 0;}
	}
	@keyframes fadeout {
	  from {bottom: 30px; opacity: 1;}
	  to {bottom: 0; opacity: 0;}
	}	
</style>
<?php 
$menutab='';
$mensaje='';
$verinforme='';
$FTBAI_informe_trimestre='';
$FTBAI_informe_ejercicio='';
$validardatos='';

//clave registro
if (isset($_POST['fact_nonce_action'])){
	if (wp_verify_nonce($_POST['fact_nonce_action'], 'fact_nonce_action')){
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "salvaropciones"){
			$FTBAI_DNI = sanitize_text_field(trim($_POST['FTBAI_DNI']));
			$FTBAI_CLAVE = sanitize_text_field(trim($_POST['FTBAI_CLAVE']));
			if($FTBAI_DNI=='' || $FTBAI_CLAVE==''){$FTBAI_DNI='';$FTBAI_CLAVE='';}
			update_option('FTBAI_DNI',$FTBAI_DNI);
			update_option('FTBAI_CLAVE',$FTBAI_CLAVE);
			$validardatos = FTBAI_validardatos();
			$menutab='Ajustes';
		}
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "verinforme"){
			$FTBAI_informe_ejercicio=sanitize_text_field($_POST['FTBAI_informe_ejercicio']);
			$FTBAI_informe_trimestre=sanitize_text_field($_POST['FTBAI_informe_trimestre']);
			$verinforme = FTBAI_informesiva($FTBAI_informe_ejercicio,$FTBAI_informe_trimestre);
			$menutab='Informes';
		}
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "registrarse"){
			$FTBAI_REG_NOMBRE = sanitize_text_field(trim($_POST['FTBAI_REG_NOMBRE']));
			$FTBAI_REG_DNI = sanitize_text_field(trim($_POST['FTBAI_REG_DNI']));
			$FTBAI_REG_EMAIL = sanitize_text_field(trim($_POST['FTBAI_REG_EMAIL']));
			$FTBAI_REG_TELEFONO = sanitize_text_field(trim($_POST['FTBAI_REG_TELEFONO']));	
			$FTBAI_REG_MENSAJE = sanitize_text_field(trim($_POST['FTBAI_REG_MENSAJE']));	
			update_option('FTBAI_REG_NOMBRE',$FTBAI_REG_NOMBRE);
			update_option('FTBAI_REG_DNI',$FTBAI_REG_DNI);
			update_option('FTBAI_REG_EMAIL',$FTBAI_REG_EMAIL);
			update_option('FTBAI_REG_TELEFONO',$FTBAI_REG_TELEFONO);
			update_option('FTBAI_REG_MENSAJE',$FTBAI_REG_MENSAJE);
			FTBAI_registrarse();
			$menutab='Registro';		
		} 
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "salvar_avanzado" && isset($_POST['FTBAI_emitefactautomatica'])){
			update_option('FTBAI_emitefactautomatica',sanitize_text_field($_POST['FTBAI_emitefactautomatica']));
			$mensaje=__( 'Change made successfully', 'wp-ticketbai' );
			$menutab='Avanzado';
		}		
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "salvar_avanzado" && isset($_POST['FTBAI_sendfactautomatica'])){
			update_option('FTBAI_sendfactautomatica',sanitize_text_field($_POST['FTBAI_sendfactautomatica']));
			$mensaje=__( 'Change made successfully', 'wp-ticketbai' );
			$menutab='Avanzado';
		}		
		if(isset($_POST['action2']) && sanitize_text_field($_POST['action2']) == "btnsavecopyemail"){
			update_option('FTBAI_copyemail',sanitize_text_field($_POST['FTBAI_copyemail']));
			$mensaje=__( 'Change made successfully', 'wp-ticketbai' );
			$menutab='Avanzado';
		}
		if(isset($_POST['action3']) && sanitize_text_field($_POST['action3']) == "btnsavemaxsimplificada"){
			update_option('FTBAI_maxsimplificada',sanitize_text_field($_POST['FTBAI_maxsimplificada']));
			$mensaje=__( 'Change made successfully', 'wp-ticketbai' );
			$menutab='Avanzado';
		}
		if(isset($_POST['action4']) && sanitize_text_field($_POST['action4']) == "btnsavenumpedido"){
			update_option('FTBAI_apartirnumeropedido',sanitize_text_field($_POST['FTBAI_apartirnumeropedido']));
			$mensaje=__( 'Change made successfully', 'wp-ticketbai' );
			$menutab='Avanzado';
		}
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "salvar_avanzado" && isset($_POST['FTBAI_shownif'])){
			update_option('FTBAI_shownif',sanitize_text_field($_POST['FTBAI_shownif']));
			$mensaje=__( 'Change made successfully', 'wp-ticketbai' );
			$menutab='Avanzado';
		}	
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "salvar_avanzado" && isset($_POST['FTBAI_clientesRE'])){
			update_option('FTBAI_clientesRE',sanitize_text_field($_POST['FTBAI_clientesRE']));
			$mensaje=__( 'Change made successfully', 'wp-ticketbai' );
			$menutab='Avanzado';
		}
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "salvar_avanzado" && isset($_POST['FTBAI_posicionQR'])){
			update_option('FTBAI_posicionQR',sanitize_text_field($_POST['FTBAI_posicionQR']));
			$mensaje=__( 'Change made successfully', 'wp-ticketbai' );
			$menutab='Avanzado';
		}        
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "salvar_avanzado" && isset($_POST['FTBAI_operacionextranjero'])){
			update_option('FTBAI_operacionextranjero',sanitize_text_field($_POST['FTBAI_operacionextranjero']));
			$mensaje=__( 'Change made successfully', 'wp-ticketbai' );
			$menutab='Avanzado';
		}
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "salvar_avanzado" && isset($_POST['FTBAI_canariasnoexentoiva'])){
			update_option('FTBAI_canariasnoexentoiva',sanitize_text_field($_POST['FTBAI_canariasnoexentoiva']));
			$mensaje=__( 'Change made successfully', 'wp-ticketbai' );
			$menutab='Avanzado';
		}
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "salvar_avanzado" && isset($_POST['FTBAI_permitefueraUE'])){
			update_option('FTBAI_permitefueraUE',sanitize_text_field($_POST['FTBAI_permitefueraUE']));
			$mensaje=__( 'Change made successfully', 'wp-ticketbai' );
			$menutab='Avanzado';
		}
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == "salvar_avanzado" && isset($_POST['FTBAI_empresaroi'])){
			update_option('FTBAI_empresaroi',sanitize_text_field($_POST['FTBAI_empresaroi']));
			$mensaje=__( 'Change made successfully', 'wp-ticketbai' );
			$menutab='Avanzado';
		}	
		if ($mensaje!=''){echo("<div class='updated message' style='padding:10px;margin:16px 4px;margin-right: 19px;'>".$mensaje."</div>");}
	}
}

//tips
if(get_option('FTBAI_DNI')=='' || get_option('FTBAI_CLAVE')==''){
	$clickregistro="FTBAI_openCity(0,'Registro');";
	echo '	<div id="FTBAI-header-upgrade-message">
				<p id="FTBAI-p-message"><span title="'.__( 'With the TicketBAI test mode activated, no issued invoice will be posted', 'wp-ticketbai' ).'" class="dashicons dashicons-info"></span>
					WP TicketBAI '.__( 'mode', 'wp-ticketbai' ).' <b style="color:red;">'.__( 'TICKETBAI TEST ENVIRONMENT ENABLED', 'wp-ticketbai' ).'</b> <a href="#" onclick="'.$clickregistro.'">'.__( 'Click here to register for free', 'wp-ticketbai' ).'</a></p>
			</div>';	
}else if(get_option('FTBAI_NOMBRE')!=''){
	$FTBAI_dias = FTBAI_dias();
	if($FTBAI_dias->dleft!=''){
		$diasleft=$FTBAI_dias->dleft;
	}else{
		$diasleft='';
	}
	echo '	<div id="FTBAI-header-ok-message">
				<p id="FTBAI-p-message"><span class="dashicons dashicons-yes-alt" style="color:#1f4d5e;"></span>
				'.get_option('FTBAI_NOMBRE').' | '.get_option('FTBAI_DNI').$diasleft.'
				</p>
			</div>';	
}
//if(is_plugin_ active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php') || is_plugin_ active('wpo_wcpdf')){
//	echo "<div class='notice notice-warning settings-error' style='border-left-color: #ff0000;padding: 10px'>ERROR: el plugin <b><u>PDF Invoices & Packing Slips for WooCommerce</u></b> esta instalado y activado. No es compatible con el plugin <b><u>WP TicketBAI Facturas</u></b>.</div>";
//}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php _e( 'WP Invoices TicketBAI', 'wp-ticketbai' ); ?></title>
<script>
	document.addEventListener("DOMContentLoaded", function() {
	  FTBAI_codeAddress();
	});
	function FTBAI_codeAddress() {
		if ('<?php echo $menutab;?>'=='Registro'){
			FTBAI_openCity(0, "Registro");
		}else if ('<?php echo $menutab;?>'=='Ajustes'){
			FTBAI_openCity(0, "Ajustes");
		}else if ('<?php echo $menutab;?>'=='Avanzado'){
			FTBAI_openCity(0, "Avanzado");			
		}else if ('<?php echo $menutab;?>'=='Facturas'){
			FTBAI_openCity(0, "Ajustes");
		}else if ('<?php echo $menutab;?>'=='Informes'){
			FTBAI_openCity(0, "Informes");
		}else{
			FTBAI_openCity(0, "Facturas");
		}
	}
	function FTBAI_openCity(evt, cityName) {
		  var i, tabcontent, tablinks;
		  tabcontent = document.getElementsByClassName("tabcontent");
		  for (i = 0; i < tabcontent.length; i++) {
			tabcontent[i].style.display = "none";
		  }
		  tablinks = document.getElementsByClassName("tablinks");
		  for (i = 0; i < tablinks.length; i++) {
			tablinks[i].className = tablinks[i].className.replace(" active", "");
		  }
		  document.getElementById(cityName).style.display = "block";
		  document.getElementById("button_"+cityName).className += " active";
		  //evt.currentTarget.className += " active";
	}
	function FTBAI_validateDNI(dni) {
		return true;
		var numero, let, letra;
		var expresion_regular_dni = /^[XYZ]?\d{5,8}[A-Z]$/;

		dni = dni.toUpperCase();

		if(expresion_regular_dni.test(dni) === true){
			numero = dni.substr(0,dni.length-1);
			numero = numero.replace('X', 0);
			numero = numero.replace('Y', 1);
			numero = numero.replace('Z', 2);
			let = dni.substr(dni.length-1, 1);
			numero = numero % 23;
			letra = 'TRWAGMYFPDXBNJZSQVHLCKET';
			letra = letra.substring(numero, numero+1);
			if (letra != let) {
				//alert('Dni erroneo, la letra del NIF no se corresponde');
				return false;
			}else{
				//alert('Dni correcto');
				return true;
			}
		}else{
			//alert('Dni erroneo, formato no válido');
			return false;
		}
	}
	function FTBAI_ValidateEmail(mail) 
	{
	 if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail))
	  {
		return (true)
	  }
		return (false)
	}
	function FTBAI_checkdni(){
		var acceso_nif = document.getElementById('FTBAI_DNI').value;
		var acceso_clave = document.getElementById('FTBAI_CLAVE').value;
		var checkdni = FTBAI_validateDNI(acceso_nif);
		if(checkdni){
			document.getElementById("FTBAI_DNI").style.color = "black";
		}else{
			document.getElementById("FTBAI_DNI").style.color = "red";
		}
		if((checkdni && acceso_clave) || (acceso_nif=='' && acceso_clave=='')){
			document.getElementById("btnvalidar").disabled = false;
		}else{
			document.getElementById("btnvalidar").disabled = true;
		}		
	}
	function FTBAI_checkregistro(){
		var client_nif = document.getElementById('FTBAI_REG_DNI').value;
		var client_email = document.getElementById('FTBAI_REG_EMAIL').value;
		var checkdni = FTBAI_validateDNI(client_nif);
		var checkemail = FTBAI_ValidateEmail(client_email);
		if (checkdni){
			document.getElementById("FTBAI_REG_DNI").style.color = "black";
		}else{
			document.getElementById("FTBAI_REG_DNI").style.color = "red";
		}
		if (checkemail){
			document.getElementById("FTBAI_REG_EMAIL").style.color = "black";
		}else{
			document.getElementById("FTBAI_REG_EMAIL").style.color = "red";
		}
		if (checkdni && checkemail){
			document.getElementById("btncontactar").disabled = false;
		}else{
			document.getElementById("btncontactar").disabled = true;
		}
	}
	function fone_toastmessage(txt,timeout,color) {
	  var x = document.getElementById("fone_snackbar");
	  x.innerHTML = txt;
	  x.className = "show";
	  if (color){x.style.backgroundColor=color;}
	  setTimeout(function(){ x.className = x.className.replace("show", ""); }, timeout);
	}	
</script>
</head>
<body>
	<div style="width:99%;margin-top:20px;">
		<div class="tab">
		  <button class="tablinks" style="width:150px;height:50px;" id="button_Facturas" onclick="FTBAI_openCity(event, 'Facturas')"><?php _e( 'Invoices', 'wp-ticketbai' ); ?></button>
		  <button class="tablinks" style="width:150px;height:50px;" id="button_Ajustes" onclick="FTBAI_openCity(event, 'Ajustes')"><?php _e( 'Settings', 'wp-ticketbai' ); ?></button>
		  <button class="tablinks" style="width:150px;height:50px;" id="button_Avanzado" onclick="FTBAI_openCity(event, 'Avanzado')"><?php _e( 'Avanced', 'wp-ticketbai' ); ?></button>			
		  <button class="tablinks" style="width:150px;height:50px;" id="button_Registro" onclick="FTBAI_openCity(event, 'Registro')"><?php _e( 'Register', 'wp-ticketbai' ); ?></button>
		  <button class="tablinks" style="width:150px;height:50px;" id="button_Informes" onclick="FTBAI_openCity(event, 'Informes')"><?php _e( 'Reports', 'wp-ticketbai' ); ?></button>
		</div>
		<div id="Facturas" class="tabcontent">
			<div>
				<form method='post'>
					<?php FTBAI_listafacturas(); ?>
				</form>
			</div>
		</div>		
		
		<div id="Ajustes" class="tabcontent">
			<?php if ($validardatos!=''){echo("<div style='background: #fff;border: 1px solid #c3c4c7;border-left-width: 4px;box-shadow: 0 1px 1px rgb(0 0 0 / 4%);border-left-color: #00a32a;padding:10px;margin:16px 4px;margin-right: 19px;'>".$validardatos."</div>");} ?>
			<div>
				<form method='post'>
					<input type="hidden" name="fact_nonce_action" value="<?php echo wp_create_nonce('fact_nonce_action');?>"/>
					<input type='hidden' name='action' value='salvaropciones'>
					<table class="form-table">
						<tr><th><?php _e( 'NIF Identification', 'wp-ticketbai' ); ?></th>
							<td>
								<input class="regular-text" type='text' placeholder="introduzca su DNI/CIF válido" name='FTBAI_DNI' id='FTBAI_DNI' onChange="FTBAI_checkdni()" onBlur="FTBAI_checkdni()" onKeyUp="FTBAI_checkdni()" value='<?=get_option('FTBAI_DNI')?>'>
							</td>
						</tr>
						<tr><th><?php _e( 'APIKey Password', 'wp-ticketbai' ); ?></th>
							<td>
								<input class="regular-text" type='text' placeholder="introduzca APIKEY" name='FTBAI_CLAVE' id='FTBAI_CLAVE' onChange="FTBAI_checkdni()" onBlur="FTBAI_checkdni()" onKeyUp="FTBAI_checkdni()" value='<?=get_option('FTBAI_CLAVE')?>'>
							</td>
						</tr>
						<tr><th></th>
							<td colspan='2'>
								<button id='btnvalidar' class="button button-primary" style="padding:11px 20px 11px 20px;" onclick = "document.getElementById('validar').submit();"><?php _e( 'VALIDATE APIKEY', 'wp-ticketbai' ); ?><br><?php _e( 'PRESENTIAL VERIFICATION', 'wp-ticketbai' ); ?></button>
							</td>
						</tr>		
					</table>
				</form>
				<?php 
					$clientefacturas=0;
					if(get_option('FTBAI_NOMBRE')!=''){
						$FTBAI_dias = FTBAI_dias();
						if($FTBAI_dias->listafac!=''){
							echo $FTBAI_dias->listafac;
							$clientefacturas=1;
						}else if($FTBAI_dias->listafac==''){
							$clientefacturas=2;
						}
					} 
				?>
			</div>
		</div>

		<div id="Avanzado" class="tabcontent">
			<div>
				<form method='post'>
					<input type="hidden" name="fact_nonce_action" value="<?php echo wp_create_nonce('fact_nonce_action');?>"/>
					<input type='hidden' name='action' value='salvar_avanzado'>
					<table class="form-table">
						<tr><th>*¹ <?php _e( 'Issue invoice when status is changed', 'wp-ticketbai' ); ?></th>
							<td>
								<select style="width:700px;max-width:700px;" onchange="this.form.submit()" name="FTBAI_emitefactautomatica" id="FTBAI_emitefactautomatica">
									<option <?php if(get_option('FTBAI_emitefactautomatica')==0){echo 'selected';} ?> value="0"><?php _e( 'No (Admin has to manually issue)', 'wp-ticketbai' ); ?></option>
									<option <?php if(get_option('FTBAI_emitefactautomatica')==1){echo 'selected';} ?> value="1"><?php _e( 'Processing or Completed (They are issued when the client makes the VISA or Cash on Delivery payment)', 'wp-ticketbai' ); ?></option>
									<option <?php if(get_option('FTBAI_emitefactautomatica')==2){echo 'selected';} ?> value="2"><?php _e( 'Completed (Issued when the administrator changes the status to Completed)', 'wp-ticketbai' ); ?></option>
									<option <?php if(get_option('FTBAI_emitefactautomatica')==3){echo 'selected';} ?> value="3"><?php echo 'Se emite la factura al llegar al estado "Completado"'; ?></option>
								</select> 
							</td>
						</tr>
						<tr><th>*² <?php _e( 'Send invoice automatically', 'wp-ticketbai' ); ?></th>
							<td>
								<select style="width:320px;" onchange="this.form.submit()" name="FTBAI_sendfactautomatica" id="FTBAI_sendfactautomatica">
									<option <?php if(get_option('FTBAI_sendfactautomatica')==0){echo 'selected';} ?> value="0"><?php _e( 'No', 'wp-ticketbai' ); ?></option>
									<option <?php if(get_option('FTBAI_sendfactautomatica')==1){echo 'selected';} ?> value="1"><?php _e( 'Yes, send email', 'wp-ticketbai' ); ?></option>
								</select> 
							</td>
						</tr>	
						<tr><th><?php _e( 'Send invoice copy to email', 'wp-ticketbai' ); ?></th>
							<td>
								<form method='post'>
									<input type="hidden" name="fact_nonce_action" value="<?php echo wp_create_nonce('fact_nonce_action');?>"/>
									<input type='hidden' name='action2' value='btnsavecopyemail'>
									<input style="width: 274px;" placeholder="<?php _e( 'enter email', 'wp-ticketbai' ); ?>" class="regular-text" type='email' name='FTBAI_copyemail' id='FTBAI_copyemail' value='<?=get_option('FTBAI_copyemail')?>'>
									<input class="button button-primary" type='submit' value='OK'>
								</form>
							</td>
						</tr>	
						<tr>
							<td colspan="2">
								<p>*¹ <?php _e( 'They are issued automatically, when a new order arrives with status PROCESSING', 'wp-ticketbai' ); ?>
								</p>
								<p>*² <?php _e( "When issuing the invoice, it is sent to the customer's email automatically. The option to canceled the invoice will not be available.", 'wp-ticketbai' ); ?>
								</p>
							</td>
						</tr>	
						<tr><th><?php _e( 'Maximum amount for Simplified Invoices', 'wp-ticketbai' ); ?></th>
							<td>
								<form method='post'>
									<input type="hidden" name="fact_nonce_action" value="<?php echo wp_create_nonce('fact_nonce_action');?>"/>
									<input type='hidden' name='action3' value='btnsavemaxsimplificada'>
									<input style="width: 274px;" placeholder="<?php _e( 'Enter amount', 'wp-ticketbai' ); ?>" class="regular-text" type="number" step="any" name='FTBAI_maxsimplificada' id='FTBAI_maxsimplificada' value='<?=get_option('FTBAI_maxsimplificada')?>'>
									<input class="button button-primary" type='submit' value='OK'>
								</form>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<p>* <?php _e( 'If the amount of the order exceeds the maximum established for Simplified Invoices, the identification of the client NIF/CIF/NIE will be required.', 'wp-ticketbai' ); ?>
								</p>
							</td>
						</tr>					
						<tr><th>*³ <?php _e( 'Show NIF/CIF/NIE fields', 'wp-ticketbai' ); ?></th>
							<td>
								<select style="width:320px;" onchange="this.form.submit()" name="FTBAI_shownif" id="FTBAI_shownif">
									<option <?php if(get_option('FTBAI_shownif')==2){echo 'selected';} ?> value="2"><?php _e( 'Always Required', 'wp-ticketbai' ); ?></option>
									<option <?php if(get_option('FTBAI_shownif')==1){echo 'selected';} ?> value="1"><?php _e( 'Yes', 'wp-ticketbai' ); ?></option>
									<option <?php if(get_option('FTBAI_shownif')==0){echo 'selected';} ?> value="0"><?php _e( 'No', 'wp-ticketbai' ); ?></option>
								</select> 
							</td>
						</tr>			
						<tr>
							<td colspan="2">
								<p>*³ <?php _e( 'If you do not show the NIF/CIF/NIE field, all invoices will be generated as SIMPLIFIED INVOICE.', 'wp-ticketbai' ); ?>
								</p>
								<p>*³ <?php _e( 'To make sales in the EU, the Canary Islands, Ceuta or Melilla you need to show NIF/CIF/NIE', 'wp-ticketbai' ); ?>
								</p>
							</td>
						</tr>	
						
						<tr><th>*³ <?php _e( 'Activar RE para los clientes', 'wp-ticketbai' ); ?></th>
							<td>
								<select style="width:320px;" onchange="this.form.submit()" name="FTBAI_clientesRE" id="FTBAI_clientesRE">
									<option <?php if(get_option('FTBAI_clientesRE')==0){echo 'selected';} ?> value="0"><?php _e( 'No', 'wp-ticketbai' ); ?></option>
									<option <?php if(get_option('FTBAI_clientesRE')==1){echo 'selected';} ?> value="1"><?php _e( 'Yes', 'wp-ticketbai' ); ?></option>
								</select> 
							</td>
						</tr>			
						<tr>
							<td colspan="2">
								<p>* <?php _e( 'Solo en el caso de necesitar emitir facturas con Recargo de Equivalencia a CLIENTES determinados. (Requiere Configuración Externa)', 'wp-ticketbai' ); ?>
								</p>
							</td>
						</tr>
						
						<tr><th>*⁴ <?php _e( 'Foreign operation', 'wp-ticketbai' ); ?></th>
							<td>
								<select style="width:320px;" onchange="this.form.submit()" name="FTBAI_operacionextranjero" id="FTBAI_operacionextranjero">
									<option <?php if(get_option('FTBAI_operacionextranjero')==0){echo 'selected';} ?> value="0"><?php _e( 'Delivery Goods (Sale of products)', 'wp-ticketbai' ); ?></option>
									<option <?php if(get_option('FTBAI_operacionextranjero')==1){echo 'selected';} ?> value="1"><?php _e( 'Provision of services (Only Spain)', 'wp-ticketbai' ); ?></option>
								</select> 
							</td>
						</tr>			
						<tr>
							<td colspan="2">
								<p>*⁴ <?php _e( 'For sales of Goods/Products in the Canary Islands, Ceuta or Melilla you must configure VAT 0 on the products of those areas within WOOCOMMERCE', 'wp-ticketbai' ); ?>
								</p>
								<p>*⁴ <?php _e( 'The provision of services is not available for the EU, the Canary Islands, Ceuta or Melilla', 'wp-ticketbai', 'wp-ticketbai' ); ?>
								</p>
							</td>
						</tr>	
							
						<tr><th><?php _e( 'Canarias / Ceuta / Melilla', 'wp-ticketbai' ); ?></th>
							<td>
								<select style="max-width: 700px;width:700px;" onchange="this.form.submit()" name="FTBAI_canariasnoexentoiva" id="FTBAI_canariasnoexentoiva">
									<option <?php if(get_option('FTBAI_canariasnoexentoiva')==0){echo 'selected';} ?> value="0"><?php _e( 'Exento de IVA (Exportación a extranjero fuera de la UE, ventas a Canarias, Ceuta y Melilla)', 'wp-ticketbai' ); ?></option>
									<option <?php if(get_option('FTBAI_canariasnoexentoiva')==1){echo 'selected';} ?> value="1"><?php _e( 'Venta con IVA', 'wp-ticketbai' ); ?></option>
								</select> 
							</td>
						</tr>
						
						<tr><th>*⁵ <?php _e( 'Permite venta fuera de UE', 'wp-ticketbai' ); ?></th>
							<td>
								<select style="max-width: 700px;width:700px;" onchange="this.form.submit()" name="FTBAI_permitefueraUE" id="FTBAI_permitefueraUE">
									<option <?php if(get_option('FTBAI_permitefueraUE')==0){echo 'selected';} ?> value="0"><?php _e( 'No permitido', 'wp-ticketbai' ); ?></option>
									<option <?php if(get_option('FTBAI_permitefueraUE')==1){echo 'selected';} ?> value="1"><?php _e( 'Venta fuera de UE permitida', 'wp-ticketbai' ); ?></option>
								</select> 
							</td>
						</tr>						
						<tr>
							<td colspan="2">
								<p>*⁵ <?php _e( 'Para la venta fuera de UE -> Asegurese que puede realizar ventas fuera de la UE segun su fiscalidad y aplicando o no los impuestos requeridos.', 'wp-ticketbai' ); ?>
								</p>
							</td>
						</tr>
						
						<tr><th><?php _e( 'Facturar TicketBAI a partir del NºPedido', 'wp-ticketbai' ); ?></th>
							<td>
								<form method='post'>
									<input type="hidden" name="fact_nonce_action" value="<?php echo wp_create_nonce('fact_nonce_action');?>"/>
									<input type='hidden' name='action4' value='btnsavenumpedido'>
									<input style="width: 274px;" placeholder="<?php _e( 'Indroduzca número pedido', 'wp-ticketbai' ); ?>" class="regular-text" type="number" step="any" name='FTBAI_apartirnumeropedido' id='FTBAI_apartirnumeropedido' value='<?=get_option('FTBAI_apartirnumeropedido')?>'>
									<input class="button button-primary" type='submit' value='OK'>
								</form>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<p>* <?php _e( 'Indique a partir de que número de pedido va a empezar a facturar con TicketBAI (0 = para empezar desde cualquier pedido)', 'wp-ticketbai' ); ?>
								</p>
							</td>
						</tr>	
						
						<?php if(1==2){ ?>
						<tr><th>*⁵ <?php _e( 'My company is registered in ROI (Intra-Community Operations)', 'wp-ticketbai' ); ?></th>
							<td>
								<select style="width:320px;" onchange="this.form.submit()" name="FTBAI_empresaroi" id="FTBAI_empresaroi">
									<option <?php if(get_option('FTBAI_empresaroi')==0){echo 'selected';} ?> value="0"><?php _e( 'No', 'wp-ticketbai' ); ?></option>
									<option <?php if(get_option('FTBAI_empresaroi')==1){echo 'selected';} ?> value="1"><?php _e( 'Yes, my company is in ROI', 'wp-ticketbai' ); ?></option>
								</select> 
							</td>
						</tr>			
						<tr>
							<td colspan="2">
								<p>*⁵ <?php _e( 'Companies registered in ROI that provide or receive services in EU states', 'wp-ticketbai' ); ?>
								</p>
							</td>
						</tr>
						<?php } ?>
                        
                        <?php if (function_exists('is_plugin_active')){
	                               if(is_plugin_active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php') || is_plugin_active('wpo_wcpdf')){ ?>
                                    <tr><th>*⁶ <?php _e('Posición QR en PDFInvoices packingslips', 'wp-ticketbai' ); ?></th>
                                        <td>
                                            <select style="width:320px;" onchange="this.form.submit()" name="FTBAI_posicionQR" id="FTBAI_posicionQR">
                                                <option <?php if(get_option('FTBAI_posicionQR')==0){echo 'selected';} ?> value="0"><?php _e( 'Pie Factura', 'wp-ticketbai' ); ?></option>
                                                <option <?php if(get_option('FTBAI_posicionQR')==1){echo 'selected';} ?> value="1"><?php _e( 'Debajo de las lineas (No se debe usar)', 'wp-ticketbai' ); ?></option>
                                            </select> 
                                        </td>
                                    </tr>	
                                    <tr>
                                        <td colspan="2">
                                            <p>*⁶ <?php _e( 'Según normativa de TicketBAI, la posición del QR debe ser en el PIE de la FACTURA.', 'wp-ticketbai' ); ?>
                                            </p>
                                        </td>
                                    </tr> 
                                    <?php } ?>
                        <?php } ?>
						
					</table>
				</form>
			</div>
		</div>		
		<div id="Registro" class="tabcontent">
			<div>
				<p><?php _e( 'Enter your details to sign up for the free trial of our WP TicketBAI plugin.', 'wp-ticketbai' ); ?><br><?php _e( 'You can also use this form to contact us.', 'wp-ticketbai' ); ?></p>
				<form method='post'>
					<input type="hidden" name="fact_nonce_action" value="<?php echo wp_create_nonce('fact_nonce_action');?>"/>
					<input type='hidden' name='action' value='registrarse'>
					<table class="form-table">
						<tr><th>* <?php _e( 'Name and surname', 'wp-ticketbai' ); ?></th>
							<td>
								<input class="regular-text" type='text' name='FTBAI_REG_NOMBRE' id='FTBAI_REG_NOMBRE' onChange="FTBAI_checkregistro()" onBlur="FTBAI_checkregistro()" onKeyUp="FTBAI_checkregistro()" value='<?=get_option('FTBAI_REG_NOMBRE')?>'>
							</td>
						</tr>
						<tr><th>* <?php _e( 'DNI/NIF', 'wp-ticketbai' ); ?></th>
							<td>
								<input class="regular-text" type='text' name='FTBAI_REG_DNI' id='FTBAI_REG_DNI' onChange="FTBAI_checkregistro()" onBlur="FTBAI_checkregistro()" onKeyUp="FTBAI_checkregistro()" value='<?=get_option('FTBAI_REG_DNI')?>'>
							</td>
						</tr>						
						<tr><th>* <?php _e( 'Phone', 'wp-ticketbai' ); ?></th>
							<td>
								<input class="regular-text" type='tel' pattern="[0-9]{9}" name='FTBAI_REG_TELEFONO' id='FTBAI_REG_TELEFONO' onChange="FTBAI_checkregistro()" onBlur="FTBAI_checkregistro()" onKeyUp="FTBAI_checkregistro()" value='<?=get_option('FTBAI_REG_TELEFONO')?>'>
							</td>
						</tr>
						<tr><th>* <?php _e( 'Email', 'wp-ticketbai' ); ?></th>
							<td>
								<input class="regular-text" type='email' name='FTBAI_REG_EMAIL' id='FTBAI_REG_EMAIL' onChange="FTBAI_checkregistro()" onBlur="FTBAI_checkregistro()" onKeyUp="FTBAI_checkregistro()" value='<?=get_option('FTBAI_REG_EMAIL')?>'>
							</td>
						</tr>		
						<tr><th><?php _e( 'Message', 'wp-ticketbai' ); ?></th>
							<td>
								<textarea placeholder="Si lo desea puede dejar un mensaje aquí." class="regular-text" name='FTBAI_REG_MENSAJE' id='FTBAI_REG_MENSAJE' onChange="FTBAI_checkregistro()" onBlur="FTBAI_checkregistro()" onKeyUp="FTBAI_checkregistro()" style="height:140px;"></textarea>
							</td>
						</tr>		
						
						<tr><th></th>
							<td colspan='2'>
								<input disabled class="button button-primary" id='btncontactar' type='submit' value='CONTACTAR'>
							</td>
						</tr>

						<?php if($clientefacturas<2){ ?>
							<tr>
								<td colspan="2">
									<p><?php echo __( 'Check more information about the WP-TBAI plugin in', 'wp-ticketbai' ).' <a href="https://wp-tbai.com/" target="_blank">WP-TBAI.COM</a>'; ?>
									</p>
									<p><?php echo __( 'If you need a complete ERP to manage your WooCommerce and TicketBai', 'wp-ticketbai' ).' <a href="https://www.facturaone.com/" target="_blank">FACTURAONE.COM</a>'; ?>
									</p>
								</td>
							</tr>
						<?php } ?>
					</table>
				</form>
			</div>			
			
		</div>

		
		<div id="Informes" class="tabcontent">
			<form method='post'>
				<input type="hidden" name="fact_nonce_action" value="<?php echo wp_create_nonce('fact_nonce_action');?>"/>
				<input type='hidden' name='action' value='verinforme'>
				<table class="form-table">
					<tr><th><?php _e( 'Exercise', 'wp-ticketbai' ); ?></th>
						<td>
							<select style="width:200px;" onchange="" name="FTBAI_informe_ejercicio" id="FTBAI_informe_ejercicio">
								<?php 
									$ejerselec = date("Y");
									if($FTBAI_informe_ejercicio>0){$ejerselec=$FTBAI_informe_ejercicio;}
								?>
								<option <?php if($ejerselec==2022){echo 'selected';} ?> value="2022">2022</option>
								<option <?php if($ejerselec==2023){echo 'selected';} ?> value="2023">2023</option>
								<option <?php if($ejerselec==2024){echo 'selected';} ?> value="2024">2024</option>
							</select> 
						</td>
					</tr>
					<tr><th><?php _e( 'Periodo', 'wp-ticketbai' ); ?></th>
						<td>
							<select style="width:200px;" onchange="" name="FTBAI_informe_trimestre" id="FTBAI_informe_trimestre">
								<option <?php if($FTBAI_informe_trimestre==0){echo 'selected';} ?> value="0"><?php _e( 'All Exercise', 'wp-ticketbai' ); ?></option>
								<option <?php if($FTBAI_informe_trimestre==1){echo 'selected';} ?> value="1">1º <?php _e( 'Trimester', 'wp-ticketbai' ); ?></option>
								<option <?php if($FTBAI_informe_trimestre==2){echo 'selected';} ?> value="2">2º <?php _e( 'Trimester', 'wp-ticketbai' ); ?></option>
								<option <?php if($FTBAI_informe_trimestre==3){echo 'selected';} ?> value="3">3º <?php _e( 'Trimester', 'wp-ticketbai' ); ?></option>
								<option <?php if($FTBAI_informe_trimestre==4){echo 'selected';} ?> value="4">4º <?php _e( 'Trimester', 'wp-ticketbai' ); ?></option>
								<option value="" disabled>
								<option value="101" <?php if ($FTBAI_informe_trimestre == '101') { ?>selected="selected"<?php } ?>><?php _e( 'January', 'wp-ticketbai' ); ?></option>
								<option value="102" <?php if ($FTBAI_informe_trimestre == '102') { ?>selected="selected"<?php } ?>><?php _e( 'February', 'wp-ticketbai' ); ?></option>
								<option value="103" <?php if ($FTBAI_informe_trimestre == '103') { ?>selected="selected"<?php } ?>><?php _e( 'March', 'wp-ticketbai' ); ?></option>
								<option value="104" <?php if ($FTBAI_informe_trimestre == '104') { ?>selected="selected"<?php } ?>><?php _e( 'April', 'wp-ticketbai' ); ?></option>
								<option value="105" <?php if ($FTBAI_informe_trimestre == '105') { ?>selected="selected"<?php } ?>><?php _e( 'May', 'wp-ticketbai' ); ?></option>
								<option value="106" <?php if ($FTBAI_informe_trimestre == '106') { ?>selected="selected"<?php } ?>><?php _e( 'June', 'wp-ticketbai' ); ?></option>
								<option value="107" <?php if ($FTBAI_informe_trimestre == '107') { ?>selected="selected"<?php } ?>><?php _e( 'July', 'wp-ticketbai' ); ?></option>
								<option value="108" <?php if ($FTBAI_informe_trimestre == '108') { ?>selected="selected"<?php } ?>><?php _e( 'August', 'wp-ticketbai' ); ?></option>
								<option value="109" <?php if ($FTBAI_informe_trimestre == '109') { ?>selected="selected"<?php } ?>><?php _e( 'September', 'wp-ticketbai' ); ?></option>
								<option value="110" <?php if ($FTBAI_informe_trimestre == '110') { ?>selected="selected"<?php } ?>><?php _e( 'Octuber', 'wp-ticketbai' ); ?></option>
								<option value="111" <?php if ($FTBAI_informe_trimestre == '111') { ?>selected="selected"<?php } ?>><?php _e( 'November', 'wp-ticketbai' ); ?></option>
								<option value="112" <?php if ($FTBAI_informe_trimestre == '112') { ?>selected="selected"<?php } ?>><?php _e( 'December', 'wp-ticketbai' ); ?></option>							
							</select> 
						</td>
					</tr>
					<tr><th></th>
						<td colspan='2'>
							<button id='btninformes' class="button button-primary" style="padding:11px 20px 11px 20px;" onclick = "document.getElementById('btninformes').submit();"><?php _e( 'GENERATE REPORT OF INVOICES ISSUED', 'wp-ticketbai' ); ?></button>
						</td>
					</tr>		
				</table>
			</form>			
			<div style="margin-top:50px;">
				<?php echo $verinforme; ?>
			</div>
		</div>			

<!--	
		<div style="margin-top:-20px;margin-left:10px;" id="version_num">
			<?php //if (get_option('FTBAI_version')){echo 'v'.get_option('FTBAI_version');} ?>
		</div>
-->
		<div id="fone_snackbar"></div>
	</div>
</body>
</html>