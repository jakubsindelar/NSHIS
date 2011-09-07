					<?php if($data['info']): ?>
					<?php $row = $data['info']->row(); ?>
					<script type="text/javascript">
					$(document).ready(function() {
						$('.cubLink a').button();

					});
					</script>
					<div class="section width600" >
						<div class="sectionHeader">Cubicle <?php echo $row->name;?> Info</div>
						<div class="sectionBody">
							<table width="100%" border="0" cellpadding="5" cellspacing="0" id="cubicle_table">
								<tr>
									<td id="resultName" width="175">Cubicle Name</td><td><?php echo $row->name;?></td><td width="220">&nbsp;</td>
								</tr>
								<tr>
									<td id="resultName">CPU</td><td><?php echo anchor('cpu/view/'.$row->cpu_id, $row->cpu_name.' ', 'title="View"');?></td><td class="cubLink"><?php echo anchor('action/assign/cubicle/'.$row->cubicle_id.'/cpu', 'assign', 'title="Assign new item"'); echo isset($row->cpu_id) ? anchor('action/transfer/cpu/'.$row->cpu_id, 'transfer', 'title="Transfer this item to another cubicle"').anchor('action/swap/cpu/'.$row->cpu_id, 'swap', 'title="Swap this item from other cubicle"').anchor('action/pullout/cpu/'.$row->cpu_id, 'pullout', 'title="Pullout this item on this cubicle"') : '';?></td>
								</tr>
								<tr>
									<td id="resultName">Keyboard</td><td><?php echo anchor('keyboard/view/'.$row->keyboard_id, $row->kyb_name.' ', 'title="View"');?></td><td class="cubLink"><?php echo anchor('action/assign/cubicle/'.$row->cubicle_id.'/keyboard', 'assign', 'title="Assign new item"'); echo isset($row->keyboard_id) ? anchor('action/transfer/keyboard/'.$row->keyboard_id,'transfer', 'title="Transfer this item to another cubicle"').anchor('action/swap/keyboard/'.$row->keyboard_id, 'swap', 'title="Swap this item from other cubicle"').anchor('action/pullout/keyboard/'.$row->keyboard_id, 'pullout', 'title="Pullout this item on this cubicle"') : '';?></td>
								</tr>
								<tr>
									<td id="resultName">Mouse</td><td><?php echo anchor('mouse/view/'.$row->mouse_id, $row->mse_name.' ', 'title="View"');?></td><td class="cubLink"><?php echo anchor('action/assign/cubicle/'.$row->cubicle_id.'/mouse', 'assign', 'title="Assign new item"'); echo isset($row->mouse_id) ? anchor('action/transfer/mouse/'.$row->mouse_id,'transfer', 'title="Transfer this item to another cubicle"').anchor('action/swap/mouse/'.$row->mouse_id, 'swap', 'title="Swap this item from other cubicle"').anchor('action/pullout/mouse/'.$row->mouse_id, 'pullout', 'title="Pullout this item on this cubicle"') : '';?></td>
								</tr>
								<tr>
									<td id="resultName">Monitor</td><td><?php echo anchor('monitor/view/'.$row->monitor_id, $row->mon_name.' ', 'title="View"');?></td><td class="cubLink"><?php echo anchor('action/assign/cubicle/'.$row->cubicle_id.'/monitor', 'assign', 'title="Assign new item"'); echo isset($row->monitor_id) ? anchor('action/transfer/monitor/'.$row->monitor_id,'transfer', 'title="Transfer this item to another cubicle"').anchor('action/swap/monitor/'.$row->monitor_id, 'swap', 'title="Swap this item from other cubicle"').anchor('action/pullout/monitor/'.$row->monitor_id, 'pullout', 'title="Pullout this item on this cubicle"') : '';?></td>
								</tr>
								<tr>
									<td id="resultName">Dial Pad</td><td><?php echo anchor('dialpad/view/'.$row->dialpad_id, $row->dlp_name.' ', 'title="View"');?></td><td class="cubLink"><?php echo anchor('action/assign/cubicle/'.$row->cubicle_id.'/dialpad', 'assign', 'title="Assign new item"'); echo isset($row->dialpad_id) ? anchor('action/transfer/dialpad/'.$row->dialpad_id,'transfer', 'title="Transfer this item to another cubicle"').anchor('action/swap/dialpad/'.$row->dialpad_id, 'swap', 'title="Swap this item from other cubicle"').anchor('action/pullout/dialpad/'.$row->dialpad_id, 'pullout', 'title="Pullout this item on this cubicle"') : '';?></td>
								</tr>
								<tr>
									<td id="resultName">Connector</td><td><?php echo anchor('connector/view/'.$row->connector_id, $row->con_name.' ', 'title="View"');?></td><td class="cubLink"><?php echo anchor('action/assign/cubicle/'.$row->cubicle_id.'/connector', 'assign', 'title="Assign new item"'); echo isset($row->connector_id) ? anchor('action/transfer/connector/'.$row->connector_id,'transfer', 'title="Transfer this item to another cubicle"').anchor('action/swap/connector/'.$row->connector_id, 'swap', 'title="Swap this item from other cubicle"').anchor('action/pullout/connector/'.$row->connector_id, 'pullout', 'title="Pullout this item on this cubicle"') : '';?></td>
								</tr>
								<tr>
									<td id="resultName">Headset(Analog)</td><td><?php echo anchor('headset/view/'.$row->headset_id, $row->hst_name.' ', 'title="View"');?></td><td class="cubLink"><?php echo anchor('action/assign/cubicle/'.$row->cubicle_id.'/headset', 'assign', 'title="Assign new item"'); echo isset($row->headset_id) ? anchor('action/transfer/headset/'.$row->headset_id,'transfer', 'title="Transfer this item to another cubicle"').anchor('action/swap/headset/'.$row->headset_id, 'swap', 'title="Swap this item from other cubicle"').anchor('action/pullout/headset/'.$row->headset_id, 'pullout', 'title="Pullout this item on this cubicle"') : '';?></td>
								</tr>
								<tr>
									<td id="resultName">UPS</td><td><?php echo anchor('ups/view/'.$row->ups_id, $row->ups_name.' ', 'title="View"');?></td><td class="cubLink"><?php echo anchor('action/assign/cubicle/'.$row->cubicle_id.'/ups', 'assign', 'title="Assign new item"'); echo isset($row->ups_id) ? anchor('action/transfer/ups/'.$row->ups_id,'transfer', 'title="Transfer this item to another cubicle"').anchor('action/swap/ups/'.$row->ups_id, 'swap', 'title="Swap this item from other cubicle"').anchor('action/pullout/ups/'.$row->ups_id, 'pullout', 'title="Pullout this item on this cubicle"') : '';?></td>
								</tr>
								<!--<tr>
									<td id="resultName">Date Added</td><td><?php echo $row->cdate;?></td><td>&nbsp;</td>
								</tr>
							 --></table>
						</div>
					</div>
					<div class="section width700" >
						<div class="sectionHeader">Logs</div>
						<div class="sectionBody">
							<?php 
								//get parent class
								$class = $this->router->fetch_class();
								//generate id format
								$id = $this->router->fetch_class().'_id';
								//generate logs.
								$this->devicelog->generate_logs($row->$id, $class);	
							?> 
						</div>
					</div>
					<?php else: ?>
					<div class="section width500" >
						<div class="sectionHeader">Cubicle Info</div>
						<div class="sectionBody">
							Cubicle dont exist.
						</div>
					</div>
					<?php endif; ?>