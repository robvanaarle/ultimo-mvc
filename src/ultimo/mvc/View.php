<?php

namespace ultimo\mvc;

interface View {

  /**
   * Renders the template with the specified name and returns the rendered data.
   * @param string $templateName The name of the template to render.
   * @return string The rendered data.
   */
  public function render($templateName);
  
  /**
   * Adds a path to a directory where templates and supporting classes can be
   * found.
   * @param string $path The path to the directory where template and supporting
   * classes can be found.
   * @param string $namespace The namespace of the supporting classes in the
   * directory.
   * @return View This instance for fluid design.
   */
  public function addBasePath($path, $namespace);
  
  /**
   * Returns the directories with namespaces where templates and supporting
   * classes can be found.
   * @return array An array of hashtables with the directory path stored in the
   * key 'path', and the namespace stored in key 'namespace'.
   */
  public function getBasePaths();
}