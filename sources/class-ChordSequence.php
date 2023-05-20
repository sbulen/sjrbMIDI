<?php
/**
 *	Chord sequence - a set of parameters for generating a chord sequence.
 *	In general, one channel, one instrument (e.g., piano), lots of parameters (chords, phrases)...
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

class ChordSequence extends AbstractSequence
{
	/**
	 * Properties
	 */
	protected $key;
	protected $chords;				// Chord sequence passed as a simple array; if passed, used, if not passed, random chords are generated
	protected $root_seq;			// Array of roots of chords/phrases
	protected $root_oct;			// Where to start...  Used when generating chords/phrases
	protected $max_notes_per_chord;	// How big are the chords, when auto-generated
	protected $max_inc_dec;			// Max amount to inc or dec when building chord sequences
	protected $min_dnote;			// Min dnote when building chord sequences
	protected $max_dnote;			// Max dnote when building chord sequences
	protected $inversion_pct;		// What percentage of chords are inverted, when auto-generated
	protected $chord_note_pct;		// What percentage of chords are kept, when auto-generated
	protected $chord_trip_pct;		// What percentage of chords are triplets, when auto-generated

	protected $intervals;			// Derived from root_seq

	/**
	 * Constructor
	 *
	 * Builds object to hold a set of parameters to generate some music.
	 *
	 * @param Key $key
	 * @param Rhythm $rhythm
	 * @param int $downbeat
	 * @param int $duration
	 * @param int[] $destinations
	 * @param float $note_pct
	 * @param float $trip_pct
	 * @param array() $chords
	 * @param DNote[] $root_seq
	 * @param int $root_oct
	 * @param int $max_notes_per_chord
	 * @param int $max_inc_dec
	 * @param int $min_dnote
	 * @param int $max_dnote
	 * @param float $inversion_pct
	 * @param float $chord_note_pct
	 * @param float $chord_trip_pct
	 * @return void
	 */
	function __construct($key, $rhythm, $downbeat = 1, $duration = 1, $dests = array(1), $note_pct = 1, $trip_pct = 0, $chords = array(), $root_seq = null, $root_oct = 5, $max_notes_per_chord = 4, $max_inc_dec = 4, $min_dnote = 30, $max_dnote = 70, $inversion_pct = 0, $chord_note_pct = .8, $chord_trip_pct = .1)
	{
		// Load all the basics first...
		parent::__construct($rhythm, $downbeat, $duration, $dests, $note_pct, $trip_pct);

		// Key...
		if (is_a($key, 'Key'))
			$this->key = $key;
		else
			Errors::fatal('inv_key');
		
		// Passed something?
		if (is_array($chords) && (count($chords) > 0))
		{
			$this->chords = array();
			foreach ($chords AS $chord)
				if (is_a($chord, 'Chord'))
					$this->chords[] = clone $chord;
				else
					Errors::fatal('inv_chord');
		}
		else
		// OK, we need we need to generate them...
		// Two layers of randomness here; if no root_seq provided, create one.
		// Then generate random chords.
		// Only validate chord parameters if we need to generate them...
		if ($this->chords === null)
		{
			// root_oct
			if (is_int($root_oct) && ($root_oct >= 0) && ($root_oct <= 11))
				$this->root_oct = $root_oct;
			else
				Errors::fatal('inv_rootoct');

			// max inc dec
			if (is_int($max_inc_dec) && ($max_inc_dec > 0) && ($max_inc_dec < 127))
				$this->max_inc_dec = $max_inc_dec;
			else
				Errors::fatal('inv_maxid');

			// min dnote
			if (is_int($min_dnote) && ($min_dnote >= 0) && ($min_dnote <= 144))
				$this->min_dnote = $min_dnote;
			else
				Errors::fatal('inv_minnote');

			// max dnote
			if (is_int($max_dnote) && ($max_dnote >= 0) && ($max_dnote <= 144) && ($max_dnote > $min_dnote))
				$this->max_dnote = $max_dnote;
			else
				Errors::fatal('inv_maxnote');

			// Load up array of roots...  Convert to DNotes...
			if (empty($root_seq))
			{
				// start off...
				$roots = $this->rhythm->getBeats();
				$note = $this->key->getD($this->root_oct, 0);
				$this->root_seq = array();
				for ($i = 0; $i < $roots; $i++)
				{
					$this->root_seq[] = $note;
					$note = $this->key->dAdd($note, $this->randIncDec($note));
				}
			}
			// Might be an array of ints or an array of dnotes
			elseif (is_array($root_seq) && ($root_seq == array_filter($root_seq, function($a) {return (is_int($a) || (isset($a['dn']) && is_numeric($a['dn'])));})))
				$this->root_seq = $root_seq;
			else
				Errors::fatal('inv_rootseq');

			// Derive your intervals from root_seq
			$this->intervals = array(0);
			for ($i = 1; $i < count($this->root_seq); $i++)
				$this->intervals[] = $key->dSub($this->root_seq[$i], $this->root_seq[0]);

			// Used 30 as a theoretical limit, since 3rds are ~4 semitones apart & we only have 127 to work with in midi!
			if (is_int($max_notes_per_chord) && ($max_notes_per_chord > 0) && ($max_notes_per_chord <= 30))
				$this->max_notes_per_chord = $max_notes_per_chord;
			else
				Errors::fatal('inv_mnpc');

			// inversion_pct
			if (is_numeric($inversion_pct) && ($inversion_pct >= 0) && ($inversion_pct <= 1))
				$this->inversion_pct = $inversion_pct;
			else
				Errors::fatal('inv_invpct');

			// chord_note_pct
			if (is_numeric($chord_note_pct) && ($chord_note_pct >= 0) && ($chord_note_pct <= 1))
				$this->chord_note_pct = $chord_note_pct;
			else
				Errors::fatal('inv_chnotepct');

			// chord_trip_pct
			if (is_numeric($chord_trip_pct) && ($chord_trip_pct >= 0) && ($chord_trip_pct <= 1))
				$this->chord_trip_pct = $chord_trip_pct;
			else
				Errors::fatal('inv_chtrippct');

			// Got it all, generate the chords...
			$this->chords = $this->genChords();
		}
	}

	// Gen Chords
	// Build some random chord ***phrasings*** from scratch...
	// Transposition happens later, in generation.
	// What varies among the phrasings are: number of notes in chord, inversions, note percents/gaps...
	private function genChords()
	{
		$chords = array();

		// Generate using root from start of root_seq.
		// Transpositions based on intervals from root_seq later...
		$dnote = $this->key->cleanseDNote($this->root_seq[0]);

		for ($notes = 1; $notes < $this->max_notes_per_chord; $notes++)
		{
			$intervals = array();
			for ($j = 0; $j < $notes; $j++)
			{
				// Apply chord note pct here...
				if (MathFuncs::randomFloat() <= $this->getChordNotePct())
					$intervals[] = ($j + 1) * 2;
			}
			$chord = new Chord($dnote, $intervals);

			// Final step, apply a random 1st or 2nd inversion...
			if (MathFuncs::randomFloat() <= $this->inversion_pct)
				$chord->inversion(rand(1, 2));

			// Save off constructed chord
			$chords[] = $chord;
		}

		return $chords;
	}

	/*
	 * Return a safe, random, amount to inc or dec by, honoring $max_inc_dec, $min_dnote & $max_dnote
	 *
	 * @param dnote
	 * @return int
	 */

	public function randIncDec($curr_dnote)
	{
		$val = base_convert($curr_dnote['dn'], 7, 10);
		$min = base_convert($this->min_dnote, 7, 10);
		$max = base_convert($this->max_dnote, 7, 10);

		$min_inc = -$this->max_inc_dec;
		$max_inc = $this->max_inc_dec;

		if (($val + $this->max_inc_dec) > $max)
			$max_inc = 0;
		if (($val - $this->max_inc_dec) < $min)
			$min_inc = 0;

		return rand($min_inc, $max_inc);
	}

	/*
	 * Get chords...
	 *
	 * @return Chord[]
	 */

	public function getChords()
	{
		return $this->chords;
	}

	/*
	 * Get root_seq...
	 *
	 * @return DNote[]
	 */

	public function getRootSeq()
	{
		return $this->root_seq;
	}

	/*
	 * Get root_oct...
	 *
	 * @return int
	 */

	public function getRootOct()
	{
		return $this->root_oct;
	}

	/*
	 * Get max_notes_per_chord...
	 *
	 * @return int
	 */

	public function getMaxNotesPerChord()
	{
		return $this->max_notes_per_chord;
	}

	/*
	 * Get inversion_pct...
	 *
	 * @return float
	 */

	public function getInversionPct()
	{
		return $this->inversion_pct;
	}

	/*
	 * Get chord_note_pct...
	 *
	 * @return float
	 */

	public function getChordNotePct()
	{
		return $this->chord_note_pct;
	}

	/*
	 * Get chord_trip_pct...
	 *
	 * @return float
	 */

	public function getChordTripPct()
	{
		return $this->chord_trip_pct;
	}

	/*
	 * Get intervals...
	 *
	 * @return int[]
	 */

	public function getIntervals()
	{
		return $this->intervals;
	}

	/*
	 * Get key...
	 *
	 * @return Key
	 */

	public function getKey()
	{
		return $this->key;
	}
}
?>