<?php

namespace ultimo\mvc\plugins;

class PluginBroker {
  /**
   * The plugins.
   * @var array
   */
  protected $plugins = array();
  
  /**
   * The previously used plugin id as name for added plugins without names.
   * @var integer
   */
  protected $pluginId = 0;
  
  /**
   * Adds a plugin, optionally with a name.
   * @param Plugin $plugin The plugin to add.
   * @param string $name The name with which the plugin can retrieved.
   * @return PluginBroker This instance for fluid design.
   */
  public function addPlugin(Plugin $plugin, $name=null) {
    if ($name === null){
      $name = '_plugin' .$this->pluginId;
      $this->pluginId++;
    }
    
    $this->plugins[$name] = $plugin;
    return $this;
  }
  
  /**
   * Returns the plugin with the specified name.
   * @param string $name The name of the plugin to get.
   * @return Plugin The plugin with the specified name, or
   * null if no plugin with that name exists.
   */
  public function getPlugin($name) {
    if (!isset($this->plugins[$name])) {
      return null;
    }
    
    return $this->plugins[$name];
  }
  
  /**
   * Invokes a function on all plugins.
   * @param string $functionName The namme of the function to invoke on all
   * plugins.
   * @param array $parameters The parameters to invoke the functoin with.
   * @return PluginBroker This instance for fluid design.
   */
  public function invoke($functionName, array $parameters) {
    foreach ($this->plugins as $plugin) {
      call_user_func_array(array($plugin, $functionName), $parameters);
    }
  }
  
  /**
   * Removed the plugin with the specified name.
   * @param string $name The name of the plugin to remove.
   * @return PluginBroker This instance for fluid design.
   */
  public function removePlugin($name) {
    if (isset($this->plugins[$name])) {
      unset($plugins[$name]);
    }
    return $this;
  }
}