<?php
/**
 * sjrbMIDI example
 * Song Generator!
 */

// First things first, these scripts need to know source & language folders.
$sourcedir = 'd:\wamp64\www\sjrbMIDI\sources\\';
$langdir = 'd:\wamp64\www\sjrbMIDI\languages\\';

spl_autoload_register(function ($class_name) use ($sourcedir) {
		include $sourcedir . '\class-' . $class_name . '.php';
	}
);
Errors::setLanguageDir($langdir);
Errors::setVerbosity(true);

// Place output same place as this script...
$out_name = __DIR__ . '\\example-9.mid';

// Some variables to drive everything...
$time_sig_top = 4;
$time_sig_bottom = 4; 
$bpm = 90;

// Now do it...
$myFile = new MIDIFile();
$myFile->setBPM($bpm);
$myFile->setTimeSignature($time_sig_top, $time_sig_bottom);
$key = new Key(Key::A_NOTE, Key::MINOR_SCALE);
$myFile->setKeySignature($key->getMIDIsf(), $key->getMIDImm());

// Setup Rhythms
$rhythm = new Rhythm(4, 4, 4, 4);
$rhythm->randomize(16);

// Define sequences for lead instrument, chords, & drums

// Multiple sequences can be requested.  Each request has the following params:
// - Key
// - Rhythm
// - Downbeat = usually 1 for rock, 2 for jazz & R&B
// - Pattern duration in measures
// - Pattern destinations
// - Pattern note pct, 0 - 1.0
// - Pattern triplet pct, 0 - 1.0
// - Phrases - if null, phrases will be generated
// - Root seq - if null, auto-generated; array of roots of chords, if generated
// - Root oct - of chords, if generated
// - Num phrases - how many phrases to choose from, if generated
// - Max notes per phrase - how many notes per phrase max, if generated
// - Max inc dec - max amount to inc or dec by
// - Min dnote - range check, in dnote form (base 7)
// - Max dnote - range check, in dnote form (base 7)
// - Phrase note pct, 0 - 1.0, if generated
// - Phrase triplet pct, 0 - 1.0, if generated
$tonal_sequences = array(
	new TonalSequence($key, $rhythm, 2, 4, array(1), 1, 0, null, null, 5, 2, 7, 3, 30, 100, 1, 0),
	new TonalSequence($key, $rhythm, 2, 4, array(5), 1, 0, null, null, 5, 2, 7, 3, 30, 100, 1, 0),
	new TonalSequence($key, $rhythm, 2, 4, array(9), 1, 0, null, null, 5, 2, 7, 3, 30, 100, 1, 0),
	new TonalSequence($key, $rhythm, 2, 4, array(13), 1, 0, null, null, 5, 2, 7, 3, 30, 100, 1, 0),
);
$root_seq = $tonal_sequences[0]->getRootSeq();

// Multiple instruments can be used:
// - Channel - for track to be generated
// - Track Name - for track to be generated
// - Sub_inst tone => array of ONE INSTANCE (tone is a dummy value) of
//	 - Min hits per rhythmic beat, always an int >= 0 
//	 - Max hits per rhythmic beat, always an int >= -1; -1 means "use the # of pulses"
//	 - Velocity factor, 0 - 1.0; scales back returned velocity this much, allowing you to blend drums better
$tonal_instruments = array(
	new Instrument(2, 'Lead', array(-1 => array(0, 4, 1))),
);

// Multiple chords sequences can be requested.  Each request has the following params:
// - Key
// - Rhythm
// - Downbeat = usually 1 for rock, 2 for jazz & R&B
// - Pattern duration in measures
// - Pattern destinations
// - Pattern note pct, 0 - 1.0
// - Pattern triplet pct, 0 - 1.0
// - Chords - if null, chords will be generated
// - Root seq - if null, auto-generated; array of roots of chords, if generated
// - Root oct - of chords, if generated
// - Max_notes_per_chord
// - Max inc dec - max amount to inc or dec by
// - Min dnote - range check, in dnote form (base 7)
// - Max dnote - range check, in dnote form (base 7)
// - Inversion pct, 0 - 1.0, if generated
// - Chord note pct, 0 - 1.0, if generated
// - Chord triplet pct, 0 - 1.0, if generated
$chord_sequences = array(
	new ChordSequence($key, $rhythm, 2, 4, array(1), .8, 0, null, $root_seq, 5, 4, 3, 30, 100, .3, 1, .1),
	new ChordSequence($key, $rhythm, 2, 4, array(5), .8, 0, null, $root_seq, 5, 4, 3, 30, 100, .3, 1, .1),
	new ChordSequence($key, $rhythm, 2, 4, array(9), .8, 0, null, $root_seq, 5, 4, 3, 30, 100, .3, 1, .1),
	new ChordSequence($key, $rhythm, 2, 4, array(13), .8, 0, null, $root_seq, 5, 4, 3, 30, 100, .3, 1, .1),
);
// Chord instrument...
$chord_instruments = array(
	new Instrument(3, 'Chords', array(-1 => array(1, 2, .7))),
);

// Multiple sequences can be requested.  Each request has the following params:
// - Rhythm
// - Start beat = usually 1 for rock, 2 for jazz & R&B
// - Pattern duration in measures
// - Pattern destinations
// - Pattern note pct, 0 - 1.0
// - Pattern triplet pct, 0 - 1.0
$drum_sequences = array(
	new DrumSequence($rhythm, 1, 1, array(1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15), .4, .1),
	new DrumSequence($rhythm, 1, 1, array(4, 8, 12, 16), .2, .2),
);

// Multiple instruments can be used:
// - Channel
// - Track Name
// - Sub_inst tone => array of
//	 - Min hits per rhythmic beat, always an int >= 0 
//	 - Max hits per rhythmic beat, always an int >= -1; -1 means "use the # of pulses"
//	 - Velocity factor, 0 - 1.0; scales back returned velocity this much, allowing you to blend drums better
$drum_instruments = array(
	new Instrument(9, 'Drums', array(
		MIDIEvent::DRUM_AC_BASS => array(1, 1, 1),
		MIDIEvent::DRUM_AC_SNARE => array(1, 1, .8),
		MIDIEvent::DRUM_LOW_MID_TOM => array(0, 2, .6),
		MIDIEvent::DRUM_HI_MID_TOM => array(0, 2, .6),
		MIDIEvent::DRUM_OPEN_HH => array(0, 4, .7),
		MIDIEvent::DRUM_CRASH => array(0, 1, .7),
	))
);

// Song portion...
$song = new TonalGenerator($myFile, $tonal_sequences, $tonal_instruments, );
$song->generate();

// Chords portion...
$chords = new ChordGenerator($myFile, $chord_sequences, $chord_instruments, );
$chords->generate();

// Pass along the sequences & instruments & invoke the drum generator
$drums = new DrumGenerator($myFile, $drum_sequences, $drum_instruments);
$drums->generate();

// Write & dump the file if you wanna
$myFile->writeMIDIFile($out_name);
//$myFile->displayMIDIFile();

return;

?>