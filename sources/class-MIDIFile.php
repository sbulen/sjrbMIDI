<?php
/**
 *	MIDI class hierarchy for MIDI files.
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

abstract class MIDIChunk
{
	/**
	 * File stuff - chunks
	 */
	const CHUNK_HDR = 'MThd';
	const CHUNK_TRK = 'MTrk';

	// Type & length are common to all chunks
	protected $type = '';
	protected $length = 0;

	/**
	 * Pack = to convert to MIDI binary format for writing to disk
	 *
	 * @return string
	 */
	abstract function pack();
}

class MIDIHdr extends MIDIChunk
{
	/**
	 * MIDI header
	 */
	// Type & length are common to all chunks
	protected $type = MIDIChunk::CHUNK_HDR;
	protected $length = 6;
	protected $format = 1;
	protected $ntrks = 1;
	protected $ticks = 960;
	protected $smpte_format = null;
	protected $ticks_per_frame = null;

	/**
	 * Constructor
	 *
	 * Builds object to represent a midi file header.
	 * Wonderful detail may be found hered: https://www.personal.kent.edu/~sbirch/Music_Production/MP-II/MIDI/midi_file_format.htm
	 *
	 * @param int $format - MIDI file format
	 * @param int $ntrks - The number of tracks in the file
	 * @param int $ticks - The number of ticks per quarter note used throughout the file, if ticks-based
	 * @param int $smpte_format - The smpte frames/second, if smpte-based
	 * @param int $ticks_per_frame - The smpte ticks/frame, if smpte-based
	 * @return void
	 */
	function __construct($format = 1, $ntrks = 1, $ticks = 960, $smpte_format = null, $ticks_per_frame = null)
	{
		$this->format = MIDIEvent::rangeCheck($format, 0, 2);
		$this->ntrks = MIDIEvent::rangeCheck($ntrks, 0, 0xFFFF);
		if (!empty($ticks))
			$this->ticks = MIDIEvent::rangeCheck($ticks, 0, 0xFFFF);
		if (!empty($smpte_format))
			$this->smpte_format = MIDIEvent::rangeCheck($smpte_format, 0, 0x7F);
		if (!empty($ticks_per_frame))
			$this->ticks_per_frame = MIDIEvent::rangeCheck($ticks_per_frame, 0, 0xFF);
	}

	/**
	 * Return the number of tracks.
	 *
	 * @return int
	 */
	public function getNtrks()
	{
		return $this->ntrks;
	}

	/**
	 * Increment the number of tracks by 1.
	 *
	 * @return void
	 */
	public function incNtrks()
	{
		$this->ntrks++;
		return;
	}

	/**
	 * Return the number of ticks/qtr note used in this file.
	 *
	 * @return int
	 */
	public function getTicks()
	{
		return $this->ticks;
	}

	/**
	 * Pack = to convert to MIDI binary format for writing to disk
	 *
	 * @return string
	 */
	public function pack()
	{
		if (!empty($this->ticks))
			return $this->type . pack('Nnnn', $this->length, $this->format, $this->ntrks, $this->ticks);
		else
			return $this->type . pack('Nnn', $this->length, $this->format, $this->ntrks) . chr($this->smpte_format | 0x80) . chr($this->ticks_per_frame);
	}
}

class MIDItrk extends MIDIChunk
{
	/**
	 * MIDI Track info
	 */
	// Type & length are common to all chunks
	protected $type = MIDIChunk::CHUNK_TRK;
	protected $length = 0;
	protected $events = array();

	/**
	 * Adds an event to the track
	 *
	 * @param MIDIEvent $event
	 * @return void
	 */
	public function addEvent($event)
	{
		$this->events[] = $event;
		return;
	}

	/**
	 * Adds multiple events to the track
	 *
	 * @param MIDIEvent[] $events
	 * @return void
	 */
	public function addEvents($events)
	{
		foreach($events AS $event)
			$this->addEvent($event);

		return;
	}

