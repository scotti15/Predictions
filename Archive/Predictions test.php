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

$sql = "SELECT * FROM `league`";
$all_leagues = mysqli_query($conn,$sql);

$sql = "SELECT * FROM `predictor`";
$all_predictors = mysqli_query($conn,$sql);

$sql = "SELECT * FROM `prediction`";
$all_predictions = mysqli_query($conn,$sql);

// BEGIN FLIP PICK
if (isset($_POST['flip']) && isset($_POST['idmatchup']))
{
$idmatchup = $_POST['idmatchup'];
$query = "UPDATE prediction p 
Join matchup m on p.IDMatchup = m.IDMatchup
Set p.IDWinner = Case When p.IDWinner = m.IDFavourite Then m.IDUnderdog Else m.IDFavourite End 
Where p.IDMatchup = '$idmatchup' and IDPredictor = 2";
$result = $conn->query($query);
if (!$result) echo "FLIP failed<br><br>";
}
// END FLIP PICK

?>

<form action="Predictions test.php" method="post" id="submitform" >
<input type="submit" value="Admin Tools" name="AdminTools" >
<input type="submit" value="Tournament Management" name="TournamentManagementTools">
<input type="submit" value="Prediction Page" name="PredictionTools">
<input type="submit" value="Results Page" name="ResultsPage">
</form> 


<select name="LeagueDisplay" form="addtournamentdetails">
<?php
// use a while loop to fetch data
// from the $all_categories variable
// and individually display as an option
while ($leagueTable = mysqli_fetch_array(
        $all_leagues,MYSQLI_ASSOC)):;
?>
<option value="<?php echo $leagueTable["IDLeague"];
    // The value we usually set is the primary key
?>">
    <?php echo $leagueTable["LeagueName"];
        // To show the category name to the user
    ?>
</option>
<?php
endwhile;
// While loop must be terminated
?>
</select> 

