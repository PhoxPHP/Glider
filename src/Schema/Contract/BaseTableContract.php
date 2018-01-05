<?php
namespace Kit\Glider\Schema\Contract;

use Closure;

interface BaseTableContract
{

	/**
	* Construct a new table.
	*
	* @param 	$tableName <String>
	* @param 	$columns <Array>	
	* @param 	$indexes <Array>
	* @param 	$primaryKey <String>
	* @access 	public
	* @return 	void
	*/
	public function __construct(String $tableName, Array $columns=[], Array $indexes=[], String $primaryKey=null);

	/**
	* Sets the table engine.
	*
	* @param 	$engine <String>
	* @access 	public
	* @return 	Mixed
	*/
	public function setEngine(String $engine);

	/**
	* Checks if a table exists.
	*
	* @access 	public
	* @return 	Boolean
	*/
	public function exists();

	/**
	* Creates table.
	*
	* @param 	$scheme <Closure>
	* @access 	public
	* @return 	Mixed
	*/
	public function create(Closure $scheme);

	/**
	* Modifies or alters a table.
	*
	* @param 	$scheme <Closure>
	* @access 	public
	* @return 	Mixed
	*/
	public function modify(Closure $scheme);

	/**
	* Drops table.
	*
	* @access 	public
	* @return 	Mixed
	*/
	public function drop();

	/**
	* Renames table.
	*
	* @param 	$newName <String>
	* @access 	public
	* @return 	Mixed
	*/
	public function rename(String $newName);

	/**
	* Checks if a table has column.
	*
	* @param 	$column <String>|<Kit\Glider\Schema\Column>
	* @access 	public
	* @return 	Boolean
	*/
	public function hasColumn($column) : Bool;

	/**
	* Return table columns.
	*
	* @access 	public
	* @return 	Mixed
	*/
	public function getColumns();

	/**
	* Return name of columns.
	*
	* @access 	public
	* @return 	Mixed
	*/
	public function getColumnNames();

	/**
	* Return types of columns with length.
	*
	* @access 	public
	* @return 	Mixed
	*/
	public function getColumnTypes();

	/**
	* Return type of a column.
	*
	* @access 	public
	* @return 	Mixed
	*/
	public function getColumnType(String $column);

	/**
	* Return table column.
	*
	* @param 	$column <Mixed> String|Kit\Glider\Schema\Column
	* @access 	public
	* @return 	Mixed
	*/
	public function getColumn($column);

	/**
	* Renames a column.
	*
	* @param 	$column <String>
	* @param 	$newName <String>
	* @access 	public
	* @return 	Mixed
	*/
	public function renameColumn(String $column, String $newName);

	/**
	* Returns all indexes on a table.
	*
	* @access 	public
	* @return 	Mixed
	*/
	public function getAllIndexes();

	/**
	* Checks if a table has column.
	*
	* @param 	$column <String>
	* @access 	public
	* @return 	void
	*/
	public function hasIndex(String $column) : Bool;

	/**
	* Creates a new index.
	*
	* @param 	$name <String>
	* @param 	$options <Array>
	* @access 	public
	* @return 	void
	*/
	public function addIndex(String $name, Array $options=[]);

	/**
	* Renames an index on a table. @param $oldName is the index's current
	* name while @param $newName is the name which the index will be changed to.
	*
	* @param 	$oldName <String>
	* @param 	$newName <String>
	* @access 	public
	* @return 	Mixed
	*/
	public function renameIndex(String $oldName, String $newName);

}