	/**
	 * Adds a note - both the NoteOn and NoteOff, separated by $dur
	 *
	 * @param int $at absolute time
	 * @param int $chan MIDI channel
	 * @param int $note MIDI note number
	 * @param int $vel note velocity
	 * @param int $dur note duration in ticks
	 * @return void
	 */
	public function addNote($at, $chan, $note, $vel, $dur)
	{
		// 40 is 'neutral' velocity on a note off...
		$this->events[] = new NoteOn($at, $chan, $note, $vel);
		$this->events[] = new NoteOff($at + $dur, $chan, $note, 0x40);
		return;
	}

	/**
	 * Adds a chord
	 *
	 * @param int $at absolute time
	 * @param int $chan MIDI channel
	 * @param int[] $notearr array of MIDI note numbers
	 * @param int $vel note velocity
	 * @param int $dur note duration in ticks
	 * @return void
	 */
	public function addChord($at, $chan, $notearr, $vel, $dur)
	{
		foreach ($notearr AS $note)
		{
			// 0x40 is 'neutral' velocity on a note off...
			$this->events[] = new NoteOn($at, $chan, $note, $vel);
			$this->events[] = new NoteOff($at + $dur, $chan, $note, 0x40);
		}
		return;
	}

	/**
	 * Adds a continuous controller event
	 *
	 * @param int $at absolute time
	 * @param int $chan MIDI channel
	 * @param int $cc controller number
	 * @param int $val controller value
	 * @return void
	 */
	public function addCC($at, $chan, $cc, $val)
	{
		$this->events[] = new ControlChange($at, $chan, $cc, $val);
		return;
	}

	/**
	 * Adds a pitch wheel event
	 *
	 * @param int $at absolute time
	 * @param int $chan MIDI channel
	 * @param int $val pitch wheel value
	 * @return void
	 */
	public function addWheel($at, $chan, $val)
	{
		$this->events[] = new PitchWheel($at, $chan, $val);
		return;
	}

	/**
	 * Get event by type; return the first event that matches the $type
	 * Useful when there is one primary one, e.g., header information like time signature
	 *
	 * @param int $type MIDI event type
	 * @return (false|MIDIEvent)
	 */
	public function getEvent($type)
	{
		// Check both the type & subtype, if available
		foreach ($this->events AS $event)
		{
			if (($event->getType() == $type) || (method_exists($event, 'getSubType') & ($event->getSubType() == $type)))
			{
				return $event;
			}
		}
		return false;
	}

	/**
	 * Add Track End event, required at the end of each track in a midi file
	 * If abs time specified, use it.
	 * If not, find last abs time in track & put it there.
	 *
	 * @param int $abs_time absolute time
	 * @return void
	 */
	public function addTrackEnd($abs_time = null)
	{
		if ($abs_time === null)
		{
			//Find greatest abs_time
			$max_at = 0;
			foreach($this->events AS $event)
				if ($event->getAt() > $max_at)
					$max_at = $event->getAt();
			$this->events[] = new TrackEnd($max_at);
		}
		else
		{
			$this->events[] = new TrackEnd($abs_time);
		}

		return;
	}

	/**
	 * Pack = to convert track to binary for writing to disk
	 *
	 * @return string
	 */
	public function pack()
	{
		// First, sort event objects by abs_time
		usort($this->events,
			function ($a, $b) {
				return $a->getAt() - $b->getAt();
			}
		);

		$abstime = 0;
		$string = '';
		foreach ($this->events AS &$event)
		{
			// Calc delta times as diffs between absolute times
			$event->setDt($event->getAt() - $abstime);
			$abstime = $event->getAt();
			// Build up track string from packed events
			$string .= $event->pack();
		}

		//Now we have the length of all events, so we can add the last pieces
		$this->length = strlen($string);
		$string = MIDIChunk::CHUNK_TRK . pack('N', $this->length) . $string;

		return $string;
	}
}

