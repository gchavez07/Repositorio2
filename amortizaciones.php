<?php
 
class ControllerCreditosAmortizaciones extends Controller {

	private $error = array();

  	public function index() {

		if (!isset($this->session->data['token'])){

			$this->redirect($this->url->link('common/login', ' ', 'SSL'));

		}

		if (!isset($this->session->data['token'])){

			$this->redirect($this->url->link('common/login', ' ', 'SSL'));

		}

		$this->document->setTitle('Amortizaciones');

      //$this->getList();
      
      $this->redirect($this->url->link('error/notfound', 'token=' . $this->session->data['token'], 'SSL'));

  	}

	private function getList(){


		if (isset($this->request->get['page'])) {

			$page = $this->request->get['page'];

			$url .= '&page=' . $this->request->get['page'];

		} else {

			$page = 1;

		}

        if (isset($this->request->post['filter_credit'])) {

            $credit = $this->request->post['filter_credit'];

		} else {

			$credit = '';

		}

        $this->data['credit'] = $credit;

		$data = array(

            'credit'        => $credit,

			'start'         => ($page-1) * LIMIT,

			'limit'         => LIMIT
		);

		$this->data['insert'] = $this->url->link('creditos/amortizaciones/insert', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['delete'] = $this->url->link('creditos/amortizaciones/delete', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['filter'] = $this->url->link('creditos/amortizaciones', 'token=' . $this->session->data['token'], 'SSL');

        $results = $this->amortizacion->listado($data);

		$this->data['amortizaciones'] = array();

		$results_total = 0;
        if(isset($results)){
    		foreach ($results as $result) {

          	    $this->data['amortizaciones'][] = array(

    				'disposicion'     => $result['disposicion_id'],

    				'amortizacion' 	    => $result['no_amortizacion'],

    				'importe' 	    => '$ ' . number_format($result['importe_amortizacion'], 2, ".", ","),

    				'fecha_vencimiento' 	    => $result['fecha_vencimiento'],

    				'importe_pagado' 	    => '$ ' . number_format($result['importe_pagado'], 2, ".", ","),
    			);

    			$results_total++;
        	}
        }
		$this->data['heading_title'] = 'Amortizaciones';

		$pagination = new Pagination();

		$pagination->total = $results_total;

		$pagination->page = $page;

		$pagination->limit = LIMIT;

		$pagination->url = $this->url->link('creditos/amortizaciones/', 'token=' . $this->session->data['token'] .'&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['text_no_results'] = 'No se han encontrado resultados';

		$this->template = 'creditos/amortizaciones_lista.tpl';
		$this->children = array(

			'common/header',

			'common/footer'

		);

		$this->response->setOutput($this->render());


	}
}

?>
