<?php
define('ADMIN_USER', 'USER_NAME_HERE');
define('ADMIN_PASSWORD','PASS_HERE');
define('ESM_STORE','DB_STORE_HERE');
define('DB_HOST','HOST_HERE');

include 'lib/bones.php';
include 'classes/user.php';
include 'classes/post.php';
include 'classes/ExperienceCaptureHandlers/ImageExperienceCaptureHandler.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST,GET,OPTIONS, DELETE, PATCH, PUT');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');

get('/', function($app){

    if(!User::isAuthenticated())
    {
        $app->render('home');
    }
    else
    {
        $app->render('user/projects');
    }

});

get('/projects', function($app){

    if(User::isAuthenticated())
    {
        $app->render('user/projects');
    }
    else
    {
        $app->set('error', 'No authenticated session exists. Please login.');
        $app->render('home');
    }
});

get('/words', function($app){

    session_start();
    $cou = $_SESSION['couchdb'];
    session_write_close();
    $poolid = $_GET['id'];

    $cou->setDatabase(ESM_STORE);
    $dbWords = $cou->get('/' . $poolid)->body->words;
    if(isset($dbWords)){
        echo json_encode($dbWords);
    } else {
        echo 'empty';
    }
});

get('/test', function($app){

    $app->render('user/test');
});


get('/version', function($app){
    echo 'Xpr Server 1.0';
});

post('/project/create', function($app){


    error_log('/project/create');

    if(User::isAuthenticated())
    {

        session_start();
        $cou = $_SESSION['couchdb'];
        session_write_close();

        $http_method = $_SERVER['REQUEST_METHOD'];

        if ($http_method == "POST")
        {
            $entityBody = file_get_contents('php://input');
            $surveyJSON = json_decode(stripslashes($entityBody),true);

            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    echo 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    echo'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    echo'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    echo'Malformed UTF8 characters, possibly incorrectly encoded';
                    break;
                default:
                    break;
            }

            try
            {
                $cou->setDatabase(ESM_STORE);
                $id = $cou->generateIDs(1)->body->uuids[0];
                $result = $cou->put($id,json_encode($surveyJSON));

                header('Content-Type: application/json');
                echo json_encode($result);
                exit;

            }
            catch(SagCouchException $e)
            {
                $error = array('resp'=> 'error','code' => $e->getCode(), 'desc' => $e->getMessage());
                error_log('error:/project/create' . $e->getCode() . $e->getMessage());
                exit;
            }
        }
    }
    else
    {

        $error = array('resp'=> 'error','code' => '401', 'desc' => 'unauthorized');

        exit;
    }
});

get('/wordsList', function($app){
    session_start();
    $cou = $_SESSION['couchdb'];
    session_write_close();

    $cou->setDatabase(ESM_STORE);
    $result = $cou->get($_GET['wordsListID'])->body;
    if(isset($result)){
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        echo 'empty';
    }
});

post('wordList/create',function($app){

    if(User::isAuthenticated())
    {
        session_start();
        $cou = $_SESSION['couchdb'];
        session_write_close();

        $http_method = $_SERVER['REQUEST_METHOD'];
        if ($http_method == "POST")
        {
            $entityBody = file_get_contents('php://input');
            $wordListJSON = json_decode(stripslashes($entityBody),true);
            header('Content-Type: application/json');

            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    echo 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    echo'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    echo'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    echo'Malformed UTF8 characters, possibly incorrectly encoded';
                    break;
                default:
                    break;
            }
        }
        try
        {
            $cou->setDatabase(ESM_STORE);
            $result = $cou->put($wordListJSON["id"], json_encode($wordListJSON));
            header('Content-Type: application/json');

            exit;
        }
        catch(SagCouchException $e)
        {
            $error = array('resp'=> 'error','code' => $e->getCode(), 'desc' => $e->getMessage());
            error_log('error:/project/create' . $e->getCode() . $e->getMessage());
            exit;
        }

    }
    else
    {
        $error = array('resp'=> 'error','code' => '401', 'desc' => 'unauthorized');
        error_log('error:/project/create' . 'unauthorized');
        exit;
    }

});




