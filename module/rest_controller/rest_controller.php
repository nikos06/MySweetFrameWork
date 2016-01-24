<?php

 namespace t0t1\mysfw\module;
 use t0t1\mysfw\frame;

 $this->_learn('frame\contract\controller');
 $this->_learn('module\controller_base\controller_base');

 class rest_controller extends controller_base implements frame\contract\controller, frame\contract\dna {
   protected $_defaults = [
    'rest:response'              => 'http_response',
    'rest:mime_type_placeholder' => 'response:mime-type',
    'rest:mime_type'             => 'application/json',
    'rest:entity_placeholder'    => 'entity',
    'rest:entity_id_placeholder' => 'entity_id',
    'rest:data_storage'          => 'data_storage',
    'rest:post_data'             => 'data',
    'rest:tmpl'                  => 'rest.tmpl'
   ];

  protected $_ds_actions = [
    'GET'    => 'retrieve',
    'PUT'    => 'change',
    'POST'   => 'add',
    'DELETE' => 'delete'
  ];

  protected function _get_ready() {
   $this->_set_tmpl($this->inform('rest:tmpl'));
   $this->_v = $this->pop($this->inform('rest:response'), 
     $this->get_configuration_context(),
     [ $this->inform('rest:mime_type_placeholder') => $this->inform('rest:mime_type') ]
     );
  }

  protected function _check_entity($request) {
   $entity = $request->get_query($this->inform('rest:entity_placeholder'));
   if(! $entity) 
    throw $this->except('No entity name found in request');
   return $entity;
  }

  protected function _check_entity_id($request, $mandatory = true) {
   $entity_id = $request->get_query($this->inform('rest:entity_id_placeholder'));
   if(! $entity_id && $mandatory) 
    throw $this->except('No entity id found in request');
   return $entity_id;
  }

  protected function _check_definition($entity, $values) {
   if(count($values) != 1)
    throw $this->except('Entity uid needs to be of exactly _one_ part');
   $defs = $this->inform('rest:definitions');
   if(! isset($defs[$entity]))
    throw $this->except("No definitions found for entity $entity");
   if(count($defs[$entity]) != 1)
    throw $this->except("Entity definion must be of exactly _one_ part"); 
   return [current($defs[$entity]) => current($values)];
  }

  protected function _read($request) {
   $entity = $this->_check_entity($request);
   $criteria = [];
   if($entity_id = $this->_check_entity_id($request, false))
    $criteria = $this->_check_definition($entity, [$entity_id]);
   $ds = $this->indicate($this->inform('rest:data_storage'));
   $res = $ds->retrieve($entity, $criteria, ['l' => 100]);
   $response = [];
   $response['meta']['method'] = 'READ';
   $response['doc'] = $res;
   return $this->_finalize($response);
  }

  protected function _finalize($response) {
   $this->set('response', $response);
  }

  public function control($request) {
   $method = $request->get_method();
   switch($method){
    case 'GET':
     return $this->_read($request);
    case 'PUT':
    case 'DELETE':
    case 'POST':
    default:
     throw $this->except("Unhandled HTTP method: $method");
   }
  }
 }
