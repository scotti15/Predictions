<?php // administration.php
require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die("Fatal Error");


if (isset($_POST ["predicted"])) { 

    // $sql = "SELECT pr.Name, Concat(pa.FirstName,' ',pa.LastName) as Winner, IDMatchup FROM `prediction` p Left Join participant pa on p.IDWinner = pa.IDParticipant JOIN predictor pr on p.IDPredictor = pr.IDPredictor";
    // $all_predictions = mysqli_query($conn,$sql);
    
    // $sql = "SELECT Distinct p.IDPredictor, p.Name From predictor p Join prediction pr on p.IDPredictor = pr.IDPredictor";
    // $all_predictors = mysqli_query($conn,$sql);

    // $numberofpredictors = $all_predictors->num_rows;
    // $numberofpredictions = $all_predictions->num_rows;


// $n = 0;        
// $i = 0;
// while ($row = mysqli_fetch_array($all_predictors, MYSQLI_ASSOC)) {
//     $PredictorList[$i] = $row["Name"];
//     $i++;
// }
    
// $i = 0;
// while ($row = mysqli_fetch_array($all_predictions, MYSQLI_ASSOC)) {
//     $Name[$i] = $row["Name"];
//     $Winner[$i] = $row["Winner"];
//     $Idmatchup[$i] = $row["IDMatchup"];
//     $i++;
// }

    // var_dump("Predicted");
    var_dump($_POST);
    $predicted = $_POST["predicted"];
    $predictor = $_POST["PredictorDisplay"];
    $idunderdog = $_POST["IDUnderdog"];
    $idfavourite = $_POST["idFavourite"];
    $idmatchup = $_POST["idmatchup"];
    $numberofgames = $_POST["numberofgames"];
    $winner = $predicted == "Favourite" ? $idfavourite : $idunderdog;

    $saveprediction = "UPDATE prediction SET IDWinner = $winner, Games = $numberofgames WHERE IDMatchup = '$idmatchup' AND IDPredictor = $predictor";
    var_dump($saveprediction);
    $savepredictionresult = $conn->query($saveprediction);
    if (!$savepredictionresult) die("Select From matchup access failed 2");

} else {
    var_dump("Nothing Predicted");
};
//header('Location: ' . $_SERVER['HTTP_REFERER']);
 header("Location: Predictions.php");

