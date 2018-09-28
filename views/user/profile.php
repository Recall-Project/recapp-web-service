<div class="page-header">
	<h1>
		<?php echo $user->full_name; ?>
		<?php if($is_current_user) { ?>
		<code> This is you! <?php echo $_SESSION['username'];?></code>
		<?php } ?>
	</h1> 
</div>
<div class="container">
	<div class="row">
		<div class="span4">
			<div class="well sidebar-nav">
				<ul class="nav nav-list">
					<li><h3>User Information</h3></li>
					<li><b>Username:</b><?php echo $user->name; ?></li>
					<li><b>Email:</b><?php echo $user->email; ?></li>
				</ul>
			</div>
		</div>
		<div class="span8">
			
				<h2>Posts (<?php echo $post_count;?>)</h2>
				
				<?php foreach($posts as $post): ?>
				<div class="post-item row">
					<div class="span7">
						<strong><?php echo $user->name; ?></strong>
						<p>
							<?php echo $post->content; ?>
						</p>
							<?php echo $post->date_created; ?>
					</div>
					<div class="span1">
					<?php if($is_current_user) { ?>
						<a href="<?php echo Bones::get_instance()->make_route('/post/delete/' . $post->_id . '/' . $post->_rev) ?>" class="delete">(delete)</a>
						<?php } ?>
					</div>
					<div class="span8"></div>
				</div>
			<?php endforeach;?>
				<?php if($is_current_user) { ?>
					<h2>Create Post</h2>
					<form action ="<?php echo $this->make_route('/post') ?>" method = "post">
						<textarea id="content" name="content" class="span8" rows="3"></textarea>
						<button id="create_post" class="btn btn-primary">Submit</button>
					</form>
			<?php } ?>
		
		</div>
	</div>
</div>