<?php

class ControllerCreditosCreditos extends Controller {

 public function index() {

	$this->getList();

 }

 public function info() {

       $this->getInformationCredit();

 }

 public function detail() {

        $this->getDetalle();

    }

 public function voucher() {

		//$this->getVoucherInfo();
      $this->voucher->generaVoucher($this->request->get['registro_id'],2);

 }

 public function xml() {

   //$this->getVoucherInfo();
   $dataXml = array(

     'creditoId'          => $this->request->get['registro_id'],

     'noDisposicion'      => 1,

     'mes'                => isset($this->request->get['mes']) ? $this->request->get['mes'] : date('m',strtotime($this->session->data['f_operaciones'])),

     'anio'               => isset($this->request->get['anio']) ? $this->request->get['anio'] : date('Y',strtotime($this->session->data['f_operaciones']))

   );

   if(strlen($dataXml['mes'])<2) {

     $mes = '0'.$dataXml['mes'];

   } else {

     $mes =$dataXml['mes'];

   }
   if(!empty($this->request->get['curp'])) {

     $filename = $this->request->get['curp'] . '_' . $dataXml['anio'] . $mes . '.xml';

   } else {
     $filename = $dataXml['anio'] . $mes .'.xml';

   }

   $xml = $this->shf->generaXml($dataXml);


   if($this->request->get['download'] == 1) {

     header ("Content-Disposition: attachment; filename=" . $filename );
     header ("Content-Type: application/force-download");

   } else {

     header('Content-Type: application/xml');

   }

   echo $xml;

 }

