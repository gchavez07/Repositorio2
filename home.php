<?php   
class ControllerPagosHome extends Controller {   

	public function index() {
	
	
		if (empty($this->session->data['usuario_id'])) {
			
			$this->redirect($this->url->link('common/login', '', 'SSL'));

		}

		$this->template = 'pagos/home.tpl';

		$this->children = array(

			'common/header',

			'common/footer'

		);

		$this->response->setOutput($this->render());

  	}
		
}

?>