<?php
/**
 * sjrbMIDI example
 *
 * Demo of surround library functions
 *
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
$out_name = __DIR__ . '\\example-10-surround.mid';

// Some basic song & environment parameters...
$time_sig_top = 4;
$time_sig_bottom = 4; 
$bpm = 120;
$key = new Key(Key::C_NOTE, Key::MAJOR_SCALE);

// Open up a file & track to write to, syncing key & tempo & time signature...
$myFile = new MIDIFile();
$myFile->setBPM($bpm);
$myFile->setTimeSignature($time_sig_top, $time_sig_bottom);
$myFile->setKeySignature($key->getMIDIsf(), $key->getMIDImm());
$note_track = $myFile->addTrack('Surround Test - Notes');
$cc_track = $myFile->addTrack('Surround Test - CCs');

// Initiate surround environment
$surr_env = new SurroundEnv();

// Test 1 - Plunk a note in 25 different locations in a grid
$chan = 0;
$note = 64;
$vel = 120;
$dur = $myFile->b2dur(4);
$meas = 1;
$cart_locs = array(
		array(-10, 10), array(-5, 10), array(0, 10), array(5, 10), array(10, 10),
		array(-10, 5), array(-5, 5), array(0, 5), array(5, 5), array(10, 5),
		array(-10, 0), array(-5, 0), array(0, 0), array(5, 0), array(10, 0),
		array(-10, -5), array(-5, -5), array(0, -5), array(5, -5), array(10, -5),
		array(-10, -10), array(-5, -10), array(0, -10), array(5, -10), array(10, -10),
	);

foreach ($cart_locs AS $loc)
{
	$meas++;
	$start = $myFile->mbt2at($meas);
	// Set the location...
	$cc_track->addEvents($surr_env->moveTo($loc, true, $start, $chan));
	// ...and add the note.
	$note_track->addNote($start, $chan, $note, $vel, $dur);
}

// Test 2 - Plunk random notes randomly in space
$chan = 0;
$vel = 120;
$meas = 28;
$start = $myFile->mbt2at($meas);
$dur = $myFile->b2dur(1);

for ($i = 0; $i < 128; $i++)
{
	$meas++;
	// Set the location...
	$cc_track->addEvents($surr_env->moveTo(array(rand(-10,10),rand(-10,10)), true, $start, $chan));
	// ...and add the note.
	$note_track->addNote($start, $chan, rand(44,74), $vel, $dur);
	$start += $dur;
}

// Test 3 - Draw a line from front left to back right over 5 measures
$chan = 0;
$vel = 120;
$notedur = $myFile->b2dur(20);
$meas = 61;

$start_coords = array(-10, 10);
$end_coords = array(10, -10);
$start = $myFile->mbt2at($meas);
$end = $myFile->mbt2at($meas + 4);
$stepdur = $myFile->b2dur(.25);

// Set the location...
$cc_track->addEvents($surr_env->lineTo($start_coords, $end_coords, true, $start, $end, $stepdur, $chan));
// ...and add the note.
$note_track->addNote($start, $chan, rand(44,74), $vel, $notedur);

// Test 4 - Draw a circle 20x around the room over 10 measures
$chan = 0;
$vel = 120;
$notedur = $myFile->b2dur(40);
$meas = 67;

$start_coords = array(-10, 10);
$cycles = 20.0;
$start = $myFile->mbt2at($meas);
$end = $myFile->mbt2at($meas + 10);
$stepdur = $myFile->b2dur(.0625);

// Set the location...
$cc_track->addEvents($surr_env->circleTo($start_coords, true, $cycles, $start, $end, $stepdur, $chan));
// ...and add the note.
$note_track->addNote($start, $chan, rand(44,74), $vel, $notedur);

// Test 5 - Draw a circle 20x around the room over 10 measures, that draws increasingly closer
$chan = 0;
$vel = 120;
$notedur = $myFile->b2dur(40);
$meas = 78;

$start_coords = array(-10, 10);
$cycles = -20.0;
$end_radius = 2.0;
$start = $myFile->mbt2at($meas);
$end = $myFile->mbt2at($meas + 10);
$stepdur = $myFile->b2dur(.0625);

// Set the location...
$cc_track->addEvents($surr_env->spiralTo($start_coords, true, $cycles, $end_radius, $start, $end, $stepdur, $chan));
// ...and add the note.
$note_track->addNote($start, $chan, rand(44,74), $vel, $notedur);

// Write & dump the file if you wanna
$myFile->writeMIDIFile($out_name);
//$myFile->displayMIDIFile();

return;

?>