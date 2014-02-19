<?php

namespace ultimo\mvc\routers;

class RuleBasedRouterException extends \Exception {
  const NO_ROUTE_FOR_REQUEST = 1;
  const UNKNOWN_ROUTE_NAME = 2;
}