<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
$birdy= birdyCMS::getInstance();
//============================================================================
birdySession::init();
//============================================================================
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(0);
//============================================================================
include "page-parts".DS."doctype.php";
$birdy->addStyleSheet(BIRDY_TEMPLATE_URL.'css/roulette.css');
//============================================================================
?>
{(birdy_feedback)}
<?php
// clear_tameio
if (isset($_REQUEST['clear_tameio'])) {
	unset($_SESSION['tameio']);
}
// check for password
if (isset($_REQUEST['pss'])) {
	$pss = $_REQUEST['pss'];
	if ($pss=='alexia') {
		$_SESSION['pss'] = $pss;
	} else {
		echo '<h2 class="style">ΛΑΘΟΣ!</h2>';
	}
}
if (empty($_SESSION['pss'])) {
	echo '<form style="float:left;" action="" method="POST">';
	echo 'KΩΔΙΚΟΣ: <input type="password" name="pss" />';
	echo '<input type="submit" name="submit" value="OK"/>';
	echo '</form>';	
	echo '</body>';
	echo '</html>';
	return;
}
// if new game requested, start a new session
if (empty($_SESSION['balance'])) {
	$_SESSION['winnings'] = 0;
	$_SESSION['losings'] = 0;
	$_SESSION['balance'] = 1000;
}
if (isset($_REQUEST['new_game'])) {
	//$winnings = $_SESSION['winnings'];
	//$losings = $_SESSION['losings'];
	$spins = isset($_SESSION['spins']) ? $_SESSION['spins'] : array();
	$balance = isset($_SESSION['balance']) ? $_SESSION['balance'] : 1000;
	$tameio = isset($_SESSION['tameio']) ? $_SESSION['tameio'] : 0;
	$pss = $_SESSION['pss'];
	session_unset();
	session_destroy();
	session_start();
	$_SESSION['spins'] = $spins;
	$_SESSION['tameio'] = $tameio + ($balance - 1000);
	$_SESSION['pss'] = $pss;
	$_SESSION['balance'] = 1000;
	// to store these values correctly, you have to store them in a file.
}
//============================================================================
// fix the roulette table
$roulette[1] = array('small',	'red',	'odd');
$roulette[2] = array('small',	'black','even');
$roulette[3] = array('small',	'red',	'odd');

$roulette[4] = array('small',	'black','even');
$roulette[5] = array('small',	'red',	'odd');
$roulette[6] = array('small',	'black','even');

$roulette[7] = array('small',	'red',	'odd');
$roulette[8] = array('small',	'black','even');
$roulette[9] = array('small',	'red',	'odd');

$roulette[10] = array('small',	'black','even');
$roulette[11] = array('small',	'black','odd');
$roulette[12] = array('small',	'red',	'even');

$roulette[13] = array('small',	'black','odd');
$roulette[14] = array('small',	'red',	'even');
$roulette[15] = array('small',	'black','odd');

$roulette[16] = array('small',	'red',	'even');
$roulette[17] = array('small',	'black','odd');
$roulette[18] = array('small',	'red',	'even');

$roulette[19] = array('big',	'red',	'odd');
$roulette[20] = array('big',	'black','even');
$roulette[21] = array('big',	'red',	'odd');

$roulette[22] = array('big',	'black','even');
$roulette[23] = array('big',	'red',	'odd');
$roulette[24] = array('big',	'black','even');

$roulette[25] = array('big',	'red',	'odd');
$roulette[26] = array('big',	'black','even');
$roulette[27] = array('big',	'red',	'odd');

$roulette[28] = array('big',	'black','even');
$roulette[29] = array('big',	'black','odd');
$roulette[30] = array('big',	'red',	'even');

$roulette[31] = array('big',	'black','odd');
$roulette[32] = array('big',	'red',	'even');
$roulette[33] = array('big',	'black','odd');

$roulette[34] = array('big',	'red',	'even');
$roulette[35] = array('big',	'black','odd');
$roulette[36] = array('big',	'red',	'even');

