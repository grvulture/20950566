<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
$birdy= birdyCMS::getInstance();
//============================================================================
birdySession::init();
//============================================================================
include "page-parts".DS."doctype.php";
$birdy->addStyleSheet(BIRDY_TEMPLATE_URL.'css/roulette.css');
//============================================================================
?>
<body>
{(birdy_feedback)}
<div style="float:left;width:49%">
<?php
function swap(&$a, &$b) {
    $a = $a ^ $b;
    $b = $a ^ $b;
    $a = $a ^ $b;
}

if (empty($_SESSION['tameio'])) $_SESSION['tameio'] = 0;
if (empty($_SESSION['losings_last'])) $_SESSION['losings_last'] = 0;
// clear_tameio
if (isset($_REQUEST['clear_tameio'])) {
	$_SESSION['tameio'] = 0;
	$_SESSION['losings'] = 0;
	$_SESSION['balance'] = 2500;
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
$winnings = 0;
$losings = 0;
// if new game requested, start a new session
if (empty($_SESSION['balance'])) {
	$_SESSION['winnings'] = 0;
	$_SESSION['losings'] = 0;
	$_SESSION['balance'] = 2500;
	$_SESSION['now_play_black_red'] = false;
	$_SESSION['now_play_small_big'] = false;
	$_SESSION['now_play_odd_even'] = false;
}
if (isset($_REQUEST['new_game'])) {
	$_SESSION['now_play_black_red'] = false;
	$_SESSION['now_play_small_big'] = false;
	$_SESSION['now_play_odd_even'] = false;
	//$winnings = $_SESSION['winnings'];
	$losings = $_SESSION['losings'];
	$spins = isset($_SESSION['spins']) ? $_SESSION['spins'] : array();
	$balance = isset($_SESSION['balance']) ? $_SESSION['balance'] : 2500;
	$tameio = isset($_SESSION['tameio']) ? $_SESSION['tameio'] : 0;
	$pss = $_SESSION['pss'];
	session_unset();
	session_destroy();
	session_start();
	$_SESSION['spins'] = $spins;
	$_SESSION['tameio'] = $tameio;
	$_SESSION['pss'] = $pss;
	$_SESSION['balance'] = $balance;
	$_SESSION['losings'] = $losings;
	// to store these values correctly, you have to store them in a file.
}
if (!isset($_SESSION['losings'])) $_SESSION['losings']=0;
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
	$black_red=array();

	$odd_even=array();

	$small_big=array();

} else {
	$black_red 	= $_SESSION['bet_tables']['black_red'];
	$odd_even 	= $_SESSION['bet_tables']['odd_even'];
	$small_big	= $_SESSION['bet_tables']['small_big'];
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
$losings_last['small'] = 0;
$losings_last['big'] = 0;
$losings_last['odd'] = 0;
$losings_last['even'] = 0;
$losings_last['black'] = 0;
$losings_last['red'] = 0;
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
		$small_big_spinned	 = $roulette[$spinned][0]; // this gives us small or big
		$black_red_spinned	 = $roulette[$spinned][1]; // this gives us black or red
		$odd_even_spinned	 = $roulette[$spinned][2]; // this gives us odd or even
		
		$previous_greek = ''; 
		$previous_sum_small_big = 0;
		$previous_sum_odd_even = 0;
		$previous_sum_black_red = 0;
		$to_bet_small = '-';
		$to_bet_big = '-';
		$to_bet_odd = '-';
		$to_bet_even = '-';
		$to_bet_black = '-';
		$to_bet_red = '-';
		
		$processed_small_big = false;
		$processed_odd_even  = false;
		$processed_black_red = false;

		$spinned_tables = array('small'=>'small_big','big'=>'small_big' , 'odd'=>'odd_even','even'=>'odd_even' , 'black'=>'black_red','red'=>'black_red');
		foreach ($spinned_tables as $key=>$value) {
			$spinned_switch = $value.'_spinned';
			//echo $key.": ".$_SESSION['bets'][$key].' && '.$value.'_spinned='.$$spinned_switch.'<br />';
			if (!empty($_SESSION['now_play_'.$value]) && intval($_SESSION['bets'][$key]) && $$spinned_switch!=$key) $losings_last[$key] += $_SESSION['bets'][$key];
			$spinned_switch = $value.'_spinned';
			$processed = 'processed_'.$value;
			if (count($$value)>3 && empty($_SESSION['now_play_'.$value]) && ($$spinned_switch==$key && ($_SESSION['bets'][$key]=='-'||$_SESSION['bets'][$key]=='0'))) {
				//if small then play big elseif big then play small
				$key1 = explode("_",$value);
				$key2 = $key1[1];
				$key1 = $key1[0];
				if ($key==$key1) { //
					$_SESSION['bets'][$key1] = 0;
					$_SESSION['bets'][$key2] = 0;
				} elseif ($key==$key2) { //
					$_SESSION['bets'][$key1] = 0;
					$_SESSION['bets'][$key2] = 0;
				}
				$_SESSION['now_play_'.$value] =  true;
				$_SESSION['now_play_'] =  true; //general
 				$_SESSION['finished_'.$value] = false;
				$$value = array();
// 			} elseif (empty($_SESSION['now_play_'.$value]) && !empty($_SESSION['finished_'.$value])) {
// 				$_SESSION['now_play_'.$value] =  false;
// 				$_SESSION['initialized_'.$value] =  false;
			} else {
				//$losings_last[$key] = 0;
				$$processed = true;
			}
		}
		
		// IF THIS IS THE SECOND TIME WE PLAY, AND WE WIN SET XASOURA TO 0 FOR NEXT TIME
		foreach ($spinned_tables as $key=>$value) {
			$spinned_switch = $value.'_spinned';
			if ($$spinned_switch==$key && $_SESSION['bets'][$key]!='-' && !empty($_SESSION['now_play_'.$value])) {
				if (!empty($_SESSION['first_win_'.$value])) {
					$losings_last[$key] = 0;
				}
			}
		}
		// KEEP IN SESSION THAT OUR FIRST WIN NOW SHOULD BE CONSIDERED AS A LOSS IN THE TABLE *BUT* AS A WIN IN TAMEIO
		foreach ($spinned_tables as $key=>$value) {
			$spinned_switch = $value.'_spinned';
			if ($$spinned_switch==$key && $_SESSION['bets'][$key]!='-' && !empty($_SESSION['now_play_'.$value])) {
				//if what came is what we played and we are actually playing it
				if (empty($_SESSION['first_win_'.$value])) {
					// this is our first win, now do not delete from table
					$_SESSION['first_win_'.$value] = true;
				} else {
					// this is our second win, now delete from table.
					$_SESSION['first_win_'.$value] = false;
					if (count($$value)<6) {
						$_SESSION['now_play_'.$value] = false;
						$_SESSION['now_play_'] = false; // general
						$_SESSION['initialized_'.$value] =  false;
						$_SESSION['finished_'.$value] = true;
					}
				}
				$winnings+= $_SESSION['bets'][$key];
			}
		}
		
// 		foreach ($spinned_tables as $key=>$value) {
// 			if (!empty($_SESSION['now_play_'.$value]) && empty($_SESSION['finished_'.$value]) && !empty($_SESSION['initialized_'.$value])) {
// 				$_SESSION['initialized_'.$value] =  true;
// 				$$value = array();
// 			}
// 		}
		
		foreach ($spinned_tables as $key=>$value) {
				$spinned_switch = $value.'_spinned';
				
					if ($_SESSION['bets'][$key]!='-') {
						if ($key=='small') $previous_greek .= 'ΜΙΚΡΟ ';
						if ($key=='big') $previous_greek .= 'ΜΕΓΑΛΟ ';
						if ($key=='odd') $previous_greek .= 'ΜΟΝΟ ';
						if ($key=='even') $previous_greek .= 'ΖΥΓΟ ';
						if ($key=='black') $previous_greek .= 'ΜΑΥΡΟ ';
						if ($key=='red') $previous_greek .= 'ΚΟΚΚΙΝΟ ';
						$previous_sum = 'previous_sum_'.$value;
						$$previous_sum = $_SESSION['bets'][$key];
					}

				if ($$spinned_switch==$key) {//small_big_spinned==small
					//if ($_SESSION['bets'][$key]!='-') $winnings++;
					//echo "small start:<pre>";print_r($small_big);echo "</pre>";
					if ($key=='small') $came_greek[2]= 'ΜΙΚΡΟ ';
					if ($key=='big') $came_greek[2]= 'ΜΕΓΑΛΟ ';
					if ($key=='odd') $came_greek[2] .= 'ΜΟΝΟ ';
					if ($key=='even') $came_greek[2] .= 'ΖΥΓΟ ';
					if ($key=='black') $came_greek[2] .= 'ΜΑΥΡΟ ';
					if ($key=='red') $came_greek[2] .= 'ΚΟΚΚΙΝΟ ';
					$to_add = 0; $added = 0;
					$i = count($$value);
					$total = (count($$value)>4) ? 4 : count($$value);
					if ($i==0) ${$value}[1] = 1;
					else {
						$start = count($$value)-$total;
	// 				if ($key=='odd') echo "I:$i | SMALL_BIG:".$start."<br />";
						while($i>$start) {
							// if we played it remove 4
								if ($_SESSION['bets'][$key]!='-') {
									if (empty($_SESSION['first_win_'.$value])) {
										//echo $value.": FINISHED!<br />";
										unset(${$value}[$i]);
									} else {
										if (isset(${$value}[$i])) {
											$to_add+= ${$value}[$i];
											if ($i!=$start+1 || $total<4) $added+= ${$value}[$i];
										}
									}
								// if we played opposite add the sum of the last 4
								} else {
									if (isset(${$value}[$i])) {
										$to_add+= ${$value}[$i];
										if ($i!=$start+1 || $total<4) $added+= ${$value}[$i];
									}
								}
							$i--;
						}
					}
					$to_bet_key = 'to_bet_'.$key;
					if (!empty($to_add)) {
						${$value}[]=$to_add;
						$$to_bet_key = $to_add+$added;
					} else {
						if (empty($$value)) {
							${$value}[1]=1;
							$to_add=1; //to bet on the next round
						} else {
							$i = count($$value);
							$total = (count($$value)>4) ? 4 : count($$value);
							if ($i==0) {
								${$value}[1] = 1;
								$to_add=1; //to bet on the next round
							} else {
								$start = count($$value)-$total;
								while($i>$start) {
									if (isset(${$value}[$i])) {
										$to_add+= ${$value}[$i];
										if ($i!=$start+1 || $total<4) $added+= ${$value}[$i];
									}
									$i--;
								}
							}
						}
						$$to_bet_key = $to_add;
					}
					if ($$to_bet_key==0) $$to_bet_key=1;
					//echo "small end:<pre>";print_r($small_big);echo "</pre>";
				}
			//}
		}

	} else {
		// 0! all bets lost!
		$small_big_spinned = 'zero';
		$black_red_spinned = 'zero';
		$odd_even_spinned  = 'zero';

		$to_bet_small = $_SESSION['bets']['small'];
		$to_bet_big = $_SESSION['bets']['big'];
		$to_bet_odd = $_SESSION['bets']['odd'];
		$to_bet_even = $_SESSION['bets']['even'];
		$to_bet_black = $_SESSION['bets']['black'];
		$to_bet_red = $_SESSION['bets']['red'];

		$processed_small_big = false;
		$processed_odd_even  = false;
		$processed_black_red = false;

		$spinned_tables = array('small_big'=>'small_big','odd_even'=>'odd_even','black_red'=>'black_red');
		foreach ($spinned_tables as $key=>$value) {
				$key1 = explode("_",$value);
				$key2 = $key1[1];
				$key1 = $key1[0];
				$to_add = 0; $added = 0;
				$i = count($$value);
				$total = (count($$value)>4) ? 4 : count($$value);
				if ($i==0) ${$value}[1] = 1;
				else {
					$start = count($$value)-$total;
					while($i>$start) {
							if (isset(${$value}[$i])) {
								$to_add+= ${$value}[$i];
								if ($i!=$start+1 || $total<4) $added+= ${$value}[$i];
							}
						$i--;
					}
				}
				$to_bet_key1 = 'to_bet_'.$key1;
				$to_bet_key2 = 'to_bet_'.$key2;
				if (!empty($to_add)) {
					${$value}[]=$to_add;
					if (!empty($_SESSION['now_play_'.$value]) && $_SESSION['bets'][$key1]!='-') $$to_bet_key1 = $to_add+$added;
					if (!empty($_SESSION['now_play_'.$value]) && $_SESSION['bets'][$key2]!='-') $$to_bet_key2 = $to_add+$added;
				} else {
					if (empty($$value)) {
						${$value}[1]=1;
						$to_add=1; //to bet on the next round
					} else {
						$i = count($$value);
						$total = (count($$value)>4) ? 4 : count($$value);
						if ($i==0) {
							${$value}[1] = 1;
							$to_add=1; //to bet on the next round
						} else {
							$start = count($$value)-$total;
							while($i>$start) {
								if (isset(${$value}[$i])) {
									$to_add+= ${$value}[$i];
									if ($i!=$start+1 || $total<4) $added+= ${$value}[$i];
								}
								$i--;
							}
						}
					}
					if (!empty($_SESSION['now_play_'.$value]) && $_SESSION['bets'][$key1]!='-') $$to_bet_key1 = $to_add;
					if (!empty($_SESSION['now_play_'.$value]) && $_SESSION['bets'][$key2]!='-') $$to_bet_key2 = $to_add;
				}
				if ($_SESSION['bets'][$key1]!='-') {
					if (!empty($_SESSION['now_play_'.$value])) {
						if ($$to_bet_key1==0) $$to_bet_key1=1;
						$losings_last[$key1] += $_SESSION['bets'][$key1];
						$losings += $$to_bet_key1;
					}
				} elseif ($_SESSION['bets'][$key2]!='-') {
					if (!empty($_SESSION['now_play_'.$value])) {
						if ($$to_bet_key2==0) $$to_bet_key2=1;
						$losings_last[$key2] += $_SESSION['bets'][$key2];
						$losings += $$to_bet_key2;
					}
				}
				//echo $key1.": ".$_SESSION['bets'][$key1].' && BET:'.$$to_bet_key1.'<br />';
				//echo $key2.": ".$_SESSION['bets'][$key2].' && BET:'.$$to_bet_key2.'<br />';
				//echo "small end:<pre>";print_r($small_big);echo "</pre>";
		}
		
		$spinned_tables = array('small'=>'small_big','big'=>'small_big' , 'odd'=>'odd_even','even'=>'odd_even' , 'black'=>'black_red','red'=>'black_red');
		foreach ($spinned_tables as $key=>$value) {
			$spinned_switch = $value.'_spinned';
			//echo $key.": ".$_SESSION['bets'][$key].' && '.$value.'_spinned='.$$spinned_switch.'<br />';
			//if (!empty($_SESSION['now_play_'.$value]) && intval($_SESSION['bets'][$key]) && $$spinned_switch!=$key) $losings_last += $_SESSION['bets'][$key];
			$spinned_switch = $value.'_spinned';
			$processed = 'processed_'.$value;
			if (count($$value)>4 && empty($_SESSION['now_play_'.$value]) && ($_SESSION['bets'][$key]=='-'||$_SESSION['bets'][$key]=='0')) {
				$_SESSION['now_play_'.$value] =  true;
				$_SESSION['now_play_'] =  true; //general
 				$_SESSION['finished_'.$value] = false;
				$$value = array(1=>1);
				$losings_last[$key] = 0;
// 			} elseif (empty($_SESSION['now_play_'.$value]) && !empty($_SESSION['finished_'.$value])) {
// 				$_SESSION['now_play_'.$value] =  false;
// 				$_SESSION['initialized_'.$value] =  false;
			} else {
				$$processed = true;
			}
		}
		
		// process winnings and losings
		$winnings = 0;//$_SESSION['winnings'];
		$losings = 0;//$_SESSION['losings'];
		$came_greek = array(0=>'ZERO',1=>'',2=>'');$bet_won = '';$previous_greek = ''; 
		$previous_sum_black_red = 0; $previous_sum_odd_even = 0; $previous_sum_small_big = 0;
		foreach ($_SESSION['bets'] as $previous_bet => $value) {
			if ($previous_bet=='small' && $value!='-') { 
				$previous_greek .= 'ΜΙΚΡΟ '; 
				$previous_sum_small_big+= $value; 
				//$to_bet_small = $value;
			}
			if ($previous_bet=='big' && $value!='-') { 
				$previous_greek .= 'ΜΕΓΑΛΟ '; 
				$previous_sum_small_big+= $value; 
				//$to_bet_big = $value;
			}
			if ($previous_bet=='odd' && $value!='-') { 
				$previous_greek .= 'ΜΟΝΟ '; 
				$previous_sum_odd_even+= $value; 
				//$to_bet_odd = $value;
			}
			if ($previous_bet=='even' && $value!='-') { 
				$previous_greek .= 'ΖΥΓΟ '; 
				$previous_sum_odd_even+= $value; 
				//$to_bet_even = $value;
			}
			if ($previous_bet=='black' && $value!='-') { 
				$previous_greek .= 'ΜΑΥΡΟ '; 
				$previous_sum_black_red+= $value; 
				//$to_bet_black = $value;
			}
			if ($previous_bet=='red' && $value!='-') { 
				$previous_greek .= 'ΚΟΚΚΙΝΟ '; 
				$previous_sum_black_red+= $value; 
				//$to_bet_red = $value;
			}
			//$losings += ($value!='-') ? $value : 0;
		}
	}
	// make this to add/subtract only if now_bet session var is true!
	if (!empty($_SESSION['now_play_black_red'])) $losings += array_sum($black_red);
	if (!empty($_SESSION['now_play_odd_even'])) $losings += array_sum($odd_even);
	if (!empty($_SESSION['now_play_small_big'])) $losings += array_sum($small_big);
	
	//echo $black_red." | ".$odd_even." | ".$small_big."<br />";
	/*
		$_SESSION['winnings'] = $winnings;
		$_SESSION['losings'] = $losings;
	*/
	
	if (isset($_REQUEST['simulate'])) $came = $spinned;
} else {
	//initialize some variables...
	$small_big_spinned = 'zero';
	$odd_even_spinned = 'zero';
	$black_red_spinned = 'zero';
	$to_bet_small = '-';
	$to_bet_big = '-';
	$to_bet_black = '-';
	$to_bet_red = '-';
	$to_bet_odd = '-';
	$to_bet_even = '-';
}

	if ($spinned!='0') {
			swap($to_bet_big,$to_bet_small);
			swap($to_bet_black,$to_bet_red);
			swap($to_bet_odd,$to_bet_even);
			swap($_SESSION['bets']['big'],$_SESSION['bets']['small']);
			swap($_SESSION['bets']['black'],$_SESSION['bets']['red']);
			swap($_SESSION['bets']['odd'],$_SESSION['bets']['even']);
	}
			$_SESSION['bets'] = array('black'=>$to_bet_black, 'red'=>$to_bet_red, 'odd'=>$to_bet_odd, 'even'=>$to_bet_even, 'small'=>$to_bet_small, 'big'=>$to_bet_big);
			// PLAY NORMAL ONLY AFTER 5 SAME SPINS ON THIS LUCK!
			if (empty($_SESSION['now_play_black_red'])) {
				$to_bet_black = '-';
				$to_bet_red = '-';
			}
			if (empty($_SESSION['now_play_odd_even'])) {
				$to_bet_odd = '-';
				$to_bet_even = '-';
			}
			if (empty($_SESSION['now_play_small_big'])) {
				$to_bet_small = '-';
				$to_bet_big = '-';
			}
			// ON SECOND WIN UNSET THE ABOVE SESSION VARIABLE, EVERYTHING RUNS NORMAL
			// WHILE THE ABOVE VARIABLE IS TRUE, EVERYTHING PLAYS NORMALLY, UNTIL WE ERASE ALL BETS ON THIS LUCK.

