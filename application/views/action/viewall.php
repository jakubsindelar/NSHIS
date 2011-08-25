					<div class="section width500" >
						<div class="sectionHeader">View All <?php echo strtoupper($data['device']);?></div>
						<div class="sectionBody">
							<?php 
								$this->deviceaction->view_all(array('device' => $data['device']));
							?>
						</div>
					</div>