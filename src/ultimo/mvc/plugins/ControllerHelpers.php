<?php

namespace ultimo\mvc\plugins;

class ControllerHelpers implements ApplicationPlugin, ModulePlugin, ControllerPlugin {
  
  protected $helpers = array();
  
  /**
   * @var \ultimo\mvc\Module
   */
  protected $module = null;
  
  public function onPluginAdded(\ultimo\mvc\Application $application) { }
  
  /**
   * Attach a new instance of itself to the module.
   */
  public function onModuleCreated(\ultimo\mvc\Module $module) {
    $plugin = new static();
    $plugin->module = $module;
    $module->addPlugin($plugin);
  }
  
  public function onRoute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) { }
  
  public function onRouted(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request=null) { }
  
  public function onDispatch(\ultimo\mvc\Application $application) { }
  
  public function onDispatched(\ultimo\mvc\Application $application) { }
  
  public function onControllerCreated(\ultimo\mvc\Controller $controller) {
    $controller->addPlugin($this, 'helper');
  }
  
  public function onActionCall(\ultimo\mvc\Controller $controller, &$actionName) { }

  public function onActionCalled(\ultimo\mvc\Controller $controller, $actionName) { }
  
  /**
   * Returns the controller helper with the specified qualified name.
   * @param string $qName The qualified name of the controller helper.
   * @return ControllerHelper The controller helper with the specfied qualified
   * name, or null if no controller helper exists with that name.
   */
  public function getHelper($qName) {
    if ($this->module === null) {
      return null;
    }
    
    $qName = 'controllers\helpers\\' . ltrim($qName, '\\');
    if (!array_key_exists($qName, $this->helpers)) {
      $fqName = $this->module->getFQName($qName);
      if ($fqName === null) {
        return null;
      }
      
      if (!is_subclass_of('\\' . $fqName, '\ultimo\mvc\plugins\ControllerHelper')) {
        throw new ApplicationException("Helper '{$fqName}' is not a subclass of '\ultimo\mvc\plugins\ModuleHelper'");
      }
      
      $this->helpers[$qName] = new $fqName($this->module);
    }
    
    return $this->helpers[$qName];
  }
  
}