//============================================================================
// our forms...
/*
if ($_SESSION['balance']>1016) {
	echo "<h2 class='roulette-h2'>ΠΗΡΕΣ ".($_SESSION['balance']-2500)."€! Θέλεις να συνεχίσεις;</h2>";
	echo '<form class="roulette-form" action="" method="POST">';
	echo '<input type="submit" class="submit" name="new_game" style="width:350px" value="ΝΕΟ ΠΑΙΧΝΙΔΙ"/>';
	echo '</form>';
	echo "<br style='clear:both'/>";
}
elseif ($_SESSION['balance']<910) {
	echo "<div class='alert'>";
	echo "<h2 class='roulette-h2' style='color:#EFEFEF'>ΧΑΝΕΙΣ ".(2500-$_SESSION['balance'])."€! Θέλεις να συνεχίσεις;</h2>";
	echo '<form class="roulette-form" action="" method="POST">';
	echo '<input type="submit" class="submit" name="new_game" style="width:350px" value="ΝΕΟ ΠΑΙΧΝΙΔΙ"/>';
	echo '</form>';
	echo '</div>';
	echo "<br style='clear:both'/>";
}
*/
echo '<form style="float:left;" action="" method="POST" onsubmit="return check_spinned()">';
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
/*
if (!empty($previous_greek) && !empty($_SESSION['now_play_'])) {
	echo "<hr />";
	echo "<tr><td>Έπαιξες: </td><td style='padding-left:5px;font-weight:bold'>$previous_greek ";
	echo "( ";
	if ($previous_sum_black_red) echo $previous_sum_black_red."€ <span style='color:#BBB'>|</span> ";
	if ($previous_sum_odd_even) echo $previous_sum_odd_even."€ <span style='color:#BBB'>|</span> ";
	if ($previous_sum_small_big) echo $previous_sum_small_big."€ ";
	echo ")</td></tr>";
}
*/
if (!empty($came_greek)) {
	echo "<tr><td>Ηρθε: </td><td style='padding-left:5px;font-weight:bold'>".implode(" ",$came_greek)."</td></tr>";
}
echo "</table>";
/*
if ($winnings<1) {
	$_SESSION['losings'] = $_SESSION['losings']-$winnings;
} else {
*/
//echo "<pre>";print_r($losings_last);echo "</pre>";
$kerdh = $_SESSION['tameio']+$winnings;
$xasoura = $_SESSION['losings']+array_sum($losings_last);
$_SESSION['losings'] += array_sum($losings_last);
$_SESSION['losings_last'] = $losings - array_sum($_SESSION['bets']);
$_SESSION['tameio'] += $winnings;

