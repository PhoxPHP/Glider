<?php
/**
* @author 		Peter Taiwo <peter@phoxphp.com>
* @package 		Kit\Glider\Schema\Column\Type\Varchar
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

namespace Kit\Glider\Schema\Column\Type;

use Kit\Glider\Schema\Column\Type\Contract\TypeContract;

class Varchar implements TypeContract
{

	/**
	* {@inheritDOc}
	*/
	public function getName() : String
	{
		return 'VARCHAR';
	}

	/**
	* {@inheritDOc}
	*/
	public function getMinimumLength() : int
	{
		return 1;
	}

	/**
	* {@inheritDOc}
	*/
	public function getMaximumLength() : int
	{
		return 500;
	}

	/**
	* {@inheritDOc}
	*/	
	public function getMaxTypeValueLength() : int
	{
		return 1;
	}

}