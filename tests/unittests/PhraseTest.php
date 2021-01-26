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

		$this->assertIsIterable($myPhrase->walkSD);
		$this->assertIsIterable($myPhrase->walkAll);
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
			$this->assertEquals($note_start, 1000 + (960 * 2 * $count));
			$this->assertEquals($note_dur, 960 * 2);
			$count++;
		}

		// Next test, setStartDur()...  Start at 0 & original lengths...
		$myPhrase->setStartDur(0, 960 * 24);

		// Now verify it...
		$count = 0;
		foreach($myPhrase->walkSD AS $note_start => $note_dur)
		{
			$this->assertEquals($note_start, 960 * $count);
			$this->assertEquals($note_dur, 960);
			$count++;
		}
	}
}
?>