post('/project/delete', function($app){

    header('Content-Type: application/json');

    if(User::isAuthenticated())
    {
        session_start();
        $cou = $_SESSION['couchdb'];
        session_write_close();

        $http_method = $_SERVER['REQUEST_METHOD'];

        if ($http_method == "POST")
        {
            $entityBody = file_get_contents('php://input');
            $surveyJSON = json_decode(stripslashes($entityBody),true);

            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    echo 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    echo'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    echo'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    echo'Malformed UTF8 characters, possibly incorrectly encoded';
                    break;
                default:
                    break;
            }

            try
            {
                $cou->setDatabase(ESM_STORE);
                $id = $surveyJSON['_id'];
                $rev = $surveyJSON['_rev'];

                $result = $cou->delete($id,$rev);
                echo json_encode($result);
                exit;
            }
            catch(SagCouchException $e)
            {
                $error = array('resp'=> 'error','code' => $e->getCode(), 'desc' => $e->getMessage());
                echo json_encode($error);
                exit;
            }
        }
    }
    else
    {
        $error = array('resp'=> 'error','code' => '401', 'desc' => 'unauthorized');
        echo json_encode($error);
        exit;
    }
});


post('/project/update', function($app){

    header('Content-Type: application/json');


    if(User::isAuthenticated())
    {
        session_start();
        $cou = $_SESSION['couchdb'];
        session_write_close();

        $http_method = $_SERVER['REQUEST_METHOD'];

        if ($http_method == "POST")
        {
            $entityBody = file_get_contents('php://input');
            $surveyJSON = json_decode(stripslashes($entityBody),true);

            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    echo 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    echo'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    echo'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    echo'Malformed UTF8 characters, possibly incorrectly encoded';
                    break;
                default:
                    break;
            }

            try
            {
                $cou->setDatabase(ESM_STORE);
                $id = $surveyJSON['_id'];

                $result = $cou->put($id,json_encode($surveyJSON));
                echo json_encode($result);
                exit;
            }
            catch(SagCouchException $e)
            {
                $canSave = false;
                error_log('error:/project/update' . $e->getCode() . $e->getMessage());
            }
        }
    }
    else
    {
        $error = array('resp'=> 'error','code' => '401', 'desc' => 'unauthorized');
        echo json_encode($error);
        exit;
    }
});


get('/user/surveys', function($app){

    $canSave = true;
    if(!User::isAuthenticated())
    {
        $emailName = preg_replace('/[^a-z0-9-]/','', strtolower($_COOKIE['xpr_username']));
        $userAccount = User::get_by_username($emailName);

        if($userAccount)
        {
            $userAccount->login($_COOKIE['xpr_password']);
        }
        else
        {
            $canSave = false;
            echo 'error';
        }
    }

    if($canSave)
    {
        session_start();
        $cou = $_SESSION['couchdb'];
        $username =  $_SESSION['username'];
        session_write_close();

        if(!$userAccount)
        {
            $userAccount = User::get_by_username($username);
        }

        $cou->setDatabase(ESM_STORE);

        $documentResults = $cou->get('/_design/xpr/_view/usersurveys?reduce=false&startkey=["' . $userAccount->email . '"]&endkey=["' . $userAccount->email . '"]&include_docs=true')->body->rows;
        $parsedDocuments = array();

        if(count($documentResults) > 0)
        {
            foreach($documentResults as $project)
            {
                $configuration = $project->value;
                unset($configuration->participants);
                unset($configuration->_rev);
                unset($configuration->coordinator);
                unset($configuration->_id);
                unset($configuration->stimulus_max);
                unset($configuration->stimulus_min);

                foreach($configuration->stimulus_alloc as $users){

                    if($users->identifier == $userAccount->email){
                        $configuration->stimulus_alloc = $users->trials;
                        break;
                    }
                }

                foreach($configuration->stimulus_alloc as $trialAllocation){
                    foreach($configuration->surveys as $survey) {

                        if($trialAllocation->trial_identifier == $survey->identifier)
                        {
                            $stimulus_count = count($trialAllocation->stimulus);

                            $subsetQuestions = array_slice($survey->questions, 0, $stimulus_count);

                            $recallQuestion = end($survey->questions);
                            $recallQuestion->ordinal = $stimulus_count;
                            array_push($subsetQuestions,$recallQuestion);

                            $survey->questions = $subsetQuestions;
                        }

                    }
                }

                foreach($configuration->surveys as $survey) {

                    foreach ($survey->questions as $question) {

                        $question->ordinal = intval($question->ordinal);

                    }
                }

                array_push($parsedDocuments,$configuration);
            }


            if($users->identifier == '182005') {
                error_log('doc');
                error_log(json_encode($parsedDocuments));
            }

            echo json_encode($parsedDocuments);
        }
        else
        {
            echo 'empty';
        }


    }

});

