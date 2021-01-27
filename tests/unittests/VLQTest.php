<?php

use PHPUnit\Framework\TestCase;

class VLQTest extends TestCase {

    /*
     * Testing the VLQ functions
     */

    public function testVLQ(){

		//Array of string, value, lengths for test data
		$data = array (
			array('str' => hex2bin("00"), 'val' => 0, 'len' => 1),
			array('str' => hex2bin("7f"), 'val' => 127, 'len' => 1),
			array('str' => hex2bin("8100"), 'val' => 128, 'len' => 2),
			array('str' => hex2bin("C000"), 'val' => 8192, 'len' => 2),
			array('str' => hex2bin("FF7F"), 'val' => 16383, 'len' => 2),
			array('str' => hex2bin("818000"), 'val' => 16384, 'len' => 3),
			array('str' => hex2bin("FFFF7F"), 'val' => 2097151, 'len' => 3),
			array('str' => hex2bin("81808000"), 'val' => 2097152, 'len' => 4),
			array('str' => hex2bin("C0808000"), 'val' => 134217728, 'len' => 4),
			array('str' => hex2bin("FFFFFF7F"), 'val' => 268435455, 'len' => 4),
		);

		foreach ($data AS $row)
		{
			// constructor
			$result = new VLQ($row['str']);

			// check val
			$this->assertEquals($row['val'], $result->getValue(), 'VLQ test 1 failed');

			// check len
			$this->assertEquals($row['len'], $result->getLen(), 'VLQ test 2 failed');

			// check str
			$this->assertEquals($row['str'], $result->getStr(), 'VLQ test 3 failed');

			// Again, via reading
			$val = $result->readVLQ($row['str']);
			$this->assertEquals($row['val'], $val, 'VLQ test 4 failed');
			$this->assertEquals($row['val'], $result->getValue(), 'VLQ test 5 failed');
			$this->assertEquals($row['len'], $result->getLen(), 'VLQ test 6 failed');
			$this->assertEquals($row['str'], $result->getStr(), 'VLQ test 7 failed');

			// Again, via setting value
			$str = $result->setValue($row['val']);
			$this->assertEquals($row['str'], $str, 'VLQ test 8 failed');
			$this->assertEquals($row['val'], $result->getValue(), 'VLQ test 9 failed');
			$this->assertEquals($row['len'], $result->getLen(), 'VLQ test 10 failed');
			$this->assertEquals($row['str'], $result->getStr(), 'VLQ test 11 failed');

		}
	}
}
?>