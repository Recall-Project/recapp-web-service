<?php

include 'ExperienceCaptureHandler.php';

class ImageExperienceCaptureHandler extends ExperienceCaptureHandler
{
    public $image;
    public $label;
    private $_id;
    private $image_id;
    private $filled_experience;
    private $_rev;

    public function __construct($experienceCaptured, $id, $survey)
    {
        parent::__construct();

            $filled_experience = $experienceCaptured;
            $this->_id = $id;

                $path_parts = pathinfo($experienceCaptured->image_url);

                if(isset($path_parts['extension']) && $path_parts['dirname'] == '.')
                {
                    if($path_parts['extension'] == 'jpg')
                    {
                        $bones = Bones::get_instance();
                        $experienceCaptured->image_url = $bones->getDatabaseBaseURL() . $this->_id . '/' . $filled_experience->image_url;
                        $this->image_id = $path_parts['filename'];
                    }
                }

        session_start();
        $cou = $_SESSION['couchdb'];
        session_write_close();
        $cou->setDatabase(ESM_STORE);
        $updated_survey = $cou->put($id,json_encode($survey));
        $this->_rev = $updated_survey->body->rev;
    }

    public function process()
    {
        return $this->storeImage();
    }

    protected function storeImage()
    {
        session_start();
        $cou = $_SESSION['couchdb'];
        session_write_close();
        $cou->setDatabase(ESM_STORE);

        $file = $_FILES[$this->image_id];

        if(isset($file))
        {
            $result = $cou->setAttachment($file['name'],
            file_get_contents($file['tmp_name']),
            $file['type'],
            $this->_id, $this->_rev);

            $NEW_SURVEY = $cou->get($this->_id)->body;

            return $NEW_SURVEY;
        }
        else
        {
            return;
        }


    }
}