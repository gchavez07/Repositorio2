<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of aprobaciones
 *
 * @author 
 */
class ControllerPagosAprobaciones extends Controller{
    //put your code here
    
    private $error = array();
    
    public function index() 
    {
        $this->load->model("pagos/aprobaciones");
        
        $this->getList();
    }
    
    public function insert() 
    {
        $this->load->model("pagos/aprobaciones");	
		
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) 
        {            
                
                $this->model_pagos_aprobaciones->insertaRegistro($this->request->post);                

                $this->redirect($this->url->link('pagos/aprobaciones', 'token=' . $this->session->data['token'], 'SSL'));		
        }
        
        $this->getForm(); 
        
    }
    
    public function update() 
    {
        $this->load->model("pagos/aprobaciones");	
		
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm())
        {
            $this->model_pagos_aprobaciones->actualizaRegistro($this->request->post, $this->request->get['registro_id']);
            
            $this->redirect($this->url->link('pagos/aprobaciones', 'token=' . $this->session->data['token'], 'SSL'));		
        }
        
        $this->getForm();
    }
    
    public function delete()
    {
        $this->load->model("pagos/aprobaciones");	
		
        if (isset($this->request->post['selector'])) 
        {

                foreach ($this->request->post['selector'] as $registro_id) 
                {

                        $this->model_pagos_aprobaciones->borraRegistro($registro_id);

                }

                $this->session->data['success'] = 'EXITO! Se ha eliminado exitosamente el registro';

                $this->redirect($this->url->link('pagos/aprobaciones', 'token=' . $this->session->data['token'], 'SSL'));
        }

	$this->getList();
        
    }
    
    public function getList()
    {
        
        $url = '';
        
        $registros = $this->model_pagos_aprobaciones->traeRegistros();
		$registros2 = $this->model_pagos_aprobaciones->traeRegistros2();
		
        
        $this->data['insert'] = $this->url->link('pagos/aprobaciones/insert', 'token='.$this->request->get['token'], 'SSL');
        
        $this->data['action'] = $this->url->link('pagos/aprobaciones/delete', 'token='.$this->request->get['token'], 'SSL');
        
		
        foreach($registros2 as $regist)
        {
            $action = array();
			
            $action[] = array(

                'text' => 'Aprobar monto', 

                'icon_font' => '',

                'href' => $this->url->link('pagos/aprobaciones/update', 'token='.$this->session->data['token'] . '&registro_id=' . $regist['pago_id'] . $url, 'SSL')
                
            );

            $this->data['registros2'][] = array(

                "pago_id" => $regist['pago_id'],

                "acreditado" => $regist['acreditado'],
				"credito_id" => $regist['credito_id'],
				"importe_pagado" => $regist['importe_pagado'],
                
               
			   
                
                "action" => $action
            );            
        }
		foreach($registros as $registro)
        {
            $action = array();
			
            $action[] = array(

                'text' => 'Aprobar monto', 

                'icon_font' => '',

                'href' => $this->url->link('pagos/aprobaciones/update', 'token='.$this->session->data['token'] . '&registro_id=' . $registro['pago_id'] . $url, 'SSL')
                
            );

            $this->data['registros'][] = array(

                "pago_id" => $registro['pago_id'],

                "importe_negativo" => $registro['importe_pagado'],
				"fecha_valor" => $registro['fecha_valor'],
				//"importe" => "200",
                
                "action" => $action
            );
			$montos = $this->model_pagos_aprobaciones->traeMontos($registro['fecha_valor']);
			foreach($montos as $monto)
			{
				$this->data['montos'][] = array(

					"pago_id" => $monto['pago_id'],
					"importe" => $monto['importe_pagado'],
					"fecha_valor" => $monto['fecha_valor'],
				);            
			}
			
		  
        }
        
        $this->template = 'pagos/aprobaciones_lista.tpl';

        $this->data['token'] = $this->session->data['token'];

        $this->children = array(

                'common/header',

                'common/footer'

        );

        $this->response->setOutput($this->render()); 
    }
    
    public function getForm()
    {
        
        $this->load->model("pagos/aprobaciones");	
        
         if(isset($this->request->get['registro_id'])) 
        {
            
            $this->data['action'] = $this->url->link('pagos/aprobaciones/update', 'token='.$this->request->get['token'] . '&registro_id=' . $this->request->get['registro_id'], 'SSL');
	
            $registro_info = $this->model_pagos_aprobaciones->traeRegistro($this->request->get['registro_id']);
			
	} 
        else             
        {
	
            $this->data['action'] = $this->url->link('pagos/aprobaciones/insert', 'token='.$this->request->get['token'], 'SSL');
	
        }
        
        if(isset($this->request->post['razon'])) {
			
                $this->data['razon'] = $this->request->post['razon']; 

        } elseif(isset($registro_info['razonsocial'])) {

                $this->data['razon'] = $registro_info['razonsocial'];

        } else {

                $this->data['razon'] = '';
        }
        
        if(isset($this->request->post['representante'])) {
			
                $this->data['representante'] = $this->request->post['representante']; 

        } elseif(isset($registro_info['representante'])) {

                $this->data['representante'] = $registro_info['representante'];

        } else {

                $this->data['representante'] = '';
        }
        
        if(isset($this->request->post['rfc'])) {
			
                $this->data['rfc'] = $this->request->post['rfc']; 

        } elseif(isset($registro_info['rfc_agencia'])) {

                $this->data['rfc'] = $registro_info['rfc_agencia'];

        } else {

                $this->data['rfc'] = '';
        }
        
        if(isset($this->request->post['direccion'])) {
			
                $this->data['direccion'] = $this->request->post['direccion']; 

        } elseif(isset($registro_info['direccion'])) {

                $this->data['direccion'] = $registro_info['direccion'];

        } else {

                $this->data['direccion'] = '';
        }
        
        if(isset($this->request->post['ciudad'])) {
			
                $this->data['ciudad'] = $this->request->post['ciudad']; 

        } elseif(isset($registro_info['ciudad'])) {

                $this->data['ciudad'] = $registro_info['ciudad'];

        } else {

                $this->data['ciudad'] = '';
        }
        
        if(isset($this->request->post['estado'])) {
			
                $this->data['estado'] = $this->request->post['estado']; 

        } elseif(isset($registro_info['estado'])) {

                $this->data['estado'] = $registro_info['estado'];

        } else {

                $this->data['estado'] = '';
        }
        
        if(isset($this->request->post['pais'])) {
			
                $this->data['pais'] = $this->request->post['pais']; 

        } elseif(isset($registro_info['pais'])) {

                $this->data['pais'] = $registro_info['pais'];

        } else {

                $this->data['pais'] = '';
        }
                
        if(isset($this->request->post['codigo'])) {
			
                $this->data['codigo'] = $this->request->post['codigo']; 

        } elseif(isset($registro_info['cp'])) {

                $this->data['codigo'] = $registro_info['cp'];

        } else {

                $this->data['codigo'] = '';
        }
        
        if(isset($this->request->post['status'])) {
			
                $this->data['status'] = $this->request->post['status']; 

        } elseif(isset($registro_info['status'])) {

                $this->data['status'] = $registro_info['status'];

        } else {

                $this->data['status'] = '';
        }
        
        /*ERRORES*/
        if(!empty($this->error['razon'])) {
            
                $this->data['error_razon'] = $this->error['razon'];

        } else {
            
                $this->data['error_razon'] = '';	
        }
        
        if(!empty($this->error['representante'])) {
            
                $this->data['error_representante'] = $this->error['representante'];

        } else {
            
                $this->data['error_representante'] = '';	
        }
        
        if(!empty($this->error['rfc'])) {
            
                $this->data['error_rfc'] = $this->error['rfc'];

        } else {
            
                $this->data['error_rfc'] = '';	
        } 
        
        if(!empty($this->error['direccion'])) {
            
                $this->data['error_direccion'] = $this->error['direccion'];

        } else {
            
                $this->data['error_direccion'] = '';	
        }
        
        if(!empty($this->error['ciudad'])) {
            
                $this->data['error_ciudad'] = $this->error['ciudad'];

        } else {
            
                $this->data['error_ciudad'] = '';	
        }
        
        if(!empty($this->error['estado'])) {
            
                $this->data['error_estado'] = $this->error['estado'];

        } else {
            
                $this->data['error_estado'] = '';	
        }
        
        if(!empty($this->error['pais'])) {
            
                $this->data['error_pais'] = $this->error['pais'];

        } else {
            
                $this->data['error_pais'] = '';	
        }
        
        if(!empty($this->error['codigo'])) {
            
                $this->data['error_codigo'] = $this->error['codigo'];

        } else {
            
                $this->data['error_codigo'] = '';	
        }        
        
        
        $this->data['cancel'] = $this->url->link('pagos/aprobaciones', 'token='.$this->request->get['token'], 'SSL');
		
        $this->template = 'pagos/aprobaciones.tpl';

        $this->children = array(

                'common/header',

                'common/footer'

        );

        $this->response->setOutput($this->render());       
    
    }
    
    private function validateForm() 
    {
        if(empty($this->request->post['razon'])){
            
            $this->error['razon'] = 'Debe especificar una Razón Social';
        }
        
        if(empty($this->request->post['representante'])){
            
            $this->error['representante'] = 'Debe especificar un representante';
        }
        
        if(empty($this->request->post['rfc'])){
            
            $this->error['rfc'] = 'Debe especificar un rfc';
        }        
        
        if(empty($this->request->post['direccion'])){
            
            $this->error['direccion'] = 'Debe especificar una dirección';
        }  
        
        if(empty($this->request->post['ciudad'])){
            
            $this->error['ciudad'] = 'Debe especificar una ciudad';
        } 
        
        if(empty($this->request->post['estado'])){
            
            $this->error['estado'] = 'Debe especificar un estado';
        }  
        
        if(empty($this->request->post['pais'])){
            
            $this->error['pais'] = 'Debe especificar un país';
        }  
        
        if(empty($this->request->post['codigo'])){
            
            $this->error['codigo'] = 'Debe especificar un código postal';
        }  
        
        if (!$this->error) {
            
            return true;           
            
        } else {
			
            return false;            
        }
        
    }
    
}
