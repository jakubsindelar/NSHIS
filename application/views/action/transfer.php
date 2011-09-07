					<div class="section width500" >
						<div class="sectionHeader">Assign <?php echo ucwords($this->device); ?></div>
						<div class="sectionBody">
							<form action="" method="post">
								<?php 
									$this->deviceaction->transfer($this->device, $this->device_id);
								?>
							</form>
						</div>
					</div>