<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Predictions</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round|Open+Sans">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<style type="text/css">
.bs-example{
margin: 20px;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
$('[data-toggle="tooltip"]').tooltip();   
});
</script>
</head>
<body>
<div class="bs-example">
<div class="container">
<div class="row">
<div class="col-md-12">
<div class="page-header clearfix">
</div>
<?php 
// Begin Admin
require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die("Fatal Error");

$sql = "SELECT * FROM `tournament` Order by IDTournament Desc";
$all_tournaments = mysqli_query($conn,$sql);

$sql = "SELECT Distinct LeagueName FROM `league`";
$all_leagues = mysqli_query($conn,$sql);

$sql = "SELECT * FROM `predictor`";
$all_predictors = mysqli_query($conn,$sql);

$sql = "SELECT * FROM `prediction`";
$all_predictions = mysqli_query($conn,$sql);

?>

<form action="Predictions.php" method="post" id="submitform">
<input type="submit" value="Admin Tools" name="AdminTools">
<input type="submit" value="Tournament Management" name="TournamentManagementTools">
<input type="submit" value="Prediction Page" name="PredictionTools">
<input type="submit" value="Results Page" name="ResultsPage">
</form> 


<?php

if(isset($_POST['ResultsPage'])) {
echo "Results page !";
var_dump($_POST);
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
<input type='submit' name='ShowResults' form='submitform' value='Show Results'>
<?php
}


if(isset($_POST['ShowResults'])) {
    $tournament = ($_POST['TournamentDisplay']);
    $gettournamentname = "Select Name, Favourites From tournament Where IDTournament = $tournament";
    $gettournamentnameresults = $conn->query($gettournamentname);
    $row = $gettournamentnameresults->fetch_array(MYSQLI_NUM);
    $tournamentname = htmlspecialchars($row[0]);
    $favourites = htmlspecialchars($row[1]);
    $hidden = $favourites == 'Yes' ? 'invisible' : 'visible';
    
    $getlearderboard = "SELECT 
    pr.Name
    ,Sum(p.Points) as Points
    FROM `matchup` m 
    Join prediction p on m.IDMatchup = p.IDMatchup
    Join tournament t on m.IDTournament = t.IDTournament
    Join predictor pr on p.IDPredictor = pr.IDPredictor
    WHERE t.IDTournament = $tournament
    Group by pr.Name
    Order by Sum(p.Points) Desc, pr.Name;";
    $leaderboard = mysqli_query($conn,$getlearderboard);

      if (mysqli_num_rows($leaderboard) > 0) {
    ?> 
    
    <h1> <?php echo $tournamentname ?></h1>
    <h2 class="pull-left">Leaderboard</h2>
    <table class='table table-bordered table-striped'>
    <tr>
    <td>Name</td>
    <td>Points</td>
    </tr>
    <?php
    $i=0;
    while($row = mysqli_fetch_array($leaderboard)) {
    ?>
    <tr>
    <td><?php echo $row["Name"]; ?></td>
    <td><?php echo $row["Points"]; ?></td>
    </tr>
    <?php
    $i++;
    }
    ?>
    </table>
    <?php
    }
    else{
    echo "No result found";
    }
    $getresults ="SELECT Concat(fav.FirstName,' ',fav.LastName) as Favourite 
    ,Concat(und.FirstName,' ',und.LastName) as Underdog 
    ,pr.Name 
    ,Concat(pa.FirstName,' ',pa.LastName) as PredictedWinner 
    ,p.Games as PredictedGames 
    ,Case When m.FavouriteScore > m.UnderdogScore Then Concat(fav.FirstName,' ',fav.LastName) Else Concat(und.FirstName,' ',und.LastName) End as Winner 
    ,m.FavouriteScore + m.UnderdogScore as Games 
    ,p.Points 
    FROM `matchup` m Join prediction p on m.IDMatchup = p.IDMatchup 
    Join participant pa on p.IDWinner = pa.IDParticipant 
    Join participant fav on m.IDFavourite = fav.IDParticipant 
    Join participant und on m.IDUnderdog = und.IDParticipant 
    Join predictor pr on p.IDPredictor = pr.IDPredictor 
    WHERE m.IDTournament = $tournament Order by m.IDMatchup, pr.Name";
    
$result = mysqli_query($conn,$getresults);
?>
<?php
if (mysqli_num_rows($result) > 0) {
    if ($favourites == "Yes") {

        ?>
        <h2 class="pull-left">Results Table</h2>
        <table class='table table-bordered table-striped'>
        <tr>
        <td>Name</td>
        <td>Predicted Winner</td>
        <td>Winner</td>
        <td>Points</td>
        </tr>
        <?php
        $i=0;
        while($row = mysqli_fetch_array($result)) {
        ?>
        <tr>
        <td><?php echo $row["Name"]; ?></td>
        <td><?php echo $row["PredictedWinner"]; ?></td>
        <td><?php echo $row["Winner"]; ?></td>
        <td><?php echo $row["Points"]; ?></td>
        </tr>
        <?php
        $i++;
        }
        ?>
        </table>
        <?php
        
    } else {
        
?>
<h2 class="pull-left">Results Table</h2>
<table class='table table-bordered table-striped'>
<tr>
<td>Name</td>
<td>Predicted Winner</td>
<td>Predicted Games</td>
<td>Winner</td>
<td>Games</td>
<td>Points</td>
</tr>
<?php
$i=0;
while($row = mysqli_fetch_array($result)) {
?>
<tr>
<td><?php echo $row["Name"]; ?></td>
<td><?php echo $row["PredictedWinner"]; ?></td>
<td><?php echo $row["PredictedGames"]; ?></td>
<td><?php echo $row["Winner"]; ?></td>
<td><?php echo $row["Games"]; ?></td>
<td><?php echo $row["Points"]; ?></td>
</tr>
<?php
$i++;
}
?>
</table>
<?php

    }
}
else{
echo "No result found";
}
    die();
}

