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


/** \brief Class for managing tags.
 *
 *
 * @Entity(repositoryClass="MDNP\Notes\Repositories\Tags")
 * @Table(name="tags")
 */
class Tag
{
	/** \brief ID of the tag.
	 *
	 *
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;


	/** \brief Name of the tag.
	 *
	 *
	 * @Column(type="string", unique=true)
	 */
	protected $name;


	/** \brief Date & time of creating the tag.
	 *
	 *
	 * @Column(type="datetimetz")
	 */
	protected $created_at;


	/** \brief Constructor.
	 *
	 * \details Create a new tag with \p $name.
	 *
	 * \note The \p $name can't be changed at a later time, as already existing
	 *  notes may reference it already.
	 *
	 *
	 * \param string $name The tag name.
	 */
	public function __construct(string $name)
	{
		$this->name = strtolower($name);
		$this->created_at = new DateTime;
	}


	/** \brief Get the tags ID.
	 *
	 *
	 * \return int The tags ID.
	 */
	public function getId(): int
	{
		return $this->id;
	}


	/** \brief Get the tags name.
	 *
	 *
	 * \return string The tag name.
	 */
	public function getName(): string
	{
		return $this->name;
	}


	/** \brief Get the date and time of creating this tag.
	 *
	 *
	 * \return DateTime The create date and time of the tag.
	 */
	public function getCreated(): DateTime
	{
		return $this->created_at;
	}
}
