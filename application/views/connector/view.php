					<?php //$row = $data['info']->row(); ?>
					<div class="section width500" >
						<div class="sectionHeader">Info</div>
						<div class="sectionBody">
							<?php 
								//get parent class
								$class = $this->router->fetch_class();
								//generate id format
								$id = $this->router->fetch_class().'_id';
								$this->deviceaction->view($this->router->fetch_class(), $this->uri->segment(3));
							?>
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
								$this->devicelog->generate_logs($this->uri->segment(3), $class);	
							?>
						</div>
					</div>