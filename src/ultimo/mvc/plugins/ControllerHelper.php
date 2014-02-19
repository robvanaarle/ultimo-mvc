<?php

namespace ultimo\mvc\plugins;

abstract class ControllerHelper {
  /**
   * The module the helper is for.
   * @var \ultimo\mvc\Module
   */
  protected $module;
  
  /**
   * The application of the module.
   * @var \ultimo\mvc\Application
   */
  protected $application;
  
  /**
   * The view the controller must use to render.
   * @var \ultimo\mvc\View
   */
  protected $view;
  
  /**
   * The request being dispatched.
   * @var \ultimo\mvc\Request
   */
  protected $request;
  
  /**
   * Constructor.
   * @param \ultimo\mvc\Module $module The module the helper is for.
   */
  public function __construct(\ultimo\mvc\Module $module) {
    $this->module = $module;
    $this->application = $module->getApplication();
    $this->view = $this->module->getView();
    $this->request = $this->application->getRequest();
  }
}