// fix our initial bets
if (!isset($_SESSION['bet_tables'])) {
	$black[1]=1;
	$black[2]=1;
	$black[3]=1;
	$black[4]=1;

	$red[1]=1;
	$red[2]=1;
	$red[3]=1;
	$red[4]=1;

	$odd[1]=1;
	$odd[2]=1;
	$odd[3]=1;
	$odd[4]=1;

	$even[1]=1;
	$even[2]=1;
	$even[3]=1;
	$even[4]=1;

	$small[1]=1;
	$small[2]=1;
	$small[3]=1;
	$small[4]=1;

	$big[1]=1;
	$big[2]=1;
	$big[3]=1;
	$big[4]=1;
} else {
	$black 	= $_SESSION['bet_tables']['black'];
	$red 	= $_SESSION['bet_tables']['red'];
	$odd 	= $_SESSION['bet_tables']['odd'];
	$even 	= $_SESSION['bet_tables']['even'];
	$small	= $_SESSION['bet_tables']['small'];
	$big 	= $_SESSION['bet_tables']['big'];
}
$smallBET	= '';
$bigBET		= '';
$oddBET		= '';
$evenBET	= '';
$blackBET	= '';
$redBET		= '';
//============================================================================
// if this is first time we are here, nothing came yet..
$came = '';

