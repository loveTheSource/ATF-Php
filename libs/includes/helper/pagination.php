<?php

namespace ATFApp\Helper;

use ATFApp\Core;
use ATFApp\Exceptions;

class Pagination {
    
    private $table = null;
    private $selectCols = [];
    private $orderCols = [];

	public function __construct() { }

    /**
     * initialize pagination
     * 
     * @param string $table
     * @param array $selectCols
     * @param array $orderCols
     */
    public function init(string $table, array $selectCols, array $orderCols) {
        if (empty($table)) {
            throw new Exceptions\Custom('pagination: table may not be empty.');
        }
        if (empty($selectCols)) {
            throw new Exceptions\Custom('pagination: "selectCols" may not be empty.');
        }
        if (empty($orderCols)) {
            throw new Exceptions\Custom('pagination: "orderCols" may not be empty.');
        }

        $this->table = $table;
        $this->selectCols = $selectCols;
        $this->orderCols = $orderCols;
    }

    /**
     * get page of results
     * 
     * @param array $where ['col' => 'value', 'col2' => 'value2']
     * @param string $order column
     * @param string $sort ASC|DESC
     * @param int $start
     * @param int $limit
     * @param ATFApp\Models $modelClass
     */
    public function getPage(array $where, $order, $sort, $start, $limit, $modelClass=null) {
        $sort = (strtoupper($sort) === "ASC") ? "ASC" : "DESC";
        $start = (int)$start;
        $limit = (int)$limit;

        $dbSelector = Core\Includer::getDbSelector();
        $dbSelector->select($this->selectCols)->from($this->table);
        foreach ($where as $col => $value) {
            if (in_array($col, $this->selectCols)) {
                $dbSelector->where($col, $value);
            }
        }
        if (!in_array($order, $this->orderCols) || !in_array($order, $this->selectCols)) {
            $order = $this->selectCols[0];
        }
        
        $dbSelector->orderBy($order, $sort);
        $dbSelector->limit($start, $limit);

        $resultsCount = $dbSelector->countResults();

        if (!is_null($modelClass)) {
            $results = $dbSelector->fetchResults('class', $modelClass);
        } else {
            $results = $dbSelector->fetchResults();
        }

        return [
            'results' => $results,
            'count' => $resultsCount
        ];
    }
}