<?php

namespace ATFApp\Helper;

use ATFApp\Core;
use ATFApp\Exceptions;

class Pagination {
    
    private $table = null;
    private $selectCols = [];
    private $orderCols = [];
    private $limit = null;

    // checkboxes in form
    private $addCheckboxes = false;
    private $checkboxValueColumn = null;
    private $checkboxFormAction = null;
    private $checkboxFormOptions = [];
    private $checkboxFormSubmit = null;

    // options in row
    private $addOptions = false;
    private $optionValueColumn = null;     // column value to append to link
    private $optionBtns = [];               // ['link1' => 'text1', 'link2' => 'text2']
    
	public function __construct() { }

    /**
     * initialize pagination
     * 
     * @param string $table
     * @param int $limit
     * @param array $selectCols
     * @param array $orderCols
     */
    public function init(string $table, array $selectCols, int $limit = 10, array $orderCols) {
        if (empty($table)) {
            throw new Exceptions\Custom('pagination: table may not be empty.');
        }
        if (empty($selectCols)) {
            throw new Exceptions\Custom('pagination: "selectCols" may not be empty.');
        }
        if (empty($limit)) {
            throw new Exceptions\Custom('pagination: "limit" may not be empty.');
        }
        if (!is_array($orderCols)) {
            throw new Exceptions\Custom('pagination: "orderCols" must be an array.');
        }

        $limit = (int)$limit;
        if ($limit < 1) {
            $limit = 10;
        }

        $this->table = $table;
        $this->limit = $limit;
        $this->selectCols = $selectCols;
        $this->orderCols = $orderCols;
    }

    /**
     * get page of results
     * 
     * @param int $page 
     * @param array $where ['col' => 'value', 'col2' => 'value2']
     * @param string $order column
     * @param string $sort ASC|DESC
     * @param ATFApp\Models $modelClass
     */
    public function getPage($page, array $where, $order, $sort, $modelClass=null) {
        // sort
        $sort = (strtoupper($sort) === "ASC") ? "ASC" : "DESC";
        // limit
        $limit = $this->limit;
        // page
        $page = (int)$page;
        if ($page < 1) $page = 1;
        $start = ($page -1) * $limit;

        $dbSelector = Core\Includer::getDbSelector();
        $dbSelector->select($this->selectCols)->from($this->table);
        foreach ($where as $col => $value) {
            $dbSelector->where($col, $value);
        }
        if (!in_array($order, $this->orderCols) || !in_array($order, $this->selectCols)) {
            $order = $this->selectCols[0];
        }
        
        $dbSelector->orderBy($order, $sort);
        $dbSelector->limit($start, $limit);

        $resultsCount = $dbSelector->countResults();

        // pagination infos
        $totalPages = (int)ceil($resultsCount / $limit);
        $firstPage = 1;
        $lastPage = $totalPages;
        $previousPage = false; // no previous page
        $nextPage = false; // no next page
        $currentPage = $page;
        if ($currentPage > 1) {
            $previousPage = $currentPage - 1;
            if ($previousPage > $totalPages) {
                $previousPage = $totalPages;
            }
        }
        if ($currentPage < $totalPages) {
            $nextPage = $currentPage + 1;
        }
        if ($firstPage === $previousPage || $firstPage === $currentPage) {
            // prevent duplicate
            $firstPage = false;
        }
        if ($lastPage === $nextPage || $lastPage === $currentPage) {
            // prevent duplicate
            $lastPage = false;
        }

        $results = [];
        if ($currentPage <= $totalPages) {
            if (!is_null($modelClass)) {
                $results = $dbSelector->fetchResults('class', $modelClass);
            } else {
                $results = $dbSelector->fetchResults();
            }
            if (!$results) {
                $results = [];
            }
        } else {
            // remove links
            $lastPage = false;
        }


        return [
            'results' => $results,
            'count' => $resultsCount,
            'firstPage' => $firstPage,
            'lastPage' => $lastPage,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'nextPage' => $nextPage,
            'previousPage' => $previousPage,
            'offset' => $start,
            'showPagination' => ($nextPage !== false || $previousPage !== false),
            'addCheckboxes' => $this->addCheckboxes,
            'checkboxFormOptions' => $this->checkboxFormOptions,
            'checkboxValueColumn' => $this->checkboxValueColumn,
            'checkboxFormAction' => $this->checkboxFormAction,
            'checkboxFormSubmit' => $this->checkboxFormSubmit,
            'addOptions' => $this->addOptions,
            'optionBtns' => $this->optionBtns,
            'optionValueColumn' => $this->optionValueColumn
        ];
    }

    public function setAddCheckboxes($checkboxFormAction, $checkboxValueColumn, $formOptions, $submitBtnText) {
        $this->addCheckboxes = true;
        $this->checkboxFormAction = $checkboxFormAction;
        $this->checkboxValueColumn = $checkboxValueColumn;
        $this->checkboxFormOptions = $formOptions;
        $this->checkboxFormSubmit = $submitBtnText;
    }

    public function setAddOptions($optionValueColumn, $optionBtns) {
        $this->addOptions = true;
        $this->optionValueColumn = $optionValueColumn;
        $this->optionBtns = $optionBtns;
    }
}