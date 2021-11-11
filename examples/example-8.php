<?php
/**
 * sjrbMIDI example
 * Drum Generator!
 */

spl_autoload_register(function ($class_name) {
		include '..\sources\class-' . $class_name . '.php';
	}
);

// Some variables to drive everything...
$time_sig_top = 4;
$time_sig_bottom = 4;
$bpm = 90;
$out_name = 'example-8.mid';
$chan = 9;
Errors::setVerbosity(false);

// Now do it...
$myFile = new MIDIFile();
$myFile->setBPM($bpm);
$myFile->setTimeSignature($time_sig_top, $time_sig_bottom);

// Setup Rhythm
$rhythm = new Rhythm(4, 4, 4, 4);

// Invoke drum generator
// You define sequences & instruments, examples below.
// These match the defaults, you can modify & experiment different settings...
// Just drop the .mid file in your DAW to play.

// Try looping each 4 measure sequence!

// Multiple sequences can be requested.  Each request has the following params:
// - Rhythm
// - Start beat = usually 1 for rock, 2 for jazz & R&B
// - Pattern duration in measures
// - Pattern destinations
// - Pattern note pct, 0 - 1.0
// - Pattern triplet pct, 0 - 1.0

$sequences = array(
	new DrumSequence($rhythm, 1, 1, array(1, 2, 3), .8, .1),
	new DrumSequence($rhythm, 1, 1, array(4), 1, 0),
	new DrumSequence($rhythm, 2, 1, array(5, 6, 7), 1, 0),
	new DrumSequence($rhythm, 2, 1, array(8), 1, .8),
	new DrumSequence($rhythm, 1, 1, array(9, 10, 11), .8, .1),
	new DrumSequence($rhythm, 1, 1, array(12), 1, 0),
	new DrumSequence($rhythm, 2, 1, array(13, 14, 15), 1, 0),
	new DrumSequence($rhythm, 2, 1, array(16), 1, .8),
);

// Multiple instruments can be used:
// - Channel
// - Track Name
// - Sub_inst tone => array of
//	 - Min hits per rhythmic beat, always an int >= 0 
//	 - Max hits per rhythmic beat, always an int >= -1; -1 means "use the # of pulses"
//	 - Velocity factor, 0 - 1.0; scales back returned velocity this much, allowing you to blend drums better
$instruments = array(
	new DrumInstrument($chan, 'Drums', array(
		MIDIEvent::DRUM_AC_BASS => array(0, 1, 1),
		MIDIEvent::DRUM_AC_SNARE => array(0, 1, 1),
		MIDIEvent::DRUM_LOW_MID_TOM => array(0, -1, .8),
		MIDIEvent::DRUM_CLOSED_HH => array(0, -1, .6),
		MIDIEvent::DRUM_RIDE => array(0, -1, .7),
	))
);

// Pass along the sequences & instruments & invoke the drum generator
$drums = new DrumGenerator($myFile, $sequences, $instruments);
$drums->generate();

// Write & dump the file if you wanna
$myFile->writeMIDIFile($out_name);
//$myFile->displayMIDIFile();

?>