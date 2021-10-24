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
// *** START of user configs ***
// *** START of user configs ***
// *** START of user configs ***
$time_sig_top = 4;
$time_sig_bottom = 4;
$bpm = 90;
$out_name = 'example-8.mid';
$chan = 9;

// *** END of user configs ***
// *** END of user configs ***
// *** END of user configs ***

// Now do it...
$myFile = new MIDIFile();
$myFile->setBPM($bpm);
$myFile->setTimeSignature($time_sig_top, $time_sig_bottom);
$new_track = $myFile->addTrack('Drums');

// Invoke drum generator
// You define sequences & instruments, examples below.
// These match the defaults, you can modify & experiment different settings...
// Just drop the .mid file in your DAW to play.

// Try looping each 4 measure sequence!

// Multiple sequences can be requested, defaults here.  Each request has the following params:
// - Euclid = true/false; if false a standard rhythm
// - Pattern = a 1-measure pattern; for Euclid, beats/rests; for Rhythm, array of lengths; random if null
// - Start beat = usually 1 for rock, 2 for jazz & R&B
// - Pattern measures
// - Pattern note pct, 0 - 1.0
// - Pattern triplet pct, 0 - 1.0
// - Fill measures
// - Fill note pct, 0 - 1.0
// - Fill triplet pct, 0 - 1.0
$sequences = array(
	array('euclid' => true, 'pattern' => array(7, 9), 'start_beat' => 1,
		'patt_meas' => 3, 'patt_note_pct' => .8, 'patt_trip_pct' => .1,
		'fill_meas' => 1, 'fill_note_pct' => 1, 'fill_trip_pct' => 0),
	array('euclid' => false, 'pattern' => array(4, 4, 4, 4), 'start_beat' => 2,
		'patt_meas' => 3, 'patt_note_pct' => 1, 'patt_trip_pct' => .0,
		'fill_meas' => 1, 'fill_note_pct' => 1, 'fill_trip_pct' => .8),
	array('euclid' => true, 'pattern' => null, 'start_beat' => 1,
		'patt_meas' => 3, 'patt_note_pct' => .8, 'patt_trip_pct' => .1,
		'fill_meas' => 1, 'fill_note_pct' => 1, 'fill_trip_pct' => 0),
	array('euclid' => false, 'pattern' => null, 'start_beat' => 2,
		'patt_meas' => 3, 'patt_note_pct' => 1, 'patt_trip_pct' => .0,
		'fill_meas' => 1, 'fill_note_pct' => 1, 'fill_trip_pct' => .8),
);

// Multiple instruments can be used, defaults here:
// - Instrument
// - Min hits per rhythmic beat, always an int >= 0 
// - Max hits per rhythmic beat, always an int >= -1; -1 means "use the # of pulses"
// - Velocity factor, 0 - 1.0; scales back returned velocity this much, allowing you to blend drums better
$instruments = array(
	MIDIEvent::DRUM_AC_BASS => array('min_hits' => 0, 'max_hits' => 1, 'vel_factor' => 1),
	MIDIEvent::DRUM_AC_SNARE => array('min_hits' => 0, 'max_hits' => 1, 'vel_factor' => 1),
	MIDIEvent::DRUM_LOW_MID_TOM => array('min_hits' => 0, 'max_hits' => -1, 'vel_factor' => .8),
	MIDIEvent::DRUM_CLOSED_HH => array('min_hits' => 0, 'max_hits' => -1, 'vel_factor' => .6),
	MIDIEvent::DRUM_RIDE => array('min_hits' => 0, 'max_hits' => -1, 'vel_factor' => .7),
);

// Pass along the sequences & instruments & invoke the drum generator
$drums = new DrumGenerator($myFile);
$drums->setSequences($sequences);
$drums->setInstruments($instruments);
$new_track->addEvents($drums->getNotes());

// Wrap up
$new_track->addTrackEnd();
$myFile->writeMIDIFile($out_name);
$myFile->displayMIDIFile();

return;

?>