get('/data/project/schemas', function($app){

    if(User::isAuthenticated())
    {
        session_start();
        $cou = $_SESSION['couchdb'];
        session_write_close();
        $cou->setDatabase(ESM_STORE);

        parse_str($_SERVER['QUERY_STRING'], $querystr);

        $ch = curl_init();
        $url = 'http://' . DB_HOST . ':5984/' . ESM_STORE . '/_design/xpr/_list/surveyschemas/allsurveys?include_docs=true&startkey="'. $querystr['surveyid'] .'"&endkey="'. $querystr['surveyid'] .'"';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, ADMIN_USER . ':' . ADMIN_PASSWORD);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="survey-schema-'. $querystr['surveyid'] .'.csv"');

        echo $output;
    }
    else
    {
        $app->set('error', 'No authenticated session exists. Please login.');
        $app->render('home');
    }
});


get('/data/project/survey/completed', function($app){

    if(User::isAuthenticated())
    {
        session_start();
        $cou = $_SESSION['couchdb'];
        session_write_close();
        $cou->setDatabase(ESM_STORE);

        parse_str($_SERVER['QUERY_STRING'], $querystr);

        $ch = curl_init();
        $url = 'http://' . DB_HOST . ':5984/' . ESM_STORE . '/_design/xpr/_list/surveyresults/completedsurveys?include_docs=true&startkey="'. $querystr['surveyid'] .'"&endkey="'. $querystr['surveyid'] .'"';

        if($querystr['survey_type'] == 'stimulus')
        {
            $url = 'http://'. DB_HOST. ':5984/' . ESM_STORE . '/_design/xpr/_list/completedstimulusresponses/completedstimulussurveys?include_docs=true&startkey="'. $querystr['surveyid'] .'"&endkey="'. $querystr['surveyid'] .'"';
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, ADMIN_USER . ':' . ADMIN_PASSWORD);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="survey-responses-' . $querystr['surveyid'] . '.csv"');

        echo $output;
    }
    else
    {
        $app->set('error', 'No authenticated session exists. Please login.');
        $app->render('home');
    }

});


get('/data/project/survey/stimulusallocations', function($app){

    if(User::isAuthenticated())
    {
        session_start();
        $cou = $_SESSION['couchdb'];
        session_write_close();
        $cou->setDatabase(ESM_STORE);

        parse_str($_SERVER['QUERY_STRING'], $querystr);

        $ch = curl_init();

        $url = '';

        if(array_key_exists('surveyid', $querystr)) {

            $url = 'http://'. DB_HOST. ':5984/' . ESM_STORE . '/_design/xpr/_list/stimallocs/stimulussurveys?startkey=["' . $querystr['surveyid'] . '"]&endkey=["' . $querystr['surveyid'] . '",{},{}]';
        }

        if(array_key_exists('studyid', $querystr)) {
            $url = 'http://'. DB_HOST. ':5984/' . ESM_STORE . '/_design/xpr/_list/stimallocsbystudy/stimulussurveysbystudy?startkey=["' . $querystr['studyid'] . '"]&endkey=["' . $querystr['studyid'] . '",{},{}]';
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, ADMIN_USER . ':' . ADMIN_PASSWORD);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="survey-stim-allocations-'. $querystr['surveyid'] . '.csv"');

        echo $output;

    }
    else
    {
        $app->set('error', 'No authenticated session exists. Please login.');
        $app->render('home');
    }

});

