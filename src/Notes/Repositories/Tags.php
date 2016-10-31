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

namespace MDNP\Notes\Repositories;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityRepository;
use MDNP\Notes\Entities\Tag;


/** \brief Tag repository.
 *
 * \details This repository will be used to lookup tags.
 */
class Tags extends EntityRepository
{
	/** \brief Find or create a tag named \p $name.
	 *
	 * \details This function searches for a tag named \p $name. If the tag does
	 *  not exist, a new tag will be created.
	 *
	 *
	 * \param $name Name of the tag to be searched or creaded.
	 *
	 * \return \ref Tag Found or created tag object.
	 *
	 *
	 * \todo This function is not thread-safe, but Doctrine does not support
	 *  insert ignore at the moment, so we can't handle this without flushing.
	 */
	public function findOrCreate(string $name): Tag
	{
		/* If a tag with the searched name could be found, we can abort
		 * searching now. */
		$tag = $this->findOneBy(array('name' => $name));
		if ($tag !== null)
			return $tag;

		/* Otherwise we'll create a new tag. */
		$tag = new Tag($name);
		$this->getEntityManager()->persist($tag);
		return $tag;
	}
}