if(isset($_POST['matchupforprediction'])) {
    echo "Matchup for Predictions";
    var_dump($_POST);
    $predictor = ($_POST['PredictorDisplay']);
    $matchupforprediction = ($_POST['IDMatchup']);
    $showmatchupforprediction = "SELECT Substring(IDMatchup,Locate('-',IDMatchup)+1,Length(IDMatchup))
    , Round
    , matchup.IDTournament
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
    , t.Favourites
    FROM matchup JOIN participant on matchup.IDFavourite = participant.IDParticipant 
                    JOIN participant p2 on matchup.IDUnderdog = p2.IDParticipant  
                    JOIN tournament t on matchup.IDTournament = t.IDTournament Where IDMatchup = '$matchupforprediction' 
                    Order by IDMatchup";
    // var_dump($showmatchupforprediction);
    // die();
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
        $r13 = htmlspecialchars($row[13]);// Favourites
        $r13 = $r13 == 'Yes' ? 'hidden' : '';
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
        <select name="numberofgames" $r13>
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
    <input type='hidden' name='PredictorDisplay' value='$predictor'> 
    <input type='radio' name='predicted' value=Underdog>
    <input type='submit' name = 'prediction' value='Submit Prediction'>
    <br>     
    _END;
    }
    $matchuplistforprediction->close();
}


