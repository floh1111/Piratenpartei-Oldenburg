<?php

//CSV von ISO-8859-15 nach UTF-8 konvertieren
//iconv -t UTF-8 -f ISO-8859-15 ./Wahlbezirke_nach_Strassen27012012.csv > ./Wahlbezirke_nach_Strassen27012012.csv.utf8

header("Content-Type: text/html; charset=UTF-8");

//Ordne den Wahlkreisen die enthaltenen Wahlbereiche zu
$wk2wb[62] = array(1,2,5);
$wk2wb[63] = array(3,4,6);

//Lies das CSV-Straßenverzeichnis in einen String
$csv = file_get_contents("Wahlbezirke_nach_Strassen27012012.csv.utf8");

//Lies den CSV-String in ein zweidimensionales Array
$csv = str_getcsv($csv, "\n"); //parse the rows
foreach($csv as &$Row) $Row = str_getcsv($Row, ";"); //parse the items in rows 

//Erzeuge Liste der enthaltenen Straßen für das Dropdownmenü
$streets = array();
foreach($csv as $entry) {
    if(!in_array($entry[0], $streets))
    $streets[] = $entry[0];
}
sort($streets);

?>

<h2>Landtagswahlkreis Suche für die Stadt Oldenburg</h2>
<p>Bitte gib deinen Wohnort an:</p>

<form action="./index.php" method="POST">
	Straße: 
	<select name="street">
		<?php foreach($streets as $street) { ?>
		<option value="<?php echo $street;?>" <?php if($_POST['street']==$street) echo "selected";?>><?php echo $street;?></option>
		<?php }?>
	</select><br>
	Hausnummer: <input name="house_number" type="number" size="3" value="<?php if(isset($_POST['house_number'])) echo $_POST['house_number']; ?>"><br>
	Zusatz: <input name="house_number_letter" size="1" value="<?php if(isset($_POST['house_number_letter'])) echo $_POST['house_number_letter']; ?>">
	<p><input type="submit" value="Wahlkreis suchen"></p>
</form>

<?php


$csvKey = -1;
if(!empty($_POST['street']) AND !empty($_POST['house_number'])) {
	//durchlaufe verzeichnis
	foreach($csv as $key=>$entry) {
		if($entry[0] == $_POST['street']) { //wenn straße gefunden
			if($entry[1] == "alle") { //wenn eintrag für alle nummern der Straße gilt
				$csvKey = $key;
				break;
			} elseif($_POST['house_number']==$entry[1] AND empty($entry[4])) { // wenn eintrag für eine bestimmte hausnummer gilt
				if(empty($_POST['house_number_letter']) AND empty($entry[2])) { //wenn eintrag für eine bestimmte hausnummer ohne buchstabenzusatz gilt
					$csvKey = $key;
					break;
				} elseif (strcasecmp($_POST['house_number_letter'], $entry[2])==0) { //wenn eintrag für eine bestimmte hausnummer mit buchstabenzusatz gilt
					$csvKey = $key;
					break;
				}
			} elseif((is_numeric($entry[1]) AND is_numeric($entry[4])) AND ($_POST['house_number']>=$entry[1] AND $_POST['house_number']<=$entry[4]))  { // wenn Eintrag nur für einen bestimmten Hausnummernraum der Straße gilt
				$csvKey = $key;
				break;
			}
		}
	}

	//Wenn die angegebene Straße nicht gefunden werden konnte
	echo "<h3>Ergebnis</h3>";
	if($csvKey==-1) { 
		echo "Es tut uns leid, die eingegebene Adresse existiert anscheinend nicht. Wohnst du nicht in Oldenburg? Sollte dies ein Fehler sein, nimm bitte Kontakt mit uns per Email unter <a href='mailto:kontakt@piratenpartei-oldenburg.de'>kontakt@piratenpartei-oldenburg.de</a> auf und teile uns mit welche Daten du eingegeben hast. Danke.</p>";
	} else {
		foreach($wk2wb as $wk=>$wbs) {
			if(in_array(floor($csv[$csvKey][8]/100), $wbs)) {
				echo "<p>Du wohnst im Landtagswahlkreis <b>".$wk."</b>.<br>Um deinen Direktkandidaten zu unterstützen, fülle bitte folgendes Formular aus und sende es an unseren Vorsitzenden Clemens John, Hamelmannstraße 12, 26129 Oldenburg: <a href='http://piratenpartei-oldenburg.de/data/wahlen/landtagswahl/2013/formblatt_unterstuetzerunterschrift_ltwnds13_wk$wk.pdf'>Formblatt für den Wahlkreis $wk</a></p>";
/*				echo "<pre>";
				print_r($csv[$csvKey]);
				echo "</pre>";*/
			}
		}
	}
	echo "<p>Zur Website der <a href='https://piratenpartei-oldenburg.de'>Piratenpartei Oldenburg</a></p>";

}

?>