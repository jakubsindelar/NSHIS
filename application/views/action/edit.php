					<div class="section width500" >
						<div class="sectionHeader">Edit <?php echo ucwords($this->device); ?></div>
						<div class="sectionBody">
							<form action="" method="post">
								<?php 
									$this->deviceaction->edit($this->device, $this->device_id);
								?>
							</form>
						</div>
					</div>