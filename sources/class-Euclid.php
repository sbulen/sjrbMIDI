<?php
/**
 *	Calculate a rhythm (array of relative lengths) via Euclid algorithm
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

class Euclid extends Rhythm
{
	protected $pattern = '';

	/**
	 * Builds a Euclid object
	 *
	 * @param int $beats
	 * @param int $rests
	 * @return void
	 */
	function __construct($beats = 0, $rests = 0)
	{
		$this->beats = $beats;
		$this->rests = $rests;
		$this->pulses = $beats + $rests;

		// Convert beats/rests to string for input to algorithm...
		$arr = array();
		for ($i = 0; $i < ($beats + $rests); $i++)
			if ($i < $beats)
				$arr[$i] = '1';
			else
				$arr[$i] = '0';

		// Apply algorithm
		if ($beats > 0)
			$result = $this->fold($arr, $rests);
		else
			$result = array();
		$this->pattern = implode('', $result);

		// Extract rhythm (array of relative durations) from returned string
		$matches = array();
		preg_match_all('~10*~', $this->pattern, $matches);
		$this->rhythm = array();
		foreach ($matches[0] as $match)
			$this->rhythm[] = strlen($match);
	}

	/**
	 * Key internal recursive function at heart of Euclid algorithm
	 * Internal worker function.
	 *
	 * @param int[] $arr
	 * @param int $rem
	 * @return int[]
	 */
	private function fold($arr, $rem)
	{
		if($rem > 1)
		{
			$new = array();
			$offset = count($arr) - $rem;
			$max = max($offset, $rem);
			$rem = abs($offset - $rem);
			for ($i = 0; $i < $max; $i++)
			{
				if ($i >= $offset)
					$new[$i] = $arr[$i];
				else
					$new[$i] = $arr[$i] . (isset($arr[$i + $offset]) ? $arr[$i + $offset] : '');
			};
			$arr = $this->fold($new, $rem);
		}
		return $arr;
	}

	/**
	 * Return the pattern - the string of 1s and 0s
	 *
	 * @return string
	 */
	public function getPattern()
	{
		return $this->pattern;
	}

	/**
	 * Randomize current object...
	 *
	 * @param int $pulses
	 * @return Euclid
	 */
	public function randomRhythm($pulses)
	{
		$beats = rand(1, $pulses);
		$rests = $pulses - $beats;

		return self::__construct($beats, $rests);
	}
}
?>