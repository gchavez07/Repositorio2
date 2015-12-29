<?php

class ControllerPagosCancelaciones extends Controller {

	private $error = array();

  	public function index() {

				if (!isset($this->session->data['token'])){

					$this->redirect($this->url->link('common/login', ' ', 'SSL'));

				}

				if (!isset($this->session->data['token'])){

					$this->redirect($this->url->link('common/login', ' ', 'SSL'));

				}

		        $this->getForm();

  	}

		public function update() {

					if (($this->request->server['REQUEST_METHOD'] == 'POST') && count($this->request->post['pagos']) > 0 ) {

									//Recorremos el array de inputs seleccionados
									foreach($this->request->post['pagos'] as $pago) {

										//Obtenemos la informacion de esta cuota
										$sql  = "SELECT * FROM layoutFovi WHERE id = '" . $pago . "'";

										$cuota = $this->db->query($sql);

										//Cancelamos el pago directo en la tabla de layoutFovi
										$sql  = "UPDATE layoutFovi
														 SET status = 'C',
														 		 fechaCancelacion = '" . $this->session->data['f_operaciones']."'
														 WHERE id = '" . $pago . "'";

										$this->db->query($sql);

										//Revertimos los movimientos hasta la fechavalor del pago
										$dataMov = array(

											'fecha_vencimiento'	=> $cuota->row['fechavalor'],

											'f_operaciones'		=> $this->session->data['f_operaciones'],

											'credito_id'			=> $cuota->row['creditoId'],

											'notIn'						=> '2',

											'disposicion_id'	=> $cuota->row['noDisposicion'],
										);

										//realizamos la reversa por la cancelacion del pago
										$this->operaciones->revertirMovimientos($dataMov);

										//Colocamos todas las transacciones de este flujo con status de transaccion 3 en el historico de movimientos
										$sql = "UPDATE historialMovimientos SET status = 3 WHERE layoutid = '" . $pago . "'";

										$this->db->query($sql);

										//Colocamos todas las transacciones de este flujo con status de transaccion 3 en el temporal de movimientos
										$sql = "UPDATE movimientos SET status = 3 WHERE layoutid = '" . $pago . "'";

										$this->db->query($sql);

										//Desactivamos las cuotas por la reversa
										$dataDesactiva = array(

											'credito_id' 		=> $cuota->row['creditoId'],

											'disposicion_id' 	=> $cuota->row['noDisposicion']

										);

										//Guardamos las amortizaciones que se cancelaran para ejecutar el ajustes
										//de capital e interes por la cancelacion del pago
										$dataReversa = $this->pagos->traeCuotasDesactivar($dataDesactiva,$cuota->row['fechavalor']);

										$this->pagos->desactivaCuotasFecha($dataDesactiva,$cuota->row['fechavalor']);

										$dataDesactiva = array(

											'credito_id' 		=> $cuota->row['creditoId'],

											'disposicion_id' 	=> $cuota->row['noDisposicion'],

											'fecha' 	=> $cuota->row['fechavalor']

							      );

										$this->pagos->desactivaPagosReversa($dataDesactiva);



													//realizamos la revision de cartera para ajustar los saldos por la cancelacion del pago
													// y movimientos de saldos
													$fecha_transaccion = $cuota->row['fechavalor'];

													$f_operaciones = $cuota->row['fechavalor'];

													//Obtenemos los id de historico y de movimientos para este layout
													$sql="SELECT *,0 historial
																FROM movimientos m
																	INNER JOIN movimientosDetalle md ON md.movimientoId = m.movimientoId
																WHERE layoutid = '" . $pago . "'
																UNION
																SELECT *,0 historial
																FROM historialMovimientos m
																	INNER JOIN historialMovimientosDetalle md ON md.movimientoId = m.movimientoId
																WHERE layoutid = '" . $pago . "'";

													$movimientos = $this->db->query($sql);

													foreach($movimientos->rows as $movimiento) {

														if(($movimiento['nroAmortizacion']) && ($movimiento['nroInteres'] == 0)) {

															$sql ="SELECT *
																		 FROM amortizaciones
																		 WHERE creditoId='" . $movimiento['creditoId'] . "' AND
																		 			 noDisposicion='" . $movimiento['nroDisposicion'] ."' AND
																					 noAmortizacion='" . $movimiento['nroAmortizacion'] ."';";

															$importeAmortizacion = $this->db->query($sql);

															$resta = $importeAmortizacion->row['importePagado'] - $movimiento['montoImporte'];

															if($resta<=0) {
																$status = '17';
																$resta=0;
															} else {
																$status = '21';
															}

															$sql = "UPDATE amortizaciones
																			SET importePagado ='" . $resta  ."',
																					situacion='" . $status ."'
																			 WHERE creditoId='" . $movimiento['creditoId'] . "' AND
																			 			 noDisposicion='" . $movimiento['nroDisposicion'] ."' AND
																						 noAmortizacion='" . $movimiento['nroAmortizacion'] ."';";

															$this->db->query($sql);

														}

														if($movimiento['nroInteres']) {

															$sql ="SELECT *
																		 FROM intereses
																		 WHERE creditoId='" . $movimiento['creditoId'] . "' AND
																		 			 noDisposicion='" . $movimiento['nroDisposicion'] ."' AND
																					 noAmortizacion='" . $movimiento['nroAmortizacion'] ."';";

															$importeInteres = $this->db->query($sql);

															$resta = $importeInteres->row['importePagado'] - $movimiento['montoImporte'];

															if($resta<=0) {
																$status = '9';
																$resta=0;
															} else {
																$status = '14';
															}
															$sql = "UPDATE intereses
																			SET importePagado ='" . $resta ."',
																					situacion='" . $status ."'
																			 WHERE creditoId='" . $movimiento['creditoId'] . "' AND
																			 			 noDisposicion='" . $movimiento['nroDisposicion'] ."' AND
																						 noAmortizacion='" . $movimiento['nroAmortizacion'] ."' AND
																						 noInteres='" . $movimiento['nroInteres'] ."';";

															$this->db->query($sql);

														}

													}

													while($fecha_transaccion < $this->session->data['f_operaciones']) {

															$dataValida = array(

																		"credito_id" 	=> $cuota->row['creditoId'],

																		"disposicion_id" 	=> $cuota->row['noDisposicion'],

																		"fecha" 			=> $fecha_transaccion,

																		"flujoId" 			=> $pago

															);

															$pagos = $this->pagos->validaPagoFecha($dataValida);

															//Evisten pagos aplicados en estea fecha generamos un nuevo movimiento de aplicacion
															if(count($pagos) > 0) {

																		//Recorremos el array con los pago que fueron encontrado
																		foreach($pagos as $pagoAplicado) {

																			//Creamos la variable de periodo para esta operacion
																			$periodo = date('Y',strtotime($pagoAplicado['fechavalor'])) . date('m',strtotime($pagoAplicado['fechavalor']));
																			//Formateamos el array para enviar de nueva cuenta a la funcion de aplicar pagos
																			$cadenaPago = $pagoAplicado['dependencia'] . "|".
																										$periodo . "|".
																										$pagoAplicado['dependencia'] . "|"  .
																										str_replace('-','',$pagoAplicado['fechavalor']) . "|" .
																										$pagoAplicado['rfc'] ."|".
																										$pagoAplicado['curp'] . "|".
																										$pagoAplicado['paterno'] ."|".
																										$pagoAplicado['materno'] ."|".
																										$pagoAplicado['nombre'] ."|" .
																										$pagoAplicado['monto'] . "|"  .
																										$pagoAplicado['credito'] . "|" .
																										$pagoAplicado['origen'] . "|".
																										$pagoAplicado['origenSistema'] . "|".
																										$pagoAplicado['edoctaid'] . "|".
																										$pagoAplicado['id'] . "|";

																			//Convertimos la cadena string en array para enviar a la funcion de aplicar pagos
																			$pagoArray = explode("|", $cadenaPago);
																			//Enviamos el array y aplicamos el pago ordernado

																			$resultado = $this->pagos->aplicarPagos('R',$pagoArray, 0);

																			if($resultado['situacion'] == 'ok') {

											                    $sql = "UPDATE layoutFovi
											                            SET status='A'
											                            WHERE id='" . $pagoAplicado['id'] . "';";

											                    $this->db->query($sql);

											              	} else {

											                      $sql = "UPDATE layoutFovi
											                              SET status='E',
											                                  msgerror = '" . $resultado['mensaje'] . "'
											                              WHERE id='" . $layoutId . "';";

											                      $this->db->query($sql);
											              	}


																	  }
															}

															$dataRevision = array(

																		"credito_id" 	=> $cuota->row['creditoId'],

																		"disposicion_id" 	=> $cuota->row['noDisposicion'],

																		'amortizacion_id' => '',

																		'interes_id' => '',

																		'fecha' => $fecha_transaccion

														);

														//Realizamos la revision B6
														$this->revision->generaRevision($dataRevision);
														//Validamos si existe un pago en esta fecha
														$fecha_transaccion = date('Y-m-d', strtotime($fecha_transaccion . " + 1 day"));

												}

												$success = '<br/>El Pago fue cancelado Exitosamente</b>';

												$this->session->data['success'] = $success;

									}
					}

					$this->redirect($this->url->link('pagos/cancelaciones', 'token=' . $this->session->data['token'] . '&registro_id='. $this->request->post['credito_id_hidden'], 'SSL'));
  	}

