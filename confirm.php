<!-------------------------- step 3 Booking ------------------------------------->
<?php
if($_POST['hd_step']=='3'){
 ?>

 <?php
 $index = 0;
 $k=array();
 foreach ($_POST['pr_ice'] as $key => $value) {
  if($value!='0'){
 	 $m=array_push($k,$index);
  }
  $index++;
 }

 foreach($k as $m){
 	 $pricnamee[] = $_POST['hd_price_name'][$m];
 }

 foreach ($k as $r) {
 	$rr[] = $_POST['hd_price_rate'][$r];
 }

 foreach ($k as $q) {
  $qq[] = $_POST['pr_ice'][$q];
 }


  ?>

<form action="" method="post">

<table class="table">
	<tr>
		<td colspan="2">
			<p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;" class="">Confirm Your Booking </p>
		</td>
	</tr>
	<tr>
		<td>
			Tour Date Time
		</td>
		<td>
			<?php echo date('l dS F Y h:i A',strtotime($_POST['hd_tour_date_time'])); ?>
		</td>
	</tr>
	<?php
	$i=0;
	foreach($pricnamee as $ss){
		?>
	<tr>
		<td>

				<?php echo '<p>'.$ss.' x '.$qq[$i].'</p>'; ?>

		</td>
		<td>
			<?php echo '$'.$rr[$i] * $qq[$i]; ?>
		</td>
	</tr>
	<?php
	$t += $rr[$i] * $qq[$i];
	$i++;
 }
 ?>

<tr>
	<td>
		Total Price:
	</td>
	<td>
		<?php echo '$'.$t; ?>
	</td>
</tr>

<tr>
 <td colspan="2">
	 <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;" class="">Your Details </p>
 </td>
</tr>

<tr>
	<td>
		Name
	</td>
	<td>
		<?php echo $_POST['fname'].' '.$_POST['lname']; ?>
	</td>
</tr>

<tr>
	<td>
		Email
	</td>
	<td>
		<?php echo $_POST['email']; ?>
	</td>
</tr>

<tr>
	<td>
		Phone
	</td>
	<td>
		<?php echo $_POST['phone']; ?>
	</td>
</tr>

<tr>
 <td colspan="2">
	 <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;" class="">Terms & Conditions </p>
 </td>
</tr>

<tr>
	<td colspan="2">
		<input type="checkbox" name="tandc" value="0"> I have read and accept the Terms and Conditions.
	</td>
</tr>



</table>

<button type="submit" onclick="confirmm()" class="btn btn-primary pull-right" name="button">Confirm & Make Payment</button>

</form>








 <div class="hidden_hd">
   <input type="hidden" name="hd_step" value="3">
   <input type="hidden" name="hd_tour_key" value="<?php echo $_POST['hd_tour_key']; ?>">
   <input type="hidden" name="hd_date" value="<?php echo $_POST['hd_date']; ?>">
   <input type="hidden" name="hd_tour_name" value="<?php echo $_POST['hd_tour_name']; ?>">
   <input type="hidden" name="hd_tour_date_time" value="<?php echo $_POST['hd_tour_date_time']; ?>">
 	<input type="hidden" name="return_url" value="http://www.flatdesignstudio.com/work/wp-plugin">
 </div>

<?php
}
?>
