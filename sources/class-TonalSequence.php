<?php
/**
 *	Tonal sequence - a set of parameters for generating some music, oriented toward tonal instruments.
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

class TonalSequence extends AbstractSequence
{
	/**
	 * Properties
	 */
	protected Key $key;						// Key of sequence
	protected array $phrases;				// Phrases passed as a simple array; if passed, used, if not passed, random phrases are generated
	protected array $root_seq;				// Array of roots of chords/phrases, if null, auto-generated
	protected int $root_oct;				// Where to start...  Used when generating chords/phrases
	protected int $num_phrases;				// Number of phrases to auto-generate
	protected int $max_notes_per_phrase;	// How big are the phrases, when auto-generated
	protected int $max_inc_dec;				// Max amount to inc or dec when building phrases
	protected int $min_dnote;				// Min dnote when building phrases
	protected int $max_dnote;				// Max dnote when building phrases
	protected float $phrase_note_pct;		// What percentage of notes are kept, when phrases are auto-generated
	protected float $phrase_trip_pct;		// What percentage of notes are triplets, when phrases are auto-generated

	protected array $intervals;				// Derived from root_seq

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
	 * @param int $max_notes_per_phrase
	 * @param int $max_inc_dec
	 * @param int $min_dnote
	 * @param int $max_dnote
	 * @param float $phrase_note_pct
	 * @param float $phrase_trip_pct
	 * @return void
	 */
	function __construct(Key $key, Rhythm $rhythm, int $downbeat = 1, int $duration = 1, array $dests = array(1), float $note_pct = 1, float $trip_pct = 0, array $phrases = null, array $root_seq = null, int $root_oct = 5, int $num_phrases = 4, int $max_notes_per_phrase = 5, int $max_inc_dec = 4, int $min_dnote = 30, int $max_dnote = 70, float $phrase_note_pct = .8, float $phrase_trip_pct = .1)
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
					$note = $this->key->dAdd($note, $this->randIncDec($note, false));
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

			// max notes per phrase
			if (is_int($max_notes_per_phrase) && ($max_notes_per_phrase > 0))
				$this->max_notes_per_phrase = $max_notes_per_phrase;
			else
				Errors::fatal('inv_mnpp');

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
	private function genPhrases(): array
	{
		// Dummy vars, placeholders...  Will be overridden later...
		static $chan = 0;
		static $vel = 30;
		
		$phrase_objs = array();
		for ($i = 0; $i < $this->num_phrases; $i++)
		{
			// Get # of pulses from curr rhythm...
			$pulses = $this->rhythm->getPulses();

			// But don't let it get out of hand...
			if ($pulses > $this->max_notes_per_phrase)
				$pulses = $this->max_notes_per_phrase;

			// Create new random rhythm based on # of pulses... x3 in case of triplets...
			$new_rhythm = new Rhythm();
			$new_rhythm->randomize($pulses);
			$new_rhythm->setStartDur(0, $pulses * 3);
			
			$note_arr = array();
			// Generate using root from start of root_seq.
			// Transpositions based on intervals from root_seq later...
			$dnote = $this->key->cleanseDNote($this->root_seq[0]);
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

						$dnote = $this->key->dAdd($dnote, $this->randIncDec($dnote, true));
						$new_start = $new_start + $new_dur;
					}
					continue;
				}

				// Apply note pct
				if (MathFuncs::randomFloat() <= $this->phrase_note_pct)
					$note_arr[] = new Note($chan, $start, $dnote, $vel, $dur);

				$dnote = $this->key->dAdd($dnote, $this->randIncDec($dnote, true));
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
	 * Return a safe, random, amount to inc or dec by, honoring $max_inc_dec, $min_dnote & $max_dnote
	 * Phrases need their own version...
	 *
	 * @param dnote
	 * @return int
	 */

	public function randIncDec(array $curr_dnote, bool $phrase_gen = false): int
	{
		// Adjust min/max based on (dnote - root_oct) during phrase generation.
		// Otherwise phrases can get squished, e.g., when passed a root_seq.
		// To fix, treat min/max as 'floating' during phrase generation.
		// I.e., at this point, honor changes in root_seq, yet still keep narrow ranges if defined...
		if ($phrase_gen)
		{
			$gen_root = $this->key->getD($this->root_oct, 0);
			$diff = $this->key->dSub($curr_dnote, $gen_root);
			$temp_min = $this->key->dAdd($this->min_dnote, $diff);
			$temp_max = $this->key->dAdd($this->max_dnote, $diff);
		}
		else
		{
			$temp_min = Key::cleanseDNote($this->min_dnote);
			$temp_max = Key::cleanseDNote($this->max_dnote);
		}

		$val = base_convert($curr_dnote['dn'], 7, 10);
		$min = base_convert($temp_min['dn'], 7, 10);
		$max = base_convert($temp_max['dn'], 7, 10);

		$min_inc = -$this->max_inc_dec;
		$max_inc = $this->max_inc_dec;

		if (($val + $this->max_inc_dec) > $max)
			$max_inc = 0;
		if (($val - $this->max_inc_dec) < $min)
			$min_inc = 0;

		return rand($min_inc, $max_inc);
	}

	/*
	 * Get phrases...
	 *
	 * @return Phrase[]
	 */

	public function getPhrases(): array
	{
		return $this->phrases;
	}

	/*
	 * Get root_seq...
	 *
	 * @return DNote[]
	 */

	public function getRootSeq(): array
	{
		return $this->root_seq;
	}

	/*
	 * Get root_oct...
	 *
	 * @return int
	 */

	public function getRootOct(): int
	{
		return $this->root_oct;
	}

	/*
	 * Get num_phrases...
	 *
	 * @return int
	 */

	public function getNumPhrases(): int
	{
		return $this->num_phrases;
	}

	/*
	 * Get phrase_note_pct...
	 *
	 * @return float
	 */

	public function getPhraseNotePct(): float
	{
		return $this->phrase_note_pct;
	}

	/*
	 * Get phrase_trip_pct...
	 *
	 * @return float
	 */

	public function getPhraseTripPct(): float
	{
		return $this->phrase_trip_pct;
	}

	/*
	 * Get intervals...
	 *
	 * @return int[]
	 */

	public function getIntervals(): array
	{
		return $this->intervals;
	}

	/*
	 * Get key...
	 *
	 * @return Key
	 */

	public function getKey(): Key
	{
		return $this->key;
	}
}
?>