<?php
include 'base.php';
include 'httpresponse.php';


class User extends Base
{
	protected $name;
	public $email;
	protected $full_name;
	protected $salt;
	protected $password_sha;
	protected $roles;
	
	public function __construct()
	{
		parent::__construct('user');
	}
	
	public function signup($username, $password)
	{
        $resp = new XprHTTPResponse();
		$bones = Bones::get_instance();
        $bones->couch->login(ADMIN_USER, ADMIN_PASSWORD);
		$bones->couch->setDatabase('_users');

		$this->roles = array("participant","researcher");
		$this->name = preg_replace('/[^a-z0-9-]/','', strtolower($username));
		$this->_id = 'org.couchdb.user:' . $this->name;
		$this->salt = $bones->couch->generateIDs(1)->body->uuids[0];
		$this->password_sha = sha1($password . $this->salt);

		try
		{
			$bones->couch->put($this->_id, $this->to_json());
		}
		catch(SagCouchException $e)
		{
			if($e->getCode() == "409")
			{
                $resp->code = (string) $e->getCode();
                $resp->message = "This Pin is already in use. Please try another pin.";
				return $resp;
			}
			else
			{
                $resp->code = (string) $e->getCode();
                $resp->message = "Internal Server Error";
                return $resp;
			}
		}

        $resp->code = "100";
        $resp->message = "complete";
        return $resp;
	}
	
	public function login($password) 
	{
		$bones = Bones::get_instance();
		$bones->couch->setDatabase('_users');

		try
		{
            $bones->couch->login(null, null);
			$bones->couch->login($this->name, $password, Sag::$AUTH_COOKIE);

            session_start();
			$_SESSION['username'] = $bones->couch->getSession()->body->userCtx->name;
			$_SESSION['couchdb'] = $bones->couch;

			session_write_close();
		}
		catch(SagCouchException $e)
		{
            $errorMsg = '';
			if($e->getCode() == "401")
			{
                $errorMsg = 'Login failed. Error 401';
			}
			else
			{
                $errorMsg = 'Login failed. Error 500';
			}

            $bones->set('error', $errorMsg);
            return false;
		}

        return true;
	}
	
	public static function logout() 
	{
        session_start();
        $couch_session = $_SESSION['couchdb'];
        session_write_close();

        if($couch_session)
        {
            $couch_session->login(null, null);
		    session_start();
		    session_destroy();
        }
	}
	
	public static function current_user()
	{
		session_start();
		return $_SESSION['username'];
		session_write_close();
	}
	
	public static function isAuthenticated()
	{
        session_start();
        $couch_session = $_SESSION['couchdb'];
        session_write_close();

		if($couch_session)
		{
            $username = $couch_session->getSession()->body->userCtx->name; //is there a valid session
            if($username)
            {

                $xpr_username = preg_replace('/[^a-z0-9-]/','', strtolower($_COOKIE['xpr_username']));
                if($username == $xpr_username)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
		}
		else
		{

			return false;
		}


	}
	
	public static function get_by_username($username = null)
	{
		$bones = Bones::get_instance();
		$bones->couch->setDatabase('_users');
		$bones->couch->login(ADMIN_USER, ADMIN_PASSWORD);
		$user = new User();

		try
		{
            $treatedUsername = preg_replace('/[^a-z0-9-]/','', strtolower($username));
			$document = $bones->couch->get('org.couchdb.user:' . $treatedUsername)->body;
			$user->_id = $document->_id;
			$user->name = $document->name;
			$user->email = $document->email;
			$user->full_name = $document->full_name;
			return $user;
		}
		catch(SagCouchException $e)
		{
			if($e->getCode() == "404")
			{
				return;
			}
			else
			{
                return;
			}
		}

        return;
	}
	
}