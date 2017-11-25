<?
if(isset($_POST['speed'])){
	
	$speed			= $_POST['speed'];
	$filament_width = $_POST['filament'];
	$total_height	= $_POST['part_height'];
	$nozzle			= $_POST['nozzle'];
	$layer_height	= $_POST['layer'];
	$flow			= $_POST['flow'];
	$distance		= $_POST['part_width'];
	$cooling		= $_POST['cooling'];
	$ox				= $_POST['x_offset'];
	$oy				= $_POST['y_offset'];

	$spot_weld		= $_POST['weld'];
	$spot_weld_dwell= $_POST['dwell'];
	$weld_strength	= $_POST['weld_distance'];

	$F = $speed * 60;
	$E = 0;
	$Z = 0;
	
	header('Content-Type: text/plain');
	header('Content-Disposition: attachment; filename=test.gcode'); 

	$gcode = 'G21 ;Metric values'."\n";	
	$gcode = 'G90 ;Absolute position mode'."\n";	
	$gcode.= 'M82 ;Set extruder to absolute mode'."\n";	
	$gcode.= 'M107 ;Start with the fan off'."\n";	
	$gcode.= 'G28 X0 Y0 ;Home X/Y Axis'."\n";	
	$gcode.= 'G28 Z0 ;Home Z Axis'."\n";

	$gcode.= 'G92 E0 ;zero the extruded length'."\n";
	$gcode.= 'G1 F200 E3 ;extrude 3mm of feed stock'."\n";
	$gcode.= 'G92 E0 ;zero the extruded length again'."\n";

	for($L=0; $L<$total_height/$layer_height; $L++){
		$Z+= $layer_height;
		$gcode.= 'G0 Z'.$Z."\n";

		if($cooling>0 && $Z>0.6 && $Z<1){
			$gcode.= 'M106 S'.(255*($cooling/100))."\n";
		}
		
		//square 1
		$gcode.= 'G0 X'.$ox.' Y'.$oy.' F'.$F."\n";
		$gcode.= 'G11 ;unretract'."\n";
		$gcode.= 'G1 X'.$ox.' Y'.($oy+$distance).' E'.ecalc($distance).' F'.$F."\n";
		$gcode.= 'G1 X'.($ox+$distance).' Y'.($oy+$distance).' E'.ecalc($distance).' F'.$F."\n";
		$gcode.= 'G1 X'.($ox+$distance).' Y'.$oy.' E'.ecalc($distance).' F'.$F."\n";
		$gcode.= 'G1 X'.($ox+$nozzle).' Y'.$oy.' E'.ecalc($distance-$nozzle).' F'.$F."\n";
		$gcode.= ''."\n";

		//square 2
		$gcode.= 'G1 X'.($ox+$nozzle).' Y'.($oy+$distance-$nozzle).' E'.ecalc($distance-$nozzle).' F'.$F."\n";
		$gcode.= 'G1 X'.($ox+$distance-$nozzle).' Y'.($oy+$distance-$nozzle).' E'.ecalc($distance-$nozzle).' F'.$F."\n";
		$gcode.= 'G1 X'.($ox+$distance-$nozzle).' Y'.($oy+$nozzle).' E'.ecalc($distance-$nozzle).' F'.$F."\n";
		$gcode.= 'G1 X'.($ox+($nozzle*2)).' Y'.($oy+$nozzle).' E'.ecalc($distance-($nozzle*2)).' F'.$F."\n";
		$gcode.= ''."\n";

		//square 3
		$gcode.= 'G1 X'.($ox+($nozzle*2)).' Y'.($oy+$distance-($nozzle*2)).' E'.ecalc($distance-($nozzle*2)).' F'.$F."\n";
		$gcode.= 'G1 X'.($ox+$distance-($nozzle*2)).' Y'.($oy+$distance-($nozzle*2)).' E'.ecalc($distance-($nozzle*2)).' F'.$F."\n";
		$gcode.= 'G1 X'.($ox+$distance-($nozzle*2)).' Y'.($oy+($nozzle*2)).' E'.ecalc($distance-($nozzle*2)).' F'.$F."\n";
		$gcode.= 'G1 X'.($ox+($nozzle*3)).' Y'.($oy+($nozzle*2)).' E'.ecalc($distance-($nozzle*3)).' F'.$F."\n";
		$gcode.= ''."\n";

		//square 4
		$gcode.= 'G1 X'.($ox+($nozzle*3)).' Y'.($oy+$distance-($nozzle*3)).' E'.ecalc($distance-($nozzle*3)).' F'.$F."\n";
		$gcode.= 'G1 X'.($ox+$distance-($nozzle*3)).' Y'.($oy+$distance-($nozzle*3)).' E'.ecalc($distance-($nozzle*3)).' F'.$F."\n";
		$gcode.= 'G1 X'.($ox+$distance-($nozzle*3)).' Y'.($oy+($nozzle*3)).' E'.ecalc($distance-($nozzle*3)).' F'.$F."\n";
		$gcode.= 'G1 X'.($ox+($nozzle*3)).' Y'.($oy+($nozzle*3)).' E'.ecalc($distance-($nozzle*4)).' F'.$F."\n";
		$gcode.= 'G10 ;retract'."\n";
		$gcode.= ''."\n";

		if($spot_weld=='1'){	//spot weld stuff
			$gcode.= 'G0 X'.($ox+($nozzle*2)).' Y'.($oy+$distance-($nozzle*2)).' F'.($F*2)."\n";	//goto top left
				$gcode.= 'G0 Z'.($Z-$weld_strength)."\n";	//make the weld
				$gcode.= 'G4 P'.$spot_weld_dwell."\n";		//dwell / wait
				$gcode.= 'G0 Z'.($Z+$weld_strength)."\n";	//weld release

			$gcode.= 'G0 X'.($ox+$distance-($nozzle*2)).' Y'.($oy+$distance-($nozzle*2)).' F'.($F*2)."\n";	//goto top right
				$gcode.= 'G0 Z'.($Z-$weld_strength)."\n";
				$gcode.= 'G4 P'.$spot_weld_dwell."\n";
				$gcode.= 'G0 Z'.($Z+$weld_strength)."\n";

			$gcode.= 'G0 X'.($ox+$distance-($nozzle*2)).' Y'.($oy+($nozzle*2)).' F'.($F*2)."\n";	//goto bot right
				$gcode.= 'G0 Z'.($Z-$weld_strength)."\n";
				$gcode.= 'G4 P'.$spot_weld_dwell."\n";
				$gcode.= 'G0 Z'.($Z+$weld_strength)."\n";

			$gcode.= 'G0 X'.($ox+($nozzle*2)).' Y'.($oy+($nozzle*2)).' F'.($F*2)."\n";	//goto bottom left
				$gcode.= 'G0 Z'.($Z-$weld_strength)."\n";
				$gcode.= 'G4 P'.$spot_weld_dwell."\n";
				$gcode.= 'G0 Z'.($Z+$weld_strength)."\n";
		}
	
	}
	$gcode.= 'M107'."\n";
	$gcode.= 'G0 X0 F'.$F."\n";

	echo $gcode;
	die();
}

