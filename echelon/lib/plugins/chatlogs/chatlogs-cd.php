<?php
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'chatlogs-cd.php' == basename($_SERVER['SCRIPT_FILENAME']))
  		die ('Please do not load this page directly. Thanks!');

		
	
if(!empty($tables_info)) {
	$tables = $tables_info;
} else
	$tables = array(0 => 'chatlog');

$num_tables = count($tables); // number of tables to pull data from

$limit_rows = 100;

$i = 0; // start counter at 0
$total_overall_rows = 0; // set default value to 0

## Loop thro the tables and retrieve the relevant data
while($i < $num_tables) : // write and preform query for each server
		
	// create query array
	$query = array();
	
	$table_name = $tables[$i];
	
	if(empty($table_name))
		$table_name = 'chatlog';
	
	// write query
	$query[$i] = "SELECT id, msg_time, msg_type, msg FROM $table_name WHERE client_id = $cid ORDER BY msg_time DESC LIMIT $limit_rows";

	$db = DB_B3::getPointer();
	
	// run query
	$results = $db->mysql->query($query[$i]) or die('DB Error');
	
	while($row = $results->fetch_object()) :
		$records[$i][] = array(
			'id' => $row->id,
			'msg_time' => $row->msg_time,
			'msg_type' => $row->msg_type,
			'msg' => $row->msg
		);
	endwhile;
	
	// find num of rows found
	$num_rows_[$i] = $results->num_rows;
	
	// start count on num of total overall rows
	$total_overall_rows = $total_overall_rows + $num_rows_[$i]; // keeps last loop number plus addition of this loops num_rows
	
	// add 1 to counter
	$i++;
	$results = NULL;
			
endwhile; // end while looping thro all tables to find any records


## Spit out content if there is any ##
if($total_overall_rows > 0) :  // if total recordset not empty

echo '<div class="tab-content" id="chatlog">
	<div id="cd-chat-table" class="tab-pane fade table table-hover table-responsive table-sm active show"  role="tabpanel" aria-labelledby="-tab">';
	## RECORDS
	
	echo '<div id="chats-box">';
	
	$i = 0;

	while ($i < $num_tables) : // loop for 1 tab per server ?>
	
		<div id="chat-tab-<?php echo $i; ?>" class="chat-content">
			<?php if($num_rows_[$i] == 0) { ?>
					<table style="display: none;">
			<?php } else { ?>
					<table width="100%">
			<?php } ?>
			
			<thead>
				<tr>
					<th>ID</th>
					<th>Scope</th>
					<th>Message</th>
					<th>Time</th>
                    <th>Server</th>
				</tr>
			</thead>
			<tfoot>
				<tr><td colspan="4"></td></tr>
			</tfoot>
	
		<?php // nested while loop for content
			
			if($num_rows_[$i] > 0) :
				
				foreach($records[$i] as $record) : //there are still rows in results
					
					$id = $record['id'];
					$time = date($tformat, $record['msg_time']);
					$type = tableClean($record['msg_type']);
					$msg = tableClean($record['msg']);
                    $server = $tables_names[$i];
					
					## Highlight Commands ##
					if (substr($msg, 0,1) == '!' or substr($msg, 0,1) == '@')
						$msg = '<span class="chat-cmd">'. $msg ."</span>"; 
					
					## Row color
					$alter = alter();
					
					## preapre heredoc
					$data = <<<EOD
					<tr class="$alter">
						<td>$id</td>
						<td>$type</td>
						<td>$msg</td>
						<td><em>$time</em></td>
                        <td>$server</td>
					</tr>
EOD;
					
					echo $data; // echo content
					
				endforeach;
				
			endif;
			
		?>
			
		</table>
		</div>
	
	<?php
		$i++;

	endwhile; // end loop - make content for each server
	
	echo '</div>'; // close #chats-box

	echo '</div>';
	
echo '</div>'; // close #chat-logs 

endif; // end if no records return in total
?>