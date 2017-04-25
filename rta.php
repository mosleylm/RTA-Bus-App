<?php
/*
Liam Mosley
CSE 551 Web Services, Dr Campbell

Main page for RTA Bus App, responsive design implemented w/ bootstrap
From here -> choose route -> DROP DOWN APPEARS -> Choose Stop
At any time you can "hit RTA" at the top and it takes you back to route screen
You can choose a different stop at the top and different times will appear

DISPLAYS NEXT FOUR TIMES, scroll for more

*/
include('httpful.phar');

function convertTime($tmToCv) {
	$tmToCv = $tmToCv * 86400;
	$hr = floor($tmToCv / 3600);
	$min = floor($tmToCv / 60 % 60);
	$sc = floor($tmToCv % 60);
		
	//echo(gmdate("H:i", $tmToCv));

	//if($min < 10) 
	
	if($hr >= 12) {
		$hr = $hr - 12;
		echo(gmdate("h:i", $tmToCv)."pm");
	} else {
		echo(gmdate("h:i", $tmToCv)."am");
	} 
}

?>
<!DOCTYPE HTML>
<HTML>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="style.css"> 
</head>
<body>
	<div class = "container-fluid">
		<div class="row">
			<div class="col-md-12">
				<h1 class="text-center"><a href="rta.php">RTA App<div>by Liam Mosley</div></a></h1>
			</div>		
		</div>
		<div class="form-group">
			&nbsp;
		</div>
		<div class="row">	
			<div class="col-md-12 text-center">
				<form action="#" method="post">
					<div class="form-group">
						<select class="selectpicker input-lg" style:'btn-lg' name="route" value="<?php echo $_POST['route'];?>" <?php if(isset($_POST['route'])) {?> disabled<?php }; ?> >
						<?php
							$response = \Httpful\Request::get('ceclnx01.cec.miamioh.edu/~mosleylm/RTA/rtaServer')->send();
							if(isset($response->body->response->error)) {
								
							} else {
								$response = json_decode($response);
								$routes = $response->rts;
								
								foreach($routes as $rte) {
									?>
									<option <?php if(isset($_POST['route']) && $_POST['route'] == $rte) { ?>selected <?php } ?>><?php echo($rte); ?></option>
									<?php
								}
							}
						?>
						</select>
					</div>
				<?php 
				if(!isset($_POST['route'])) { ?>
					<input type="submit" class="btn btn-primary"/>
				<?php
				} else { ?>
					<div class="form-group">
						<select class="selectpicker input-lg" style:'btn-lg' name="stop">
					<?php
						$rte = htmlspecialchars($_POST['route']);

						$response = \Httpful\Request::get('ceclnx01.cec.miamioh.edu/~mosleylm/RTA/stops/'.$rte)->send();
						
						if(isset($response->body->response->error)) {
						} else {
							$response = json_decode($response);
							$stops = $response->stps;

							foreach($stops as $stp) {
							?>
								<option <?php if(isset($_POST['stop']) && $_POST['stop'] == $stp) { ?>selected <?php } ?>><?php echo($stp); ?></option>
							<?php
							}
						}				
					?>
						</select>	
					</div>
					<input type="hidden" name="route" value="<?php echo($_POST['route']); ?>">
					<input type="hidden" name="offset" value=4>
					<input type="submit" class="btn btn-primary"/>
				<?php
				} ?>
				</form>
			</div>
		</div>
	<?php
	
	$secondsInDay = 86400;
	
	if(isset($_POST['route']) && isset($_POST['stop'])) { ?>
		<div class = "form-group">
			&nbsp;
		</div>
		<div class = "row">
		<div class = "table-responsive">	
			<table class="table text-center table-condensed table-scrollable">
				<thead>
					<tr>
						<th>Next stop times for: <?php echo($_POST['route']." at ".$_POST['stop']); ?></th>
					</tr>
				</thead>
				<tbody>
			<?php
				$route = htmlspecialchars($_POST['route']);
				$offset = htmlspecialchars($_POST['offset']);
				$stop = htmlspecialchars(str_replace(' ', '_', $_POST['stop']));

				$response = \Httpful\Request::get('ceclnx01.cec.miamioh.edu/~mosleylm/RTA/times/'.$route.'/'.$stop.'/'.$offset)->send();
				
				if(isset($response->body->response->error)) {

				} else {
					$response = json_decode($response);
					$times = $response->tms;
					
					foreach($times as $tm) {
					?>
						<tr><td><?php convertTime($tm); ?></td></tr>					
					<?php
					}

				}			
			?>
				</tbody>
			</table>
		</div>
		<div>
			<form action="#" method="post">
				<input type="hidden" name="offset" value=<?php $offs = $_POST['offset'] + 4; echo($offs); ?>>
				<input type="hidden" name="route" value="<?php echo($_POST['route']); ?>">
				<input type="hidden" name="stop" value="<?php echo($_POST['stop']); ?>">
				<button type="submit" class="btn btn-primary">Get more stop times</button>
			</form>
		</div>
		</div>
	<?php	
	}
	?>
	</div>
</body>
</HTML>