//============================================================================
// check what was spinned. Because 0(zero) plays also, we have to set an initial value of 'new' when nothing spinned
$spinned = (isset($_REQUEST['spinned'])) ? $_REQUEST['spinned'] : 'new';
if ($spinned=='new' && isset($_REQUEST['simulate'])) {
	$spinned = mt_rand(0,36);
}
$spinned = (string)$spinned;
//============================================================================
// if spinned, process the tables, give play suggestion
if ($spinned!='new') {
	// add to the session's spins
	$_SESSION['spins'][] = $spinned;
	
	// process our bets...
	if ($spinned!='0') {
		$small_big	 = $roulette[$spinned][0]; // this gives us small or big
		$black_red	 = $roulette[$spinned][1]; // this gives us black or red
		$odd_even	 = $roulette[$spinned][2]; // this gives us odd or even
		
		// process winnings and losings
		$winnings = 0;//$_SESSION['winnings'];
		$losings = 0;//$_SESSION['losings'];
		$came_greek = array(0=>'',1=>'',2=>'');$bet_won = '';$previous_greek = ''; 
		$previous_sum_blackred = 0; $previous_sum_oddeven = 0; $previous_sum_small_big = 0;
		// first show what came...
		switch ($small_big) {
			case 'small':
				$came_greek[2]= 'ΜΙΚΡΟ ';
				break;
			case 'big':
				$came_greek[2]= 'ΜΕΓΑΛΟ ';
				break;
		}
		switch ($odd_even) {
			case 'odd':
				$came_greek[1]= 'ΜΟΝΟ ';
				break;
			case 'even':
				$came_greek[1]= 'ΖΥΓΟ ';
				break;
		}
		switch ($black_red) {
			case 'black':
				$came_greek[0]= 'ΜΑΥΡΟ ';
				break;
			case 'red':
				$came_greek[0]= 'ΚΟΚΚΙΝΟ ';
				break;
		}
		foreach ($_SESSION['bets'] as $previous_bet => $value) {
			if ($previous_bet=='small' && $value!='-') { $previous_greek .= 'ΜΙΚΡΟ '; $previous_sum_small_big+= $value; }
			if ($previous_bet=='big' && $value!='-') { $previous_greek .= 'ΜΕΓΑΛΟ '; $previous_sum_small_big+= $value; }
			if ($previous_bet=='odd' && $value!='-') { $previous_greek .= 'ΜΟΝΟ '; $previous_sum_oddeven+= $value; }
			if ($previous_bet=='even' && $value!='-') { $previous_greek .= 'ΖΥΓΟ '; $previous_sum_oddeven+= $value; }
			if ($previous_bet=='black' && $value!='-') { $previous_greek .= 'ΜΑΥΡΟ '; $previous_sum_blackred+= $value; }
			if ($previous_bet=='red' && $value!='-') { $previous_greek .= 'ΚΟΚΚΙΝΟ '; $previous_sum_blackred+= $value; }
			switch ($small_big) {
				case 'small':
					if ($previous_bet=='small') {
						$winnings += ($value!='-') ? $value : 0;
					}
					if ($previous_bet=='big') $losings += ($value!='-') ? $value : 0;
					$small_big_next_bet = 'small';
					break;
				case 'big':
					if ($previous_bet=='big') {
						$winnings += ($value!='-') ? $value : 0;
					}
					if ($previous_bet=='small') $losings += ($value!='-') ? $value : 0;
					$small_big_next_bet = 'big';
					break;
			}
			switch ($odd_even) {
				case 'odd':
					if ($previous_bet=='odd') {
						$winnings += ($value!='-') ? $value : 0;
					}
					if ($previous_bet=='even') $losings += ($value!='-') ? $value : 0;
					$odd_even_next_bet = 'odd';
					break;
				case 'even':
					if ($previous_bet=='even') {
						$winnings += ($value!='-') ? $value : 0;
					}
					if ($previous_bet=='odd') $losings += ($value!='-') ? $value : 0;
					$odd_even_next_bet = 'even';
					break;
			}
			switch ($black_red) {
				case 'black':
					if ($previous_bet=='black') {
						$winnings += ($value!='-') ? $value : 0;
					}
					if ($previous_bet=='red') $losings += ($value!='-') ? $value : 0;
					$black_red_next_bet = 'black';
					break;
				case 'red':
					if ($previous_bet=='red') {
						$winnings += ($value!='-') ? $value : 0;
					}
					if ($previous_bet=='black') $losings += ($value!='-') ? $value : 0;
					$black_red_next_bet = 'red';
					break;
			}
		}
		$_SESSION['winnings'] = $winnings;
		$_SESSION['losings'] = $losings;
		$_SESSION['balance'] = $_SESSION['balance'] + ($winnings - $losings);
		
	} else {
		// 0! all bets lost!
		$small_big = 'zero';
		$black_red = 'zero';
		$odd_even  = 'zero';

		// process winnings and losings
		$winnings = 0;//$_SESSION['winnings'];
		$losings = 0;//$_SESSION['losings'];
		$came_greek = array(0=>'ZERO',1=>'',2=>'');$bet_won = '';$previous_greek = ''; 
		$previous_sum_blackred = 0; $previous_sum_oddeven = 0; $previous_sum_small_big = 0;
		foreach ($_SESSION['bets'] as $previous_bet => $value) {
			if ($previous_bet=='small' && $value!='-') { $previous_greek .= 'ΜΙΚΡΟ '; $previous_sum_small_big+= $value; }
			if ($previous_bet=='big' && $value!='-') { $previous_greek .= 'ΜΕΓΑΛΟ '; $previous_sum_small_big+= $value; }
			if ($previous_bet=='odd' && $value!='-') { $previous_greek .= 'ΜΟΝΟ '; $previous_sum_oddeven+= $value; }
			if ($previous_bet=='even' && $value!='-') { $previous_greek .= 'ΖΥΓΟ '; $previous_sum_oddeven+= $value; }
			if ($previous_bet=='black' && $value!='-') { $previous_greek .= 'ΜΑΥΡΟ '; $previous_sum_blackred+= $value; }
			if ($previous_bet=='red' && $value!='-') { $previous_greek .= 'ΚΟΚΚΙΝΟ '; $previous_sum_blackred+= $value; }
			$losings += ($value!='-') ? $value : 0;
		}
		$_SESSION['winnings'] = $winnings;
		$_SESSION['losings'] = $losings;
		$_SESSION['balance'] = $_SESSION['balance'] + ($winnings - $losings);
		
	}
	//echo $black_red." | ".$odd_even." | ".$small_big."<br />";

	$to_process = array($small_big, $black_red, $odd_even);
	$processing = array('small'=>$small, 'big'=>$big, 'black'=>$black, 'red'=>$red, 'odd'=>$odd, 'even'=>$even);
	if ($spinned!='0') {
		// first the winners
		foreach ($to_process as $process) {
			if ($_SESSION['bets'][$process]!='-') {
				// winner?
				foreach ($$process as $key => $value) {
					// first in array that plays, won, plays no more
					if ($value) { //this is true
						$processing[$process][$key] = 0;
						break;
					}
				}
			}
			$reverse = array_reverse($$process, true);
			foreach ($reverse as $key => $value) {
				if ($_SESSION['bets'][$process]!='-') {
					// last in array that plays, won, plays no more
					if ($value) {
						$processing[$process][$key] = 0;
						break;
					}
				}
			}
		}
		
		// opposites
		if ($small_big=='small') $small_big = 'big';
		elseif ($small_big=='big') $small_big = 'small';
		if ($black_red=='black') $black_red = 'red';
		elseif ($black_red=='red') $black_red = 'black';
		if ($odd_even=='odd') $odd_even = 'even';
		elseif ($odd_even=='even') $odd_even = 'odd';
		// now the losers (for calculating on the next round)
		$to_process = array($small_big, $black_red, $odd_even);
		foreach ($to_process as $process) {
			// loser?
			$added = 0;
			foreach ($$process as $key => $value) {
				// first in array that plays, lost, add to the next bet
				if ($value) {
					$added+= $key;
					break;
				}
			}
			$reverse = array_reverse($$process, true);
			foreach ($reverse as $key => $value) {
				// last in array that plays, lost, add to the next bet
				if ($value) {
					$added+= $key;
					break;
				}
			}
			// add the next bet
			$processing[$process][$added] = 1;
		}
		
	} else {
		// 0! EVERYTHING LOST!
		$to_process = array('small','big','black','red','odd','even');
		foreach ($to_process as $process) {
			// loser?
			$added = 0;
			foreach ($$process as $key => $value) {
				// first in array that plays, lost, add to the next bet
				if ($value) {
					$added+= $key;
					break;
				}
			}
			$reverse = array_reverse($$process, true);
			foreach ($reverse as $key => $value) {
				// last in array that plays, lost, add to the next bet
				if ($value) {
					$added+= $key;
					break;
				}
			}
			// add the next bet
			$processing[$process][$added] = 1;
		}
	}

	// reassign the processed values to the tables
	foreach ($processing as $key => $value) {
		$$key = $value;
	}
	
	// now the suggestion (WHAT TO BET)
	$to_process = array('small'=>$small, 'big'=>$big, 'black'=>$black, 'red'=>$red, 'odd'=>$odd, 'even'=>$even);
	foreach ($to_process as $name => $process) {
		$proc = 'added'.$name;
		$$proc = 0;
		foreach ($process as $key => $value) {
			if ($value) {
				$$proc+= $key;
				break;
			}
		}
		$reverse = array_reverse($process, true);
		foreach ($reverse as $key => $value) {
			if ($value) {
				$$proc+= $key;
				break;
			}
		}
	}
	// OPPOSITE BETS
	$smallBET	= $addedsmall 	- $addedbig;
	$bigBET		= $addedbig 	- $addedsmall;
	$oddBET		= $addedodd 	- $addedeven;
	$evenBET	= $addedeven 	- $addedodd;
	$blackBET	= $addedblack 	- $addedred;
	$redBET		= $addedred 	- $addedblack;

	$black_or_red = ($blackBET>0) ? "ΜΑΥΡΑ=".$blackBET." | " : "ΚΟΚΚΙΝΑ=".$redBET." | ";
	$odd_or_even  = ($oddBET>0)   ? "ΜΟΝΑ=".$oddBET." | "   : "ΖΥΓΑ=".$evenBET." | ";
	$small_or_big = ($smallBET>0) ? "ΜΙΚΡΑ=".$smallBET : "ΜΕΓΑΛΑ=".$bigBET;
	/*
	echo '<div style="font-weight:bold">';
	echo 'ΠΑΙΞΕ: '.$black_or_red.$odd_or_even.$small_or_big;
	echo '</div>';
	*/
	if (isset($_REQUEST['simulate'])) $came = $spinned;
}

