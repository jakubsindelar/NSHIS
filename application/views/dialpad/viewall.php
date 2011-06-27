					<div class="section width950" >
						<div class="sectionHeader">View All Dialpadss</div>
						<div class="sectionBody">
							<table width="100%" border="0" cellpadding="0" id="latestStatusTable">
								<tr class="latestStatusTableHeader"><td>#</td><td>NAME</td><td>OTHER NAME</td><td>ASSIGNED CUBICLE</td><td></td></tr>
							<?php 
								if ($data)
								{
									$ctr = 0;
									foreach ($data->result() as $row)
									{
										if($ctr%2==0){
											$color=" bgcolor='#d8ebeb'";
										}else{
											$color=" bgcolor='#FFFFFF'";
										}
										echo "<tr $color><td>".($ctr+1)."</td><td width=150px>";
										echo anchor('dialpad/view/'.$row->dialpad_id,$row->dialpad_name);
										if ($row->cb_name)
										{
											$cubicle_link = anchor('cubicle/view/'.$row->cubicle_id,$row->cb_name);
										}
										else 
										{
											$cubicle_link = "";
										}
										echo "</td><td width=250px>".$row->other_name."</td><td width=250px>".$cubicle_link."</td><td>".anchor('dialpad/edit/'.$row->dialpad_id,'edit')." | ".anchor('dialpad/delete/'.$row->dialpad_id,'delete')."</td></tr>";
										$ctr ++;
									}
								}
								else 
								{
									echo "<tr><td>";
									echo "No dialpads added on the system.";
									echo "</td></tr>";
								}
								
							
							?>
							</table>
						</div>
					</div>