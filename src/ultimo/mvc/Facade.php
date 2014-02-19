<?php

namespace ultimo\mvc;

abstract class Facade {
  /**
   * The module the facade is part of.
   * @var \ultimo\mvc\Module
   */
  protected $module;
  
  /**
   * The application the facade is part of.
   * @var \ultimo\mvc\Application
   */
  protected $application;
  
  /**
   * Constructor.
   * @param Module $module The module this facade is constructed for.
   */
  public function __construct(Module $module) {
    $this->module = $module;
    $this->application = $module->getApplication();
    $this->init();
  }
  
  /**
   * Called at the end of the contructor. The facade can initialize itself with
   * this function.
   */
  protected function init() { }
  
  /**
   * Returns the module the facade is part of.
   * @return Module The module the facade is part of.
   */
  public function getModule() {
    return $this->module;
  }
  
  
  /**
   * Returns the application the facade is part of. 
   * @return Application The application the facade is part of.
   */
  public function getApplication() {
    return $this->application;
  }
  
}