get('/coordinator/surveys', function($app){


    header('Content-Type: application/json');
    $canSave = true;
    if(!User::isAuthenticated())
    {


        $emailName = preg_replace('/[^a-z0-9-]/','', strtolower($_COOKIE['xpr_username']));
        $userAccount = User::get_by_username($emailName);

        if($userAccount)
        {
            $userAccount->login($_COOKIE['xpr_password']);
        }
        else
        {
            $canSave = false;
            echo 'error';
        }
    }

    if($canSave)
    {
        session_start();
        $cou = $_SESSION['couchdb'];
        $username =  $_SESSION['username'];
        session_write_close();

        if(!$userAccount)
        {
            $userAccount = User::get_by_username($username);
        }

        $cou->setDatabase(ESM_STORE);
        $documentResults = $cou->get('/_design/xpr/_view/coordinatorsurveys?reduce=false&startkey="' . $userAccount->email . '"&endkey="' . $userAccount->email . '"&include_docs=true')->body->rows;

        $parsedDocuments = array();
        if(count($documentResults) > 0)
        {
            foreach($documentResults as $project)
            {
                $configuration = $project->value;
                array_push($parsedDocuments,$configuration);
            }
            echo json_encode($parsedDocuments);
        }
        else
        {
            echo 'empty';
        }
    }

});



post('/project-new', function($app){

    $app->render('user/projects');
});


post('/survey/save',function($app){


    $canSave = true;
    if(!User::isAuthenticated())
    {

        $emailName = preg_replace('/[^a-z0-9-]/','', strtolower($_COOKIE['xpr_username']));
        $userAccount = User::get_by_username($emailName);

        error_log('/survey/save by:' . $emailName);

        if($userAccount)
        {
            $userAccount->login($_COOKIE['xpr_password']);

        }
        else
        {
            $canSave = false;
            echo 'error';
        }
    }

    if($canSave)
    {
        session_start();
        $cou = $_SESSION['couchdb'];
        session_write_close();

        $http_method = $_SERVER['REQUEST_METHOD'];

        if ($http_method == "POST")
        {

                $surveyJSON = json_decode(stripslashes($_POST["surveys"]),true);


                switch (json_last_error()) {
                    case JSON_ERROR_DEPTH:
                        error_log('Maximum stack depth exceeded');
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        echo'Underflow or the modes mismatch';
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        echo'Unexpected control character found';
                        break;
                    case JSON_ERROR_SYNTAX:
                        echo'Syntax error, malformed JSON';
                        break;
                    case JSON_ERROR_UTF8:
                        echo'Malformed UTF8 characters, possibly incorrectly encoded';
                        break;
                    default:
                        break;
                }

                try
                {
                    $cou->setDatabase(ESM_STORE);
                    $id = $cou->generateIDs(1)->body->uuids[0];
                    $cou->put($id,json_encode($surveyJSON));
                    $surveyJSON = $cou->get($id)->body;

                    $i = 0;

                    foreach($surveyJSON->completed_questions as $question)
                    {
                        if($question->type == "ImageExperienceCapture")
                        {
                            $imageCaptureHandler = new ImageExperienceCaptureHandler($surveyJSON->completed_questions[$i], $id, $surveyJSON);
                            $surveyJSON = $imageCaptureHandler->process();
                        }

                        $i++;
                    }

                }
                catch(SagCouchException $e)
                {
                    error_log('error:/survey/save' . $e->getCode() . $e->getMessage());
                }

                echo $surveyJSON->identifier;
        }
    }
});

get('/pins',function($app)
{
    if(isset($_GET["participantAmount"])){
        $participantAmount = (int)$_GET["participantAmount"];
    } else {
        $participantAmount = 10;
    }
    $randomPins = array();
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);

    for ($i = 0; $i <$participantAmount; $i++) {
        do{
            $randomString = '';
            for ($j = 0; $j < 6; $j++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            $isUnique = checkUniquePin($randomString);
        } while(!$isUnique);
        array_push($randomPins, $randomString);
    }
    header('Content-Type: application/json');
    echo json_encode($randomPins);
});

