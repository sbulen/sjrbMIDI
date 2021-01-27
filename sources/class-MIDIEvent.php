<?php
/**
 *	MIDI class hierarchy for events.  ALL the events are here...
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

abstract class MIDIEvent
{
	/**
	 * Bunch of constants
	 */
	const NOTE_OFF = 0x8;
	const NOTE_ON = 0x9;
	const POLY_AFTER_TOUCH = 0xA;
	const CONTROL_CHANGE = 0xB;
	const PROGRAM_CHANGE = 0xC;
	const AFTER_TOUCH = 0xD;
	const PITCH_WHEEL = 0xE;
	const META_SYSEX = 0xF;			// All meta & sysex events start with F...

	const SYSEX = 0xF0;				// Sysex start
	const SYSEX_ESCAPE = 0xF7;		// Sysex anything start
	const META_EVENT = 0xFF;		// Start of meta event

	const META_SEQ_NO = 0x00;		// Sequence number
	const META_TEXT = 0x01;			// Text event
	const META_COPYRIGHT = 0x02;	// Copyright
	const META_TRACK_NAME = 0x03;	// Track name
	const META_INST_NAME = 0x04;	// Instrument name
	const META_LYRIC = 0x05;		// Lyrics
	const META_MARKER = 0x06;		// Descriptive text, e.g., "intro"
	const META_CUE = 0x07;			// Cue
	const META_CHAN_PFX = 0x20;		// Channel prefix - sets default channel until overridden...
	const META_TRACK_END = 0x2F;	// End of track
	const META_TEMPO = 0x51;		// Tempo
	const META_SMPTE = 0x54;		// SMPTE info
	const META_TIME_SIG = 0x58;		// Time Signature
	const META_KEY_SIG = 0x59;		// Key Signature
	const META_SEQ_SPEC = 0x7F;		// Sequencer specific content

	const DRUM_AC_BASS = 35;		// Drum note #s
	const DRUM_BASS_1 = 36;			// Drum note #s
	const DRUM_SIDE_STICK = 37;		// Drum note #s
	const DRUM_AC_SNARE = 38;		// Drum note #s
	const DRUM_HAND_CLAP = 39;		// Drum note #s
	const DRUM_ELEC_SNARE = 40;		// Drum note #s
	const DRUM_LOW_FL_TOM = 41;		// Drum note #s
	const DRUM_CLOSED_HH = 42;		// Drum note #s
	const DRUM_HIGH_FL_TOM = 43;	// Drum note #s
	const DRUM_PEDAL_HH = 44;		// Drum note #s
	const DRUM_LOW_TOM = 45;		// Drum note #s
	const DRUM_OPEN_HH = 46;		// Drum note #s
	const DRUM_LOW_MID_TOM = 47;	// Drum note #s
	const DRUM_HI_MID_TOM = 48;		// Drum note #s
	const DRUM_CRASH = 49;			// Drum note #s
	const DRUM_HIGH_TOM = 50;		// Drum note #s
	const DRUM_RIDE = 51;			// Drum note #s
	const DRUM_CHINESE_CYM = 52;	// Drum note #s
	const DRUM_RIDE_BELL = 53;		// Drum note #s
	const DRUM_TAMBOURINE = 54;		// Drum note #s
	const DRUM_SPLASY_CYM = 55;		// Drum note #s
	const DRUM_COWBELL = 56;		// Drum note #s
	const DRUM_CRASH_2 = 57;		// Drum note #s
	const DRUM_VIBRA_SLAP = 58;		// Drum note #s
	const DRUM_RIDE_2 = 59;			// Drum note #s
	const DRUM_HI_BONGO = 60;		// Drum note #s
	const DRUM_LOW_BONGO = 61;		// Drum note #s
	const DRUM_MUTE_HI_CONGA = 62;	// Drum note #s
	const DRUM_OPEN_HI_CONGA = 63;	// Drum note #s
	const DRUM_LOW_CONGA = 64;		// Drum note #s
	const DRUM_HI_TIMBALE = 65;		// Drum note #s
	const DRUM_LOW_TIMBALE = 66;	// Drum note #s
	const DRUM_HIGH_AGOGO = 67;		// Drum note #s
	const DRUM_LOW_AGOGO = 68;		// Drum note #s
	const DRUM_CABASA = 69;			// Drum note #s
	const DRUM_MARACAS = 70;		// Drum note #s
	const DRUM_SHORT_WHISTLE = 71;	// Drum note #s
	const DRUM_LONG_WHISTLE = 72;	// Drum note #s
	const DRUM_SHORT_GUIRO = 73;	// Drum note #s
	const DRUM_LONG_GUIRO = 74;		// Drum note #s
	const DRUM_CLAVES = 75;			// Drum note #s
	const DRUM_HI_WOOD_BLK = 76;	// Drum note #s
	const DRUM_LOW_WOOD_BLK = 77;	// Drum note #s
	const DRUM_MUTE_CUICA = 78;		// Drum note #s
	const DRUM_OPEN_CUICA = 79;		// Drum note #s
	const DRUM_MUTE_TRIANGLE = 80;	// Drum note #s
	const DRUM_OPEN_TRIANGLE = 81;	// Drum note #s

	/**
	 * All events have a time & a type
	 * All events are read from disk/written to disk with their delta_time, per spec (ticks since last event).
	 * All events in memory are in absolute times for ease of manipulation (ticks from start of track).
	 */
	protected $delta_time = 0;
	protected $abs_time = 0;
	protected $type = NULL;

	/**
	 * Simple range check; default to note/velocity value range
	 * Very simple - if it's outside the range, trim the value to be within the range
	 *
	 * @param int $value
	 * @param int $min
	 * @param int $max
	 * @return int
	 */
	public static function rangeCheck($value, $min = 0, $max = 127) {
		if ($value < $min)
			$value = $min;
		elseif ($value > $max)
			$value = $max;

		return $value;
	}

	/**
	 * Return abs_time
	 *
	 * @return int
	 */
	public function getAt()
	{
		return $this->abs_time;
	}

	/**
	 * Set delta_time
	 *
	 * @param int $dt
	 * @return void
	 */
	public function setDt($dt = 0)
	{
		$dt = $this->rangeCheck($dt, 0, 0xFFFFFFF);
		$this->delta_time = $dt;
		return;
	}

	/**
	 * Return the event type
	 *
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Pack = to convert to binary for writing
	 * Every event & file element must have a pack function
	 *
	 * @return string
	 */
	abstract function pack();

}

