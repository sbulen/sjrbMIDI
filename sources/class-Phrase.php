<?php
/**
 *	MIDI class for a musical Phrase.
 *	Includes various transformations, e.g., inversion, retrograde & snowflake.
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

class Phrase implements IteratorAggregate
{
	/**
	 * Bunch of constants
	 */
	// Transformation types
	const TRANS_START_DUR = 0;
	const TRANS_TRANSPOSE = 1;
	const TRANS_ROTATE = 2;
	const TRANS_RETROGRADE = 3;
	const TRANS_INVERT = 4;
	const TRANS_INVERT_SET = 5;

	/*
	 * Properties
	 */
	protected array $note_arr;
	protected ?Key $key;
	protected int $start;
	protected int $dur;

	/*
	 * Iterators
	 */
	public Iterator $walkSD;
	public Iterator $walkAll;

	/**
	 * Constructor
	 *
	 * Builds a Phrase object, an array of Note objects
	 *
	 * @param Note[] $note_arr - array of Note objects
	 * @param Key $key - Key object
	 * @return void
	 */
	function __construct(array $note_arr = array(), Key $key = null)
	{
		$min_time = null;
		$max_time = null;
		$this->start = 0;
		$this->dur = 0;

		$this->note_arr = array();
		$this->key = null;

		if (!is_array($note_arr))
			return;

		// Confirm they're all notes...
		// While going thru, find min & max times used.
		foreach($note_arr AS $note)
			if (!is_a($note, 'Note'))
				return;
			else
			{
				if ($min_time === null || ($note->getAt() < $min_time))
					$min_time = $note->getAt();
				if ($max_time === null || (($note->getAt() + $note->getDur()) >= $max_time))
					$max_time = $note->getAt() + $note->getDur();
			}

		// Confirm key is a Key...
		if (!is_a($key, 'Key'))
			return;

		// OK to assign...
		$this->note_arr = $note_arr;
		$this->key = $key;

		// Normalize so start is always 0 upon initial creation
		foreach ($this->note_arr AS $note)
			$note->setAt($note->getAt() - $min_time);

		$this->dur = $max_time - $min_time;

		$this->walkSD = new PhraseWalkSD($this->note_arr, $this->start, $this->dur);
		$this->walkAll = new PhraseWalkAll($this->note_arr, $this->start, $this->dur);
	}

	/**
	 * Clone method
	 *
	 * Ensure all subobjects are actually new objects
	 *    ...or bad things happen.
	 *
	 * @return void
	 */
	function __clone(): void
	{
		foreach($this->note_arr AS $ix => $note)
			$this->note_arr[$ix] = clone $this->note_arr[$ix];
	}

	/**
	 * Allow for iteration thru note objects...
	 *
	 * @return MIDIEvent[]
	 */
	public function getIterator() : Traversable
	{
		return new ArrayIterator($this->note_arr);
	}

	/**
	 * Get note events...
	 *
	 * @return MIDIEvent[]
	 */
	public function getNotes(): array
	{
		$events = array();
		foreach ($this->note_arr AS $note)
		{
			$events[] = new NoteOn($note->getAt(), $note->getChan(), $this->key->d2m($note->getDNote()), $note->getVel());
			$events[] = new NoteOff($note->getAt() + $note->getDur(), $note->getChan(), $this->key->d2m($note->getDNote()), 0x40);
		}

		return $events;
	}

	/**
	 * Get current Phrase start...
	 *
	 * @return int
	 */
	public function getStart(): int
	{
		return $this->start;
	}

	/**
	 * Get current Phrase dur...
	 *
	 * @return int
	 */
	public function getDur(): int
	{
		return $this->Dur;
	}

	/**
	 * Get specified note object from note_arr...
	 *
	 * @param int $notenum
	 * @return Note
	 */
	public function getNoteObj(int $notenum): Note
	{
		return $this->note_arr[$notenum];
	}

	/**
	 * Transformation - Move & scale it - Set start & duration...
	 *
	 * @param int $start - new start time
	 * @param int $dur - new duration
	 * @return void
	 */
	public function setStartDur(int $start, int $dur): void
	{
		$prior_start = $this->start;
		$prior_dur = $this->dur;
		$this->start = $start;
		$this->dur = $dur;

		foreach ($this->note_arr AS $ix => $note)
		{
			$note->setAt((int) round((($this->note_arr[$ix]->getAt() - $prior_start) * $this->dur) / $prior_dur) + $this->start);
			$note->setDur((int) round(($this->note_arr[$ix]->getDur() * $this->dur) / $prior_dur));
		}

		$this->walkSD = new PhraseWalkSD($this->note_arr, $this->start, $this->dur);
		$this->walkAll = new PhraseWalkAll($this->note_arr, $this->start, $this->dur);
	}

	/**
	 * Transformation - Diatonic transposition...
	 *
	 * @param int $interval - interval
	 * @return void
	 */
	public function transpose(int $interval): void
	{
		$interval = (int) round($interval);
		foreach ($this->note_arr AS $note)
			$note->setDNote($this->key->dAdd($note->getDNote(), $interval));
	}

	/**
	 * Transformation - Rotation...
	 *
	 * @param int $notes - number of notes to rotate by; negative rotates left, positive rotates right
	 * @return void
	 */
	public function rotate(int $notes): void
	{
		$notes = (int) round($notes);

		// Need to know all start times.
		$start_positions = array();
		foreach ($this->note_arr AS $note)
			$start_positions[] = $note->getAt();

		// Get unique entries, sorted by time...
		$start_positions = array_unique($start_positions);
		sort($start_positions);

		// Turn 'em all into positive rotations for ease
		// (E.g., if 4 notes, rotating left 3 = rotating right 1...)
		$count = count($start_positions);
		$notes = $notes % $count;
		if ($notes < 0)
			$notes = $notes + $count;
		$notes = $count - $notes;

		// Do the actual rotation...
		if ($notes != 0)
		{
		    $diff = $this->start + $this->dur - $start_positions[$notes];
			foreach ($this->note_arr AS $note)
			{
				if (($note->getAt() + $diff) < ($this->start + $this->dur))
					$note->setAt($note->getAt() + $diff);
				else
					$note->setAt($note->getAt() + $diff - $this->dur);
			}
		}
	}

	/**
	 * Transformation - Retrograde (complete)...
	 * Flips notes & durations, mirroring the original horizontally.
	 *
	 * @return void
	 */
	public function retrograde(): void
	{
		foreach ($this->note_arr AS $note)
			$note->setAt(($this->start * 2) + $this->dur - $note->getAt() - $note->getDur());
	}

	/**
	 * Transformation - Invert...
	 * A vertical mirroring.  The old high note is the new low note & vice versa.
	 * E.g., ABGGG becomes GFAAA.
	 *
	 * @return void
	 */
	public function invert(): void
	{
		// First, save off working copy of all the dnotes...
		$notes = array();
		foreach ($this->note_arr as $note)
			$notes[] = $note->getDNote();

		// Sort by diatonic note, then sf.
		// Yes, I know this isn't always true *in terms of tones*, e.g., in the key of C, Cb < B#...
		// But this is intended to be an abstraction for all keys & modals, allowing for transpositions, etc.
		// This model provides meaningful output thru all transpositions, all keys, all modals.
		usort($notes, function($a, $b) {
			if ($a['dn'] == $b['dn'])
				return $a['sf'] - $b['sf'];
			else
				return $a['dn'] - $b['dn'];
			}
		);
		$low_note = $notes[0];
		$maxix = count($notes) - 1;
		$high_note = $notes[$maxix];

		// For each note, invert it within the range of existing notes.
		foreach ($this->note_arr as $ix => $note)
		{
			// Calculate delta from lowest note, diatonic & sf components...
			$interval = array();
			$interval['dn'] = $this->key->dSub($note->getDNote(), $low_note);
			$interval['sf'] = $note->getDNote()['sf'] - $low_note['sf'];

			// Subtract that from highest note, diatonic & sf components...
			$new_note = array();
			$new_note['dn'] = $this->key->dAdd($high_note, -$interval['dn'])['dn'];
			$new_note['sf'] = $high_note['sf'] - $interval['sf'];

			// Update note...
			$note->setDNote($new_note);
		}
	}

	/**
	 * Transformation - Invert within note set, only using existing notes
	 * A vertical mirroring.  The old high note is the new low note & vice versa.
	 * E.g., ABGGG becomes GBAAA.
	 *
	 * @return void
	 */
	public function invert_set(): void
	{
		// First, save off working copy of all the dnotes...
		$notes = array();
		foreach ($this->note_arr as $note)
			$notes[] = $note->getDNote();

		// Sort by diatonic note, then sf.
		// Yes, I know this isn't always true *in terms of tones*, e.g., in the key of C, Cb < B#...
		// But this is intended to be an abstraction for all keys & modals, allowing for transpositions, etc.
		// This model provides meaningful output thru all transpositions, all keys, all modals.
		usort($notes, function($a, $b) {
			if ($a['dn'] == $b['dn'])
				return $a['sf'] - $b['sf'];
			else
				return $a['dn'] - $b['dn'];
			}
		);

		// Eliminate dupes. It'd be nice if array_unique worked here...
		$prev_note = array();
		foreach ($notes AS $ix => $note)
		{
			if ($note === $prev_note)
				unset($notes[$ix]);
			else
				$prev_note = $note;
		}

		// Renumber keys...
		$notes = array_merge($notes);
		$maxix = count($notes) - 1;

		// For each note, invert it within the range of existing notes.
		foreach ($this->note_arr as $ix => $note)
		{
			$key = array_search($note->getDnote(), $notes);
			$new_note = $notes[$maxix - $key];
			$note->setDNote($new_note);
		}
	}
}
?>