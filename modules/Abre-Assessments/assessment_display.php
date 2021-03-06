<?php

	/*
	* Copyright (C) 2016-2018 Abre.io Inc.
	*
	* This program is free software: you can redistribute it and/or modify
    * it under the terms of the Affero General Public License version 3
    * as published by the Free Software Foundation.
	*
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    * GNU Affero General Public License for more details.
	*
    * You should have received a copy of the Affero General Public License
    * version 3 along with this program.  If not, see https://www.gnu.org/licenses/agpl-3.0.en.html.
    */

	//Required configuration files
	require_once(dirname(__FILE__) . '/../../core/abre_verification.php');
	require(dirname(__FILE__) . '/../../configuration.php');
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');
	require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
	require_once('functions.php');
	require_once('permissions.php');

	if($pagerestrictions=="")
	{

		$token=getCerticaToken();

		?>
		<script src='https://cdn.certicasolutions.com/sdk/js/sdk.itemconnect.min.js?x-ic-credential=<?php echo $token; ?>'></script>
		<script src='https://cdn.certicasolutions.com/player/js/player.itemconnect.min.js'></script>
		<link rel="stylesheet" href='https://cdn.certicasolutions.com/player/css/player.itemconnect.min.css'>
		<?php

		$Assessment_ID=htmlspecialchars($_GET["id"], ENT_QUOTES);
		$sqllookup = "SELECT Title, Owner, Editors, Grade, Subject, Locked FROM assessments WHERE ID='$Assessment_ID' AND (Owner='".$_SESSION['useremail']."' OR Editors LIKE '%".$_SESSION['useremail']."%' OR Shared=1)";
		$result2 = $db->query($sqllookup);
		$setting_preferences=mysqli_num_rows($result2);
		while($row = $result2->fetch_assoc())
		{
			$Title=htmlspecialchars($row["Title"], ENT_QUOTES);
			$Owner=htmlspecialchars($row["Owner"], ENT_QUOTES);
			$Editors=htmlspecialchars($row["Editors"], ENT_QUOTES);
			$Grade=htmlspecialchars($row["Grade"], ENT_QUOTES);
			$Subject=htmlspecialchars($row["Subject"], ENT_QUOTES);
			$Locked=htmlspecialchars($row["Locked"], ENT_QUOTES);

			//Check to see if allowed to edit
			$access=0;
			if($Owner==$_SESSION['useremail']){ $access=1; }
			if(strpos($Editors, $_SESSION['useremail']) !== false){ $access=1; }

			echo "<div class='page_container'>";
				echo "<div class='row'><div class='center-align' style='padding:20px;'><h3 style='font-weight:600;'>$Title";
					if($Locked==1){ echo " <i class='material-icons'>lock</i>"; }
				echo "</h3><h6 style='color:#777;'>$Subject &#183; Grade Level: $Grade &#183; Total Points: <span id='totalpoints'>0</span></div></div>";

				echo "<ul class='collapsible popout questionsort' data-collapsible='accordion'>";

					$sqllookup2 = "SELECT ID, Bank_ID, Points FROM assessments_questions WHERE Assessment_ID='$Assessment_ID' ORDER BY Question_Order";
					$result3 = $db->query($sqllookup2);
					$unitcount=mysqli_num_rows($result3);
					$questioncount=0;
					while($row2 = $result3->fetch_assoc())
					{
						$questioncount++;
						$questionid=htmlspecialchars($row2["ID"], ENT_QUOTES);
						$Bank_ID=htmlspecialchars($row2["Bank_ID"], ENT_QUOTES);
						$Points=htmlspecialchars($row2["Points"], ENT_QUOTES);
						if($Points==""){ $Points=0; }

						echo "<li style='position:relative' id='item-$questionid' class='topicholder'>";
							echo "<div class='collapsible-header unit' data-bankid='$Bank_ID'>";
					    		echo "<i class='material-icons' style='font-size: 36px; color:".getSiteColor()."'>fiber_manual_record</i>";

								echo "<span style='position:absolute; right:0; z-index:1000; cursor:move;' class='mdl-color-text--grey-700";
									if($Locked!=1 && $access==1){ echo " handle"; }
								echo "'>";
										if($Locked!=1 && $access==1){ echo "<i class='material-icons'>reorder</i>"; }
								echo "</span>";

								echo "<span class='title truncate' style='margin-right:40px;'><b>Question <span class='index'>$questioncount</span></b></span>";
							echo "</div>";

							echo "<div class='collapsible-body mdl-color--white' style='padding:25px'>";

								//Display the question
								echo "<div id='questionplayerloader-$Bank_ID' style='display:none;'><div id='p2' class='mdl-progress mdl-js-progress mdl-progress__indeterminate' style='width:100%'></div></div>";
								echo "<div id='questionplayer-$Bank_ID'></div>";
								echo "<hr>";
								echo "<div class='toolbar' style='height:60px;'>";
									if($Locked!=1 && $access==1)
									{
										echo "<div style='float:right;'>";
											echo "<div class='input-field' style='float:left; width:45px;'>";
									        	echo "<input class='questionpoints' id='$questionid' type='number' min='0' style='text-align:center;' value='$Points'>";
									        echo "</div>";

									        echo "<div style='float:left; width:60px; margin:22px 0 0 5px;'>";
									        	echo "<span class='mdl-color-text--grey-700'>points</span>";
									        echo "</div>";

											echo "<div style='float:left; width:25px; margin:22px 0 0 10px;'>";
												echo "<a href='modules/".basename(__DIR__)."/question_remove_process.php?questionid=".$questionid."' class='removequestion' id='delete'><i class='material-icons mdl-color-text--grey-700'>delete</i></a>";
												echo "<div class='mdl-tooltip' data-mdl-for='delete'>Delete Question</div>";
											echo "</div>";
										echo "</div>";
									}
								echo "</div>";

							echo "</div>";
						echo "</li>";

					}

				echo "</ul>";

			if($unitcount==0){
				if($Locked!=1 && $access==1)
				{
					echo "<div class='center-align'>Click the '+' in the bottom right to add a question to this assessment.";
				}
				else
				{
					echo "<div class='center-align'>This assessment is locked. The owner must unlock before this assessment can be modified.";
				}
			}

			echo "</div>";

			if($Locked!=1 && $access==1){ include "question_button.php"; }

		}

		if($setting_preferences==0){ echo "<div class='row center-align'><div class='col s12'><h6>You do not have access to this assessment.</h6></div></div>";  }

		$db->close();

	}

