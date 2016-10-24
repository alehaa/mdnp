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
use Symfony\Component\HttpFoundation\Request;


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
		$routes = $app['controllers_factory'];


		/* Search interfaces.
		 *
		 * MDNP provides two pages with lists of notes: The root page will show
		 * all open notes, the search page will show all notes that match the s
		 * GET parameter. */

		$routes->get('/', function () use ($app) {
			return $app['twig']->render('notelist.twig', array(
				'notes' => $app['notes.storage']->fetchAll('is:open'),
				'search' => 'is:open'
			));
		})
		->bind('notes.overview');

		$routes->get('/search', function (Request $request) use ($app) {
			$search = $request->get('s') ?: '';
			return $app['twig']->render('notelist.twig', array(
				'notes' => $app['notes.storage']->fetchAll($search),
				'search' => $search
			));
		})
		->bind('notes.search');


		/* Note detail page.
		 */

		$routes->get('/{id}', function (int $id) use ($app) {
			return $app['twig']->render('note.twig', array(
				'note' => $app['notes.storage']->fetch($id)
			));
		})
		->assert('id', '\d+')
		->bind('notes.note');


		/* Add and edit notes.
		 *
		 * We'll use the same editor template for both the new note editor and
		 * editing an existing note. */

		$routes->get('/new', function () use ($app) {
		})
		->bind('notes.new');

		$routes->get('/{id}/edit', function (int $id) use ($app) {
		})
		->assert('id', '\d+')
		->bind('notes.edit');


		/* Delete a note.
		 */

		$routes->get('/{id}/delete', function (int $id) use ($app) {
		})
		->assert('id', '\d+')
		->bind('notes.delete');


		return $routes;
	}
}

?>
