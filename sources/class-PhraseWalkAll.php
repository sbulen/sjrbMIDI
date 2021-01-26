<?php
/**
 *	Definition of an Iterator for the Phrase object.
 *	This provides information for each note.
 *
 *	Copyright 2020 Shawn Bulen
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

class PhraseWalkAll implements Iterator
{
	protected $note_arr = array();
	protected $start = 0;
	protected $dur = 0;

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
	function __construct($note_arr, $start, $dur)
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
	public function rewind()
	{
		$this->position = 0;
	}

	/**
	 * Current - return current note
	 *
	 * @return Note
	 */
	public function current()
	{
		return $this->note_arr[$this->position];
	}

	/**
	 * Current - return start value of current note
	 *
	 * @return int
	 */
	public function key()
	{
		return $this->note_arr[$this->position]->getAt();
	}

	/**
	 * Next - advance the position
	 *
	 * @return void
	 */
	public function next()
	{
		++$this->position;
	}

	/**
	 * Valid - current position exist?  (or done?)
	 *
	 * @return bool
	 */
	public function valid()
	{
		return isset($this->note_arr[$this->position]);
	}

}
?>