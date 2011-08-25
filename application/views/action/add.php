					<div class="section width500" >
						<div class="sectionHeader">Add <?php echo ucwords($this->device); ?></div>
						<div class="sectionBody">
							<form action="" method="post">
								<?php 
									$this->deviceaction->add($this->device);
								?>
							</form>
						</div>
					</div>