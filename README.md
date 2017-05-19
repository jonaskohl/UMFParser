# UMFParser
A parser of my own UMF file format.

## What is UMF?
UMF stands for __U__niversal __M__anifest __F__ormat. It is a manifest and settings file format with type declaration.  
Sample file:

    #!%UMF File Format 1.0

    # This is a sample file

    string		"name"		"My project"
    string 		"developer"	"TheDev"
    string		"license"	"MIT License"
    string 		"website"	"http://example.com/"
    array<number>	"version"	(1, 0, 0, 0)

The whitespace inbetween does not matter.
### All available types
    string "NameOfString" "Value"
    number "NameOfNumber" 1.5
    boolean "NameOfBoolean" true
    array<number> "NameOfNumberArray" (0, 1, 2)
    array<string> "NameOfStringArray" ("test1", "test2", "test3")
    array<boolean> "NameOfBoolArray" (true, false, false, true)