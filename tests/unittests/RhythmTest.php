<?php

use PHPUnit\Framework\TestCase;

class RhythmTest extends TestCase {

	/*
	 * Testing the Rhythm functions
	 */

	public function testRhythm(){

		for ($i = 1; $i <= 16; $i++)
		{
			$myRhythm = new Rhythm($i, $i, $i, $i);
			$this->assertEquals(4, count($myRhythm->getRhythm()), 'Rhythm pattern test 1 failed');
			$this->assertEquals(4, $myRhythm->getBeats(), 'Rhythm pattern test 2 failed');
			$this->assertEquals(($i - 1) * 4, $myRhythm->getRests(), 'Rhythm pattern test 3 failed');
			$this->assertEquals($i * 4, $myRhythm->getPulses(), 'Rhythm pattern test 4 failed');
		}
	}

	public function testWalkSDRhythm(){

		$myRhythm = new Rhythm(2, 2, 2, 2, 2, 2, 2, 2);
		$myRhythm->setStartDur(1000, 8000);
		$count = 0;
		foreach($myRhythm->walkSD AS $start => $dur)
		{
			$this->assertEquals(1000 + ($count * 1000), $start, 'Rhythm walksd start test failed');
			$this->assertEquals(1000, $dur, 'Rhythm walksd dur test failed');
			$count++;
		}
	}

	public function testWalkAllRhythm(){

		$myRhythm = new Rhythm(2, 2, 2, 2, 2, 2, 2, 2);
		$myRhythm->setStartDur(1000, 8000);
		$count = 0;
		foreach($myRhythm->walkAll AS $start => $details)
		{
			$this->assertEquals(1000 + ($count * 1000), $start, 'Rhythm walkall start test failed');
			$this->assertEquals(1000, $details['dur'], 'Rhythm walkall dur test failed');
			$this->assertEquals($count, $details['beat'], 'Rhythm walkall beat test failed');
			$this->assertEquals(2, $details['pulses'], 'Rhythm walkall pulses test failed');
			$count++;
		}
	}
}
?>