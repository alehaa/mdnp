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


/** \brief Storage class for MDNP Notes.
 *
 * \details This class handles operations on the \ref NoteStorage. It acts as a
 *  proxy between the \ref NoteController and the underlaying database.
 */
class NoteStorage
{
	private $app; ///< Copy of the calling app instance.


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

		return $this->convert($note);
	}
}

?>