abstract class MIDIChannelEvent extends MIDIEvent
{
	/**
	 * properties unique to this layer
	 */
	protected $channel = 0;
}

abstract class MIDISysexEvent extends MIDIEvent
{
	/**
	 * properties unique to this layer
	 */
	protected $length = 0;
}

abstract class MIDIMetaEvent extends MIDIEvent
{
	/**
	 * properties unique to this layer
	 */
	protected $subtype = 0;
	protected $length = 0;

	/**
	 * Return the meta event subtype
	 *
	 * @return int
	 */
	public function getSubType()
	{
		return $this->subtype;
	}
}

	/**
	 * THE REAL, ACTUAL, USABLE, EVENTS FOLLOW...
	 */

class NoteOff extends MIDIChannelEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::NOTE_OFF;
	protected $note = 0;
	protected $velocity = 0;

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param int $chan - channel
	 * @param int $note - note
	 * @param int $veloicity - velocity
	 * @return void
	 */
	function __construct($at = 0, $chan = 0, $note = 0, $velocity = 0)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->channel = $this->rangeCheck($chan, 0, 0xF);
		$this->note = $this->rangeCheck($note);
		$this->velocity = $this->rangeCheck($velocity);
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr(($this->type << 4) | $this->channel) . chr($this->note) . chr($this->velocity);
	}
}

class NoteOn extends MIDIChannelEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::NOTE_ON;
	protected $note = 0;
	protected $velocity = 0;

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param int $chan - channel
	 * @param int $note - note
	 * @param int $veloicity - velocity
	 * @return void
	 */
	function __construct($at = 0, $chan = 0, $note = 0, $velocity = 0)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->channel = $this->rangeCheck($chan, 0, 0xF);
		$this->note = $this->rangeCheck($note);
		$this->velocity = $this->rangeCheck($velocity);
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr(($this->type << 4) | $this->channel) . chr($this->note) . chr($this->velocity);
	}
}