//============================================================================
// our forms...
if ($_SESSION['balance']>1016) {
	echo "<h2 class='roulette-h2'>ΠΗΡΕΣ ".($_SESSION['balance']-1000)."€! Θέλεις να συνεχίσεις;</h2>";
	echo '<form class="roulette-form" action="" method="POST">';
	echo '<input type="submit" class="submit" name="new_game" style="width:350px" value="ΝΕΟ ΠΑΙΧΝΙΔΙ"/>';
	echo '</form>';
	echo "<br style='clear:both'/>";
}
elseif ($_SESSION['balance']<910) {
	echo "<div class='alert'>";
	echo "<h2 class='roulette-h2' style='color:#EFEFEF'>ΧΑΝΕΙΣ ".(1000-$_SESSION['balance'])."€! Θέλεις να συνεχίσεις;</h2>";
	echo '<form class="roulette-form" action="" method="POST">';
	echo '<input type="submit" class="submit" name="new_game" style="width:350px" value="ΝΕΟ ΠΑΙΧΝΙΔΙ"/>';
	echo '</form>';
	echo '</div>';
	echo "<br style='clear:both'/>";
}
echo '<form onsubmit="submitParent()" style="float:left;" action="" method="POST" onsubmit="if (document.getElementById(\'spinned\').value>36) {alert(\'ΜΕΧΡΙ 36!\');return false}">';
echo 'ΗΡΘΕ: <input type="text" class="spinned" id="spinned" name="spinned" value="'.$came.'" autocomplete="off" />';
echo '<input type="submit" name="submit" class="submit" value="OK"/>';
echo '</form>';
echo '<form style="float:left;" action="" method="POST">';
echo '<input type="submit" name="simulate" style="height:70px;" value="ΠΡΟΣΟΜΕΙΩΣΗ"/>';
echo '</form>';
echo "<hr style='clear:both'/>";