/**
 * MIDI file class
 * Operates on MIDI format 1 files.
 * Can read 0, 1 & 2, but manipulates & writes format 1.
 */

class MIDIFile
{
	/**
	 * File stuff
	 * Content is, basically, a header + 1 or more tracks.
	 * 
	 */
	protected $file_name = '';
	protected $header = null;
	protected $tracks = array();
	protected $file_raw_contents = '';

	/**
	 * Constructor
	 *
	 * Builds object to represent a midi file.
	 * If constructor is passed file name, read it.
	 * If not, initialize with good default content for the header & track 0 (meta data).
	 *
	 * @param string $file - MIDI file name
	 * @return void
	 */
	public function __construct($file = null)
	{
		if ($file !== null)
			$this->readMIDIFile($file);
		else
		{
			$this->file_name = '';
			$this->file_raw_contents = '';
			$this->tracks = array();

			// Header
			$this->header = new MIDIHdr;

			// Tempo track, track 0
			$this->tracks[0] = new MIDITrk;
			$this->tracks[0]->addEvent(new TrackName(0, 'Tempo track'));
			$this->tracks[0]->addEvent(new TimeSignature());
			$this->tracks[0]->addEvent(new KeySignature());
			$this->tracks[0]->addEvent(new Tempo());
			$this->tracks[0]->addEvent(new TrackEnd());

			// Load up raw_file_contents in case you want to display it...
			$this->raw_file_contents = $this->pack();
		}
	}

	/**
	 * Pack = to convert file to binary for writing to disk
	 * Places the contents into file_raw_contents
	 *
	 * @return string
	 */
	protected function pack()
	{
		$this->file_raw_contents = $this->header->pack();
		foreach ($this->tracks AS &$track)
			$this->file_raw_contents .= $track->pack();
		return;
	}

	/**
	 * Read the specified file.
	 * Needs to read the binary file contents one byte at a time & parse per MIDI file spec rules...
	 * If no name specified, use file name from the constructor.
	 * Places the contents into file_raw_contents
	 *
	 * @param string $file - MIDI file name
	 * @return void
	 */
	protected function readMIDIFile($file = null)
	{
		if ($file === null)
			$file = $this->file_name;
		else
			$this->file_name = $file;

		$this->file_raw_contents = file_get_contents($this->file_name);

		// Load the chunks...
		$offset = 0;
		$length = strlen($this->file_raw_contents);
		while ($offset < $length)
		{
			$chunktype = substr($this->file_raw_contents, $offset, 4);
			$chunklen = unpack('Nlength', substr($this->file_raw_contents, $offset + 4, 4))['length'];
			if ($chunktype == MIDIChunk::CHUNK_HDR)
			{
				// Header
				$this->header = $this->parseHeader(substr($this->file_raw_contents, $offset, $chunklen + 8));
			}
			elseif ($chunktype == MIDIChunk::CHUNK_TRK)
			{
				// Tracks
				$this->tracks[] = $this->parseTrack(substr($this->file_raw_contents, $offset, $chunklen + 8));
			}
			$offset += 8 + $chunklen;
		}

		return;
	}

	/**
	 * Parse the header attributes from the MThd.
	 * Needs to read the binary file contents one byte at a time & parse per MIDI file spec rules...
	 *
	 * @param string $data - MIDI file contents to parse
	 * @return MIDIHdr
	 */
	protected function parseHeader($data)
	{
		$format = unpack('nformat', substr($data, 8, 2))['format'];
		$ntrks = unpack('nntrks', substr($data, 10, 2))['ntrks'];
		$division = unpack('ndivision', substr($data, 12, 2))['division'];
		if (($division & 0x8000) === 0)
		{
			$ticks = $division;
			$smpte_format = null;
			$ticks_per_frame = null;
		}
		else
		{
			// smpte stuff...
			$smpte_format = ($division >> 8) & 0x7F;
			$ticks_per_frame = $division & 0xFF;
			$ticks = null;
		}

		return new MIDIHdr($format, $ntrks, $ticks, $smpte_format, $ticks_per_frame);
	}

