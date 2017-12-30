<?php
/**
* @package 	SqlGenerator
* @version 	0.1.0
*
* This class helps to generate sql queries from an array of query
* keywords.
*/

namespace Kit\Glider\Query\Builder;

use StdClass;
use Kit\Glider\Query\Parameters;
use Kit\Glider\Query\Builder\Type;
use Kit\Glider\Query\Builder\QueryBinder;
use Kit\Glider\Connectors\Contract\ConnectorProvider;
use Kit\Glider\Query\Exceptions\ParameterNotFoundException;
use Kit\Glider\Query\Exceptions\InvalidParameterCountException;

class SqlGenerator
{

	/**
	* Set of keys that are not allowed when in a select query.
	*
	* @var 		$disallowedChars
	* @access 	private
	*/
	private 	$disallowedChars = ['?', '&', '%', '$', '#', '+', '!', ')', '(', '-', '^', '=', '/', '>', '<', ':', ';'];

	/**
	* Constructor accepts Kit\Glider\Query\Builder\QueryBinder as an argument. It gets the
	* created queries and then generates sql query from it.
	*
	* @param 	$binder Kit\Glider\Query\Builder\QueryBinder
	* @access 	public
	* @return 	void
	*/
	public function __construct(QueryBinder $binder)
	{
		//
	}

	/**
	* Converts a query string with named parameters to marked parameters and returs an object.
	*
	* @param 	$query <String>
	* @param 	$parameterBag Kit\Glider\Query\Parameters
	* @access 	public
	* @return 	Object
	*/
	public function convertToSql(String $query, Parameters $parameterBag) : StdClass
	{
		$stdClass = new StdClass();
		$stdClass->parameters = [];
		$stdClass->query = '';
		$match = false;

		if (preg_match_all('/\:([^ ]+)/s', $query, $matched)) {
			$stdClass->parameters = array_map(function($m) use ($parameterBag, $matched) {
				if ($parameterBag->getParameter($m) == null) {
					throw new InvalidParameterCountException('Number of parameters does not match length of proposed parameters.', $matched[1], $parameterBag);
				}
				return $m;
			}, $matched[1]);
		}

		$setParams = $parameterBag->getAll();
		$stdClass->query = str_replace($matched[0], '?', $query);

		return $stdClass;
	}

	/**
	* Returns an array of selected fields in a `SELECT` statement.
	*
	* @param 	$query <String>
	* @access 	public
	* @return 	Array
	*/
	public function getSelectedFields(String $query) : Array
	{
		$columns = [];

		if (Type::getStatementType($query) == 1) {
			if (preg_match("/(SELECT|select|Select)(.*?)FROM|from|From([^ ]+)/s", $query, $matches)) {
				$columns = explode(',', $matches[2]);
				$columns = array_map(function($field) {
					return trim(ltrim($field));
				}, $columns);
			}
		}

		return $columns;
	}

	/**
	* This method returns an array of fields to map in a query.
	*
	* @param 	$query <String>
	* @access 	public
	* @return 	Array
	*/
	public function hasMappableFields(String $query) : Array
	{
		$fields = [];

		if (preg_match("/=([^ ]+)?/s", $query, $matches)) {
			$fields = $matches;
		}

		return $fields;
	}

	/**
	* Return array of disallowed characters.
	*
	* @access 	public
	* @return 	Array
	*/
	public function getDisallowedChars() : Array
	{
		return $this->disallowedChars;
	}

}