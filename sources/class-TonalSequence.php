<?php
/**
 *	Tonal sequence - a set of parameters for generating some music, oriented toward tonal instruments.
 *	In general, one channel, one instrument (e.g., piano), lots of parameters (chords, phrases)...
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

class TonalSequence extends AbstractSequence
{
	/**
	 * Properties
	 */
	protected $key;
	protected $phrases;			// Phrases passed as a simple array; if passed, used, if not passed, random phrases are generated
	protected $root_seq;		// Array of roots of chords/phrases, if null, auto-generated
	protected $root_oct;		// Where to start...  Used when generating chords/phrases
	protected $num_phrases;		// Number of phrases to auto-generate
	protected $phrase_note_pct;	// What percentage of notes are kept, when phrases are auto-generated
	protected $phrase_trip_pct;	// What percentage of notes are triplets, when phrases are auto-generated

	protected $intervals;		// Derived from root_seq

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
	 * @param array() $phrases
	 * @param DNote[] $root_seq
	 * @param int $root_oct
	 * @param int $num_phrases
	 * @param float $phrase_note_pct
	 * @param float $phrase_trip_pct
	 * @return void
	 */
	function __construct($key, $rhythm, $downbeat = 1, $duration = 1, $dests = array(1), $note_pct = 1, $trip_pct = 0, $phrases = array(), $root_seq = null, $root_oct = 5, $num_phrases = 4, $phrase_note_pct = .8, $phrase_trip_pct = .1)
	{
		// Load all the basics first...
		parent::__construct($rhythm, $downbeat, $duration, $dests, $note_pct, $trip_pct);

		// Key...
		if (is_a($key, 'Key'))
			$this->key = $key;
		else
			Errors::fatal('inv_key');
		
		// Passed something?
		if (is_array($phrases) && count($phrases > 0))
		{
			$this->phrases = array();
			foreach ($phrases AS $phrase)
				if (is_a($phrase, 'Phrase'))
					$this->phrases[] = clone $phrase;
				else
					Errors::fatal('inv_phrase');
		}
		elseif (empty($phrases))
		// OK, we need we need to generate them...
		// Two layers of randomness here; if no root_seq provided, create one.
		// Then generate random phrases.
		// Only validate phrase parameters if we need to generate them...
		{
			// root_oct
			if (is_int($root_oct) && ($root_oct >= 0) && ($root_oct <= 11))
				$this->root_oct = $root_oct;
			else
				Errors::fatal('inv_rootoct');

			// Load up array of roots...  Convert to DNotes...
			if (empty($root_seq))
			{
				// start off...  
				$roots = $this->rhythm->getBeats() * $duration;
				$note = $this->key->getD($this->root_oct, 0);
				$this->root_seq = array();
				for ($i = 0; $i < $roots; $i++)
				{
					$this->root_seq[] = $note;
					$note = $this->key->dAdd($note, rand(-4, 4));
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

			// num_phrases
			if (is_int($num_phrases) && ($num_phrases > 0))
				$this->num_phrases = $num_phrases;
			else
				Errors::fatal('inv_numphr');

			// phrase_note_pct
			if (is_numeric($phrase_note_pct) && ($phrase_note_pct >= 0) && ($phrase_note_pct <= 1))
				$this->phrase_note_pct = $phrase_note_pct;
			else
				Errors::fatal('inv_phnotepct');

			// phrase_trip_pct
			if (is_numeric($phrase_trip_pct) && ($phrase_trip_pct >= 0) && ($phrase_trip_pct <= 1))
				$this->phrase_trip_pct = $phrase_trip_pct;
			else
				Errors::fatal('inv_phtrippct');

			// Got it all, generate the phrases...
			$this->phrases = $this->genPhrases();
		}
		else
			Errors::fatal('inv_phrase');
	}

	// Gen Phrases
	// Build phrases from scratch...
	// Transpositions, etc., happen later, in generation
	private function genPhrases()
	{
		// Dummy vars, placeholders...  Will be overridden later...
		static $chan = 0;
		static $vel = 30;
		
		$phrase_objs = array();
		for ($i = 0; $i < $this->num_phrases; $i++)
		{
			// Get # of pulses from curr rhythm...
			$pulses = $this->rhythm->getPulses();

			// Create new random rhythm based same # of pulses... x3 in case of triplets...
			$new_rhythm = new Rhythm();
			$new_rhythm->randomize($pulses);
			$new_rhythm->setStartDur(0, $pulses * 3);
			
			$note_arr = array();
			// Start or end on the root...
			$dnote = $this->key->getD($this->root_oct, 0);
			foreach ($new_rhythm->walkSD AS $start => $dur)
			{
				// Apply a triplet?
				if (MathFuncs::randomFloat() <= $this->phrase_trip_pct)
				{
					$new_start = $start;
					$new_dur = (int) ($dur / 3);
					for ($i = 0; $i < 3; $i++)
					{
						if (MathFuncs::randomFloat() <= $this->phrase_note_pct)
							$note_arr[] = new Note($chan, $new_start, $dnote, $vel, $new_dur);

						$dnote = $this->key->dAdd($dnote, rand(-2, 2));
						$new_start = $new_start + $new_dur;
					}
					continue;
				}

				// Apply note pct
				if (MathFuncs::randomFloat() <= $this->phrase_note_pct)
					$note_arr[] = new Note($chan, $start, $dnote, $vel, $dur);

				$dnote = $this->key->dAdd($dnote, rand(-2, 2));
			}

			// Start or end on the root, 50/50...
			$phrase = new Phrase($note_arr, $this->key);
			if (MathFuncs::randomFloat() <= 0.5)
				$phrase->retrograde();

			$phrase_objs[] = $phrase;
		}
		return $phrase_objs;
	}

	/*
	 * Get phrases...
	 *
	 * @return Phrase[]
	 */

	public function getPhrases()
	{
		return $this->phrases;
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
	 * Get num_phrases...
	 *
	 * @return int
	 */

	public function getNumPhrases()
	{
		return $this->num_phrases;
	}

	/*
	 * Get phrase_note_pct...
	 *
	 * @return float
	 */

	public function getPhraseNotePct()
	{
		return $this->phrase_note_pct;
	}

	/*
	 * Get phrase_trip_pct...
	 *
	 * @return float
	 */

	public function getPhraseTripPct()
	{
		return $this->phrase_trip_pct;
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