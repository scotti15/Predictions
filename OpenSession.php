<?php 

session_start();

?>
<form action='Predictions.php' method='post' id='opensession'>
<p>Open Session for </p>
<select name="sessionpredictor">
<option value="4">Mom</option>
<option value="3">Michael</option>
<option value="5">Paula</option>
<option value="2">Ian</option>
</select>
    <input type='submit' name = 'opensession' value='Go to Predictions'>