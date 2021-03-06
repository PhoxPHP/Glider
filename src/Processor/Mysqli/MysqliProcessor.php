<?php
/**
* @author 		Peter Taiwo <peter@phoxphp.com>
* @package 		Kit\Glider\Processor\Mysqli\MysqliProcessor
* @license 		MIT License
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*/

namespace Kit\Glider\Processor\Mysqli;

use StdClass;
use Exception;
use RuntimeException;
use mysqli_sql_exception;
use Kit\Glider\Query\Parameters;
use Kit\Glider\Result\Collection;
use Kit\Glider\Result\ResultMapper;
use Kit\Glider\Query\Builder\QueryBuilder;
use Kit\Glider\Result\Platforms\MysqliResult;
use Kit\Glider\Platform\Contract\PlatformProvider;
use Kit\Glider\Processor\Exceptions\QueryException;
use Kit\Glider\Processor\Contract\ProcessorProvider;
use Kit\Glider\Statements\Platforms\MysqliStatement;
use Kit\Glider\Result\Contract\ResultMapperContract;
use Kit\Glider\Statements\Contract\StatementContract;
use Kit\Glider\Results\Contract\ResultObjectProvider;

class MysqliProcessor implements ProcessorProvider
{

	/**
	* @var 		$platformProvider
	* @access 	private
	*/
	private 	$platformProvider;

	/**
	* @var 		$sqlGenerator
	* @access 	private
	*/
	private 	$sqlGenerator;

	/**
	* @var 		$result
	* @access 	protected
	*/
	protected 	$result;

	/**
	* @var 		$connection
	* @access 	protected
	*/
	protected 	$connection;

	/**
	* {@inheritDoc}
	*/
	public function __construct(PlatformProvider $platformProvider)
	{
		$this->platformProvider = $platformProvider;
		$this->connection = $platformProvider->connector()->connect();
	}

	/**
	* {@inheritDoc}
	*/
	public function fetch(QueryBuilder $queryBuilder, Parameters $parameterBag) : Collection
	{
		$resolvedQueryObject = $this->resolveQueryObject($queryBuilder, $parameterBag);
		$statement = $resolvedQueryObject->statement;
		$resultMetaData = $statement->result_metadata();

		$result = [];
		$params = [];
		$mappedFields = [];

		while ($field = $resultMetaData->fetch_field()) {
			$var = $field->name; 
			$mappedFields[] = $var;
			$$var = null; 
			$params[] = &$$var;
		}

		try {

			$statement->store_result();
			call_user_func_array([$statement, 'bind_result'], $params);

			while($statement->fetch()) {

				$resultStdClass = new StdClass();

				if ($queryBuilder->resultMappingEnabled()) {

					$mapper = $queryBuilder->getResultMapper();
					
					$mapper = new $mapper();
					
					if ($mapper instanceof ResultMapper) {
					
						$resultStdClass = $mapper;
					
						if (!$resultStdClass->register()) {
					
							continue;
					
						}
					
					}
				
				}

				foreach($mappedFields as $field) {
					// If no `ResultMapper` class is registered or provided, we'll use
					// `StdClass` to store and retrieve our columns instead.
					if (!$queryBuilder->resultMappingEnabled()) {
						$resultStdClass->$field = $$field;
						continue;
					}

					if (!property_exists($resultStdClass, $field)) {
						throw new RuntimeException(sprintf("Result Mapping Failed. Property %s does not exist in Mapper class.", $field));
					}

					// Here, each field will be mapped to a class property if the class
					// exists.
					$resultStdClass->mapFieldToClassProperty($field, $$field);
				}

				$result[] = $resultStdClass;
			}

		}catch(mysqli_sql_exception $sqlExp) {

			throw new QueryException($sqlExp->getMessage(), $resolvedQueryObject->queryObject);
		
		}

		$this->result = $result;
		
		return new Collection($this, $statement);
	}

	/**
	* {@inheritDoc}
	*/
	public function insert(QueryBuilder $queryBuilder, Parameters $parameterBag) : StatementContract
	{
		$queryObject = $this->resolveQueryObject($queryBuilder, $parameterBag);
		return new MysqliStatement($queryObject->statement);
	}

