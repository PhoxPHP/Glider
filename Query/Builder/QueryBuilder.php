<?php
namespace Glider\Query\Builder;

use RuntimeException;
use Glider\ClassLoader;
use Glider\Query\Parameters;
use InvalidArgumentException;
use Glider\Query\Builder\Type;
use Glider\Result\ResultMapper;
use Glider\Query\Builder\QueryBinder;
use Glider\Query\Builder\SqlGenerator;
use Glider\Connection\ConnectionManager;
use Glider\Platform\Contract\PlatformProvider;
use Glider\Result\Contract\ResultMapperContract;
use Glider\Statements\Contract\StatementProvider;
use Glider\Connectors\Contract\ConnectorProvider;
use Glider\Events\Subscribers\BuildEventsSubscriber;
use Glider\Query\Builder\Contract\QueryBuilderProvider;

class QueryBuilder implements QueryBuilderProvider
{

	/**
	* @var 		$connector
	* @access 	private
	*/
	private 	$connector;

	/**
	* @var 		$generator
	* @access 	public
	*/
	public 		$generator;

	/**
	* @var 		$bindings
	* @access 	protected
	*/
	protected 	$bindings = [];

	/**
	* @var 		$binder
	* @access 	protected
	*/
	protected 	$binder;

	/**
	* @var 		$sqlQuery
	* @access 	protected
	*/
	protected 	$sqlQuery;

	/**
	* @var 		$isCustomQuery
	* @access 	private
	* @static
	*/
	private static $isCustomQuery = false;

	/**
	* @var 		$strictType
	* @access 	private
	*/
	private 	$strictType;

	/**
	* @var 		$parameters
	* @access 	private
	*/
	private 	$parameters = [];

	/**
	* @var 		$provider
	* @access 	private
	*/
	private 	$provider;

	/**
	* @var 		$queryResult
	* @access 	private
	*/
	private 	$queryResult = null;

	/**
	* @var 		$statement
	* @access 	private
	*/
	private 	$statement;

	/**
	* @var 		$parameterBag
	* @access 	private
	*/
	private 	$parameterBag;

	/**
	* @var 		$resultMapper
	* @access 	private
	*/
	private 	$resultMapper;

	/**
	* @var 		$allowedOperators
	* @access 	protected
	*/
	protected 	$allowedOperators = [];

	/**
	* {@inheritDoc}
	*/
	public function __construct(ConnectionManager $connectionManager, PlatformProvider $platform)
	{
		$classLoader = new ClassLoader();
		$this->provider = $connectionManager->getConnection();
		$this->connector = $this->provider->connector();
		$this->statement = $this->provider->statement();
		$this->generator = $classLoader->getInstanceOfClass('Glider\Query\Builder\SqlGenerator');
		$this->binder = new QueryBinder();
		$this->parameterBag = new Parameters();
		$this->allowedOperators = ['AND', 'OR', '||', '&&']; // Will add more here.
	}

