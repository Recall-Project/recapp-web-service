<?php


class Post extends Base
{
	protected $date_created;
	protected $content;
	protected $user;
	
	public function __construct()
	{
		parent::__construct('post');
	}
	
	public function create()
	{
		$bones = Bones::get_instance();
		$this->_id = $bones->couch->generateIDs(1)->body->uuids[0];
		$this->date_created = date('r');
		$this->user = User::current_user();
		
		session_start();
		$cou = $_SESSION['couch'];
		session_write_close();
				
		try
		{
			$cou->setDatabase('verge');
			$cou->put($this->_id, $this->to_json());
		}
		catch(SagCouchException $e)
		{
			$bones->error500($e);
		}
	}
	
	public function get_posts_by_user($username)
	{
		$bones = Bones::get_instance();
		$posts = array();
		$bones->couch->setDatabase('verge');
		
		foreach ($bones->couch->get('_design/application/_view/posts_by_user?key="' . $username .'"&descending=true&reduce=false')->body->rows as $_post)
		{
			$post = new Post();
			$post->_id = $_post->id;
			$post->_rev = $_post->value->_rev;
			$post->date_created = $_post->value->date_created;
			$post->content = $_post->value->content;
			$post->user = $_post->value->user;
			array_push($posts, $post);
		}
			
		return $posts;
	}
	
	public function get_post_count_by_user($username)
	{
		$bones = Bones::get_instance();
		$rows = $bones->couch->get('_design/application/_view/posts_by_user?key="' . $username .'"&reduce=true')->body->rows;
		
		if($rows)
		{
			return $rows[0]->value;
		}
		else
		{
			return 0;
		}
	}
	
	public function delete()
	{
		$bones = Bones::get_instance();
		try
		{
		
			
			
			session_start();
			$cou = $_SESSION['couch'];
			session_write_close();
			$cou->setDatabase('verge');
			
			//echo $cou->getSession()->body->userCtx->name;
			//exit;
			
			$cou->delete($this->_id, $this->_rev);
		}
		catch(SagCouchException $e)
		{
			echo 'ERROR';
			exit;
			$bones->error500($e);
		}
	}
	
	
	
	
	
	
}