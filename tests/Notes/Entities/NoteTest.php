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

namespace MDNP\Notes\Entities;

use DateTime;
use MDNP\AppTestCase;
use RuntimeException;
use TypeError;


/** \brief Test cases for \ref Tag.
 */
class NoteTest extends AppTestCase
{
	/** \brief Check if \ref Note automatically sets the created_at value.
	 */
	public function testCreated()
	{
		$note = new Note;
		$this->assertNotEquals($note->getCreated(), null);
	}




	/** \brief Check if an empty \ref Note does not contain an ID.
	 */
	public function testIdException()
	{
		$this->expectException(TypeError::class);

		$note = new Note;
		$note->getId();
	}


	/** \brief Check if the note in the database has an ID set.
	 */
	public function testHasId()
	{
		$note = new Note;
		$note->setTitle('test');
		$this->app['orm.em']->persist($note);
		$this->app['orm.em']->flush();

		$this->assertNotEquals($note->getId(), null);
	}




	/** \brief Check if \ref Note title can't be more than 255 chars.
	 */
	public function testTitleToLong()
	{
		$this->expectException(RuntimeException::class);

		$note = new Note;
		$note->setTitle(sprintf('%-256s', ''));
	}


	/** \brief Check if \ref Note title can't be empty.
	 */
	public function testTitleEmpty()
	{
		$this->expectException(RuntimeException::class);

		$note = new Note;
		$note->setTitle('');
	}


	/** \brief Check if \ref Note title can be set and get.
	 */
	public function testTitleSetAndGet()
	{
		$note = new Note;
		$note->setTitle('foo');
		$this->assertEquals($note->getTitle(), 'foo');
	}




	/** \brief Check if \ref Note content may be empty.
	 *
	 * \details This check has the intention to check that TypeHinting is
	 *  configured right and null may be returned, too.
	 */
	public function testContentEmpty()
	{
		$note = new Note;
		$this->assertEquals($note->getContent(), null);
	}


	/** \brief Check if \ref Note content can be set and get.
	 */
	public function testContentSetAndGet()
	{
		$note = new Note;
		$note->setContent('foo');
		$this->assertEquals($note->getContent(), 'foo');
	}




	/** \brief Check if \ref Note priority has a default value.
	 */
	public function testPriorityDefault()
	{
		$note = new Note;
		$this->assertEquals($note->getPriority(), 0);
	}


	/** \brief Check for \ref Note priority lower bound.
	 */
	public function testPriorityToLow()
	{
		$this->expectException(RuntimeException::class);

		$note = new Note;
		$note->setPriority(-2);
	}


	/** \brief Check for \ref Note priority higher bound.
	 */
	public function testPriorityToHigh()
	{
		$this->expectException(RuntimeException::class);

		$note = new Note;
		$note->setPriority(3);
	}


	/** \brief Check if \ref Note priority can be set and get.
	 */
	public function testPrioritySetAndGet()
	{
		$note = new Note;
		$note->setPriority(2);
		$this->assertEquals($note->getPriority(), 2);
	}




	/** \brief Check if \ref Note priority has a default value.
	 */
	public function testDeadlineDefault()
	{
		$note = new Note;
		$this->assertEquals($note->getDeadline(), null);
	}


	/** \brief Check if \ref Note deadline can be set and get.
	 */
	public function testDeadlineSetAndGet()
	{
		$deadline = new DateTime('now');

		$note = new Note;
		$note->setDeadline($deadline);
		$this->assertEquals($note->getDeadline(), $deadline);
	}


	/** \brief Check if \ref Note deadline did not expired.
	 */
	public function testDeadlineNotExpired()
	{
		$note = new Note;
		$note->setDeadline(new DateTime('now + 10 minutes'));
		$this->assertFalse($note->isExpired());
	}


	/** \brief Check if \ref Note deadline did not expired.
	 */
	public function testDeadlineExpired()
	{
		$note = new Note;
		$note->setDeadline(new DateTime('now - 10 minutes'));
		$this->assertTrue($note->isExpired());
	}




	/** \brief Check if \ref Note has no tags by default.
	 */
	public function testTagsDeafault()
	{
		$note = new Note;
		$this->assertCount(0, $note->getTags());
	}


	/** \brief Check if \ref Tag can be added to \ref Note.
	 */
	public function testTagsAdd()
	{
		$note = new Note;
		$tag = new Tag('foo');
		$note->addTag($tag);
		$this->assertEquals($note->getTags()[0], $tag);
	}


	/** \brief Check if \ref Tag can be removed from \ref Note.
	 */
	public function testTagsRemove()
	{
		$note = new Note;
		$tag = new Tag('foo');
		$note->addTag($tag);
		$note->removeTag($tag);
		$this->assertCount(0, $note->getTags());
	}




	/** \brief Check if \ref Note changed_at default value is null.
	 */
	public function testUpdated()
	{
		$note = new Note;
		$this->assertEquals($note->getUpdated(), null);
	}
}
