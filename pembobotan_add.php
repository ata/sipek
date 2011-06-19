<?php
session_start();
include 'connect.php';
if(!isset($_SESSION['user'])){
	header('Location:login.php');
}

if($_SESSION['user']['is_admin'] == '1'){
	header('Location:login.php');
}

$sth = $db->prepare("SELECT * FROM kriteria WHERE parent_id is NULL");
$sth->execute();
$listKriteria = $sth->fetchAll (PDO::FETCH_ASSOC);

$sth = $db->prepare("SELECT id,parent_id FROM kriteria");
$sth->execute();
$listAllKriteria = $sth->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])) {
	$db->beginTransaction();
	$sth = $db->prepare("INSERT INTO periode(nama) VALUES(?)");
	$sth->execute(array($_POST['nama_periode']));
	$periodeId = $db->lastInsertId();
	foreach($_POST['bobot'] as $kriteria_id => $kriteria){
		foreach($kriteria as $pembanding_id => $nilai){
			$sth = $db->prepare("INSERT INTO bobot(
										periode_id, kriteria_id, kriteria_pembanding_id, nilai)
									values(?, ?, ?, ?)");
			$sth->execute(array($periodeId, $kriteria_id, $pembanding_id, $nilai));
		}
	}

	foreach($_POST['bobot_kriteria'] as $kriteria_id => $nilai){
		$sth = $db->prepare("INSERT INTO bobot_kriteria(
										periode_id, kriteria_id, nilai)
									values(?, ?, ?)");
		$sth->execute(array($periodeId, $kriteria_id, $nilai));
	}

	$saved=true;
	$db->commit();
}
?>

<?php include 'header.php'?>
<div class="body" align="center">
	<h1 align="center">Pembobotan Kriteria Balance Scorecard</h1>
	<?php if(isset($saved)):?>
	<div class="message">
		Data Tersimpan!
	</div>
	<?php endif?>
	&nbsp;
	<form method="post">
		<table>
			<tr>
				<td valign="center" class="name">
					<label for="Periode">Nama Periode</label>
				</td>
				<td valign="top" class="value">
					<input type="text" name="nama_periode"/>
				</td>
			</tr>
		</table>
		<br/>
		<br/>
		<div class="dialog">
			<table border="0">
				<tr>
					<td>Perspektif</td>
					<?php foreach($listKriteria as $kriteria):?>
					<td><?php echo $kriteria['nama'] ?></td>
					<?php endforeach ?>
					<td>Jumlah</td>
					<td>Bobot</td>
				</tr>
				<?php foreach($listKriteria as $baris):?>
				<tr>
					<td><?php echo $baris['nama'] ?></td>
					<?php foreach($listKriteria as $kolom):?>
					<td>
						<select <?php echo $baris['id'] == $kolom['id']?'disabled="disabled"':''?> class="bobot_<?php echo $baris['id']?>" name="bobot[<?php echo $baris['id'] ?>][<?php echo $kolom['id'] ?>]" id="bobot_<?php echo $baris['id']?>_<?php echo $kolom['id']?>">
							<option value="9">9</option>
							<option value="8">8</option>
							<option value="7">7</option>
							<option value="6">6</option>
							<option value="5">5</option>
							<option value="4">4</option>
							<option value="3">3</option>
							<option value="2">2</option>
							<option value="1" selected="selected">1</option>
							<option value="0.5">1/2</option>
							<option value="0.33">1/3</option>
							<option value="0.25">1/4</option>
							<option value="0.2">1/5</option>
							<option value="0.17">1/6</option>
							<option value="0.14">1/7</option>
							<option value="0.13">1/8</option>
							<option value="0.11">1/9</option>
						</select>
					</td>
					<?php endforeach ?>
					<td id="jumlah_<?php echo $baris['id']?>" class="jumlah_<?php echo $baris['id']?>"><?php echo count($listKriteria) ?></td>
					<td id="bobot_kriteria_display_<?php echo $baris['id']?>" class="bobot_kriteria_<?php echo $baris['id']?>"> <?php echo round(1 / count($listKriteria),2)?></td>
					<input  id="bobot_kriteria_<?php echo $baris['id']?>" type="hidden" name="bobot_kriteria[<?php echo $baris['id']?>]" value="<?php echo round(1 / count($listKriteria),2)?>"/>
				</tr>
				<?php endforeach ?>
				<tr>
					<td style="text-align:center; font-weight:bold" colspan="<?php echo count($listKriteria) + 1?>"> <b>Total</b></td>
					<td id="total_0" style="font-weight:bold"><?php echo pow(count($listKriteria),2) ?></td>
				</tr>
			</table>
			<br/>
			<?php foreach($listKriteria as $parent):?>
			<?php
			$sth = $db->prepare("SELECT * FROM kriteria WHERE parent_id = :id");
			$sth->execute(array('id' => $parent['id']));
			$listChild = $sth->fetchAll(PDO::FETCH_ASSOC);
			?>

			<?php if (count($listChild)>0):?>

			<div class="body" align="center">
				<h1 align="center">Pembobotan Kriteria Perspektif <?php echo $parent['nama'] ?></h1>
				<table border="0">
					<tr>
						<td>Perspektif</td>
						<?php foreach($listChild as $kriteria):?>
						<td><?php echo $kriteria['nama'] ?></td>
						<?php endforeach ?>
						<td>Jumlah</td>
						<td>Bobot</td>
					</tr>
					<?php foreach($listChild as $baris):?>
					<tr>
						<td><?php echo $baris['nama'] ?></td>
						<?php foreach($listChild as $kolom):?>
						<td>
							<select <?php echo $baris['id'] == $kolom['id']?'disabled="disabled"':''?> class="bobot_<?php echo $baris['id']?>" name="bobot[<?php echo $baris['id'] ?>][<?php echo $kolom['id'] ?>]" id="bobot_<?php echo $baris['id']?>_<?php echo $kolom['id']?>">
								<option value="9">9</option>
								<option value="8">8</option>
								<option value="7">7</option>
								<option value="6">6</option>
								<option value="5">5</option>
								<option value="4">4</option>
								<option value="3">3</option>
								<option value="2">2</option>
								<option value="1" selected="selected">1</option>
								<option value="0.5">1/2</option>
								<option value="0.33">1/3</option>
								<option value="0.25">1/4</option>
								<option value="0.2">1/5</option>
								<option value="0.17">1/6</option>
								<option value="0.14">1/7</option>
								<option value="0.13">1/8</option>
								<option value="0.11">1/9</option>
								</select>
						</td>
						<?php endforeach ?>
						<td id="jumlah_<?php echo $baris['id']?>" class="jumlah_<?php echo $baris['id']?>"><?php echo count($listChild) ?></td>
						<td id="bobot_kriteria_display_<?php echo $baris['id']?>" class="bobot_kriteria_<?php echo $baris['id']?>"> <?php echo round(1 / count($listChild),2)?></td>
						<input  id="bobot_kriteria_<?php echo $baris['id']?>" type="hidden" name="bobot_kriteria[<?php echo $baris['id']?>]" value="<?php echo round(1 / count($listChild),2)?>"/>
					</tr>
					<?php endforeach ?>
					<tr>
						<td style="text-align:center; font-weight:bold" colspan="<?php echo count($listChild) + 1?>"> <b>Total</b></td>
						<td id="total_<?php echo $parent['id'] ?>" style="font-weight:bold"><?php echo pow(count($listChild),2) ?></td>
					</tr>
				</table>
			</div>
			<?php endif ?>
			<?php endforeach ?>
			<div class="buttons" align="center">
				<span class="button"><input class="save" name="submit" type="submit" value="Create" /></span>
			</div>
		</div>
	</form>
