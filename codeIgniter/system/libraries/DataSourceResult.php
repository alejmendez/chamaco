<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class CI_DataSourceResult {
    protected $db;

    protected $stringOperators = array(
        'eq' => 'LIKE',
        'neq' => 'NOT LIKE',
        'doesnotcontain' => 'NOT LIKE',
        'contains' => 'LIKE',
        'startswith' => 'LIKE',
        'endswith' => 'LIKE'
    );

    protected $operators = array(
        'eq' => '=',
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
        'neq' => '!='
    );

    protected $aggregateFunctions = array(
        'average' => 'AVG',
        'min' => 'MIN',
        'max' => 'MAX',
        'count' => 'COUNT',
        'sum' => 'SUM'
    );
    
    protected $conf = array(
		'db' => false
	);

   	function __construct($conf = false){
   		$this->conf = array_merge($this->conf, $conf);
   		
   		if ($this->conf['db'] === false){
   			$ci = &get_instance();
   			$this->conf['db'] = $ci->db;
   		}
   		
   		$this->db = $this->conf['db'];
    }

    protected function total($tableName, $properties, $opciones) {
    	$this->db->seleccionar($tableName, 'COUNT(*) AS total', $opciones);
        return (int) $this->db->ur('total');
    }

    protected function group($data, $groups, $table, $request, $propertyNames) {
        if (count($data) > 0) {
            return $this->groupBy($data, $groups, $table, $request, $propertyNames);
        }
        return array();
    }

    protected function mergeSortDescriptors($request) {
    	$sort = isset($request['sort']) && count($request['sort']) ? $request['sort'] : array();
        $groups = isset($request['group']) && count($request['group']) ? $request['group'] : array();

        return array_merge($sort, $groups);
    }

    protected function groupBy($data, $groups, $table, $request, $propertyNames) {
        if (count($groups) > 0) {
            $field = $groups[0]['field'];
            $count = count($data);
            $result = array();
            $value = $data[0][$field];
            $aggregates = isset($groups[0]['aggregates']) ? $groups[0]['aggregates']: array();

            $hasSubgroups = count($groups) > 1;
            $groupItem = $this->createGroup($field, $value, $hasSubgroups, $aggregates, $table, $request, $propertyNames);

            for ($index = 0; $index < $count; $index++) {
                $item = $data[$index];
                if ($item[$field] != $value) {
                    if (count($groups) > 1) {
                        $groupItem['items'] = $this->groupBy($groupItem['items'], array_slice($groups, 1), $table, $request, $propertyNames);
                    }

                    $result[] = $groupItem;

                    $groupItem = $this->createGroup($field, $data[$index][$field], $hasSubgroups, $aggregates, $table, $request, $propertyNames);
                    $value = $item[$field];
                }
                $groupItem['items'][] = $item;
            }

            if (count($groups) > 1) {
                $groupItem['items'] = $this->groupBy($groupItem['items'], array_slice($groups, 1), $table, $request, $propertyNames);
            }

            $result[] = $groupItem;
            return $result;
        }
        return array();
    }

    protected function addFilterToRequest($field, $value, $request) {
        $filter = (object)array(
            'logic' => 'and',
            'filters' => array(
                (object)array(
                    'field' => $field,
                    'operator' => 'eq',
                    'value' => $value
                ))
            );

        if (isset($request->filter)) {
            $filter->filters[] = $request->filter;
        }

        return (object) array('filter' => $filter);
    }

    protected function addFieldToProperties($field, $propertyNames) {
        if (!in_array($field, $propertyNames)) {
            $propertyNames[] = $field;
        }
        return $propertyNames;
    }

    protected function createGroup($field, $value, $hasSubgroups, $aggregates, $table, $request, $propertyNames) {
        if (count($aggregates) > 0) {
            $request = $this->addFilterToRequest($field, $value, $request);
            $propertyNames = $this->addFieldToProperties($field, $propertyNames);
        }

        $groupItem = array(
            'field' => $field,
            'aggregates' => $this->calculateAggregates($table, $aggregates, $request, $propertyNames),
            'hasSubgroups' => $hasSubgroups,
            'value' => $value,
            'items' => array()
        );

        return $groupItem;
    }

    protected function calculateAggregates($table, $aggregates, $request, $propertyNames) {
        $count = count($aggregates);

        if (count($aggregates) > 0) {
            $functions = array();

            for ($index = 0; $index < $count; $index++) {
                $aggregate = $aggregates[$index];
                $name = $this->aggregateFunctions[$aggregate->aggregate];
                $functions[] = $name.'('.$aggregate->field.') as '.$aggregate->field.'_'.$aggregate->aggregate;
            }

            $sql = sprintf('SELECT %s FROM %s', implode(', ', $functions), $table);

            if (isset($request->filter)) {
                $sql .= $this->filter($propertyNames, $request->filter);
            }

            $statement = $this->db->prepare($sql);

            if (isset($request->filter)) {
                $this->bindFilterValues($statement, $request->filter);
            }

            $statement->execute();

            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            return $this->convertAggregateResult($result[0]);
        }
        return (object)array();
    }

    protected function convertAggregateResult($propertyNames) {
        $result = array();

        foreach($propertyNames as $property => $value) {
            $item = array();
            $split = explode('_', $property);
            $field = $split[0];
            $function = $split[1];
            if (array_key_exists($field, $result)) {
                $result[$field][$function] = $value;
            } else {
                $result[$field] = array($function => $value);
            }
        }

        return $result;
    }

    protected function sort($propertyNames, $sort) {
        $count = count($sort);

        $sql = '';

        if ($count > 0) {
            $order = array();

            for ($index = 0; $index < $count; $index ++) {
                $field = $sort[$index]['field'];

                if (in_array($field, $propertyNames)) {
                    $dir = 'ASC';

                    if ($sort[$index]['dir'] == 'desc') {
                        $dir = 'DESC';
                    }

                    $order[] = "$field $dir";
                }

            }

            $sql .= implode(',', $order);
        }

        return $sql;
    }

    protected function where($properties, $filter, $all) {
        if (isset($filter['filters'])) {
            $logic = $filter['logic'] == 'or' ? ' OR ' : ' AND ';
            $filters = $filter['filters'];

            $where = array();

            for ($index = 0; $index < count($filters); $index++) {
                $where[] = $this->where($properties, $filters[$index], $all);
            }
			//var_dump($where);
            $where = implode($logic, $where);

            return "($where)";
        }
		
        $field = $filter['field'];

        $propertyNames = $this->propertyNames($properties);

        if (in_array($field, $propertyNames)) {
            $type = 'string';

            $index = array_search($filter, $all);
			//var_dump($filter);
			
            //$value = ':filter$index'3;
            $value = $filter['value'];
            
            if (isset($properties[$field])) {
                $type = $properties[$field]['type'];
                $value = $this->db->qstr($value);
            }elseif ($this->isDate($filter['value'])) {
                $type = 'date';
            }elseif (array_key_exists($filter['operator'], $this->operators) && !$this->isString($filter['value'])) {
                $type = 'number';
                $value = $filter['value'];
            }

            if ($type == "date") {
                $field = "date($field)";
                $value = "date(" . $this->db->qstr(date('Y-m-d H:i:s', strtotime($value))) . ")";
            }

            if ($type == "string"){
                $operator = $this->stringOperators[$filter['operator']];
                
                if ($this->db->driver === 'postgres' and $operator === 'LIKE'){
                	$operator = 'ILIKE';
                }
                
                $value = $this->db->qstr(strpos($filter['value'], '%') === false ? '%' . $filter['value'] . '%' : $filter['value']);
            }else{
                $operator = $this->operators[$filter['operator']];
            }

            return "$field $operator $value";
        }
    }

    protected function flatten(&$all, $filter) {
        if (isset($filter['filter'])) {
            $filters = $filter['filter'];

            for ($index = 0; $index < count($filters); $index++) {
                $this->flatten($all, $filters[$index]);
            }
        } else {
            $all[] = $filter;
        }
    }

    protected function filter($properties, $filter) {
        $all = array();
		$this->flatten($all, $filter);
		$where = $this->where($properties, $filter, $all);

        return $where;
    }

    protected function isDate($value) {
        $result = date_parse($value);
        return $result['error_count'] < 1;
    }

    protected function isString($value) {
        return !is_bool($value) && !is_numeric($value) && !$this->isDate($value);
    }

    protected function propertyNames($properties) {
        $names = array();

        foreach ($properties as $key => $value) {
            if (is_string($value)) {
                $names[] = $value;
            } else {
                $names[] = $key;
            }
        }

        return $names;
    }

    protected function bindFilterValues($statement, $filter) {
        $filters = array();
        $this->flatten($filters, $filter);

        for ($index = 0; $index < count($filters); $index++) {
            $value = $filters[$index]['value'];
            $operator = $filters[$index]['operator'];
            $date = date_parse($value);

            if ($operator == 'contains' || $operator == 'doesnotcontain') {
                $value = "%$value%";
            } else if ($operator == 'startswith') {
                $value = "$value%";
            } else if ($operator == 'endswith') {
                $value = "%$value";
            }

            $statement->bindValue(":filter$index", $value);
        }
    }

    public function read($table, $properties, $request = null) {
        $opciones = $result = array();
        
        $propertyNames = $this->propertyNames($properties);
        
        if (isset($request['filter'])) {
        	if (is_array($request['filter'])){
        		$opciones['w'] = $this->filter($properties, $request['filter']);
        	}else{
        		unset($request['filter']);
        	}
        }
        
        $result['total'] = $this->total($table, $properties, $opciones);
		
        $sort = $this->mergeSortDescriptors($request);

        if (count($sort) > 0) {
        	$opciones['o'] = $this->sort($propertyNames, $sort);
        }
		
        if (isset($request['skip']) && isset($request['take'])) {
        	$opciones['l'] = array($request['take'], $request['skip']);
        }
		
        $this->db->seleccionar($table, implode(', ', $propertyNames), $opciones);
		//$this->db->uq();
        //$data = $this->db->rs->GetAssoc();
        
        $data = array();
		foreach ($this->db->rs as $row) {
			$data[] = $row;
		}

        if (isset($request['group']) && count($request['group']) > 0) {
            $data = $this->group($data, $request['group'], $table, $request, $propertyNames);
            $result['groups'] = $data;
        } else {
            $result['data'] = $data;
        }

        if (isset($request->aggregate)) {
            $result["aggregates"] = $this->calculateAggregates($table, $request->aggregate, $request, $propertyNames);
        }

        return $result;
    }

    public function readJoin($table, $joinTable, $properties, $key, $column, $request = null) {
        $result = $this->read($table, $properties, $request);

        for ($index = 0, $count = count($result['data']); $index < $count; $index++) {
            $sql = sprintf('SELECT %s FROM %s WHERE %s = %s', $column, $joinTable, $key, $result['data'][$index][$key]);

            $statement = $this->db->prepare($sql);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_NUM);
            $result['data'][$index]['Atendees'] = $data;
        }

        return $result;
    }
}