<?php // administration.php
require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die("Fatal Error");

echo <<<_END
<form action="predictionManager.php" method="post" id="submitform">
<pre>
Select Tournament
<input type="submit" name="showmatchups" value="Get Tournament Matchups">
<input type="checkbox" name="populatematch">
</pre>
</form> 
_END;

$sql = "SELECT * FROM `predictor`";
$all_predictors = mysqli_query($conn,$sql);

$sql = "SELECT * FROM `league`";
$all_leagues = mysqli_query($conn,$sql);

$sql = "SELECT * FROM `tournament`";
$all_tournaments = mysqli_query($conn,$sql);

$sql = "SELECT Distinct Year FROM `tournament`";
$all_years = mysqli_query($conn,$sql);

// var_dump($_POST);
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

<?php
// if (isset($_POST['matchupforprediction'])) {
// var_dump("Submit prediction is set ! ".$_POST['matchupforprediction']);
// }

if (isset($_POST['showmatchups'])|| isset($_POST ["matchupforprediction"])) {
    // var_dump($_POST);
    // $league =  ($_POST['LeagueDisplay']);
    // $year = ($_POST['TournamentYearDisplay']);
    $tournament = ($_POST['TournamentDisplay']);
    if(isset($_POST['MatchupDisplay'])) {
    $matchupforprediction = ($_POST['MatchupDisplay']);
    }
    // die();
    
    $showmatchups = "SELECT * From matchup Where IDTournament = $tournament";
    $all_matchups = mysqli_query($conn, $showmatchups);
?>

<select name="PredictorDisplay" form='submitform'>
    <?php
    // use a while loop to fetch data
    // from the $all_categories variable
    // and individually display as an option
    while ($predictorTable = mysqli_fetch_array(
            $all_predictors,MYSQLI_ASSOC)):;
?>
    <option value="<?php echo $predictorTable["IDPredictor"];
        // The value we usually set is the primary key
    ?>">
        <?php echo $predictorTable["Name"];
            // To show the category name to the user
        ?>
    </option>
<?php
    endwhile;
    // While loop must be terminated
?>
</select> 
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

    <input type='submit' name = 'matchupforprediction' value='Get matchup for prediction' form='submitform'>

    
<?php

if(isset($_POST['MatchupDisplay'])) {
    $showmatchupforprediction = "SELECT Substring(IDMatchup,Locate('-',IDMatchup)+1,Length(IDMatchup))
    , Round
    , IDTournament
    , CONCAT(participant.FirstName,' ',participant.LastName)
    , CONCAT(p2.FirstName,' ',p2.LastName)
    , FavouriteScore
    , UnderdogScore
    , StartTime
    , FavouriteSeeding
    , UnderdogSeeding
    , IDMatchup
    , IDFavourite
    , IDUnderdog
    FROM matchup JOIN participant on matchup.IDFavourite = participant.IDParticipant 
                    JOIN participant p2 on matchup.IDUnderdog = p2.IDParticipant  Where IDMatchup = '$matchupforprediction' 
                    Order by IDMatchup";
    
    $matchuplistforprediction = $conn->query($showmatchupforprediction);
    if (!$matchuplistforprediction) die("Select From matchup access failed");
    $rows = $matchuplistforprediction->num_rows;
    for ($j = 0; $j < $rows; ++$j) {
        $row = $matchuplistforprediction->fetch_array(MYSQLI_NUM);// 
        $r0 = htmlspecialchars($row[0]);// Matchup
        $r1 = htmlspecialchars($row[1]);// Round
        $r2 = htmlspecialchars($row[2]);// IDTournament
        $r3 = htmlspecialchars($row[3]);// Participant full name
        $r4 = htmlspecialchars($row[4]);// IDUnderdog
        $r5 = htmlspecialchars($row[5]);// FavouriteScore
        $r6 = htmlspecialchars($row[6]);// UnderdogScore
        $r7 = htmlspecialchars($row[7]);// StartTime
        $r8 = htmlspecialchars($row[8]);// FavouriteSeeding
        $r9 = htmlspecialchars($row[9]);// UnderdogSeeding
        $r10 = htmlspecialchars($row[10]);// IDMatchup
        $r11 = htmlspecialchars($row[11]);// IDFavourite
        $r12 = htmlspecialchars($row[12]);// IDUnderdog
        echo <<<_END
    <pre>
    </pre>
    <form action='savePrediction.php' method='post' id='submitprediction'>
    <input type='hidden' name='idmatchup' value='$r10'>
    $r0 
    <input type='text' name='fseed' value='$r8'>
    <input type='text' name='Favourite' value='$r3'>
    <input type='hidden' name='idFavourite' value='$r11'>
    <input type='radio' name='predicted' value=Favourite>
        <select name="numberofgames">
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6">6</option>
        <option value="7">7</option>
    </select>
    <br>     
    $r0
    <input type='text' name='useed' value='$r9'>
    <input type='text' name='Underdog' value='$r4'>
    <input type='hidden' name='IDUnderdog' value='$r12'> 
    <input type='text' name='underdogscore' >
    <input type='radio' name='predicted' value=Underdog>
    <input type='submit' name = 'prediction' value='Submit Prediction'>
    <input type='submit' name = 'result' value='Submit Result'>
    _END;
    }
    $matchuplistforprediction->close();
}

    $showmatchups = "SELECT Substring(IDMatchup,Locate('-',IDMatchup)+1,Length(IDMatchup))
    , Round
    , IDTournament
    , CONCAT(participant.FirstName,' ',participant.LastName)
    , CONCAT(p2.FirstName,' ',p2.LastName)
    , FavouriteScore
    , UnderdogScore
    , StartTime
    , FavouriteSeeding
    , UnderdogSeeding
    , IDMatchup
    , IDFavourite
    , IDUnderdog
    FROM matchup JOIN participant on matchup.IDFavourite = participant.IDParticipant 
                    JOIN participant p2 on matchup.IDUnderdog = p2.IDParticipant  Where IDTournament = $tournament Order by IDMatchup";
    
    $matchuplist = $conn->query($showmatchups);
    if (!$matchuplist) die("Select From matchup access failed");
    $rows = $matchuplist->num_rows;
    for ($j = 0; $j < $rows; ++$j) {
        $row = $matchuplist->fetch_array(MYSQLI_NUM);// 
        $p0 = htmlspecialchars($row[0]);// Matchup
        $p1 = htmlspecialchars($row[1]);// Round
        $p2 = htmlspecialchars($row[2]);// IDTournament
        $p3 = htmlspecialchars($row[3]);// Participant full name
        $p4 = htmlspecialchars($row[4]);// IDUnderdog
        $p5 = htmlspecialchars($row[5]);// FavouriteScore
        $p6 = htmlspecialchars($row[6]);// UnderdogScore
        $p7 = htmlspecialchars($row[7]);// StartTime
        $p8 = htmlspecialchars($row[8]);// FavouriteSeeding
        $p9 = htmlspecialchars($row[9]);// UnderdogSeeding
        $p10 = htmlspecialchars($row[10]);// IDMatchup
        $p11 = htmlspecialchars($row[11]);// IDFavourite
        $p12 = htmlspecialchars($row[12]);// IDUnderdog
        echo <<<_END
    <pre>
    </pre>
    $p0 
    <input type='text' value='$p8'>
    <input type='text' value='$p3'>
    <input type='text' name='favouritescore' >
    <br>     
    $p0
    <input type='text' value='$p9'>
    <input type='text'  value='$p4'>
    <input type='text' name='underdogscore' >
    _END;
    }
    $matchuplist->close();
    

function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}

}
?>