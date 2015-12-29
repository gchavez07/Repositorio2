<?php
/**
 * Description of ControllerCreditoDisposicion
 *
 * @author JoséLuis
 */
class ControllerCreditosDisposicion extends Controller{
    
    private $error = array(); 
    
    public function index() {
    	
		if (!isset($this->session->data['token'])){
            $this->redirect($this->url->link('common/login', ' ', 'SSL'));
		}	
	
      if (!isset($this->session->data['token'])){
            $this->redirect($this->url->link('common/login', ' ', 'SSL'));
      }
   
		if(!isset($this->request->get['operacion_origen_id'])) {
			
			$this->redirect($this->url->link('creditos/creditos', '&token=' . $this->session->data['token'] . '&filter_status=2', 'SSL'));
		
		}      
      
      $this->document->setTitle('Disposicion');       
      
      $this->load->model('creditos/disposicion');
      
      $this->getForm();
    }
    
    public function getForm() {
        
        $operacionOrigen = $this->request->get['operacion_origen_id'];
        
        $this->template = 'creditos/disposicion_form.tpl';

        $this->data['error_monto'] = empty($this->error['monto'])? '' : $this->error['monto'];
        $this->data['error_fechaAplicacion'] = empty($this->error['fechaAplicacion'])? '' : $this->error['fechaAplicacion'];
        $this->data['error_formaLiquidacion'] = empty($this->error['formaLiquidacion'])? '' : $this->error['formaLiquidacion'];
        $this->data['error_origenRecurso'] = empty($this->error['origenRecurso'])? '' : $this->error['origenRecurso'];
        $this->data['error_fechaFirma'] = empty($this->error['fechaFirma'])? '' : $this->error['fechaFirma'];
        $this->data['error_descuento'] = empty($this->error['descuento'])? '' : $this->error['descuento'];
        $this->data['error_diasGracia'] = empty($this->error['diasGracia'])? '' : $this->error['diasGracia'];
        $this->data['error_base'] = empty($this->error['base'])? '' : $this->error['base'];
        $this->data['error_factor'] = empty($this->error['factor'])? '' : $this->error['factor'];
        $this->data['error_puntos'] = empty($this->error['puntos'])? '' : $this->error['puntos'];
        $this->data['error_tasaInicial'] = empty($this->error['tasaInicial'])? '' : $this->error['tasaInicial'];
        $this->data['error_tasaActualMoratoria'] = empty($this->error['tasaActualMoratoria'])? '' : $this->error['tasaActualMoratoria'];

        $this->data['operacion_origen_id'] = $operacionOrigen;
        $this->data['credito'] = $this->model_creditos_disposicion->obtieneCredito($operacionOrigen);
        $this->data['formas_liquidacion'] = $this->model_creditos_disposicion->listadoFormasLiquidacion();
        $this->data['origenes_recursos'] = $this->model_creditos_disposicion->listadoOrigenesRecursos();
        $this->data['bases'] = $this->model_creditos_disposicion->listadoBases();

        $this->data['action'] = $this->url->link('creditos/disposicion/insert', 'token=' . $this->session->data['token'] .'&operacion_origen_id='.$operacionOrigen, 'SSL');

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    public function insert(){

        if (!isset($this->session->data['token'])) {

            $this->redirect($this->url->link('common/login', ' ', 'SSL'));
		  }
        $operacionOrigen = $this->request->get['operacion_origen_id'];

        $this->document->setTitle('Disposicion');

        $this->load->model('creditos/disposicion');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm($operacionOrigen)) {

            if(isset($this->request->get['operacion_origen_id'])) {

                $this->data['forma']['registro_credito_id'] = $operacionOrigen;

                $this->generaAmortizaciones($operacionOrigen);

                $id = $this->model_creditos_disposicion->insert($this->data['forma'], $this->data['amortizaciones']);

                //$this->session->data['success'] = 'EXITO! Operacion realizada exitosamente';

                //$this->redirect($this->url->link('creditos/disposicion/', 'token=' . $this->session->data['token'] .'&operacion_origen_id='.$operacionOrigen, 'SSL'));

            }
        }
        $this->getForm();
    }

