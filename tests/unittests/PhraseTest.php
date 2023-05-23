<?php

use PHPUnit\Framework\TestCase;

class PhraseTest extends TestCase {

	/*
	 * Testing the Phrase functions
	 */

	public function testIsIterable(){

		$myKey = new Key(Key::C_NOTE, Key::MAJOR_SCALE);

		$startnote = $myKey->getD(5, 0);

		$myPhrase = new Phrase(
			array(
				new Note(0, 0 * 960, $myKey->dAdd($startnote, 0), 100, 960),
				new Note(0, 1 * 960, $myKey->dAdd($startnote, 4), 100, 960),
				new Note(0, 2 * 960, $myKey->dAdd($startnote, 8), 100, 960),
				new Note(0, 3 * 960, $myKey->dAdd($startnote, 12), 100, 960),

			),
			$myKey,
		);

		$this->assertIsIterable($myPhrase->walkSD, 'Phrase iterable test 1 failed');
		$this->assertIsIterable($myPhrase->walkAll, 'Phrase iterable test 1 failed');
	}

	public function testTransformationSetStartDur(){

		$myKey = new Key(Key::C_NOTE, Key::MAJOR_SCALE);

		$note_data = array(
			array('dn' => 50, 'sf' => -1),
			array('dn' => 50, 'sf' => 0),
			array('dn' => 50, 'sf' => 1),
			array('dn' => 51, 'sf' => -1),
			array('dn' => 51, 'sf' => 0),
			array('dn' => 51, 'sf' => 1),
			array('dn' => 52, 'sf' => -1),
			array('dn' => 52, 'sf' => 0),
			array('dn' => 52, 'sf' => 1),
			array('dn' => 53, 'sf' => -1),
			array('dn' => 53, 'sf' => 0),
			array('dn' => 53, 'sf' => 1),
			array('dn' => 54, 'sf' => -1),
			array('dn' => 54, 'sf' => 0),
			array('dn' => 54, 'sf' => 1),
			array('dn' => 55, 'sf' => -1),
			array('dn' => 55, 'sf' => 0),
			array('dn' => 55, 'sf' => 1),
			array('dn' => 56, 'sf' => -1),
			array('dn' => 56, 'sf' => 0),
			array('dn' => 56, 'sf' => 1),
			array('dn' => 60, 'sf' => -1),
			array('dn' => 60, 'sf' => 0),
			array('dn' => 60, 'sf' => 1),
		);

		$note_arr = array();
		foreach($note_data AS $ix => $note)
		{
			$note_arr[] = new Note(0, $ix * 960, array('dn' => $note['dn'], 'sf' => $note['sf']), 100, 960);
		}

		$myPhrase = new Phrase($note_arr, $myKey);

		// First test, setStartDur()...  Start at 1000 & double all lengths...
		$myPhrase->setStartDur(1000, 960 * 48);

		// Now verify it...
		$count = 0;
		foreach($myPhrase->walkSD AS $note_start => $note_dur)
		{
			$this->assertEquals($note_start, 1000 + (960 * 2 * $count), 'Phrase sd start test failed');
			$this->assertEquals($note_dur, 960 * 2, 'Phrase sd dur test failed');
			$count++;
		}

		// Next test, setStartDur()...  Start at 0 & original lengths...
		$myPhrase->setStartDur(0, 960 * 24);

		// Now verify it...
		$count = 0;
		foreach($myPhrase->walkSD AS $note_start => $note_dur)
		{
			$this->assertEquals($note_start, 960 * $count, 'Phrase sd start test 2 failed');
			$this->assertEquals($note_dur, 960, 'Phrase sd dur test 2 failed');
			$count++;
		}
	}