?>
<style>
	html,body{ font-family:sans-serif;}
</style>
<h2>Layer spot welding test script.</h2><br>
Tested using generic i3 clone printer with repetier firmware.<br>
<br>
Notes:...
<ul>
	<li>Uses firmware retraction!</li>
	<li>Please preheat and purge your extruder to whatever temperature you like</li>
	<li style="color:red;">This is an experiment, it works for me, use at your own risk</li>
</ul>
<br>
<form method="post" action="" target="make_it">
	<table>
		<tr>
			<td>Part Height</td>
			<td><input type="text" name="part_height" value="40">&nbsp;<small>mm</small></td>
		</tr>
		<tr>
			<td>Part Width</td>
			<td><input type="text" name="part_width" value="10">&nbsp;<small>mm</small></td>
		</tr>
		<tr>
			<td>X Offset</td>
			<td><input type="text" name="x_offset" value="50">&nbsp;<small>mm</small></td>
		</tr>
		<tr>
			<td>Y Offset</td>
			<td><input type="text" name="y_offset" value="50">&nbsp;<small>mm</small></td>
		</tr>
		<tr>
			<td>speed</td>
			<td><input type="text" name="speed" value="60">&nbsp;<small>mm/s</small></td>
		</tr>
		<tr>
			<td>Filament width</td>
			<td><input type="text" name="filament" value="1.75">&nbsp;<small>mm</small></td>
		</tr>
		<tr>
			<td>Nozzle diameter</td>
			<td><input type="text" name="nozzle" value="0.4">&nbsp;<small>mm</small></td>
		</tr>
		<tr>
			<td>Layer height</td>
			<td><input type="text" name="layer" value="0.3">&nbsp;<small>mm</small></td>
		</tr>
		<tr>
			<td>Flowrate</td>
			<td><input type="text" name="flow" value="75">&nbsp;<small>%</small></td>
		</tr>
		<tr>
			<td>Cooling</td>
			<td>
				<select name="cooling">
					<option value="0">OFF</option>
					<option value="35">35</option>
					<option value="50">50</option>
					<option value="60">60</option>
					<option value="80">80</option>
					<option value="100" selected>100</option>
				</select>&nbsp;%
			</td>
		</tr>
		<tr>
			<td>Spot weld</td>
			<td>
				<select name="weld">
					<option value="1">ON</option>
					<option value="0" selected>OFF</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Spot weld dwell</td>
			<td><input type="text" name="dwell" value="250">&nbsp;<small>milliseconds</small></td>
		</tr>
		<tr>
			<td>Spot weld distance</td>
			<td><input type="text" name="weld_distance" value="0.3">&nbsp;<small>spot weld Z pinch, mm</small></td>
		</tr>
		
	</table>
	<br>
	<input type="submit" value=" Generate ">
</form>
<iframe name="make_it" border=0 frameborder=0></iframe>
<?


function ecalc($distance){	//keeps track of the extruded filament
	global $e_mm, $E, $layer_height, $nozzle, $filament_width, $flow;
	$e_mm = ($layer_height * $nozzle * $distance) / $filament_width;
	$e_mm = $e_mm * ($flow/100);
	$E+= $e_mm;
	return $E;
}
?>