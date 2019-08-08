<style type="text/css">
	.formm{
		background-color: #1e282c;
		width: 50%; 
		padding: 10px; 
		font-family: Trebuchet MS; 
		border-bottom: 5px solid #3c8dbc; 
		border-left: 5px solid #3c8dbc; 
		color: #fff; 
		font-size: 13pt;
	}
	.fieldsett{
		width: 45%;
		float: right; 
		position: relative; 
		top: -320px; 
		padding: 10px; 
		text-align: center; 
		font-family: Trebuchet MS; 
		margin-bottom: 10px
	}
</style>

<?php
include_once 'koneksi.php';

	$query_select_ontologi = "SELECT kata_dasar, COUNT(kata_dasar) AS jumlah from hasil group by kata_dasar ORDER BY jumlah DESC limit 5";
	$hasil_select_ontologi = mysqli_query($koneksi, $query_select_ontologi);
		$n = 0;

		foreach ($hasil_select_ontologi as $hasil_ontologi) {
			$n++;
			?>
				<form action="" method="post">
					<input class="formm" type="submit" name="keyword_ontologi" value="<?php echo $hasil_ontologi['kata_dasar'];?>" >
				</form>
			<?php
		}
	?>

<fieldset class="fieldsett">
	<legend>The Same</legend>
		<?php
			if (isset($_POST['keyword_ontologi'])) {
				$url = file_get_contents('http://kateglo.com/api.php?format=json&phrase='.$_POST['keyword_ontologi'].'');	
				$character = json_decode($url);
				$penampung = $character->kateglo->all_relation;

				foreach ($penampung as $k) {
					echo $k->related_phrase;
					echo "<br>";
				}
			}
		?>
</fieldset>