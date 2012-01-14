<?php

class RecordsSearchRequest
{

  /**
   * 
   * @var string $tenantName
   * @access public
   */
  public $tenantName;

  /**
   * 
   * @var string $roomFilter
   * @access public
   */
  public $roomFilter;

  /**
   * 
   * @var string $usernameFilter
   * @access public
   */
  public $usernameFilter;

  /**
   * 
   * @var string $query
   * @access public
   */
  public $query;

  /**
   * 
   * @var recordScopeFilter $recordScope
   * @access public
   */
  public $recordScope;

  /**
   * 
   * @var sortBy $sortBy
   * @access public
   */
  public $sortBy;

  /**
   * 
   * @var sortDirection $dir
   * @access public
   */
  public $dir;

  /**
   * 
   * @var int $limit
   * @access public
   */
  public $limit;

  /**
   * 
   * @var int $start
   * @access public
   */
  public $start;

  /**
   * 
   * @var boolean $webcast
   * @access public
   */
  public $webcast;

  /**
   * 
   * @param string $tenantName
   * @param string $roomFilter
   * @param string $usernameFilter
   * @param string $query
   * @param recordScopeFilter $recordScope
   * @param sortBy $sortBy
   * @param sortDirection $dir
   * @param int $limit
   * @param int $start
   * @param boolean $webcast
   * @access public
   */
  public function __construct($tenantName, $roomFilter, $usernameFilter, $query, $recordScope, $sortBy, $dir, $limit, $start, $webcast)
  {
    $this->tenantName = $tenantName;
    $this->roomFilter = $roomFilter;
    $this->usernameFilter = $usernameFilter;
    $this->query = $query;
    $this->recordScope = $recordScope;
    $this->sortBy = $sortBy;
    $this->dir = $dir;
    $this->limit = $limit;
    $this->start = $start;
    $this->webcast = $webcast;
  }

}
