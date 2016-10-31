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

use DateTime;
use MDNP\Notes\Entities\Note;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/** \brief Register and handle routes for MDNP.
 *
 * \details This class implements the ControllerProviderInterface of Silex, so
 *  this class may be mounted to any route in Silex.
 */
class NoteController implements ControllerProviderInterface
{
	/** \brief Update \p $note values with data from \p $request.
	 *
	 *
	 * \param \ref Note &$note The note to be edited.
	 * \param Reuest &$request The request to be handled.
	 */
	private function setNoteValues(Note &$note, Request &$request)
	{
		$note->setTitle($request->get('title'));
		$note->setContent($request->get('content'));
		$note->setPriority($request->get('priority'));

		/* The custom note editor will send the current date & time, even if no
		 * deadline was selected. The deadline only will be set, if it's in the
		 * future. */
		$deadline = new DateTime($request->get('deadline'));
		if ($deadline > new DateTime('now + 10 minutes'))
			$note->setDeadline($deadline);
		else
			$note->setDeadline(null);
	}


	/** \brief Update tags referenced by \p $note.
	 *
	 * \details This function updates the relations for all referenced tags by
	 *  \p $note. New tags will be added and created if not already in the data-
	 *  base and for tags not referenced anymore the relation will be removed.
	 *
	 *
	 * \param \ref Note &$note The note where the tags should be linked to.
	 * \param \ref Request &$request The request to be handled.
	 * \param \ref Application &$app The container we're using for \p $request.
	 */
	private function updateNoteTags(Note &$note, Request &$request,
	                                Application &$app)
	{
		$new = array_filter(explode(',', $request->get('tags')));
		$current = array();
		foreach ($note->getTags() as $tag)
			$current[] = $tag->getName();

		/* Add tags to note, that are not already linked to the note. If the tag
		 * does not exist in the database, findOrCreate of the Tag repository
		 * will create it. */
		$repo = $app['orm.em']->getRepository('MDNP\Notes\Entities\Tag');
		foreach(array_diff($new, $current) as $tagname)
			$note->addTag($repo->findOrCreate($tagname));

		/* If there are tags not associated with the note anymore, they'll be
		 * deleted now. */
		foreach(array_diff($current, $new) as $tagname)
			foreach ($note->getTags() as $tag)
				if ($tag->getName() == $tagname)
					$note->removeTag($tag);
	}


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
				'notes' => $app['notes']->search('is:open'),
				'search' => 'is:open'
			));
		})
		->bind('notes.overview');

		$routes->get('/search', function (Request $request) use ($app) {
			$search = $request->get('s') ?: '';
			return $app['twig']->render('notelist.twig', array(
				'notes' => $app['notes']->search($search),
				'search' => $search
			));
		})
		->bind('notes.search');


		/* Note detail page.
		 */

		$routes->get('/{id}', function (int $id) use ($app) {
			$note = $app['notes']->find($id);
			if ($note === null)
				throw new NotFoundHttpException('Note '.$id.' does not exist.');

			return $app['twig']->render('note.twig', array('note' => $note));
		})
		->assert('id', '\d+')
		->bind('notes.note');


		/* Add a note.
		 */

		$routes->get('/new', function () use ($app) {
			return $app['twig']->render('editor.twig');
		})
		->bind('notes.new');

		$routes->post('/new', function (Request $request) use ($app) {
			$note = new Note;
			$this->setNoteValues($note, $request);
			$this->updateNoteTags($note, $request, $app);

			$app['orm.em']->persist($note);
			$app['orm.em']->flush();

			return $app->redirect('/');
		});


		/* Edit a notes.
		 *
		 * We'll use the same editor template for both the new note editor and
		 * editing an existing note. */

		$routes->get('/{id}/edit', function (int $id) use ($app) {
			$note = $app['notes']->find($id);
			if ($note === null)
				throw new NotFoundHttpException('Note '.$id.' does not exist.');

			return $app['twig']->render('editor.twig', array('note' => $note));
		})
		->assert('id', '\d+')
		->bind('notes.edit');

		$routes->post('/{id}/edit',
		              function (int $id, Request $request) use ($app) {
			$note = $app['notes']->find($id);
			$this->setNoteValues($note, $request);
			$this->updateNoteTags($note, $request, $app);
			$app['orm.em']->flush();

			return $app->redirect('/'.$id);
		})
		->assert('id', '\d+');

		$routes->get('/{id}/edit/status/{status}',
		              function (int $id, string $status) use ($app) {
			$note = $app['notes']->find($id);
			$note->setStatus($status);
			$app['orm.em']->flush();

			return $app->redirect('/'.$id);
		})
		->assert('id', '\d+')
		->bind('notes.edit.status');


		/* Delete a note.
		 */

		$routes->get('/{id}/delete', function (int $id) use ($app) {
			$note = $app['notes']->find($id);
			if ($note === null)
				throw new NotFoundHttpException('Note '.$id.' does not exist.');

			$app['orm.em']->remove($note);
			$app['orm.em']->flush();

			return $app->redirect('/');
		})
		->assert('id', '\d+')
		->bind('notes.delete');


		return $routes;
	}
}

?>