if(isset($_POST['populatematch'])) {
    $participant =  ($_POST['IDFavourite']);
    $opponent =  ($_POST['IDUnderdog']);
    $matchup =  ($_POST['IDMatchup']);
    $newmatchup =  ($_POST['IDNextMatchup']);
    if(isset($_POST['Favourites'])) {
    $tournamentrounds =  ($_POST['rounds']);
    $favourites =  ($_POST['Favourites']);
    }
    $tournament = substr($matchup,0,2);
    $round = intval(substr($matchup,stripos($matchup, '-')+1,1));
    $nextround = $round + 1;
    $currentslot = intval(substr($matchup,strrpos($matchup, '-')+2,1));



    $nextslot = (intval($currentslot) % 2 == 0)  ? intval($currentslot) / 2 : (intval($currentslot) + 1) / 2;
  
    $insertparticipant = "UPDATE matchup Set IDFavourite = $participant, IDUnderdog = $opponent Where IDMatchup = '$matchup'";
    $insertparticipantresult = $conn->query($insertparticipant);




    
    if(isset($_POST['FavouriteSeeding'])) {
        $favouriteseed = ($_POST['FavouriteSeeding']);              
        $underdogseed = ($_POST['UnderdogSeeding']);   
        $insertseed = "UPDATE matchup Set FavouriteSeeding = $favouriteseed, UnderdogSeeding = $underdogseed Where IDMatchup= '$matchup'";
        $insertseedresult = $conn->query($insertseed);
    }
    if(isset($_POST['favouritescore'])) {
        $favouritescore = ($_POST['favouritescore']); 
        $underdogscore = ($_POST['underdogscore']); 
        $insertscore = "UPDATE matchup Set FavouriteScore = $favouritescore, UnderdogScore = $underdogscore Where IDMatchup= '$matchup'";
        $insertscoreresult = $conn->query($insertscore);

        if($favourites == 'No')  {
        $updatepoints = "Update prediction
        Join matchup on prediction.IDMatchup = matchup.IDMatchup
        Set prediction.Points = 
        Case When (Case When (matchup.FavouriteScore > matchup.UnderdogScore) Then matchup.IDFavourite Else matchup.IDUnderdog End) = prediction.IDWinner Then 8 - Abs(prediction.Games - (matchup.FavouriteScore + matchup.UnderdogScore)) 
        Else (prediction.Games - 4) + Least(matchup.FavouriteScore, matchup.UnderdogScore) End
        Where prediction.IDMatchup = '$matchup';";
        } else {
        $upsetfactor = ($favouritescore < $underdogscore ? 2 : 1);
        $updatepoints = "Update prediction
        Join matchup on prediction.IDMatchup = matchup.IDMatchup
        Set prediction.Points = (Case When prediction.IDWinner = 
        (Case When matchup.FavouriteScore < matchup.UnderdogScore Then matchup.IDUnderdog Else matchup.IDFavourite End) 
        Then matchup.Round Else 0 End) * Case When matchup.FavouriteScore < matchup.UnderdogScore Then 2 Else 1 End
        Where prediction.IDMatchup = '$matchup';";
        $defaultfavpick = "Update prediction p
        Join matchup m on p.IDMatchup = matchup.IDMatchup
        Set IDWinner = matchup.IDFavourite
        Where matchup.IDmatchup = '$matchup'";
        $defaultfavpickresult = $conn->query($defaultfavpick);
        }
        $updatepointsresult = $conn->query($updatepoints);

        

        $winner = ($favouritescore > $underdogscore) ? $participant : $opponent;
        $winnerseeding  = ($favouritescore > $underdogscore) ? $favouriteseed : $underdogseed;  
        if($favouritescore > 0 || $underdogscore > 0) {
            $getseed = "SELECT IDFavourite, FavouriteScore, FavouriteSeeding From matchup Where IDMatchup = '$newmatchup' and IDFavourite is not Null";

            $seedlist = $conn->query($getseed);
            $row = $seedlist->fetch_array(MYSQLI_NUM);
            if (empty($row) && $nextround <= $tournamentrounds)
            {
                echo "No next seed";
                $insertwinner = "Update matchup 
                Set IDFavourite = $winner, FavouriteSeeding = $winnerseeding
                Where IDMatchup = '$newmatchup'";
                $insertwinnerresult = $conn->query($insertwinner); 
            }  else {
                $foundseeding = $row[2];
                echo "Next seed found : ";
                if ($foundseeding >= $winnerseeding) {
                    echo "Found seed better";
                    $putfavinunderdogspot = "Update matchup Set IDUnderdog = IDFavourite, UnderdogSeeding = FavouriteSeeding
                    Where IDMatchup = '$newmatchup'";
                    var_dump("Put F in U spot ".$putfavinunderdogspot);
                    $putfavinunderdogspotresult = $conn->query($putfavinunderdogspot);
                    $insertwinner = "Update matchup 
                    Set IDFavourite = $winner, FavouriteSeeding = $winnerseeding 
                    Where IDMatchup =  '$newmatchup'";       
                    var_dump("Insert Winner after PFIU ".$insertwinner);
                    $insertwinnerresult = $conn->query($insertwinner); 
                }
                else {
                    echo "Found seed worse";
                    $insertwinner = "Update matchup 
                    Set IDUnderdog = $winner, UnderdogSeeding = $winnerseeding 
                    Where IDMatchup =  '$newmatchup'";    
                    echo "Found Seeding = ". $foundseeding;
                    echo "Winner Seeding = ". $winnerseeding;
                    var_dump("Insert Winner ".$insertwinner);
                    $insertwinnerresult = $conn->query($insertwinner); 
                }
        }
            }
        
}
}

