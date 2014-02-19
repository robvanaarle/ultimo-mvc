<?php

namespace ultimo\mvc\plugins;

interface ApplicationPlugin extends Plugin {
  
  /**
   * Called after the plugin is added to an application.
   * @param \ultimo\mvc\Application $application The application the plugin is
   * added to.
   */
  public function onPluginAdded(\ultimo\mvc\Application $application);
  
  /**
   * Called after a module is created by the application.
   * @param \ultimo\mvc\Module $module The created module.
   */
  public function onModuleCreated(\ultimo\mvc\Module $module);
  
  /**
   * Called before routing.
   * @param \ultimo\mvc\Application $application The application calling the
   * router.
   * @param \ultimo\mvc\Request $request The unrouted request.
   */
  public function onRoute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request);
  
  /**
   * Called after routing.
   * @param \ultimo\mvc\Application $application The application that called the
   * router.
   * @param \ultimo\mvc\Request $request The routed request.
   */
  public function onRouted(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request=null);
  
  /**
   * Called before dispatch.
   * @param \ultimo\mvc\Application $application The application dispathcing.
   */
  public function onDispatch(\ultimo\mvc\Application $application);
  
  /**
   * Called after dispatch.
   * @param \ultimo\mvc\Application $application The application that
   * dispatched.
   */
  public function onDispatched(\ultimo\mvc\Application $application);
}