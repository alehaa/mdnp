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

use PHPUnit\Framework\TestCase;


/** \brief Test cases for \ref Lexer.
 */
class LexerTest extends TestCase
{
	/** \brief Check if \ref Lexer detects unquouted strings.
	 */
	public function testUnquotedString()
	{
		$lexer = new Lexer();
		$lexer->setInput('foo');
		$lexer->moveNext();

		$this->assertEquals($lexer->lookahead['type'], Lexer::T_NONE);
		$this->assertEquals($lexer->lookahead['value'], 'foo');
	}


	/** \brief Check if \ref Lexer detects quouted strings.
	 */
	public function testQuotedString()
	{
		$lexer = new Lexer();
		$lexer->setInput('"foo bar"');
		$lexer->moveNext();

		$this->assertEquals($lexer->lookahead['type'], Lexer::T_STRING);
		$this->assertEquals($lexer->lookahead['value'], 'foo bar');
	}




	/** \brief Check if \ref Lexer detects 'is:open'.
	 */
	public function testStatus()
	{
		$lexer = new Lexer();
		$lexer->setInput('is:open');
		$lexer->moveNext();

		$this->assertEquals($lexer->lookahead['type'], Lexer::T_STATUS);
		$this->assertEquals($lexer->lookahead['value'], 'open');
	}


	/** \brief Check if \ref Lexer detects 'is:closed'.
	 */
	public function testIsClosed()
	{
		$lexer = new Lexer();
		$lexer->setInput('is:closed');
		$lexer->moveNext();

		$this->assertEquals($lexer->lookahead['type'], Lexer::T_STATUS);
		$this->assertEquals($lexer->lookahead['value'], 'closed');
	}




	/** \brief Check if \ref Lexer detects 'priority:low'.
	 */
	public function testPriorityLow()
	{
		$lexer = new Lexer();
		$lexer->setInput('priority:low');
		$lexer->moveNext();

		$this->assertEquals($lexer->lookahead['type'], Lexer::T_PRIORITY);
		$this->assertEquals($lexer->lookahead['value'], 'low');
	}


	/** \brief Check if \ref Lexer detects 'priority:low'.
	 */
	public function testPriorityNormal()
	{
		$lexer = new Lexer();
		$lexer->setInput('priority:normal');
		$lexer->moveNext();

		$this->assertEquals($lexer->lookahead['type'], Lexer::T_PRIORITY);
		$this->assertEquals($lexer->lookahead['value'], 'normal');
	}


	/** \brief Check if \ref Lexer detects 'priority:low'.
	 */
	public function testPriorityMedium()
	{
		$lexer = new Lexer();
		$lexer->setInput('priority:medium');
		$lexer->moveNext();

		$this->assertEquals($lexer->lookahead['type'], Lexer::T_PRIORITY);
		$this->assertEquals($lexer->lookahead['value'], 'medium');
	}


	/** \brief Check if \ref Lexer detects 'priority:low'.
	 */
	public function testPriorityHigh()
	{
		$lexer = new Lexer();
		$lexer->setInput('priority:high');
		$lexer->moveNext();

		$this->assertEquals($lexer->lookahead['type'], Lexer::T_PRIORITY);
		$this->assertEquals($lexer->lookahead['value'], 'high');
	}




	/** \brief Check if \ref Lexer detects 'priority:low'.
	 */
	public function testTag()
	{
		$lexer = new Lexer();
		$lexer->setInput('tag:foo');
		$lexer->moveNext();

		$this->assertEquals($lexer->lookahead['type'], Lexer::T_TAG);
		$this->assertEquals($lexer->lookahead['value'], 'foo');
	}




	/** \brief Check if \ref Lexer detects mixed input.
	 */
	public function testMixed1()
	{
		$lexer = new Lexer();
		$lexer->setInput('foo is:open');
		$lexer->moveNext();

		$this->assertEquals($lexer->lookahead['type'], Lexer::T_NONE);
		$this->assertEquals($lexer->lookahead['value'], 'foo');
		$lexer->moveNext();
		$this->assertEquals($lexer->lookahead['type'], Lexer::T_STATUS);
		$this->assertEquals($lexer->lookahead['value'], 'open');
	}


	/** \brief Check if \ref Lexer detects mixed input.
	 */
	public function testMixed2()
	{
		$lexer = new Lexer();
		$lexer->setInput('is:open priority:high');
		$lexer->moveNext();

		$this->assertEquals($lexer->lookahead['type'], Lexer::T_STATUS);
		$this->assertEquals($lexer->lookahead['value'], 'open');
		$lexer->moveNext();
		$this->assertEquals($lexer->lookahead['type'], Lexer::T_PRIORITY);
		$this->assertEquals($lexer->lookahead['value'], 'high');
	}
}
