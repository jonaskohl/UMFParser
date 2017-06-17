# UMFParser
A parser of my own UMF file format.

## What is UMF?
UMF stands for **U**niversal **M**anifest **F**ormat. It is a manifest and settings file format with type declaration.  
Sample file:

<pre><code><span class="code-comment">#!%UMF File Format 1.0</span>

<span class="code-comment"># This is a sample file</span>

<span class="code-keyword">string</span>		<span class="code-string">"name"</span>		<span class="code-string">"My project"</span>
<span class="code-keyword">string</span>		<span class="code-string">"developer"</span>	<span class="code-string">"TheDev"</span>
<span class="code-keyword">string</span>		<span class="code-string">"license"</span>	<span class="code-string">"MIT License"</span>
<span class="code-keyword">string</span>		<span class="code-string">"website"</span>	<span class="code-string">"http://example.com/"</span>
<span class="code-keyword">array&lt;number&gt;</span>	<span class="code-string">"version"</span>	(1, 0, 0, 0)
</code></pre>

The whitespace inbetween does not matter.
### All available types

<pre><code><span class="code-comment"># This is a comment</span>
<span class="code-keyword">string</span> <span class="code-string">"NameOfString"</span> <span class="code-string">"Value"</span>
<span class="code-keyword">number</span> <span class="code-string">"NameOfNumber"</span> 4.02
<span class="code-keyword">boolean</span> <span class="code-string">"NameOfBoolean"</span> true
<span class="code-keyword">array&lt;number&gt;</span> <span class="code-string">"NameOfNumberArray"</span> (0, 1, 2)
<span class="code-keyword">array&lt;string&gt;</span> <span class="code-string">"NameOfStringArray"</span> (<span class="code-string">"test1"</span>, <span class="code-string">"test2"</span>, <span class="code-string">"test3"</span>)
<span class="code-keyword">array&lt;boolean&gt;</span> <span class="code-string">"NameOfBoolArray"</span> (true, false, false, true)
</code></pre>

## Parse & build
You can create or read these files using the included PHP library `UMFParse.php`. See the `examples/` directory for usage.
