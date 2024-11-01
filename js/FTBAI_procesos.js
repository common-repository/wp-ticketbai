	function ftbai_anular(postid,numfac,manual){
		swal({
			  title: 'Anulación de Factura '+numfac,
			  text: "¿Seguro que quiere anular esta factura?",
			  type: 'warning',
			  showCancelButton: true,
			  confirmButtonColor: '#d33',
			 // cancelButtonColor: '#3085d6',
			  confirmButtonText: 'Anular Factura',
			  cancelButtonText: 'Cancelar'
			}).then((result) => {
				if (result.value) {
					jQuery.ajax({
						type: "POST",
						url: "",
						data: { action: 'anular', postid: postid, manual: manual }, 
						cache:false,
						timeout:0,
						success: function(data) {
							//console.log(data);
							var response = JSON.parse(data);
							if (response.success == '1'){
								location.reload();
							}else if(response.result!=''){
								swal.fire({
								  type: 'warning',
								  title: response.result,
								  html: '',
								  showConfirmButton: false,
								  //timer: 8000
								})	
							}
						} 
					});
					event.preventDefault();	
				}
			})	
	}
	function ftbai_recuperapedido(postid){
		jQuery.ajax({
			type: "POST",
			url: "",
			data: { action: 'recuperapedido', postid: postid }, 
			cache:false,
			timeout:0,
			success: function(data) {
				//console.log(data);
				var response = JSON.parse(data);
				if (response.success == '1'){
					location.reload();
				}
			} 
		});
		event.preventDefault();	
	}			
	function ftbai_cancelar(postid){
		swal({
			  title: 'Cancelar pedido #'+postid,
			  text: "¿Seguro que quiere cancelar este pedido?",
			  type: 'warning',
			  showCancelButton: true,
			  confirmButtonColor: '#d33',
			 // cancelButtonColor: '#3085d6',
			  confirmButtonText: 'Cancelar Pedido',
			  cancelButtonText: 'No'
			}).then((result) => {
				if (result.value) {
					jQuery.ajax({
						type: "POST",
						url: "",
						data: { action: 'eliminar', postid: postid }, 
						cache:false,
						timeout:0,
						success: function(data) {
							//console.log(data);
							var response = JSON.parse(data);
							if (response.success == '1'){
								location.reload();
							}
						} 
					});
					event.preventDefault();	
				}
			})	
	}		
	function ftba_sendemail(postid,numfac){
		//alert(postid);
		jQuery.ajax({
			type: "POST",
			url: "",
			data: { action: 'get_billing_email', postid: postid }, 
			cache:false,
			timeout:0,
			success: function(data) {
				var email=data;
				swal({
				  title: '¿Quiere enviar la factura al email del cliente?',
				  text: '',
				  inputValue: email,
				  //type: 'warning' 'info',
				  input: 'text',
				  showCancelButton: true,
				  //confirmButtonColor: '#d33',
				  // cancelButtonColor: '#3085d6',
				  confirmButtonText: 'Enviar Email',
				  cancelButtonText: 'Cancelar'
					}).then((result) => {
						if (result.value && result.value!='') {
							email=result.value;
							swal({
								title: 'Enviando email ... '+email,
								text: '',
								allowEscapeKey: false,
								allowOutsideClick: false,
								timer: 10000,
								onOpen: () => {
								  swal.showLoading();
								}
							  });					
							jQuery.ajax({
								type: "POST",
								url: "",
								data: { action: 'enviaremail', postid: postid, email:email }, 
								cache:false,
								timeout:0,
								success: function(data) {
									//console.log(data);
									var response = JSON.parse(data);
									if (response.success == '1'){
										//fone_toastmessage("Email enviado correctamente",3000,"black");
										location.reload();
									}else{
										fone_toastmessage("Ha ocurrido algun problema al enviar el email",3000,"#dd3333");
									}
								} 
							});
							event.preventDefault();	
						}else{
							location.reload();
						}
					})					
			} 
		});
	}
	function ftbai_emitir(postid,pruebas){
		jQuery.ajax({
			type: "POST",
			url: "",
			data: { action: 'datosfactura', postid: postid }, 
			cache:false,
			timeout:0,
			success: function(data) {
				//console.log(data);
				var response = JSON.parse(data);
				console.log(response.order_data);
				if (response.success == '1'){
					ftbai_confirmaemitir(postid,pruebas,response.datosfactura);
				}
			} 
		});
	}
	function ftbai_confirmaemitir(postid,pruebas,datosfactura){
		if(pruebas==1){
			var titulo = 'Modo de Prueba Activado';
			var texto = 'Esta factura no se emitira en entorno real. Sólo aparecerá como facturada en el entorno de pruebas.';
			var tiposw = 'info';
		}else{
			var titulo = 'Emitir factura del pedido #'+postid;
			var texto = '¿Esta seguro que quiere emitir esta factura? Al emitir la factura, se enviará automáticamente a su administración foral correspondiente.';
			var tiposw = 'warning';
		}
		swal({
			  title: titulo,
			  html: `<div style="margin-left:50px;margin-right:30px;margin-top:15px;text-align:left;">`+datosfactura+`</div>
					 <div style="margin:10px;margin-top:20px;">`+texto+`</div>`,
			  type: tiposw,
			  showCancelButton: true,
			  confirmButtonColor: '#d33',
			 // cancelButtonColor: '#3085d6',
			  confirmButtonText: 'Emitir Factura',
			  cancelButtonText: 'Cancelar'
			}).then((result) => {
				if (result.value) {
					swal({
						title: 'Emitiendo la factura ...',
						allowEscapeKey: false,
						allowOutsideClick: false,
						timer: 10000,
						onOpen: () => {
						  swal.showLoading();
						}
					  });
					jQuery.ajax({
						type: "POST",
						url: "",
						data: { action: 'emitir', postid: postid }, 
						cache:false,
						timeout:0,
						success: function(data) {
							//console.log(data);
							var response = JSON.parse(data);
							if (response.success == '1'){
								if(response.enviaremail==0){
									ftba_sendemail(postid,0);								
								}else{
									location.reload();
								}
							}else{
								let mensaje = response.resultado;
								if(mensaje.includes("Error") && mensaje.includes("CIF/NIF/NIE")){
									swal({
									  title: 'Error en DNI/NIF del cliente',
									  text: "No puede emitir una factura a un cliente que no tenga DNI/NIF o sea erróneo. Entre en el pedido #"+postid+" y introduzca el DNI/NIF correctamente",
									  type: 'warning',
									  showCancelButton: false,
									  confirmButtonColor: '#d33',
									  //cancelButtonColor: '#3085d6',
									  confirmButtonText: 'Cancelar',
									  //cancelButtonText: 'Cancelar'
									}).then((result) => {
										if (result.value) {
											event.preventDefault();	
										}
									})	
								}else{
									if(mensaje){var txt=mensaje;}else{var txt="Error en datos de factura";}
									swal.fire({
									  type: 'warning',
									  title: txt,
									  html: '',
									  showConfirmButton: false,
									  //timer: 4000
									})	
								}
							}
						} 
					});
					event.preventDefault();	
				}
			})			
	}
	
	function ftbai_addfactura(){
		swal({
		  title: 'Introduzca el número del pedido para crear la factura al mismo destinatario',
		  text: "",
		  //type: 'warning',
		  input: 'text',
		  showCancelButton: true,
		  //confirmButtonColor: '#d33',
		  // cancelButtonColor: '#3085d6',
		  confirmButtonText: 'Aceptar',
		  cancelButtonText: 'Cancelar'
			}).then((result) => {
				if (result.value && result.value>0) {
					jQuery.ajax({
						type: "POST",
						url: "",
						data: { action: 'datosfactura', postid: result.value }, 
						cache:false,
						timeout:0,
						success: function(data) {
							//console.log(data);
							var response = JSON.parse(data);
							if (response.success == '1'){
								ftbai_creafacmanual(result.value,response.datosfactura);
							}
						} 
					});					
					event.preventDefault();	
				}
			})			
	}
	function ftbai_recalculafac(){
		var total = 0
		var lineas = 3;
		for(var i = 0; i < lineas; i++){
			var ftbaiproducto = document.getElementById("ftbaiproducto"+(i+1))
			var ftbaiprecio = document.getElementById("ftbaiprecio"+(i+1))
			var ftbaiiva = document.getElementById("ftbaiiva"+(i+1));
			if (ftbaiproducto.value && ftbai_isNumeric(ftbaiprecio.value) && ftbai_isNumeric(ftbaiiva.value)){
				total = total + (parseFloat(ftbaiprecio.value)+(parseFloat(ftbaiprecio.value)*parseFloat(ftbaiiva.value))/100)
				ftbaiproducto.style.backgroundColor = "#e0ffff";
				ftbaiprecio.style.backgroundColor = "#e0ffff";
				ftbaiiva.style.backgroundColor = "#e0ffff";
			}else{
				ftbaiproducto.style.backgroundColor = "#ffcccc";
				ftbaiprecio.style.backgroundColor = "#ffcccc";
				ftbaiiva.style.backgroundColor = "#ffcccc";
			}			
		}
		if(total!=0){
			document.getElementById("ftbai_faclintotal").innerHTML = "Se emitirá una factura con un TOTAL: "+total.toFixed(2)+" €";	
		}else{
			document.getElementById("ftbai_faclintotal").innerHTML = "No puede emitir una factura sin importes o producto";	
		}
	}
	function ftbai_isNumeric(str) {
	  if (typeof str != "string") return false // we only process strings!  
	  return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
			 !isNaN(parseFloat(str)) // ...and ensure strings of whitespace fail
	}
	function ftbai_creafacmanual(postid,datosfactura){
		swal({
			  title:'Emitir factura manual',
			  html:`<div style="margin-left:30px;margin-right:30px;margin-top:15px;text-align:left;">`+datosfactura+`</div>
					<table width="80%" border="0" style="padding:30px;">
					  <tbody style="text-align:left;">
						<tr style="line-height:10px;">
						  <td>Producto&nbsp;</td>
						  <td>Precio&nbsp;</td>
						  <td>IVA&nbsp;</td>
						</tr>
						<tr style="line-height: 0px;">
						  <td><input type="text" id="ftbaiproducto1" name="ftbaiproducto1" onkeyup="ftbai_recalculafac()" onchange="ftbai_recalculafac()" style="background-color:#ffcccc;width:245px;">&nbsp;</td>
						  <td><input type="number" id="ftbaiprecio1" name="ftbaiprecio1" onkeyup="ftbai_recalculafac()" onchange="ftbai_recalculafac()" style="width:110px;background-color:#ffcccc;">&nbsp;</td>
						  <td>	<select name="ftbaiiva1" id="ftbaiiva1" onchange="ftbai_recalculafac()" style="background-color:#ffcccc;">
								  <option value="">Seleccione Iva</option>
								  <option value="21">IVA 21%</option>
								  <option value="10">IVA 10%</option>
								  <option value="5">IVA 5%</option>
								  <option value="4">IVA 4%</option>
								</select>
						  </td>
						</tr>
						<tr style="line-height: 0px;">
						  <td><input type="text" id="ftbaiproducto2" name="ftbaiproducto2" onkeyup="ftbai_recalculafac()" onchange="ftbai_recalculafac()" style="background-color:#ffcccc;width:245px;">&nbsp;</td>
						  <td><input type="number" id="ftbaiprecio2" name="ftbaiprecio2" onkeyup="ftbai_recalculafac()" onchange="ftbai_recalculafac()" style="width:110px;background-color:#ffcccc;">&nbsp;</td>
						  <td>	<select name="ftbaiiva2" id="ftbaiiva2" onchange="ftbai_recalculafac()" style="background-color:#ffcccc;">
								  <option value="">Seleccione Iva</option>
								  <option value="21">IVA 21%</option>
								  <option value="10">IVA 10%</option>
								  <option value="5">IVA 5%</option>
								  <option value="4">IVA 4%</option>
								</select>
						  </td>
						</tr>
						<tr style="line-height: 0px;">
						  <td><input type="text" id="ftbaiproducto3" name="ftbaiproducto3" onkeyup="ftbai_recalculafac()" onchange="ftbai_recalculafac()" style="background-color:#ffcccc;width:245px;">&nbsp;</td>
						  <td><input type="number" id="ftbaiprecio3" name="ftbaiprecio3" onkeyup="ftbai_recalculafac()" onchange="ftbai_recalculafac()" style="width:110px;background-color:#ffcccc;">&nbsp;</td>
						  <td>	<select name="ftbaiiva3" id="ftbaiiva3" onchange="ftbai_recalculafac()" style="background-color:#ffcccc;">
								  <option value="">Seleccione Iva</option>
								  <option value="21">IVA 21%</option>
								  <option value="10">IVA 10%</option>
								  <option value="5">IVA 5%</option>
								  <option value="4">IVA 4%</option>
								</select>
						  </td>
						</tr>
					  </tbody>
					</table>
					<div id="ftbai_faclintotal" style="margin-left:40px;margin-top:30px;text-align:left;font-weight:bold;">No puede emitir una factura sin importes</div>
				`,
			  type: '',
			  width: 600,
			  showCancelButton: true,
			  confirmButtonColor: '#d33',
			 // cancelButtonColor: '#3085d6',
			  confirmButtonText: 'Emitir Factura',
			  cancelButtonText: 'Cancelar'
			}).then((result) => {
				if(result.dismiss=='cancel'){return;}
				if (result.value) {
					var total = 0;
					var arrlineas = [];
					var lineas = 3;
					for(var i = 0; i < lineas; i++){
						var ftbaiproducto = document.getElementById("ftbaiproducto"+(i+1))
						var ftbaiprecio = document.getElementById("ftbaiprecio"+(i+1))
						var ftbaiiva = document.getElementById("ftbaiiva"+(i+1));
						if (ftbaiproducto.value && ftbai_isNumeric(ftbaiprecio.value) && ftbai_isNumeric(ftbaiiva.value)){
							total = total + (parseFloat(ftbaiprecio.value)+(parseFloat(ftbaiprecio.value)*parseFloat(ftbaiiva.value))/100)
							var obj = new Object();
							obj.producto = ftbaiproducto.value;
							obj.precio = ftbaiprecio.value;
							obj.iva = ftbaiiva.value;
							arrlineas.push(obj);
						}			
					}
					if(total==0){
						fone_toastmessage("No puede crear una factura sin productos/importes",3000,"#dd3333");
						return;
					}
					//console.log(arrlineas);
					//console.log(JSON.stringify(arrlineas));
					jQuery.ajax({
						type: "POST",
						url: "",
						data: {action:'creafacmanual', postid:postid, total:total, arrlineas:ftbai_utf8_to_b64(JSON.stringify(arrlineas))},
						cache:false,
						timeout:0,
						success: function(data) {
							//console.log(data);
							var response = JSON.parse(data);
							if (response.success == '1'){
								location.reload();
							}
						} 
					});
					event.preventDefault();					
				}
			})			
	}
	function ftbai_utf8_to_b64(str) {
	  return window.btoa(unescape(encodeURIComponent(str)));
	}

	function ftbai_rectificativa(postid,numfac){
		jQuery.ajax({
			type: "POST",
			url: "",
			data: { action: 'rectificativa', postid: postid }, 
			cache:false,
			timeout:0,
			success: function(data) {
				//console.log(data);
				var response = JSON.parse(data);
				if (response.success == '1'){
					console.log(response.datosfactura);
					console.log(response.detalles);
					ftbai_confirmarectificativa(postid,response);
				}
			} 
		});
	}
	function ftbai_totalproductos(){
		var total = 0
		var checkboxes = document.querySelectorAll('input[type=checkbox]:checked')
		for (var i = 0; i < checkboxes.length; i++) {
			total=total+parseFloat(checkboxes[i].value);
		}
		total = parseFloat(total*-1).toFixed(2);
		if(total!=0){
			document.getElementById("ftbai_lintotal").innerHTML = "Se emitirá una Rectificativa con un TOTAL: "+total+" €";	
		}else{
			document.getElementById("ftbai_lintotal").innerHTML = "No puede emitir una rectificativa sin importes a abonar";	
		}
	}
	function ftbai_confirmarectificativa(postid,response){
		var datosfactura = response.datosfactura;
		var detalles = response.detalles;
		var numfac = response.numfac;
		var linea = "";
		detalles.forEach(function(numero) {
			linea = linea + `
			<div class="form-check" style="display:flex;margin-left:35px;margin-top:5px;margin-bottom:0px;">
				<div style="margin-top:0px;"><input type="checkbox" onclick="ftbai_totalproductos()" name="`+numero.item_id+`" value="`+numero.total+`" id="`+numero.item_id+`" unchecked></div>
				<label for="`+numero.item_id+`" style="text-align:left;margin-left:5px;">`+numero.nombre+` `+numero.cantidad+` x `+numero.precio_unidad+`</label>
			</div>`;
		})
		var titulo = 'Emitir factura rectificativa de la factura número '+numfac;
		var texto = '¿Esta seguro que quiere emitir esta factura rectificativa?';
		var tiposw = 'warning';
		swal({
			  title: titulo,
			  html: `<div style="margin-left:30px;margin-right:30px;margin-top:15px;margin-bottom:20px;text-align:left;">`+datosfactura+`</div>
					 `+linea+`
					 <div style="margin-left:40px;margin-top:5px;text-align:left;font-size:13px;">* Marque las lineas que quiere abonar</div>	
					 <div id="ftbai_lintotal" style="margin-left:40px;margin-top:30px;text-align:left;font-weight:bold;color:#dd3333">No puede emitir una rectificativa sin importes a abonar</div>
					 
					 <div style="margin:5px;margin-top:30px;text-align:left;margin-left:20px;font-size:21px;">`+texto+`</div>
					 <div style="margin-left:24px;margin-top:5px;text-align:left;font-size:13px;">* Al emitir la rectificativa no podrá realizar ningun cambio o volver a emitir otra rectificativa</div>
				`,
			  type: tiposw,
			  width: 600,
			  showCancelButton: true,
			  confirmButtonColor: '#d33',
			 // cancelButtonColor: '#3085d6',
			  confirmButtonText: 'Emitir Factura',
			  cancelButtonText: 'Cancelar'
			}).then((result) => {
				if(result.dismiss=='cancel'){return;}
				if (result.value) {
					var total = 0;
					var arrlineas = [];
					var checkboxes = document.querySelectorAll('input[type=checkbox]:checked')
					for (var i = 0; i < checkboxes.length; i++) {
						total=total+parseFloat(checkboxes[i].value);
						arrlineas.push(parseInt(checkboxes[i].name));
					}
					if(total==0){
						fone_toastmessage("No puede crear una rectificativa sin productos/importes",3000,"#dd3333");
						return;
					}
					//console.log(JSON.stringify(arrlineas));
					jQuery.ajax({
						type: "POST",
						url: "",
						data: { action: 'crearectificativa', postid: postid, total: total, arrlineas: JSON.stringify(arrlineas) },
						cache:false,
						timeout:0,
						success: function(data) {
							//console.log(data);
							var response = JSON.parse(data);
							if (response.success == '1'){
								location.reload();
							}
						} 
					});
					event.preventDefault();						
				}
			})			
	}