?>

	<script>

		$(function()
		{

			//Calculate Total Points
			function totalPoints()
			{
				var totalpoints = 0;
				var points = 0;
				$('.questionpoints').each(function()
				{
					points = $(this).val();
					if(isNaN(parseFloat(points))){ points=0; }
					totalpoints += parseFloat(points);
				});
				$('#totalpoints').text(totalpoints);
			}
			totalPoints();

			//Remove topic from assessment
			$( ".removequestion" ).click(function() {
				event.preventDefault();
				var result = confirm("Remove this question?");
				if (result) {
					$(this).closest(".topicholder").hide();
					var address = $(this).attr("href");
					$.ajax({
						type: 'POST',
						url: address,
						data: '',
					})

					//Show the notification
					.done(function(response) {
						$("#content_holder").load( "modules/<?php echo basename(__DIR__); ?>/assessment.php?id="+<?php echo $Assessment_ID; ?>, function(){
							mdlregister();

							var notification = document.querySelector('.mdl-js-snackbar');
							var data = { message: response };
							notification.MaterialSnackbar.showSnackbar(data);

						});
					})

				}
			});

			//Call accordion
			$('.collapsible').collapsible({ });

			//Close Menu when collapsible closed
			$( ".menuui" ).unbind().click(function(event)
			{
 				$(".collapsible-header").removeClass(function(){
 					return "active";
  				});
  				$(".collapsible").collapsible({accordion: true});
				$(".collapsible").collapsible({accordion: false});
 			});

			//Load the question
			$(".collapsible-header").unbind().click(function(event)
			{
				var Bank_ID= $(this).data('bankid');
				var timeout = setTimeout(function(){ $('#questionplayerloader-'+Bank_ID).show(); }, 1000);
 				$('#questionplayer-'+Bank_ID).hide();
 				$('.toolbar').hide();

 				$.get( "modules/<?php echo basename(__DIR__); ?>/question_viewer.php", { id: Bank_ID } )
			    .done(function( data ) {
				    clearTimeout(timeout);
				    $('#questionplayerloader-'+Bank_ID).hide();
				    $('#questionplayer-'+Bank_ID).show();
				    $('.toolbar').show();
			    	$("#questionplayer-"+Bank_ID).html( data );
			  	});

 			});

			//Question sorting
			updateIndex = function(e, ui) {
			    $('.index', ui.item.parent()).each(function (i) {
			        $(this).html(i + 1);
			    });
			};
			$( ".questionsort" ).sortable({
				axis: 'y',
				handle: '.handle',
				helper: 'clone',
				stop: updateIndex,
				update: function(event, ui){

					//Sent Form Data
					var data = $(this).sortable('serialize');
					$.ajax({
			            data: data,
			            type: 'POST',
			            url: '/modules/<?php echo basename(__DIR__); ?>/questions_save_order.php'
			        });

				}
			});

			//Update points
			$( ".questionpoints" ).change(function() {

				//Save Points
				var questionid = $(this).attr('id');
				var questionvalue = $(this).val();
				$.post( "/modules/<?php echo basename(__DIR__); ?>/question_savepoints.php", { questionid: questionid, questionvalue: questionvalue })

				//Update Total Points
				totalPoints();

			});

		});

	</script>