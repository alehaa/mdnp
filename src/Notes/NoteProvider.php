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

use Pimple\Container;
use Pimple\ServiceProviderInterface;


/** \brief MDNP Note provider for Silex (and Pimple).
 *
 * \details This class implements the ServiceProviderInterface of Pimple, so
 *  this class may be registered to Silex applications and Pimple containers.
 */
class NoteProvider implements ServiceProviderInterface
{
	/** \brief Register the MDNP \ref NoteProvider.
	 *
	 * \details Adds the required elements to \p app.
	 *
	 *
	 * \param $app The Pimple container to attach to.
	 */
	public function register(Container $app)
	{
		$app['notes.storage'] = function (Container $app) {
			return new NoteStorage($app);
		};

		/* QueryBuilder factory for internal usage. This will be used to get a
		 * new QueryBuilder, which is specialized for note-requests and will be
		 * used by the NoteStorage class. */
		$app['notes.noteqb'] = $app->factory(function (Container $app) {
			return new NoteQueryBuilder($app['db']);
		});
	}
}

?>
