<?php

namespace ultimo\mvc\plugins;

interface ModulePlugin extends Plugin {
  /**
   * Called after a module created a controller.
   * @param \ultimo\mvc\Controller $controller The created controller.
   */
  public function onControllerCreated(\ultimo\mvc\Controller $controller);
}