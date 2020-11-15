<?php
/**
 *	Information about a rhythm - an array of relative lengths of time
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

class Rhythm
{
	protected $beats = 4;
	protected $rests = 0;
	protected $pulse = 4;
	protected $rhythm = array();

	/**
	 * Constructor
	 *
	 * Passed a series of #s, corresponding to the relative time durations.
	 * E.g., if passed "4, 4, 4, 4", that's basically 4/4 time with a pulse
	 * of 1/16 notes.  "2, 2, 2, 2", is also 4/4 time, but with a pulse of
	 * of 1/8 notes.
	 *
	 * @param int $lengths - A variable number of lengths
	 * @return void
	 */
	function __construct(...$lengths)
	{
		$this->beats = 0;
		$this->rests = 0;
		$this->pulse = 0;
		$this->rhythm = array();
		foreach($lengths as $length)
		{
			if (is_numeric($length) && $length > 0)
			{
				$this->rhythm[] = $length;
				$this->pulse += $length;
			}
		}
		$this->beats = count($this->rhythm);
		$this->rests = $this->pulse - $this->beats;
	}

	/**
	 * Return the number of beats.
	 *
	 * @return int
	 */
	public function getBeats()
	{
		return $this->beats;
	}

	/**
	 * Return the number of rests.
	 *
	 * @return int
	 */
	public function getRests()
	{
		return $this->rests;
	}

	/**
	 * Return the rhythm array.
	 *
	 * @return int[]
	 */
	public function getRhythm()
	{
		return $this->rhythm;
	}

	/**
	 * Return the number of pulses.
	 *
	 * @return int
	 */
	public function getPulse()
	{
		return $this->pulse;
	}

}
?>