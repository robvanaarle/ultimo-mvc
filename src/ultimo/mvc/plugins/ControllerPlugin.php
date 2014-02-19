<?php

namespace ultimo\mvc\plugins;

interface ControllerPlugin extends Plugin {
  
  /**
   * Called before a controller action is called.
   * @param \ultimo\mvc\Controller $controller The controller the action is
   * being called on.
   * @param string $actionName The name of the action.
   */
  public function onActionCall(\ultimo\mvc\Controller $controller, &$actionName);
  
  /**
   * Called after a controller action was called.
   * @param \ultimo\mvc\Controller $controller The controller the action was
   * calloed on.
   * @param string $actionName The name of the called action.
   */
  public function onActionCalled(\ultimo\mvc\Controller $controller, $actionName);
}