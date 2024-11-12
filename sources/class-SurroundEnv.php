<?php
/**
 *	Surround Environment
 *
 *	Some helpful functions for leveraging the 5.1 surround panner
 *  within Cakewalk.
 *
 *  This library maps a sound location in cartesian coordinates
 *  relative to the listener to the polar(-ish) coordinates used
 *  within Cakewalk.  The output will be several MIDI CCs
 *  (continuous controllers) to be used to control the placement
 *  of the sound.  Several functions for standard movements, e.g.,
 *  to move the sound to a location, or in a line, in a circle, or a spiral
 *  are also provided.
 *
 *  The goal is to provide more natural & realistic movement of the
 *  sound within the listening area.
 *
 *  For sound movement to be realistic, a volume/distance mathematical rule
 *  must be followed.  Also, delay and reverb can be added to convey distance.
 *
 *  Basically, the inputs include environment information, sound
 *  location information, and optionally movement instructions.  The
 *  outputs will be MIDI CCs to control Cakewalk's Surround Panner,
 *  a reverb audio effect and a delay reverb audio effect.
 *
 *	Copyright 2024 Shawn Bulen
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

class SurroundEnv
{
	// Passed info; x, y coords in feet
	protected array $cart_coords = array('x' => 0.0, 'y' => 0.0);

	// Derived info
	// $polar coords include the angle in radians & distance in feet
	// $ms is the distance converted to milliseconds via speed of sound; for delay

	// $vol_127 is the distance converted to a volume CC values 0-127
	// $ang_127 is the angle converted to Cakewalk surround panel angle in CC values 0-127
	// $foc_127 is the distance converted to Cakewalk surround panel focus in CC values 0-127
	// $rvb_127 is the distance converted to a reverb CC values 0-127
	// $ms_127 is $ms converted to CC values 0-127
	protected array $polar_coords = array('ang' => 0.0, 'dist' => 0.0);
	protected float $ms = 0.0;

	protected int $vol_127 = 0;
	protected int $ang_127 = 0;
	protected int $foc_127 = 0;
	protected int $rvb_127 = 0;
	protected int $ms_127 = 0;

	protected int $vol_cc = 12;
	protected int $ang_cc = 13;
	protected int $foc_cc = 14;
	protected int $rvb_cc = 91;
	protected int $ms_cc = 93;

	/**
	 * Constructor
	 *
	 * Passed location coordinates.  Cartesian coordinates assumed in constructor.
	 *
	 * @param array $cart_coords - x, y coordinates in feet
	 * @param int $vol_cc - CC to pass the surround volume events, based on distance
	 * @param int $ang_cc - CC to pass the surround angle events
	 * @param int $foc_cc - CC to pass the surround focus events
	 * @param int $rvb_cc - CC to pass the reverb level events (91 =  standard-ish...)
	 * @param int $ms_cc - CC to pass the delay events in ms (93 = I've seen used on some chorus/delay vsts...)
	 * @return void
	 */
	function __construct($cart_coords = array(0.0, 0.0), $vol_cc = 12, $ang_cc = 13, $foc_cc = 14, $rvb_cc = 91, $ms_cc = 93)
	{
		// Make sure coords are all pairs of ints or floats
		if (!is_array($cart_coords) || (count($cart_coords) != 2) || ($cart_coords != array_filter($cart_coords, function($a) {return is_int($a) || is_float($a);})))
			Errors::fatal('inv_surr_coords');
		else
		{
			$this->cart_coords['x'] = (float) $cart_coords[0];
			$this->cart_coords['y'] = (float) $cart_coords[1];
		}

		// Simple range checks, between -4000 to +4000
		$this->cart_coords['x'] = (float) MIDIEvent::rangeCheck($this->cart_coords['x'], -4000.0, 4000.0);
		$this->cart_coords['y'] = (float) MIDIEvent::rangeCheck($this->cart_coords['y'], -4000.0, 4000.0);

		// Just do a simple cart2pol...  With some wrinkles to work with Cakewalk Surround.
		//
		// Our cartesian values are in feet, specified in x, y
		//  - Positive X values are to the right, negative to the left
		//  - Positive Y values are forward, negative to the rear
		//  - The Sonitus delay in CW ranges from .1 to 4000 ms, so we'll use that as the max distance.
		// Our polar coordinates are an angle in radians & distance in feet
		//  - 0 radians is to the right, pi/2 is to the front, pi to the left, 3pi/2 is to the rear
		$this->polar_coords['ang'] = atan2($this->cart_coords['y'], $this->cart_coords['x']);
		$this->polar_coords['dist'] = sqrt($this->cart_coords['x']**2 + $this->cart_coords['y']**2);
		$this->polar_coords['dist'] = (float) MIDIEvent::rangeCheck($this->polar_coords['dist'], 0.0, 4000.0);

		// Set which CCs to use
		$this->vol_cc = (int) MIDIEvent::rangeCheck($vol_cc);
		$this->ang_cc = (int) MIDIEvent::rangeCheck($ang_cc);
		$this->foc_cc = (int) MIDIEvent::rangeCheck($foc_cc);
		$this->rvb_cc = (int) MIDIEvent::rangeCheck($rvb_cc);
		$this->ms_cc = (int) MIDIEvent::rangeCheck($ms_cc);

		$this->deriveCcValues();
	}

	/**
	 * cleanCoords - simple function to do range and type checks on coordinate pairs (simple format)
	 *
	 * @param array $coords (simple format)
	 * @param bool $cart - true if cartesian, false if polar
	 * @return array $coords (simple format)
	 */
	public static function cleanCoords($coords = array(0.0, 0.0), $cart = true): array
	{
		$clean_coords = array(0.0, 0.0);

		// Make sure coords are a pair of ints or floats
		if (!is_array($coords) || (count($coords) != 2) || ($coords != array_filter($coords, function($a) {return is_int($a) || is_float($a);})))
			Errors::fatal('inv_surr_coords');

		// Simple range checks on input
		if ($cart)
		{
			$clean_coords[0] = (float) MIDIEvent::rangeCheck($coords[0], -4000.0, 4000.0);
			$clean_coords[1] = (float) MIDIEvent::rangeCheck($coords[1], -4000.0, 4000.0);
		}
		else
		{
			$clean_coords[0] = fmod($coords[0], 2 * M_PI);
			$clean_coords[1] = (float) MIDIEvent::rangeCheck($coords[1], 0.0, 4000.0);
		};

		return $clean_coords;
	}

	/**
	 * pol2cart
	 *
	 * @param array $polar_coords (simple format)
	 * @return array $cart_coords (simple format)
	 */
	public static function pol2cart($polar_coords = array(0.0, 0.0)): array
	{
		$cart_coords = array(0.0, 0.0);

		// Simple range checks on input
		$polar_coords = SurroundEnv::cleanCoords($polar_coords, false);

		//pol2cart...
		$cart_coords[0] = cos($polar_coords[0]) * $polar_coords[1];
		$cart_coords[1] = sin($polar_coords[0]) * $polar_coords[1];

		// Simple range checks on output
		$cart_coords = SurroundEnv::cleanCoords($cart_coords);

		return $cart_coords;
	}

	/**
	 * cart2pol
	 *
	 * @param array $cart_coords (simple format)
	 * @return array $polar_coords (simple format)
	 */
	public static function cart2pol($cart_coords = array(0.0, 0.0)): array
	{
		$polar_coords = array(0.0, 0.0);

		// Simple range checks on input, between -4000 to +4000
		$cart_coords = SurroundEnv::cleanCoords($cart_coords);

		// cart2pol...
		$polar_coords[0] = atan2($cart_coords[1], $cart_coords[0]);
		$polar_coords[1] = sqrt($cart_coords[0]**2 + $cart_coords[1]**2);

		// Simple range check on output
		$polar_coords = SurroundEnv::cleanCoords($polar_coords, false);

		return $polar_coords;
	}

	/**
	 * setCartCoords
	 *
	 * @param array $cart_coords - x, y coordinates in feet
	 * @return void
	 */
	public function setCartCoords($cart_coords = array(0.0, 0.0)): void
	{
		// local working copy in simple format
		$polar_coords = array(0.0, 0.0);

		// Clean the input...
		$cart_coords = SurroundEnv::cleanCoords($cart_coords);
		$this->cart_coords['x'] = (float) $cart_coords[0];
		$this->cart_coords['y'] = (float) $cart_coords[1];

		// cart2pol...
		$polar_coords = SurroundEnv::cart2pol($cart_coords);
		$this->polar_coords['ang'] = (float) $polar_coords[0];
		$this->polar_coords['dist'] = (float) $polar_coords[1];

		$this->deriveCcValues();
	}

	/**
	 * setPolarCoords
	 *
	 * @param array $polar_coords, in radians & feet
	 * @return void
	 */
	public function setPolarCoords($polar_coords = array(0.0, 0.0)): void
	{

		// local working copy in simple format
		$cart_coords = array(0.0, 0.0);

		// Clean the input...
		$polar_coords = SurroundEnv::cleanCoords($polar_coords, false);
		$this->polar_coords['ang'] = (float) $polar_coords[0];
		$this->polar_coords['dist'] = (float) $polar_coords[1];

		//pol2cart...
		$cart_coords = SurroundEnv::pol2cart($polar_coords);
		$this->cart_coords['x'] = (float) $cart_coords[0];
		$this->cart_coords['y'] = (float) $cart_coords[1];

		$this->deriveCcValues();
	}

	/**
	 * deriveCcValues
	 *
	 * Derive all the values used for the appropriate CCs from the coordinates.  
	 *
	 * @return void
	 */
	private function deriveCcValues(): void
	{
		// Upon entry, we should have clean polar coordinates, either from constructor or setter.

		// Scale volume by distance...  
		if ($this->polar_coords['dist'] == 0.0)
			$this->vol_127 = 127;
		else
			$this->vol_127 = (int) round((1/($this->polar_coords['dist']**0.5))*127);
		if ($this->vol_127 > 127)
			$this->vol_127 = 127;

		$this->vol_127 = (int) MIDIEvent::rangeCheck($this->vol_127);

		// Cakewalk surround angles are kinda weird...
		//  - We don't use these, but important to know/understand when using the panner.
		//  - 0 degrees is to the front, +90 degrees is to the right, +180 degrees straight back;
		//  - 0 degrees is to the front, -90 degrees is to the left, -180 degrees straight back & = +180;
		// OTOH...  Cakewalk surround angles when driven by CCs use these values, mapped 0-127...
		//  - CC=0 maps to directly behind you
		//  - CC=32 maps to the right
		//  - CC=64 maps to directly in front
		//  - CC=95 maps to the left (-ish... some rounding funkiness involved...)
		//  - CC=127 maps to directly behind you (-ish... not 128 as expected, which introduces the rounding funkiness...)
		//
		// So... Offset by 90 degrees, then scale 0-2pi to 0-127...  This math matches the CW surround panner.
		$this->ang_127 = (int) round(fmod($this->polar_coords['ang']+(2.5*M_PI),2*M_PI)*127/2/M_PI);
		$this->ang_127 = (int) MIDIEvent::rangeCheck($this->ang_127);

		// Focus is how much the sound "surrounds" you...  At distance, it's just a point on the horizon.
		// Up close, say, within the confines of the speaker configuration, it should be coming out of most/all speakers.
		// Low focus = 0 = all the speakers = very close; high focus = 127 = far away.
		// Basically, the inverse of the vol, so flip it.  I.e., if vol = 127, it's very very close, so low focus.
		if ($this->polar_coords['dist'] == 0.0)
			$this->foc_127 = 0;
		else
			$this->foc_127 = 127 - (int) round((1/($this->polar_coords['dist']**2.5))*127);
		if ($this->foc_127 > 127)
			$this->foc_127 = 127;
		if ($this->foc_127 < 0)
			$this->foc_127 = 0;
		$this->foc_127 = (int) MIDIEvent::rangeCheck($this->foc_127);

		// Add a little reverb to stuff far away.  Zero reverb up close.
		// This may be used for either reverb room size, diffusion, or wet/dry balance settings.
		$this->rvb_127 = (int) ($this->polar_coords['dist']/20)**2.0;
		$this->rvb_127 = (int) MIDIEvent::rangeCheck($this->rvb_127);

		// The speed of sound in dry air at 20 °C = 0.888629738 milliseconds per foot
		$this->ms = $this->polar_coords['dist'] * 0.888629738;

		// Scale ms-4000.0 to 0-127 for use as MIDI CC
		$this->ms_127 = (int) round($this->ms*127/4000);
		$this->ms_127 = (int) MIDIEvent::rangeCheck($this->ms_127);
	}

	/**
	 * getVol127
	 *
	 * Get the volume in 0-127 format
	 *
	 * @return int
	 */
	public function getVol127(): int
	{
		return $this->vol_127;
	}

	/**
	 * getAng127
	 *
	 * Get the angle in 0-127 format
	 *
	 * @return int
	 */
	public function getAng127(): int
	{
		return $this->ang_127;
	}

	/**
	 * getFoc127
	 *
	 * Get the focus in 0-127 format
	 *
	 * @return int
	 */
	public function getFoc127(): int
	{
		return $this->foc_127;
	}

	/**
	 * getRvb127
	 *
	 * Get the reverb value in 0-127 format
	 *
	 * @return int
	 */
	public function getRvb127(): int
	{
		return $this->rvb_127;
	}

	/**
	 * getMs127
	 *
	 * Get the distance in milliseconds (per speed of sound) in 0-127 format
	 *
	 * @return int
	 */
	public function getMs127(): int
	{
		return $this->ms_127;
	}

	/**
	 * getVolCc
	 *
	 * Get the CC # used for returning the surround volume
	 *
	 * @return int
	 */
	public function getVolCc(): int
	{
		return $this->vol_Cc;
	}

	/**
	 * getAngCc
	 *
	 * Get the CC # used for returning the surround angle
	 *
	 * @return int
	 */
	public function getAngCc(): int
	{
		return $this->ang_Cc;
	}

	/**
	 * getFocCc
	 *
	 * Get the CC # used for returning the surround focus
	 *
	 * @return int
	 */
	public function getFocCc(): int
	{
		return $this->foc_Cc;
	}

	/**
	 * getRvbCc
	 *
	 * Get the CC # used for returning the reverb level
	 *
	 * @return int
	 */
	public function getRvbCc(): int
	{
		return $this->rvb_Cc;
	}

	/**
	 * getMsCc
	 *
	 * Get the CC # used for returning ms
	 *
	 * @return int
	 */
	public function getMsCc(): int
	{
		return $this->ms_Cc;
	}

	/**
	 * moveTo
	 *
	 * Move to a specific location.
	 * Returns an array of MIDI CC events.
	 *
	 * @param array $coords - either x,y or ang,dist
	 * @param bool $cart - true if cartesian coords passed, false if polar coords passed
	 * @param int $start - start time, in absolute time
	 * @param int $chan - MIDI channel
	 * @return MIDIEvent::ControlChange[]
	 */
	public function moveTo($coords = array(0, 0), $cart = true, $start = 0, $chan = 0): array
	{
		$result = array();

		// Clean everything...
		$start = (int) MIDIEvent::rangeCheck($start, 0, 0xFFFFFFF);
		$chan = (int) MIDIEvent::rangeCheck($chan, 0, 15);

		// Note that the sets clean the coords
		if ($cart)
			$this->setCartCoords($coords);
		else
			$this->setPolarCoords($coords);

		$result[] = new ControlChange($start, $chan, $this->vol_cc, $this->vol_127);
		$result[] = new ControlChange($start, $chan, $this->ang_cc, $this->ang_127);
		$result[] = new ControlChange($start, $chan, $this->foc_cc, $this->foc_127);
		$result[] = new ControlChange($start, $chan, $this->rvb_cc, $this->rvb_127);
		$result[] = new ControlChange($start, $chan, $this->ms_cc, $this->ms_127);

		return $result;
	}

	/**
	 * lineTo
	 *
	 * Draw a line from point A to point B.
	 * Returns an array of MIDI CC events.
	 *
	 * @param array $start_coords - either x,y or ang,dist
	 * @param array $end_coords - either x,y or ang,dist
	 * @param bool $cart - true if cartesian coords passed, false if polar coords passed
	 * @param int $start - start time, in absolute time
	 * @param int $end - end time, in absolute time
	 * @param int $interval - amount of time between each movement
	 * @param int $chan - MIDI channel
	 * @return MIDIEvent::ControlChange[]
	 */
	public function lineTo($start_coords = array(-20, 20), $end_coords = array(20, -20), $cart = true, $start = 0, $end = 3840, $interval = 960, $chan = 0): array
	{
		$result = array();

		// Clean everything...
		$start = (int) MIDIEvent::rangeCheck($start, 0, 0xFFFFFFF);
		$end = (int) MIDIEvent::rangeCheck($end, 0, 0xFFFFFFF);
		$interval = (int) MIDIEvent::rangeCheck($interval, 0, 0xFFFFFFF);
		$chan = (int) MIDIEvent::rangeCheck($chan, 0, 15);

		// Easiest to do this in cartesian coords
		if ($cart)
		{
			$start_coords = SurroundEnv::cleanCoords($start_coords);
			$end_coords = SurroundEnv::cleanCoords($end_coords);
		}
		else
		{
			$start_coords = SurroundEnv::pol2cart($start_coords);
			$end_coords = SurroundEnv::pol2cart($end_coords);
		}
		$working_coords = $start_coords;

		// Calculate deltas to increment coordinates by each interval
		$x_inc = 0.0;
		$y_inc = 0.0;

		$steps = (int) (($end - $start)/$interval);
		if ($steps > 0)
		{
			$x_inc = (float) (($end_coords[0] - $start_coords[0])/$steps);
			$y_inc = (float) (($end_coords[1] - $start_coords[1])/$steps);
		}

		// Now for the main loop!
		for ($i = $start; $i < $end; $i += $interval)
		{
			$this->setCartCoords($working_coords);

			$result[] = new ControlChange($i, $chan, $this->vol_cc, $this->vol_127);
			$result[] = new ControlChange($i, $chan, $this->ang_cc, $this->ang_127);
			$result[] = new ControlChange($i, $chan, $this->foc_cc, $this->foc_127);
			$result[] = new ControlChange($i, $chan, $this->rvb_cc, $this->rvb_127);
			$result[] = new ControlChange($i, $chan, $this->ms_cc, $this->ms_127);

			$working_coords[0] += $x_inc;
			$working_coords[1] += $y_inc;
		}

		// Don't forget that last fencepost...
		$this->setCartCoords($end_coords);

		$result[] = new ControlChange($end, $chan, $this->vol_cc, $this->vol_127);
		$result[] = new ControlChange($end, $chan, $this->ang_cc, $this->ang_127);
		$result[] = new ControlChange($end, $chan, $this->foc_cc, $this->foc_127);
		$result[] = new ControlChange($end, $chan, $this->rvb_cc, $this->rvb_127);
		$result[] = new ControlChange($end, $chan, $this->ms_cc, $this->ms_127);

		return $result;
	}

	/**
	 * circleTo
	 *
	 * Draws a circle at given distance (radius) and speed.
	 * Returns an array of MIDI CC events.
	 *
	 * @param array $start_coords - either x,y or ang,dist - sets starting point, which indicates phase & radius
	 * @param bool $cart - true if cartesian coords passed, false if polar coords passed
	 * @param float $cycles - number of complete cycles around the room between start & end times; max 100,000, just because
	 * @param int $start - start time, in absolute time
	 * @param int $end - end time, in absolute time
	 * @param int $interval - amount of time between each movement
	 * @param int $chan - MIDI channel	 * @return MIDIEvent::ControlChange[]
	 */
	public function circleTo($start_coords = array(-20, 20), $cart = true, $cycles = 1.0, $start = 0, $end = 3840, $interval = 960, $chan = 0): array
	{
		$result = array();

		// Clean everything...
		$cycles = (float) MIDIEvent::rangeCheck($cycles, -100000.0, 100000.0);
		$start = (int) MIDIEvent::rangeCheck($start, 0, 0xFFFFFFF);
		$end = (int) MIDIEvent::rangeCheck($end, 0, 0xFFFFFFF);
		$interval = (int) MIDIEvent::rangeCheck($interval, 0, 0xFFFFFFF);
		$chan = (int) MIDIEvent::rangeCheck($chan, 0, 15);

		// Easiest to do this in polar coords
		if ($cart)
			$start_coords = SurroundEnv::cart2pol($start_coords);
		else
			$start_coords = SurroundEnv::cleanCoords($start_coords);
		$working_coords = $start_coords;

		// Calculate delta to increment angle of rotation by each interval
		$ang_inc = 0.0;

		$steps = (int) (($end - $start)/$interval);
		if ($steps > 0)
			$ang_inc = (float) (($cycles*2*M_PI)/$steps);

		// Now for the main loop!
		for ($i = $start; $i < $end; $i += $interval)
		{
			$this->setPolarCoords($working_coords);

			$result[] = new ControlChange($i, $chan, $this->vol_cc, $this->vol_127);
			$result[] = new ControlChange($i, $chan, $this->ang_cc, $this->ang_127);
			$result[] = new ControlChange($i, $chan, $this->foc_cc, $this->foc_127);
			$result[] = new ControlChange($i, $chan, $this->rvb_cc, $this->rvb_127);
			$result[] = new ControlChange($i, $chan, $this->ms_cc, $this->ms_127);

			$working_coords[0] += $ang_inc;
		}

		// Don't forget that last fencepost...
		$this->setPolarCoords($working_coords);

		$result[] = new ControlChange($end, $chan, $this->vol_cc, $this->vol_127);
		$result[] = new ControlChange($end, $chan, $this->ang_cc, $this->ang_127);
		$result[] = new ControlChange($end, $chan, $this->foc_cc, $this->foc_127);
		$result[] = new ControlChange($end, $chan, $this->rvb_cc, $this->rvb_127);
		$result[] = new ControlChange($end, $chan, $this->ms_cc, $this->ms_127);

		return $result;
	}

	/**
	 * spiralTo
	 *
	 * Draws a spiral at given distance (radius) and speed.
	 * Like circleTo, but the radius varies over time.
	 * Returns an array of MIDI CC events.
	 *
	 * @param array $start_coords - either x,y or ang,dist - sets starting point, which indicates phase & radius
	 * @param bool $cart - true if cartesian coords passed, false if polar coords passed
	 * @param float $cycles - number of complete cycles around the room between start & end times; max 100,000, just because
	 * @param float $end_radius - delta in radius from start to end times
	 * @param int $start - start time, in absolute time
	 * @param int $end - end time, in absolute time
	 * @param int $interval - amount of time between each movement
	 * @param int $chan - MIDI channel	 * @return MIDIEvent::ControlChange[]
	 */
	public function spiralTo($start_coords = array(-20, 20), $cart = true, $cycles = 1.0, $end_radius = 1.0, $start = 0, $end = 3840, $interval = 960, $chan = 0): array
	{
		$result = array();

		// Clean everything...
		$cycles = (float) MIDIEvent::rangeCheck($cycles, -100000.0, 100000.0);
		$end_radius = (float) MIDIEvent::rangeCheck($end_radius, 0.0, 4000.0);
		$start = (int) MIDIEvent::rangeCheck($start, 0, 0xFFFFFFF);
		$end = (int) MIDIEvent::rangeCheck($end, 0, 0xFFFFFFF);
		$interval = (int) MIDIEvent::rangeCheck($interval, 0, 0xFFFFFFF);
		$chan = (int) MIDIEvent::rangeCheck($chan, 0, 15);

		// Easiest to do this in polar coords
		if ($cart)
			$start_coords = SurroundEnv::cart2pol($start_coords);
		else
			$start_coords = SurroundEnv::cleanCoords($start_coords);
		$working_coords = $start_coords;

		// Calculate delta to increment angle of rotation by each interval
		$ang_inc = 0.0;
		$radius_inc = 0.0;

		$steps = (int) (($end - $start)/$interval);
		if ($steps > 0)
		{
			$ang_inc = (float) (($cycles*2*M_PI)/$steps);
			$radius_inc = (float) (($end_radius - $working_coords[1])/$steps);
		}

		// Now for the main loop!
		for ($i = $start; $i < $end; $i += $interval)
		{
			$this->setPolarCoords($working_coords);

			$result[] = new ControlChange($i, $chan, $this->vol_cc, $this->vol_127);
			$result[] = new ControlChange($i, $chan, $this->ang_cc, $this->ang_127);
			$result[] = new ControlChange($i, $chan, $this->foc_cc, $this->foc_127);
			$result[] = new ControlChange($i, $chan, $this->rvb_cc, $this->rvb_127);
			$result[] = new ControlChange($i, $chan, $this->ms_cc, $this->ms_127);

			$working_coords[0] += $ang_inc;
			$working_coords[1] += $radius_inc;
		}

		// Don't forget that last fencepost...
		$this->setPolarCoords($working_coords);

		$result[] = new ControlChange($end, $chan, $this->vol_cc, $this->vol_127);
		$result[] = new ControlChange($end, $chan, $this->ang_cc, $this->ang_127);
		$result[] = new ControlChange($end, $chan, $this->foc_cc, $this->foc_127);
		$result[] = new ControlChange($end, $chan, $this->rvb_cc, $this->rvb_127);
		$result[] = new ControlChange($end, $chan, $this->ms_cc, $this->ms_127);

		return $result;
	}
}
?>