 public function getList() {

        if (isset($this->request->get['page'])) {

            $page = $this->request->get['page'];
        } else {

            $page = 1;
        }

        if (isset($this->request->get['filter_folio'])) {

            $filter_folio = $this->request->get['filter_folio'];
        } else {

            $filter_folio = null;
        }

        if (isset($this->request->get['filter_cliente'])) {

            $filter_status = $this->request->get['filter_status'];
        } else {

            $filter_status = null;
        }

        if (isset($this->request->get['filter_status'])) {

            $filter_status = $this->request->get['filter_status'];

        } else {

            $filter_status = null;
        }

        if (isset($this->request->get['filter_importe_from'])) {

            $filter_importe_from = $this->request->get['filter_importe_to'];

        } else {

            $filter_importe_from = null;

        }

        if (isset($this->request->get['filter_importe_to'])) {

            $filter_importe_to = $this->request->get['filter_importe_to'];

        } else {

            $filter_importe_to = null;

        }

        if (isset($this->request->get['filter_init_from'])) {

            $filter_init_from = $this->request->get['filter_init_from'];

        } else {

            $filter_init_from = null;

        }

        if (isset($this->request->get['filter_init_to'])) {

            $filter_init_to = $this->request->get['filter_init_to'];

        } else {

            $filter_init_to = null;

        }

		  if (isset($this->request->get['filter_end_from'])) {

            $filter_end_from = $this->request->get['filter_end_from'];

        } else {

            $filter_end_from = null;

        }

        if (isset($this->request->get['filter_end_to'])) {

            $filter_end_to = $this->request->get['filter_end_to'];

        } else {

            $filter_end_to = null;

        }

        $url = '';

        $data = array(

            'start' 					=> ($page - 1) * LIMIT,

            'limit' 					=> LIMIT,

            'filter_init_to' 		=> $filter_init_to,

				'filter_init_from' 	=> $filter_init_from,

				'filter_importe_to' 		=> $filter_importe_to,

				'filter_importe_from' 	=> $filter_importe_from,

            'filter_end_to' 		=> $filter_end_to,

				'filter_end_from' 	=> $filter_end_from,

            'filter_status' 		=> $filter_status,

        );

        $registros = $this->credito->traeRegistrados($data);

        $registros_total = $this->credito->traeTotalRegistros($data);

        $this->data['registros'] = array();

        foreach ($registros as $registro) {

				$action = array();

            $action[] = array(

                'text' => 'Ver Detalle',

                'icon_font' => 'fa-search',

                'targe' => '_self',

                'href' => $this->url->link('creditos/creditos/info', 'token=' . $this->session->data['token'] . '&registro_id=' . $registro['creditoId'] . $url, 'SSL')

            );

            $action[] = array(

                'text' => 'Ver Edo de Cuenta',

                'icon_font' => 'fa-file',

                'targe' => '_blank',

                'href' => $this->url->link('creditos/creditos/voucher', 'token=' . $this->session->data['token'] . '&registro_id=' . $registro['creditoId'] . $url, 'SSL')

            );


            $action[] = array(

                'text' => 'XML de Comportamiento Periodo Actual',

                'icon_font' => 'fa-code',

                'targe' => '_blank',

                'href' => $this->url->link('creditos/creditos/xml', 'token=' . $this->session->data['token'] . '&registro_id=' . $registro['creditoId'] . $url, 'SSL')

            );

            $this->data['registros'][] = array(

                "credito_id" 				=> $registro['creditoId'],

                "no_solicitud" 			=> $registro['nroSolicitud'],

                "acreditado" 				=> $registro['acreditadoNombre'],

                "producto" 				=> $registro['descripcion'],

                "fecha_inicio" 			=> date('d/m/Y',strtotime($registro['fechaInicio'])),

                "fecha_vencimiento" 	=> date('d/m/Y',strtotime($registro['fechaTermino'])),

                "monto_capital" 			=> '$ ' . number_format($registro['importeDispuesto'], 2, ".", ","),

                "monto_dispuesto" 		=> '$ ' . number_format($registro['importeDispuesto'], 2, ".", ","),

                "action" => $action
            );
        }

        $this->template = 'creditos/creditos_lista.tpl';

        $this->data['token'] = $this->session->data['token'];

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

 public function getInformationCredit() {

		$url = '';

		if (isset($this->request->get['page'])) {

  			$page = $this->request->get['page'];

  			$url .= '&page=' . $this->request->get['page'];

		} else {

    			$page = 1;

		}

		if (isset($this->request->get['registro_id'])) {

			$url .= '&registro_id='  . $this->request->get['registro_id'];

		}

    $registro_info = $this->credito->informacionCredito($this->request->get['registro_id']);

    $this->data['credito'] = $registro_info;

    //Abrimos la cabecera de informacion del credito
     $this->data['hcreditos'] = $this->getChild('widgets/hcreditos', $this->request->get['registro_id']);

     $this->data['acreditado'] = $this->getChild('widgets/acreditado', $this->request->get['registro_id']);

     $this->data['garantia'] = $this->getChild('widgets/garantia', $this->request->get['registro_id']);

     $this->data['escrituracion'] = $this->getChild('widgets/garantia', $this->request->get['registro_id']);

     $this->data['inmueble'] = $this->getChild('widgets/inmueble', $this->request->get['registro_id']);

     $this->data['tablaAmortizacion'] = $this->getChild('widgets/tablaamortizacion', $this->request->get['registro_id']);

     $this->data['tabproyectada'] = $this->getChild('widgets/tabproyectada', $this->request->get['registro_id']);

     $this->data['transacciones'] = $this->getChild('widgets/movimientos', $this->request->get['registro_id']);

     $this->data['saldos'] = $this->getChild('widgets/saldos', $this->request->get['registro_id']);

     $this->data['pagos'] =$this->getChild('widgets/pagos', $this->request->get['registro_id']);

     $this->data['cuotas'] = $this->amortizacion->traeCuotas($this->request->get['registro_id']);

     $disposicion = $this->credito->traeDisposicionesCredito($this->request->get['registro_id']);

     $this->data['disposiciones'] = $disposicion;

     $this->data['saldo'] = $this->saldos->traeSaldo($this->request->get['registro_id']);

     $this->data['avales'] = $this->acreditado->traeAvales($this->request->get['registro_id']);

     $this->data['cancel'] = $this->url->link('creditos/creditos/info', 'token=' . $this->request->get['token'] . $url, 'SSL');

     $dataValidaMora = array(

         'credito_id' 			=> $this->request->get['registro_id'],

          'disposicion_id' 		=> $disposicion[0]['noDisposicion'],

           'saldo_id' 				=> 2

     );

     $this->data['existeMora'] = $this->intereses->validaExistenciaMoratorios($dataValidaMora);

     $this->data['ulrModal'] = $this->url->link('creditos/creditos/cuotas','token=' . $this->session->data['token'], 'SSL');

     $this->data['ulrModalPagos'] = $this->url->link('creditos/creditos/pagos','token=' . $this->session->data['token'], 'SSL');

     $this->template = 'creditos/credito_general.tpl';

     $this->children = array(

            'common/header',

            'common/footer'
    );

    $this->response->setOutput($this->render());
 }

	public function getVoucherInfo() {

        $this->voucher->generaVoucher($this->request->get['registro_id']);
  }

	public function getDetalle() {

		$url = '';

		if (isset($this->request->get['page'])) {

			$page = $this->request->get['page'];

			$url .= '&page=' . $this->request->get['page'];

		} else {

			$page = 1;

		}

		if (isset($this->request->get['registro_id'])) {

			$url .= '&registro_id=' . $this->request->get['registro_id'];

		}

			 $data = array(

            'start' 					=> ($page - 1) * LIMIT,

            'limit' 					=> LIMIT,

            'filter_init_to' 		=> '',

				'filter_init_from' 	=> '',

				'filter_importe_to' 		=> '',

				'filter_importe_from' 	=> '',

            'filter_end_to' 		=> '',

				'filter_end_from' 	=> '',

            'filter_status' 		=> 2,

        );

		$registro_info = $this->credito->informacionCredito($this->request->get['registro_id']);

    $this->data['credito'] = $registro_info;

      $this->data['disposiciones'] = $this->credito->traeDisposicionesCredito($this->request->get['registro_id']);

      $disposicion_vigente = $this->credito->traeDisposicionVigenteByCredito($this->request->get['registro_id']);

      $exigible = $this->amortizacion->pagoExigible($this->request->get['registro_id']);

      $this->data['capital_vigente'] = $this->amortizacion->traeCapitalVigente($this->request->get['registro_id']);

      $dataSaldo = array(

      	'credito_id' 			=> $this->request->get['registro_id'],

      	'disposicion_id' 		=> $disposicion_vigente['noDisposicion'],

      	'saldo_id' 				=> 2

      );

      $this->data['capital_vencido'] =  $this->saldos->traeImportePorSaldo($dataSaldo);

		 $dataSaldo = array(

      	'credito_id' 			=> $this->request->get['registro_id'],

      	'disposicion_id' 		=> $disposicion_vigente['noDisposicion'],

      	'saldo_id' 				=> '7,6'

      );

      $this->data['interes_vigente'] =  $this->saldos->traeImportePorSaldo($dataSaldo);
      //$this->intereses->traeInteresVigente($this->request->get['registro_id']);

      $dataSaldo = array(

      	'credito_id' 			=> $this->request->get['registro_id'],

      	'disposicion_id' 		=> $disposicion_vigente['noDisposicion'],

      	'saldo_id' 				=> 8

      );

      $this->data['interes_vencido'] =  $this->saldos->traeImportePorSaldo($dataSaldo);
      //$this->intereses->traeInteresVencido($this->request->get['registro_id'],1);

      $dataSaldo = array(

      	'credito_id' 			=> $this->request->get['registro_id'],

      	'disposicion_id' 		=> $disposicion_vigente['noDisposicion'],

      	'saldo_id' 				=> 2

      );

      $this->data['interes_moroso'] = $this->intereses->traeInteresVencido($this->request->get['registro_id'],2);

      $dataSaldo = array(

      	'credito_id' 			=> $this->request->get['registro_id'],

      	'disposicion_id' 		=> $disposicion_vigente['noDisposicion'],

      	'saldo_id' 				=> 5

      );

      $this->data['capitalizacion'] = $this->saldos->traeImportePorSaldo($dataSaldo);

		$this->data['pagoExigible'] = 0;

		$dataSaldo = array(

      	'credito_id' 			=> $this->request->get['registro_id'],

      	'disposicion_id' 		=> $disposicion_vigente['noDisposicion'],

      	'saldo_id' 				=> 10

      );

      $saldo_actual =   $this->saldos->traeImportePorSaldo($dataSaldo);

      if($saldo_actual <= 0) {

      	$this->data['saldo'] = $this->data['credito']['importeDispuesto'];

      } else {
      	$this->data['saldo'] = $saldo_actual;
      }


      $this->data['avales'] = $this->acreditado->traeAvales($this->request->get['registro_id']);

      $this->data['garantia'] = $this->acreditado->traeGarantia($this->request->get['registro_id']);

      $dataPago = array(

			'credito' =>   $this->request->get['registro_id']

      );

      $this->data['pagos'] = $this->pagos->traePagos($dataPago,1);

      $this->data['link_amortizacion'] 	= $this->url->link('creditos/creditos/detail', 'token=' . $this->session->data['token'] . $url . '#amortizaciones', 'SSL');

		$this->data['link_saldos'] 			= $this->url->link('creditos/creditos/detail', 'token=' . $this->session->data['token'] . $url . '#saldos', 'SSL');

		$this->data['link_cobranza'] 		= $this->url->link('creditos/creditos/detail', 'token=' . $this->session->data['token'] . $url . '#cobranza', 'SSL');

      $this->data['cuotas'] = $this->amortizacion->traeCuotas($this->request->get['registro_id']);

      $this->data['proximo_pago'] =  $this->amortizacion->proximoPago($this->request->get['registro_id'], 2);

      $pago_accesorios = $this->producto->traePagoAccesorio($this->request->get['registro_id']);

      $accesorios_costo = 0;

      $monto = $registro_info['importeDispuesto'];

      $saldo_actual = $registro_info['importeDispuesto'];

      $this->data['cancel'] = $this->url->link('creditos/creditos/info', 'token=' . $this->request->get['token'] . $url, 'SSL');

		$pagination = new Pagination();

		$pagination->total = $registro_info['noAmortizaciones'];

		$pagination->page = $page;

		$pagination->limit = LIMIT;

		$pagination->text = 'Mostrando {start} de {end} de {total} ({pages} Páginas)';

		$pagination->url = $this->url->link('creditos/creditos/detail', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

        $this->template = 'creditos/credito_detalles.tpl';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
	}

	public function detallePago() {

		$html = '';

		$html ='<table class="table table-striped table-hover table-bordered" id="editable-sample">';
      $html ='<thead>';
      	$html ='<tr>';
      		$html ='<th class="center" style="text-align:center">#</th>';
      		$html ='<th class="center" style="text-align:center">Fecha Operacion</th>';
      		$html ='<th class="center" style="text-align:center">Fecha Aplicacion</th>';
      		$html ='<th class="center" style="text-align:center">Concepto</th>';
      		$html ='<th class="center" style="text-align:center">Monto</th>';
      		$html ='<th class="center" style="text-align:center">Cuotas Afectadas</th>';
      		$html ='<th class="center" style="text-align:center">Moratorios</th>';
      		$html ='<th class="center" style="text-align:center">Intereses</th>';
		      $html ='<th class="center" style="text-align:center">Capital</th>';
		      $html ='<th class="center" style="text-align:center">Abono Capital</th>';
      $html ='</tr>';
      $html ='</thead>';
      $html ='<tbody>';
      $html ='</tbody>';
      $html ='</table>';

		$this->response->setOutput($html);
	}

	public function cuotas() {

		if(!empty($this->request->get['noAmortizacion']) && !empty($this->request->get['creditoId'])) {

      $data = array(

        'creditoId'       => $this->request->get['creditoId'],

        'noAmortizacion'  => $this->request->get['noAmortizacion']

      );

      $detalleCuota = $this->amortizacion->traAmortizacionPorClaves($data);

			//Buscamos el detalle de la cuota
			$html = '<table class="table table-striped table-hover" id="editable-sample">
                  <thead>
                    <tr>
                      <th class="center" style="text-align:center">Concepto</th>
                      <th class="center" style="text-align:center">Importe</th>
                      <th class="center" style="text-align:center">Fecha Operacion</th>
                      <th class="center" style="text-align:center">Fecha Vencimiento</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="center" style="text-align:center">Capital Amortizado</td>
                      <td class="center" style="text-align:left;padding-left:45px">$ ' . number_format($detalleCuota['importeAmortizacion'],2,",",".") . '</td>
                      <td class="center" style="text-align:center">' . date('d/m/Y',strtotime($detalleCuota['fechaApertura'])) . '</td>
                      <td class="center" style="text-align:center">' . date('d/m/Y',strtotime($detalleCuota['fechaVencimiento'])) . '</td>
                    </tr>
                    <tr>
                      <td class="center" style="text-align:center">Interes Calculado</td>
                      <td class="center" style="text-align:left;padding-left:45px">$ ' . number_format($detalleCuota['importeInteres'],2,",",".") . '</td>
                      <td class="center" style="text-align:center">' . date('d/m/Y',strtotime($detalleCuota['fechaApertura'])) . '</td>
                      <td class="center" style="text-align:center">' . date('d/m/Y',strtotime($detalleCuota['fechaVencimiento'])) . '</td>
                  	</tr>
                    <tr>
                      <td class="center" style="text-align:center">Interes Moratorio</td>
                      <td class="center" style="text-align:left;padding-left:45px">$ ' . number_format($detalleCuota['moratorios'],2,",",".") . '</td>
                      <td class="center" style="text-align:center">' . date('d/m/Y',strtotime($detalleCuota['fechaVencimiento'] . ' + 1 day')) . '</td>
                      <td class="center" style="text-align:center">No Aplica</td>
                  	</tr>';

      //$html .= '</tbody>';

      //$html .= '</table>';

      $data = array(

        'creditoId'         => $this->request->get['creditoId'],

        'noAmortizacion'    => $this->request->get['noAmortizacion'],

        'fechaApertura'     => $detalleCuota['fechaApertura'],

        'fechaVencimiento'  => $detalleCuota['fechaVencimiento'],

        'layoutId'         => '',

      );

      $detallePago = $this->amortizacion->traeDetallePago($data);

      if(count($detallePago) > 0) {

        $html .= '  <thead>
                    <tr>
                     <th class="center" style="text-align:center" colspan="4">Pagos Aplicados</th>
                    </tr>';

        $i = 1;

        foreach($detallePago as $pago) {

          if(!empty($pago['origenSistema'])) {
            $origenSistema = $pago['origenSistema'];
          } else {
            $origenSistema = 'CARGA MASIVA';
          }
          if($i>1) {
            $html .= '<tr>
                          <th class="center" style="text-align:center" colspan="4"><hr/></th>
                      </tr>';

          }

          $html .= '<tr>';
            $html .= '<td class="center" style="text-align:right"><b>Nro Pago:</b> ' . $i . '</td>';
            $html .= '<td class="center" style="text-align:right"><b>Origen: </b>' . $origenSistema .'</td>';
            $html .= '<td class="center" style="text-align:right"><b>Concepto</b></td>';
            $html .= '<td class="center" style="text-align:left">' . $pago['origen'] .'</td>';
          $html .= '</tr>';
          $html .= '<tr>';
            $html .= '<td class="center" style="text-align:right"><b>Fideicomiso</b></td>';
            $html .= '<td class="center" style="text-align:left">' . $pago['descripcion'] .'</td>';
            $html .= '<td class="center" style="text-align:right"><b>Nro Cta</b></td>';
            $html .= '<td class="center" style="text-align:left">' . $pago['nroCta'] .'</td>';
          $html .= '</tr>';
          $html .= '<tr>';
            $html .= '<td class="center" style="text-align:right"><b>Imp. Deposito</b></td>';
            $html .= '<td class="center" style="text-align:left">$ ' . number_format($pago['abono'],2,",",".") .'</td>';
            $html .= '<td class="center" style="text-align:right"><b>Imp. Aplicado</b></td>';
            $html .= '<td class="center" style="text-align:left">$ ' . number_format($pago['monto'],2,",",".") .'</td>';
          $html .= '</tr>';

          $i++;

          $dataLayut = array(

            'creditoId'         => $this->request->get['creditoId'],

            'noAmortizacion'    => $this->request->get['noAmortizacion'],

            'layoutid'          => $pago['id']

          );

          $detalleCuotaPago = $this->amortizacion->traePagoCuota($dataLayut);

          if(count($detalleCuotaPago) > 0) {

             $html .= '  <thead>
                         <tr>
                          <th class="center" style="text-align:center" colspan="4">Prelacion de pago</th>
                         </tr>
                         <tr>
                            <th class="center" style="text-align:center">Concepto</th>
                            <th class="center" style="text-align:center">Importe</th>
                            <th class="center" style="text-align:center">Fecha Operacion</th>
                            <th class="center" style="text-align:center">Fecha Valor</th>
                          </tr>
                        </thead>';

            foreach($detalleCuotaPago as $detalle) {

              if((int)$detalle['montoImporte'] != 0) {

                $html .= '<tr>';

                switch($detalle['tipoOperacionId']) {

                  case 4:
                    $html .= '<td class="center" style="text-align:center">Pago Cap. Vigente</td>';
                    break;
                  case 5:
                    $html .= '<td class="center" style="text-align:center">Pago Cap. Vencido</td>';
                    break;
                  case 6:
                    $html .= '<td class="center" style="text-align:center">Pago Int. Vigente</td>';
                    break;
                  case 7:
                    $html .= '<td class="center" style="text-align:center">Pago Int. Vencido</td>';
                    break;
                  case 8:
                    $html .= '<td class="center" style="text-align:center">Pago Int. Moratorio</td>';
                    break;
                  case 21:
                    $html .= '<td class="center" style="text-align:center">Abono a Capital</td>';
                    break;

                }

                $html .= '<td class="center" style="text-align:left;padding-left:45px">$ ' . number_format($detalle['montoImporte'],2,",",".") . '</td>
                          <td class="center" style="text-align:center">' . date('d/m/Y',strtotime($detalle['fechaHoraOperacion'])) . '</td>
                          <td class="center" style="text-align:center">' . date('d/m/Y',strtotime($detalle['fechaMovimiento'])) . '</td>
                        </tr>';

              }

            }

          } else {

            $html .= '<tr>
                        <td class="center" style="text-align:center" colspan="4"><b>Sin detalle de Pago</b></td>
                      </tr>';
          }

        }

      }


      $html .= '<tbody>';

      $html .= '</table>';

			echo $html;
		}
	}

  public function pagos() {

		if(!empty($this->request->get['layoutId'])) {

      $data = array(

        'creditoId'        => '',

        'layoutId'         => $this->request->get['layoutId'],

        'noAmortizacion'   => '',

        'fechaApertura'    => '',

        'fechaVencimiento' =>  ''

      );

      $detallePago = $this->amortizacion->traeDetallePago($data);

      if(count($detallePago) > 0) {

        $html .= '<table class="table table-striped table-hover" id="editable-sample">
                  <thead>
                    <tr>
                     <th class="center" style="text-align:center" colspan="4">Pagos Aplicados</th>
                    </tr>
                  </thead>';

        $i = 1;

        foreach($detallePago as $pago) {

          if(!empty($pago['origenSistema'])) {
            $origenSistema = $pago['origenSistema'];
          } else {
            $origenSistema = 'CARGA MASIVA';
          }
          if($i>1) {
            $html .= '<tr>
                          <th class="center" style="text-align:center" colspan="4"><hr/></th>
                      </tr>';

          }

          $html .= '<tr>';
            $html .= '<td class="center" style="text-align:right"><b>Nro Pago:</b> ' . $i . '</td>';
            $html .= '<td class="center" style="text-align:right"><b>Origen: </b>' . $origenSistema .'</td>';
            $html .= '<td class="center" style="text-align:right"><b>Concepto</b></td>';
            $html .= '<td class="center" style="text-align:left">' . $pago['origen'] .'</td>';
          $html .= '</tr>';
          $html .= '<tr>';
            $html .= '<td class="center" style="text-align:right"><b>Fideicomiso</b></td>';
            $html .= '<td class="center" style="text-align:left">' . $pago['descripcion'] .'</td>';
            $html .= '<td class="center" style="text-align:right"><b>Nro Cta</b></td>';
            $html .= '<td class="center" style="text-align:left">' . $pago['nroCta'] .'</td>';
          $html .= '</tr>';
          $html .= '<tr>';
            $html .= '<td class="center" style="text-align:right"><b>Imp. Deposito</b></td>';
            $html .= '<td class="center" style="text-align:left">$ ' . number_format($pago['abono'],2,",",".") .'</td>';
            $html .= '<td class="center" style="text-align:right"><b>Imp. Aplicado</b></td>';
            $html .= '<td class="center" style="text-align:left">$ ' . number_format($pago['monto'],2,",",".") .'</td>';
          $html .= '</tr>';

          $i++;

          $dataLayut = array(

            'creditoId'         => $pago['creditoId'],

            'noAmortizacion'    => '',

            'layoutid'          => $pago['id']

          );

          $detalleCuotaPago = $this->amortizacion->traePagoCuota($dataLayut);

          if(count($detalleCuotaPago) > 0) {

             $html .= '  <thead>
                         <tr>
                          <th class="center" style="text-align:center" colspan="4">Prelacion de pago</th>
                         </tr>
                         <tr>
                            <th class="center" style="text-align:center">Concepto</th>
                            <th class="center" style="text-align:center">Importe</th>
                            <th class="center" style="text-align:center">Fecha Operacion</th>
                            <th class="center" style="text-align:center">Fecha Valor</th>
                          </tr>
                        </thead>';

            foreach($detalleCuotaPago as $detalle) {

              if((int)$detalle['montoImporte'] != 0) {

                $html .= '<tr>';

                switch($detalle['tipoOperacionId']) {

                  case 4:
                    $html .= '<td class="center" style="text-align:center">Pago Cap. Vigente</td>';
                    break;
                  case 5:
                    $html .= '<td class="center" style="text-align:center">Pago Cap. Vencido</td>';
                    break;
                  case 6:
                    $html .= '<td class="center" style="text-align:center">Pago Int. Vigente</td>';
                    break;
                  case 7:
                    $html .= '<td class="center" style="text-align:center">Pago Int. Vencido</td>';
                    break;
                  case 8:
                    $html .= '<td class="center" style="text-align:center">Pago Int. Moratorio</td>';
                    break;
                  case 21:
                    $html .= '<td class="center" style="text-align:center">Abono a Capital</td>';
                    break;

                }

                $html .= '<td class="center" style="text-align:left;padding-left:45px">$ ' . number_format($detalle['montoImporte'],2,",",".") . '</td>
                          <td class="center" style="text-align:center">' . date('d/m/Y',strtotime($detalle['fechaHoraOperacion'])) . '</td>
                          <td class="center" style="text-align:center">' . date('d/m/Y',strtotime($detalle['fechaMovimiento'])) . '</td>
                        </tr>';

              }

            }

          } else {

            $html .= '<tr>
                        <td class="center" style="text-align:center" colspan="4"><b>Sin detalle de Pago</b></td>
                      </tr>';
          }

        }

      }


      $html .= '<tbody>';

      $html .= '</table>';

			echo $html;
		}
	}

	public function saldos() {

		if (!isset($this->session->data['token'])){

			$this->redirect($this->url->link('common/login', ' ', 'SSL'));

		}

		if (!isset($this->session->data['token'])){

			$this->redirect($this->url->link('common/login', ' ', 'SSL'));

		}

		$this->document->setTitle('Taxonomía');

        $this->load->model('creditos/historialsaldos');

        $this->saldosList();

  	}

	public function saldosList() {
		$url = '';

		if (isset($this->request->get['page'])) {

			$page = $this->request->get['page'];

			$url .= '&page=' . $this->request->get['page'];

		} else {

			$page = 1;

		}

      if (isset($this->request->get['registro_id'])) {

			$this->data['registro_id'] = $this->request->get['registro_id'];

			$url .= '&registro_id=' . $this->request->get['registro_id'];

		} else {

			$this->data['registro_id'] = '';

		}


     if (isset($this->request->get['operacion'])) {

			$this->data['operation'] = $this->request->get['operacion'];

			$url .= '&operacion=' . $this->request->get['operacion'];

		} else {

			$this->data['operation'] = '';

		}

		if (isset($this->request->get['filter_date_from'])) {

			$this->data['filter_date_from'] = $this->request->get['filter_date_from'];

			$url .= '&filter_date_from=' . $this->request->get['filter_date_from'];

		} else {

			$this->data['filter_date_from'] = '';

		}

		if (isset($this->request->get['filter_date_to'])) {

			$this->data['filter_date_to'] = $this->request->get['filter_date_to'];

			$url .= '&filter_date_to=' . $this->request->get['filter_date_to'];

		} else {

			$this->data['filter_date_to'] = '';

		}

		if (isset($this->request->get['filter_importe_to'])) {

			$this->data['filter_importe_to'] = $this->request->get['filter_importe_to'];

			$url .= '&filter_importe_to=' . $this->request->get['filter_importe_to'];

		} else {

			$this->data['filter_importe_to'] = '';

		}

		if (isset($this->request->get['filter_importe_from'])) {

			$this->data['filter_importe_from'] = $this->request->get['filter_importe_from'];

			$url .= '&filter_importe_from=' . $this->request->get['filter_importe_from'];

		} else {

			$this->data['filter_importe_from'] = '';

		}

		if (isset($this->request->get['filter_saldos'])) {

			$this->data['filter_saldos'] = $this->request->get['filter_saldos'];

			$url .= '&filter_saldos=' . $this->request->get['filter_saldos'];

		} else {

			$this->data['filter_saldos'] = '';

		}

      $this->data['token'] = $this->session->data['token'];

		$data = array(

         'credit'     => $this->request->get['registro_id'],

         'operation'     => $this->data['operation'],

         'saldo'     => $this->data['filter_saldos'],

         'date_from'     => $this->data['filter_date_from'],

         'date_to'     => $this->data['filter_date_to'],

         'importe_from'     => $this->data['filter_importe_from'],

         'importe_to'     => $this->data['filter_importe_to'],

			'start'         => ($page-1) * LIMIT,

			'limit'         => LIMIT
		);

		$registro_info = $this->credito->informacionCredito($this->request->get['registro_id']);

      $this->data['credito'] = $registro_info;

		$this->data['insert'] = $this->url->link('creditos/saldos/insert', 'token=' . $this->session->data['token']. $url, 'SSL');

		$this->data['delete'] = $this->url->link('creditos/saldos/delete', 'token=' . $this->session->data['token']. $url, 'SSL');

      $results = $this->saldos->traeSaldosPorCredito($data);

      $this->data['operaciones'] = $this->operaciones->traeTiposOperaciones();

      $this->data['tSaldos'] = $this->saldos->traeTiposSaldos();

		$this->data['historial'] = array();

		$results_total = 0;

      if(isset($results)){
    		foreach ($results as $result) {

          	    $this->data['historial'][] = array(

    				'operacion'     => $result['tipoOperacion'],

    				'saldo' 	    => $result['tipoSaldo'],

    				'fecha' 	    => $result['fecha'],

    				'inicial' 	    => '$ ' . number_format($result['saldoInicial'], 2, ".", ","),

    				'cargo' 	    => '$ ' . number_format($result['cargo'], 2, ".", ","),

               'abono' 	    => '$ ' . number_format($result['abono'], 2, ".", ","),

               'importe' 	    => $result['tipo_importe'],

               'final'         => '$ ' . number_format($result['saldo_final'], 2, ".", ",")
    			);

    			$results_total++;
        	}
      }

		$this->data['heading_title'] = 'Historial de Movimientos del Credito: #' . $this->request->get['registro_id'];

		$pagination = new Pagination();

		$pagination->total = $results_total;

		$pagination->page = $page;

		$pagination->limit = LIMIT;

		$pagination->url = $this->url->link('creditos/saldos/', 'token=' . $this->session->data['token'] .'&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['text_no_results'] = 'No se han encontrado resultados';

		$this->template = 'creditos/taxonomia_lista.tpl';
		$this->children = array(

			'common/header',

			'common/footer'

		);

		$this->response->setOutput($this->render());


	}

  public function calculaLiquidacion () {

    $creditoId = $this->request->get['creditoId'];

    $registro_info = $this->credito->informacionCredito($this->request->get['creditoId']);

    if($registro_info['situacionCredito'] == 2) {

        $this->data['block-css'] = 'danger-box';

    } else if($registro_info['situacionCredito'] == 3) {

        $this->data['block-css'] = 'dark-box';

    } else if($registro_info['situacionCredito'] == 4) {

        $this->data['block-css'] = 'success-box';

    } else {

        $this->data['block-css'] = '';

    }

     $this->data['credito'] = $registro_info;

      //SALDOS
      //CAPITAL VIGENTE
      $dataSaldo = array(

        'credito_id' 			=> $creditoId,

        'disposicion_id' 		=> $registro_info['noDisposicion'],

        'saldo_id' 				=> '1,5'

      );

      $capital_vigente =  $this->saldos->traeImportePorSaldo($dataSaldo);

      //CAPITAL VIGENTE
      $dataSaldo = array(

        'credito_id' 			=> $creditoId,

        'disposicion_id' 		=> $registro_info['noDisposicion'],

        'saldo_id' 				=> '6'

      );

      $interes_calculado =  $this->saldos->traeImportePorSaldo($dataSaldo);

      //CAPITAL VENCIDO
      $dataSaldo = array(

        'credito_id' 			=> $creditoId,

        'disposicion_id' 		=> $registro_info['noDisposicion'],

        'saldo_id' 				=> '2,4'

      );

      $capital_vencido =  $this->saldos->traeImportePorSaldo($dataSaldo);

      //INTERES VIGENTE
      $dataSaldo = array(

        'credito_id' 			=> $creditoId,

        'disposicion_id' 		=> $registro_info['noDisposicion'],

        'saldo_id' 				=> '7,9,11'

      );

      $interes_vigente =  $this->saldos->traeImportePorSaldo($dataSaldo);

      //INTERESES VECIDOS
      $dataSaldo = array(

        'credito_id' 			=> $creditoId,

        'disposicion_id' 		=> $registro_info['noDisposicion'],

        'saldo_id' 				=> '8,10,12'

      );

      $interes_vencido =  $this->saldos->traeImportePorSaldo($dataSaldo);

      //INTERESES MORATORIOS
      $sql = "SELECT SUM(importeCalculo) - SUM(importePagado) total
              FROM intereses
              WHERE tipoInteres = 2 AND creditoId = '" . $creditoId . "' AND noDisposicion = '" . $registro_info['noDisposicion'] . "'";

      $mora = $this->db->query($sql);

      $interes_moratorios =  $mora->row['total'];//$this->saldos->traeImportePorSaldo($dataSaldo);*/

      //obtenemos la ultima amrtizacion activa
      $sql ="SELECT *
             FROM intereses
             WHERE creditoId = '" . $creditoId . "' AND
                   noDisposicion = '" . $registro_info['noDisposicion'] . "' AND
                   situacion = 24";

      $ultimaAmortizacion = $this->db->query($sql);

      $fechaVencimiento = '';

      if($ultimaAmortizacion->num_rows) {

        $fechaVencimiento = $ultimaAmortizacion->row['fechaVencimiento'];

        $fechaConsulta = $ultimaAmortizacion->row['fechaCalculo'];

        $noAmortizacion = $ultimaAmortizacion->row['noAmortizacion'];

      } else {

        $fechaVencimiento = date('Y-m-d',strtotime($this->session->data['f_operaciones'] . ' + 15 days'));

        $fechaConsulta = $this->session->data['f_operaciones'];

        //OBTENEMOS EL ULTIMO NUMERO DE LA SIGUIENTE AAMORTIZACION
        $dataAmortizacion = array(

          'credito' 		=> $creditoId,

          'disposicion' 	=> $registro_info['noDisposicion']

        );

        $noAmortizacion = $this->amortizacion->generaNoAmortizacion($dataAmortizacion);

      }

      $htmlHeader = '<table class="table table-striped table-hover table-bordered" id="editable-sample">';
      $htmlHeader .= '<thead>';
          $htmlHeader .= '<tr>';
              $htmlHeader .= '<th class="center" align="center">Fecha</th>';
              $htmlHeader .= '<th class="center" align="center">Saldo Inicial</th>';
              $htmlHeader .= '<th class="center" align="center">Capital Vencido</th>';
              $htmlHeader .= '<th class="center" align="center">Intereses Vencidos</th>';
              $htmlHeader .= '<th class="center" align="center">Intereses Calculados</th>';
              $htmlHeader .= '<th class="center" align="center">Saldo a Pagar</th>';
          $htmlHeader .= '</tr>';
      $htmlHeader .= '</thead>';
      $htmlHeader .= '<tbody>';


      $saldoInicial = $capital_vigente;

      $saldoFinal = $capital_vigente;

      $saldoCalculo = $capital_vigente;

      $inteSuma = 0;

      while($fechaConsulta <= $fechaVencimiento) {

        $dias	= (strtotime($fechaConsulta)-strtotime($fechaVencimiento))/86400;

        $dias 	= abs($dias);

        $dias = floor($dias);

        $dataProvision = array(

          'saldo'  			=> $saldoInicial,

          'tasa'  			=> $registro_info['tasaActualOrdinaria'],

          'dias'  			=> 1

        );
        $interes = $this->intereses->calculaInteresesDias($dataProvision);

        $inteSuma = $inteSuma + $interes;

        $htmlHeader .= '<tr>';

            $htmlHeader .= '<td class="center" align="center">' . date('d/m/Y',strtotime($fechaConsulta)) . '</td>';

            $htmlHeader .= '<td class="center" align="center">$ ' . number_format($saldoInicial,2,".",",") . '</td>';

            $htmlHeader .= '<td class="center" align="center">$ ' . number_format($capital_vencido,2,".",",") . '</td>';

            $htmlHeader .= '<td class="center" align="center">$ ' . number_format($interes_vencido,2,".",",") . '</td>';

            $htmlHeader .= '<td class="center" align="center">$ ' . number_format($inteSuma,2,".",",") . '</td>';

            $saldoFinal = $saldoInicial + $inteSuma + $interes_vencido + $capital_vencido;

            if($noAmortizacion <= 6) {

              $saldoCalculo = $saldoFinal + $interes;

            } else {

              $saldoCalculo = $saldoFinal;

            }



            $htmlHeader .= '<td class="center" align="center">' . number_format($saldoFinal,2,".",",") . '</td>';

        $htmlHeader .= '</tr>';

        $fechaConsulta =  date('Y-m-d',strtotime($fechaConsulta . ' + 1 day'));
      }

      $htmlEnd =  '</tbody>
                          <tfoot>
                              <td class="center" style="text-align:center;" colspan="6"><b>Esta tabla es unicamente informativa</b></td>
                          </tfoot>
                      <tr></table>';

      $html = $htmlHeader . $htmlBody . $htmlEnd;

      echo $html;

  }

}
