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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;


/** \brief Extended Doctrine QueryBuilder to build queries to retrieve notes.
 *
 * \details SQL queries to get notes from the database have to be generated at
 *  different places in the code. We'll use a specialized QueryBuilder to
 *  build a basic query, so we have to specialize it for the specific query only
 *  but may reuse the default settings.
 */
class NoteQueryBuilder extends QueryBuilder
{
	/** \brief Constructor.
	 *
	 * \details Initialize the parent class and define some basic settings for a
	 *  regular note query.
	 *
	 *
	 * \param $conn The database connection.
	 */
	public function __construct(Connection $conn)
	{
		/* Call the parent constructor to initialize the QueryBuilder. */
		parent::__construct($conn);


		/* Define some basic settings for a regular note query. */
		$this->select('notes.id as id',
		              'notes.title as title',
		              'notes.priority as priority',
		              'extract(epoch from date_trunc(\'minute\',
		                notes.done_until)) as done_until',
		              '(case when now() > notes.done_until then 1 else 0 end)
		                as expired',
		              'array_to_json(array(
		                SELECT tags.name
		                FROM tags
		                JOIN notes_tags ON tags.id = notes_tags.tag
		                WHERE notes_tags.note = notes.id
		              )) as tags')
		     ->from('notes')
		     ->addOrderBy('expired', 'DESC')
		     ->addOrderBy('done_until')
		     ->addOrderBy('priority', 'DESC');
	}


	/** \brief Check \p search for special commands.
	 *
	 * \details This function searches for keywords in \p $search to generate the
	 *  where clauses for the SQL query depending on \p $search. The following
	 *  keywords are supported:
	 *
	 * * 'is:open' The note must have the state open.
	 * * 'is:closed' The note must have the state closed.
	 * * 'priority:low' Notes with low priority.
	 * * 'priority:normal' Notes with normal priority.
	 * * 'priority:medium' Notes with medium priority.
	 * * 'priority:high' Notes with high priority.
	 * * 'tag:*' Notes with a specific tag.
	 *
	 * \note The original search string will be converted to lowercase and the
	 *  keywords will be deleted from \p $search after processing them.
	 *
	 *
	 * \param $search The search string.
	 */
	private function find_commands(string &$search)
	{
		/* Search via regex in search for the search keywords. The input string
		 * will be converted to lowercase, so it'll be easyier to lookup the
		 * keywords. This has no side-effect on the search query, as the search
		 * for other words will be done case-insensitive. */
		$search = strtolower($search);
		preg_match_all("/(is|priority|tag):([[:alnum:]]+)/", $search, $matches);

		foreach ($matches[1] as $id => $keyword)
			switch ($keyword) {
				case 'is':
					switch ($matches[2][$id]) {
						case 'open':
							$this->andwhere('notes.open = true');
							break;
						case 'closed':
							$this->andwhere('notes.open = false');
							break;
					}
					break;

				case 'priority':
					switch ($matches[2][$id]) {
						case 'low':
							$this->andwhere('notes.priority = -1');
							break;
						case 'normal':
							$this->andwhere('notes.priority = 0');
							break;
						case 'medium':
							$this->andwhere('notes.priority = 1');
							break;
						case 'high':
							$this->andwhere('notes.priority = 2');
							break;
					}
					break;

				case 'tag':
					$this->leftJoin('notes', 'notes_tags', 'notes_tags',
					                'notes_tags.note = notes.id')
					     ->leftJoin('notes_tags', 'tags', 'tags',
					                'notes_tags.tag = tags.id')
					     ->andwhere('lower(tags.name) = :tag')
					     ->setParameters(array('tag' => $matches[2][$id]));
					break;
			}

		/* Remove all special commands from search string. */
		$search = trim(preg_replace("/(is|priority|tag):([[:alnum:]]+)/", null,
		                            $search));
	}


	/** \brief Define a search string for SQL query.
	 *
	 * \details This function evaluates \p search for special commands and
	 *  strings notes should contain and defines rules depending on that for
	 *  the resulting SQL query.
	 *
	 *
	 * \param $search The search string.
	 *
	 * \return This QueryBuilder instance.
	 */
	public function search(string $search): NoteQueryBuilder
	{
		$this->find_commands($search);

		/* If search contains more than just special commands, append the string
		 * to search in title and content for it (case insensitive). */
		if (!empty($search))
			$this->andwhere(
				$this->expr()->orX(
					$this->expr()->like('lower(notes.title)', ':search'),
					$this->expr()->like('lower(notes.content)', ':search')
			))
			->setParameters(array('search' => '%'.$search.'%'));

		return $this;
	}


	/** \brief Wrapper for \ref select.
	 *
	 * \details As we have already defined a select in \ref __construct, we'll
	 *  have to use addSelect for any future calls to add selects. As the user
	 *  did not call select before, he might think he can use it for the first
	 *  call - so this wrapper will redirect the first call to addSelect.
	 *
	 *
	 * \param $select The fields to be selected.
	 *
	 * \return Return value of addSelect will be pass through.
	 */
	public function select($select = null)
	{
		$selects = is_array($select) ? $select : func_get_args();
		return $this->addSelect($selects);
	}
}

?>
