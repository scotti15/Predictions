<?php // sqltest.php
require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die("Fatal Error");


$sql = "SELECT * FROM `league`";
$all_leagues = mysqli_query($conn,$sql);

$sql = "SELECT * FROM `tournament`";
$all_tournaments = mysqli_query($conn,$sql);

$sql = "SELECT Distinct Year FROM `tournament`";
$all_years = mysqli_query($conn,$sql);


echo <<<_END
<form action="matchupManager.php" method="post" id="submitform">
<pre>
Select Tournament
<input type="submit" name="getmatchups" value="Get Tournament Matchups">
<input type="checkbox" name="populatematch">
</pre>
</form> 
_END;

?>
<select name="LeagueDisplay" form='submitform'>
    <?php
    // use a while loop to fetch data
    // from the $all_categories variable
    // and individually display as an option
    while ($leaugeTable = mysqli_fetch_array(
            $all_leagues,MYSQLI_ASSOC)):;
?>
    <option value="<?php echo $leaugeTable["IDLeague"];
        // The value we usually set is the primary key
    ?>">
        <?php echo $leaugeTable["LeagueName"];
            // To show the category name to the user
        ?>
    </option>
<?php
    endwhile;
    // While loop must be terminated
?>
</select> 
<?php
echo <<<_END
_END;
?>

<select name="TournamentDisplay" form='submitform'>
    <?php
    // use a while loop to fetch data
    // from the $all_categories variable
    // and individually display as an option
    while ($tournamentTable = mysqli_fetch_array(
            $all_tournaments,MYSQLI_ASSOC)):;
?>
    <option value="<?php echo $tournamentTable["IDTournament"];
        // The value we usually set is the primary key
    ?>">
        <?php echo $tournamentTable["Name"];
            // To show the category name to the user
        ?>
    </option>
<?php
    endwhile;
    // While loop must be terminated
?>
</select> 


<select name="TournamentYearDisplay" form='submitform'>
    <?php
    // use a while loop to fetch data
    // from the $all_categories variable
    // and individually display as an option
    while ($tournamentYear = mysqli_fetch_array(
            $all_years,MYSQLI_ASSOC)):;
?>
    <option value="<?php echo $tournamentYear["Year"];
        // The value we usually set is the primary key
    ?>">
        <?php echo $tournamentYear["Year"];
            // To show the category name to the user
        ?>
    </option>
<?php
    endwhile;
    // While loop must be terminated
?>
</select> 