//============================================================================
// show the past 9 spins
$total = 0;
echo "ΜΠΙΛΙΕΣ:";
if (isset($_SESSION['spins'])) {
	$spins = array_reverse($_SESSION['spins']);
	$total = count($spins)>9 ? 9 : count($spins);
}
for ($i=0;$i<$total;$i++) {
	$color = ($spins[$i]=='0') ? "#00D000" /*green*/ : $roulette[$spins[$i]][1];
	echo ' <span style="color:#BBB">|</span> ';
	echo '<span style="color:'.$color.'">';
	echo $spins[$i];
	echo '</span>';
}

//============================================================================
// winnings, etc.
echo "<table>";
if (!empty($previous_greek)) {
	echo "<hr />";
	echo "<tr><td>Έπαιξες: </td><td style='padding-left:5px;font-weight:bold'>$previous_greek ";
	echo "( ";
	if ($previous_sum_blackred) echo $previous_sum_blackred."€ <span style='color:#BBB'>|</span> ";
	if ($previous_sum_oddeven) echo $previous_sum_oddeven."€ <span style='color:#BBB'>|</span> ";
	if ($previous_sum_small_big) echo $previous_sum_small_big."€ ";
	echo ")</td></tr>";
}
if (!empty($came_greek)) {
	echo "<tr><td>Ηρθε: </td><td style='padding-left:5px;font-weight:bold'>".implode(" ",$came_greek)."</td></tr>";
}
echo "</table>";

// ΚΕΡΔΗ: ".$_SESSION['winnings']."€ ΧΑΣΟΥΡΑ: ".$_SESSION['losings']."€ 
$color = 'black';
if ($_SESSION['balance']>1000) $color = 'green';
if ($_SESSION['balance']<1000) $color = 'grey';
if ($_SESSION['balance']<0) $color = 'red';
echo "<hr />ΚΕΦΑΛΑΙΟ: <span style='color:$color;'>".$_SESSION['balance']."€</span> ";
if (!empty($previous_greek)) {
	$winnings = $winnings - $losings;
	$color = ($winnings > 0) ? "green" : "red";
	echo "<span style='color:$color'>";
	echo ($winnings > 0) ? "(πήρες " : "(έχασες ";
	if ($winnings<0) $winnings = $winnings * (-1);
	echo $winnings.'€)';
	echo "</span>";
}
if (!empty($_SESSION['tameio'])) {
	echo "<br />ταμείο: ".$_SESSION['tameio'].'€';
	echo " <form style='display:inline' action='' method='POST'><input type='submit' name='clear_tameio' value='ΕΚΚΑΘΑΡΙΣΗ' /></form>";
}
echo "<hr style='clear:both'/>";

