<?php
/**
* MIT License
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:

* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.

* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*/

/**
* @author 	Peter Taiwo
* @package 	Kit\Glider\Model\Model
*/

namespace Kit\Glider\Model;

use RuntimeException;
use Kit\Glider\Repository;
use Kit\Glider\Model\Attributes;
use Kit\Glider\Result\Collection;
use Kit\Glider\Schema\SchemaManager;
use Kit\Glider\Model\Relationships\HasOne;
use Kit\Glider\Model\Uses\{Record, Finder};
use Kit\Glider\Query\Builder\QueryBuilder;
use Kit\Glider\Model\Relationships\HasMany;
use Kit\Glider\Model\Contracts\ModelContract;

class Model extends Repository implements ModelContract
{

	use Record, Finder;

	/**
	* @var 		$connectionId
	* @access 	protected
	*/
	protected 	$connectionId = null;

	/**
	* @var 		$softProperties
	* @access 	protected
	*/
	protected static $softProperties = [];

	/**
	* @var 		$relations
	* @access 	protected
	*/
	protected 	$relations = [];

	/**
	* @var 		$key
	* @access 	protected
	*/
	protected 	$key;

	/**
	* @var 		$table
	* @access 	protected
	*/
	protected 	$table;

	/**
	* @access 	public
	* @return 	void
	*/
	public function __construct()
	{
		$this->key = $this->primaryKey();
	}

	/**
	* __set magic method.
	*
	* @param 	$var <String>
	* @param 	$value <Mixed>
	* @access 	public
	* @return 	void
	*/
	public function __set($var, $value)
	{
		Model::$softProperties[$var] = $value;
	}

	/**
	* Retrieves a property value.
	*
	* @param 	$var <String>
	* @access 	public
	* @return 	Mixed
	*/
	public function __get($var)
	{
		$accessibleProperties = $this->accessibleProperties();
		if (in_array($var, $accessibleProperties) && isset(Model::$softProperties[$var])) {
			return Model::$softProperties[$var];
		}
	}

	/**
	* __call magic method.
	*
	* @param 	$method <String>
	* @param 	$arguments <Mixed>
	* @access 	public
	* @return 	Mixed
	*/
	public function __call($method, $arguments)
	{
		//
	}

	/**
	* Returns an instance of schema manager.
	*
	* @access 	public
	* @return 	Object Kit\Glider\Schema\SchemaManager
	*/
	final public function schema() : SchemaManager
	{
		return parent::getSchema($this->connectionId);
	}

	/**
	* Returns sn instance of query builder.
	* 
	* @access 	public
	* @return 	Object Kit\Glider\Query\Builder\QueryBuilder
	*/
	final public function queryBuilder() : QueryBuilder
	{
		return parent::getQueryBuilder($this->connectionId);
	}

	/**
	* Returns name of the model class.
	*
	* @access 	public
	* @return 	String
	*/
	final public function getName()
	{
		return get_class($this);
	}

	/**
	* Adds a property to list of soft properties if condition is true. 
	*
	* @param 	$condition <Boolean>
	* @param 	$property <String>
	* @param 	$value <Mixed>
	* @access 	public
	* @return 	void
	*/
	final public function addPropertyIf(Bool $condition=null, String $property, $value=null)
	{
		if ($condition == true) {
			Model::$softProperties[$property] = $value;
		}
	}

	/**
	* Adds a property to list of soft properties if condition is false. 
	*
	* @param 	$condition <Boolean>
	* @param 	$property <String>
	* @param 	$value <Mixed>
	* @access 	public
	* @return 	void
	*/
	final public function addPropertyIfNot(Bool $condition=null, String $property, $value=null)
	{
		if ($condition == false) {
			Model::$softProperties[$property] = $value;
		}
	}

	/**
	* Checks if a model has a table.
	*
	* @access 	public
	* @return 	Boolean
	* @final
	*/
	final public function hasQualifiedTable() : Bool
	{
		if ($this->schema()->hasTable($this->table)) {
			return true;
		}

		return false;
	}

	/**
	* Checks if a property exists.
	*
	* @param 	$property <String>
	* @access 	public
	* @static
	* @return 	Boolean
	*/
	public static function hasSoftProperty(String $property=null) : Bool
	{
		return (isset(Model::$softProperties[$property])) ? true : false; 
	}

	/**
	* Returns properties created using the __set magic method either as an array or an object
	* if the @param $asObject is set to true.
	*
	* @param 	$asObject <Boolean>
	* @access 	public
	* @static
	* @return 	Mixed
	*/
	public static function getSoftProperties(Bool $asObject=false)
	{
		if ($asObject == true) {
			return (Object) Model::$softProperties;
		}

		return Model::$softProperties;
	}