class PolyAfterTouch extends MIDIChannelEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::POLY_AFTER_TOUCH;
	protected $note = 0;
	protected $value = 0;

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param int $chan - channel
	 * @param int $note - note
	 * @param int $value - aftertouch value
	 * @return void
	 */
	function __construct($at = 0, $chan = 0, $note = 0, $value = 0)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->channel = $this->rangeCheck($chan, 0, 0xF);
		$this->note = $this->rangeCheck($note);
		$this->value = $this->rangeCheck($value);
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr(($this->type << 4) | $this->channel) . chr($this->note) . chr($this->value);
	}
}

class ControlChange extends MIDIChannelEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::CONTROL_CHANGE;
	protected $controller = 0;
	protected $value = 0;

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param int $chan - channel
	 * @param int $controller - controller #
	 * @param int $value - value
	 * @return void
	 */
	function __construct($at = 0, $chan = 0, $controller = 0, $value = 0)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->channel = $this->rangeCheck($chan, 0, 0xF);
		$this->controller = $this->rangeCheck($controller);
		$this->value = $this->rangeCheck($value);
	}

	/**
	 * getValue...
	 *
	 * @return int
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr(($this->type << 4) | $this->channel) . chr($this->controller) . chr($this->value);
	}
}

class ProgramChange extends MIDIChannelEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::PROGRAM_CHANGE;
	protected $program = 0;

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param int $chan - channel
	 * @param int $program - new program #
	 * @return void
	 */
	function __construct($at = 0, $chan = 0, $program = 0)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->channel = $this->rangeCheck($chan, 0, 0xF);
		$this->program = $this->rangeCheck($program);
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr(($this->type << 4) | $this->channel) . chr($this->program);
	}
}

class AfterTouch extends MIDIChannelEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::AFTER_TOUCH;
	protected $value = 0;

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param int $chan - channel
	 * @param int $value - aftertouch value
	 * @return void
	 */
	function __construct($at = 0, $chan = 0, $value = 0)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->channel = $this->rangeCheck($chan, 0, 0xF);
		$this->value = $this->rangeCheck($value);
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr(($this->type << 4) | $this->channel) . chr($this->value);
	}
}

class PitchWheel extends MIDIChannelEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::PITCH_WHEEL;
	protected $value = 0;

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param int $chan - channel
	 * @param int $value - value
	 * @return void
	 */
	function __construct($at = 0, $chan = 0, $value = 0)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->channel = $this->rangeCheck($chan, 0, 0xF);
		$this->value = (int) $this->rangeCheck($value, -0x2000, 0x1FFF);
	}

	/**
	 * getValue...
	 *
	 * @return int
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		// Big-endian, & offset by 8192... 
		$temp = $this->value + 8192;
		return $dt->setValue($this->delta_time) . chr(($this->type << 4) | $this->channel) . chr($temp & 0x7F) . chr(($temp >> 7) & 0x7F);
	}
}

class Sysex extends MIDISysexEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::SYSEX;
	protected $data = '';

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param string $data - binary string of data
	 * @return void
	 */
	function __construct($at = 0, $data = '')
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->length = strlen($data);
		$this->data = $data;
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . $dt->setValue($this->length) . $this->data;
	}
}

class SysexEscape extends MIDISysexEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::SYSEX_ESCAPE;
	protected $data = '';

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param string $data - raw binary data
	 * @return void
	 */
	function __construct($at = 0, $data = '')
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->length = strlen($data);
		$this->data = $data;
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . $dt->setValue($this->length) . $this->data;
	}
}

class SequenceNo extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_SEQ_NO;
	protected $length = 2;	// fixed at 2
	protected $seq_no = 0;

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param int $seq_no - sequence number
	 * @return void
	 */
	function __construct($at = 0, $seq_no = 0)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->seq_no = $this->rangeCheck($seq_no, 0, 0xFFFF);
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . pack('n', $this->seq_no);
	}
}

