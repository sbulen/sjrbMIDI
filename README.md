# sjrbMIDI
sjrbMIDI is an open-source (GNU-GPL3 license) collection of classes to simplify creating and reading MIDI files via PHP.

The idea was to make it as easy as possible to write a bunch of MIDI events to a physical MIDI file.  The file will be readable by any DAW.

PHP is such an easy language to work with, I wanted to be able to experiment with algorithmic composition in PHP.  I am sharing this here in case anyone else finds it helpful and fun.


## Capabilities
1. Can read and write existing MIDI files, and dump their contents.
2. Can create new MIDI files, with full control over all aspects of the MIDI file.
3. A class exists for a musical Key, to enable diatonic math & writing logic that stays "in key".
4. A class exists for Rhythm, to make it easier to manipulate note durations to follow a rhythm.
5. An extension to the Rhythm class exists for Euclid, for generation of Euclidean rhythms.
6. Classes exist for creation & manipulation of event series, for both Pitch Wheel events and Control Change events.  Several wave forms exist, from sin to sawtooth to square, etc., for these curves.


## To use
Create your file in .php and execute it from your browser.  The MIDI file that is created can then be loaded into your DAW.

Use one of the examples as an easy starter.

Documentation may be found here: https://github.com/sbulen/sjrbMIDI/blob/main/sjrbMIDI.pdf

## Limitations
1. This does not honor any of the real-time MIDI messages at this time.  Not sure PHP would be the platform to do that...
2. This software is geared towards generating files in MIDI file format 1.
3. I have not tested the SMPTE functions, only straight-up MIDI.


## Further reading
 - The MIDI spec: https://www.midi.org/specifications-old/item/table-1-summary-of-midi-message
 - Helpful, readable MIDI spec info: https://www.personal.kent.edu/~sbirch/Music_Production/MP-II/MIDI/midi_file_format.htm
 - Euclidean rhythm paper: http://cgm.cs.mcgill.ca/~godfried/publications/banff.pdf