	/**
	 * Parse an Mtrk as an array of events in a MIDITrk object.
	 * Needs to read the binary file contents one byte at a time & parse per MIDI file spec rules...
	 *
	 * @param string $data - MIDI file contents to parse
	 * @return MIDITrk
	 */
	protected function parseTrack($data = '')
	{
		$trackobj = new MIDITrk();

		$delta_time = new VLQ();
		$abstime = 0;
		$offset = 8;
		$length = strlen($data);
		while ($offset < $length)
		{
			$delta_time->readVLQ(substr($data, $offset, 4));
			$abstime += $delta_time->getValue();
			$byte1 = ord(substr($data, $offset + $delta_time->getLen(), 1));

			// Allow for running status, where they don't repeat the status byte...
			if ($byte1 < 0x80)
				$status_len = 0;
			else
			{
				$status_byte = $byte1;
				$status_len = 1;
			}

			$event = $status_byte >> 4;
			switch ($event)
			{
				case MIDIEvent::NOTE_OFF:
					$trackobj->addEvent(new NoteOff(
						$abstime,
						$status_byte & 0xF,
						ord(substr($data, $offset + $delta_time->getLen() + $status_len, 1)),
						ord(substr($data, $offset + $delta_time->getLen() + $status_len + 1, 1))));
					$offset += $delta_time->getLen() + $status_len + 2;
					break;
				case MIDIEvent::NOTE_ON:
					$trackobj->addEvent(new NoteOn(
						$abstime,
						$status_byte & 0xF,
						ord(substr($data, $offset + $delta_time->getLen() + $status_len, 1)),
						ord(substr($data, $offset + $delta_time->getLen() + $status_len + 1, 1))));
					$offset += $delta_time->getLen() + $status_len + 2;
					break;
				case MIDIEvent::POLY_AFTER_TOUCH:
					$trackobj->addEvent(new PolyAfterTouch(
						$abstime,
						$status_byte & 0xF,
						ord(substr($data, $offset + $delta_time->getLen() + $status_len, 1)),
						ord(substr($data, $offset + $delta_time->getLen() + $status_len + 1, 1))));
					$offset += $delta_time->getLen() + $status_len + 2;
					break;
				case MIDIEvent::CONTROL_CHANGE:
					$trackobj->addEvent(new ControlChange(
						$abstime,
						$status_byte & 0xF,
						ord(substr($data, $offset + $delta_time->getLen() + $status_len, 1)),
						ord(substr($data, $offset + $delta_time->getLen() + $status_len + 1, 1))));
					$offset += $delta_time->getLen() + $status_len + 2;
					break;
				case MIDIEvent::PROGRAM_CHANGE:
					$trackobj->addEvent(new ProgramChange(
						$abstime,
						$status_byte & 0xF,
						ord(substr($data, $offset + $delta_time->getLen() + $status_len, 1))));
					$offset += $delta_time->getLen() + $status_len + 1;
					break;
				case MIDIEvent::AFTER_TOUCH:
					$trackobj->addEvent(new AfterTouch(
						$abstime,
						$status_byte & 0xF,
						ord(substr($data, $offset + $delta_time->getLen() + $status_len, 1))));
					$offset += $delta_time->getLen() + $status_len + 1;
					break;
				case MIDIEvent::PITCH_WHEEL:
					$trackobj->addEvent(new PitchWheel(
						$abstime,
						$status_byte & 0xF,
						((ord(substr($data, $offset + $delta_time->getLen() + $status_len, 1)) & 0x7F) << 7) | ord(substr($data, $offset + $delta_time->getLen() + $status_len + 1, 1)) & 0x7F));
					$offset += $delta_time->getLen() + $status_len + 2;
					break;
				case MIDIEvent::META_SYSEX:
					$offset += $this->parseMetaSysex(substr($data, $offset), $trackobj, $abstime);
					break;
				default:
					echo 'UNKNOWN TYPE<br>UNKNOWN TYPE<br>UNKNOWN TYPE<br><br>';
					$offset = $length;
					break;
			};
		}

		return $trackobj;
	}