<?php
if(isset($_POST['ResultsPage'])) {
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
    // $hidden = $favourites == 'Yes' ? 'invisible' : 'visible';

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
    else {
        echo "No result found";
    }

    if($favourites == 'Yes') {
        $getresults ="SELECT  Case When f.Winner = f.Ian Then (Case When f.Upsetfactor = 2 Then f.UpsetPick Else f.FavouritePick End) Else f.WrongPick End as IanColour
        , Case When f.Winner = f.Michael Then (Case When f.Upsetfactor = 2 Then f.UpsetPick Else f.FavouritePick End) Else f.WrongPick End as MichaelColour
        , Case When f.Winner = f.Paula Then (Case When f.Upsetfactor = 2 Then f.UpsetPick Else f.FavouritePick End) Else f.WrongPick End as PaulaColour
        , Case When f.Winner = f.Mom Then (Case When f.Upsetfactor = 2 Then f.UpsetPick Else f.FavouritePick End) Else f.WrongPick End as MomColour
        , Case When UpsetFactor = 1 Then 'p-3 mb-2 bg-primary text-white' Else 'p-3 mb-2 bg-primary text-warning' End as WinnerColour
        , f.Mom
        , f.Ian
        , f.Michael
        , f.Paula
        , f.Winner
        , f.Upsetfactor
        , f.Matchup
        From (
        Select Max(Case When v.Name = 'Mom' Then v.Prediction Else Null End) as Mom
        ,Max(Case When v.Name = 'Ian' Then v.Prediction Else Null End) as Ian
        , Max(Case When v.Name = 'Michael' Then v.Prediction Else Null End) as Michael
        , Max(Case When v.Name = 'Paula' Then v.Prediction Else Null End) as Paula
        ,v.Winner
        ,v.UpsetFactor
        ,v.Matchup
        , 'p-3 mb-2 bg-success text-warning' as UpsetPick
        , 'p-3 mb-2 bg-success text-white' as FavouritePick
        , 'p-3 mb-2 bg-danger text-white' as WrongPick
        , 'p-3 mb-2 bg-danger text-white' as WinnerUpset
        From (SELECT  m.IDMatchup as Matchup, p.Name
        , Concat(pp.FirstName,' ',pp.LastName) as Prediction
        , Concat(pw.FirstName,' ',pw.LastName) as Winner 
        , Case When m.FavouriteScore > m.UnderdogScore Then 1 Else 2 End as UpsetFactor  
        FROM prediction pr
        Join matchup m on pr.IDMatchup = m.IDMatchup
        Join predictor p on pr.IDPredictor = p.IDPredictor
        Join participant pp on pp.IDParticipant = pr.IDWinner
        Join participant pw on pw.IDParticipant = (Case When m.FavouriteScore > m.UnderdogScore Then IDFavourite Else IDUnderdog End)
        Where m.IDTournament = $tournament) v
        Group by v.Winner, v.UpsetFactor, v.matchup
        , 'p-3 mb-2 bg-success text-warning'
        , 'p-3 mb-2 bg-success text-white'
        , 'p-3 mb-2 bg-danger text-white'
        , 'p-3 mb-2 bg-primary text-warning'
        ) f Order by f.Matchup";

    } else {
        $getresults = "SELECT Case When Result.IanPoints = 8 Then 'p-3 mb-2 bg-success text-warning' Else Case When Result.Ian = Result.Winner Then 'p-3 mb-2 bg-success text-white' Else 'p-3 mb-2 bg-danger text-white' End End as IanColour
        , Case When Result.MomPoints = 8 Then 'p-3 mb-2 bg-success text-warning' Else Case When Result.Mom = Result.Winner Then 'p-3 mb-2 bg-success text-white' Else 'p-3 mb-2 bg-danger text-white' End End as MomColour
        , Case When Result.MichaelPoints = 8 Then 'p-3 mb-2 bg-success text-warning' Else Case When Result.Michael = Result.Winner Then 'p-3 mb-2 bg-success text-white' Else 'p-3 mb-2 bg-danger text-white' End End as MichaelColour
        , Case When Result.PaulaPoints = 8 Then 'p-3 mb-2 bg-success text-warning' Else Case When Result.Paula = Result.Winner Then 'p-3 mb-2 bg-success text-white' Else 'p-3 mb-2 bg-danger text-white' End End as PaulaColour
        ,Result.Ian
        ,Result.IanGames
        ,Result.IanPoints
        ,Result.Mom
        ,Result.MomGames
        ,Result.MomPoints
        ,Result.Michael
        ,Result.MichaelGames
        ,Result.MichaelPoints
        ,Result.Paula
        ,Result.PaulaGames
        ,Result.PaulaPoints
        ,Result.Winner
        ,Result.Games
        From (
        Select Max(Case When pr.Name = 'Ian' Then Concat(pw.FirstName,' ',pw.LastName) Else Null End) as Ian
        ,Max(Case When pr.Name = 'Ian' Then p.Games Else Null End) as IanGames
        ,Max(Case When pr.Name = 'Ian' Then p.Points Else Null End) as IanPoints
        ,Max(Case When pr.Name = 'Mom' Then Concat(pw.FirstName,' ',pw.LastName) Else Null End) as Mom
        ,Max(Case When pr.Name = 'Mom' Then p.Games Else Null End) as MomGames
        ,Max(Case When pr.Name = 'Mom' Then p.Points Else Null End) as MomPoints
        ,Max(Case When pr.Name = 'Michael' Then Concat(pw.FirstName,' ',pw.LastName) Else Null End) as Michael
        ,Max(Case When pr.Name = 'Michael' Then p.Games Else Null End) as MichaelGames
        ,Max(Case When pr.Name = 'Michael' Then p.Points Else Null End) as MichaelPoints
        ,Max(Case When pr.Name = 'Paula' Then Concat(pw.FirstName,' ',pw.LastName) Else Null End) as Paula
        ,Max(Case When pr.Name = 'Paula' Then p.Games Else Null End) as PaulaGames
        ,Max(Case When pr.Name = 'Paula' Then p.Points Else Null End) as PaulaPoints
        ,Case When m.FavouriteScore > m.UnderdogScore Then Concat(pf.FirstName,' ',pf.LastName) Else Concat(pu.FirstName,' ',pu.LastName) End as Winner
        ,m.FavouriteScore + m.UnderdogScore as Games    
        ,m.IDMatchup
        From matchup m
        Join prediction p on m.IDMatchup = p.IDMatchup
        Join predictor pr on p.IDPredictor = pr.IDPredictor
        Join participant pw on pw.IDParticipant = p.IDWinner
        Join participant pf on pf.IDParticipant = m.IDFavourite
        Join participant pu on pu.IDParticipant = m.IDUnderdog
        Where m.IDTournament = $tournament
        Group by Case When m.FavouriteScore > m.UnderdogScore Then Concat(pf.FirstName,' ',pf.LastName) Else Concat(pu.FirstName,' ',pu.LastName) END, m.IDMatchup
        Order by m.IDMatchup ) Result";
    }
    $result = mysqli_query($conn,$getresults);

    if (mysqli_num_rows($result) > 0) {

        if ($favourites == "Yes") {

        ?>
        <h2 class="pull-left">Results Table</h2>
        <table class='table table-bordered table-striped'>
        <tr>
        <td width='10%'>Matchup</td>
        <td width='15%'>Mom</td>
        <td width='15%'>Michael</td>
        <td width='15%'>Paula</td>
        <td width='15%'>Ian</td>
        <td width='15%'>Winner</td>
        </tr>
        </table>
        <?php
        $i=0;
        while($row = mysqli_fetch_array($result)) {
            ?>
            <table class='table table-bordered table-striped'>
            <tr>
            <td width='10%' class='<?php echo $row["WinnerColour"]?>'><?php echo $row["Matchup"]; ?></td>
            <td width='15%' class='<?php echo $row["MomColour"]?>'><?php echo $row["Mom"]; ?></td>
            <td width='15%' class='<?php echo $row["MichaelColour"]?>'><?php echo $row["Michael"]; ?></td>
            <td width='15%' class='<?php echo $row["PaulaColour"]?>'><?php echo $row["Paula"]; ?></td>
            <td width='15%' class='<?php echo $row["IanColour"]?>'><?php echo $row["Ian"]; ?></td>
            <td width='15%' class='<?php echo $row["WinnerColour"]?>'><?php echo $row["Winner"]; ?></td>
            </tr>
            <?php
            $i++;
            ?>
            </table>
            <?php
        } 
    } else {

        ?>
        <h2 class="pull-left">Results Table</h2>
        <table class='table table-bordered table-striped'>
        <tr>
        <td>Mom</td>
        <td>Games</td>
        <td>Points</td>
        <td>Michael</td>
        <td>Games</td>
        <td>Points</td>
        <td>Paula</td>
        <td>Games</td>
        <td>Points</td>
        <td>Ian</td>
        <td>Games</td>
        <td>Points</td>
        <td>Winner</td>
        <td>Games</td>
        </tr>
        <?php
        $i=0;
        while($row = mysqli_fetch_array($result)) {
            ?>
            <tr>
            <td class='<?php echo $row["MomColour"]?>'><?php echo $row["Mom"]; ?></td>
            <td class='<?php echo $row["MomColour"]?>'><?php echo $row["MomGames"]; ?></td>
            <td class='<?php echo $row["MomColour"]?>'><?php echo $row["MomPoints"]; ?></td>
            <td class='<?php echo $row["MichaelColour"]?>'><?php echo $row["Michael"]; ?></td>
            <td class='<?php echo $row["MichaelColour"]?>'><?php echo $row["MichaelGames"]; ?></td>
            <td class='<?php echo $row["MichaelColour"]?>'><?php echo $row["MichaelPoints"]; ?></td>
            <td class='<?php echo $row["PaulaColour"]?>'><?php echo $row["Paula"]; ?></td>
            <td class='<?php echo $row["PaulaColour"]?>'><?php echo $row["PaulaGames"]; ?></td>
            <td class='<?php echo $row["PaulaColour"]?>'><?php echo $row["PaulaPoints"]; ?></td>
            <td class='<?php echo $row["IanColour"]?>'><?php echo $row["Ian"]; ?></td>
            <td class='<?php echo $row["IanColour"]?>'><?php echo $row["IanGames"]; ?></td>
            <td class='<?php echo $row["IanColour"]?>'><?php echo $row["IanPoints"]; ?></td>
            <td class="p-3 mb-2 bg-primary text-white"><?php echo $row["Winner"]; ?></td>
            <td class="p-3 mb-2 bg-primary text-white"><?php echo $row["Games"]; ?></td>        
            </tr>
            <?php
            $i++;
        }
    }
    ?>
    </table>
    <?php
    }


    else {
    echo "No result found";
    }
    
}

