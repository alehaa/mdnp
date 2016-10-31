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

namespace MDNP\Notes\Search;

use Doctrine\Common\Lexer\AbstractLexer;


/** \brief Search query lexer
 *
 * \details To parse the search queries, we'll use a custom lexer to analyze the
 *  used query.
 */
class Lexer extends AbstractLexer {
	const T_NONE = 0; ///< The token has no special meaning.

	const T_STRING   = 1; ///< The token is a string.
	const T_STATUS   = 2; ///< The token is a status identifier.
	const T_PRIORITY = 3; ///< The token is a priority identifier.
	const T_TAG      = 4; ///< The token is a tag identifier.


	/** \brief Get the patterns to be catched by the lexer.
	 *
	 *
	 * \return array The patterns to be catched.
	 */
	protected function getCatchablePatterns(): array
	{
		return array(
			'[[:alnum:]]*:[[:alnum:]]*',
			'"[^"]*"'
		);
	}


	/** \brief Get patterns to be not catched by the lexer.
	 *
	 *
	 * \return array The patterns to be not catched.
	 */
	protected function getNonCatchablePatterns(): array
	{
		return array('\s+');
	}


	/** \brief Get the type of \p $value.
	 *
	 * \details Analyze the type of \p $value and and parse it, so the data may
	 *  be used directly in other functions.
	 *
	 *
	 * \param mixed &$value The value to be analyzed.
	 *
	 * \return int Type of \p $value.
	 */
	protected function getType(&$value): int
	{
		if (preg_match('/"(.*)"/'.$this->getModifiers(), $value,
		                    $matches)) {
			$value = $matches[1];
			return self::T_STRING;
		}

		else if (preg_match('/is:(.*)/'.$this->getModifiers(), $value,
		                    $matches)) {
			$value = $matches[1];
			return self::T_STATUS;
		}

		else if (preg_match('/priority:(.*)/'.$this->getModifiers(), $value,
		                    $matches)) {
			$value = $matches[1];
			return self::T_PRIORITY;
		}

		else if (preg_match('/tag:(.*)/'.$this->getModifiers(), $value,
		                    $matches)) {
			$value = $matches[1];
			return self::T_TAG;
		}


		return self::T_NONE;
	}
}