	/**
	* {@inheritDoc}
	*/
	final public function find(Int $key=1, Array $options=[]) : ModelContract
	{
		$model = Model::getInstanceOfModel();

		$columns = $this->getAccessibleProperties();

		$builder = $this->toSql($columns);

		$builder->where(
			$this->primaryKey(), $key
		);

		$result = $builder->get();

		if ($result->first()) {

			$first = $result->toArray()->first();

			array_map(function($key) use ($first) {

				Model::$softProperties[$key] = $first[$key];

			}, array_keys($first));

		}

		return $this;
	}

	/**
	* {@inheritDoc}
	*/
	final public function all()
	{
		$accessibleProperties = $this->accessibleProperties();
		$results = [];
		$columns = $this->getAccessibleProperties();

		$builder = $this->toSql(Attributes::ALL_SELECTOR);

		$result = $builder->get();

		if ($result->size() > 0) {

			$resultArray = $result->toArray()->all();

			foreach($resultArray as $i => $result) {

				$res = $resultArray[$i];

				$accessible = [];

				foreach(array_keys($res) as $i => $key) {
					if ($this->isAccessible($key)) {
						$accessible[$key] = $res[$key];
					}
				}

				$results[] = $accessible;
			}

		}

		return $results;
	}

	/**
	* {@inheritDoc}
	*/
	final public function first()
	{
		$records = $this->all();

		return $records[0] ?? false;
	}

	/**
	* {@inheritDoc}
	*/
	final public function last()
	{
		$records = $this->all();

		return $records[count($records) - 1] ?? false;
	}

	/**
	* {@inheritDoc}
	*/
	final public function offset(Int $offset)
	{
		$records = $this->all();

		return $records[$offset] ?? false;
	}

	/**
	* {@inheritDoc}
	*/
	public static function getInstanceOfModel() : ModelContract
	{
		return new self();
	}

	/**
	* {@inheritDoc}
	*/	
	public function getConnectionId() : String
	{
		return Attributes::CONNECTION_ID;
	}

	/**
	* {@inheritDoc}
	*/	
	public function accessibleProperties() : Array
	{
		return ['id'];
	}

	/**
	* {@inheritDoc}
	*/	
	public function primaryKey() : String
	{
		return Attributes::PRIMARY_KEY;
	}

	/**
	* {@inheritDoc}
	*/
	public static function __callStatic($method, $arguments)
	{
		$context = Model::getInstanceOfModel();
		$childClass = get_called_class();

		$result = $childModel = new $childClass;
		$isFindBy = null;

		if (preg_match(Attributes::FINDBY_REGEX, $method, $match)) {

			// Is this a findBy static method?
			$clause = $match[1];
			$result = $context->initializeFindBy(
				$context,
				$childModel,
				$clause,
				$arguments
			);

		}else{

			// Not a findBy static method, then it has to be a query builder method.
			switch ($method) {
				case 'value':
					# code...
					break;
				
				default:
					# code...
					break;
			}

			// $result = ['<>', '/\\'];

		}

		return $result;
	}

	/**
	* Returns a prepared query that will be used by other find methods.
	*
	* @param 	$fields <Mixed>
	* @access 	protected
	* @static
	* @return 	Object Kit\Glider\Query\Builder\QueryBuilder
	*/
	protected function toSql($fields=null)
	{
		return $this->queryBuilder()
		->select($fields)
		->from($this->table);
	}

	/**
	* Returns an array of accessible properties modified. This method accepts two arguments.
	* The first argument is an array of columns that will be merged to the model's accessible properties,
	* while the second argument accepts a boolean type value. It is default to true, if it is set to false,
	* the model's accessible properties will be ignored and the columns in @param $with will be used instead.
	*
	* @param 	$with <Array>
	* @param 	$addAccessibleProperties <Boolean>
	* @access 	protected
	* @return 	Array
	*/
	protected function getAccessibleProperties(Array $with=[], Bool $addAccessibleProperties=true) : Array
	{
		$accessibleProperties = array_merge($this->accessibleProperties(), $with);

		if ($addAccessibleProperties == false) {
			$accessibleProperties = $with;
		}

		$properties = [];
		$associatedTable = $this->table;

		$properties = array_map(function($property) use ($associatedTable) {
			return $associatedTable . '.' . $property;
		}, $accessibleProperties);

		return $properties;
	}

	/**
	* Checks if a property can be accessed.
	*
	* @param 	$property <String>
	* @access 	protected
	* @return 	Boolean
	*/
	protected function isAccessible(String $property) : Bool
	{
		if (in_array($property, $this->accessibleProperties())) {
			return true;
		}

		return false;
	}

	/**
	* Returns the child model that is extending this base model.
	*
	* @access 	protected
	* @return 	Object Kit\Glider\Model\Contracts\ModelContract
	*/
	protected function getInheritingModel() : ModelContract
	{
		$childClass = get_class();
		return new $childClass();
	}

}