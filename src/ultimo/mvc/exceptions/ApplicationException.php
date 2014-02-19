<?php

namespace ultimo\mvc\exceptions;

class ApplicationException extends MvcException {
  const INVALID_BOOTSTRAP_TYPE = 1;
  const MODULECLASS_NOT_FOUND = 2;
  const NOT_DISPATCHING = 3;
}