<?php
if (isset($_POST['getmatchups'])) {
    // var_dump($_POST);
    $league =  ($_POST['LeagueDisplay']);
    $year = ($_POST['TournamentYearDisplay']);
    $tournament = ($_POST['TournamentDisplay']);

    $getparticipants = "SELECT * From participant Where IDLeagueType = $league";
    $all_participants = mysqli_query($conn,$getparticipants);
    
    $getopponents = "SELECT * From participant Where IDLeagueType = $league";
    $all_opponents = mysqli_query($conn,$getopponents);

    $getmatchups = "SELECT * From matchup Where IDTournament = $tournament";
    $all_matchups = mysqli_query($conn, $getmatchups);

    if(isset($_POST['populatematch'])) {
        
            $participant =  ($_POST['LeagueParticipantsDisplay']);
            $opponent =  ($_POST['LeagueOpponentDisplay']);
            $matchup =  ($_POST['MatchupDisplay']);
            $tournament = ($_POST['TournamentDisplay']);
            
            $round = intval(substr($matchup,stripos($matchup, '-')+1,1));
            $currentslot = intval(substr($matchup,strrpos($matchup, '-')+2,1));

            $nextround = $round + 1;
            $nextslot = (intval($currentslot) % 2 == 0)  ? intval($currentslot) / 2 : (intval($currentslot) + 1) / 2;
          
        
            $insertparticipant = "UPDATE matchup Set IDFavourite = $participant, IDUnderdog = $opponent Where IDMatchup = '$matchup'";
            $insertparticipantresult = $conn->query($insertparticipant);




            
            if(isset($_POST['participantseed'])) {
                $participantseeding = ($_POST['participantseed']);              
                $opponantseeding = ($_POST['opponentseed']);   
                $insertseed = "UPDATE matchup Set FavouriteSeeding = $participantseeding, UnderdogSeeding = $opponantseeding Where IDMatchup= '$matchup'";
                $insertseedresult = $conn->query($insertseed);
            }
            if(isset($_POST['favouritescore'])) {
                $favouritescore = ($_POST['favouritescore']); 
                var_dump($favouritescore);
                die();
                $underdogscore = ($_POST['underdogscore']); 
                $insertscore = "UPDATE matchup Set FavouriteScore = $favouritescore, UnderdogScore = $underdogscore Where IDMatchup= '$matchup'";
                $insertscoreresult = $conn->query($insertscore);
                $updatepoints = "Update prediction
                Join matchup on prediction.IDMatchup = matchup.IDMatchup
                Set prediction.Points = 
                Case When (Case When (matchup.FavouriteScore > matchup.UnderdogScore) Then matchup.IDFavourite Else matchup.IDUnderdog End) = prediction.IDWinner Then 7 - Abs(prediction.Games - (matchup.FavouriteScore + matchup.UnderdogScore)) 
                Else (prediction.Games - 4) + Least(matchup.FavouriteScore, matchup.UnderdogScore) End
                Where prediction.IDMatchup = '$matchup';";
                $updatepointsresult = $conn->query($updatepoints);

                

                $winner = ($participantscore > $opponentscore) ? $participant : $opponent;
                $winnerseeding  = ($participantscore > $opponentscore) ? $participantseeding : $opponantseeding;  
                $newmatchup = $tournament.'-'.$nextround.'-'.str_pad($nextslot,2,"0", STR_PAD_LEFT);
                if($participantscore > 0 || $opponentscore > 0) {
                    $getseed = "SELECT IDFavourite, FavouriteScore, FavouriteSeeding From matchup Where IDMatchup = '$newmatchup' and IDFavourite is not Null";
                    $seedlist = $conn->query($getseed);
                    $row = $seedlist->fetch_array(MYSQLI_NUM);
                    if (empty($row))
                    {
                        $insertwinner = "Update matchup 
                        Set IDFavourite = $winner, FavouriteSeeding = $winnerseeding 
                        Where IDMatchup = '$newmatchup'";
                        $insertwinnerresult = $conn->query($insertwinner); 
                    }  else {
                        $foundseeding = $row[2];
                        if ($foundseeding > $winnerseeding) {
                            $putfavinunderdogspot = "Update matchup Set IDUnderdog = IDFavourite, UnderdogSeeding = FavouriteSeeding
                            Where IDMatchup = '$newmatchup'";
                            $putfavinunderdogspotresult = $conn->query($putfavinunderdogspot);
                            $insertwinner = "Update matchup 
                            Set IDFavourite = $winner, FavouriteSeeding = $winnerseeding 
                            Where IDMatchup =  '$newmatchup'";       
                            $insertwinnerresult = $conn->query($insertwinner); 
                        }
                        else {
                            $insertwinner = "Update matchup 
                            Set IDUnderdog = $winner, UnderdogSeeding = $winnerseeding 
                            Where IDMatchup =  '$newmatchup'";
                            $insertwinnerresult = $conn->query($insertwinner); 
                        }
                }
                    }
                
        }
    }
?>
<br>
Seeding <input type="text" name="participantseed" form='submitform'> 

<select name="LeagueParticipantsDisplay" form='submitform'>
    <?php
    // use a while loop to fetch data
    // from the $all_categories variable
    // and individually display as an option
    while ($particpants = mysqli_fetch_array(
            $all_participants,MYSQLI_ASSOC)):;
?>
    <option value="<?php echo $particpants["IDParticipant"];
        // The value we usually set is the primary key
    ?>">
        <?php echo $particpants["FirstName"] . ' ' . $particpants["LastName"];
            // To show the category name to the user
        ?>
    </option>
<?php
    endwhile; 
    // While loop must be terminated
?>

</select>     

Score <input type="text" name="participantscore" form='submitform'> 
<br>

Seeding <input type="text" name="opponentseed" form='submitform'> 

<select name="LeagueOpponentDisplay" form='submitform'>
    <?php
    // use a while loop to fetch data
    // from the $all_categories variable
    // and individually display as an option
    while ($opponents = mysqli_fetch_array(
            $all_opponents,MYSQLI_ASSOC)):;
?>
    <option value="<?php echo $opponents["IDParticipant"];
        // The value we usually set is the primary key
    ?>">
        <?php echo $opponents["FirstName"] . ' ' . $opponents["LastName"];
            // To show the category name to the user
        ?>
    </option>
<?php
    endwhile; 
        // While loop must be terminated
?>

</select>     

Score <input type="text" name="opponentscore" form='submitform'> 

<h3>Choose matchup</h3> 

<select name="MatchupDisplay" form='submitform'>
    <?php
    // use a while loop to fetch data
    // from the $all_categories variable
    // and individually display as an option
    while ($matchups = mysqli_fetch_array(
            $all_matchups,MYSQLI_ASSOC)):;
?>
     <option value="<?php echo $matchups["IDMatchup"];
        // The value we usually set is the primary key
    ?>">
        <?php echo $matchups["IDMatchup"];
            // To show the category name to the user
        ?>
    </option>
<?php
    endwhile;
    // While loop must be terminated
?>
</select> 

<input type="submit" name="submitmatchup" value="Submit Match up" form='submitform'>

<?php

$getmatchups = "SELECT Substring(IDMatchup,Locate('-',IDMatchup)+1,Length(IDMatchup))
, Round
, IDTournament
, CONCAT(participant.FirstName,' ',participant.LastName)
, CONCAT(p2.FirstName,' ',p2.LastName)
, FavouriteScore
, UnderdogScore
, StartTime
, FavouriteSeeding
, UnderdogSeeding
FROM matchup JOIN participant on matchup.IDFavourite = participant.IDParticipant 
                JOIN participant p2 on matchup.IDUnderdog = p2.IDParticipant  Where IDTournament = $tournament Order by IDMatchup";
         
$matchuplist = $conn->query($getmatchups);
if (!$matchuplist) die("Select From matchup access failed");
$rows = $matchuplist->num_rows;
for ($j = 0; $j < $rows; ++$j) {
    $row = $matchuplist->fetch_array(MYSQLI_NUM);// 
    $r0 = htmlspecialchars($row[0]);// MatchupID
    $r1 = htmlspecialchars($row[1]);// Round
    $r2 = htmlspecialchars($row[2]);// IDTournament
    $r3 = htmlspecialchars($row[3]);// Participant full name
    $r4 = htmlspecialchars($row[4]);// IDUnderdog
    $r5 = htmlspecialchars($row[5]);// FavouriteScore
    $r6 = htmlspecialchars($row[6]);// UnderdogScore
    $r7 = htmlspecialchars($row[7]);// StartTime
    $r8 = htmlspecialchars($row[8]);// FavouriteSeeding
    $r9 = htmlspecialchars($row[9]);// UnderdogSeeding
    echo <<<_END
<pre>
</pre>
<form action='matchupManager.php' method='post'>
<input type='hidden' name='delete' value='yes'>
<input type='hidden' name='idmatchup' value='$r0'>
$r0 
<input type='text' name='fseed' value='$r8'>
<input type='text' name='Participant' value='$r3'>
<input type='text' name='fscore' value='$r5' >
<br>     
$r0
<input type='text' name='useed' value='$r9'>
<input type='text' name='Underdog' value='$r4'>
<input type='text' name='uscore' value='$r6'>
<input type='submit' value='Delete matchup'>
_END;
}
$matchuplist->close();
}

/*  ?>

<form action='matchupManager.php' method='post'>
<input type='hidden' name='submitform' value= 'yes'>
<input type='hidden' name='matchups' value= '<?php $matchups ?>'>
<input type='submit' value='Submit Participant'></form>
<?php
*/
function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}
?>
