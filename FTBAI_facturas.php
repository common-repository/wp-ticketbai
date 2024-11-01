<?php if(!is_admin() || strpos( $_SERVER['REQUEST_URI'], 'admin.php?page=WPTicketBAI') === false) { return; } ?>
<?php 
	$pdfinvoices=0;
	if (function_exists('is_plugin_active')){	
		if(is_plugin_active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php') || is_plugin_active('wpo_wcpdf')){
			$pdfinvoices=1;
		}
	}
	if( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}
	class FTBAI_List_invoices_Table extends WP_List_Table {
		public function __construct(){
			global $status, $page;
				parent::__construct( array(
					'singular'  => __( 'book', 'wp-ticketbai' ),     //singular name of the listed records
					'plural'    => __( 'books', 'wp-ticketbai' ),   //plural name of the listed records
					'ajax'      => false        //does this table support ajax?
			) );
		}
		function no_items () { 
		  _e('No invoices','wp-ticketbai');
		} 
		protected function get_views() { 
			$status_links = array(
				"Todas"       	=> "<a href='admin.php?page=WPTicketBAI'>".__('All','wp-ticketbai')."</a>",
				"Emitidas"      => "<a href='admin.php?page=WPTicketBAI&status=Emitida'>".__('Issued','wp-ticketbai')."</a>",
				"Pendientes"    => "<a href='admin.php?page=WPTicketBAI&status=Pendiente'>".__('Pending','wp-ticketbai')."</a>",
				"Anuladas"      => "<a href='admin.php?page=WPTicketBAI&status=Anulada'>".__('Canceled','wp-ticketbai')."</a>",
				"Completas"		=> "<a href='admin.php?page=WPTicketBAI&tipo=COMPLETA'>".__('Complete','wp-ticketbai')."</a>",
				"Simplificadas" => "<a href='admin.php?page=WPTicketBAI&tipo=SIMPLIFICADA'>".__('Simplified','wp-ticketbai')."</a>",
				"Rectificativas"=> "<a href='admin.php?page=WPTicketBAI&tipo=RECTIFICATIVA'>".__('Rectifications','wp-ticketbai')."</a>",
			);
			return $status_links;
		}	
		function column_default( $item, $column_name ) {
			$btn = "";
			$res = "";
			$verpdfcmd = "ftba_verpdf('".$item['pedido_numero']."');event.preventDefault();";
			$verxmlcmd = "ftba_verxml('".$item['pedido_numero']."');event.preventDefault();";
			$sendemailcmd = "ftba_sendemail('".$item['pedido_numero']."');event.preventDefault();";
			global $wpdb;
			$user_nif = get_option('FTBAI_DNI');
			$tbaicode = get_option('FTBAI_CLAVE');
			if($user_nif=='' || $tbaicode==''){ 
				//en demo no envia email
				$sendemailcmd = "ftba_swap('No puede enviar la factura por email en la version demostración');event.preventDefault();";
			}
			$numfac = "'".$item['factura']."'";
			//si tiene tbai es emitida
			if($item['factura_estado']=='Anulada'){
				$factura_estado=$item['factura_estado'];
			}else{
				if($item['tbai_num']!=''){$factura_estado='Emitida';}else{$factura_estado=$item['factura_estado'];}	
			}
			switch( $column_name ) { 
				case 'factura':
					$btn.= '<div style="display:inline-flex;">';
					//factura qr y num
					if($factura_estado=='Pendiente' || $factura_estado=='Pedido Cancelado'){
						$btn.= '<img loading="lazy" src="'.plugins_url( 'assets/nofac.png', __FILE__ ).'" style="opacity:.5;width:180px;">';
					}else{
						$tbai_num=$item['tbai_num'];
						$tbai_qr=$item['tbai_qr'];
						$verqrcmd = "ftba_swaliframe('".$tbai_qr."');event.preventDefault();";
						$factura_rectificativa_de = $item['factura_rectificativa_de'];
						if( $factura_rectificativa_de>0 && get_post_status($item['pedido_numero'])!='wc-facmanual' ){$colornum='background:#fb3f3f;';}else{$colornum='background:#2a91e5;';}
						if($item['factura_numero']!='' && $tbai_num!='' && $tbai_qr!=''){
							$btn.= '<a title="'.$tbai_num.'" href="#" onclick="'.$verqrcmd.'" target="_blank"><img loading="lazy" height="40" src="'.plugins_url( 'assets/tbai.png', __FILE__ ).'"></a>';
							$btn.= '<button title="'.__('Download Invoice','wp-ticketbai').'" class="button-primary" style="margin-left:5px;'.$colornum.'color:#fff;border-width:0px;padding:6px 7px 6px 7px;width:135px;" onclick="'.$verpdfcmd.'"><span style="margin-top:5px;margin-left:-5px" class="dashicons dashicons-download"></span> '.$item[ $column_name ].'</button>';
						}
					}
					//estado
					if($factura_estado=='Anulada'){
						$btn.= "<button class='button-primary' style='pointer-events:none;background:#f96565;color:#fff;border-width:0px;padding: 3px 5px 3px 5px;width:105px;margin-left:5px;' onclick='event.preventDefault()'>".__('Canceled','wp-ticketbai')."</button>";
					}else if($factura_estado=='Pedido Cancelado'){
						$btn.= "<button class='button-primary' style='pointer-events:none;background:#e5e5e5;color:#777;border-width:0px;padding: 3px 5px 3px 5px;width:105px;margin-left:5px;white-space:normal;line-height:15px;' onclick='event.preventDefault()'>".__('Cancel Order','wp-ticketbai')."</button>";
					}else if($factura_estado=='Pendiente'){
						$btn.= "<button class='button-primary' style='pointer-events:none;background:#f8dda7;color:#94660c;border-width:0px;padding: 3px 5px 3px 5px;width:105px;margin-left:5px;' onclick='event.preventDefault()'>".__('Pending','wp-ticketbai')."</button>";
					}else if($factura_estado=='Emitida'){
						$btn.= "<button class='button-primary' style='pointer-events:none;background:#359738;color:#fff;border-width:0px;padding: 3px 5px 3px 5px;width:105px;margin-left:5px;' onclick='event.preventDefault()'>".__('Issued','wp-ticketbai')."</button>";
						if($item['factura_rectificada_en']==0 && $item['factura_rectificativa_de']==0){
						}
					}
					$btn.='</div>';
					//acciones
					if($factura_estado=='Anulada' || $item['factura_rectificada_en']>0){
						$btn.='<div style="width:300px;"><div style="float:right;margin-right:16px;margin-top:8px;">';
						$btn.='<button type="button" class="button-link editinline" onclick="'.$verpdfcmd.'">'.__('VIEW','wp-ticketbai').'</button> ';
						//if($factura_estado!='Anulada'){
							$btn.=' | <button type="button" title="'.__('SEND EMAIL','wp-ticketbai').'" class="button-link editinline" onclick="'.$sendemailcmd.'">'.__('SEND','wp-ticketbai').'</button>';
						//}
						$btn.='</div></div>';
					}else if($factura_estado=='Pedido Cancelado'){
						$btn.='<div style="width:300px;"><div style="float:right;margin-right:16px;margin-top:8px;">';
						$btn.='<button type="button" class="button-link editinline" onclick="ftbai_recuperapedido('.$item['pedido_numero'].')">'.__('Retrieve Order','wp-ticketbai').'</button>';
						$btn.='</div></div>';
					}else if($factura_estado=='Pendiente'){
						$btn.='<div style="width:300px;"><div style="float:right;margin-right:16px;margin-top:8px;">';
						$btn.='<button type="button" class="button-link editinline" onclick="ftbai_cancelar('.$item['pedido_numero'].')">'.__('Cancel Order','wp-ticketbai').'</button> | ';
						$btn.='<button type="button" class="button-link editinline"  onclick="ftbai_emitir('.$item['pedido_numero'].','.$item['modoprueba'].')">'.__('Generate Invoice','wp-ticketbai').'</button>';
						$btn.='</div></div>';
					}else if($factura_estado=='Emitida' && $item['factura_rectificada_en']==0){
						$btn.='<div style="width:300px;"><div style="float:right;margin-right:16px;margin-top:8px;">';
						if( $item['factura_rectificativa_de']==0 || get_post_status($item['pedido_numero'])=='wc-facmanual' ){
							if (get_post_status($item['pedido_numero'])!='wc-facmanual'){
								$btn.='<button type="button" class="button-link editinline" style="color:#950606;" onclick="ftbai_rectificativa('.$item['pedido_numero'].','.$numfac.')">'.__('Create Corrective','wp-ticketbai').'</button> | ';
							}
							//$fechafactura = date("Y-m-d", strtotime($item['fechafactura']) );
							//if ($fechafactura > '2022-11-02' && $GLOBALS["pdfinvoices"]==0) {
								//en el caso de enviada por email impide anular factura
								//if ($item['emailenviado']=='0000-00-00 00:00:00'){
									if(get_post_status($item['pedido_numero'])=='wc-facmanual'){$manual=1;}else{$manual=0;}
									$btn.='<button type="button" class="button-link editinline" style="color:#950606;" onclick="ftbai_anular('.$item['pedido_numero'].','.$numfac.','.$manual.')">'.__('Cancel Invoice','wp-ticketbai').'</button> | ';
								//}
							//}
						}
						$btn.='<button type="button" class="button-link editinline" onclick="'.$verpdfcmd.'">'.__('VIEW','wp-ticketbai').'</button> | ';
						$btn.='<button type="button" class="button-link editinline" onclick="'.$verxmlcmd.'">'.__('XML','wp-ticketbai').'</button> | ';
						$btn.='<button type="button" title="'.__('SEND EMAIL','wp-ticketbai').'" class="button-link editinline" onclick="'.$sendemailcmd.'">'.__('SEND','wp-ticketbai').'</button>';
						$btn.='</div></div>';						
					}
					return $btn;
					break;
				case 'emailenviado':
					if ($item[ $column_name ]!='0000-00-00 00:00:00') {
						$res.=strftime("%d-%m-%Y", strtotime($item[ $column_name ]));
						$res.='<br>'.strftime("%H:%M", strtotime($item[ $column_name ]));
					}else{
						$res.='---';
					}
					return $res;
					break;
				case 'fechafactura':
					$res.=strftime("%e %B, %Y", strtotime($item[ $column_name ]));
					$res.='<br>'.strftime("%H:%M", strtotime($item[ $column_name ]));
					return $res;
					break;
				case 'tipo':
					if($factura_estado=='Pendiente' || $factura_estado=='Pedido Cancelado'){
					}else if(get_post_status($item['pedido_numero'])=='wc-facmanual'){
						return $item[ $column_name ];
					} else if($item['factura_rectificativa_de']>0){
						$factura_rectificativa_de = $item['factura_rectificativa_de'];
						$results = FTBAI_getfield_facturas($factura_rectificativa_de);
						$numero = $results->factura_serie.' / '.$results->factura_numero;
						return __('Corrective Of:','wp-ticketbai').' <b style="color:#950606;">'.$numero.'</b>';
					}else if($item['factura_rectificada_en']>0){
						$factura_rectificada_en = $item['factura_rectificada_en'];
						$results = FTBAI_getfield_facturas($factura_rectificada_en);
						$numero = $results->factura_serie.' / '.$results->factura_numero;
						return 'Completa <b style="color:#950606;">'.__('Rectified in:','wp-ticketbai').' '.$numero.'</b>';
					}else{
						return $item[ $column_name ];
					}
					break;
				case 'pedido_numero':
					if (get_post_status($item['pedido_numero'])=='wc-facmanual'){
						$btn.= '<a><b>Factura Manual</b> <br>'.$item['cliente_nombre'].' '.$item['cliente_nif'].'</a>';
					}else if ($item['factura_rectificativa_de']>0){
					}else{
						$btn.= '<a href="'.get_edit_post_link($item[ $column_name ]).'"><b>#'.$item[ $column_name ].'</b> <br>'.$item['cliente_nombre'].' '.$item['cliente_nif'].'</a>';
					}
					return $btn;
					break;
				case 'wc_actions':
					if($item['factura_rectificativa_de']>0){break;}

					$this->disable_storing_document_settings();
					$meta_box_actions = array();
					$documents = WPO_WCPDF()->documents->get_documents();
					$order = wc_get_order( $item['pedido_numero'] );
					foreach ( $documents as $document ) {
						$document_title = $document->get_title();
						if ( $document = wcpdf_get_document( $document->get_type(), $order ) ) {
							$pdf_url        = WPO_WCPDF()->endpoint->get_document_link( $order, $document->get_type() );
							$document_title = is_callable( array( $document, 'get_title' ) ) ? $document->get_title() : $document_title;
							$meta_box_actions[$document->get_type()] = array(
								'url'		=> esc_url( $pdf_url ),
								'alt'		=> "PDF " . $document_title,
								'title'		=> "PDF " . $document_title,
								'exists'	=> is_callable( array( $document, 'exists' ) ) ? $document->exists() : false,
							);
						}
					}
					$meta_box_actions = apply_filters( 'wpo_wcpdf_meta_box_actions', $meta_box_actions, $item['pedido_numero'] );
						foreach ($meta_box_actions as $document_type => $data) {
							$data['class'] = ( isset( $data['exists'] ) && $data['exists'] == true ) ? 'exists' : '';
							if ($document_type!='packing-slip'){
								if($item['tbai_num']!=''){
									echo '<a href="'.$data['url'].'" class="button tips wpo_wcpdf exists invoice" style="padding: 4px 5px 0px 5px;line-height: 0px;min-height: 27px !important;margin-right:3px;" target="_blank" alt="'.$data['alt'].'" title="'.$data['title'].'"><img src="'.plugins_url( 'assets/invoice.svg', __FILE__ ).'" alt="'.$data['alt'].'" width="16"></a>';
								}
							}else{
								echo '<a href="'.$data['url'].'" class="button tips wpo_wcpdf packing-slip" style="padding: 4px 5px 0px 5px;line-height: 0px;min-height: 27px !important;" target="_blank" alt="'.$data['alt'].'" title="'.$data['title'].'"><img src="'.plugins_url( 'assets/packing-slip.svg', __FILE__ ).'" alt="'.$data['alt'].'" width="16"></a>';
							}
						}
					break;
					
				case 'factura_total':
					if($item[ $column_name ]<0){
						return '<div style="color:#950606;">'.wc_price($item[ $column_name ]).'</div>';
					}else{
						return wc_price($item[ $column_name ]);
					}
					break;
				default:
					//return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
			}
		}
		function get_columns(){
			$columns = array(
				'factura'    		=> '<div style="min-width:250px;">'.__( 'TbaiQR | Invoice PDF | Status', 'wp-ticketbai' ).'</div>',
				'emailenviado' 		=> __( 'Date Email Sent', 'wp-ticketbai' ),
				'fechafactura' 		=> __( 'Date Invoice', 'wp-ticketbai' ),
				'tipo' 				=> __( 'Type', 'wp-ticketbai' ),
				'factura_total' 	=> __( 'Total', 'wp-ticketbai' ),
				'pedido_numero'   	=> __( 'Order', 'wp-ticketbai' ),
			);
			if (function_exists('is_plugin_active')){
				if(is_plugin_active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php') || is_plugin_active('wpo_wcpdf')){
					$columns = array(
						'factura'    		=> '<div style="min-width:250px;">'.__( 'TbaiQR | Invoice PDF | Status', 'wp-ticketbai' ).'</div>',
						'emailenviado' 		=> __( 'Date Email Sent', 'wp-ticketbai' ),
						'fechafactura' 		=> __( 'Date Invoice', 'wp-ticketbai' ),
						'tipo' 				=> __( 'Type', 'wp-ticketbai' ),
						'factura_total' 	=> __( 'Total', 'wp-ticketbai' ),
						'pedido_numero'   	=> __( 'Order', 'wp-ticketbai' ),
						'wc_actions'   		=> __( 'Actions', 'wp-ticketbai' ),
					);
				}
			}
			return $columns;
		}
		function get_table_classes() {
			$mode = get_user_setting( 'posts_list_mode', 'list' );

			$mode_class = esc_attr( 'table-view-' . $mode );

			return array( 'widefat', 'fixed', 'striped', $mode_class, $this->_args['plural'] );
		}
		public function get_hidden_columns()
		{
			return array();
		}
		public function get_sortable_columns()
		{
			$sortable_columns = array(
				  'emailenviado'	=> array('emailenviado', false),
                  'fechafactura'  	=> array('fechafactura', false),
                  'factura_total' 	=> array('factura_total', false),
                  'factura'   		=> array('factura', true),
				  'pedido_numero' 	=> array('pedido_numero', true)
            );
            return $sortable_columns;
		}
		// Sorting function
      	function usort_reorder($a, $b)
      	{
			if( isset( $_GET['orderby'] ) ){$orderby = sanitize_text_field($_GET['orderby']);}else{$orderby="";}
			if( isset( $_GET['order'] ) ){$order = sanitize_text_field($_GET['order']);}else{$order="";}
			// If no sort, default to user_login
			//$orderby = (!empty(sanitize_text_field($orderby))) ? sanitize_text_field($orderby) : 'pedido_numero';
			$orderby = (!empty(sanitize_text_field($orderby))) ? sanitize_text_field($orderby) : 'fechafactura';
			// If no order, default to asc
			$order = (!empty(sanitize_text_field($order))) ? sanitize_text_field($order) : 'desc';
			// Determine sort order
			$result = strcmp($a[$orderby], $b[$orderby]);
			// Send final sort direction to usort
			return ($order === 'asc') ? $result : -$result;
      	}		
	  	function prepare_items() {
			$where="";
			$wheredate="";
			$wheresearch="";
			if (!empty($_REQUEST['m'])) {
				$search = sanitize_text_field($_REQUEST['m']);
				$year = substr($search,0,4);
				$month = substr($search,4,5);
				$wheredate='';
				if(!empty($year)){
					$wheredate.= ' And YEAR(factura_fecha)="' . $year . '"';
				}
				if(!empty($month)){
					$wheredate.= ' And MONTH(factura_fecha)="' . $month . '"';
				}
			}			
			
			$search = ( isset( $_REQUEST['s'] ) ) ? sanitize_text_field($_REQUEST['s']) : false;
			if($search!=''){
				$wheresearch = " AND concat(cliente_nombre,' ',cliente_nif,' ',factura_numero,' ',pedido_numero) like '%".$search."%' ";
			}
			
			$columns = $this->get_columns();
			$hidden = $this->get_hidden_columns();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			global $wpdb;
			$user_nif = get_option('FTBAI_DNI');
			$tbaicode = get_option('FTBAI_CLAVE');
			$modoprueba = FTBAI_modopruebas();

			if($user_nif=='' || $tbaicode==''){
				$user_nif=get_option('FTBAI_DNI_TEST');
				$tbaicode=get_option('FTBAI_CLAVE_TEST');			
			}		
			
			if ( isset( $_GET['status'] ) ) {
               $where = " AND factura_estado = '".sanitize_text_field($_GET['status'])."' ";
            }
			if ( isset( $_GET['tipo'] ) ) {
               $where = " AND factura_tipo = '".sanitize_text_field($_GET['tipo'])."' ";
            }			
			
			$product_ids = $wpdb->get_results("	SELECT * FROM ".$wpdb->prefix."ftbai_facturas 
												where user_dni='".$user_nif."' AND factura_total<>0 AND user_apikey='".$tbaicode."' ".$where." ".$wheresearch." ".$wheredate."
												ORDER BY date(factura_fecha) DESC;" );
			$detalles=array();
			foreach ($product_ids as $product){
				array_push($detalles,array( 	'pedido_numero' 			=> $product->pedido_numero,
												'factura_estado' 			=> $product->factura_estado,
												'tipo'  					=> $product->factura_tipo,
										   		'factura_numero'			=> $product->factura_numero,
												'factura' 					=> $product->factura_serie.' / '.$product->factura_numero, 
												'emailenviado'				=> $product->factura_vista,
										   		'fechafactura' 				=> $product->factura_fecha,
												'cliente_nombre'			=> $product->cliente_nombre,
												'cliente_nif' 				=> $product->cliente_nif,
										   		'factura_rectificada_en'	=> $product->factura_rectificada_en,
										   		'factura_rectificativa_de'	=> $product->factura_rectificativa_de,
												'factura_total'				=> $product->factura_total,
												'tbai_num'					=> $product->tbai_num,
												'tbai_qr'					=> $product->tbai_qr,
										   		'modoprueba'				=> $modoprueba
										)
				  );	
			}	
			
			$perPage = 50;
			$currentPage = $this->get_pagenum();
			$totalItems = count($detalles);
			
			//if( isset( $_GET['orderby'] ) ){
			usort($detalles, array(&$this, 'usort_reorder'));
			//}
			
			$this->set_pagination_args( array(
				'total_items' => $totalItems,
				'per_page'    => $perPage
			) );

			$detalles = array_slice($detalles,(($currentPage-1)*$perPage),$perPage);

			$this->_column_headers = array($columns, $hidden, $sortable);

			$this->items = $detalles;
		}
		
	function extra_tablenav( $which )
		{
			switch ( $which )
			{
				case 'top':
					// Your html code to output
					global $wpdb, $wp_locale;
						$sql = "
						SELECT DISTINCT YEAR( factura_fecha ) AS year, MONTH( factura_fecha ) AS month
						FROM ".$wpdb->prefix."ftbai_facturas
						ORDER BY factura_fecha DESC";
						$months = $wpdb->get_results(
							$wpdb->prepare(
								$sql
							)
						);
						$month_count = count( $months );

						if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) {
							return;
						}
						$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;

						//$wp_query = add_query_arg();
						$wp_query = remove_query_arg('m');
						$link = esc_url_raw($wp_query);
						?>
						<div class="alignleft actions">
						<select name="m" id="filter-by-date">
							<option<?php selected( $m, 0 ); ?> value="0" data-rc="<?php _e($link); ?>"><?php _e('All dates','wp-ticketbai'); ?></option>
						<?php

						foreach ( $months as $arc_row ) {
							if ( 0 == $arc_row->year ) {
								continue;
							}

							$month = zeroise( $arc_row->month, 2 );
							$year  = $arc_row->year;

							$wp_query = add_query_arg('m', $arc_row->year . $month);
							$link = esc_url_raw($wp_query);

							printf(
								"<option %s value='%s' data-rc='%s'>%s</option>\n",
								selected( $m, $year . $month, false ),
								esc_attr( $arc_row->year . $month ),
								esc_attr( $link),
								/* translators: 1: Month name, 2: 4-digit year. */
								sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
							);
						}
						?>
						</select>
						<a href="javascript:void(0)" class="button" onclick="window.location.href = jQuery('#filter-by-date option:selected').data('rc');"><?php _e('Filter','wp-ticketbai'); ?></a>
						</div>
						<?php
						echo '<button class="button" onclick="ftbai_addfactura();event.preventDefault()">Crear Factura Manualmente</button>';
					break;

				case 'bottom':
					break;
			}
		}		
	} //class
																							
																							
	function FTBAI_listafacturas(){
	  $myListTable = new FTBAI_List_invoices_Table();
	  echo '</pre><style>.tabcontent .table-view-list td{vertical-align: middle !important;}</style>';
	  $myListTable->views();
	  $myListTable->prepare_items(); 
	  $myListTable->search_box(__('Search','wp-ticketbai'), 'search');
	  $myListTable->display(); 
	  echo '</div>'; 
	}
	
	if(is_admin() && strpos( $_SERVER['REQUEST_URI'],'admin.php?page=WPTicketBAI')!==false){add_action('admin_head','FTBAI_admin_header');}
	function FTBAI_admin_header() {
		$page = ( isset($_GET['page'] ) ) ? esc_attr( sanitize_text_field($_GET['page']) ) : false;
		if( 'WPTicketBAI' != $page )
			return; 
		echo '<style type="text/css">';
		echo '.wp-list-table .column-factura {width:310px;}';
		echo '.wp-list-table .column-wc_actions {width:80px;}';
		echo '.wp-list-table .column-emailenviado {width:150px;}';
		echo '</style>';
	}																							
?>