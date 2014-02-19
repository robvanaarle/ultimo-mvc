<?php

namespace ultimo\mvc\exceptions;

class DispatchException extends ApplicationException {
  const ABSTRACT_MODULE = 104;
  const PARTIAL_MODULE = 105;
  const INVALID_REQUEST = 106;
  const PAGE_NOT_FOUND = 404;
}