	/**
	 * Parse a single meta event or sysex event.
	 * Must return the # of bytes read that held the event.
	 * Needs to read the binary file contents one byte at a time & parse per MIDI file spec rules...
	 *
	 * @param string $data - MIDI file contents to parse
	 * @param MIDITrk &$trackobj - MIDI file track object to add event to - passed by reference
	 * @param int $abstime - Start time of the current set of data
	 * @return int
	 */
	protected function parseMetaSysex($data, &$trackobj, &$abstime)
	{
		if (empty($data))
			$data = '';
		$delta_time = new VLQ();
		$delta_time->readVLQ(substr($data, 0, 4));
		$abstime += $delta_time->getValue();

		$datalen = new VLQ();
		$msglen = strlen($data);

		$type = ord(substr($data, $delta_time->getLen(), 1));
		switch ($type)
		{
			case MIDIEvent::SYSEX:
				$datalen->readVLQ(substr($data, $delta_time->getLen() + 1, 4));
				$trackobj->addEvent(new Sysex(
					$abstime,
					substr($data, $delta_time->getLen() + 1 + $datalen->getLen, $datalen->getValue)));
				$msglen = $delta_time->getLen() + 1 + $datalen->getLen() + $datalen->getValue();
				break;
			case MIDIEvent::SYSEX_ESCAPE:
				$datalen->readVLQ(substr($data, $delta_time->getLen() + 1, 4));
				$trackobj->addEvent(new SysexEscape(
					$abstime,
					substr($data, $delta_time->getLen() + 1 + $datalen->getLen, $datalen->getValue)));
				$msglen = $delta_time->getLen() + 1 + $datalen->getLen() + $datalen->getValue();
				break;
			case MIDIEvent::META_EVENT:
				$msglen = $this->parseMetaEvent($data, $trackobj, $abstime);
				break;
			default:
				echo 'UNKNOWN TYPE<br>UNKNOWN TYPE<br>UNKNOWN TYPE<br><br>';
				$msglen = strlen($data);
				break;
		};

		return $msglen;
	}

