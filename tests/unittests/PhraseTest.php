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
}
?>