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
			$this->assertEquals($row['val'], $result->getValue());

			// check len
			$this->assertEquals($row['len'], $result->getLen());

			// check str
			$this->assertEquals($row['str'], $result->getStr());

			// Again, via reading
			$val = $result->readVLQ($row['str']);
			$this->assertEquals($row['val'], $val);
			$this->assertEquals($row['val'], $result->getValue());
			$this->assertEquals($row['len'], $result->getLen());
			$this->assertEquals($row['str'], $result->getStr());

			// Again, via setting value
			$str = $result->setValue($row['val']);
			$this->assertEquals($row['str'], $str);
			$this->assertEquals($row['val'], $result->getValue());
			$this->assertEquals($row['len'], $result->getLen());
			$this->assertEquals($row['str'], $result->getStr());

		}
	}
}
?>