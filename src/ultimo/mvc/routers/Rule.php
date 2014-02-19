<?php

namespace ultimo\mvc\routers;

interface Rule {
  public function matches(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request);
  
  public function route(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request);
  
  public function unroute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request);
}