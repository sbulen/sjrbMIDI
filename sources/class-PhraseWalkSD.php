<?php
/**
 *	Definition of an Iterator for the Phrase object.
 *	Simple one - returns $start => $dur for each note.
 *
 *	Copyright 2020-2023 Shawn Bulen
 *
 *	This file is part of the sjrbMIDI library.
 *
 *	sjrbMIDI is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *	
 *	sjrbMIDI is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with sjrbMIDI.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

class PhraseWalkSD implements Iterator
{
	protected array $note_arr = array();
	protected int $start = 0;
	protected int $dur = 0;
	protected int $position = 0;

	/**
	 * Constructor
	 *
	 * Needs several fields passed from the Phrase object.
	 *
	 * @param int[] $note_arr - An array of notes
	 * @param int $start - start point of this instance of rhythm
	 * @param int $dur - duration of this instance of rhythm
	 * @return void
	 */
	function __construct(array $note_arr, int $start, int $dur)
	{
		$this->note_arr = $note_arr;
		$this->start = $start;
		$this->dur = $dur;

		$this->position = 0;
	}

	/**
	 * Rewind - start over
	 *
	 * @return void
	 */
	public function rewind(): void
	{
		$this->position = 0;
	}

	/**
	 * Current - return duration of current note
	 *
	 * @return mixed
	 */
	public function current(): int
	{
		return $this->note_arr[$this->position]->getDur();
	}

	/**
	 * Current - return start value of current note
	 *
	 * @return int
	 */
	public function key(): int
	{
		return $this->note_arr[$this->position]->getAt();
	}

	/**
	 * Next - advance the position
	 *
	 * @return void
	 */
	public function next(): void
	{
		++$this->position;
	}

	/**
	 * Valid - current position exist?  (or done?)
	 *
	 * @return bool
	 */
	public function valid(): bool
	{
		return isset($this->note_arr[$this->position]);
	}

}
?>