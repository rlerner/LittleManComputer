//Accepts one input and counts down to zero, outputting all values
	INP
loop	OUT
	STA count
	SUB one
	STA count
	BRP loop
	HLT

one	DAT 1
count	DAT 