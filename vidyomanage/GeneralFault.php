<?php

class GeneralFault
{

  /**
   * 
   * @var Exception $GeneralFault
   * @access public
   */
  public $GeneralFault;

  /**
   * 
   * @param Exception $GeneralFault
   * @access public
   */
  public function __construct($GeneralFault)
  {
    $this->GeneralFault = $GeneralFault;
  }

}