class Text extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_TEXT;
	protected $length = 0;
	protected $text = '';

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param string $text - text
	 * @return void
	 */
	function __construct($at = 0, $text = '')
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->length = strlen($text);
		$this->text = $text;
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . $this->text;
	}
}

class Copyright extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_COPYRIGHT;
	protected $length = 0;
	protected $copyright = '';

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param string $copyright - copyright
	 * @return void
	 */
	function __construct($at = 0, $copyright = '')
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->length = strlen($copyright);
		$this->copyright = $copyright;
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . $this->copyright;
	}
}

class TrackName extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_TRACK_NAME;
	protected $length = 0;
	protected $track_name = '';

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param string $track_name - track name
	 * @return void
	 */
	function __construct($at = 0, $track_name = '')
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->length = strlen($track_name);
		$this->track_name = $track_name;
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . $this->track_name;
	}
}

class InstName extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_INST_NAME;
	protected $length = 0;
	protected $inst_name = '';

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param string $inst_name - instrument name
	 * @return void
	 */
	function __construct($at = 0, $inst_name = '')
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->length = strlen($inst_name);
		$this->inst_name = $inst_name;
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . $this->inst_name;
	}
}

class Lyric extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_LYRIC;
	protected $length = 0;
	protected $lyric = '';

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param string $lyric - lyric
	 * @return void
	 */
	function __construct($at = 0, $lyric = '')
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->length = strlen($lyric);
		$this->lyric = $lyric;
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . $this->lyric;
	}
}

class Marker extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_MARKER;
	protected $length = 0;
	protected $marker = '';

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param string $marker - marker
	 * @return void
	 */
	function __construct($at = 0, $marker = '')
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->length = strlen($marker);
		$this->marker = $marker;
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . $this->marker;
	}
}

class Cue extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_CUE;
	protected $length = 0;
	protected $cue = '';

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param string $cue - cue
	 * @return void
	 */
	function __construct($at = 0, $cue = '')
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->length = strlen($cue);
		$this->cue = $cue;
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . $this->cue;
	}
}

class ChannelPrefix extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_CHAN_PFX;
	protected $length = 1;	//Fixed at 1
	protected $channel = 0;

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param int $channel - channel
	 * @return void
	 */
	function __construct($at = 0, $channel = 0)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->channel = $this->rangeCheck($channel, 0, 0xF);
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . chr($this->channel);
	}
}

class TrackEnd extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_TRACK_END;
	protected $length = 0;	//Fixed at 0

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @return void
	 */
	function __construct($at = 0)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length);
	}
}

class Tempo extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_TEMPO;
	protected $length = 3;	//Fixed at 3
	protected $tempo = 500000;	//Default to 120 bpm

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param int $tempo - tempo, default to 120 bpm
	 * @return void
	 */
	function __construct($at = 0, $tempo = 500000)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->tempo = $this->rangeCheck($tempo, 0, 0xFFFFFF);
	}

	/**
	 * Set the tempo
	 *
	 * @param int $tempo defaults to 120 bpm
	 * @return void
	 */
	public function setTempo($tempo = 500000)
	{
		$this->tempo = $this->rangeCheck($tempo, 0, 0xFFFFFF);
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . chr(($this->tempo >> 16) & 0xFF) . chr(($this->tempo >> 8) & 0xFF) . chr($this->tempo & 0xFF);
	}
}

class SmpteOffset extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_SMPTE;
	protected $length = 5;	//Fixed at 5
	protected $hh = 0;
	protected $mm = 0;
	protected $se = 0;
	protected $fr = 0;
	protected $ff = 0;

	/**
	 * Constructor
	 *
	 * Note I'm not familiar with the specifics here, this needs work...
	 *
	 * @param int $at - absolute time
	 * @param int $hh - smpte hours
	 * @param int $mm - smpte minutes
	 * @param int $se - smpte seconds
	 * @param int $fr - smpte frames
	 * @param int $ff - fractional frames
	 * @return void
	 */
	function __construct($at = 0, $hh = 0, $mm = 0, $se = 0, $fr = 0, $ff = 0)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->hh = $this->rangeCheck($hh, 0, 0xFF);
		$this->mm = $this->rangeCheck($mm, 0, 0xFF);
		$this->se = $this->rangeCheck($se, 0, 0xFF);
		$this->fr = $this->rangeCheck($fr, 0, 0xFF);
		$this->ff = $this->rangeCheck($ff, 0, 0xFF);
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . chr($this->hh) . chr($this->mm) . chr($this->se) . chr($this->fr) . chr($this->ff);
	}
}

