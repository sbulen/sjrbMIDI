<?php
/**
 *	Math Functions
 *
 *  Some helpful math functions
 *
 *	Copyright 2021-2023 Shawn Bulen
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

class MathFuncs
{
	/**
	 * Generate random float between 0 and 1
	 *
	 * @return float
	 */
	static function randomFloat(): float
	{
		return (float)rand() / (float)getrandmax();
	}

	/**
	 * Return true if number provided is a power of 2, e.g., 2, 4, 8, 16, 32...
	 *
	 * @param float | int
	 * @return bool
	 */
	static function isPow2(float|int $value): bool
	{
		$value = (float) $value;
		return (fmod(log($value, 2), 1) === 0.0);
	}

	/**
	 * Return true if number provided is even
	 *
	 * @param float | int
	 * @return bool
	 */
	static function isEven(float|int $value): bool
	{
		$value = (float) $value;
		return (fmod($value, 2) === 0.0);
	}
}
?>