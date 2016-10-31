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

use MDNP\AppTestCase;
use TypeError;


/** \brief Test cases for \ref Tag.
 */
class TagTest extends AppTestCase
{
	/** \brief Check if \ref Tag constructor sets the tag name.
	 */
	public function testConstructorName()
	{
		$tag = new Tag('foo');
		$this->assertEquals($tag->getName(), 'foo');
	}


	/** \brief Check if \ref Tag automatically sets the created_at value.
	 */
	public function testCreated()
	{
		$tag = new Tag('foo');
		$this->assertNotEquals($tag->getCreated(), null);
	}


	/** \brief Check if an empty \ref Tag does not contain an ID.
	 */
	public function testIdException()
	{
		$this->expectException(TypeError::class);

		$tag = new Tag('foo');
		$tag->getId();
	}


	/** \brief Check if the tag in the database has an ID set.
	 */
	public function testTagHasId()
	{
		$tag = new Tag('test123');
		$this->app['orm.em']->persist($tag);
		$this->app['orm.em']->flush();

		$this->assertNotEquals($tag->getId(), null);
	}
}
