<?php

use PHPUnit\Framework\TestCase;

class CCSeriesTest extends TestCase {

    /*
     * Testing the CCSeries functions
     */

    public function testCCSeries(){

		$cc_data = array(
			0=> array('sine' => 64, 'saw' => 0, 'square' => 127, 'expo' => 0),
			96=> array('sine' => 101, 'saw' => 12, 'square' => 'same', 'expo' => 'same'),
			192=> array('sine' => 124, 'saw' => 25, 'square' => 'same', 'expo' => 'same'),
			288=> array('sine' => 'same', 'saw' => 38, 'square' => 'same', 'expo' => 'same'),
			384=> array('sine' => 101, 'saw' => 50, 'square' => 'same', 'expo' => 'same'),
			480=> array('sine' => 64, 'saw' => 63, 'square' => 'same', 'expo' => 'same'),
			576=> array('sine' => 26, 'saw' => 76, 'square' => 0, 'expo' => 'same'),
			672=> array('sine' => 3, 'saw' => 88, 'square' => 'same', 'expo' => 'same'),
			768=> array('sine' => 'same', 'saw' => 101, 'square' => 'same', 'expo' => 2),
			864=> array('sine' => 26, 'saw' => 114, 'square' => 'same', 'expo' => 22),
		);

		// SINE...
		$cc_series = new CCSeries(0, 13, EVENTSeries::SINE, 1, 0, 0, 100, 96);
		$events = $cc_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($cc_data[$event->getAt()]['sine'], $event->getValue(), 'CCSeries sine test failed');
		}