//============================================================================
// fix for extreme big bets! >300€
// first check if bet is bigger than 300
/*
if ($blackBET>299) {
	// if yes, check which column is bigger from the other pairs
	$oddeven_adds = (count($odd)>count($even)) ? 'evenBET' : 'oddBET';
	$smallbig_adds= (count($small)>count($big)) ? 'bigBET' : 'smallBET';
	// divider of the bet
	$divider = 1;
	// if  the smaller column of the other pairs is not already betting more than 150€ divide, else do NOT divide
	if ($$soddeven_adds>150) $oddeven_adds = false; else $divider++;
	if ($$smallbig_adds>150) $smallbig_adds= false; else $divider++;
	// divide the bet by the divider
	$blackBET = intval($blackBET / $divider);
	// add the divided bet to the rest columns
	if ($oddeven_adds) $$oddeven_adds = $$oddeven_adds + $blackbet;
	if ($smallbig_adds) $$smallbig_adds= $$smallbig_adds + $blackbet;
}

$currentTable = array('ΜΑΥΡΑ'=>'black', 'ΚΟΚΚ.'=>'red', 'ΜΟΝΑ'=>'odd', 'ΖΥΓΑ'=>'even', 'ΜΙΚΡΑ'=>'small', 'ΜΕΓΑΛΑ'=>'big');
$otherpairs = array(
	'ΜΑΥΡΑ'=>array(
		array($odd,$even,$small,$big),
		array('oddBET','evenBET','smallBET','bigBET'),
		array('oddBET'=>'ΜΟΝΑ','evenBET'=>'ΖΥΓΑ','smallBET'=>'ΜΙΚΡΑ','bigBET'=>'ΜΕΓΑΛΑ')
	),
	'ΚΟΚΚ.'=>array(
		array($odd,$even,$small,$big),
		array('oddBET','evenBET','smallBET','bigBET'),
		array('oddBET'=>'ΜΟΝΑ','evenBET'=>'ΖΥΓΑ','smallBET'=>'ΜΙΚΡΑ','bigBET'=>'ΜΕΓΑΛΑ')
	),
	'ΜΟΝΑ'=>array(
		array($black,$red,$small,$big),
		array('blackBET','redBET','smallBET','bigBET'),
		array('blackBET'=>'ΜΑΥΡΑ','redBET'=>'ΚΟΚΚ.','smallBET'=>'ΜΙΚΡΑ','bigBET'=>'ΜΕΓΑΛΑ')
	),
	'ΖΥΓΑ'=>array(
		array($black,$red,$small,$big),
		array('blackBET','redBET','smallBET','bigBET'),
		array('blackBET'=>'ΜΑΥΡΑ','redBET'=>'ΚΟΚΚ.','smallBET'=>'ΜΙΚΡΑ','bigBET'=>'ΜΕΓΑΛΑ')
	),
	'ΜΙΚΡΑ'=>array(
		array($odd,$even,$black,$red),
		array('oddBET','evenBET','blackBET','redBET'),
		array('oddBET'=>'ΜΟΝΑ','evenBET'=>'ΖΥΓΑ','blackBET'=>'ΜΑΥΡΑ','redBET'=>'ΚΟΚΚ.')
	),
	'ΜΕΓΑΛΑ'=>array(
		array($odd,$even,$black,$red),
		array('oddBET','evenBET','blackBET','redBET'),
		array('oddBET'=>'ΜΟΝΑ','evenBET'=>'ΖΥΓΑ','blackBET'=>'ΜΑΥΡΑ','redBET'=>'ΚΟΚΚ.')
	)
);
*/
//============================================================================
$output = array( 'ΜΑΥΡΑ'=>$black, 'ΚΟΚΚ.'=>$red, 'ΜΟΝΑ'=>$odd, 'ΖΥΓΑ'=>$even, 'ΜΙΚΡΑ'=>$small, 'ΜΕΓΑΛΑ'=>$big);
// fix for extreme big bets! >300€
// first check if bet is bigger than 300
foreach ($output as $key => $table) {
		// divider of the bet
		$divider = 3;
		$message = '';

	$currentBET = $currentTable[$key].'BET';
/*
	if ($$currentBET>299) {
			$firstpair_table1 = str_replace("BET","",$otherpairs[$key][1][0]); // red, black, etc... (tables)
			$firstpair_adds1 =  $otherpairs[$key][1][0]; // redBET, blackBET, etc...
			$firstpair_table2 = str_replace("BET","",$otherpairs[$key][1][1]); // red, black, etc... (tables)
			$firstpair_adds2 =  $otherpairs[$key][1][1]; // redBET, blackBET, etc...
			$secondpair_table1 =  str_replace("BET","",$otherpairs[$key][1][2]);
			$secondpair_adds1 =  $otherpairs[$key][1][2];
			$secondpair_table2 =  str_replace("BET","",$otherpairs[$key][1][3]);
			$secondpair_adds2 =  $otherpairs[$key][1][3];
		// divide the bet by the divider
		$initialBET = $$currentBET;
		$$currentBET = intval($$currentBET / $divider);
		// add the divided bet to the rest columns
			$firstpair_adds1 = $$firstpair_adds1 + intval($$currentBET / 2); //10 + 10
			${$firstpair_table1}[] = $firstpair_adds1;
			$firstpair_adds2 = $$firstpair_adds2 + intval($$currentBET / 2); //20 + 10
			${$firstpair_table2}[] = $firstpair_adds2;
			//20-30 = -10 => 0 (arxiko)
			//30-20 = 10 => 10+10 = 20
			$$firstpair_adds1 = ($firstpair_adds1-$firstpair_adds2)>-1 ? ($firstpair_adds1-$firstpair_adds2) : 0;
			$$firstpair_adds2 = ($firstpair_adds2-$firstpair_adds1)>-1 ? ($firstpair_adds2-$firstpair_adds1) : 0;
			$secondpair_adds1 = $$secondpair_adds1 + intval($$currentBET / 2);
			${$secondpair_table1}[] = $secondpair_adds1;
			$secondpair_adds2 = $$secondpair_adds2 + intval($$currentBET / 2);
			${$secondpair_table2}[] = $secondpair_adds2;
			//20-30 = -10 => 0 (arxiko)
			//30-20 = 10 => 10+10 = 20
			$$secondpair_adds1 = ($secondpair_adds1-$secondpair_adds2)>-1 ? ($secondpair_adds1-$secondpair_adds2) : 0;
			$$secondpair_adds2 = ($secondpair_adds2-$secondpair_adds1)>-1 ? ($secondpair_adds2-$secondpair_adds1) : 0;
	} else {
		$divider = 0; // there was no need to divide anything
	}
	
	if ($divider==1) $message = '<div><strong>ΔΕΝ ΜΠΟΡΕΣΑ ΝΑ ΜΟΙΡΑΣΩ ΤΟ ΠΟΝΤΑΡΙΣΜΑ ΣΤΑ '.$key.'! ΠΟΣΟ: '.$$currentBET.'€</strong></div>';
	elseif ($divider>1) {
		$message = '<div><strong>ΜΟΙΡΑΣΤΗΚΕ ΤΟ ΠΟΝΤΑΡΙΣΜΑ ΣΤΑ '.$key.'('.$initialBET.'€)!<br />
		ΝΕΑ ΠΟΝΤΑΡΙΣΜΑΤΑ: '.$key.'('.$$currentBET.'€)';
		if ($firstpair_adds1) $message.= ', '.$otherpairs[$key][2][$firstpair_adds1].'('.$$currentBET.'€)';
		if ($secondpair_adds1) $message.= ', '.$otherpairs[$key][2][$secondpair_adds1].'('.$$currentBET.'€)';
		if ($firstpair_adds2) $message.= ', '.$otherpairs[$key][2][$firstpair_adds2].'('.$$currentBET.'€)';
		if ($secondpair_adds2) $message.= ', '.$otherpairs[$key][2][$secondpair_adds2].'('.$$currentBET.'€)';
		$message.= '</strong></div>';
	}
*/
	if ($$currentBET>99) {
		$message = '<h2>'.$key.'! ΠΟΝΤΑΡΙΣΜΑ: '.$$currentBET.'€<br />';
		$message.= "Θέλεις να συνεχίσεις;</h2>";
		$message.= '<form class="roulette-form" action="" method="POST">';
		$message.= '<input type="submit" class="submit" name="new_game" style="width:350px" value="ΝΕΟ ΠΑΙΧΝΙΔΙ"/>';
		$message.= '</form>';
		$message.= "<br style='clear:both'/>";
	}
	$birdy->outputWarning($message);
}
//============================================================================
$tyxes=9;
// make sure we play what the roulette plays
/*
if ($black_red_next_bet=='black') {
	if ($blackBET<1) {
		$blackBET=$redBET;
		$redBET=0;
	}
}
elseif ($black_red_next_bet=='red') {
	if ($redBET<1) {
		$redBET=$blackBET;
		$blackBET=0;
	}
}
*/
if ($blackBET<1) $tyxes--; //8
if ($redBET<1) $tyxes--; //7 $tyxes sigura 8
if (array_sum($black)<1 || array_sum($red)<1) $tyxes--;