class TimeSignature extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_TIME_SIG;
	protected $length = 4;	//Fixed at 4
	protected $top = 4;
	protected $bottom = 2;	// Careful, powers of 2, 2=4
	protected $cc = 24;		// Standard 24 clocks per beat
	protected $bb = 8;		// Standard 8 1/32 notes per 24 clocks

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param int $top - time sig top
	 * @param int $bottom - time sig bottom, powers of 2 (2=4...)
	 * @param int $cc - clocks per beat
	 * @param int $bb - 1/32 notes per 24 clocks
	 * @return void
	 */
	function __construct($at = 0, $top = 4, $bottom = 2, $cc = 24, $bb = 8)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->top = $this->rangeCheck($top);
		$this->bottom = $this->rangeCheck($bottom);
		$this->cc = $this->rangeCheck($cc);
		$this->bb = $this->rangeCheck($bb);
	}

	/**
	 * Set time signature
	 *
	 * @param int $top
	 * @param int $bottom
	 * @param int $cc MIDI clocks per metronome tick (24 standard)
	 * @param int $bb Number of 1/32 notes per 24 MICI clock ticks (8 standard)
	 * @return void
	 */
	public function setTimeSignature($top = 4, $bottom = 2, $cc = 24, $bb = 8)
	{
		$this->top = $this->rangeCheck($top);
		$this->bottom = $this->rangeCheck($bottom);
		$this->cc = $this->rangeCheck($cc);
		$this->bb = $this->rangeCheck($bb);
	}

	/**
	 * Get time signature
	 *
	 * @return int[]
	 */
	public function getTimeSignature()
	{
		return array('top' => $this->top, 'bottom' => $this->bottom);
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . chr($this->top) . chr($this->bottom) . chr($this->cc) . chr($this->bb);
	}
}

class KeySignature extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_KEY_SIG;
	protected $length = 2;	//Fixed at 2
	protected $sharps = 0;
	protected $minor = 0;

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param int $sharps
	 * @param int $minor
	 * @return void
	 */
	function __construct($at = 0, $sharps = 0, $minor = 0)
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->sharps = $this->rangeCheck($sharps, -7, 7);
		$this->minor = $this->rangeCheck($minor, 0, 1);
	}

	/**
	 * Set key signature
	 *
	 * @param int $sharps
	 * @param int $minor
	 * @return void
	 */
	public function setKeySignature($sharps = 0, $minor = 0)
	{
		$this->sharps = $this->rangeCheck($sharps, -7, 7);
		$this->minor = $this->rangeCheck($minor, 0, 1);
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . pack('c', $this->sharps) . chr($this->minor);
	}
}

class SequencerSpecific extends MIDIMetaEvent
{
	/**
	 * Properties unique to this event
	 */
	protected $type = MIDIEvent::META_EVENT;
	protected $subtype = MIDIEvent::META_SEQ_SPEC;
	protected $length = 0;
	protected $bytes = '';

	/**
	 * Constructor
	 *
	 * @param int $at - absolute time
	 * @param string $bytes - sequencer specific raw binary data
	 * @return void
	 */
	function __construct($at = 0, $bytes = '')
	{
		$this->abs_time = $this->rangeCheck($at, 0, 0xFFFFFFF);
		$this->delta_time = 0;	//Needs to be set later...
		$this->length = strlen($bytes);
		$this->bytes = $bytes;
	}

	/**
	 * Everybody has to have a pack...
	 *
	 * @return string
	 */
	public function pack()
	{
		$dt = new VLQ();
		return $dt->setValue($this->delta_time) . chr($this->type) . chr($this->subtype) . $dt->setValue($this->length) . $this->bytes;
	}
}

?>