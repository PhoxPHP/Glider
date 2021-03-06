<?php
/**
* @author 		Peter Taiwo <peter@phoxphp.com>
* @package 		Kit\Glider\Model\Contracts\ModelContract
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

namespace Kit\Glider\Model\Contracts;

interface ModelContract
{

	/**
	* Returns instance of model.
	* Because most of this methods cannot be accessed statically, this method will make it
	* easy to access those methods by just creating a static instance of the model.
	*
	* @access 	public
	* @static
	* @return 	<Object> <Kit\Glider\Model\Contracts\ModelContract>
	*/
	public static function getInstanceOfModel() : ModelContract;

	/**
	* Returns connection id.
	*
	* @access 	public
	* @return 	<Mixed>
	*/
	public function getConnectionId() : String;

	/**
	* Returns an array of properties that can be retrieved and accessed
	* in a result set.
	*
	* @access 	public
	* @return 	<Array>
	*/
	public function accessibleProperties() : Array;

	/**
	* Returns the name of the primary key of the associated table.
	*
	* @access 	public
	* @return 	<String>
	*/
	public function primaryKey() : String;

	/**
	* Finds a record using the primary key.
	*
	* @param 	$key <Integer>
	* @param 	$options <Array>
	* @access 	public
	* @return 	<Object> <Kit\Glider\Model\Contracts\ModelContract>
	*/
	public function find(Int $key=null, Array $options=[]) : ModelContract;

	/**
	* Finds and returns all rows.
	*
	* @access 	public
	* @return 	<Mixed>
	*/
	public function all();

	/**
	* Finds and returns the first row in result.
	*
	* @access 	public
	* @return 	<Mixed>
	*/
	public function first();

	/**
	* Finds and returns the last row in result.
	*
	* @access 	public
	* @return 	<Mixed>
	*/
	public function last();

	/**
	* Finds and returns a row at a certain offset in result.
	*
	* @param 	$offset <Integer>
	* @access 	public
	* @return 	<Mixed>
	*/
	public function offset(Int $offset);

	/**
	* __callStatic magic method.
	*
	* @param 	$method <String>
	* @param 	$arguments <Array>
	* @access 	public
	* @static
	* @return 	<Mixed>
	*/
	public static function __callStatic($method, $arguments);

	/**
	* Sets query options to use when finding results.
	* -----------------------------------------------
	* Available options as at v1.6.1
	* -----------------------------------------------
	* 
	* asc -> Orders result by field by ascending
	* desc -> Orders result by field by descending
	* limit -> Limits number of rows returned. 
	*
	* @param 	$options <Array>
	* @access 	public
	* @return 	<void>
	*/
	public function setFindOptions(Array $options);

}