<?php

/* This file is part of MDNP.
 *
 * MDNP is free software: you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MDNP is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see
 *
 *  http://www.gnu.org/licenses/
 *
 *
 * Copyright (C)
 *  2016 Alexander Haase <ahaase@alexhaase.de>
 */

namespace MDNP;

use Silex\WebTestCase;
use MDNP\Application;


abstract class AppTestCase extends WebTestCase
{
	/** \brief Create a new MDNP \ref Application and return the instance.
	 *
	 *
	 * \return The new \ref Application instance.
	 */
	final public function createApplication(): Application
	{
		$app = new Application;
		$app['debug'] = true;
		unset($app['exception_handler']);

		return $app;
	}
}
