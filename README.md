# LittleManComputer
Little Man Computer Assembler and Emulator / Simulator in PHP


Wanted to emulate a machine, and the LMC instruction set is easy. Wanted to use PHP because I'm an untalented, one trick pony web developer. Gave myself three hours to do it.

I did it.

I also wrote an assembler because I recognized quickly that having nothing relative (line labels, variables) and everything absolute to memory locations is pretty terrible.

## How to use:

Install PHP, probably 7 or newer, I forget what was added in 7, so try at least 5.3. That said, 8 is current.

Run:
php assembler.php FILENAME.asm
- This will output FILENAME.lmc

Then open the lmc.php and set any inputs you want and update the filename to the LMC above. I may make this CLI-based at sometime, but I didn't yet.

php lmc.php

(Output stuff)

/fin


I picked the LMC for a few reasons:
 - Big-Endian & Base 10, for you non nerds, that means memory locations and contents are specified like normal people numbers (0,1,2,3,4,5,6,7,8,9,10), not (0,1,2,3,4,5,6,7,8,9,A) etc.
 - Input and Output are native to the language, so no need to implement interrupts, or handle static memory locations to do that
 - Unlike my Brainfuck Interpreter (yes, that's what the language is called), which only puts out numbers and translates it to ASCII codes, this only outputs numbers, so no character tables are needed. You can implement it though, because here's the code.
 - Extremely spacious memory (compared to BF) of 100 cells for program + data. I know that's not great, but I wanted it to work with existing LMC code instead if inventing a new system based on LMC that supports more.
 - Overflow is simply not allowed (anything <0 or >999). Some simulators online allow it, but I stuck with not allowing it. As I type this I realize I don't think I limited the boundary of negative numbers in RAM. Whoops.



#Limitations
There are limitations of the machine I won't go into here, but I will call out a few for my assembler. These were design decisions that are easy to change if you care to:
 - The symbol table is shared for line labels and variables. This means that you can't call a variable LOL and also label a line LOL or weird things will happen. I'll probably change this later, but I have stuff here on GitHub from a decade ago in private repos that say the same thing.
 - The symbols are case insensitive. It's easy to fix this, but with 100 memory locations, I don't think you'll have too robust of a symbol table anyway.
 - There are trailing BRK (000) opcodes. I think this is due to how I iterate memory locations but I'm not sure. Would for sure want to fix this if any programs are near the 100 cell limit.

