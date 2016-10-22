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

namespace MDNP\Notes;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;


/** \brief Register and handle routes for MDNP.
 *
 * \details This class implements the ControllerProviderInterface of Silex, so
 *  this class may be mounted to any route in Silex.
 */
class NoteController implements ControllerProviderInterface
{
	/** \brief Mount the MDNP routes.
	 *
	 *
	 * \param $app The Silex \ref Application to attach to.
	 */
	public function connect(Application $app)
	{
		return $app['controllers_factory'];
	}
}

?>
