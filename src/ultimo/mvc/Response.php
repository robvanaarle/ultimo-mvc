<?php

namespace ultimo\mvc;

class Response extends \ultimo\net\http\Response {
  
  /**
   * The exceptions.
   * @var array
   */
  protected $exceptions;
  
  public function __construct() {
    parent::__construct();
    $this->clearExceptions();
    
    // set default Content-type
    $this->addHeader(new \ultimo\net\http\headers\ContentType('text', 'html', array('charset' => 'utf-8')));
  }
  
  /**
   * Adds an exception.
   * @param \Exception $exception The exception.
   * @return Response This instance for fluid design.s
   */
  public function addException(\Exception $exception) {
    $this->exceptions[] = $exception;
    return $this;
  }
  
  /**
   * Returns whether the response has exceptions.
   * @return boolean Whether the response has exceptions.
   */
  public function hasExceptions() {
    return !empty($this->exceptions);
  }
  
  /**
   * Returns the exceptions.
   * @return array The exceptions.
   */
  public function getExceptions() {
    return $this->exceptions;
  }
  
  /**
   * Clears the exceptions.
   * @return Response This instance for fluid design.
   */
  public function clearExceptions() {
    $this->exceptions = array();
    return $this;
  }
  
  /**
   * Redirect to a url with a http response code indicating a redirect.
   * @param string $url The url to redirect to.
   * @param integer $statusCode The http status code to indicate the redirect.
   * @return Response This instance for fluid design.
   */
  public function redirect($url, $statusCode=302) {
    $this->setHeader('Location', $url)
         ->setStatusCode($statusCode);
    return $this;
  }
  
  /**
   * Returns whether the response represents a redirect.
   * @return boolean Whether the response indicates a redirect.
   */
  public function isRedirect() {
    return $this->getStatusCode() >= 300 && $this->getStatusCode() <= 399;
  }
  
  public function appendFile($filePath) {
    $this->appendBody(new \ultimo\net\http\responsebodies\File($filePath));
    return $this;
  }
  
}