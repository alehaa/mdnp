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

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use MDNP\Notes\Entities\Note;
use MDNP\Notes\Search\Lexer;


/** \brief Note repository.
 *
 * \details This repository will be used to lookup notes.
 */
class Notes extends EntityRepository
{
	/** \brief Get a DQL query for \p $search.
	 *
	 * \details Parses the search query \p $search and returns a new DQL
	 *  QueryBuilder with the preconfigured values.
	 *
	 *
	 * \param string $search The search string.
	 *
	 * \return QueryBuilder The preconfigured QueryBuilder instance.
	 */
	protected function getSearchQuery(string $search): QueryBuilder
	{
		$qb = $this->createQueryBuilder('notes');

		$lexer = new Lexer();
		$lexer->setInput($search);

		for ($lexer->moveNext(); $lexer->lookahead !== null;
		     $lexer->moveNext()) {
			switch ($lexer->lookahead['type']) {
				case Lexer::T_STRING:
				case Lexer::T_NONE:
					static $searchindex = 0;

					$qb->andwhere(
						$qb->expr()->orX(
							$qb->expr()->like('lower(notes.title)',
							                  ':search_'.$searchindex),
							$qb->expr()->like('lower(notes.content)',
							                  ':search_'.$searchindex)
					     ))
					   ->setParameter('search_'.$searchindex,
					                  '%'.$lexer->lookahead['value'].'%');
					$searchindex++;
					break;

				case Lexer::T_STATUS:
					$qb->andwhere('lower(notes.status) = :status')
					   ->setParameter('status', $lexer->lookahead['value']);
					break;

				case Lexer::T_PRIORITY:
					switch ($lexer->lookahead['value']) {
						case 'low':    $value = -1; break;
						case 'normal': $value = 0; break;
						case 'medium': $value = 1; break;
						case 'high':   $value = 2; break;
					}

					$qb->andwhere('notes.priority = :priority')
					   ->setParameter('priority', $value);
					break;

				case Lexer::T_TAG:
					$qb->leftjoin('notes.tags', 'tags')
					   ->andwhere('lower(tags.name) = :tag')
					   ->setParameters(
						array('tag' => $lexer->lookahead['value']));
			}
		}

		return $qb;
	}


	/** \brief Searches for notes matching \p $search.
	 *
	 *
	 * \param string $search The search string.
	 *
	 * \return array The found notes.
	 */
	public function search(string $search): array
	{
		return $this->getSearchQuery($search)
		            ->getQuery()
		            ->getResult();
	}
}
