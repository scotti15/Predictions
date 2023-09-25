<?php 


require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die("Fatal Error");


if(isset($_POST['sessionpredictor'])) {
    $predictor=$_POST['sessionpredictor'];

    $getpredictorname = "SELECT * FROM `predictor` Where idpredictor = $predictor";
    
    $getpredictornameresults = $conn->query($getpredictorname);
    $row = $getpredictornameresults->fetch_array(MYSQLI_NUM);
    $predictorname = htmlspecialchars($row[1]);
 ?>
 
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
<body >
<h1>Predictions for <?php echo $predictorname ?></h1>
<?php // administration.php
require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die("Fatal Error");

// BEGIN FLIP PICK
if (isset($_POST['flip']) && isset($_POST['idmatchup']))
{
$idmatchup = $_POST['idmatchup'];
$query = "UPDATE prediction p 
Join matchup m on p.IDMatchup = m.IDMatchup
Set p.IDWinner = Case When p.IDWinner = m.IDFavourite Then m.IDUnderdog Else m.IDFavourite End 
Where p.IDMatchup = '$idmatchup' and IDPredictor = '$predictor'";
$result = $conn->query($query);
if (!$result) echo "FLIP failed<br><br>";
}
// END FLIP PICK
?>
<form action="Predictions.php" method="post" id="submitform" >
<input type="submit" value="Admin Tools" name="AdminTools" >
<input type="submit" value="Tournament Management" name="TournamentManagementTools">
<input type="submit" value="Prediction Page" name="PredictionTools">
<input type="submit" value="Results Page" name="ResultsPage">
<input type="hidden" name='sessionpredictor' value=<?php echo $predictor ?>>
</form> 
<?php
    $tournament = $_POST['IDTournament'];
    $idpredictor = $_POST['sessionpredictor'];

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
, '' as FavouriteStyle
, Case When pr.IDWinner <> m.IDUnderdog Then '' Else 'color : #ffffb3; background:#000000; font-weight:bold' End as UnderdogStyle
FROM matchup m JOIN participant on m.IDFavourite = participant.IDParticipant 
                JOIN participant p2 on m.IDUnderdog = p2.IDParticipant  
                Join prediction pr on m.IDMatchup = pr.IDMatchup
                Join participant p3 on pr.IDWInner = p3.IDParticipant
Where IDTournament = $tournament
                and m.FavouriteScore is Null and m.UnderdogScore is Null 
                and pr.IDPredictor = $idpredictor
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
    $r14 = htmlspecialchars($row[14]); // FavouriteStyle
    $r15 = htmlspecialchars($row[15]); // UnderdogStyle
    echo <<<_END
<pre>
</pre>
<form action='changelabeltext.php' method='post'>
<input type="hidden" value="$idpredictor" name="sessionpredictor">
<input type='hidden' name='flip' value='yes'>
<input type='hidden' name='idmatchup' value='$r10'>
<input type='hidden' name='IDTournament' value='$tournament'>
<input type='hidden' name='PredictorDisplay' value='$idpredictor'>
<input type='text' style='width:40px' value='$r8'>
<input type='text' style='width:240px; $r14' value='$r3'>
<input type='submit' value='Flip My Pick !'>
<br>
<input type='text' style='width:40px' value='$r9'>
<input type='text' style='width:240px; $r15' value='$r4'>
</form>
_END;
}

?>
</body>
  
</html>  
<?php
function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}

} else {
    header("Location: OpenSession.php");
}