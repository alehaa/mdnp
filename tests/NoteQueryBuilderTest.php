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

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use MDNP\Notes\NoteQueryBuilder;
use PHPUnit\Framework\TestCase;


class NoteQueryBuilderTest extends TestCase
{
	private $conn; ///< Doctrine database connection.


	/** \brief Initialize the database connection.
	 */
	protected function setUp()
	{
		$config = new Configuration();
		$params = array(
			'driver' => 'pdo_pgsql',
			'host' => $GLOBALS['db_host'],
			'dbname' => $GLOBALS['db_name'],
			'user' => $GLOBALS['db_user'],
			'password' => $GLOBALS['db_pass']
		);
		$this->conn = DriverManager::getConnection($params, $config);
	}


	/** \brief Compare two SQL queries.
	 *
	 * \details This function compares two SQL queries. Whitespace and newlines
	 *  will be deleted for comparison.
	 *
	 *
	 * \p a String that must match \p b.
	 * \p b
	 */
	private function assertSQL(string $a, string $b)
	{
		$this->assertEquals(
			trim(preg_replace('([[:space:]]+)', ' ',
			     str_replace("\n", ' ', $a))),
			trim(preg_replace('([[:space:]]+)', ' ',
			     str_replace("\n", ' ', $b)))
		);
	}


	/** \brief Check empty \ref NoteQueryBuilder result.
	 */
	public function testEmpty()
	{
		$qb = new NoteQueryBuilder($this->conn);
		$sql = "SELECT
					notes.id as id,
					notes.title as title,
					notes.priority as priority,
					extract(epoch from date_trunc('minute', notes.done_until))
						as done_until,
					(case when now() > notes.done_until then 1 else 0 end)
						as expired,
					array_to_json(array(
						SELECT tags.name
						FROM tags
						JOIN notes_tags ON tags.id = notes_tags.tag
						WHERE notes_tags.note = notes.id
						)) as tags
				FROM notes
				ORDER BY expired DESC, done_until ASC, priority DESC";

		$this->assertSQL((string) $qb, $sql);
	}


	/** \brief Check if \ref NoteQueryBuilder detects 'is:open'.
	 */
	public function testIsOpen()
	{
		$qb = new NoteQueryBuilder($this->conn);
		$qb->search('is:open');
		$sql = "SELECT
					notes.id as id,
					notes.title as title,
					notes.priority as priority,
					extract(epoch from date_trunc('minute', notes.done_until))
						as done_until,
					(case when now() > notes.done_until then 1 else 0 end)
						as expired,
					array_to_json(array(
						SELECT tags.name
						FROM tags
						JOIN notes_tags ON tags.id = notes_tags.tag
						WHERE notes_tags.note = notes.id
						)) as tags
				FROM notes
				WHERE notes.open = true
				ORDER BY expired DESC, done_until ASC, priority DESC";

		$this->assertSQL((string) $qb, $sql);
	}


	/** \brief Check if \ref NoteQueryBuilder detects 'is:closed'.
	 */
	public function testIsClosed()
	{
		$qb = new NoteQueryBuilder($this->conn);
		$qb->search('is:closed');
		$sql = "SELECT
					notes.id as id,
					notes.title as title,
					notes.priority as priority,
					extract(epoch from date_trunc('minute', notes.done_until))
						as done_until,
					(case when now() > notes.done_until then 1 else 0 end)
						as expired,
					array_to_json(array(
						SELECT tags.name
						FROM tags
						JOIN notes_tags ON tags.id = notes_tags.tag
						WHERE notes_tags.note = notes.id
						)) as tags
				FROM notes
				WHERE notes.open = false
				ORDER BY expired DESC, done_until ASC, priority DESC";

		$this->assertSQL((string) $qb, $sql);
	}


	/** \brief Check if \ref NoteQueryBuilder detects 'priority:low'.
	 */
	public function testPriorityLow()
	{
		$qb = new NoteQueryBuilder($this->conn);
		$qb->search('priority:low');
		$sql = "SELECT
					notes.id as id,
					notes.title as title,
					notes.priority as priority,
					extract(epoch from date_trunc('minute', notes.done_until))
						as done_until,
					(case when now() > notes.done_until then 1 else 0 end)
						as expired,
					array_to_json(array(
						SELECT tags.name
						FROM tags
						JOIN notes_tags ON tags.id = notes_tags.tag
						WHERE notes_tags.note = notes.id
						)) as tags
				FROM notes
				WHERE notes.priority = -1
				ORDER BY expired DESC, done_until ASC, priority DESC";

		$this->assertSQL((string) $qb, $sql);
	}


	/** \brief Check if \ref NoteQueryBuilder detects 'priority:low'.
	 */
	public function testPriorityNormal()
	{
		$qb = new NoteQueryBuilder($this->conn);
		$qb->search('priority:normal');
		$sql = "SELECT
					notes.id as id,
					notes.title as title,
					notes.priority as priority,
					extract(epoch from date_trunc('minute', notes.done_until))
						as done_until,
					(case when now() > notes.done_until then 1 else 0 end)
						as expired,
					array_to_json(array(
						SELECT tags.name
						FROM tags
						JOIN notes_tags ON tags.id = notes_tags.tag
						WHERE notes_tags.note = notes.id
						)) as tags
				FROM notes
				WHERE notes.priority = 0
				ORDER BY expired DESC, done_until ASC, priority DESC";

		$this->assertSQL((string) $qb, $sql);
	}