function checkUniquePin($pin){
    $userAccount = User::get_by_username($pin);
    return !isset($userAccount);
}

get('/register',function($app)
{
    $user = new User();
    $user->email = $_COOKIE['xpr_username'];
    $result = $user->signup($_COOKIE['xpr_username'],$_COOKIE['xpr_password']);

    if($result->code == "100")
    {
        $user->login($_COOKIE['xpr_password']);
    }

    echo $result->to_json();
});

post('/register',function($app)
{
    $user = new User();
    $user->email = $app->form('create_user_name_box');
    $result = $user->signup($app->form('create_user_name_box'),$app->form('create_user_secret_box'));

    if($result->code == "100")
    {
        $validate = $user->login($app->form('create_user_secret_box'));
        if($validate)
        {

            setcookie("xpr_username", $app->form('create_user_name_box'), time()+3600);
            setcookie("xpr_password", $app->form('create_user_secret_box'), time()+3600);
            $app->redirect('/projects');
        }
        else
        {
            $app->set('error', 'the username or password entered was incorrect. Please try again.');
            $app->render('home');
        }
    }
    else
    {
        $app->set('error', $result->message);
        $app->render('home');
    }
});


get('/login',function($app)
{
    //error_log('get login');
        $emailName = preg_replace('/[^a-z0-9-]/','', strtolower($_COOKIE['xpr_username']));
        $userAccount = User::get_by_username($emailName);

        if($userAccount)
        {
            $validate = $userAccount->login($_COOKIE['xpr_password']);
            if($validate)
            {
                echo 'success';
            }
            else
            {
                echo 'error';
            }
        }
        else
        {
            echo 'error';
        }

});

post('/login',function($app)
{
    $emailName = preg_replace('/[^a-z0-9-]/','', strtolower($app->form('login_user_name_box')));
    $userAccount = User::get_by_username($emailName);


    if($userAccount)
    {
        $validate = $userAccount->login($app->form('login_user_password_box'));

        if($validate)
        {

            setcookie("xpr_username", $app->form('login_user_name_box'), time()+ (10 * 365 * 24 * 60 * 60));
            setcookie("xpr_password", $app->form('login_user_password_box'), time()+ (10 * 365 * 24 * 60 * 60));

            $_COOKIE['xpr_username'] = $app->form('login_user_name_box');
            $a =  $_COOKIE['xpr_username'];
            $app->redirect('/projects');
        }
        else
        {
            $app->set('error', 'the username or password entered was incorrect. Please try again.');
            $app->render('home');
        }
    }
    else
    {
        $app->set('error', 'Account does not exist. Please register with us.');
        $app->render('home');
    }

});



get('/say/:message',function($app)
{
	$app->set('message', $app->request('message'));
	$app->render('home');
});

get('/login',function($app)
{
	$app->render('user/login');
});

get('/logout',function($app)
{	
	User::logout();

    setcookie("xpr_username", "", time()-3600);
    setcookie("xpr_password", "", time()-3600);
    setcookie("PHPSESSID", "", time()-3600);


	$app->redirect('/');
});

get('/user/:username',function($app)
{
	$app->set('user', User::get_by_username($app->request('username')));

	$app->set('is_current_user', ($app->request('username') == User::current_user() ? true : false));
	$app->set('posts', Post::get_posts_by_user($app->request('username')));
	
	$app->set('post_count', Post::get_post_count_by_user($app->request('username')));
	
	$app->render('user/profile');
});


post('/post',function($app)
{
	if(User::isAuthenitcated())
	{
		$post = new Post();
		$post->content = $app->form('content');
		$post->create();
		$app->redirect('/user/' . User::current_user());
	}
	else
	{
		$app->set('error', 'You need to be logged in');
		$app->render('user/login');
	}	
});


get('/post/delete/:id/:rev',function($app)
{
	$post = new Post();
	$post->_id = $app->request('id');
	$post->_rev = $app->request('rev');
	
	$post->delete();
	
	$app->set('success', 'Post Deleted');
	$app->redirect('/user/' . User::current_user());
});

resolve();
