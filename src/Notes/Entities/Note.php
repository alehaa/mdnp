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
use Doctrine\Common\Collections\ArrayCollection;
use MDNP\Notes\Entities\tag;
use RuntimeException;


/** \brief Note.
 *
 * \details This class manages a full note.
 *
 *
 * @Entity(repositoryClass="MDNP\Notes\Repositories\Notes")
 * @Table(name="notes")
 */
class Note
{
	/** \brief ID of the note.
	 *
	 *
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;

	/** \brief Note title.
	 *
	 *
	 * @Column(type="string")
	 */
	protected $title;

	/** \brief Note content.
	 *
	 *
	 * @Column(type="text", nullable=true)
	 */
	protected $content = null;

	/** \brief Note priority.
	 *
	 *
	 * @Column(type="smallint")
	 */
	protected $priority = 0;

	/** \brief Date until the note should have been done.
	 *
	 *
	 * @Column(type="datetimetz", nullable=true)
	 */
	protected $deadline = null;

	/** \brief Status of note.
	 *
	 *
	 * @Column(type="string", length=8)
	 */
	protected $status = 'open';

	/** \brief tags associated with the note.
	 *
	 *
	 * @ManyToMany(targetEntity="Tag")
	 * @JoinTable(name="notes_tags")
	 */
	protected $tags = null;

	/** \brief Date & time of creating the note.
	 *
	 *
	 * @Column(type="datetimetz")
	 */
	protected $created_at;

	/** \brief Date & time of the last changeset of this note.
	 *
	 *
	 * @Column(type="datetimetz", nullable=true)
	 */
	protected $updated_at = null;


	/** \brief Constructor.
	 *
	 * \details Create a new note.
	 */
	public function __construct()
	{
		$this->tags = new ArrayCollection;
		$this->created_at = new DateTime;
	}


	/** \brief Get the notes ID.
	 *
	 *
	 * \return int The tags ID.
	 */
	public function getId(): int
	{
		return $this->id;
	}


	/** \brief Get the notes title.
	 *
	 *
	 * \return string The notes title.
	 */
	public function getTitle(): string
	{
		return $this->title;
	}


	/** \brief Set the notes title.
	 *
	 *
	 * \param string $title The new title. Maximum length is 255 chars.
	 *
	 * \return \ref Note Reference to this \ref Note.
	 */
	public function setTitle(string $title): self
	{
		/* The title must not be longer than 255 characters, as the database
		 * stores it into a varchar(255). */
		if (strlen($title) > 255)
			throw new RuntimeException('Title is more than 255 chars.');
		else if (empty($title))
			throw new RuntimeException('Title must not be empty.');

		$this->title = $title;
		return $this;
	}


	/** \brief Get the notes content.
	 *
	 *
	 * \return mixed The notes content.
	 */
	public function getContent()
	{
		return $this->content;
	}


	/** \brief Set the notes content.
	 *
	 *
	 * \param string $content The new content.
	 *
	 * \return \ref Note Reference to this \ref Note.
	 */
	public function setContent(string $content): self
	{
		$this->content = $content;
		return $this;
	}


	/** \brief Get the notes priority.
	 *
	 *
	 * \return int The notes priority.
	 */
	public function getPriority(): int
	{
		return $this->priority;
	}


	/** \brief Set the notes content.
	 *
	 *
	 * \param int $priority The new priority. Must be between -1 and 2. Higher
	 *  value means higher priority.
	 *
	 * \return \ref Note Reference to this \ref Note.
	 */
	public function setPriority(int $priority): self
	{
		/* Valid priorities are in the range -1 to 2. If the new priority does
		 * not match the requirements, an exception will be thrown. */
		if (($priority < -1) || ($priority > 2))
			throw new RuntimeException('Priority must be between -1 and 2.');

		$this->priority = $priority;
		return $this;
	}


	/** \brief Get the notes deadline.
	 *
	 *
	 * \return mixed The notes deadline.
	 */
	public function getDeadline()
	{
		return $this->deadline;
	}


	/** \brief Set the notes deadline.
	 *
	 *
	 * \param mixed $deadline The new deadline.
	 *
	 * \return \ref Note Reference to this \ref Note.
	 */
	public function setDeadline($deadline): self
	{
		$this->deadline = $deadline;
		return $this;
	}


	/** \brief Check if note is expired.
	 *
	 *
	 * \return true The note has passed the \ref deadline.
	 * \return false The \ref deadline is in the future.
	 */
	public function isExpired(): bool
	{
		if ($this->deadline == null)
			return false;

		return ($this->deadline < (new DateTime('now')));
	}


	/** \brief Get the status of the note.
	 *
	 *
	 * \return string The notes status.
	 */
	public function getStatus(): string
	{
		return $this->status;
	}


	/** \brief Set the notes status.
	 *
	 *
	 * \param string $status The new status.
	 *
	 * \return \ref Note Reference to this \ref Note.
	 */
	public function setStatus(string $status): self
	{
		$this->status = $status;
		return $this;
	}


	/** \brief Get the tags associated with the note.
	 *
	 *
	 * \return mixed Tags associated with the note.
	 */
	public function getTags()
	{
		return $this->tags;
	}


	/** \brief Add a tag to the note.
	 *
	 *
	 * \param \ref Tag $tag Tag to be referenced with the note.
	 *
	 * \return \ref Note Reference to this \ref Note.
	 */
	public function addTag(Tag $tag): self
	{
		$this->tags[] = $tag;
		return $this;
	}


	/** \brief Remove a tag from the note.
	 *
	 *
	 * \param \ref Tag $tag Tag to be dereferenced with the note.
	 *
	 * \return \ref Note Reference to this \ref Note.
	 */
	public function removeTag(Tag $tag): self
	{
		$this->tags->removeElement($tag);
		return $this;
	}


	/** \brief Get the date and time of creating this note.
	 *
	 *
	 * \return DateTime The create date and time of the note.
	 */
	public function getCreated(): DateTime
	{
		return $this->created_at;
	}


	/** \brief Get the date and time of last updating this note.
	 *
	 *
	 * \return mixed The last update date and time of the note.
	 */
	public function getUpdated()
	{
		return $this->updated_at;
	}
}
