<?php
/**
 * sjrbMIDI example
 * Experiment with phrases
 */

spl_autoload_register(function ($class_name) {
		include '..\sources\class-' . $class_name . '.php';
	}
);

$out_name = 'example-7.mid';
Errors::setVerbosity(false);

$myFile = new MIDIFile();
$myFile->setBPM(103);
$track1 = $myFile->addTrack('Testing...');

// Set key signature for use by note/chord processing
$key = new Key(Key::C_NOTE, Key::MAJOR_SCALE);

$currnote = $key->getD(5, 0);
$sf = 0;

// Sync with file's MIDI key signature
$myFile->setKeySignature($key->getMIDIsf(), $key->getMIDImm());

$notes = array();
$eighth = $myFile->b2dur(1)/4;
$count = 0;
for ($n = 0; $n < 8; $n++)
{
	$notes[] = new Note(0, $count * $eighth, array('dn' => $currnote['dn'], 'sf' => $sf), 100, $eighth);
	$count++;
	$currnote = $key->dAdd($currnote, rand(-2, 2));
	$sf = rand(-1, 1);
}

$phrase = new Phrase($notes, $key);

// Starting phrase...
$i = 1;
$phrase->setStartDur($myFile->mbt2at($i), $eighth * 24);
$track1->addEvents($phrase->getNotes());

// Retrograde...
$i = 3;
$phrase->setStartDur($myFile->mbt2at($i), $eighth * 24);
$phrase->retrograde();
$track1->addEvents($phrase->getNotes());

// Inversion...  Mirror by intervals, e.g., ABGGG becomes GFAAA.
$i = 5;
$phrase->setStartDur($myFile->mbt2at($i), $eighth * 24);
$phrase->invert();
$track1->addEvents($phrase->getNotes());

// Inversion...  By notes used, e.g., E.g., ABGGG becomes GBAAA.
$i = 7;
$phrase->setStartDur($myFile->mbt2at($i), $eighth * 24);
$phrase->invert_set();
$track1->addEvents($phrase->getNotes());

// Transpose - by a diatonic interval
$i = 9;
$phrase->setStartDur($myFile->mbt2at($i), $eighth * 24);
$phrase->transpose(Key::FOURTH);
$track1->addEvents($phrase->getNotes());

// Rotate - pass # of notes to rotate by
$i = 11;
$phrase->setStartDur($myFile->mbt2at($i), $eighth * 24);
$phrase->rotate(2);
$track1->addEvents($phrase->getNotes());

$track1->addTrackEnd();

// Write & dump the file if you wanna
$myFile->writeMIDIFile($out_name);
//$myFile->displayMIDIFile();

return;

?>