	/** \brief Check if \ref NoteQueryBuilder detects 'priority:medium'.
	 */
	public function testPriorityMedium()
	{
		$qb = new NoteQueryBuilder($this->conn);
		$qb->search('priority:medium');
		$sql = "SELECT
					notes.id as id,
					notes.title as title,
					notes.priority as priority,
					extract(epoch from date_trunc('minute', notes.done_until))
						as done_until,
					(case when now() > notes.done_until then 1 else 0 end)
						as expired,
					array_to_json(array(
						SELECT tags.name
						FROM tags
						JOIN notes_tags ON tags.id = notes_tags.tag
						WHERE notes_tags.note = notes.id
						)) as tags
				FROM notes
				WHERE notes.priority = 1
				ORDER BY expired DESC, done_until ASC, priority DESC";

		$this->assertSQL((string) $qb, $sql);
	}


	/** \brief Check if \ref NoteQueryBuilder detects 'priority:high'.
	 */
	public function testPriorityHigh()
	{
		$qb = new NoteQueryBuilder($this->conn);
		$qb->search('priority:high');
		$sql = "SELECT
					notes.id as id,
					notes.title as title,
					notes.priority as priority,
					extract(epoch from date_trunc('minute', notes.done_until))
						as done_until,
					(case when now() > notes.done_until then 1 else 0 end)
						as expired,
					array_to_json(array(
						SELECT tags.name
						FROM tags
						JOIN notes_tags ON tags.id = notes_tags.tag
						WHERE notes_tags.note = notes.id
						)) as tags
				FROM notes
				WHERE notes.priority = 2
				ORDER BY expired DESC, done_until ASC, priority DESC";

		$this->assertSQL((string) $qb, $sql);
	}


	/** \brief Check if \ref NoteQueryBuilder detects 'tag:*'.
	 */
	public function testTag()
	{
		$qb = new NoteQueryBuilder($this->conn);
		$qb->search('tag:test');
		$sql = "SELECT
					notes.id as id,
					notes.title as title,
					notes.priority as priority,
					extract(epoch from date_trunc('minute', notes.done_until))
						as done_until,
					(case when now() > notes.done_until then 1 else 0 end)
						as expired,
					array_to_json(array(
						SELECT tags.name
						FROM tags
						JOIN notes_tags ON tags.id = notes_tags.tag
						WHERE notes_tags.note = notes.id
						)) as tags
				FROM notes
				LEFT JOIN notes_tags notes_tags ON notes_tags.note = notes.id
				LEFT JOIN tags tags ON notes_tags.tag = tags.id
				WHERE lower(tags.name) = :tag
				ORDER BY expired DESC, done_until ASC, priority DESC";

		$this->assertSQL((string) $qb, $sql);
	}


	/** \brief Check if \ref NoteQueryBuilder detects additional input.
	 */
	public function testAdditional()
	{
		$qb = new NoteQueryBuilder($this->conn);
		$qb->search('foo bar');
		$sql = "SELECT
					notes.id as id,
					notes.title as title,
					notes.priority as priority,
					extract(epoch from date_trunc('minute', notes.done_until))
						as done_until,
					(case when now() > notes.done_until then 1 else 0 end)
						as expired,
					array_to_json(array(
						SELECT tags.name
						FROM tags
						JOIN notes_tags ON tags.id = notes_tags.tag
						WHERE notes_tags.note = notes.id
						)) as tags
				FROM notes
				WHERE (lower(notes.title) LIKE :search)
					OR (lower(notes.content) LIKE :search)
				ORDER BY expired DESC, done_until ASC, priority DESC";

		$this->assertSQL((string) $qb, $sql);
	}


	/** \brief Check if \ref NoteQueryBuilder detects combined commands.
	 */
	public function testCombined1()
	{
		$qb = new NoteQueryBuilder($this->conn);
		$qb->search('is:open tag:test');
		$sql = "SELECT
					notes.id as id,
					notes.title as title,
					notes.priority as priority,
					extract(epoch from date_trunc('minute', notes.done_until))
						as done_until,
					(case when now() > notes.done_until then 1 else 0 end)
						as expired,
					array_to_json(array(
						SELECT tags.name
						FROM tags
						JOIN notes_tags ON tags.id = notes_tags.tag
						WHERE notes_tags.note = notes.id
						)) as tags
				FROM notes
				LEFT JOIN notes_tags notes_tags ON notes_tags.note = notes.id
				LEFT JOIN tags tags ON notes_tags.tag = tags.id
				WHERE (notes.open = true) AND (lower(tags.name) = :tag)
				ORDER BY expired DESC, done_until ASC, priority DESC";

		$this->assertSQL((string) $qb, $sql);
	}


	/** \brief Check if \ref NoteQueryBuilder detects combined commands and
	 *  additional input.
	 */
	public function testCombined2()
	{
		$qb = new NoteQueryBuilder($this->conn);
		$qb->search('is:open tag:test foo bar');
		$sql = "SELECT
					notes.id as id,
					notes.title as title,
					notes.priority as priority,
					extract(epoch from date_trunc('minute', notes.done_until))
						as done_until,
					(case when now() > notes.done_until then 1 else 0 end)
						as expired,
					array_to_json(array(
						SELECT tags.name
						FROM tags
						JOIN notes_tags ON tags.id = notes_tags.tag
						WHERE notes_tags.note = notes.id
						)) as tags
				FROM notes
				LEFT JOIN notes_tags notes_tags ON notes_tags.note = notes.id
				LEFT JOIN tags tags ON notes_tags.tag = tags.id
				WHERE (notes.open = true) AND (lower(tags.name) = :tag)
					AND ((lower(notes.title) LIKE :search)
						OR (lower(notes.content) LIKE :search))
				ORDER BY expired DESC, done_until ASC, priority DESC";

		$this->assertSQL((string) $qb, $sql);
	}
}
