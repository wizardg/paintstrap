<?php
/**
 * @see{Cocur\Autoloader\Autoloader} file.
 *
 * Copyright (c) 2011 Florian Eckerstorfer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package   org.cocur.autoloader
 * @copyright 2011 Florian Eckerstorfer
 * @author    Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace Cocur\Autoloader;

/**
 * Class to autoload PHP classes.
 *
 * @package   org.cocur.autoloader
 * @copyright 2011 Florian Eckerstorfer
 * @author    Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class Autoloader
{
	
	/**
	 * Registers the autoload method @see{loadClass()}.
	 *
	 * @param  boolean $prepend If TRUE the autoloader will be prepended to the autoloader stack instead of appended.
	 * @return void
	 * @throws \InvalidArgumentException when $autoloader is not an instance of Autoloader.
	 */
	public static function register($autoloader = null, $prepend = false)
	{
		if (null == $autoloader)
		{
			$autoloader = new self();
		}
		else if (!($autoloader instanceof Autoloader))
		{
			throw new \InvalidArgumentException('$autoloader must be an instance of "Cocur\Autoloader\Autoloader".');
		}
		spl_autoload_register(array($autoloader, 'loadClass'), true, $prepend);
	}
	
	/**
	 * Loads the given class.
	 *
	 * @param  string  $class_name Name of a class.
	 * @return boolean             TRUE if the class could be found, FALSE if not.
	 */
	public function loadClass($class_name)
	{
		$paths = array_merge(explode(PATH_SEPARATOR, get_include_path()), $this->paths);
		
		$filename = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $class_name) . '.php';
		foreach ($paths as $path)
		{
			$full_filename = $path . DIRECTORY_SEPARATOR . $filename;
			if (file_exists($full_filename))
			{
				require_once $full_filename;
				return true;
			}
		}
		return false;
	}
	
	/** @var array */
	protected $paths = array();
	
	/**
	 * Adds a new search path to the autoloader.
	 *
	 * @param  string     $path Path.
	 * @return Autoloader
	 */
	public function add($path)
	{
		$this->paths[$path] = $path;
		return $this;
	}
	
	/**
	 * Returns an array with all search paths.
	 *
	 * @return array Search paths.
	 */
	public function getPaths()
	{
		return $this->paths;
	}
	
}
