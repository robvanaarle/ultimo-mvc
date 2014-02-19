<?php

namespace ultimo\mvc;

abstract class Bootstrap {
  
  /**
   * The application the bootstrap runs for.
   * @var ultimo\mvc\Application
   */
  protected $application;
  
  /**
   * Constructor.
   * @param Application $application The application the bootstrap runs for.
   */
  public function __construct(Application $application) {
    $this->application = $application;
  }
  
  /**
   * Runs the bootstrap.
   */
  abstract public function run();
}