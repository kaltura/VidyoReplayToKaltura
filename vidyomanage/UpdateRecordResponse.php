<?php

class UpdateRecordResponse
{

  /**
   * 
   * @var OK $OK
   * @access public
   */
  public $OK;

  /**
   * 
   * @param OK $OK
   * @access public
   */
  public function __construct($OK)
  {
    $this->OK = $OK;
  }

}
