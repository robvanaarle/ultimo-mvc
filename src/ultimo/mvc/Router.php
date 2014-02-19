<?php

namespace ultimo\mvc;

interface Router {
  /**
   * Routes a request by adding the module, controller, action and params to the
   * request.
   * @param Application $application The application associated with the
   * routing.
   * @param Request $request The request to route.
   * @return Request The routed request.
   */
  public function route(Application $application, Request $request);
  
  /**
   * Unroutes a request by adding the url to the request.
   * @param Application $application The application associated with the
   * routing.
   * @param Request $request The request to unroute.
   * @param mixed $metadata Metadata for unrouting.
   * @return Request The unrouted request.
   */
  public function unroute(Application $application, Request $request, $metadata = null);

}