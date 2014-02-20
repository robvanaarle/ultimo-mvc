<?php

namespace ultimo\mvc;

use ultimo\mvc\exceptions\ModuleException;

class Module {
  
  /**
   * The application the module is part of.
   * @var Application
   */
  protected $application;
  
  /**
   * The view the module must use to render.
   * @var View
   */
  protected $view;
  
  /**
   * Whether the module is abstract. An abstract module can not be dispatch
   * directly.
   * @var boolean
   */
  protected $isAbstract = false;
  
  /**
   * Whether the module is final. A final module cannot be a parent of another
   * module.
   * @var boolean
   */
  protected $isFinal = false;
  
  /**
   * Whether the module is partial. A partial module cannot be dispatched
   * directly, and will be part of another module.
   * @var boolean
   */
  protected $isPartial = false;
  
  /**
   * The parent module, or null if this module has no parent.
   * @var Module
   */
  protected $parent = null;
  
  /**
   * All partial modules what become part of this module, as if their files
   * where physically in this module.
   * @var array of Module objects
   */
  protected $partials = array();
  
  /**
   * The cached namespace of this module.
   * @var string
   */
  protected $namespace;
  
  /**
   * The cached name of this module.
   * @var string
   */
  protected $name;
  
  /**
   * Cached instances of controllers.
   * @var array
   */
  protected $controllers = array();
  
  /**
   * Cached instances of facades.
   * @var array
   */
  protected $facades = array();
  
  /**
   * The base directory of the module.
   * @var string
   */
  protected $basePath;
  
  /**
   * The plugin broker for module plugins.
   * @var plugins\PluginBroker
   */
  protected $pluginBroker;
  
  /**
   * Constructor.
   * @param Application $application The application this module is constructed
   * for.
   */
  public function __construct(Application $application) {
    $this->application = $application;
    
    // cache this module namespace and name
    $thisClass = get_called_class();
    $this->namespace = substr($thisClass, 0, strrpos($thisClass, '\\'));
    $this->name = substr($this->namespace, strrpos($this->namespace, '\\')+1);
    
    $this->pluginBroker = new plugins\PluginBroker();
    $this->init();
  }
  
  /**
   * Called at the end of the constructor. The module must define itself here
   * and can initialize data.
   */
  protected function init() { }
  
  /**
   * Returns the base directory of the module.
   * @return string The base directory of the module.
   */
  public function getBasePath() {
    if ($this->basePath === null) {
      $reflectionClass = new \ReflectionClass($this);
      $this->basePath = dirname($reflectionClass->getFileName());
    }
    return $this->basePath;
  }
  
  /**
   * The view the module must use to render.
   * @param View $view The view the module must use to render.
   * @return Module This instance for fluid design.
   */
  public function setView(View $view) {
    $this->view = $view;
    return $this;
  }
  
  /**
   * Return the view the module must use to render.
   * @return View The view the module must use to render.
   */
  public function getView() {
    return $this->view;
  }
  
  /**
   * Returns the application this module belongs to.
   * @return Application The application this module belongs to.
   */
  public function getApplication() {
    return $this->application;
  }

  /**
   * Returns the controller with the specified name.
   * @param string $controllerName The name of of the controller to get.
   * @return Controller The controller with the specified name, or null if no
   * controller with that name exists.
   */
  public function getController($controllerName) {
    if ($controllerName === null) {
      $controllerName = $this->indexControllerName;
    }
    
    // make sure the first character of the controller is capitalized
    $nameElems = explode('\\', $controllerName);
    $nameElems[count($nameElems)-1] = ucfirst($nameElems[count($nameElems)-1]);
    $controllerName = implode('\\', $nameElems);
    
    // if the module is not cached, create one and cache it
    if (!isset($this->controllers[$controllerName])) {
      
      $qName = 'controllers\\' . $controllerName . 'Controller';
      
      //$qName = 'controllers\\' . ucfirst(strtolower($controllerName)) . 'Controller';
      $fqName = $this->getFQName($qName);
      if ($fqName === null) {
        return null;
      }
      $controller = new $fqName($controllerName, $this);
      
      // call 'onControllerCreated' on all module plugins
      $this->pluginBroker->invoke('onControllerCreated', array($controller));
      $this->controllers[$controllerName] = $controller;
    }
    
    return $this->controllers[$controllerName];
  }
  
  /**
   * Returns an the facade with the specified name.
   * @param string $facadeName The name of of the facade to get.
   * @return Facade The facade with the specified name, or null if no facade
   * with that name exists.
   */
  public function getFacade($facadeName) {
    
    // if the interface is not cached, create one and cache it
    if (!isset($this->facades[$facadeName])) {
      $qName = 'facades\\'.$facadeName;
      $fqName = $this->getFQName($qName);
      if ($fqName === null) {
        return null;
      }
      $this->facades[$facadeName] = new $fqName($this);
    }
    
    return $this->facades[$facadeName];
  }
  