	/**
	 * Parse a single meta event & its details.
	 * Must return the # of bytes read that held the event.
	 * Needs to read the binary file contents one byte at a time & parse per MIDI file spec rules...
	 *
	 * @param string $data - MIDI file contents to parse
	 * @param MIDITrk &$trackobj - MIDI file track object to add event to - passed by reference
	 * @param int $abstime - Start time of the current set of data
	 * @return int
	 */
	protected function parseMetaEvent($data, &$trackobj, &$abstime)
	{
		if (empty($data))
			$data = '';
		$delta_time = new VLQ();
		$delta_time->readVLQ(substr($data, 0, 4));
		$abstime += $delta_time->getValue();

		$datalen = new VLQ();
		$msglen = strlen($data);

		$subtype = ord(substr($data, $delta_time->getLen() + 1, 1));
		switch ($subtype)
		{
			case MIDIEvent::META_SEQ_NO:
				$trackobj->addEvent(new SequenceNo(
					$abstime,
					(ord(substr($data, $delta_time->getLen() + 3, 1)) << 8) | ord(substr($data, $delta_time->getLen() + 4, 1))));
				$msglen = $delta_time->getLen() + 5;
				break;
			case MIDIEvent::META_TEXT:
				$datalen->readVLQ(substr($data, $delta_time->getLen() + 2, 4));
				$trackobj->addEvent(new Text(
					$abstime,
					substr($data, $delta_time->getLen() + 2 + $datalen->getLen(), $datalen->getValue())));
				$msglen = $delta_time->getLen() + 2 + $datalen->getLen() + $datalen->getValue();
				break;
			case MIDIEvent::META_COPYRIGHT:
				$datalen->readVLQ(substr($data, $delta_time->getLen() + 2, 4));
				$trackobj->addEvent(new Copyright(
					$abstime,
					substr($data, $delta_time->getLen() + 2 + $datalen->getLen(), $datalen->getValue())));
				$msglen = $delta_time->getLen() + 2 + $datalen->getLen() + $datalen->getValue();
				break;
			case MIDIEvent::META_TRACK_NAME:
				$datalen->readVLQ(substr($data, $delta_time->getLen() + 2, 4));
				$trackobj->addEvent(new TrackName(
					$abstime,
					substr($data, $delta_time->getLen() + 2 + $datalen->getLen(), $datalen->getValue())));
				$msglen = $delta_time->getLen() + 2 + $datalen->getLen() + $datalen->getValue();
				break;
			case MIDIEvent::META_INST_NAME:
				$datalen->readVLQ(substr($data, $delta_time->getLen() + 2, 4));
				$trackobj->addEvent(new InstName(
					$abstime,
					substr($data, $delta_time->getLen() + 2 + $datalen->getLen(), $datalen->getValue())));
				$msglen = $delta_time->getLen() + 2 + $datalen->getLen() + $datalen->getValue();
				break;
			case MIDIEvent::META_LYRIC:
				$datalen->readVLQ(substr($data, $delta_time->getLen() + 2, 4));
				$trackobj->addEvent(new Lyric(
					$abstime,
					substr($data, $delta_time->getLen() + 2 + $datalen->getLen(), $datalen->getValue())));
				$msglen = $delta_time->getLen() + 2 + $datalen->getLen() + $datalen->getValue();
				break;
			case MIDIEvent::META_MARKER:
				$datalen->readVLQ(substr($data, $delta_time->getLen() + 2, 4));
				$trackobj->addEvent(new Marker(
					$abstime,
					substr($data, $delta_time->getLen() + 2 + $datalen->getLen(), $datalen->getValue())));
				$msglen = $delta_time->getLen() + 2 + $datalen->getLen() + $datalen->getValue();
				break;
			case MIDIEvent::META_CUE:
				$datalen->readVLQ(substr($data, $delta_time->getLen() + 2, 4));
				$trackobj->addEvent(new Cue(
					$abstime,
					substr($data, $delta_time->getLen() + 2 + $datalen->getLen(), $datalen->getValue())));
				$msglen = $delta_time->getLen() + 2 + $datalen->getLen() + $datalen->getValue();
				break;
			case MIDIEvent::META_CHAN_PFX:
				$trackobj->addEvent(new ChannelPrefix(
					$abstime,
					ord(substr($data, $delta_time->getLen() + 3, 1))));
				$msglen = $delta_time->getLen() + 4;
				break;
			case MIDIEvent::META_TRACK_END:
				$trackobj->addEvent(new TrackEnd(
					$abstime));
				$msglen = $delta_time->getLen() + 3;
				break;
			case MIDIEvent::META_TEMPO:
				$trackobj->addEvent(new Tempo(
					$abstime,
					(ord(substr($data, $delta_time->getLen() + 3, 1)) << 16) | (ord(substr($data, $delta_time->getLen() + 4, 1)) << 8) | ord(substr($data, $delta_time->getLen() + 5, 1))));
				$msglen = $delta_time->getLen() + 6;
				break;
			case MIDIEvent::META_SMPTE:
				$trackobj->addEvent(new SmpteOffset(
					$abstime,
					ord(substr($data, $delta_time->getLen() + 3, 1)),
					ord(substr($data, $delta_time->getLen() + 4, 1)),
					ord(substr($data, $delta_time->getLen() + 5, 1)),
					ord(substr($data, $delta_time->getLen() + 6, 1)),
					ord(substr($data, $delta_time->getLen() + 7, 1))));
				$msglen = $delta_time->getLen() + 8;
				break;
			case MIDIEvent::META_TIME_SIG:
				$trackobj->addEvent(new TimeSignature(
					$abstime,
					ord(substr($data, $delta_time->getLen() + 3, 1)),
					ord(substr($data, $delta_time->getLen() + 4, 1)),
					ord(substr($data, $delta_time->getLen() + 5, 1)),
					ord(substr($data, $delta_time->getLen() + 6, 1))));
				$msglen = $delta_time->getLen() + 7;
				break;
			case MIDIEvent::META_KEY_SIG:
				$trackobj->addEvent(new KeySignature(
					$abstime,
					unpack('csharps', substr($data, $delta_time->getLen() + 3, 1))['sharps'],
					ord(substr($data, $delta_time->getLen() + 4, 1))));
				$msglen = $delta_time->getLen() + 5;
				break;
			case MIDIEvent::META_SEQ_SPEC:
				$datalen->readVLQ(substr($data, $delta_time->getLen() + 2, 4));
				$trackobj->addEvent(new SequencerSpecific(
					$abstime,
					substr($data, $delta_time->getLen() + 2 + $datalen->getLen(), $datalen->getValue())));
				$msglen = $delta_time->getLen() + 2 + $datalen->getLen() + $datalen->getValue();
				break;
			default:
				echo 'UNKNOWN TYPE<br>UNKNOWN TYPE<br>UNKNOWN TYPE<br><br>';
				$msglen = strlen($data);
				break;
		};

		return $msglen;
	}

