<?php

use PHPUnit\Framework\TestCase;

class EuclidTest extends TestCase {

	/*
	 * Testing the Euclid functions
	 */

	public function testEuclid(){

		//Array of patterns for test data
		$data = array (
			'1000000000000000',
			'1000000010000000',
			'1000010000100000',
			'1000100010001000',
			'1001001001001000',
			'1001010010010100',
			'1001010100101010',
			'1010101010101010',
			'1011010101101010',
			'1011010110110101',
			'1011011011011011',
			'1011101110111011',
			'1011110111101111',
			'1011111110111111',
			'1111111111111110',
			'1111111111111111',
		);

		for ($i = 1; $i <= 16; $i++)
		{
			$myEuclid = new Euclid($i, 16 - $i);
			$this->assertEquals($data[$i - 1], $myEuclid->getPattern(), 'Euclid pattern test 1 failed');
			$this->assertEquals($i, $myEuclid->getBeats(), 'Euclid pattern test 2 failed');
			$this->assertEquals(16 - $i, $myEuclid->getRests(), 'Euclid pattern test 3 failed');
			$this->assertEquals(16, $myEuclid->getPulses(), 'Euclid pattern test 4 failed');
		}
	}

	public function testWalkSDEuclid(){

		$myEuclid = new Euclid(8, 8);
		$myEuclid->setStartDur(1000, 8000);
		$count = 0;
		foreach($myEuclid->walkSD AS $start => $dur)
		{
			$this->assertEquals(1000 + ($count * 1000), $start, 'Euclid walksd start test failed');
			$this->assertEquals(1000, $dur, 'Euclid walksd dur test failed');
			$count++;
		}
	}

	public function testWalkAllEuclid(){

		$myEuclid = new Euclid(8, 8);
		$myEuclid->setStartDur(1000, 8000);
		$count = 0;
		foreach($myEuclid->walkAll AS $start => $details)
		{
			$this->assertEquals(1000 + ($count * 1000), $start, 'Euclid walkall start test failed');
			$this->assertEquals(1000, $details['dur'], 'Euclid walkall dur test failed');
			$this->assertEquals($count, $details['beat'], 'Euclid walkall beat test failed');
			$this->assertEquals(2, $details['pulses'], 'Euclid walkall pulses test failed');
			$count++;
		}
	}
}
?>