    public function getForm(){
		        if (isset($this->request->get['credito'])) {

					$credit = $this->request->get['credito'];

					$url .= '&credito=' . $this->request->get['credito'];

				} else {

					$credit = '';

				}

						if(isset($this->session->data['success'])) {
								$this->data['success'] = $this->session->data['success'];
								unset($this->session->data['success']);
						} else {
								$this->data['success'] = '';
						}


						if(isset($this->session->data['warning'])) {
								$this->data['warning'] = $this->session->data['warning'];
								unset($this->session->data['warning']);
						} else {
								$this->data['warning'] = '';
						}

		        $this->data['credit'] = $credit;

		        $this->data['buscar'] = $this->url->link('pagos/cancelaciones/buscar', 'token=' . $this->request->get['token'], 'SSL');

		        $this->data['cancelarpago'] = $this->url->link('pagos/cancelaciones/update', 'token=' . $this->request->get['token'], 'SSL');

		        $this->data['heading_title'] = 'Cancelaciones';
		        $this->template = 'pagos/cancelaciones_form.tpl';
				$this->children = array(

					'common/header',

					'common/footer'

				);

				$this->response->setOutput($this->render());
    }

    public function buscar() {

		    	$data = array(

		    		'credito' 		=> $this->request->get['filter_credit'],

		    		'filter_name' 	=> $this->request->get['filter_name'],

						'f_operacion' 	=> $this->request->get['filter_faplicacion'],

						'f_valor' 	=> $this->request->get['filter_fvalor']

		    	);

		    	$pagos = $this->pagos->traePagos($data, 0);

		      $html = '';

					$i = 1;
		      foreach ($pagos as $pago) {
                $html .= '<tr class="selectable">';
								$html .= '<td width="20px"><input type="checkbox" name="pagos[]" value="'. $pago['id'].'" /><span class="custom-checkbox"></span></td>';
								$html .= '<td width="20px">' . $i . '</td>';
                $html .= '<td>' . $pago['nroSolicitud'] . '</td>';
                $html .= '<td>' . $pago['acreditado'] . '</td>';
                $html .= '<td>' . date('d/m/Y',strtotime($pago['fechavalor'])) . '</td>';
								$html .= '<td>' . date('d/m/Y',strtotime($pago['fechaAplicacion'])). '</td>';
                $html .= '<td>' . number_format($pago['monto'], 2, ".", ",") . '</td>';
                $html .= '</tr>';

								$i++;
		     	}

		      $this->response->setOutput($html);
    }

    public function cancelarpago(){

        //$pago_id= isset($this->request->get['payment_id']) ? $this->request->get['payment_id'] : '';
				/*
        $cancelado = $this->pagos->cancelaPago($pago_id);

        if(!$cancelado) {

        		$cancelado = 'false';
        }

        $this->response->setOutput($cancelado);*/
    }
}

?>
