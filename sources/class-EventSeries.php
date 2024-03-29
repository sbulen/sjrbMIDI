<?php
/**
 *	Abstract class for series of Controller or PitchWheel events
 *	Allows for sharing & consistency of all calculations for shapes & scaling.
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

abstract class EventSeries
{
	/**
	 * Bunch of constants
	 */
	const CC_MIN = 0x00;
	const CC_MAX = 0x7F;
	const PW_MIN = -0x2000;
	const PW_MAX = 0x1FFF;

	/**
	 * Shapes
	 */
	const SINE = 0x0;
	const SAW = 0x1;
	const SQUARE = 0x2;
	const EXPO = 0x3;
	const RANDOM_STEPS = 0x4;

	/**
	 * Properties
	 */
	protected int $type_min;	// CC & PW have different min/max
	protected int $type_max;	// CC & PW have different min/max
	protected int $shape;		// Shape, e.g., SINE
	protected float $freq;		// Frequency - # of cycles per $dur
	protected float $offset;		// Offset - angle of offset; applies to all types; passed in degrees
	protected float $min_pct;		// User may not want entire range...  Percent for min value
	protected float $max_pct;		// User may not want entire range...  Percent for max value
	protected int $tick_inc;	// # of ticks between each event in series

	/**
	 * Constructor
	 *
	 * Builds object to establish a series of MIDI events.
	 *
	 * @param int $shape - The shape of the curve
	 * @param float $freq - The frequency, # of cycles per duration
	 * @param float $offset - The angle of offset, passed in degrees
	 * @param float $min_pct - Minimum value used in scaling, in percent
	 * @param float $max_pct - Maximum value used in scaling, in percent
	 * @param int $tick_inc - How far apart in ticks to spread the events
	 * @return void
	 */
	protected function __construct(int $shape = EVENTSeries::SINE, float $freq = 1, float $offset = 0, float $min_pct = 0, float $max_pct = 100, int $tick_inc = 48)
	{
		$this->shape = MIDIEvent::rangeCheck($shape, EventSeries::SINE, EventSeries::RANDOM_STEPS);
		$this->freq = MIDIEvent::rangeCheck($freq, 0, 256);
		$this->offset = MIDIEvent::rangeCheck($offset, 0, 360);
		$this->min_pct = MIDIEvent::rangeCheck($min_pct, 0, 100);
		$this->max_pct = MIDIEvent::rangeCheck($max_pct, 0, 100);
		$this->tick_inc = MIDIEvent::rangeCheck($tick_inc, 1, 960);
	}

	/**
	 * genEvents must be defined by child classes
	 *
	 * @param int $start - The absolute start for the series
	 * @param int $dur - The duration of the series
	 * @return (MIDIEvent::ControlChange[]|MIDIEvent::PitchWheel[])
	 */
	abstract function genEvents(int $start, int $dur): array;

	/**
	 * Scale to requested min/max pct.
	 * Note that min > max is allowed; like in many audio packages, this will flip the shape.
	 *
	 * @param float $value - The value to be scaled
	 * @return int
	 */
	private function scale(float $value): int
	{
		// Remember that PW has a negative min value...
		$type_range = $this->type_max - $this->type_min;
		$scale_range = ($this->max_pct - $this->min_pct)/100;
		$min_offset = $this->min_pct * $type_range / 100;

		$result = (($value - $this->type_min) * $scale_range) + $this->type_min;
		$result = (int) ($result + $min_offset);
		$result = MIDIEvent::rangeCheck($result, $this->type_min, $this->type_max);

		return $result;
	}

	/**
	 * Sine function is easy...
	 *
	 * @param float $angle
	 * @return float
	 */
	private function sine(float $angle): float
	{
		$result = sin($angle);

		// Scale it to range for this controller
		$range = $this->type_max - $this->type_min;
		$result += 1;
		$result = ((($result * $range) + 1) / 2) + $this->type_min;

		return $result;
	}

	/**
	 * Saw...
	 *
	 * @param float $angle
	 * @return float
	 */
	private function saw(float $angle): float
	{
		$result = $angle / 2 / pi();
		$result = fmod($result, 1.0);

		// Scale it to range for this controller
		$range = $this->type_max - $this->type_min;
		$result = ($result * $range) + $this->type_min;

		return $result;
	}

	/**
	 * Square...
	 *
	 * @param float $angle
	 * @return float
	 */
	private function square(float $angle): float
	{
		$result = $angle / 2 / pi();
		$result = 1 - fmod($result, 1.0);
		if ($result >= 0.5)
			$result = 1;
		else
			$result = 0;

		// Scale it to range for this controller
		$range = $this->type_max - $this->type_min;
		$result = ($result * $range) + $this->type_min;

		return $result;
	}

	/**
	 * Exponential curve...
	 * Long & flat, but provides a satisfying sharp slap at the end of a note when used on a cutoff freq.
	 * I like the shape of 10, so 10 it is.  Not too steep, not too flat...
	 *
	 * @param float $angle
	 * @return float
	 */
	private function expo(float $angle): float
	{
		$result = $angle / 2 / pi();
		$result = fmod($result, 1.0);
		$result = (10 * $result) - 8.75;
		$result = pow(10, $result) / 10;

		// Sanity check...  expo can get big quick...
		if ($result > 1)
			$result = 1;

		// Scale it to range for this controller
		$range = $this->type_max - $this->type_min;
		$result = ($result * $range) + $this->type_min;

		return $result;
	}

	/**
	 * Random steps, once per freq cycle.
	 *
	 * @param float $angle
	 * @return float
	 */
	private function random_steps(float $angle): float
	{
		$result = rand($this->type_min, $this->type_max);
		return $result;
	}

	/**
	 * Invoke the shape function & generate an assoc array of start times & event values
	 *
	 * @param int $start - The absolute start for the series
	 * @param int $dur - The duration of the series
	 * @return int[]
	 */
	protected function genValues(int $start, int $dur): array
	{
		$values = array();
		$saved = null;
		for ($time = 0; $time < $dur; $time += $this->tick_inc)
		{
			// Calc angle in radians
			$angle = ($time * 2 * pi() * $this->freq / $dur) + ($this->offset * 2 * pi() / 360);
			// Exec function
			switch ($this->shape)
			{
				case EventSeries::SINE:
					$result = $this->sine($angle);
					break;
				case EventSeries::SAW:
					$result = $this->saw($angle);
					break;
				case EventSeries::SQUARE:
					$result = $this->square($angle);
					break;
				case EventSeries::EXPO:
					$result = $this->expo($angle);
					break;
				case EventSeries::RANDOM_STEPS:
					// Only update when angle is ~0
					if (fmod($angle, 2 * pi()) < 0.00001)
					{
						$result = $this->random_steps($angle);
					}
					break;
			}
			// Last step is to apply requested scaling
			// Only need to send if value changed; thin the herd a bit
			// Account for random not returning a result
			if (isset($result))
			{
				$scaled = (int) $this->scale($result);
				if ($scaled !== $saved)
				{
					$values[$start + $time] = $scaled;
					$saved = $scaled;
				}
			}
		}
		return $values;
	}

	/**
	 * Enable changing the phase, the starting point of the function
	 *
	 * @param float $angle
	 * @return void
	 */
	public function setOffset(float $angle): void
	{
		$this->offset = $angle;
	}

}

?>