    public function generaAmortizaciones($operacionOrigen){

        $credito = $this->model_creditos_disposicion->obtieneCredito($operacionOrigen);

        $totalCapital = $this->data['forma']['monto'];

        $tasaOrdinaria = $this->data['forma']['tasaInicial'];

        $tipoTasa = $credito['tasa_tipo'];

        $fechaInicio = new DateTime(date('Y-m-d',strtotime($credito['fecha_inicio'])));

        $fechaFin = new DateTime($credito['fecha_termino']);

        $diasGracia = $credito['dias_gracia'];

        $diasCobro = $this->model_creditos_disposicion->obtieneDiasCobro($credito['periodo_cc_id']);

        $fechaPago = date('Y-m-d', strtotime($this->data['forma']['fechaAplicacion']. "+ " . (int)$diasGracia . " days" ));

        $diferencia = $fechaInicio->diff($fechaFin);

        $num_pagos = 0;

        $num_pagos = ( $diferencia->y * 12 * 2) + ($diferencia->m * 2);

        /*switch ((int)$diasCobro['tipo_cobro_id']) {

            case 1:

                $num_pagos = ( $diferencia->y * 12 * 2) + ($diferencia->m * 2);
                break;
            case 2:

                $num_pagos = ( $diferencia->y * 12 * 4) + ($diferencia->m * 4);
                break;
            case 3:

                $num_pagos = ( $diferencia->y * 12 ) + $diferencia->m;

                break;
            default:
            	echo '<br/><br/> Pagos a 4 semanas valor formula: ' . ( $diferencia->y * 12 * 2) + ($diferencia->m * 2) . ' <br/><br/>' ;
            	break;
        }*/

        $this->data['amortizaciones'] = array();

        $importe = $totalCapital / $num_pagos;

        $diasFecha = $diasCobro['valor_dias'];

        for ($i = 0;$i < $num_pagos; $i++){

            $dias = $diasFecha * $i;

            $this->data['amortizaciones'][] = array(

                "no"          => $i + 1,
                
                "fechaPago"   => date('Y-m-d', strtotime($fechaPago. "+ " . (int)$dias . " days" )),
                
                "importe" 		=> $importe,
                
                "intereses" 	=> $tasaOrdinaria,
                
                "total" 		=> $importe + $tasaOrdinaria,

                "dias"        => $diasCobro['valor_dias']

            );
            //$this->model_creditos_disposicion->insert($this->request->post);
        }
    }

    private function validateForm($registro_id) {
        $credito = $this->model_creditos_disposicion->obtieneCredito($registro_id);
        if(empty($this->request->post['monto']))  {
            $this->error['monto'] = 'Debe especificar el monto de la disposición';
        }else{
            if(($credito['importe_dispuesto']+$this->request->post['monto'])<=$credito['importe_credito'])
                $this->data['forma']['monto'] = $this->request->post['monto'];
            else
                $this->error['monto'] = 'El monto de la disposición debe ser menor ya que rebasa el importe del crédito';
        }

        if(empty($this->request->post['fechaAplicacion'])) {
            $this->error['fechaAplicacion'] = 'Debe especificar la fecha de la aplicación';
        }else{
            $this->data['forma']['fechaAplicacion'] = $this->request->post['fechaAplicacion'];
        }

        if(empty($this->request->post['formaLiquidacion'])) {
            $this->error['formaLiquidacion'] = 'Debe especificar la forma de liquidación';
        }else{
            $this->data['forma']['formaLiquidacion'] = $this->request->post['formaLiquidacion'];
        }

        if(empty($this->request->post['origenRecurso'])) {
            $this->error['origenRecurso'] = 'Debe especificar el origen de recursos';
        }else{
            $this->data['forma']['origenRecurso'] = $this->request->post['origenRecurso'];
        }

        if(empty($this->request->post['fechaFirma'])) {
            $this->error['fechaFirma'] = 'Debe especificar la fecha de firma';
        }else{
            $this->data['forma']['fechaFirma'] = $this->request->post['fechaFirma'];
        }

        if(empty($this->request->post['descuento'])) {
            $this->error['descuento'] = 'Debe especificar el descuento por periodo';
        }else{
            $this->data['forma']['descuento'] = $this->request->post['descuento'];
        }

        if(empty($this->request->post['diasGracia'])) {
            $this->error['diasGracia'] = 'Debe especificar los días de gracia';
        }else{
            $this->data['forma']['diasGracia'] = $this->request->post['diasGracia'];
        }
        if($credito['tasa_tipo'] != 'FIJA'){
            if(empty($this->request->post['base'])) {
                $this->error['base'] = 'Debe especificar la base';
            }else{
                $this->data['forma']['base'] = $this->request->post['base'];
            }

            if(empty($this->request->post['factor'])) {
                $this->error['factor'] = 'Debe especificar el factor';
            }else{
                $this->data['forma']['factor'] = $this->request->post['factor'];
            }

            if(empty($this->request->post['puntos'])) {
                $this->error['puntos'] = 'Debe especificar los puntos';
            }else{
                $this->data['forma']['puntos'] = $this->request->post['puntos'];
            }
        }
        if(empty($this->request->post['tasaInicial'])) {
            $this->error['tasaInicial'] = 'Debe especificar la tasa inicial';
        }else{
            $this->data['forma']['tasaInicial'] = $this->request->post['tasaInicial'];
        }

        if(empty($this->request->post['tasaActualMoratoria'])) {
            $this->error['tasaActualMoratoria'] = 'Debe especificar la tasa actual moratoria';
        }else{
            $this->data['forma']['tasaActualMoratoria'] = $this->request->post['tasaActualMoratoria'];
        }

        if (isset($this->request->post['aplicaIVA']))
            $this->data['forma']['aplicaIVA'] = 1;
        else
            $this->data['forma']['aplicaIVA'] = 0;

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}
?>