	/**
	* {@inheritDoc}
	*/
	public function update(QueryBuilder $queryBuilder, Parameters $parameterBag) : StatementContract
	{
		$queryObject = $this->resolveQueryObject($queryBuilder, $parameterBag);
		return new MysqliStatement($queryObject->statement);
	}

	/**
	* {@inheritDoc}
	*/
	public function delete(QueryBuilder $queryBuilder, Parameters $parameterBag) : StatementContract
	{
		$queryObject = $this->resolveQueryObject($queryBuilder, $parameterBag);
		return new MysqliStatement($queryObject->statement);
	}

	/**
	* {@inheritDoc}
	*/
	public function getResult()
	{
		return $this->result;
	}

	/**
	* {@inheritDoc}
	*/
	public function query(String $queryString, int $returnType=1)
	{

		// Turn error reporting on for mysqli
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		$queryObject = $this->connection->query($queryString);
		
		try {
			
			//

		}catch(mysqli_sql_exception $sqlException) {

			throw new QueryException($sqlException->getMessage(), $queryObject);

		}

		if (!$queryObject) {
		
			return false;
		
		}

		if ($returnType == 1) {

			return new MysqliResult($queryObject);
		
		}

		return new MysqliStatement($queryObject);
	}

	/**
	* Resolves query object returning: query, parameters and connection.
	*
	* @param 	$queryBuilder <Kit\Glider\Query\Builder\QueryBuilder>
	* @param 	$parameterBag <Kit\Glider\Query\Parameters>
	* @access 	private
	* @return 	<Object> <StdClass>
	* @throws 	Kit\Glider\Processor\Exceptions\QueryException
	*/
	private function resolveQueryObject(QueryBuilder $queryBuilder, Parameters $parameterBag) : StdClass
	{
		$std = new StdClass();
		$parameterTypes = '';
		$boundParameters = [];

		$transaction = null;
		$parameters = [];

		$query = $queryBuilder->getQuery();
		$sqlGenerator = $queryBuilder->generator;

		$sqlObject = $sqlGenerator->convertToSql($query, $parameterBag);
		$query = $sqlObject->query;
		$parameters = $sqlObject->parameterValues;

		$std->queryObject = $sqlObject;
		$query = $sqlObject->query;

		if ($parameterBag->size() > 0) {

			foreach($parameters as $param) {
				
				$isset = false;

				if (is_array($param)) {

					foreach($param as $p) {
					
						$parameterTypes .= $parameterBag->getType($p);

						$isset = true;
					
					}
				
				}

				if ($isset == true) {
				
					continue;
				
				}

				$parameterTypes .= $parameterBag->getType($param);
			}

			$boundParameters[] = $parameterTypes;

			$count = 0;
			$paramValues = [];

			while ($count <= count($parameters) - 1) {
				$value = $parameters[$count];
				$isBound = false;

				if (is_array($value)) {
					$iterate = count($boundParameters);
					foreach($value as $i => $val) {
						$boundParameters[$iterate] =& $value[$i];
						$iterate++;
					}
				}

				if (!is_array($value)) {
					$boundParameters[] =& $parameters[$count];
				}
				
				$count++;
			}
		}

		// Turn error reporting on for mysqli
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		try{

			$hasMappableFields = $sqlGenerator->hasMappableFields($query);

			if (!$this->platformProvider->isAutoCommitEnabled()) {

				// Only start transaction manually if auto commit is not enabled.
				$transaction = $this->platformProvider->transaction();
				$transaction->begin($this->connection);

			}

			$statement = $this->connection->stmt_init();
			$statement->prepare($query);

			if (!empty($hasMappableFields) || in_array($queryBuilder->getQueryType(), [1, 2, 3, 4]) && !empty($boundParameters)) {
		
				call_user_func_array([$statement, 'bind_param'], $boundParameters);

			}

			$statement->execute();

			if (!$this->platformProvider->isAutoCommitEnabled()) {

				$transaction->commit($this->connection); // Commit transaction

			}


		}catch(mysqli_sql_exception $sqlExp) {

			if (!$this->platformProvider->isAutoCommitEnabled()) {

				$transaction->rollback($this->connection);

			}

			throw new QueryException($sqlExp->getMessage(), $sqlObject);

		}

		$std->statement = $statement;
		$std->transaction = $transaction;

		return $std;
	}

}