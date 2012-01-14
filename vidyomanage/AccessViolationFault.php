<?php

class AccessViolationFault
{

  /**
   * 
   * @var Exception $AccessViolationFault
   * @access public
   */
  public $AccessViolationFault;

  /**
   * 
   * @param Exception $AccessViolationFault
   * @access public
   */
  public function __construct($AccessViolationFault)
  {
    $this->AccessViolationFault = $AccessViolationFault;
  }

}
