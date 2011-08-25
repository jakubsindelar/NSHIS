					<div class="section width500" >
						<div class="sectionHeader">Info</div>
						<div class="sectionBody">
							<?php 
								$this->deviceaction->view($this->device, $this->device_id);
							?>
						</div>
					</div>
					<div class="section width700" >
						<div class="sectionHeader">Logs</div>
						<div class="sectionBody">
							<?php 
								$this->devicelog->generate_logs($this->device_id, $this->device);	
							?>
						</div>
					</div>