if(isset($_POST['GetInfoForPredictions'])) {
    echo "Get Tournament for Prediction";
    var_dump($_POST);
    $tournament = $_POST['TournamentDisplay'];
    $showmatchups = "SELECT * From matchup Where IDTournament = $tournament";
    $all_matchups = mysqli_query($conn, $showmatchups);
    ?>
    <br>
    
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

<select name="IDMatchup" form='submitform'>
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
<input type='submit' form='submitform' name='matchupforprediction' value='Get matchup for prediction' >
<input type ='hidden' name='test' form='getpredictions' value='test'>
<?php





if(isset($_POST['IDMatchup'])) {
    // die();
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
    
    die();
}


if (!empty($_POST['Tournament'])
)
    {
    $name = get_post($conn, 'Tournament');
    $description = get_post($conn, 'Description');
    $year = get_post($conn, 'Year');
    $rounds = get_post($conn, 'Rounds');
    $favourites = get_post($conn, 'Favourites');
    $idleague = get_post($conn, 'LeagueDisplay');
    // Create Tournament in Table
    $inserttournament = "INSERT INTO tournament (Name, Description, Year, Rounds, Favourites, IDLeagueType) VALUES" .
    "('$name', '$description', '$year', '$rounds', '$favourites', '$idleague')";
    $inserttournamentresult = $conn->query($inserttournament);
    if (!$inserttournamentresult) echo "INSERT failed<br><br>";
    
    // Insert all matchups for tournament in Matchup table
    $tid = $conn->query('Select Max(IDTournament) as MaxTournament From tournament');
    $tournamentid = $tid->fetch_array()[0] ?? '';
    for ($r = $rounds; $r > 0 ; --$r) {
        for ($m = 1; $m < (pow(2, $r)) / 2 + 1; ++$m) {
            $round = $rounds - $r + 1;
            $matchupid = $tournamentid . '-' . $round  . '-' . sprintf('%02d', $m);
            $nextround = ceil($m / 2);
            if ($round != $rounds) {
                $nextmatchupid = $tournamentid . '-' . ($round+1)  . '-' . sprintf('%02d', $nextround);
            } else {
                $nextmatchupid = null;
            }
            $insertmatchups = "INSERT Into matchup (IDMatchup, Round, IDTournament, IDNextMatchup) Values " .   "('$matchupid','$round','$tournamentid','$nextmatchupid')";
            $inserttournamentmatchups = $conn->query($insertmatchups);           
            if (!$inserttournamentmatchups) echo "INSERT matchups failed<br><br>";

        }
    }
    $populateprediction = "INSERT INTO prediction (IDMatchup, IDPredictor) SELECT IDMatchup, IDPredictor From matchup, predictor Where IDTournament = '$tournamentid'";
    $populatepredictionresult = $conn->query($populateprediction);
    if (!$populatepredictionresult) echo "INSERT prediction failed<br><br>";

    //Create lines in Results table to be filled later
}

if(isset($_POST['PredictionTools'])) {

    ?>
    <form id='getpredictions' method='post' action="Predictions.php">
    </form>
<select name="TournamentDisplay" form='getpredictions'>
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
<input type='submit' name='GetInfoForPredictions' form='getpredictions' value='Get Tournament for Prediction'>
<?php

}


if(isset($_POST['GetTournamentMatchups'])) {
    $tournament = $_POST['TournamentDisplay'];
    $getleagues = "SELECT Distinct IDLeagueType from tournament Where IDTournament = $tournament";
    $league = $conn->query($getleagues);
    if (!$getleagues) die("Select From matchup access failed");
    
    $row = $league->fetch_array(MYSQLI_NUM); 
    $league = htmlspecialchars($row[0]);

    
$getmatchups = "SELECT * From matchup Where IDTournament = $tournament and Round = 1";
$all_matchups = mysqli_query($conn, $getmatchups);

$getfavourites = "SELECT * From participant Where IDLeagueType = $league Order by FirstName";
$all_favourites = mysqli_query($conn,$getfavourites);

$getunderdogs = "SELECT * From participant Where IDLeagueType = $league Order by FirstName";
$all_underdogs = mysqli_query($conn,$getunderdogs);


    echo  "Register Scores";
    ?>
    <br>     
   

    
<select name="IDMatchup" form='submitform'>
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


<br>
Seeding <input type="text" name="FavouriteSeeding" form='submitform'> 

<select name="IDFavourite" form='submitform'>
    <?php
    // use a while loop to fetch data
    // from the $all_categories variable
    // and individually display as an option
    while ($favourites = mysqli_fetch_array(
            $all_favourites,MYSQLI_ASSOC)):;
?>
    <option value="<?php echo $favourites["IDParticipant"];
        // The value we usually set is the primary key
    ?>">
        <?php echo $favourites["FirstName"] . ' ' . $favourites["LastName"];
            // To show the category name to the user
        ?>
    </option>
<?php
    endwhile; 
    // While loop must be terminated
?>

</select>     

<!-- Score <input type="text" name="favouritescore" form='submitform'> -->
<br> 

Seeding <input type="text" name="UnderdogSeeding" form='submitform'> 

<select name="IDUnderdog" form='submitform'>
    <?php
    // use a while loop to fetch data
    // from the $all_categories variable
    // and individually display as an option
    while ($underdogs = mysqli_fetch_array(
            $all_underdogs,MYSQLI_ASSOC)):;
?>
    <option value="<?php echo $underdogs["IDParticipant"];
        // The value we usually set is the primary key
    ?>">
        <?php echo $underdogs["FirstName"] . ' ' . $underdogs["LastName"];
            // To show the category name to the user
        ?>
    </option>
<?php
    endwhile; 
        // While loop must be terminated
?>

</select>
     <input type="submit" name="populatematch" value="Submit Match up" form='submitform'>

<br> 
<?php
    $showmatchups = "SELECT Substring(IDMatchup,Locate('-',IDMatchup)+1,Length(IDMatchup))
    , Round
    , matchup.IDTournament
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
    , IDNextMatchup
    , t.Rounds
    , t.Favourites
    FROM matchup JOIN participant on matchup.IDFavourite = participant.IDParticipant 
                    JOIN participant p2 on matchup.IDUnderdog = p2.IDParticipant 
                    Join tournament t on matchup.IDTournament = t.IDTournament  Where matchup.IDTournament = $tournament 
                    Order by IDMatchup";
    $matchuplist = $conn->query($showmatchups);
    if (!$matchuplist) die("Select From matchup access failed");$rows = $matchuplist->num_rows;
    for ($j = 0; $j < $rows; ++$j) {
        $row = $matchuplist->fetch_array(MYSQLI_NUM);// 
        $p0 = htmlspecialchars($row[0]);// Matchup
        $p1 = htmlspecialchars($row[1]);// Round
        $p2 = htmlspecialchars($row[2]);// IDTournament
        $p3 = htmlspecialchars($row[3]);// Favourite full name
        $p4 = htmlspecialchars($row[4]);// Underdog full name
        $p5 = htmlspecialchars($row[5]);// FavouriteScore
        $p6 = htmlspecialchars($row[6]);// UnderdogScore
        $p7 = htmlspecialchars($row[7]);// StartTime
        $p8 = htmlspecialchars($row[8]);// FavouriteSeeding
        $p9 = htmlspecialchars($row[9]);// UnderdogSeeding
        $p10 = htmlspecialchars($row[10]);// IDMatchup
        $p11 = htmlspecialchars($row[11]);// IDFavourite
        $p12 = htmlspecialchars($row[12]);// IDUnderdog
        $p13 = htmlspecialchars($row[13]);// IDNextMatchup
        $p14 = htmlspecialchars($row[14]);// Rounds
        $p15 = htmlspecialchars($row[15]);// Favourites
        echo <<<_END
 
    <form action='Predictions.php' method='post' form='submitform'>
    $p0
    <input type='hidden' value='$p8' name='FavouriteSeeding'>
    <input type='hidden' value='$p13' name='IDNextMatchup'>
    <input type='text' value='$p8'>
    <input type='text' value='$p3'>
    <input type='text' name='favouritescore' value='$p5' >
    <input type='hidden' value='$p11' name='IDFavourite'>
    <br>     
    $p0
    <input type='text' value='$p9'>
    <input type='hidden' value='$p10' name='IDMatchup'>
    <input type='hidden' value='$p9' name='UnderdogSeeding'>
    <input type='text'  value='$p4'>
    <input type='hidden' value='$p12' name='IDUnderdog'>
    <input type='text' name='underdogscore' value='$p6' >
    <input type='hidden' name='populatematch'>
    <input type='hidden' name='rounds' value='$p14'>
    <input type='hidden' name='Favourites' value='$p15'>
    <input type='submit' value='Submit Scores' name='SubmitScores'>
    <br>    
    </form> 
    _END;
    }
    $matchuplist->close();
    die();
}



if(isset($_POST['AdminTools'])) {
    
$sql = "SELECT * FROM `league`";
$all_leagues = mysqli_query($conn,$sql);


echo <<<_END
<form action="Predictions.php" method="post" id="addtournamentdetails">
<pre>
Name <input type="text" name="Name"> <input type="submit" value="Add New Predictor" name="CreatePredictor">
League <input type="text" name="League"> <input type="submit" value="Add New League" name="CreateLeague">
First Name <input type="text" name="FirstName"> Last Name <input type="text" name="LastName"> <input type="submit" Name="CreateParticipant" value="Add New Participant">
Tournament Name <input type="text" name="Tournament"> Description <input type="text" name="Description">Year<input type="text" name="Year">  Rounds<input type="text" name="Rounds">  Favourites<input type="text" name="Favourites"> <input type="submit" name="CreateTournament" value="Add New Tournament">


_END;
?>

</pre>
</form>
<select name="LeagueDisplay" form="addtournamentdetails">
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
</form>
<input type="submit" name="GetTournamentMatchup" form='submitform' value='Get Tournament'>

<?php
}

if(isset($_POST['TournamentManagementTools'])) {
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
<input type="submit" name="GetTournamentMatchups" form='submitform' value='Get Tournament'>

<?php
    }
    
//SHOW LIST OF AVAILABLE TOURNAMENTS    

if(isset($_POST['CreatePredictor'])) {
    $name = get_post($conn, 'Name');
    $insertpredictor = "INSERT INTO predictor (Name) VALUES" .
    "('$name')";
    $insertpredictorresult = $conn->query($insertpredictor);
    if (!$insertpredictorresult) echo "INSERT failed<br><br>";
    }
    
if(isset($_POST['CreateParticipant'])) {
    $firstname = get_post($conn, 'FirstName');
    $lastname = get_post($conn, 'LastName');
    $idleague = get_post($conn, 'LeagueDisplay');
    $insertparticipant = "INSERT INTO Participant (FirstName, LastName, IDLeagueType) VALUES" .
    "(Trim('$firstname'), Trim('$lastname'), '$idleague')";
    $insertparticipantresult = $conn->query($insertparticipant);
    if (!$insertparticipantresult) echo "INSERT failed<br><br>";
    }
    

if(isset($_POST['CreateLeague'])) {
    $league = get_post($conn, 'League');
    $insertleague = "INSERT INTO league (LeagueName) VALUES" .
    "('$league')";
    $insertleagueresult = $conn->query($insertleague);
    if (!$insertleagueresult) echo "INSERT League failed<br><br>";
    }
    
    
    function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}
?>
</div>
</div>        
</div>
</div>
</body>
</html>