<style>
	body
	{
		box-shadow: inset 0 0 100px rgba(0,0,0,.5);
		background: #373737 url("./assets/image/mine.jpg") repeat left top;		
	}
</style>
	<div class="body container-fluid">
		<div class="row">
			<div class="col-md-8 col-xs-6 main">
				<form method="post" id="form_inp" role="form" action="index.php">
					<h2 class="page-header">Input Sentences</h2>	
					<br>			
						<!-- Multiple Radios (inline) -->
						<div class="form-group pull-right">
						  <div class="btn-group" data-toggle="buttons">
						    <label id="lang_id" class="btn btn-lg btn-default btn-noclick">
						      <input type="radio" name="lang" required> ID
						    </label>
						    <label id="lang_en" class="btn btn-lg btn-default btn-noclick">
						      <input type="radio" name="lang" required> EN
						    </label>
						  </div>            
						</div> 
					<br>										
						<div class="form-group">
							<textarea class="form-control" name="search_sen" cols="30" rows="8" placeholder="Input a sentences" required></textarea>
						</div>

						<div align="center" class="form-group">
							<input type="submit" name="submit" class="btn btn-lg btn-default" value="View Results">
						</div>
				</form>

			</div>
			<div class="col-md-4 col-xs-6 sidebar">
			<h3 class="page-header text-left">Mining Result <span class="pull-right glyphicon glyphicon-list-alt"></span></h3>
			<?php 
				$submit = $_POST[submit];
				$lang_get = $_GET[lang];				
				if( ! isset($submit))
				{
					echo "<div style='display:block; padding:10px;' class='bg-primary'> Nothing to display.</div>";
				}
				else
				{
					$sentences 	= $_POST['search_sen'];
					$origin_sen = $sentences;
					if($lang_get == "id")
					{
						// Translate bahasa lebih dulu
						$sentences_id 	= $sentences;
						include "id_en_transl.php";
						$sentences 		= $translatedStr;

						$text_f 		= "Translate from (id): <strong>$sentences_id</strong> <br>
											to (en): <u>$sentences</u>";
					}
					else if($lang_get == "en")
					{
						// English Dict langsung scoring
						$text_f 		= "Display result from (en): <u>$sentences</u>";
					}

					// Lalu jalankan perintah
					$sentences 	= preg_replace('/[^a-z]+/i', '__', $sentences); 
					$sentences 	= explode('__', $sentences);
					$total_score= 0;
					$per_phrase = "";

					// Cari nilai perkata kemudian jumlahkan
					foreach ($sentences as $phrase_p) 
					{
						$phrase_p 	= str_replace("'", "`", $phrase_p);
						$phrase_p 	= strtolower($phrase_p);
						$sql 		= "SELECT synset_term, pos_score, neg_score FROM dbtxt WHERE synset_term LIKE \"$phrase_p#%\" OR synset_term LIKE \"% $phrase_p#%\"";
						$exe_sql	= mysql_query($sql);
						$phrase 	= $phrase_p;

						$pos_score 	= 0;
						$neg_score 	= 0;
						$cnt_score 	= 0;
						while ($arr_sql = mysql_fetch_array($exe_sql))
						{
							$pos_score += $arr_sql[pos_score];
							$neg_score += $arr_sql[neg_score];
							$cnt_score += 1;
						}
						// $score 		= $out_sql[pos_score] - $out_sql[neg_score];

						//Prevent div by 0
						if($cnt_score == 0)
						{
							$cnt_score += 1;
						}

						$score_		 = $pos_score - $neg_score;
						$score_		 = $score_ / $cnt_score;
						$score 		 = round($score_, 2);

						$per_phrase .= 	"<tr><td>".$phrase."</td>";
						// Per Phrase score color
						if ($score < 0) 
						{
 							$per_phrase .= "<td class='bg-danger'>".$score."</td></tr>";
						}
						else if ($score > 0)
						{
							$per_phrase .= "<td class='bg-success'>".$score."</td></tr>";	
						}
						else
						{
							$per_phrase .= "<td>".$score."</td></tr>";
						}
										// "<td>".$score."</td></tr>";
						// $per_phrase .= 	"<tr><td>".$phrase."</td>".
						// 				"<td>".$score."</td></tr>";

						$total_score += $score;
					} // Close foreach sentence

					// Total Score Color
					if ($total_score < 0) 
					{
							$total_score = "<tr class='bg-danger'>
												<td><strong>Total Score</strong></td>
												<td><strong>".$total_score."</strong></td>
											</tr>";
					}
					else if ($total_score > 0)
					{
							$total_score = "<tr class='bg-success'>
												<td><strong>Total Score</strong></td>
												<td><strong>".$total_score."</strong></td>
											</tr>";
					}
					else
					{
							$total_score = "<tr class='bg-info'>
												<td><strong>Total Score</strong></td>
												<td><strong>".$total_score."</strong></td>
											</tr>";
					}					
			?>
				<p><?php echo $text_f; ?></p>
				<table class="table table-responsive">
					<thead class="bg-primary">
						<th>Sentences</th>
						<th>Score</th>
					</thead>
					<tbody>
						<?php echo $per_phrase; ?>
						<?php echo $total_score; ?>
					</tbody>
				</table>
				<p class="text-center"> ... </p>
			<?php
				} // Close Else ! isset
			?>			
				<!-- Footer Copyright -->
				<div align="center" class="copyright hidden-xs"> &copy; 2016 Sentinesia <br>Muhammadiyah University of Surakarta</div>			
			</div> <!-- Close class="col-md-3 sidebar -->
		</div> <!-- Close container-fluid -->
	</div>
	<script>
		$("#lang_id").click(function(){
			$("#form_inp").attr({
				"action" : "./?p=try&lang=id"
			});			
		});
		$("#lang_en").click(function(){
			$("#form_inp").attr({
				"action" : "./?p=try&lang=en"
			});			
		});
		$("#try").addClass("active");
	</script>	