if(isset($_POST['matchupforprediction'])) {
    $predictor = ($_POST['PredictorDisplay']);
    $matchupforprediction = ($_POST['IDMatchup']);

    $getmatchups = "SELECT Substring(m.IDMatchup,Locate('-',m.IDMatchup)+1,Length(m.IDMatchup)) as Matchup
    , Round
    , IDTournament
    , CONCAT(participant.FirstName,' ',participant.LastName) as Favourite
    , CONCAT(p2.FirstName,' ',p2.LastName) as Underdog
    , FavouriteScore
    , UnderdogScore
    , StartTime
    , FavouriteSeeding
    , UnderdogSeeding
    , m.IDMatchup
    , IDFavourite
    , IDUnderdog
    , CONCAT(p3.FirstName,' ',p3.LastName) as Winner
    , Case When pr.IDWinner = m.IDUnderdog Then 'background:#ffffb3' Else 'background:#ffffff' End as UnderdogStyle
    FROM matchup m JOIN participant on m.IDFavourite = participant.IDParticipant 
                    JOIN participant p2 on m.IDUnderdog = p2.IDParticipant  
                    Join prediction pr on m.IDMatchup = pr.IDMatchup
                    Join participant p3 on pr.IDWInner = p3.IDParticipant
    Where m.IDMathcup = '$matchupforprediction'
                    and m.FavouriteScore is Null and m.UnderdogScore is Null and pr.IDPredictor = $predictor
                    Order by m.IDMatchup";
    $matchuplist = $conn->query($getmatchups);
    if (!$matchuplist) die("Database access failed");
    $rows = $matchuplist->num_rows;
    for ($j = 0; $j < $rows; ++$j) {
        $row = $matchuplist->fetch_array(MYSQLI_NUM);
        $r0 = htmlspecialchars($row[0]); // Matchup
        $r1 = htmlspecialchars($row[1]); // Round
        $r2 = htmlspecialchars($row[2]); // Tournament
        $r3 = htmlspecialchars($row[3]); // Favourite
        $r4 = htmlspecialchars($row[4]); // Underdog
        $r5 = htmlspecialchars($row[5]); // Favourite Score
        $r6 = htmlspecialchars($row[6]); // Underdog Score
        $r7 = htmlspecialchars($row[7]); // Start Time
        $r8 = htmlspecialchars($row[8]); // Favourite Seeding
        $r9 = htmlspecialchars($row[9]); // Underdog Seeding
        $r10 = htmlspecialchars($row[10]); //  IDMatchup
        $r11 = htmlspecialchars($row[11]); // IDFavourite
        $r12 = htmlspecialchars($row[12]); // IDUnderdog
        $r13 = htmlspecialchars($row[13]); // Winner
        $r14 = htmlspecialchars($row[14]); // UnderdogStyle
        echo <<<_END
    <pre>
    </pre>
    <form action='Predictions test.php' method='post'>
    <input type='hidden' name='flip' value='yes'>
    <input type='hidden' name='idmatchup' value='$r10'>
    <input type='text' style='width:40px' value='$r8'>
    <input type='text' style='width:240px' value='$r3'>
    <input type='submit' value='Flip My Pick !'>
    <br>
    <input type='text' style='width:40px' value='$r9'>
    <input type='text' style='width:240px' value='$r4'>
    <input type='text' style='width:180px; $r14' value='$r13'>
    </form>
    _END;
    }
    ////////////////////////////////////////////////////////////////// BEGIN OLD SHOW MATCHUP PAGE

    // $showmatchupforprediction = "SELECT Substring(IDMatchup,Locate('-',IDMatchup)+1,Length(IDMatchup))
    // , Round
    // , matchup.IDTournament
    // , CONCAT(participant.FirstName,' ',participant.LastName)
    // , CONCAT(p2.FirstName,' ',p2.LastName)
    // , FavouriteScore
    // , UnderdogScore
    // , StartTime
    // , FavouriteSeeding
    // , UnderdogSeeding
    // , IDMatchup
    // , IDFavourite
    // , IDUnderdog
    // , t.Favourites
    // FROM matchup JOIN participant on matchup.IDFavourite = participant.IDParticipant 
    //                 JOIN participant p2 on matchup.IDUnderdog = p2.IDParticipant  
    //                 JOIN tournament t on matchup.IDTournament = t.IDTournament Where IDMatchup = '$matchupforprediction' 
    //                 Order by IDMatchup";
    // // var_dump($showmatchupforprediction);
    // // die();
    // $matchuplistforprediction = $conn->query($showmatchupforprediction);
    // if (!$matchuplistforprediction) die("Select From matchup access failed");
    // $rows = $matchuplistforprediction->num_rows;
    // for ($j = 0; $j < $rows; ++$j) {
    //     $row = $matchuplistforprediction->fetch_array(MYSQLI_NUM);// 
    //     $r0 = htmlspecialchars($row[0]);// Matchup
    //     $r1 = htmlspecialchars($row[1]);// Round
    //     $r2 = htmlspecialchars($row[2]);// IDTournament
    //     $r3 = htmlspecialchars($row[3]);// Participant full name
    //     $r4 = htmlspecialchars($row[4]);// IDUnderdog
    //     $r5 = htmlspecialchars($row[5]);// FavouriteScore
    //     $r6 = htmlspecialchars($row[6]);// UnderdogScore
    //     $r7 = htmlspecialchars($row[7]);// StartTime
    //     $r8 = htmlspecialchars($row[8]);// FavouriteSeeding
    //     $r9 = htmlspecialchars($row[9]);// UnderdogSeeding
    //     $r10 = htmlspecialchars($row[10]);// IDMatchup
    //     $r11 = htmlspecialchars($row[11]);// IDFavourite
    //     $r12 = htmlspecialchars($row[12]);// IDUnderdog
    //     $r13 = htmlspecialchars($row[13]);// Favourites
    //     $r13 = $r13 == 'Yes' ? 'hidden' : '';
    //     echo <<<_END
    // <pre>
    // </pre>
    // <form action='savePrediction.php' method='post' id='submitprediction'>
    // <input type='hidden' name='idmatchup' value='$r10'>
    // $r0 
    // <input type='text' name='fseed' value='$r8'>
    // <input type='text' name='Favourite' value='$r3'>
    // <input type='hidden' name='idFavourite' value='$r11'>
    // <input type='radio' name='predicted' value=Favourite>
    //     <select name="numberofgames" $r13>
    //     <option value="4">4</option>
    //     <option value="5">5</option>
    //     <option value="6">6</option>
    //     <option value="7">7</option>
    // </select>
    // <br>     
    // $r0
    // <input type='text' name='useed' value='$r9'>
    // <input type='text' name='Underdog' value='$r4'>
    // <input type='hidden' name='IDUnderdog' value='$r12'> 
    // <input type='hidden' name='PredictorDisplay' value='$predictor'> 
    // <input type='radio' name='predicted' value=Underdog>
    // <input type='submit' name = 'prediction' value='Submit Prediction'>
    // <br>     
    // _END;
    // }
    // $matchuplistforprediction->close();
    
}
    ////////////////////////////////////////////////////////////////// END OLD SHOW MATCHUP PAGE


