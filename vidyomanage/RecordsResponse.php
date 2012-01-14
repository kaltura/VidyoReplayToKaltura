<?php

class RecordsResponse
{

  /**
   * 
   * @var Record $records
   * @access public
   */
  public $records;

  /**
   * 
   * @param Record $records
   * @access public
   */
  public function __construct($records)
  {
    $this->records = $records;
  }

}
