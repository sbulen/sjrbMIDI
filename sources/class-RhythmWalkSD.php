<?php
/**
 *	Definition of an Iterator for the Rhythm object.
 *	Simple one - returns $start => $dur for each beat.
 *
 *	Copyright 2020-2021 Shawn Bulen
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

class RhythmWalkSD implements Iterator
{
	protected $rhythm = array();
	protected $starts = array();
	protected $start = 0;
	protected $dur = 0;
	protected $pulses = 0;
	protected $position = 0;

	/**
	 * Constructor
	 *
	 * Needs several fields passed from the Rhythm object.
	 *
	 * @param int[] $rhythm - An array of relative durations
	 * @param int $start - start point of this instance of rhythm
	 * @param int $dur - duration of this instance of rhythm
	 * @return void
	 */
	function __construct($rhythm, $start, $dur)
	{
		$this->rhythm = $rhythm;
		$this->start = $start;
		$this->dur = $dur;

		$this->position = 0;

		$this->pulses = 0;
		$this->starts = array();
		$this->starts[0] = 0;
		foreach($rhythm AS $length)
		{
			$this->pulses += $length;
			$this->starts[] = $this->pulses;
		}
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
	 * Current - return duration of current beat
	 * Use difference between start of next step & start of this step to avoid gaps of 1 due to rounding...
	 *
	 * @return mixed
	 */
	public function current(): int
	{
		return (int) (round(($this->starts[$this->position + 1] * $this->dur) / $this->pulses) - round(($this->starts[$this->position] * $this->dur) / $this->pulses));
	}

	/**
	 * Current - return start value of current beat
	 *
	 * @return int
	 */
	public function key(): int
	{
		return (int) round((($this->starts[$this->position] * $this->dur) / $this->pulses) + $this->start);
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
		return isset($this->rhythm[$this->position]);
	}

}
?>