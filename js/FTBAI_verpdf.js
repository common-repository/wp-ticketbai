	function ftba_verpdf(postid){
		swal({
			title: 'Descargando factura ...',
			allowEscapeKey: false,
			allowOutsideClick: false,
			timer: 2000,
			onOpen: () => {
			  swal.showLoading();
			}
		  });
		jQuery.ajax({
			type: "POST",
			url: "",
			data: { action: 'verpdf', postid: postid }, 
			cache:false,
			timeout:0,
			success: function(data) {
				var response = JSON.parse(data);
				if (response.success == '1'){
					var basepdf = response.basepdf;
					const d = new Date();
					let time = d.getTime();
					ftba_swaliframe(response.urlpdf+'?'+time);
					jQuery.ajax({
						type: "POST",
						url: "",
						data: { action: 'delpdf', basepdf: basepdf },
						cache:false,
						timeout:0,
						success: function(data) {} 
					});					
				}
			} 
		});
		event.preventDefault();			
	}
	function ftba_verxml(postid){
		swal({
			title: 'Descargando XML ...',
			allowEscapeKey: false,
			allowOutsideClick: false,
			timer: 2000,
			onOpen: () => {
			  swal.showLoading();
			}
		  });
		jQuery.ajax({
			type: "POST",
			url: "",
			data: { action: 'verxml', postid: postid }, 
			cache:false,
			timeout:0,
			success: function(data) {
				var response = JSON.parse(data);
				if (response.success == '1'){
					ftba_downloadURI(response.urlxml,response.namefile+'.xml');
				}
			} 
		});
		event.preventDefault();
	}
	function ftba_downloadURI(uri, name) 
	{
		var link = document.createElement("a");
		// If you don't know the name or want to use
		// the webserver default set name = ''
		link.setAttribute('download', name);
		link.href = uri;
		document.body.appendChild(link);
		link.click();
		link.remove();
	}
	function ftba_swaliframe(urliframe){
		var heightiframe = window.innerHeight-100;
		swal({
			width: '65%',
			title: "",
			html: '<iframe id="swaliframe" width="100%" src="'+urliframe+'" frameborder="0" allowfullscreen style="border-radius:7px;height:'+heightiframe+'px;box-shadow: 4px 4px 4px rgb(0 0 0 / 22%);"></iframe>',
			type: "",
			showCloseButton: true,
			showCancelButton: false,
			showConfirmButton: false,
			focusConfirm: false,
			confirmButtonText: 'Cerrar',
			customClass: 'swaliframe',
			//animation: false
			onClose: () => {
				swal.close();
			}
		});
	}
	function ftba_swap(txt){
		swal({
		  title: '',
		  text: txt,
		  type: 'info',
		  showCancelButton: false,
		  confirmButtonText: 'Aceptar',
		})
	}