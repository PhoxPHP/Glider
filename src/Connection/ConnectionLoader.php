<?php
/**
* @author 		Peter Taiwo <peter@phoxphp.com>
* @package 		Kit\Glider\Connection\ConnectionLoader
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

namespace Kit\Glider\Connection;

use Exception;
use App\Config;

class ConnectionLoader
{

	/**
	* Loads connection configuration.
	*
	* @access 	public
	* @return 	<Array>
	*/
	public function getConnectionsFromResource() : Array
	{	
		$baseDir = dirname(__DIR__);
		$configLocation = $baseDir . '/config.php';
	
		if (!file_exists($configLocation) && class_exists(Config::class)) {
			$resourceConfig = Config::get('database');
		}else{
			$resourceConfig = include $configLocation;
		}

		if (gettype($resourceConfig) !== 'array') {
			throw new Exception('Invalid configuration type.');
		}
		
		return $resourceConfig;
	}
}