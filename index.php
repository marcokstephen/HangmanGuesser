<?php

define('DB_NAME', 'wordsDB'); //fill in this field with your database name
define('DB_USER', '____'); //fill in this field with your database login name
define('DB_PASSWORD', '____'); //fill in this field with your database password
define('DB_HOST', 'localhost'); //this should be localhost
define('SITE_URL','http://stephenmarcok.com/hangman/');

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$link) {
	die('Could not connect: ' . mysql_error());
}

$db_selected = mysql_select_db(DB_NAME, $link);
if (!$db_selected) {
	die('Can\'t use ' . DB_NAME . ': ' . mysql_error());
}


$excludeCharString = mysql_real_escape_string($_POST['exclude']);
$charsToIgnoreArray = explode(",", $excludeCharString);


echo '
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Hangman Solver - Stephen Marcok</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <link href="css/bootstrap.css" rel="stylesheet">

    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->

  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand">Hangman Solver</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li><a href="http://stephenmarcok.com">Return Home</a></li>';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	echo '<li><a href="'.SITE_URL.'">Restart</a></li>';
}
echo       '</ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>


    <div class="container">';

$fullWord = "";

if($_SERVER['REQUEST_METHOD'] != 'POST'){
echo '<div class="hero-unit">
        <h1>Hangman Solver!</h1>
        <p><br />How many characters is your word?</p><br />
        <p><center><form action="" method="post"><input type="text" name="length" size="10" maxlength="2" onkeypress=\'validate(event)\' style="width:70px;height:60px;font-size:60px;" /><br /><input type="submit" value="Begin &raquo;" class="btn btn-primary btn-large" /></center></p>
      </div>';
} else {

	$wordLength = mysql_real_escape_string($_POST['length']);
	if ($wordLength == 0){
		$wordLength = mysql_real_escape_string($_GET['len']);
	}
	
	echo '<div class="hero-unit"><form action="?len='.$wordLength.'" method="post"><div class="input-group"><center>';
	for ($i = 0; $i < $wordLength; $i++){
		$currentLetter = mysql_real_escape_string($_POST['char'.$i]);
		if ($currentLetter == ""){
			$fullWord .= "_";
		} else {
			$fullWord .= strtolower($currentLetter);
		}
		echo '<input type="text" class="form-control" maxlength="1" name="char'.$i.'" value="'.$currentLetter.'" style="height:19px;width:17px;font-size:25px";>';
	}
	echo '<br /><br />Exclude the following characters:<br /><input type="text" maxlength="52" name="ignore" placeholder="a,b,c,d" value="'.mysql_real_escape_string($_POST['ignore']).'"></center></div>
       <p><center><input type="submit" value="Update" class="btn btn-primary btn-large" /></center></p></form>';


	
	$excludeCharString = mysql_real_escape_string($_POST['ignore']);
	$charsToIgnoreArray = explode(",", $excludeCharString);
	
	$excludeQuery = "";
	foreach ($charsToIgnoreArray as $value){
		if ($value != ""){
			$excludeQuery .= 'AND NOT word LIKE "%'.$value.'%"';
		}
	}
	
	$sql = "SELECT word FROM wordtable
	 WHERE word LIKE \"".$fullWord."\"".$excludeQuery." ORDER BY RAND() LIMIT 50";
	$result = mysql_query($sql);

	$giantWord = "";
	$htmlListOutput = "";
	while($row = mysql_fetch_assoc($result)){
		if ($count < 30){
			$htmlListOutput .= '<li>'.$row['word'].'</li>';
			$count++;
		}
		$giantWord .= $row['word'];
	}

	$mostCommonNum = 0;
	$mostCommonLetter = "";
		foreach (count_chars($giantWord, 1) as $i => $val) {
		if (strpos($fullWord, chr($i)) === false){
			if ($val > $mostCommonNum){
				$mostCommonNum = $val;
				$mostCommonLetter = chr($i);
			}
		}
	}
	echo '<h2><center>Best guess letter: '.$mostCommonLetter.'</h2></center></div>';
	echo '<h2>Suggestions</h2>';
	echo $htmlListOutput;

	echo '</ul>';

}

echo '<hr>

      <footer>
        <p>&copy; Stephen Marcok 2014</p>
      </footer>

    </div> <!-- /container -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
	function validate(evt) {
  	var theEvent = evt || window.event;
	  var key = theEvent.keyCode || theEvent.which;
	  key = String.fromCharCode( key );
	  var regex = /[0-9]|\./;
	  if( !regex.test(key) ) {
	    theEvent.returnValue = false;
	    if(theEvent.preventDefault) theEvent.preventDefault();
  		}
	}
	</script>

  </body>
</html>';
?>
