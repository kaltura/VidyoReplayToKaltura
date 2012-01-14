<?php

class ExceptionCustom
{

  /**
   * 
   * @var anyType $Exception
   * @access public
   */
  public $Exception;

  /**
   * 
   * @param anyType $Exception
   * @access public
   */
  public function __construct($Exception)
  {
    $this->Exception = $Exception;
  }

}
