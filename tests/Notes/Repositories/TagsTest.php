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


class TagsTest extends AppTestCase
{
	/** \brief Check for an existing tag or create a new one.
	 *
	 * \note It is asumed, that the database does not contain a tag named
	 *  'hello1234' before running this method the first time, as travis CI will
	 *  run this check on a clean database.
	 */
	public function findOrCreate() {
		$tag = $this->app['orm.em']
		            ->getRepository('MDNP\Notes\Entities\Tag')
		            ->findOrCreate('hello1234');
		$this->app['orm.em']
		     ->flush();

		/* Check that a tag was returned. */
		$this->assertNotEquals($tag, null);

		/* Check if the same tag is returned over multiple iterations. */
		static $tmp = null;
		if ($tmp == null)
			$tmp = $tag;
		else
			$this->assertEquals($tag, $tmp);
	}


	/** \brief Check for an existing tag or create a new one.
	 *
	 * \note It is asumed, that the database does not contain a tag named
	 *  'hello1234' before running this test, as travis CI will run this check
	 *  on a clean database.
	 */
	public function testFindOrCreate1()
	{
		$this->findOrCreate();
	}


	/** \brief Check for an existing tag or create a new one.
	 *
	 *
	 * @depends testFindOrCreate1
	 */
	public function testFindOrCreate2()
	{
		$this->findOrCreate();
	}


	/** \brief Check if the tag in the database has an ID set.
	 */
	public function testTagHasId()
	{
		$tag = $this->app['orm.em']
		            ->getRepository('MDNP\Notes\Entities\Tag')
		            ->findOrCreate('hello1234');
		$this->app['orm.em']
		     ->flush();

		$this->assertNotEquals($tag->getId(), null);
	}
}
