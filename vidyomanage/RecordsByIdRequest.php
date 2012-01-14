<?php

class RecordsByIdRequest
{

  /**
   * 
   * @var int $id
   * @access public
   */
  public $id;

  /**
   * 
   * @param int $id
   * @access public
   */
  public function __construct($id)
  {
    $this->id = $id;
  }

}