/*
if ($odd_even_next_bet=='odd') {
	if ($oddBET<1) {
		$oddBET=$evenBET;
		$evenBET=0;
	}
}
elseif ($odd_even_next_bet=='even') {
	if ($evenBET<1) {
		$evenBET=$oddBET;
		$oddBET=0;
	}
}
*/
if ($oddBET<1) $tyxes--; //6
if ($evenBET<1) $tyxes--; //5 $tyxes sigura 7
if (array_sum($odd)<1 || array_sum($even)<1) $tyxes--;

/*
if ($small_big_next_bet=='small') {
	if ($smallBET<1) {
		$smallBET=$bigBET;
		$bigBET=0;
	}
}
elseif ($small_big_next_bet=='big') {
	if ($bigBET<1) {
		$bigBET=$smallBET;
		$smallBET=0;
	}
}
*/
if ($smallBET<1) $tyxes--; //4
if ($bigBET<1) $tyxes--; //3 $tyxes sigura 6
if (array_sum($small)<1 || array_sum($big)<1) $tyxes--;

if ($tyxes<3) {
	$message = '<h2>ΠΑΙΖΕΙΣ ΜΕ ΜΙΑ ΜΟΝΟ ΤΥΧΗ!<br />';
	$message.= "Θέλεις να συνεχίσεις;</h2>";
	$message.= '<form class="roulette-form" action="" method="POST">';
	$message.= '<input type="submit" class="submit" name="new_game" style="width:350px" value="ΝΕΟ ΠΑΙΧΝΙΔΙ"/>';
	$message.= '</form>';
	$message.= "<br style='clear:both'/>";
}
$birdy->outputWarning($message);
//============================================================================
// show our tables
foreach ($output as $key => $table) {
	echo "<table class='roulette'>";
	if ($spinned!='new') {
		$bet_style = 'color:red;border-bottom: 3px solid red;font-weight: bold;';
		switch ($key) {
			// $black_red styles are opposite because we switched them earlier in the code
			case "ΜΑΥΡΑ":
				$class = ($black_red=='red') ? 'background:#000;color:#FFF' : '';
				echo "<tr><th style='$class'><h1>$key</h1></th></tr>";
				if ($blackBET<1) $blackBET='-';
				echo "<tr><td style='$bet_style'>$blackBET</td></tr>";
				break;
			case "ΚΟΚΚ.":
				$class = ($black_red=='black') ? 'background:#000;color:#FFF' : '';
				echo "<tr><th style='$class'><h1>$key</h1></th></tr>";
				if ($redBET<1) $redBET='-';
				echo "<tr><td style='$bet_style'>$redBET</td></tr>";
				break;
			case "ΜΟΝΑ":
				$class = ($odd_even=='even') ? 'background:#000;color:#FFF' : '';
				echo "<tr><th style='$class'><h1>$key</h1></th></tr>";
				if ($oddBET<1) $oddBET='-';
				echo "<tr><td style='$bet_style'>$oddBET</td></tr>";
				break;
			case "ΖΥΓΑ":
				$class = ($odd_even=='odd') ? 'background:#000;color:#FFF' : '';
				echo "<tr><th style='$class'><h1>$key</h1></th></tr>";
				if ($evenBET<1) $evenBET='-';
				echo "<tr><td style='$bet_style'>$evenBET</td></tr>";
				break;
			case "ΜΙΚΡΑ":
				$class = ($small_big=='big') ? 'background:#000;color:#FFF' : '';
				echo "<tr><th style='$class'><h1>$key</h1></th></tr>";
				if ($smallBET<1) $smallBET='-';
				echo "<tr><td style='$bet_style'>$smallBET</td></tr>";
				break;
			case "ΜΕΓΑΛΑ":
				$class = ($small_big=='small') ? 'background:#000;color:#FFF' : '';
				echo "<tr><th style='$class'><h1>$key</h1></th></tr>";
				if ($bigBET<1) $bigBET='-';
				echo "<tr><td style='$bet_style'>$bigBET</td></tr>";
				break;
		}
	} else {
		echo "<tr><th><h1>$key</h1></th></tr>";
	}
	foreach ($table as $key => $value) {
		$color = ($key<5) ? 'color:blue' : 'color:red';
		echo "<tr><td>";
		echo (!empty($value) && $value!='0') ? '<span style="'.$color.'">'.$key.'</span>' : "&#x25cf;";
		echo "</td></tr>";
	}
	echo "</table>";
}

//============================================================================
// new game question...
echo "<br style='clear:both'/>";
echo '<form action="" method="POST">';
echo '<input type="submit" name="new_game" value="ΝΕΟ ΠΑΙΧΝΙΔΙ"/>';
echo '</form>';
echo "<br style='clear:both'/>";

//============================================================================
// save our bets to session to see what we won/lost
$_SESSION['bets'] = array('black'=>$blackBET, 'red'=>$redBET, 'odd'=>$oddBET, 'even'=>$evenBET, 'small'=>$smallBET, 'big'=>$bigBET);
$_SESSION['bet_tables'] = array('black'=>$black, 'red'=>$red, 'odd'=>$odd, 'even'=>$even, 'small'=>$small, 'big'=>$big);

?>
<script type="text/javascript">
window.onload = function() {
  document.getElementById("spinned").focus();
};
function submitParent(){parent.spinForm.submit();return true;}
</script>
</body>
</html>