	public function testTransformationRetrograde(){

		$myKey = new Key(Key::C_NOTE, Key::MAJOR_SCALE);

		$note_data = array(
			array('dn' => 50, 'sf' => -1),
			array('dn' => 50, 'sf' => 0),
			array('dn' => 50, 'sf' => 1),
			array('dn' => 51, 'sf' => -1),
			array('dn' => 51, 'sf' => 0),
			array('dn' => 51, 'sf' => 1),
			array('dn' => 52, 'sf' => -1),
			array('dn' => 52, 'sf' => 0),
			array('dn' => 52, 'sf' => 1),
			array('dn' => 53, 'sf' => -1),
			array('dn' => 53, 'sf' => 0),
			array('dn' => 53, 'sf' => 1),
			array('dn' => 54, 'sf' => -1),
			array('dn' => 54, 'sf' => 0),
			array('dn' => 54, 'sf' => 1),
			array('dn' => 55, 'sf' => -1),
			array('dn' => 55, 'sf' => 0),
			array('dn' => 55, 'sf' => 1),
			array('dn' => 56, 'sf' => -1),
			array('dn' => 56, 'sf' => 0),
			array('dn' => 56, 'sf' => 1),
			array('dn' => 60, 'sf' => -1),
			array('dn' => 60, 'sf' => 0),
			array('dn' => 60, 'sf' => 1),
		);

		$note_arr = array();
		foreach($note_data AS $ix => $note)
		{
			$note_arr[] = new Note(0, $ix * 960, array('dn' => $note['dn'], 'sf' => $note['sf']), 100, 960);
		}

		$myPhrase = new Phrase($note_arr, $myKey);

		// Test retrograde...
		$myPhrase->retrograde();

		// Now verify it...
		$count = 0;
		foreach($myPhrase->walkAll AS $note_start => $note)
		{
			$this->assertEquals($note_start, 960 * (23 - $count), 'Phrase retro test 1 failed');
			$this->assertEquals($note->getDNote()['dn'], $note_data[$count]['dn'], 'Phrase retro test 2 failed');
			$this->assertEquals($note->getDNote()['sf'], $note_data[$count]['sf'], 'Phrase retro test 3 failed');
			$this->assertEquals($note->getDur(), 960, 'Phrase retro test 4 failed');
			$count++;
		}
	}

	public function testTransformationInversion(){

		$myKey = new Key(Key::C_NOTE, Key::MAJOR_SCALE);

		$note_data = array(
			array('dn' => 50, 'sf' => -1),
			array('dn' => 50, 'sf' => 0),
			array('dn' => 50, 'sf' => 1),
			array('dn' => 51, 'sf' => -1),
			array('dn' => 51, 'sf' => 0),
			array('dn' => 51, 'sf' => 1),
			array('dn' => 52, 'sf' => -1),
			array('dn' => 52, 'sf' => 0),
			array('dn' => 52, 'sf' => 1),
			array('dn' => 53, 'sf' => -1),
			array('dn' => 53, 'sf' => 0),
			array('dn' => 53, 'sf' => 1),
			array('dn' => 54, 'sf' => -1),
			array('dn' => 54, 'sf' => 0),
			array('dn' => 54, 'sf' => 1),
			array('dn' => 55, 'sf' => -1),
			array('dn' => 55, 'sf' => 0),
			array('dn' => 55, 'sf' => 1),
			array('dn' => 56, 'sf' => -1),
			array('dn' => 56, 'sf' => 0),
			array('dn' => 56, 'sf' => 1),
			array('dn' => 60, 'sf' => -1),
			array('dn' => 60, 'sf' => 0),
			array('dn' => 60, 'sf' => 1),
		);

		$note_arr = array();
		foreach($note_data AS $ix => $note)
		{
			$note_arr[] = new Note(0, $ix * 960, array('dn' => $note['dn'], 'sf' => $note['sf']), 100, 960);
		}

		$myPhrase = new Phrase($note_arr, $myKey);

		// Test retrograde...
		$myPhrase->invert();

		// Now verify it...
		$count = 0;
		foreach($myPhrase->walkAll AS $note_start => $note)
		{
			$this->assertEquals($note_start, 960 * $count, 'Phrase invert test 1 failed');
			$this->assertEquals($note->getDNote()['dn'], $note_data[23 - $count]['dn'], 'Phrase invert test 2 failed');
			$this->assertEquals($note->getDNote()['sf'], $note_data[23 - $count]['sf'], 'Phrase invert test 3 failed');
			$this->assertEquals($note->getDur(), 960, 'Phrase invert test 4 failed');
			$count++;
		}
	}