	/**
	* {@inheritDoc}
	*/
	public function rawQuery(String $query, Bool $useDefaultQueryMethod=true) : QueryBuilderProvider
	{
		QueryBuilder::$isCustomQuery = true;
		// Here we are creating a binding for raw sql queries.
		$this->sqlQuery = $this->binder->createBinding('sql', $query);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function select(...$arguments) : QueryBuilderProvider
	{
		if (sizeof($arguments) < 1) {
			$arguments = ['*'];
		}

		$this->sqlQuery = $this->binder->createBinding('select', $arguments);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function least(Array $arguments, String $alias) : QueryBuilderProvider
	{
		if (sizeof($arguments) > 0) {
			$this->sqlQuery .= $this->binder->alias('LEAST(' . implode(',', $arguments) . ')', $alias);
		}
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function from(String $table) : QueryBuilderProvider
	{
		$this->sqlQuery .= ' FROM ' . $table;
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function avg(String $column, String $alias) : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->alias('AVG(' . $column . ')', $alias);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function count(String $column, String $alias) : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->alias('COUNT(' . $column . ')', $alias);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function sum(String $column, String $alias) : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->alias('SUM(' . $column . ')', $alias);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function max(String $column, String $alias) : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->alias('MAX(' . $column . ')', $alias);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function min(String $column, String $alias) : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->alias('MIN(' . $column . ')', $alias);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function groupConcat(String $expression, String $alias, String $separator) : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->alias('GROUP_CONCAT(' . $expression . ')', $alias);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function where(String $column, $value='') : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->createBinding('where', $column, $value, '=', 'AND', false);
		if (!empty($value)) {
			$this->setParam($column, $value);
		}
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function orWhere(String $column, $value='') : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->createBinding('where', $column, $value, '=', 'OR', false);
		if (!empty($value)) {
			$this->setParam($column, $value);
		}
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function andWhere(String $column, $value='') : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->createBinding('where', $column, $value, '=', 'AND', false);
		if (!empty($value)) {
			$this->setParam($column, $value);
		}
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function whereNot(String $column, $value='') : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->createBinding('where', $column, $value, '!=', 'AND', false);
		if (!empty($value)) {
			$this->setParam($column, $value);
		}
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function orWhereNot(String $column, $value='') : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->createBinding('where', $column, $value, '!=', 'OR', false);
		if (!empty($value)) {
			$this->setParam($column, $value);
		}
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function andWhereNot(String $column, $value='') : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->createBinding('where', $column, $value, '!=', 'AND', false);
		if (!empty($value)) {
			$this->setParam($column, $value);
		}
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function whereIn(String $column, Array $values) : QueryBuilderProvider
	{
		if (!empty($this->binder->getBinding('select')) && sizeof($values) > 0) {
			$values = implode(',', $values);
			$this->sqlQuery .= ' WHERE ' . $column . ' IN (' . $values . ')';
		}

		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function whereBetween(String $column, $leftValue=null, $rightValue=null) : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->createBinding('between', $column, $leftValue, $rightValue, 'AND', true);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function whereNotBetween(String $column, $leftValue=null, $rightValue=null) : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->createBinding('between', $column, $leftValue, $rightValue, 'AND', false);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function whereNotIn(String $column, Array $values) : QueryBuilderProvider
	{
		if (!empty($this->binder->getBinding('select')) && sizeof($values) > 0) {
			$values = implode(',', $values);
			$this->sqlQuery .= ' WHERE ' . $column . ' NOT IN (' . $values . ')';
		}
		
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function whereLike(String $column, String $pattern, String $operator='AND') : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->createBinding('like', $column, $pattern, $operator, true);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function whereNotLike(String $column, String $pattern, String $operator='AND') : QueryBuilderProvider
	{
		$this->sqlQuery .= $this->binder->createBinding('like', $column, $pattern, $operator, false);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/	
	public function limit(Int $limit, Int $offset=0) : QueryBuilderProvider
	{
		$this->sqlQuery .= ' LIMIT ' . $limit;
		if ($offset > 0) {
			$this->sqlQuery .= ' OFFSET ' . $offset;
		}
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function orderBy(Array $columns) : QueryBuilderProvider
	{
		$columns = implode(',', $columns);
		$this->sqlQuery .= $this->setOrderByFunction(' ORDER BY ' . $columns, '');
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function orderByField(Array $columns) : QueryBuilderProvider
	{
		$columns = implode(',', $columns);
		$this->sqlQuery .= $this->setOrderByFunction($columns, 'FIELD');
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function setParam(String $key, $value) : QueryBuilderProvider
	{
		$this->parameterBag->setParameter($key, $value);
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function getResult(Bool $nullifyResultAccess=false)
	{
		$this->queryResult = [];
		if (is_null($this->queryResult)) {
			return;
		}

		return $this->statement->fetch($this, $this->parameterBag);
	}

	/**
	* {@inheritDoc}
	*/
	public function setResultMapper($resultMapper) : QueryBuilderProvider
	{
		$this->resultMapper = $resultMapper;
		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	public function getQueryParameters() : Array
	{
		return $this->parameterBag->getAll();
	}

	/**
	* {@inheritDoc}
	*/
	public function getQuery() : String
	{
		return $this->sqlQuery;
	}

	/**
	* {@inheritDoc}
	*/
	public function getResultMapper() : String
	{
		return $this->resultMapper;
	}

	/**
	* {@inheritDoc}
	*/
	public function resultMappingEnabled() : Bool
	{
		if (gettype($this->resultMapper) !== 'string' || !class_exists($this->resultMapper)) {
			return false;
		}

		return (new $this->resultMapper instanceof ResultMapper) ? true : false;
	}

	/**
	* {@inheritDoc}
	*/
	public function setOperator(String $operator) : QueryBuilderProvider
	{
		if (in_array($operator, $this->allowedOperators) && $this->sqlQuery !== '') {
			$this->sqlQuery .= $operator;
		}

		return $this;
	}

	/**
	* This static method checks if the last query is a custom query.
	*
	* @access 	public
	* @static
	* @return 	Boolean
	*/
	public static function lastQueryCustom()
	{
		return QueryBuilder::$isCustomQuery;
	}

	/**
	* This method is used to set a field on ORDER BY clause.
	*
	* @param 	$query <String>
	* @access 	protected
	* @return 	String
	*/
	protected function setOrderByFunction(String $query, String $functionName) : String
	{
		if ($functionName !== null && $functionName !== '') {
			$query =' ORDER BY ' . $functionName . '(' . $query . ')';
		}

		return $query;
	}

}