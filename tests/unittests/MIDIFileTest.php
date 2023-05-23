<?php

use PHPUnit\Framework\TestCase;

class MIDIFileTest extends TestCase {

	/*
	 * Testing the MIDIFile functions
	 */

	public function testSetGetTempo(){

		$myFile = new MIDIFile();
		for ($i = 1; $i <= 500; $i++)
		{
			// bpm 5 to 500, confirm up to 1/100 precision
			$bpm = rand(500, 50000)/100;
			$myFile->setBPM($bpm);
			// 
			$returned_bpm = round($myFile->getBPM() * 100)/100;
			$this->assertEquals($bpm, $returned_bpm, 'MIDIFile BPM test failed');
		}
	}

	public function testSetGetTimeSignature(){
		$data = array(
			array('top' => 2, 'bottom' => 2),
			array('top' => 3, 'bottom' => 4),
			array('top' => 4, 'bottom' => 4),
			array('top' => 3, 'bottom' => 8),
			array('top' => 5, 'bottom' => 8),
			array('top' => 6, 'bottom' => 8),
			array('top' => 7, 'bottom' => 8),
			array('top' => 11, 'bottom' => 8),
			array('top' => 11, 'bottom' => 16),
		);

		$myFile = new MIDIFile();
		foreach ($data AS $time_sig)
		{
			$myFile->setTimeSignature($time_sig['top'], $time_sig['bottom']);
			$returned_sig = $myFile->getTimeSignature();
			$this->assertEquals($time_sig['top'], $returned_sig['top'], 'MIDIFile Time Sig test failed');
			$this->assertEquals($time_sig['bottom'], $returned_sig['bottom'], 'MIDIFile Time Sig test failed');
		}
	}

	public function testSetGetKeySignature(){

		$data = array(
			// Cb treated as B - same tones
			array('sharps' => 5, 'minor' => 0, 'root' => Key::Cb_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => 5, 'minor' => 1, 'root' => Key::Ab_NOTE, 'modal' => KEY::MINOR_SCALE),
			array('sharps' => -6, 'minor' => 0, 'root' => Key::Gb_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => -6, 'minor' => 1, 'root' => Key::Eb_NOTE, 'modal' => KEY::MINOR_SCALE),
			array('sharps' => -5, 'minor' => 0, 'root' => Key::Db_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => -5, 'minor' => 1, 'root' => Key::Bb_NOTE, 'modal' => KEY::MINOR_SCALE),
			array('sharps' => -4, 'minor' => 0, 'root' => Key::Ab_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => -4, 'minor' => 1, 'root' => Key::F_NOTE, 'modal' => KEY::MINOR_SCALE),
			array('sharps' => -3, 'minor' => 0, 'root' => Key::Eb_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => -3, 'minor' => 1, 'root' => Key::C_NOTE, 'modal' => KEY::MINOR_SCALE),
			array('sharps' => -2, 'minor' => 0, 'root' => Key::Bb_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => -2, 'minor' => 1, 'root' => Key::G_NOTE, 'modal' => KEY::MINOR_SCALE),
			array('sharps' => -1, 'minor' => 0, 'root' => Key::F_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => -1, 'minor' => 1, 'root' => Key::D_NOTE, 'modal' => KEY::MINOR_SCALE),
			array('sharps' => 0, 'minor' => 0, 'root' => Key::C_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => 0, 'minor' => 1, 'root' => Key::A_NOTE, 'modal' => KEY::MINOR_SCALE),
			array('sharps' => 1, 'minor' => 0, 'root' => Key::G_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => 1, 'minor' => 1, 'root' => Key::E_NOTE, 'modal' => KEY::MINOR_SCALE),
			array('sharps' => 2, 'minor' => 0, 'root' => Key::D_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => 2, 'minor' => 1, 'root' => Key::B_NOTE, 'modal' => KEY::MINOR_SCALE),
			array('sharps' => 3, 'minor' => 0, 'root' => Key::A_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => 3, 'minor' => 1, 'root' => Key::Fs_NOTE, 'modal' => KEY::MINOR_SCALE),
			array('sharps' => 4, 'minor' => 0, 'root' => Key::E_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => 4, 'minor' => 1, 'root' => Key::Cs_NOTE, 'modal' => KEY::MINOR_SCALE),
			array('sharps' => 5, 'minor' => 0, 'root' => Key::B_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => 5, 'minor' => 1, 'root' => Key::Gs_NOTE, 'modal' => KEY::MINOR_SCALE),
			// F# treated as Gb - same tones
			array('sharps' => -6, 'minor' => 0, 'root' => Key::Fs_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => -6, 'minor' => 1, 'root' => Key::Ds_NOTE, 'modal' => KEY::MINOR_SCALE),
			// C# treated as Db - same tones
			array('sharps' => -5, 'minor' => 0, 'root' => Key::Cs_NOTE, 'modal' => KEY::MAJOR_SCALE),
			array('sharps' => -5, 'minor' => 1, 'root' => Key::As_NOTE, 'modal' => KEY::MINOR_SCALE),
		);

		$myFile = new MIDIFile();
		foreach ($data AS $key_sig)
		{
			$myKey = new Key($key_sig['root'], $key_sig['modal']);
			$myFile->setKeySignature($myKey->getMIDIsf(), $myKey->getMIDImm());
			$returned_sig = $myFile->getKeySignature();
			$this->assertEquals($key_sig['sharps'], $returned_sig['sharps'], 'MIDIFile Key Sig test failed');
			$this->assertEquals($key_sig['minor'], $returned_sig['minor'], 'MIDIFile Key Sig test failed');
		}
	}