	/**
	 * Display the specified file
	 * Simple hex dump + a print_r of the file object
	 *
	 * @return void
	 */
	public function displayMIDIFile()
	{
		// Without this header, flushes don't work...
		header( 'Content-type: text/html; charset=utf-8' );
		echo '<font size="3" face="Courier New">';

		// For the moment, just do a hex dump...
		$offset = 0;
		$length = strlen($this->file_raw_contents);

		echo ' ----- -------- ---- 00-1-2-3-4-5-6-7-8--10-1-2-3-4-5-6-7-8--20-1-2-3-4-5-6-7-8--30-1-2-3-4-5-6-7-8--<br>';
		while ($offset < $length)
		{
			$line = mb_strcut($this->file_raw_contents, $offset, 40);
			echo ' Byte: ' . sprintf('%08d', $offset + 1) . ' Hex: ' . str_pad(bin2hex($line), 80, '-') . ' Disp: ' . htmlentities(preg_replace('/[\x00-\x1F\x7F-\xFF]/', '?', $line)) . '<br>';
			$offset += strlen($line);
		}
		echo '<br><br>';

		// And dump the object with print_r as well...
		echo '<pre>';
		echo 'Header:<br>';
		print_r($this->header);
		echo 'Tracks:<br>';
		print_r($this->tracks);
		echo '</pre>';

		@ob_flush();
		@flush();

		return;
	}

	/**
	 * Write to the specified file
	 *
	 * @param $file string
	 * @return void
	 */
	public function writeMIDIFile($file)
	{
		// Set this object's name to match file...
		$this->file_name = $file;
		// Recalc raw_file_contents...
		$this->pack();
		// Write it...
		file_put_contents($this->file_name, $this->file_raw_contents);
	}

	/**
	 * Add an empty track to the file.
	 * If no track name provided, make one up.
	 * Add the TrackName event.
	 * Return the track #.
	 *
	 * @param string $name
	 * @return int
	 */
	public function addTrack($name = '')
	{
		if (empty($name))
			$name = 'Track ' . $this->header->getNtrks();

		$tracknum = $this->header->getNtrks();
		$this->tracks[$tracknum] = new MIDITrk();
		$this->header->incNtrks();

		$this->tracks[$tracknum]->addEvent(new TrackName(0, $name));

		return $this->tracks[$tracknum];
	}

