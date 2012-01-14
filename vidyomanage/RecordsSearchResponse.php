<?php

class RecordsSearchResponse
{

  /**
   * 
   * @var int $allVideosCount
   * @access public
   */
  public $allVideosCount;

  /**
   * 
   * @var int $searchCount
   * @access public
   */
  public $searchCount;

  /**
   * 
   * @var int $myVideosCount
   * @access public
   */
  public $myVideosCount;

  /**
   * 
   * @var int $webcastCount
   * @access public
   */
  public $webcastCount;

  /**
   * 
   * @var int $newCount
   * @access public
   */
  public $newCount;

  /**
   * 
   * @var int $privateCount
   * @access public
   */
  public $privateCount;

  /**
   * 
   * @var int $organizationalCount
   * @access public
   */
  public $organizationalCount;

  /**
   * 
   * @var int $publicCount
   * @access public
   */
  public $publicCount;

  /**
   * 
   * @var Record $records
   * @access public
   */
  public $records;

  /**
   * 
   * @param int $allVideosCount
   * @param int $searchCount
   * @param int $myVideosCount
   * @param int $webcastCount
   * @param int $newCount
   * @param int $privateCount
   * @param int $organizationalCount
   * @param int $publicCount
   * @param Record $records
   * @access public
   */
  public function __construct($allVideosCount, $searchCount, $myVideosCount, $webcastCount, $newCount, $privateCount, $organizationalCount, $publicCount, $records)
  {
    $this->allVideosCount = $allVideosCount;
    $this->searchCount = $searchCount;
    $this->myVideosCount = $myVideosCount;
    $this->webcastCount = $webcastCount;
    $this->newCount = $newCount;
    $this->privateCount = $privateCount;
    $this->organizationalCount = $organizationalCount;
    $this->publicCount = $publicCount;
    $this->records = $records;
  }

}
