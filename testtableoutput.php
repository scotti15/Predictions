<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Bootstrap 4 Bordered Table</title>
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
<h2 class="pull-left">Results Table</h2>
</div>
<?php
include_once 'login.php';
$query = "SELECT  Case When f.Winner = f.Ian Then (Case When f.Upsetfactor = 2 Then f.UpsetPick Else f.FavouritePick End) Else f.WrongPick End as IanColour
, Case When f.Winner = f.Michael Then (Case When f.Upsetfactor = 2 Then f.UpsetPick Else f.FavouritePick End) Else f.WrongPick End as MichaelColour
, Case When f.Winner = f.Paula Then (Case When f.Upsetfactor = 2 Then f.UpsetPick Else f.FavouritePick End) Else f.WrongPick End as PaulaColour
, Case When f.Winner = f.Mom Then (Case When f.Upsetfactor = 2 Then f.UpsetPick Else f.FavouritePick End) Else f.WrongPick End as MomColour
, f.Mom
, f.Ian
, f.Michael
, f.Paula
, f.Winner
, f.Upsetfactor
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
From (SELECT  m.IDMatchup as Matchup, p.Name
, Concat(pp.FirstName,' ',pp.LastName) as Prediction
, Concat(pw.FirstName,' ',pw.LastName) as Winner 
, Case When m.FavouriteScore > m.UnderdogScore Then 1 Else 2 End as UpsetFactor  
FROM prediction pr
Join matchup m on pr.IDMatchup = m.IDMatchup
Join predictor p on pr.IDPredictor = p.IDPredictor
Join participant pp on pp.IDParticipant = pr.IDWinner
Join participant pw on pw.IDParticipant = (Case When m.FavouriteScore > m.UnderdogScore Then IDFavourite Else IDUnderdog End)
Where m.IDTournament = 25 ) v
Group by v.Winner, v.UpsetFactor
, 'p-3 mb-2 bg-success text-warning'
, 'p-3 mb-2 bg-success text-white'
, 'p-3 mb-2 bg-danger text-white'
) f Order by f.Matchup";
$conn = new mysqli($hn, $un, $pw, $db);
$result = mysqli_query($conn,$query);
?>
<?php
if (mysqli_num_rows($result) > 0) {
?>
<table class='table table-bordered table-striped'>
<tr>
<td>Mom</td>
<td>Michael Winner</td>
<td>Paula Games</td>
<td>Ian</td>
<td>Winner</td>
</tr>
<?php
$i=0;
while($row = mysqli_fetch_array($result)) {
?>
<tr>
<td><?php echo $row["Mom"]; ?></td>
<td><?php echo $row["Michael"]; ?></td>
<td><?php echo $row["Paula"]; ?></td>
<td><?php echo $row["Ian"]; ?></td>
<td><?php echo $row["Winner"]; ?></td>
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
?>
</div>
</div>        
</div>
</div>
</body>
</html>