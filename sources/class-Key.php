<?php
/**
 *	MIDI class for a key
 *	Including diatonic math & conversions between diatonic notes & MIDI notes
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

class Key
{
	/**
	 * Bunch of constants
	 */

	// Modal scale offsets
	const IONIAN_MODAL = 0;		// In key Sig C, Starts & ends on C, white keys...
	const MAJOR_SCALE = 0;		// Equivalent to the major scale
	const DORIAN_MODAL = 1;		// In key Sig C, Starts & ends on D, white keys...
	const PHRYGIAN_MODAL = 2;	// In key Sig C, Starts & ends on E, white keys...
	const LYDIAN_MODAL = 3;		// In key Sig C, Starts & ends on F, white keys...
	const MIXOLYDIAN_MODAL = 4;	// In key Sig C, Starts & ends on G, white keys...
	const AEOLIAN_MODAL = 5;	// In key Sig C, Starts & ends on A, white keys...
	const MINOR_SCALE = 5;		// Equivalent to a minor scale
	const LOCRIAN_MODAL = 6;	// In key Sig C, Starts & ends on B, white keys...

	// Note base offsets, first octave
	const C_NOTE = 0;
	const Db_NOTE = 1;
	const D_NOTE = 2;
	const Eb_NOTE = 3;
	const E_NOTE = 4;
	const F_NOTE = 5;
	const Gb_NOTE = 6;
	const G_NOTE = 7;
	const Ab_NOTE = 8;
	const A_NOTE = 9;
	const Bb_NOTE = 10;
	const B_NOTE = 11;

	// Intervals, diatonic
	const UNISON = 0;
	const SECOND = 1;
	const THIRD = 2;
	const FOURTH = 3;
	const FIFTH = 4;
	const SIXTH = 5;
	const SEVENTH = 6;
	const OCTAVE = 7;
	const NINTH = 8;
	const ELEVENTH = 10;
	const THIRTEENTH = 12;
	/*
	 * Properties
	 */
	protected $root;
	protected $modal;
	protected $scale;

	protected $midi_sf;		//sharps-flats value used by MIDI
	protected $midi_mm;		//major-minor value used by MIDI

	/**
	 * Constructor
	 *
	 * Builds a Key object, needed for all diatonic math
	 *
	 * @param int $root - Root of Key
	 * @param int $modal - Modal, e.g., whether major or minor
	 * @return void
	 */
	function __construct($root = Key::C_NOTE, $modal = null)
	{
		$this->root = $root;
		$this->modal = $modal;
		$this->scale = $this->setScale($modal);
		$this->setMIDI();
	}

	/**
	 * Set scale...
	 * If none specified, choose a random one!
	 * Returns scale as an array of integers, representing offsets from the root.
	 * Internal worker function.
	 *
	 * @param int $modal - Modal, e.g., whether major or minor
	 * @return int[]
	 */
	private function setScale($modal = null)
	{
		// Steps that make up a major scale
		$major = array(2, 2, 1, 2, 2, 2, 1);

		// Set the modal...
		if ($modal === null)
			$rotate = rand(0, 6);
		else
			$rotate = $modal;

		// Rotate the array
		$rotated = array();
		for ($i = 0; $i <= 6; $i++)
			$rotated[$i] = $major[($i + $rotate) % 7];

		// Present as offsets for ease of use
		$output = array();
		$output[0] = 0;
		for ($i = 1; $i <= 6; $i++)
			$output[$i] = $output[$i - 1] + $rotated[$i - 1];

		return $output;
	}

	/**
	 * Set midi values used in MIDI file creation based on root & modal...
	 * I.e,. sharps/flats & major/minor.
	 * Internal worker function.
	 *
	 * @return void
	 */
	private function setMIDI()
	{
		// Just set minor if MINOR_SCALE or AEOLIAN_MODAL (which are the same).
		// For major scale or all other modals, leave at 0.
		// Just because.
		if ($this->modal == Key::MINOR_SCALE)
			$this->midi_mm = 1;
		else
			$this->midi_mm = 0;

		// A little bit of trickery here...
		// Predisposed to flat key sigs as opposed to sharps (e.g., Ab instead of G#)...
		// Just because.
		// First, need offset in semitones for the modal...
		static $semis = array(0, 2, 4, 5, 7, 9, 11);
		// Key sig is tied to the difference between semis & root...
		$offset = $semis[$this->modal] - $this->root;
		if ($offset < 0)
			$offset += 12;
		switch ($offset)
		{
			case 0:
				$this->midi_sf = 0;			// C equiv...
				break;
			case 1:
				$this->midi_sf = 5;			// B equiv...
				break;
			case 2:
				$this->midi_sf = -2;		// Bb equiv...
				break;
			case 3:
				$this->midi_sf = 3;			// A equiv...
				break;
			case 4:
				$this->midi_sf = -4;		// Ab equiv...
				break;
			case 5:
				$this->midi_sf = 1;			// G equiv...
				break;
			case 6:
				$this->midi_sf = -6;		// Gb equiv...
				break;
			case 7:
				$this->midi_sf = -1;		// F equiv...
				break;
			case 8:
				$this->midi_sf = 4;			// E equiv...
				break;
			case 9:
				$this->midi_sf = -3;		// Eb equiv...
				break;
			case 10:
				$this->midi_sf = 2;			// D equiv...
				break;
			case 11:
				$this->midi_sf = -5;		// Db equiv...
				break;
		}

		return;
	}

	/**
	 * Get scale info...
	 * An array of offsets from the root...
	 *
	 * @return int[]
	 */
	public function getScale()
	{
		return $this->scale;
	}

	/**
	 * Get MIDI sharps/flats...
	 *
	 * @return int
	 */
	public function getMIDIsf()
	{
		return $this->midi_sf;
	}

	/**
	 * Get MIDI midi major/minor...
	 *
	 * @return int
	 */
	public function getMIDImm()
	{
		return $this->midi_mm;
	}

	/**
	 * Returns the note an interval above/below another note...
	 * Must be passed dnote...  Math is diatonic.
	 * dnotes are basically in base 7, with the 10s digits being octave (0-11) & the units being scale note (0-6).
	 * Since dnotes are in base 7, the math is easy!
	 *
	 * @param int $dnote
	 * @param int $interval
	 * @return int
	 */
	public function dAdd($dnote, $interval)
	{
		$dnote = base_convert($dnote, 7, 10);
		$dnote += $interval;
		$dnote = base_convert($dnote, 10, 7);

		$dnote = MIDIEvent::rangeCheck($dnote, 0, 241);

		return $dnote;
	}

	/**
	 * Converts midi note (0-127) to diatonic note (octave, interval).
	 * dnotes are basically in base 7, with the 10s digits being octave (0-11) & the units being scale note (0-6).
	 * Needs to know what scale you're talking about (major, minor, modals, etc...).
	 *
	 * @param int $mnote
	 * @return int
	 */
	public function m2d($mnote)
	{
		//sanity check...
		$mnote = MIDIEvent::rangeCheck($mnote);

		$octs = intdiv($mnote, 12);
		$notes = $mnote % 12;

		// Since only keys of C starts on note 0, all other keys have a partial octave 0...
		if ($this->root != Key::C_NOTE)
			$octs++;

		// Offset from C...
		$notes = $notes - $this->root;
		if ($notes < 0)
		{
			$octs--;
			$notes += 12;
		}

		// Find which note in the scale
		// Everything will have a return value in key... Notes not in the scale will return the next highest note in the scale...
		for ($i = 0; $i < count($this->scale); $i++)
			if ($this->scale[$i] >= $notes)
				break;

		// Finally, calc dnote...
		$dnote = base_convert($octs, 10, 7) * 10 + $i;

		return $dnote;
	}

	/**
	 * Converts octave (provided in base 10) & interval into a dnote.
	 * dnotes are basically in base 7, with the 10s digits being octave (0-11) & the units being scale note (0-6).
	 *
	 * @param int $oct
	 * @param int $interval
	 * @return int
	 */
	public function getD($oct, $interval)
	{
		$octb7 = base_convert($oct, 10, 7);
		$dnote = $this->dAdd($octb7 * 10, $interval);
		$dnote = MIDIEvent::rangeCheck($dnote, 0, 241);

		return $dnote;
	}

	/**
	 * Converts diatonic note (octave, interval) to midi note (0-127).
	 * dnotes are basically in base 7, with the 10s digits being octave (0-11 (as 0-15...)) & the units being scale note (0-6).
	 * Needs to know what scale you're talking about (major, minor, modals, etc...).
	 *
	 * @param int $dnote
	 * @return int
	 */
	public function d2m($dnote)
	{
		$octb7 = intdiv($dnote, 10);
		$oct = base_convert($octb7, 7, 10);
		if ($this->root != Key::C_NOTE)
			$oct--;
		$noteb7 = $dnote % 10;
		$cnote = $this->scale[$noteb7];
		$mnote = ($oct * 12) + $cnote + $this->root;

		//sanity check...
		$mnote = MIDIEvent::rangeCheck($mnote);

		return $mnote;
	}

	/**
	 * Simple utility to make it easy to build chords.
	 * Requires a root note for the chord, & a variable # of intervals to build with.
	 * All input & interval math is diatonic.
	 * Returns an array of MIDI notes (not diatonic) suitable for being passed to addChord().
	 *
	 * @param int $dnote
	 * @param int $intervals - variable #
	 * @return int[]
	 */
	public function buildChord($dnote, ...$intervals)
	{
		$chord = array();
		$chord[] = $this->d2m($dnote);
		foreach ($intervals as $int)
			$chord[] = $this->d2m($this->dAdd($dnote, $int));

		return $chord;
	}

}

?>