$color = 'black';
$_SESSION['balance'] = 2500 - $losings;
if ($_SESSION['balance']>2500) $color = 'green';
if ($_SESSION['balance']<2500) $color = 'grey';
if ($_SESSION['balance']<0) $color = 'red';

$_SESSION['balance'] = $_SESSION['balance'] + $kerdh - $xasoura;

echo "<hr />ΚΕΦΑΛΑΙΟ: <span style='color:$color;'>".$_SESSION['balance']."€</span> ";
if (!empty($previous_greek)) {
	echo "<span style='color:red'>";
	echo "(ποντάρισματα ";
	echo $losings.'€)';
	echo "</span>";
}

echo "<p style='background:#EFEFEF;padding:3px;'>ΚΕΡΔΗ: ".$kerdh."€ ΧΑΣΟΥΡΑ: ".$xasoura."€</p>";

echo "ταμείο: ".($kerdh-$xasoura).'€ ';
echo "<span style='color:green'>";
echo "(κέρδισες ";
echo $winnings.'€)';
echo "</span>";
echo " <form style='display:inline' action='' method='POST'><input type='submit' name='clear_tameio' value='ΕΚΚΑΘΑΡΙΣΗ' /></form>";

echo "<hr style='clear:both'/>";
//============================================================================
$output = array( 'ΜΑΥΡΑ'=>$black_red, 'ΚΟΚΚ.'=>array(), 'ΜΟΝΑ'=>$odd_even, 'ΖΥΓΑ'=>array(), 'ΜΙΚΡΑ'=>$small_big, 'ΜΕΓΑΛΑ'=>array());
//============================================================================
// show our tables
foreach ($output as $key => $table) {
	//if ($spinned!='new') {
		$bet_style = 'color:red;border-bottom: 3px solid red;font-weight: bold;';
		switch ($key) {
			// $black_red styles are opposite because we switched them earlier in the code
			case "ΜΑΥΡΑ":
				echo "<table class='roulette'>";
				$class = ($black_red_spinned=='black') ? 'background:#000;color:#FFF' : '';
				echo "<tr><th style='$class'><h1>$key</h1></th>";
				$class = ($black_red_spinned=='red') ? 'background:#000;color:#FFF' : '';
				echo "<th style='$class'><h1>ΚΟΚΚ.</h1></th></tr>";
				break;
			case "ΚΟΚΚ.":
				if (!empty($_SESSION['now_play_black_red'])) $bet_style.='background:#006666;';
				echo "<tr><td style='$bet_style'>$to_bet_black</td>";
				echo "<td style='$bet_style'>$to_bet_red</td></tr>";
				foreach ($black_red as $key => $value) {
					$color = ($key<5) ? 'color:blue' : 'color:red';
					echo "<tr><td colspan='2'>";
					echo (!empty($value) && $value!='0') ? '<span style="'.$color.'">'./*$key.'=> '.*/$value.'</span>' : "&#x25cf;";
					echo "</td></tr>";
				}
				echo "</table>";
				break;
			case "ΜΟΝΑ":
				echo "<table class='roulette'>";
				$class = ($odd_even_spinned=='odd') ? 'background:#000;color:#FFF' : '';
				echo "<tr><th style='$class'><h1>$key</h1></th>";
				$class = ($odd_even_spinned=='even') ? 'background:#000;color:#FFF' : '';
				echo "<th style='$class'><h1>ΖΥΓΑ</h1></th></tr>";
				break;
			case "ΖΥΓΑ":
				if (!empty($_SESSION['now_play_odd_even'])) $bet_style.='background:#006666;';
				echo "<tr><td style='$bet_style'>$to_bet_odd</td>";
				echo "<td style='$bet_style'>$to_bet_even</td></tr>";
				foreach ($odd_even as $key => $value) {
					$color = ($key<5) ? 'color:blue' : 'color:red';
					echo "<tr><td colspan='2'>";
					echo (!empty($value) && $value!='0') ? '<span style="'.$color.'">'./*$key.'=> '.*/$value.'</span>' : "&#x25cf;";
					echo "</td></tr>";
				}
				break;
				echo "</table>";
			case "ΜΙΚΡΑ":
				echo "<table class='roulette'>";
				$class = ($small_big_spinned=='small') ? 'background:#000;color:#FFF' : '';
				echo "<tr><th style='$class'><h1>$key</h1></th>";
				$class = ($small_big_spinned=='big') ? 'background:#000;color:#FFF' : '';
				echo "<th style='$class'><h1>ΜΕΓΑΛΑ</h1></th></tr>";
				break;
			case "ΜΕΓΑΛΑ":
				if (!empty($_SESSION['now_play_small_big'])) $bet_style.='background:#006666;';
				echo "<tr><td style='$bet_style'>$to_bet_small</td>";
				echo "<td style='$bet_style'>$to_bet_big</td></tr>";
				foreach ($small_big as $key => $value) {
					$color = ($key<5) ? 'color:blue' : 'color:red';
					echo "<tr><td colspan='2'>";
					echo (!empty($value) && $value!='0') ? '<span style="'.$color.'">'./*$key.'=> '.*/$value.'</span>' : "&#x25cf;";
					echo "</td></tr>";
				}
				echo "</table>";
				break;
		}
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
$_SESSION['bet_tables'] = array('black_red'=>$black_red, 'odd_even'=>$odd_even, 'small_big'=>$small_big);
echo "</div>";
//============================================================================
$session = $_SESSION;
unset($session['spins']);
unset($session['pss']);
echo "<div style='float:right;width:50%;'>";
echo "BETS:<pre>";print_r($session['bets']);echo "</pre>";
unset($session['bets']);
unset($session['bet_tables']);
echo "SESSION:<pre>";print_r($session);echo "</pre>";
echo "</div>";
?>
<script type="text/javascript">
window.onload = function() {
  document.getElementById("spinned").focus();
};
function check_spinned() {
	if (document.getElementById('spinned').value>36) {
		alert('ΜΕΧΡΙ 36!');
		return false;
	}
	if (document.getElementById('spinned').value=='') {
		//alert('ΜΕΧΡΙ 36!');
		return false;
	}
	return true;
}
</script>
</body>
</html>