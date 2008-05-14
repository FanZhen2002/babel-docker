<?php
/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation
*******************************************************************************/

require_once("cb_global.php");

$string_id = $App->getHTTPParameter("string_id", "POST");
$stringTableIndex = $App->getHTTPParameter("stringTableIndex", "POST");

if(isset($_SESSION['language']) and isset($_SESSION['version']) and isset($_SESSION['project'])){
	$language = $_SESSION['language'];
	$version = $_SESSION['version'];
	$project_id = $_SESSION['project'];
}else{
	return false;
}


$query = "select 
			strings.string_id,
			strings.value as string_value,
			translations.value as translation_value,
			files.name,
			strings.name as token,
			max(translations.version)
		  from
		  	files,
		  	strings
		  	left join translations on
		  		(strings.string_id = translations.string_id 
		  		 and 
		  		 translations.is_active != 0 
		  		 and 
		  		 translations.language_id = '".addslashes($language)."')
		  where
		  	strings.is_active != 0
		  and
			  strings.string_id = '".addslashes($string_id)."'
		  and
		  	  strings.file_id = files.file_id
		  and
		  	  files.version = '".addslashes($version)."'
		  group by translations.version
		  order by translations.version desc
		  limit 1
			";

//print $query;

$res = mysql_query($query,$dbh);

$line = mysql_fetch_array($res, MYSQL_ASSOC);

//print_r($line);

$trans = "";

if($line['translation_value']){
	$trans = " AND translations.value = '".addslashes($line['translation_value'])."'  			
				AND 
			  translations.is_active = 1
	";
}else{
//	$trans = "translations.value is NULL ";
}

$query = "select 
				strings.string_id, strings.value, strings.name max(translations.translation_id)
			FROM 
				files,
				strings								
			left join 
				translations 
			on 
				translations.string_id = strings.string_id 
			where
				files.file_id = strings.file_id 
			AND			
				files.project_id = '".addslashes($project_id)."' 
			AND 
				strings.value = '".addslashes($line['string_value'])."'

				$trans
			AND
				files.is_active = 1
				group by translations.string_id
				";
//			AND 
//				files.name = (SELECT files.name FROM files as F where F.project_id = '".addslashes($project_id)."')
				
$query = "SELECT 
			S.*
		  FROM 
		  	strings AS S 
		  inner join files AS F on F.file_id = S.file_id 
		  inner join translations AS T on T.string_id = S.string_id 
		  where 
		  	F.project_id = '".addslashes($project_id)."' 
		  AND 
		  	F.file_id in (SELECT files.file_id FROM files where files.project_id = '".addslashes($project_id)."') 
		  AND 
		  	S.value = '".addslashes($line['string_value'])."'
		  and 
		  	T.value = '".addslashes($line['translation_value'])."' 
		  AND 
		  	T.is_active = 1
		  	";

//INSERT INTO translations SELECT S.string_id, 2, "Some Enhanced Text", other fields.....  FROM strings AS S inner join files AS F on F.file_id = S.file_id inner join translations AS T on T.string_id = S.string_id where F.project_id = "eclipse" AND F.name=(SELECT files.name FROM files where file_id = 7) AND S.name="pluginName" and T.value = "Some Old Text" AND T.is_active = 1				
				
//print $query;


/*
$res = mysql_query($query,$dbh);
while($same_trans = mysql_fetch_array($res, MYSQL_ASSOC)){
	print "<pre>--";
	print_r($same_trans);
	print "</pre>";
}
*/
?>
<form id='translation-form'>
	<input type="hidden" name="string_id" value="<?=$line['string_id'];?>">
	<input type="hidden" name="stringTableIndex" value="<?=$stringTableIndex;?>">

	<div id="english-area" class="side-component">
		<h4>English String</h4>
		<div style='overflow: auto; height: 80px;'>
			<b><?= htmlspecialchars(nl2br($line['string_value']));?></b>
		</div>
		<h4>Externalized Token</h4>
		<div style='overflow-x: hidden; overflow-y: auto; height: 80px;'>
		<?= htmlspecialchars_decode(nl2br($line['token']));?>
		</div>		
	</div>
	<div id="translation-textarea" class="side-component">
		<h4>Current Translation</h4>
		<textarea style='display: inline; width: 320px; height: 150px;' name="translation"><?=(($line['translation_value']));?></textarea>
		<br>
		<button id="allversions" type="submit" name="translateAction" value="All Versions">All Versions</button>
		<button id="onlysametrans" type="submit" name="translateAction" value="Only Version <?=$_SESSION['version']?>">Only Version <?=$_SESSION['version']?></button>
	</div>	
	<div id="translation-history-area" class="side-component">
		<h4>History of Translations</h4>
		<div id="translation-history">
		
		<table>
		<?php
			$query = "select value,first_name,last_name,translations.created_on from translations,users where string_id = '".addslashes($line['string_id'])."' and language_id = '".addslashes($language)."' and translations.userid = users.userid order by translations.created_on desc";
			$res_history = mysql_query($query,$dbh);
			
			if(!mysql_num_rows($res_history)){
				print "No history.";
			}else{		
				while($line = mysql_fetch_array($res_history, MYSQL_ASSOC)){
					print "<tr>";
					print "<td width='40%'>";
					print "<div>".htmlspecialchars($line['value'])."</div>";
					print "</td>";
					print "<td width='20%'>";
					print $line['first_name']." ".$line['last_name'];
					print "</td>";
					print "<td width='40%'>";
					print $line['created_on'];
					print "</td>";
					print "</tr>";
				}
			}
		?>
		</table>
		</div>
	</div>
</form>