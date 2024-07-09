<?php 


require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);

if ($conn->connect_error) die("Fatal Error");

if(isset($_POST['sessionpredictor'])) 
    {
        $predictor=$_POST['sessionpredictor'];
        $getpredictorname = "SELECT * FROM `predictor` Where idpredictor = $predictor";
        $getpredictornameresults = $conn->query($getpredictorname);
        $row = $getpredictornameresults->fetch_array(MYSQLI_NUM);
        $predictorname = htmlspecialchars($row[1]);
        $hide = ($predictorname == 'Ian') ? " " : "hidden";
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
        .bs-example
            {
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

        <?php

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
        <input type="submit" <?php echo $hide ?>  value="Admin Tools" name="AdminTools" >
        <input type="submit" <?php echo $hide ?>  value="Tournament Management" name="TournamentManagementTools">
        <input type="submit" value="Prediction Page" name="PredictionTools">
        <input type="submit" value="Results Page" name="ResultsPage">
        <input type="hidden" name='sessionpredictor' value=<?php echo $predictor ?>>
        </form> 
        <?php

        if(isset($_POST['Submission']))
        {
            //Begin Get Winner Pick name
            $predicted = ($_POST['predicted']);
            $numberofgames = ($_POST['numberofgames']);

            $getpredictedname = "SELECT Concat(FirstName, ' ', LastName) FROM `participant` Where IDParticipant =  $predicted";
            $getpredictednameresults = $conn->query($getpredictedname);
            $row = $getpredictednameresults->fetch_array(MYSQLI_NUM);
            $predictedname = htmlspecialchars($row[0]);
        
            echo 'You predicted The '.$predictedname.' in '.$numberofgames.' games.';
        
            //End Get Winner Pick name
            
            $idmatchup = ($_POST['idmatchup']);
            $sessionpredictor = ($_POST['sessionpredictor']);
            $registerprediction = "UPDATE prediction Set IDWinner = $predicted, Games = $numberofgames Where IDMatchup ='$idmatchup' and IDPredictor = $sessionpredictor";
            $registerpredictionresult = $conn->query($registerprediction);
        }

        $tournament = $_POST['IDTournament'];
        $idpredictor = $_POST['sessionpredictor'];
        // Find out if this is a tournament with favourites or not
    
        $gettournamenttype = "SELECT Favourites from Tournament Where IDTournament = $tournament";
        $favouriteslist = $conn->query($gettournamenttype);
        $rows = $favouriteslist->num_rows;
        for ($j = 0; $j < $rows; ++$j) {
            $row = $favouriteslist->fetch_array(MYSQLI_NUM);
            $favourites = htmlspecialchars($row[0]); // Matchup
        }
        
        if ($favourites == 'Yes') 
            {
                var_dump($_POST);
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
                for ($j = 0; $j < $rows; ++$j) 
                    {
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
                        
                        $left = $j % 4 * 350;
                        $left = $left."px";
                        $top = ceil(($j+1) / 4) * 100;
                        $top = $top."px";

                        $focus = $idmatchup == $r10 ? "autofocus" : ""; 

                        echo <<<_END
                        <pre></pre>
                        <div style= "position: absolute; top: $top; left: $left;">
                            <form action='changelabeltext.php' method='post'>
                                <input type="hidden" value="$idpredictor" name="sessionpredictor">
                                <input type='hidden' name='flip' value='yes'>
                                <input type='hidden' name='idmatchup' value='$r10'>
                                <input type='hidden' name='IDTournament' value='$tournament'>
                                <input type='hidden' name='PredictorDisplay' value='$idpredictor'>
                                <input type='text' style='width:40px' value='$r8' $focus> 
                                <input type='text' style='width:240px; $r14' value='$r3'>
                                <br>
                                <input type='text' style='width:40px' value='$r9'>
                                <input type='text' style='width:240px; $r15' value='$r4'>
                                <br>
                                <input type='submit' value='Flip My Pick !'>
                                <br>
                            </form>
                        </div>
                        _END;
                    }
            }   
        else 
            {            
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
                , '' as FavouriteStyle
                , '' as UnderdogStyle
                , pr.IDWinner
                , pr.Games
                , Case When pr.Games = 4 Then 'selected' else Null end as 4isselected 
                , Case When pr.Games = 5 Then 'selected' else Null end as 5isselected 
                , Case When pr.Games = 6 Then 'selected' else Null end as 6isselected 
                , Case When pr.Games = 7 Then 'selected' else Null end as 7isselected
                , Case When IDFavourite = pr.IDWinner then 'checked' else Null end as FavouriteChecked
                , Case When IDUnderdog = pr.IDWinner then 'checked' else Null end as UnderdogChecked
                , Case When StartTime is Null or StartTime > Now() Then ' ' else 'disabled' End as ButtonCondition
                FROM matchup m JOIN participant on m.IDFavourite = participant.IDParticipant 
                                JOIN participant p2 on m.IDUnderdog = p2.IDParticipant  
                                Left Join prediction pr on m.IDMatchup = pr.IDMatchup
                Where IDTournament = $tournament
                                and m.FavouriteScore is Null and m.UnderdogScore is Null 
                                and pr.IDPredictor = $idpredictor
                                Order by m.IDMatchup";

                $matchuplist = $conn->query($getmatchups);

                if (!$matchuplist) die("Database access failed");

                $rows = $matchuplist->num_rows;
                for ($j = 0; $j < $rows; ++$j) 
                    {
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
                        $r14 = htmlspecialchars($row[13]); // FavouriteStyle
                        $r15 = htmlspecialchars($row[14]); // UnderdogStyle
                        $r16 = htmlspecialchars($row[15]); // IDWinner
                        $r17 = htmlspecialchars($row[16]); // Games
                        $r18 = htmlspecialchars($row[17]); // 4isSelected
                        $r19 = htmlspecialchars($row[18]); // 5isSelected
                        $r20 = htmlspecialchars($row[19]); // 6isSelected
                        $r21 = htmlspecialchars($row[20]); // 7isSelected
                        $r22 = htmlspecialchars($row[21]); // Favourite Checked
                        $r23 = htmlspecialchars($row[22]); // Underdog 
                        $r24 = htmlspecialchars($row[23]); // ButtonCondition
                        
                        if ($r22 == "checked") $r22 = "checked = \"checked\"";
                        if ($r23 == "checked") $r23 = "checked = \"checked\"";

                        echo <<<_END
                        <pre>
                        </pre>
                            <form action='changelabeltext.php' method='post' form='submitform'>
                            <input type="hidden" value="$idpredictor" name="sessionpredictor">
                            <input type='hidden' name='radiopick' value='yes'>
                            <input type='hidden' name='idmatchup' value='$r10'>
                            <input type='hidden' name='IDTournament' value='$tournament'>
                            <input type='hidden' name='PredictorDisplay' value='$idpredictor'>
                            <input type='text' style='width:40px' value='$r8'>
                            <input type='text' style='width:240px; $r14' value='$r3'>
                            <input type='radio' name='predicted'  value='$r11' $r22>
                                <select name="numberofgames">
                                    <option value="4" $r18>4</option>
                                    <option value="5" $r19>5</option>
                                    <option value="6" $r20>6</option>
                                    <option value="7" $r21>7</option>
                                </select>
                            <label style='width:250px'>Start Time : $r7</label>
                            <br>
                            <input type='text' style='width:40px' value='$r9'>
                            <input type='text' style='width:240px; $r15' value='$r4'>
                            <input type='radio' name='predicted' value='$r12' $r23>
                            <input type='submit' $r24 name='Submission' value='Submit My Pick'>
                            </form>
                        _END;
                    }
        }
        ?>
        </body>
        
        </html>  
        <?php
        function get_post($conn, $var)
        {
            return $conn->real_escape_string($_POST[$var]);
        }
    } 
    else 
    {
        header("Location: OpenSession.php");
    }

 ?>
 