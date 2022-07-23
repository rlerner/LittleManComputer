// Output the larger number R.Lerner 2022/07/23
		INP
		STA a
		INP
		STA b
		SUB a
		BRP OUTPUTB
		LDA a
		OUT
		HLT
OUTPUTB	LDA b
		OUT
		HLT
		a DAT
		b DAT
