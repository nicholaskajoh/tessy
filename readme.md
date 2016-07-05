<h1>Tessy</h1>

<p>There are lots of times when you need to demonstrate stuff to your friends or colleagues, try out something you've been assuming for sometime to see if it actually works or test some code with data to see how it holds up. A full-fledged framework is good but overkill for this kind of stuff; plus you're faced with the hassle of a lot of configuration and a large project structure. But you also don't want to engage in the boring process of writing things from scratch. Tessy helps you out by providing the most basic tools you'll likely need without compelling you to follow a particular coding style, architectural pattern or project structure. In a couple of minutes, you can learn the ropes and take advantage of Tessy in your next project, so lets jump right in!</p>

<h4>What's in the box?</h4>
<p>Tessy offers the following features out of the box, but you can ofcourse add yours as your project demands:</p>
<ul>
	<li>Database connection</li>
	<li>Database query methods</li>
	<li>Data sanitization and validation</li>
	<li>Authentication</li>
	<li>HTML helpers</li>
	<li>Routing</li>
	<li>File uploading</li>
	<li>AJAX request handling</li>
	<li>Extensibility with custom or 3rd-party libraries</li>
</ul>

<h4>Tessy core</h4>
<p>The whole framework is contained in one file, <i>tessy.php</i>, which has a class called <i>Tessy</i>.</p>
<p>File structure</p>
** MyNanoProject
<ul>
<li>tessy.php</li>
<li>index.php</li>
<li>.htaccess</li>
</ul>
<i>Darn simple!</i>

<h4>Setting up Tessy</h4>
<p>Setting up Tessy is easy and straightforward. First, unzip Tessy into the root your project directory.
Then, open the index.php file in any text editor of your choice. There's just one line of code: <code>require "tessy.php"</code>.
To start, instanciate the Tessy class like so: <code>$t = Tessy();</code>.</p>
	
<h4>Moving forward</h4>
<a href="https://github.com/nicholaskajoh/tessy/wiki">Read the Documentation</a> to find out in detail how to use all the functionality Tessy offers.


<h4>Extending Tessy</h4>
<p>Tessy is nano and doesn't ship with all the bells, batteries and whistles you've come to expect in a web framework, but writing custom functionality is a breeze. Open the index.php file and include the <code>libs</code> parameter in your Tessy instance like so: </p>
<p><code>$t = Tessy( [ 'libs' => 'payment' ] ); // this assumes the payment library is contained in a file called payment.php and is located in the root of your project.</code></p>
<p>If your library is not in the root, you can add its path: </p>
<p><code>$t = Tessy( [ 'libs' => [ 'payment', 'pdf' ], 'libs_path' => 'includes' ] ); // pdf is another library in the same location</code></p>

<h4>Contribute</h4>
<p>Found any bugs, got more elegant ways a piece of code could be written or any ideas that can make Tessy better? Add an issue, send me an email or fork the project and send a pull request. <i>A rule though is that Tessy must be contained in one file, one class and have brief and straight-to-the-point methods</i>. Peace!</p>


<h4>License</h4>
<p>MIT License</p>