</div>
<script type="text/javascript">

Number.implement({
	'round': function(dec){
		return Math.round(this * Math.pow(10,dec))/Math.pow(10,dec);
	}
});

var hitung_jumlah = function(kriteria_id, parent_id) {
	document.id('jumlah_' + kriteria_id).set('html',(function(){
		var old_jumlah = document.id('jumlah_' + kriteria_id).get('html').toFloat();
		var new_jumlah = 0;
		$$('.bobot_' + kriteria_id).each(function(e_bobot){
			new_jumlah += e_bobot.get('value').toFloat();
		});
		new_jumlah = new_jumlah.round(2);
		document.id('total_' + parent_id).set('html', (function(){
			var total = document.id('total_' + parent_id).get('html').toFloat() - old_jumlah + new_jumlah;
			document.id('bobot_kriteria_' + kriteria_id).set('value', (new_jumlah / total).round(2));
			document.id('bobot_kriteria_display_' + kriteria_id).set('html', (new_jumlah / total).round(2));
			return total.round(2);
		})());
		return new_jumlah;
	})());
}


var hitung_ahp = function(kriteria_id, parent_id) {
	parent_id = parent_id == null ? 0 : parent_id;
	$$('.bobot_' + kriteria_id).each(function(eselect, index){
		eselect.addEvent('change',function(){
			hitung_jumlah(kriteria_id, parent_id);
			var arr = eselect.get('id').replace('bobot_','').split('_');
			var new_id = 'bobot_' + arr[1] + '_' + arr[0];
			document.id(new_id).set('value',(function(){
				var nilai = eselect.get('value');
				if(nilai > 1) {
					return (1 /  nilai).round(2);
				}
				return Math.round(1 / nilai);
			})());
			hitung_jumlah(arr[1], parent_id);
		});
	});
};
var penyesuaian_bobot = function(kriteria_id) {

};
window.addEvent('domready',function(){
	<?php echo json_encode($listAllKriteria)?>.each(function(kriteria){
		hitung_ahp(kriteria['id'], kriteria['parent_id']);
	});
});
</script>
<?php include 'footer.php' ?>