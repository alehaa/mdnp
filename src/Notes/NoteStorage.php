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

use Doctrine\DBAL\Query\QueryBuilder;
use Pimple\Container;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/** \brief Storage class for MDNP Notes.
 *
 * \details This class handles operations on the \ref NoteStorage. It acts as a
 *  proxy between the \ref NoteController and the underlaying database.
 */
class NoteStorage
{
	protected $app; ///< Copy of the calling app instance.


	/** \brief Constructor.
	 *
	 *
	 * \param $app The attached Pimple container.
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}


	/** \brief Convert JSON-encoded tags field in \p $row to an array.
	 *
	 * \details PostgreSQL arrays will be converted to JSON in queries build by
	 *  \ref NoteQueryBuilder. This function will convert the tags field of \p
	 *  row back to an array.
	 *
	 *
	 * \param $row Row to be converted.
	 *
	 * \return Reference to \p row.
	 */
	protected function &convert(array &$row): array
	{
		$row['tags'] = json_decode($row['tags'], true);
		return $row;
	}


	/** \brief Get all notes from the database that match the \p $search query.
	 *
	 *
	 * \param $search The search query. See \ref NoteQueryBuilder find_commands
	 *  for more details about the syntax.
	 */
	public function fetchAll(string $search): array
	{
		$notes = $this->app['notes.noteqb']
		              ->search($search)
		              ->execute()
		              ->fetchAll();

		foreach ($notes as &$note)
			$this->convert($note);

		return $notes;
	}


	/** \brief Get a specific note with id \p $id from the database.
	 *
	 *
	 * \param $id ID of the note.
	 */
	public function fetch(int $id)
	{
		$note = $this->app['notes.noteqb']
		             ->select('content')
		             ->where('notes.id = :id')
		             ->setParameters(array('id' => $id))
		             ->execute()
		             ->fetch();

		/* Check if note was found. Otherwise we'll return an error 404. */
		if (empty($note))
			throw new
				NotFoundHttpException(sprintf('Note %d does not exist.', $id));

		return $this->convert($note);
	}


	/** \brief Set \p $tags for note \p $id.
	 *
	 * \details This function will link all \p $tags to \p $note. Tag links not
	 *  defined in \p $tags will be deleted.
	 *
	 *
	 * \param $note ID of note \p $tags should be assigned.
	 * \param $tags The tags to be assigned.
	 */
	protected function set_tags(int $note, array $tags)
	{
		/* Prepare some queries for the following steps. */
		$qb_lookup = new QueryBuilder($this->app['db']);
		$qb_lookup->select('id')
		          ->from('tags')
		          ->where($qb_lookup->expr()->eq('lower(name)', ':tag'));

		$qb_insert = new QueryBuilder($this->app['db']);
		$qb_insert->insert('tags')
		          ->values(array('name' => ':name', 'created_at' => 'now()'));

		$sql_link = 'INSERT INTO notes_tags (note, tag, created_at)
		            VALUES (:note, :tag, now()) ON CONFLICT DO NOTHING';

		$qb_rm_old = new QueryBuilder($this->app['db']);
		$qb_rm_old->delete('notes_tags')
		          ->where($qb_rm_old->expr()->eq('note', ':note'))
		          ->setParameter('note', $note);


		/* Iterate over all tags and lookup their ID in the database. If a tag
		 * is not stored in the database, it'll be inserted and the new ID used.
		 * A new note-tag relation will be inserted into note_tags, if it's not
		 * already stored. */
		foreach ($tags as $tag) {
			/* We'll convert all tags to lowercase as a convention. */
			$tag = strtolower($tag);

			/* Do we have a key named $tag already in the database, or do we
			 * have to create a new one? */
			$tag_id = $qb_lookup->setParameter('tag', $tag)
			                    ->execute()
			                    ->fetchColumn(0);
			if ($tag_id == null) {
				$qb_insert->setParameter('name', $tag)
				          ->execute();
				$tag_id = $this->app['db']->lastInsertId();
			}

			/* Link note with tag. This must be done with a plain SQL query, as
			 * Doctrine does not understand UPSERTS at the moment. */
			$this->app['db']->executeQuery($sql_link,
				array('note' => $note, 'tag' => $tag_id));

			$qb_rm_old->andWhere($qb_rm_old->expr()->neq('tag',
				$qb_rm_old->createNamedParameter($tag_id)));
		}


		/* Relations not defined by $tags will be deleted. */
		$qb_rm_old->execute();
	}


	/** \brief Add a new note to the database.
	 *
	 *
	 * \param $title Note title.
	 * \param $content Note content.
	 * \param $priority Note priority.
	 * \param $done_until Date until note must be done.
	 * \param $tags Array of tags note should be linked to.
	 */
	public function add(string $title, string $content, int $priority,
	                    string $done_until, array $tags)
	{
		/* Insert note into database. */
		$qb = new QueryBuilder($this->app['db']);
		$qb->insert('notes')
		   ->values(array('title' => ':title',
		                  'content' => ':content',
		                  'priority' => ':priority',
		                  'created_at' => 'now()'))
		   ->setParameter('title', $title)
		   ->setParameter('content', $content ?: null)
		   ->setParameter('priority', $priority);

		if ((strtotime($done_until) - time()) > 600)
			$qb->setValue('done_until', ':done_until')
			   ->setParameter('done_until', $done_until);

		$qb->execute();


		/* Handle the tags. */
		$this->set_tags($this->app['db']->lastInsertId(), $tags);
	}


	public function update(int $id, string $title, string $content,
	                       int $priority, string $done_until, array $tags) {
		/* Push note changes into the database. */
		$qb = new QueryBuilder($this->app['db']);
		$qb->update('notes')
		   ->set('title', ':title')
		   ->set('content', ':content')
		   ->set('priority', ':priority')
		   ->set('updated_at', 'now()')
		   ->where($qb->expr()->eq('id', ':note'))
		   ->setParameter('note', $id)
		   ->setParameter('title', $title)
		   ->setParameter('content', $content ?: null)
		   ->setParameter('priority', $priority);

		if ((strtotime($done_until) - time()) > 600)
			$qb->set('done_until', ':done_until')
			   ->setParameter('done_until', $done_until);

		$qb->execute();


		/* Handle the tags. */
		$this->set_tags($id, $tags);
	}


	/** \brief Delete note \p $id.
	 *
	 * \param $id ID of note to be deleted.
	 */
	public function delete(int $id)
	{
		$qb = new QueryBuilder($this->app['db']);
		$qb->setParameter('note', $id);
		$qb->delete('notes_tags')
		   ->where($qb->expr()->eq('note', ':note'))
		   ->execute();
		$qb->delete('notes')
		   ->where($qb->expr()->eq('id', ':note'))
		   ->execute();
	}
}

?>