		// Saw...
		$cc_series = new CCSeries(0, 13, EVENTSeries::SAW, 1, 0, 0, 100, 96);
		$events = $cc_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($cc_data[$event->getAt()]['saw'], $event->getValue(), 'CCSeries saw test failed');
		}

		// Square...
		$cc_series = new CCSeries(0, 13, EVENTSeries::SQUARE, 1, 0, 0, 100, 96);
		$events = $cc_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($cc_data[$event->getAt()]['square'], $event->getValue(), 'CCSeries square test failed');
		}

		// Expo...
		$cc_series = new CCSeries(0, 13, EVENTSeries::EXPO, 1, 0, 0, 100, 96);
		$events = $cc_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($cc_data[$event->getAt()]['expo'], $event->getValue(), 'CCSeries expo test failed');
		}

		// Random...
		$cc_series = new CCSeries(0, 13, EVENTSeries::RANDOM_STEPS, 1, 0, 0, 100, 96);
		$events = $cc_series->genEvents(0, 960);

		// Just get back one #, since frequency is 1
		$this->assertEquals(1, count($events), 'CCSeries random test failed');
		$this->assertTrue(($events[0]->getValue() >=0x00) && ($events[0]->getValue() <=0x7F), 'CCSeries random range test failed');
	}

    public function testCCSeriesRange(){

		// Similar series tests, but with the range reversed...
		$cc_data = array(
			0=> array('sine' => 63, 'saw' => 107, 'square' => 19, 'expo' => 107),
			96=> array('sine' => 37, 'saw' => 99, 'square' => 'same', 'expo' => 'same'),
			192=> array('sine' => 20, 'saw' => 90, 'square' => 'same', 'expo' => 'same'),
			288=> array('sine' => 'same', 'saw' => 81, 'square' => 'same', 'expo' => 'same'),
			384=> array('sine' => 37, 'saw' => 72, 'square' => 'same', 'expo' => 'same'),
			480=> array('sine' => 63, 'saw' => 63, 'square' => 'same', 'expo' => 'same'),
			576=> array('sine' => 89, 'saw' => 54, 'square' => 107, 'expo' => 'same'),
			672=> array('sine' => 105, 'saw' => 45, 'square' => 'same', 'expo' => 'same'),
			768=> array('sine' => 'same', 'saw' => 36, 'square' => 'same', 'expo' => 106),
			864=> array('sine' => 89, 'saw' => 27, 'square' => 'same', 'expo' => 92),
		);

		// SINE...
		$cc_series = new CCSeries(0, 13, EVENTSeries::SINE, 1, 0, 85, 15, 96);
		$events = $cc_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($cc_data[$event->getAt()]['sine'], $event->getValue(), 'CCSeries sine test 2 failed');
		}

		// Saw...
		$cc_series = new CCSeries(0, 13, EVENTSeries::SAW, 1, 0, 85, 15, 96);
		$events = $cc_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($cc_data[$event->getAt()]['saw'], $event->getValue(), 'CCSeries saw test 2 failed');
		}

		// Square...
		$cc_series = new CCSeries(0, 13, EVENTSeries::SQUARE, 1, 0, 85, 15, 96);
		$events = $cc_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($cc_data[$event->getAt()]['square'], $event->getValue(), 'CCSeries square test 2 failed');
		}

		// Expo...
		$cc_series = new CCSeries(0, 13, EVENTSeries::EXPO, 1, 0, 85, 15, 96);
		$events = $cc_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($cc_data[$event->getAt()]['expo'], $event->getValue(), 'CCSeries expo test 2 failed');
		}

		// Random...
		$cc_series = new CCSeries(0, 13, EVENTSeries::RANDOM_STEPS, 1, 0, 85, 15, 96);
		$events = $cc_series->genEvents(0, 960);

		// Just get back one #, since frequency is 1
		$this->assertEquals(1, count($events), 'CCSeries random test failed');
		$this->assertTrue(($events[0]->getValue() >= 19) && ($events[0]->getValue() <= 107), 'CCSeries random range test 2 failed');
	}

    public function testCCSeriesRangeOffset(){

		// Similar series tests, but with a range & offset...
		$cc_data = array(
			0=> array('sine' => 114, 'saw' => 76, 'square' => 114, 'expo' => 63),
			96=> array('sine' => 109, 'saw' => 81, 'square' => 'same', 'expo' => 'same'),
			192=> array('sine' => 96, 'saw' => 86, 'square' => 'same', 'expo' => 'same'),
			288=> array('sine' => 81, 'saw' => 91, 'square' => 63, 'expo' => 'same'),
			384=> array('sine' => 68, 'saw' => 96, 'square' => 'same', 'expo' => 'same'),
			480=> array('sine' => 63, 'saw' => 101, 'square' => 'same', 'expo' => 'same'),
			576=> array('sine' => 68, 'saw' => 106, 'square' => 'same', 'expo' => 66),
			672=> array('sine' => 81, 'saw' => 111, 'square' => 'same', 'expo' => 92),
			768=> array('sine' => 96, 'saw' => 66, 'square' => 114, 'expo' => 63),
			864=> array('sine' => 109, 'saw' => 71, 'square' => 'same', 'expo' => 'same'),
		);

		// SINE...
		$cc_series = new CCSeries(0, 13, EVENTSeries::SINE, 1, 90, 50, 90, 96);
		$events = $cc_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($cc_data[$event->getAt()]['sine'], $event->getValue(), 'CCSeries sine test 3 failed');
		}

		// Saw...
		$cc_series = new CCSeries(0, 13, EVENTSeries::SAW, 1, 90, 50, 90,96);
		$events = $cc_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($cc_data[$event->getAt()]['saw'], $event->getValue(), 'CCSeries saw test 3 failed');
		}

		// Square...
		$cc_series = new CCSeries(0, 13, EVENTSeries::SQUARE, 1, 90, 50, 90, 96);
		$events = $cc_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($cc_data[$event->getAt()]['square'], $event->getValue(), 'CCSeries square test 3 failed');
		}

		// Expo...
		$cc_series = new CCSeries(0, 13, EVENTSeries::EXPO, 1, 90, 50, 90, 96);
		$events = $cc_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($cc_data[$event->getAt()]['expo'], $event->getValue(), 'CCSeries expo test 3 failed');
		}

		// Random...
		// Note that random is extremely sensitive to how many ticks apart things are, so increase resolution for this test...
		$cc_series = new CCSeries(0, 13, EVENTSeries::RANDOM_STEPS, 1, 90, 50, 90, 12);
		$events = $cc_series->genEvents(0, 960);

		// Just get back one #, since frequency is 1
		$this->assertEquals(1, count($events), 'CCSeries random test failed');
		$this->assertTrue(($events[0]->getValue() >= 63) && ($events[0]->getValue() <= 114), 'CCSeries random range test 3 failed');
	}
}
?>