if(isset($_POST['populatematch'])) {
    $participant =  ($_POST['IDFavourite']);
    $opponent =  ($_POST['IDUnderdog']);
    $matchup =  ($_POST['IDMatchup']);
    if(isset($_POST['Favourites'])) {
    }
    $tournament = substr($matchup,0,2);
    $round = intval(substr($matchup,stripos($matchup, '-')+1,1));
    $nextround = $round + 1;
    $currentslot = intval(substr($matchup,strrpos($matchup, '-')+2,1));


    // $newmatchup = (intval($currentslot) % 2 == 0)  ? intval($currentslot) / 2 : (intval($currentslot) + 1) / 2;
 
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
        $favourites =  ($_POST['Favourites']);
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
        // $defaultfavpick = "Update prediction p
        // Join matchup m on p.IDMatchup = matchup.IDMatchup
        // Set IDWinner = matchup.IDFavourite
        // Where matchup.IDmatchup = '$matchup'";
        // $defaultfavpickresult = $conn->query($defaultfavpick);
        }
        $updatepointsresult = $conn->query($updatepoints);

        

        $winner = ($favouritescore > $underdogscore) ? $participant : $opponent;
        $winnerseeding  = ($favouritescore > $underdogscore) ? $favouriteseed : $underdogseed;  
        if($favouritescore > 0 || $underdogscore > 0) {
            
        $newmatchup =  ($_POST['IDNextMatchup']);
            $getseed = "SELECT IDFavourite, FavouriteScore, FavouriteSeeding From matchup Where IDMatchup = '$newmatchup' and IDFavourite is not Null";

            $defaultfavourite = "UPDATE prediction Set IDWinner = $participant Where IDMatchup = '$newmatchup'";
            $defaultfavouriteresult = $conn->query($defaultfavourite);

            $seedlist = $conn->query($getseed);
            $row = $seedlist->fetch_array(MYSQLI_NUM);
            $tournamentrounds =  ($_POST['rounds']);
            if (empty($row))
            {
                echo "No next seed";
                $insertwinner = "Update matchup 
                Set IDFavourite = $winner, FavouriteSeeding = $winnerseeding
                Where IDMatchup = '$newmatchup'";
                $insertwinnerresult = $conn->query($insertwinner); 
            }  elseif ($nextround < $tournamentrounds) {
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
    $tournament = $_POST['TournamentDisplay'];
    $showmatchups = "SELECT * From matchup Where IDTournament = $tournament and FavouriteScore is Null and UnderdogScore is Null";
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
                    and matchup.FavouriteScore is Null and matchup.UnderdogScore is Null
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
        $r5 = htmlspecialchars($row[5]);// FavouriteScoreGetInfoForPredictions
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
                    JOIN participant p2 on matchup.IDUnderdog = p2.IDParticipant  Where IDTournament = $tournament 
                    and matchup.FavouriteScore is Null and matchup.UnderdogScore is Null Order by IDMatchup";
    
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
    <input type='text' value='$p8' size='2'>
    <input type='text' value='$p3' size='30'>
    <input type='text' name='favouritescore' size='2'>
    <br>     
    $p0
    <input type='text' value='$p9' size='2'>
    <input type='text'  value='$p4' size='30'>
    <input type='text' name='underdogscore' size='2'>
    _END;
    ?>
    <button type="button" value="Click Me" onclick="displayPhrase()">Click Me</button>
    
    <script>
    function displayPhrase()
    {
        document.getElementById("demo").innerHTML = document.getElementById("demo").innerHTML == 'New Phrase' ? <?php echo "Test" ?> : 'New Phrase';
    }
    </script>
    <span id="demo"><?php echo $p4 ?> </span>
    <?php
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
    <form id='getpredictions' method='post' action="Predictions test.php">
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

var_dump($_POST);
echo $tournament;

?>
 <button type="button" value="Click Me" onclick="displayPhrase()" >Click Me</button>
     <script>
function displayPhrase()
{
    document.getElementById("demo").innerHTML = document.getElementById("demo").innerHTML == 'New Phrase' ? <?php echo $tournament ?> : 'New Phrase';
}
</script>
<span id="demo">New Phrase</span>
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
    if (!$matchuplist) die("Select From matchup access failed");
    $rows = $matchuplist->num_rows;
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
    <form action='Predictions test.php' method='post' form='submitform'>
    $p0
    <input type='hidden' value='$p8' name='FavouriteSeeding'>
    <input type='hidden' value='$p13' name='IDNextMatchup'>
    <input style='width:40px' type='text' value='$p8'>
    <input type='text' value='$p3'>
    <input type='text' style='width:40px' name='favouritescore' value='$p5' >
    <input type='hidden' value='$p11' name='IDFavourite'>
    <br>     
    $p0
    <input type='text' style='width:40px' value='$p9'>
    <input type='hidden' value='$p10' name='IDMatchup'>
    <input type='hidden' value='$p9' name='UnderdogSeeding'>
    <input type='text'  value='$p4'>
    <input type='hidden' value='$p12' name='IDUnderdog'>
    <input type='text' style='width:40px' name='underdogscore' value='$p6' >
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
<form action="Predictions test.php" method="post" id="addtournamentdetails">
<pre>
Name <input type="text" name="Name"> <input type="submit" value="Add New Predictor" name="CreatePredictor" >
League <input type="text" name="League"> <input type="submit" value="Add New League" name="CreateLeague">
First Name <input type="text" name="FirstName"> Last Name <input type="text" name="LastName"> <input type="submit" Name="CreateParticipant" value="Add New Participant">
Tournament Name <input type="text" name="Tournament"> Description <input type="text" name="Description">Year<input type="text" name="Year">  Rounds<input type="text" name="Rounds">  Favourites<input type="text" name="Favourites"> <input type="submit" name="CreateTournament" value="Add New Tournament">

<input type="submit" value="Show Predictors" name="ShowPredictors">  <input type="submit" value="Show Leagues" name="ShowLeagues">  <input type="submit" value="Show Participants" name="ShowParticipants">  <input type="submit" value="Show Tournaments" name="ShowTournaments">

_END;
?>

</pre>
</form>
<?php
}

// Admin → Show Predictors — BEGIN
if(isset($_POST['ShowPredictors'])) {
    
    $getpredictors = "SELECT 
    pr.Name
    From predictor pr";
    $predictorlist = mysqli_query($conn,$getpredictors);

      if (mysqli_num_rows($predictorlist) > 0) {
    ?> 
    
    <h2 class="pull-left">List of Predictors</h2>
    <table class='table table-bordered table-striped'>
    <tr>
    <td class="btn btn-primary">Name</td>
    <td class="btn btn-primary">Action</td>
    </tr>
    <?php
    $i=0;
    while($row = mysqli_fetch_array($predictorlist)) {
    ?>
    <tr>
    <td><?php echo $row["Name"]; ?></td>
    <td><input type='submit' value='Delete'></td> 
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
}
// Admin → Show Predictors — END
// Admin → Show Leagues — BEGIN
if(isset($_POST['ShowLeagues'])) {
    
    $getleagues = "SELECT 
    LeagueName
    From league
    Order by LeagueName";
    $leaguelist = mysqli_query($conn,$getleagues);

      if (mysqli_num_rows($leaguelist) > 0) {
    ?> 
    
    <h2 class="pull-left">List of Leagues</h2>
    <table class='table table-bordered table-striped'>
    <tr>
    <td class="btn btn-primary">Name</td>
    <td class="btn btn-primary">Action</td>
    </tr>
    <?php
    $i=0;
    while($row = mysqli_fetch_array($leaguelist)) {
    ?>
    <tr>
    <td><?php echo $row["LeagueName"]; ?></td>
    <td><input type='submit' value='Delete'></td> 
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
}
// Admin → Show Leagues — END

// Admin → Show Participants — BEGIN
if(isset($_POST['ShowParticipants']) || isset($_POST['ShowParticipantsFromLeague'])) {

    $getparticipants = "SELECT   Concat(FirstName,' ',LastName) as Name  
    From participant  
    Order by FirstName, LastName";
    $participantlist = mysqli_query($conn,$getparticipants);

      if (mysqli_num_rows($participantlist) > 0) {
    ?> 
    
    <h2 class="pull-left">List of Participants</h2>
    <table class='table table-bordered table-striped'>
    <tr>
    <td>Name</td>
    <td>Action</td>
    </tr>
    <?php
    $i=0;
    while($row = mysqli_fetch_array($participantlist)) {
    ?>
    <tr>
    <td><?php echo $row["Name"]; ?></td>
    <td><input type='submit' value='Delete'></td> 
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
}

// Admin → Show Participants — END


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
    $firstname = Trim($firstname);
    $lastname = Trim($lastname);
    $idleague = get_post($conn, 'LeagueDisplay');
    $insertparticipant = "INSERT INTO participant (FirstName, LastName, IDLeagueType) VALUES" .
    "('$firstname', '$lastname', '$idleague')";
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