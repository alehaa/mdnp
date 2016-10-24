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

/* Table for all notes.
 *
 * This table stores all notes with their core attributes. A note will have a
 * title and the open state of this note (if it's done or not) and optional
 * content, priority and a timestamp until this note should be done. The time of
 * creating an entity and updating it will be saved in created_at and update_at.
 */
CREATE TABLE notes (
	id serial PRIMARY KEY,
	title varchar(256) NOT NULL,
	content text DEFAULT NULL,
	priority smallint DEFAULT 0,
	done_until timestamp with time zone DEFAULT NULL,
	open boolean DEFAULT true,

	created_at timestamp with time zone NOT NULL,
	updated_at timestamp with time zone DEFAULT NULL
);


/* Table for all tags.
 */
CREATE TABLE tags (
	id serial PRIMARY KEY,
	name varchar(32) NOT NULL UNIQUE,

	created_at timestamp with time zone NOT NULL
);


/* Table for refences between notes and tags.
 */
CREATE TABLE notes_tags (
	note serial REFERENCES notes (id),
	tag serial REFERENCES tags (id),
	UNIQUE(note, tag),

	created_at timestamp with time zone NOT NULL,
	updated_at timestamp with time zone DEFAULT NULL
);
