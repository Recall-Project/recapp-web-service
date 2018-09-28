<div class="page-header">
	<h1>login</h1>
</div>
<div class="row">
	<div class="span12">
		<form class="form-vertical" action ="<?php echo Bones::get_instance()->make_route('/login') ?>" method = "post">
			<fieldset>
				<?php Bootstrap::make_input('username', 'Username', 'text');?>
				<?php Bootstrap::make_input('password', 'Password', 'text');?>
				<div class="form-actions">
					<button class="btn btn primary">login</button>
				</div>
			</fieldset>
		</form>
	</div>
</div>