	/**
	 * Set BPM
	 * Translate from bpm to MIDI tempo...
	 * Assumes midi format 1 file, & tempo event is in track 0
	 *
	 * @param float $bpm
	 * @return void
	 */
	public function setBPM($bpm = 120)
	{
		// Sanity check...
		if (($bpm < 1) || ($bpm > 3000))
			$bpm = 120;

		// Convert to tempo (microseconds per beat)
		$tempo = (int) (60000000/$bpm);

		$event = $this->tracks[0]->getEvent(MIDIEvent::META_TEMPO);
		$event->setTempo($tempo);

		return;
	}

	/**
	 * Set Time Signature
	 * Numerator & denominator.  Denominator must be a power of 2...
	 * Assumes midi format 1 file, & time signature event is in track 0
	 *
	 * @param int $top
	 * @param int $bottom
	 * @return void
	 */
	public function setTimeSignature($top = 4, $bottom = 4)
	{
		// Sanity checks...
		if (($top < 1) || ($top > 32))
			$top = 4;
		$bb = (int) 32/$bottom;
		$bottom = (int) log($bottom, 2);
		if (($bottom < 1) || ($bottom > 32))
			$bottom = 2;

		$event = $this->tracks[0]->getEvent(MIDIEvent::META_TIME_SIG);
		$event->setTimeSignature($top, $bottom, 24, $bb);

		return;
	}

	/**
	 * Set Key Signature
	 * Use MIDI format: range -7 to 7 sharps, where a neg sharp is a flat
	 * 0 = major key, 1 = minor in 2nd byte
	 * Assumes midi format 1 file, & key signature event is in track 0
	 *
	 * @param int $sharps
	 * @param int $minor
	 * @return void
	 */
	public function setKeySignature($sharps = 0, $minor = 0)
	{
		// Sanity checks...
		if (($sharps < -7) || ($sharps > 7))
			$sharps = 0;
		if (($minor != 0) && ($minor != 1))
			$minor = 0;

		$event = $this->tracks[0]->getEvent(MIDIEvent::META_KEY_SIG);
		$event->setKeySignature($sharps, $minor);

		return;
	}

	/**
	 * Convert Measure, Beat, Tick to absolute time, in ticks
	 * Assumes Time Signature set
	 * Assumes midi format 1 file, time signature event is in track 0
	 *
	 * @param int $m
	 * @param int $b
	 * @param int $t
	 * @return int
	 */
	public function mbt2at($m = 1, $b = 1, $t = 0)
	{
		static $top = null;
		static $bottom = null;
		static $ticks = null;

		if ($top == null)
		{
			$event = $this->tracks[0]->getEvent(MIDIEvent::META_TIME_SIG);
			$timesig = $event->getTimeSignature();
			$top = $timesig['top'];
			$bottom = pow(2,$timesig['bottom']);
		}

		if ($ticks == null)
		{
			$ticks = $this->header->getTicks();
		}

		// Sanity checks...
		if ($m < 1) $m = 1;
		if ($b < 1) $b = 1;

		return (($m - 1) * $top * $ticks * 4 / $bottom) + (($b - 1) * $ticks * 4 / $bottom) + $t;
	}

	/**
	 * Convert Beats to ticks...  Fractions allowed...
	 * Useful for note durations
	 *
	 * @param float $b
	 * @return int
	 */
	public function b2dur($b = 1)
	{
		static $top = null;
		static $bottom = null;
		static $ticks = null;

		if ($top == null)
		{
			$event = $this->tracks[0]->getEvent(MIDIEvent::META_TIME_SIG);
			$timesig = $event->getTimeSignature();
			$top = $timesig['top'];
			$bottom = pow(2,$timesig['bottom']);
		}

		if ($ticks == null)
		{
			$ticks = $this->header->getTicks();
		}

		return (int) ($b * $ticks * 4 / $bottom);
	}

}
?>