  /**
   * Returns the fully qualified name of qualified name of a module resource.
   * This function is essential for module inheritance. It searches for the
   * resource in this specific module. If not found, it searches its used
   * partial modules. If still not found, it asks its parent (recusively) for
   * the fully qualified name.
   * @param unknown_type $qName The qualified name of the module resource.\
   * @return string The fully qualified name, or null if the resource does not
   * exist in this module.
   */
  public function getFQName($qName) {
    
    // check if the resource exists in this module's namespace
    $fqName = $this->namespace . '\\' . $qName;
    if (class_exists($fqName)) {
      return $fqName;
    }
    
    // ask the partials for the resource
    foreach ($this->partials as $partial) {
      $fqName = $partial->getFQName($qName);
      if ($fqName !== null) {
        return $fqName;
      }
    }
    
    // ask the parent
    if ($this->parent !== null) {
      return $this->parent->getFQName($qName);
    }
    
    return null;
  }
  
  /**
   * Returns the namespace of the module.
   * @return string The namespace of the module.
   */
  public function getNamespace() {
    return $this->namespace;
  }
  
  /**
   * Returns the name of the module.
   * @return string the name of the module.
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * Returns the parent modules (This parent of this module, the parent of
   * the parent, etc.)
   * @param integer $skip The number of parents to skip. Defaults to one, which
   * skips this (child) module itself.
   * @return array An array of Module objects with the parents of this module.
   */
  public function getParents($skip=1) {
    $parents = array();
    
    $parent = $this;
    while($parent !== null) {
      if ($skip > 0) {
        $skip--;
        continue;
      }
      $parents[] = $parent;
    }
    
    return $parents();
  }
  
  /**
   * Adds an module plugin, optionally with a name.
   * @param plugins\ModulePlugin $plugin The module plugin to add.
   * @param string $name The name with which the plugin can retrieved.
   * @return Module This instance for fluid design.
   */
  public function addPlugin(plugins\ModulePlugin $plugin, $name=null) {
    $this->pluginBroker->addPlugin($plugin, $name);
    return $this;
  }
  
  /**
   * Returns the plugin with the specified name.
   * @param string $name The name of the plugin to get.
   * @return plugins\ModulePlugin The plugin with the specified name, or
   * null if no plugin with that name exists.
   */
  public function getPlugin($name) {
    return $this->pluginBroker->getPlugin($name);
  }
  
  /**
   * Returns whether the module is final. A final module cannot be a parent of 
   * another module.
   * @return boolean Whether the module is final.
   */
  public function isFinal() {
    return $this->isFinal;
  }
  
  /**
   * Sets whether the module is final. A final module cannot be a parent of 
   * another module.
   * @param boolean $isFinal Whether the module is final.
   * @return Module This instance for fluid design.
   */
  public function setFinal($isFinal) {
    $this->isFinal = $isFinal;
    return $this;
  }
  
  /**
   * Returns whether the module is abstract. An abstract module can not be
   * dispatch directly.
   * @return boolean Whether the module is abstract. 
   */
  public function isAbstract() {
    return $this->isAbstract;
  }
  
  /**
   * Sets whether thie module is abstract. An abstract module can not be
   * dispatch directly.
   * @param boolean $isAbstract Whether the module is abstract. 
   * @return Module This instance for fluid design.
   */
  public function setAbstract($isAbstract) {
    $this->isAbstract = $isAbstract;
    return $this;
  }
  
  /**
   * Returns whether the module is partial. A partial module cannot be
   * dispatched directly, and will be part of another module.
   * @return boolean Whether this module is partial.
   */
  public function isPartial() {
    return $this->isPartial;
  }
  
  /**
   * Sets whether the module is partial. A partial module cannot be
   * dispatched directly, and will be part of another module.
   * @param boolean $isPartial Whether the module is partial.
   * @return Module This instance for fluid design.
   */
  public function setPartial($isPartial) {
    $this->isPartial = $isPartial;
    return $this;
  }
  
  /**
   * Sets the parent module of the module.
   * @param Module $parent The parent module of the module, or null to have
   * the module have no parent.
   * @return Module This instance for fluid design.
   */
  public function setParent(Module $parent=null) {
    if ($parent->isFinal()) {
      throw new ModuleException("Module '{$this->namespace}' cannot extend from final module '{$parent->getNamespace()}'.");
    }
    $this->parent = $parent;
    return $this;
  }
  
  /**
   * Returns the parent module.
   * @return Module The parent module, or null if the module has no parent.
   */
  public function getParent() {
    return $this->parent;
  }
  
  /**
   * Adds a partial module to the module.
   * @param Module $partial The partial module to add.
   * @return Module This instance for fluid design.
   */
  public function addPartial(Module $partial) {
    if (!$partial->isPartial()) {
      throw new ModuleException("Module '{$this->namespace}' cannot complete non-partial module '{$partial->getNamespace()}'.");
    }
    $this->partials[] = $partial;
    return $this;
  }
  
  /**
   * Returns the partial modules of the module.
   * @param boolean $askParent Whether to return the partial modules of the
   * parent modules..
   * @return array An array of partial Module objects with the partial modules
   * of the module.
   */
  public function getPartials($askParents=false) {
    if ($askParents && $this->parent !== null) {
      return array_merge($this->parent->getPartials(), $this->partials);
    } else {
      return $this->partials;
    }
  }
  
  /**
   * Returns whether the module is an instance of the specified namespace.
   * @param string $namespace Module namespace.
   * @return boolean Whether the module is an instance of the specified
   * namespace.
   */
  public function isInstanceOf($namespace) {
    $namespace = ltrim($namespace, '\\');
    if ($this->namespace == $namespace) {
      return true;
    } elseif ($this->getParent() !== null) {
      return $this->getParent()->isInstanceOf($namespace);
    } else {
      return false;
    }
  }
  
}