	public function testSetGetNotesFromTrack(){
		// This really does a bunch of end to end testing across classes, including Key, Note, etc...
		// Remember - MIDI key signature only reflects major/minor scales, so, this will only work for those,
		// i.e., it won't work for all the other modals.  You lose the modal root xlating to/from MIDI key sigs.
		// Test all major & minor scales...
		for ($root = 0; $root <= 11; $root++)
		{
			for ($mm = 0; $mm <= 1; $mm++)
			{
				$myFile = new MIDIFile();
				// Test all major & minor scales...
				$key = new Key($root, $mm * 5);
				// Sync with file's MIDI key signature
				$myFile->setKeySignature($key->getMIDIsf(), $key->getMIDImm());
				// Add a track
				$solo_track = $myFile->addTrack('Wild solo');

				// Pick random note attributes...
				$chan = rand(0, 15);
				$dur = rand(100, 100000);
				$vel = rand(1, 127);
				$at = 0;

				// Add 200 random notes to track & keep track of 'em...
				$note_arr = array();
				for ($i = 0; $i <= 200; $i++)
				{
					$mnote = rand(0, 127);
					$note_arr[$at] = array('chan' => $chan, 'mnote' => $mnote, 'vel' => $vel, 'dur' => $dur);
					$solo_track->addNote($at, $chan, $mnote, $vel, $dur);
					$at += rand(10, 1000);
				}

				// Reconstruct notes from MIDI events
				$new_notes = $myFile->getNotesFromTrack(1);

				// Compare original array of notes to current array of notes
				foreach($new_notes AS $note)
				{
					$at = $note->getAt();
					$this->assertArrayHasKey($at, $note_arr, 'MIDIFile at not found');
					$this->assertEquals($note_arr[$at]['chan'], $note->getChan(), 'MIDIFile chan test failed');
					$this->assertEquals($note_arr[$at]['mnote'], $key->d2m($note->getDNote()), 'MIDIFile mnote test failed');
					$this->assertEquals($note_arr[$at]['vel'], $note->getVel(), 'MIDIFile vel test failed');
					$this->assertEquals($note_arr[$at]['dur'], $note->getDur(), 'MIDIFile dur test failed');
				}
			}
		}
	}

	public function testFIFOQueueForSameNoteValues(){
		$myFile = new MIDIFile();
		$note_arr = array();
		$solo_track = $myFile->addTrack('Wild solo');
		$solo_track->addNote(1000, 10, 64, 120, 5000); //ends at 6000, third NoteOff
		$solo_track->addNote(2000, 10, 64, 120, 2001); //ends at 4001, first NoteOff
		$solo_track->addNote(3000, 10, 64, 120, 2002); //ends at 5002, second NoteOff

		// Need to write, then read, it so we lose NoteOn NoteOff pairings...
		$myFile->writeMIDIFile('deleteme.mid');
		$myFile2 = new MIDIFile('deleteme.mid');
		@unlink('deleteme.mid');

		$new_notes = $myFile2->getNotesFromTrack();

		foreach ($new_notes AS $note)
		{
			if ($note->getAt() == 1000)
				$this->assertEquals(3001, $note->getDur(), 'MIDIFile FIFO Sig test failed');
			elseif ($note->getAt() == 2000)
				$this->assertEquals(3002, $note->getDur(), 'MIDIFile FIFO Sig test failed');
			elseif ($note->getAt() == 3000)
				$this->assertEquals(3000, $note->getDur(), 'MIDIFile FIFO Sig test failed');
		}
	}
}
?>