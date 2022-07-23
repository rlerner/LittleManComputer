// Multiply Two Numbers - R.Lerner 2022/07/23 - Reduces cycle count by multiplying large numbers instead of small.

		INP
		STA a
		INP
		STA b
		SUB a
		BRP SWAPVAR // Some branching to figure out which has the higher var, then swaps them out to reduce cycle count
		BRA DOMATH

SWAPVAR	LDA b
		STA swap
		LDA a
		STA b
		LDA swap
		STA a

DOMATH	LDA a
		STA output
		LDA b
		SUB one
		STA b
MULT	BRZ DONE 	// If zero is in accumulator (var B) jump to done and kick it out

		LDA output
		ADD a
		STA output
		LDA b 		// Set accumulator to "b" for the MULT / BRZ compare above
		SUB one
		STA b
		BRA MULT 	// Jump back to multiply loop

DONE	LDA output
		OUT
		HLT
		a DAT
		b DAT
		output DAT
		one DAT 1
		swap DAT