	public function testTransformationRotation(){

		$myKey = new Key(Key::C_NOTE, Key::MAJOR_SCALE);

		$note_data = array(
			array('dn' => 50, 'sf' => -1),
			array('dn' => 50, 'sf' => 0),
			array('dn' => 50, 'sf' => 1),
			array('dn' => 51, 'sf' => -1),
			array('dn' => 51, 'sf' => 0),
			array('dn' => 51, 'sf' => 1),
			array('dn' => 52, 'sf' => -1),
			array('dn' => 52, 'sf' => 0),
			array('dn' => 52, 'sf' => 1),
			array('dn' => 53, 'sf' => -1),
			array('dn' => 53, 'sf' => 0),
			array('dn' => 53, 'sf' => 1),
			array('dn' => 54, 'sf' => -1),
			array('dn' => 54, 'sf' => 0),
			array('dn' => 54, 'sf' => 1),
			array('dn' => 55, 'sf' => -1),
			array('dn' => 55, 'sf' => 0),
			array('dn' => 55, 'sf' => 1),
			array('dn' => 56, 'sf' => -1),
			array('dn' => 56, 'sf' => 0),
			array('dn' => 56, 'sf' => 1),
			array('dn' => 60, 'sf' => -1),
			array('dn' => 60, 'sf' => 0),
			array('dn' => 60, 'sf' => 1),
		);

		$note_arr = array();
		foreach($note_data AS $ix => $note)
		{
			$note_arr[] = new Note(0, $ix * 960, array('dn' => $note['dn'], 'sf' => $note['sf']), 100, 960);
		}

		$myPhrase = new Phrase($note_arr, $myKey);

		// Test retrograde...  Rotate 11
		$myPhrase->rotate(11);

		// Now verify it...
		$count = 0;
		foreach($myPhrase->walkAll AS $note_start => $note)
		{
			$this->assertEquals($note_start, 960 * (($count + 11) % 24), 'Phrase rotate test 1 failed');
			$this->assertEquals($note->getDNote()['dn'], $note_data[$count]['dn'], 'Phrase rotate test 2 failed');
			$this->assertEquals($note->getDNote()['sf'], $note_data[$count]['sf'], 'Phrase rotate test 3 failed');
			$this->assertEquals($note->getDur(), 960, 'Phrase rotate test 4 failed');
			$count++;
		}

		// 13 more should match the original...
		$myPhrase->rotate(13);

		// Now verify it...
		$count = 0;
		foreach($myPhrase->walkAll AS $note_start => $note)
		{
			$this->assertEquals($note_start, 960 * $count, 'Phrase rotate test 5 failed');
			$this->assertEquals($note->getDNote()['dn'], $note_data[$count]['dn'], 'Phrase rotate test 6 failed');
			$this->assertEquals($note->getDNote()['sf'], $note_data[$count]['sf'], 'Phrase rotate test 7 failed');
			$this->assertEquals($note->getDur(), 960, 'Phrase rotate test 8 failed');
			$count++;
		}
	}
	public function testTransformationTransposition(){
		
		$myKey = new Key(Key::C_NOTE, Key::MAJOR_SCALE);

		$note_data = array(
			array('dn' => 50, 'sf' => -1, 'addfifth' => 54, 'addninth' => 65),
			array('dn' => 50, 'sf' => 0, 'addfifth' => 54, 'addninth' => 65),
			array('dn' => 50, 'sf' => 1, 'addfifth' => 54, 'addninth' => 65),
			array('dn' => 51, 'sf' => -1, 'addfifth' => 55, 'addninth' => 66),
			array('dn' => 51, 'sf' => 0, 'addfifth' => 55, 'addninth' => 66),
			array('dn' => 51, 'sf' => 1, 'addfifth' => 55, 'addninth' => 66),
			array('dn' => 52, 'sf' => -1, 'addfifth' => 56, 'addninth' => 100),
			array('dn' => 52, 'sf' => 0, 'addfifth' => 56, 'addninth' => 100),
			array('dn' => 52, 'sf' => 1, 'addfifth' => 56, 'addninth' => 100),
			array('dn' => 53, 'sf' => -1, 'addfifth' => 60, 'addninth' => 101),
			array('dn' => 53, 'sf' => 0, 'addfifth' => 60, 'addninth' => 101),
			array('dn' => 53, 'sf' => 1, 'addfifth' => 60, 'addninth' => 101),
			array('dn' => 54, 'sf' => -1, 'addfifth' => 61, 'addninth' => 102),
			array('dn' => 54, 'sf' => 0, 'addfifth' => 61, 'addninth' => 102),
			array('dn' => 54, 'sf' => 1, 'addfifth' => 61, 'addninth' => 102),
			array('dn' => 55, 'sf' => -1, 'addfifth' => 62, 'addninth' => 103),
			array('dn' => 55, 'sf' => 0, 'addfifth' => 62, 'addninth' => 103),
			array('dn' => 55, 'sf' => 1, 'addfifth' => 62, 'addninth' => 103),
			array('dn' => 56, 'sf' => -1, 'addfifth' => 63, 'addninth' => 104),
			array('dn' => 56, 'sf' => 0, 'addfifth' => 63, 'addninth' => 104),
			array('dn' => 56, 'sf' => 1, 'addfifth' => 63, 'addninth' => 104),
			array('dn' => 60, 'sf' => -1, 'addfifth' => 64, 'addninth' => 105),
			array('dn' => 60, 'sf' => 0, 'addfifth' => 64, 'addninth' => 105),
			array('dn' => 60, 'sf' => 1, 'addfifth' => 64, 'addninth' => 105),
		);

		$note_arr = array();
		foreach($note_data AS $ix => $note)
		{
			$note_arr[] = new Note(0, $ix * 960, array('dn' => $note['dn'], 'sf' => $note['sf']), 100, 960);
		}

		$myPhrase = new Phrase($note_arr, $myKey);

		// Test retrograde...  Transpose a fifth...
		$myPhrase->transpose(Key::FIFTH);

		// Now verify it...
		$count = 0;
		foreach($myPhrase->walkAll AS $note_start => $note)
		{
			$this->assertEquals($note_start, 960 * $count, 'Phrase transposition test 1 failed');
			$this->assertEquals($note->getDNote()['dn'], $note_data[$count]['addfifth'], 'Phrase transposition test 2 failed');
			$this->assertEquals($note->getDNote()['sf'], $note_data[$count]['sf'], 'Phrase transposition test 3 failed');
			$this->assertEquals($note->getDur(), 960, 'Phrase transposition test 4 failed');
			$count++;
		}

		// Test retrograde...  Transpose a ninth...
		$myPhrase->transpose(Key::NINTH);

		// Now verify it...
		$count = 0;
		foreach($myPhrase->walkAll AS $note_start => $note)
		{
			$this->assertEquals($note_start, 960 * $count, 'Phrase transposition test 5 failed');
			$this->assertEquals($note->getDNote()['dn'], $note_data[$count]['addninth'], 'Phrase transposition test 6 failed');
			$this->assertEquals($note->getDNote()['sf'], $note_data[$count]['sf'], 'Phrase transposition test 7 failed');
			$this->assertEquals($note->getDur(), 960, 'Phrase transposition test 8 failed');
			$count++;
		}
	}
}
?>