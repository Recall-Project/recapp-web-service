<script type="text/javascript">

var map;
var triggerCircle;
var createProjectModalReset;

    $(document).ready(function() {

        createProjectModalReset = $('#project-modal-view').html();
        Handlebars.registerHelper("formatOrdinal", function(ordinal) {
            ordinalInt = parseInt(ordinal);
            ordinalInt++;

            return ordinalInt.toString();
        });

        Handlebars.registerHelper("formatInterval", function(interval) {
            if(interval == 0){
                return 'Off';
            } else if(interval == 1){
                return 'Daily';
            } else if(interval == 2){
                return 'Weekly';
            } else if(interval == 3){
                return 'Yearly';
            }

        });

        Handlebars.registerHelper("questionSurveyID", function(questionID) {
            return ProjectsModelController.getQuestionSurveyID(questionID);
        });

        Handlebars.registerHelper("localTimeFormat", function(time) {

            var startTimeDate = new Date(Date.UTC(time.substring(0,4), time.substring(5,7), time.substring(8,10), time.substring(11,13), time.substring(14,16), time.substring(17,19), time.substring(20,22)));
            return startTimeDate.toLocaleString();
        });

        Handlebars.registerHelper('if_eq', function(a, b, opts) {
            if(a == b)
                return opts.fn(this);
            else
                return opts.inverse(this);
        });

        Handlebars.registerHelper("questionType", function(type) {
            if(type == 'SurveyLikertQuestion')
            {
                return 'Likert';
            }
            else if(type == 'ImageExperienceCapture')
            {
                return 'Image'
            }
            else if(type == 'StimulusQuestion'){
                return 'Stimulus'
            }
            else if(type == 'RecallQuestion'){
                return 'Recall'
            }
            else
            {
                return 'Option'
            }
        });

        Handlebars.registerHelper("isStim", function(type) {
            if(type == 'StimulusQuestion'){
                return true;
            }
            else
            {
                return false;
            }
        });

        $("#spatialContainer").hide();
        $("#temporalContainer").hide();
        $("#selected-location-trigger").hide();
        ProjectsModelController.downloadProjects();


        var mapProp = {
            center:new google.maps.LatLng(51.508742,-0.120850),
            zoom:5,
            mapTypeId:google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById("googleMap")
                ,mapProp);
    });

    function create_survey_click(project_identifier)
    {
        var surveyNameInput = document.getElementById(project_identifier + 'survey-title');

        if(surveyNameInput.value)
        {
            ProjectsModelController.createSurvey(project_identifier,surveyNameInput.value, "SurveyForm");
            surveyNameInput.value = '';

        }
        else
        {
            alert('You did not specify a name for the survey.');
            surveyNameInput.value = '';
        }
    }

    function optionType(option)
    {
        var LikertDisplay = null;
        var MultiDisplay = null;
        var imageDisplay = null;
        var optionTitle = null;

        if(option == 2) {

            imageDisplay = 'block';
            MultiDisplay = 'none';
            LikertDisplay = 'none';
            optionTitle = 'Image Capture';
            $('#add-question-modal-textbox').show();
        }
        else if(option == 1) {
            MultiDisplay = 'block';
            LikertDisplay = 'none';
            imageDisplay = 'none';
            optionTitle = 'Multi-Option';
            $('#add-question-modal-textbox').show();
        }
        else if(option == 0)
        {
            MultiDisplay = 'none';
            LikertDisplay = 'block';
            imageDisplay = 'none';
            optionTitle = 'Likert (1-7)';
            $('#add-question-modal-textbox').show();
        }
        else if(option == 3)
        {
            MultiDisplay = 'none';
            LikertDisplay = 'block';
            imageDisplay = 'none';
            optionTitle = 'Stimulus (Likert Orientation)';
            $('#add-question-modal-textbox').show();
        }
        else if(option == 4)
        {
            MultiDisplay = 'none';
            LikertDisplay = 'none';
            imageDisplay = 'none';
            optionTitle = 'Stimulus Recall';
            $('#add-question-modal-textbox').show();
        }
        else
        {
            MultiDisplay = 'none';
            LikertDisplay = 'none';
            imageDisplay = 'none';
            optionTitle = 'Select Question Type';
            $('#add-question-modal-textbox').hide();
        }
        document.getElementById("multioptionsContainer").style.display = MultiDisplay;
        document.getElementById("likertContainer").style.display = LikertDisplay;
        document.getElementById("imageCaptureContainer").style.display = imageDisplay;
        document.getElementById("question-type-title").innerText = optionTitle;

        return false;
    }


    function selectProjectType(option){
        var optionTitle = 'Questionnaire';
        if(option == 1){
            $('#stimulusContainer').show();
            optionTitle = 'Stimulus';
        } else {
            $('#stimulusContainer').hide();
        }
        document.getElementById("project-type-title").innerText = optionTitle;
    }

    function addMultiOption()
    {
        var optionText = document.getElementById('optionTitleInput').value;
        $("#question-multiOptionList").append('<li ><div style="margin-bottom:4px; overflow:hidden;" class="alert alert-info"><div style="float:left; margin-right:20;"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></div><div style="float:left"><span class="optionItem">' + optionText +'</span></div><div style="float:right"><a id="r" class="close">x</a></div></div></li>');

        document.getElementById('optionTitleInput').value = '';
    }

</script>

<div class="hero-unit well" style="min-height:87%; margin-left: 10%; margin-right: 10%;   background-color:rgba(255,255,255,0.6) ; min-width:800px;">
    <div style="background-color:#d0d022; margin-right:-19; margin-left: -19; margin-bottom: 15;">
    <h2 style=" margin-left:5;  color:#f5f5f5">Projects</h2>
        <div style="background-color:lightgray; height:10px;"></div>
    </div>
    <div class="container-fluid" style="overflow:auto">

        <div class="accordion" id="projectsContainer"></div>

        <div style="border:solid 1px #f5f5f5;border-radius: 4px; margin-right: 0; margin-left: 0; padding:20; padding-bottom:0;" class="listEntryForm">
            <form id="create_project_form" action="/">
                <div class="form-group">
                    <input id="project_name_box"  type="input"  placeholder="Add New Project Title....">
                    <button class="btn open-add-project-modal"><i class="icon-plus-sign"></i>Create Study</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="project-modal-view" style="" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div style="margin:0; padding:0" class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <div style="background-color:#d0d022; padding:5;"><h3 style="color:#f5f5f5; " id="myModalLabel">Project</h3></div>
        <div style="background-color:lightgray; height:10px;"></div>
    </div>
    <form id="create_project_modal_form" action="/">
        <div class="modal-body" style="">
            <div style=" width:100%; margin-bottom: 10px; text-align: center; font-family: helveticaneue-light" class="btn-group">
                <a id="project-type-selector" style="width:77%;" class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                    <span id="project-type-title">Select Project Type</span>
                    <span class="caret"></span>
                </a>
                <ul style="width:100%;" class="dropdown-menu">
                    <li><a href="javascript:selectProjectType(0)">Questionnaire</a></li>
                    <li><a href="javascript:selectProjectType(1)">Stimulus</a></li>
                </ul>
            </div>
            <div style="width:80%; margin-left: 10%; margin-right: 10%; margin-bottom:10px;" class="hide" id="stimulusContainer">
                <label for="surveyAmount">Enter how many surveys are required:</label>
                <input style="width:12%; height:30px; font-size: smaller" type="input" id="surveyAmount" name="add-project-survey-amount"  placeholder="12">
                <label for="wordListRangeMin">Enter the minimum amount of words a user will be asked to remember:</label>
                <input style="width:12%; height:30px;font-size: smaller ;" type="input" id="wordListRangeMin" name="add-project-word-list-min"  placeholder="1">
                <label for="wordListRangeMax">Enter the maximum amount of words a user will be asked to remember:</label>
                <input style="width:12%; height:30px;font-size: smaller ;" type="input" id="wordListRangeMax" name="add-project-word-list-max"  placeholder="16">
                <label for="defaultQuestion">Enter a default orientation likert question to be displayed with your stimulus:</label>
                <input style="width:100%; height:30px;font-size: smaller ;" type="input" id="defaultQuestion" name="add-project-orientation-question"  placeholder="How much do you like this word?">

                <label for="defaultQuestion">Do you want the participant to perform an explicit recall after each experiment?</label>
                <div style=" width:30%; margin-bottom: 10px; text-align: center; font-family: helveticaneue-light" class="btn-group">
                    <a id=question-completeType-selector" style="" class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                        <span id="question-completeType-title">Select Option</span>
                        <span class="caret"></span>
                        <span id="question-completeType-id-selection" style="visibility:hidden"></span>
                    </a>
                    <ul style="width:100%;" class="dropdown-menu">
                        <li><a onclick="javascript:document.getElementById('question-completeType-title').innerText = 'Yes';document.getElementById('question-completeType-id-selection').innerText = '1';">Yes</a></li>
                        <li><a onclick="javascript:document.getElementById('question-completeType-title').innerText = 'No';document.getElementById('question-completeType-id-selection').innerText = '0';">No</a></li>
                    </ul>
                </div>

                <label for="defaultQuestion">Select a 200 word subset of the Toronto Word Pool:</label>
                <div style=" width:30%; margin-bottom: 10px; text-align: center; font-family: helveticaneue-light" class="btn-group">
                    <a id=stimulus-pool-selector" style="" class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                        <span id="stimulus-pool-title">Select Word Pool</span>
                        <span class="caret"></span>
                        <span id="pool-id-selection" style="visibility:hidden"></span>
                    </a>
                    <ul style="width:100%;" class="dropdown-menu">
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'A Musical Instrument';document.getElementById('pool-id-selection').innerText = 'WPMUSICIAL';">A Musical Instrument</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'Article of Clothing';document.getElementById('pool-id-selection').innerText = 'WPCLOTHING';">Article of Clothing</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'A Sport';document.getElementById('pool-id-selection').innerText = 'WPSPORT';">A Sport</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'A Country';document.getElementById('pool-id-selection').innerText = 'WPCOUNTRY';">A Country</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'A Fruit';document.getElementById('pool-id-selection').innerText = 'WPFRUIT';">A Fruit</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'Part Of The Human Body';document.getElementById('pool-id-selection').innerText = 'WPBODY';">Part Of The Human Body</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'An Occupation';document.getElementById('pool-id-selection').innerText = 'WPPRO';">An Occupation</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'A Chemical Element';document.getElementById('pool-id-selection').innerText = 'WPELEMENT';">A Chemical Element</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'Transportation Vehicle';document.getElementById('pool-id-selection').innerText = 'WPTRANSPORT';">Transportation Vehicle</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'Word Pool A';document.getElementById('pool-id-selection').innerText = 'WPA';">Word Pool A</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'Word Pool B';document.getElementById('pool-id-selection').innerText = 'WPB';">Word Pool B</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'Word Pool C';document.getElementById('pool-id-selection').innerText = 'WPC';">Word Pool C</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'Word Pool D';document.getElementById('pool-id-selection').innerText = 'WPD';">Word Pool D</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'Word Pool E';document.getElementById('pool-id-selection').innerText = 'WPE';">Word Pool E</a></li>
                        <li><a onclick="javascript:document.getElementById('stimulus-pool-title').innerText = 'All Pools';document.getElementById('pool-id-selection').innerText = 'wordPool';">All Pools</a></li>

                    </ul>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            <button style=" font-family: helveticaneue-light" class="btn"><i class="icon-plus-sign"></i>Save Project</button>
        </div>
    </form>
</div>

<div id="participants-modal-view" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div style="margin:0; padding:0" class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <div style="background-color:#d0d022; padding:5;"><h3 style="color:#f5f5f5; " id="myModalLabel">Project Participants</h3></div>
        <div style="background-color:lightgray; height:10px;"></div>
    </div>

        <div style class="modal-body">

            <div class="well">
                <div class="row-fluid">
                    <div class="span9">
                        <input id="participant-email-entry" class="small-input" style="width:100%;" type="input" placeholder="Enter participant PIN...">
                    </div>
                    <div class="span3">
                        <button style="width:100%" type="button" id="add-participant-button" class="btn">Add</button>
                    </div>
                </div>
            </div>

            <ul id="participants-list" class="nav nav-pills nav-stacked"></ul>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
</div>


<div id="question-modal-view" style="" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div style="margin:0; padding:0" class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <div style="background-color:#d0d022; padding:5;"><h3 style="color:#f5f5f5; " id="myModalLabel">Survey Question</h3></div>
        <div style="background-color:lightgray; height:10px;"></div>
    </div>
    <form id="create_question_form" action="/">
        <div class="modal-body" style="overflow:visible;">
                <div style=" width:100%; margin-bottom: 10px; text-align: center; font-family: helveticaneue-light" class="btn-group">
                    <a id="question-type-selector" style="width:77%;" class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                        <span id="question-type-title">Select Question Type</span>
                        <span class="caret"></span>
                    </a>
                    <ul style="width:100%;" class="dropdown-menu">
                        <li><a href="javascript:optionType(0)">Likert (1-7)</a></li>
                        <li><a href="javascript:optionType(1)">Multi-Option</a></li>
                        <li><a href="javascript:optionType(2)">Image Capture</a></li>
                    </ul>
                </div>

                <input style=" width:80%; margin-left: 10%; margin-right: 10%; text-align: center;margin-bottom: 10px;" type="input" id="add-question-modal-textbox"  placeholder="Enter Question / Instruction...">
                <div style="width:80%; margin-left: 10%; margin-right: 10%; margin-bottom:10px;" id="likertContainer">
                    <div id="likertlist">
                        <ul style="width:80%; margin-left: 10%; margin-right: 10%; text-align:center;" >
                            <li>1</li>
                            <li>2</li>
                            <li>3</li>
                            <li>4</li>
                            <li>5</li>
                            <li>6</li>
                            <li>7</li>
                        </ul>
                    </div>
                    <input style="width:49%; height:30px; font-size: smaller" type="input" name="add-question-modal-textbox-lower-descriptor"  placeholder="Enter a Low Descriptor (e.g. Not At All)...">
                    <input style="width:49%; height:30px;font-size: smaller ; text-align:right" type="input" name="add-question-modal-textbox-higher-descriptor"  placeholder="Enter a High Descriptor (e.g. Very Much So)...">
                </div>

                <div style="width:80%; margin-left: 10%; margin-right: 10%;" id="multioptionsContainer">
                    <script>
                        $(function() {
                            $( "#question-multiOptionList" ).sortable({
                                change: function( event, ui ) {

                                }
                            });
                            $( "#question-multiOptionList" ).disableSelection();
                        });
                    </script>
                    <ul id="question-multiOptionList" class="nav nav-pills nav-stacked">
                    </ul>
                    <div class="listEntryForm">
                         <input id="optionTitleInput" type="input"  placeholder="Add Option Title..">
                         <button type="button" onclick="addMultiOption()" class="btn">Add Option</button>
                    </div>
                </div>

                <div style="width:80%; margin-left: 10%; margin-right: 10%;" id="imageCaptureContainer">
                    <img height="55" style="display:block; margin:auto;" width="70" src="<?php echo Bones::get_instance()->make_route('public/img/camera.png') ?>">
                </div>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            <button style=" font-family: helveticaneue-light" class="btn"><i class="icon-plus-sign"></i> Save Question</button>
        </div>
    </form>
</div>

<div id="trigger-modal-view" class="modal hide fade">

    <div style="margin:0; padding:0" class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <div style="background-color:#d0d022; padding:5;"><h3 style="color:#f5f5f5; " id="myModalLabel">Survey Trigger</h3></div>
        <div style="background-color:lightgray; height:10px;"></div>
    </div>
    <form id="create_trigger_form" action="/">
        <div class="modal-body" style=" max-height:650px;">
            <div style=" width:80%; margin-right:10%; margin-left:10%;">
            <div id="triggerSelectorContainer" style="margin-bottom: 5px;" class="dropdown">
                <a id="trigger-type-title" style="width:94%" class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                    Select Trigger Type
                    <span class="caret"></span>
                </a>

                <ul style="width:94%" class="dropdown-menu">
                    <li><a id="spatialOption">Spatial</a></li>
                    <li><a id="temporalOption">Temporal</a></li>
                </ul>
            </div>

            <div id="spatialContainer" style="margin-bottom:5px">
                <div class="well">
                    <div id="googleMap" style="height:300px; margin-bottom: 5px"></div>
                    <div class="row-fluid">
                        <div class="span8">
                            <input style="width:100%" id="addressInput" type="input" class="small-input" placeholder="Enter a placename or postcode..">
                        </div>
                        <div class="span4">
                            <button style="width:100%" type="button" id="locationDive" class="btn">Find</button>
                        </div>
                    </div>
                </div>
                <div id="selected-location-trigger" class="alert alert-info">
                    <a id="removeLocation" class="close">x</a>
                    <div style="margin-bottom: 5px;">
                        <span id="fullAddressName"></span>
                    </div>
                    <div>
                        <span style="margin-right:5px; color:#a9a9a9">Area Radius (Metres)</span>
                        <span id="radiusValue"></span>
                        <div id="radiusSlider"></div>
                        <script>
                            $( "#radiusSlider" ).slider();
                            $( "#radiusSlider" ).slider( "option", "min", 5 );
                            $( "#radiusSlider" ).slider( "option", "max", 5000 );
                        </script>
                    </div>
                </div>

                <ul id="spatial-times-list" class="nav nav-pills nav-stacked"></ul>

                <div class="well">
                    <div class="row-fluid">
                        <div class="span6">
                            <div>
                                <span style="color:graytext; font-size: larger">Add Time Constraint</span>
                            </div>
                            <div>
                                <span>Start Point</span>
                            </div>
                        <div id="datetimepicker" style="width:100%;" class="input-append date datetimeselector">
                            <input style="width:80%;" type="text">
                            <span style="width:10%;" class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
                        </div>
                        </div>
                        <div style="height:40%; margin-top:5%;" class="span4">

                            <div>
                                <span >Active Period</span>
                            </div>
                            <div>
                            <input id="spatial-time-duration" class="small-input" style="width:100%;" type="input" placeholder="(Seconds)">
                                </div>
                        </div>
                        <div style=" height:40%; margin-top:9%;" class="span2">
                            <button style="width:100% ;" type="button" id="add-spatial-time-button" class="btn">Add</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="temporalContainer">
                <div class="well">
                    <div class="row-fluid">
                        <div class="span6">
                            <div>
                                <span>Start Point</span>
                            </div>
                            <div id="temporal-datetimepicker" style="width:100%;" class="input-append date datetimeselector">
                                <input style="width:80%;" type="text">
                                <span style="width:10%;" class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
                            </div>
                        </div>

                        <div class="span6">
                            <div>
                                <span>Active Period (Seconds)</span>
                            </div>
                            <input id="temporal-time-duration" class="small-input" style="width:100%;" type="input" placeholder="Duration (Seconds)">
                        </div>

                    </div>
                    <div class="row-fluid">
                        <div class="btn-group" data-toggle="buttons">
                            <button class="btn btn-default active" value="0">
                                Off
                            </button>
                            <button class="btn btn-default" value="5">
                                30 Seconds
                            </button>
                            <button class="btn btn-default" value="4">
                                Minute
                            </button>
                            <button class="btn btn-default" value="1">
                                Hour
                            </button>
                            <button class="btn btn-default" value="2">
                                Day
                            </button>
                            <button class="btn btn-default" value="3">
                                Week
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            <button class="btn"><i class="icon-plus-sign"></i> Save Trigger</button>
        </div>
    </form>
</div>

<script>

    $(document).on("change", ".question", function () {
        var surveyID =  $(this).data('surveyid');
        var questionID =  $(this).data('questionid');
        var questionText = $(this).val();
        ProjectsModelController.updateQuestionText(surveyID, questionID, questionText);
    });

    $(document).on("change", ".surveyText", function () {
        var surveyID =  $(this).data('surveyid');
        var surveyTitle = $(this).val();
        ProjectsModelController.updateSurveyTitle(surveyID, surveyTitle);
    });

    $('.datetimeselector').datetimepicker({
        format: 'dd/MM/yyyy hh:mm:ss',
        language: 'pt-BR'
    });

    $('#locationDive').click(function()
    {
        geocoder = new google.maps.Geocoder();

        var address = $('#addressInput').val();

        geocoder.geocode( { 'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK)
            {
                map.setCenter(results[0].geometry.location);

                var options = {
                    strokeColor: '#d0d022',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: ' #d0d022',
                    fillOpacity: 0.35,
                    map: map,
                    center: results[0].geometry.location,
                    radius: 50
                };

                if(!triggerCircle)
                {
                    triggerCircle = new google.maps.Circle(options);
                }
                else
                {
                    triggerCircle.setCenter(results[0].geometry.location);
                }

                geocoder.geocode({'latLng': results[0].geometry.location}, function(results2, status2) {

                    if (status2 == google.maps.GeocoderStatus.OK) {
                        if (results2[1])
                        {
                            $('#fullAddressName').html(results2[1].formatted_address);
                            $("#selected-location-trigger").show('slow');
                        }
                    } else {
                        alert("Failed to find address. Please try again.");
                    }
                });

                map.setZoom(15);

            } else {
                alert("Could not find the place, please check your entry.");
            }
        });

    });

    $('#removeLocation').click(function()
    {
        $('#selected-location-trigger').hide();
        triggerCircle.setMap(null);
        triggerCircle = null;
        $('#radiusSlider').slider('value', 5);
        $('#radiusValue').text(5);
    });

    $('#radiusSlider').on( "slide", function( event, ui )
    {
        if(triggerCircle)
        {
            var radius = ui.value;
            $('#radiusValue').text(radius);

            triggerCircle.setRadius(parseInt(radius));
            map.fitBounds(triggerCircle.getBounds());
        }
    });


    $('#add-spatial-time-button').click(function()
    {
        var duration = $("#spatial-time-duration").val();
        var timepicker = $('#datetimepicker').data('datetimepicker');
        var localtime = timepicker.getLocalDate();
        var isoDate;

        if(timepicker.getLocalDate().toString())
        {
            isoDate = new Date(timepicker.getLocalDate().toString()).toISOString();
        }
        else
        {
            alert('Date not set');
        }

        var timeGUID = generateGUID();
        var nonZdateFormat = isoDate.replace("Z","+0000");

        $("#spatial-times-list").append('<li id="' + timeGUID +'" data-utcformat="' + nonZdateFormat +'" data-duration="' + duration +'"><div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button><div><span style="color:#a9a9a9">Start Time </span><span>' + localtime + '</span></div><div><span style="color:#a9a9a9">Active Duration (Seconds) </span><span>' + duration + '</span></div></div></li>');

    });

    $('#add-participant-button').click(function()
    {
        var emailAddress = $("#participant-email-entry").val();
        var projectid = $('#participants-modal-view').data('projectid');
        ProjectsModelController.addParticipant(projectid,emailAddress);
        $("#participants-list").prepend('<li><div style="margin:4" class="alert alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button><span data-projectid="'+ projectid +'">' + emailAddress + '</span></div></li>');
    });


    $('#spatialOption').click(function()
    {
        $('#spatialContainer').show('slow',function(){
            google.maps.event.trigger(document.getElementById("googleMap"), 'resize');
        });
        $('#temporalContainer').hide();
        $('#trigger-type-title').html('Spatial');
    });

    $('#temporalOption').click(function()
    {
        $('#temporalContainer').show('slow');
        $('#spatialContainer').hide();
        $('#trigger-type-title').html('Temporal');
    });

    var temporalInterval = 0;
    $(".btn-group > .btn").click(function(){
        event.preventDefault();
        $(this).addClass("active").siblings().removeClass("active");
        temporalInterval = $(this).val();
    });

    $(document).on("click", ".open-add-project-modal", function () {
        event.preventDefault();
        var projectTextBox = $('#project_name_box');
        var projectName = projectTextBox.val();
        if(projectName){
            var modalWindow = $('#project-modal-view');
            modalWindow.modal('show');
        } else {
            alert('Specify a title for your new project.');
        }
    });

    $("#project-modal-view").on('hidden.bs.modal', function () {

    });

    $("#create_project_modal_form").submit(function()
    {
        event.preventDefault();
        var form = $(this);
        var typeSelection = form.find('span[id="project-type-title"]').text();
        var projectTextBox = $('#project_name_box');
        var projectName = projectTextBox.val();
        var modalWindow = $('#project-modal-view');

        if(typeSelection == "Stimulus"){
            var surveyAmount = form.find('input[id="surveyAmount"]').val();

            if(!isNaN(surveyAmount) && surveyAmount <= 12)
            {
                var wordListRangeMin = form.find('input[id="wordListRangeMin"]').val();
                var wordListRangeMax = form.find('input[id="wordListRangeMax"]').val();
                var defaultQuestion = form.find('input[id="defaultQuestion"]').val();
                var poolID = form.find('span[id="pool-id-selection"]').text();

                var recallOption = form.find('span[id="question-completeType-id-selection"]').text();

                if(recallOption != '') {

                    if (poolID != '') {

                        if (!isNaN(wordListRangeMin) || !isNaN(wordListRangeMax)) {
                            if (Number(wordListRangeMin) <= Number(wordListRangeMax)) {
                                ProjectsModelController.createProject(projectName, "StimulusStudy", surveyAmount, wordListRangeMin, wordListRangeMax, defaultQuestion, poolID, recallOption);
                                modalWindow.modal('hide');
                            }
                            else {
                                alert('The minimum value must be less than or equal to the maximum stimulus allocated to a trial.');
                            }
                        }
                        else {
                            alert('Both minimum and maximum stimulus values must be valid numbers.');
                        }
                    }
                    else {
                        alert('Please ensure a word pool have been selected.');
                    }
                }
                else
                {
                    alert('Please ensure recall options have been selected.');
                }
            }
            else
            {
                alert('The number of stimulus surveys specified must be a valid number and less or equal to 12 (current maximum allowable)');
            }

        }
        else
        {
            ProjectsModelController.createProject(projectName, "Project");
            modalWindow.modal('hide');
        }
    });


    $('#create_trigger_form').submit(function()
    {
        event.preventDefault();
        var form = $(this);
        var modalWindow = $('#trigger-modal-view');
        var surveyID = modalWindow.data('surveyid');
        var triggerID = modalWindow.data('triggerid');

        var typeSelection = form.find('a[id="trigger-type-title"]').text();


        if(typeSelection == 'Spatial')
        {
            if(triggerCircle)
            {
                var latlon = triggerCircle.getCenter();
                var lat = latlon.lat();
                var lon = latlon.lng();
                var radiusMetres = triggerCircle.getRadius();
                var placename = $('#fullAddressName').text();
                var timesContainer = form.find('ul[id="spatial-times-list"]');

                var timeListItems =  timesContainer.find('li');
                var children = [];

                if(timeListItems.length > 0)
                {
                    for (var i = 0; i < timeListItems.length; i++) {

                        var timeli = timeListItems[i];
                        var identifier = timeli.getAttribute('id');
                        var activationTime = timeli.getAttribute('data-utcformat');
                        var duration = parseInt(timeli.getAttribute('data-duration'));

                        var timeObject = ProjectsModelController.createTemporalTrigger(surveyID, activationTime, identifier, duration, true);

                        children.push(timeObject);
                    }
                }
                ProjectsModelController.createSpatialTrigger(surveyID,placename,radiusMetres, children, lat, lon, triggerID);

            } else {
                alert('You need to add a location to the trigger before adding to the survey.');
            }
        }
        else if(typeSelection == 'Temporal')
        {
            var tempduration = parseInt($("#temporal-time-duration").val());


            if (isNaN(tempduration) || Number(tempduration) < 1)
            {
                alert('You did not specify a valid Active Period duration.');
                return;
            }

            var temptimepicker = $('#temporal-datetimepicker').data('datetimepicker');
            if(!temptimepicker.getLocalDate())
            {
                alert('no date set');
                return;
            }

            var isoDate = new Date(temptimepicker.getLocalDate().toString()).toISOString();
            var tempidentifier = generateGUID();
            var tempactivationTime = isoDate.replace("Z","+0000");

            ProjectsModelController.createTemporalTrigger(
                        surveyID, tempactivationTime, tempidentifier, tempduration, false, temporalInterval
                    );
            modalWindow.modal('hide');
        }
        else
        {
            if(!triggerID)
            {
                alert('You will need to select a trigger type first.');
            }
        }

    });


    $("#create_question_form").submit(function()
    {
        event.preventDefault();

        var form = $(this);
        var questionText = form.find('input[id="add-question-modal-textbox"]').val();
        var higherDescriptor = form.find('input[name="add-question-modal-textbox-higher-descriptor"]').val();
        var lowerDescriptor = form.find('input[name="add-question-modal-textbox-lower-descriptor"]').val();

        var modalWindow = $('#question-modal-view');

        var surveyID = modalWindow.data('surveyid');
        var mode = modalWindow.data('mode');
        var questionID = modalWindow.data('questionid');

        var questionsTabContainer = $('#' + surveyID + '-survey-questions-list');
        var ordinal =  questionsTabContainer.find('li').length;

        var typeSelection = modalWindow.find('span[id="question-type-title"]').text();


        if(typeSelection == 'Likert (1-7)')
        {
            if(mode == 'create')
            {
                ProjectsModelController.createQuestion(surveyID,'SurveyLikertQuestion',questionText,ordinal, null,lowerDescriptor, higherDescriptor);
            }
            else if(mode == 'update')
            {

                ProjectsModelController.updateQuestion(surveyID, questionID,'SurveyLikertQuestion', questionText, null, lowerDescriptor, higherDescriptor );
            }

            modalWindow.modal('hide');

        }
        else if(typeSelection == 'Stimulus (Likert Orientation)')
        {
            if(mode == 'update')
            {

                ProjectsModelController.updateQuestion(surveyID, questionID,'StimulusQuestion', questionText, null, lowerDescriptor, higherDescriptor );
            }

            modalWindow.modal('hide');

        }
        else if(typeSelection == 'Stimulus Recall')
        {
            if(mode == 'update')
            {
                ProjectsModelController.updateQuestion(surveyID, questionID,'RecallQuestion', questionText, null, null, null);
            }

            modalWindow.modal('hide');
        }
        else if(typeSelection == 'Multi-Option')
        {
            var options = [];

            var listItems = $(".optionItem").map(function() {
                return this.innerText;
            }).get();

            for (var i = 0; i < listItems.length; i++) {

                var option = {
                    value:listItems[i],
                    type:'MultiOption',
                    ordinal:i
                };
                options.push(option);
            }

            if(mode == 'create')
            {
                ProjectsModelController.createQuestion(surveyID,'SurveyMultiOptionQuestion',questionText,ordinal, options,null, null);
            }
            else if(mode == 'update')
            {
                ProjectsModelController.updateQuestion(surveyID, questionID,'SurveyMultiOptionQuestion', questionText, options, null, null);
            }

            modalWindow.modal('hide');
        }
        else if(typeSelection == 'Image Capture')
        {
            if(mode == 'create')
            {
                ProjectsModelController.createQuestion(surveyID,'ImageExperienceCapture', questionText,ordinal, null, null, null);
            }
            else if(mode == 'update')
            {

                ProjectsModelController.updateQuestion(surveyID, questionID,'ImageExperienceCapture', questionText, null, null, null);
            }

            modalWindow.modal('hide');

        }
        else
        {
            alert('Please select a type of question.');
        }


    });

    $(document).on("click", ".removeQuestion", function () {

        var removeButton = $(this);
        var quesitonid = removeButton.data('questionid');

        var surveyID =  ProjectsModelController.getQuestionSurveyID(quesitonid);
        var divquestionsContainer = $('#' + surveyID + '-survey-questions-list');

        divquestionsContainer.find('ul').empty();

        ProjectsModelController.removeQuestion(quesitonid);
    });

    $(document).on("click", ".removeProject", function () {
        var removeButton = $(this);
        var projectid = removeButton.data('projectid');
        $('#' + projectid).remove();
        ProjectsModelController.removeProject(projectid);
    });

    $(document).on("click", ".removeTrigger", function () {
        event.preventDefault();
        var removeButton = $(this);
        var triggerId = removeButton.data('trigger');
        $('#' + triggerId).remove();
        ProjectsModelController.removeTrigger(triggerId);
    });


    $("#create_option_form").submit(function()
    {
        event.preventDefault();
        var form = $(this);
        var multiOptionLabel = form.find('input[id="optionTitleInput"]').val();
        $("#question-multiOptionList").append('<li ><div style="margin-bottom:4px; overflow:hidden;" class="alert alert-info"><div style="float:left; margin-right:20;"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></div><div style="float:left"><span class="optionItem">' + multiOptionLabel +'</span></div><div style="float:right"><a id="r" class="close">x</a></div></div></li>');
    });

    $(document).on("click", ".open-add-question-modal", function () {

        var form = $(this);
        var viewMode = form.data('mode');
        var surveyID = form.data('surveyid');
        $('#question-type-selector').show();
        $("#question-multiOptionList").empty();

        if(viewMode == 'update')
        {
            $('#question-type-selector').hide();

            var questionID = form.data('questionid');
            $('#question-modal-view').data('questionid', questionID);

            var questionObj = ProjectsModelController.getQuestion(surveyID, questionID);

            $('#add-question-modal-textbox').val(questionObj.question);

            if(questionObj.type == 'SurveyMultiOptionQuestion')
            {
                optionType(1);

                for(var optionindex in questionObj.options)
                {
                    var option = questionObj.options[optionindex];
                    $("#question-multiOptionList").append('<li ><div style="margin-bottom:4px; overflow:hidden;" class="alert alert-info"><div style="float:left; margin-right:20;"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></div><div style="float:left"><span class="optionItem">' + option.value +'</span></div><div style="float:right"><a id="r" class="close">x</a></div></div></li>');
                }
            }
            else if(questionObj.type == 'SurveyLikertQuestion')
            {
                optionType(0);

                $('input[name="add-question-modal-textbox-higher-descriptor"]').val(questionObj.high_end_descriptor);
                $('input[name="add-question-modal-textbox-lower-descriptor"]').val(questionObj.low_end_descriptor);
            }
            else if(questionObj.type == 'ImageExperienceCapture')
            {
                optionType(2);
            }
            else if(questionObj.type == 'StimulusQuestion')
            {
                optionType(3);
                $('input[name="add-question-modal-textbox-higher-descriptor"]').val(questionObj.high_end_descriptor);
                $('input[name="add-question-modal-textbox-lower-descriptor"]').val(questionObj.low_end_descriptor);
            }
            else if(questionObj.type == 'RecallQuestion')
            {
                optionType(4);
            }
            else
            {
                alert('unknown question type');
            }
        }
        else if(viewMode == 'create')
        {
            $('#add-question-modal-textbox').val('');
            $('input[name="add-question-modal-textbox-higher-descriptor"]').val('');
            $('input[name="add-question-modal-textbox-lower-descriptor"]').val('');
            optionType(-1);
        }
        else
        {
            alert('error unknown mode');
        }

        $('#question-modal-view').data('surveyid', surveyID);
        $('#question-modal-view').data('mode', viewMode);

    });

    $(document).on("click", ".open-participants-modal", function () {

        var projectID = $(this).data('projectid');

        var projectObj = ProjectsModelController.getProjectByID(projectID);

        if(projectObj.stimulus_pool)
        {
            getWordsFromAPI(projectObj.stimulus_pool);
        }
        $('#participants-modal-view').data('projectid', projectID);

        $("#participants-list").empty();

        if(projectObj.participants.length > 0)
        {
            for(var participantIndex in projectObj.participants)
            {
                var participant = projectObj.participants[participantIndex];
                var id = "";
                if(participant.identifier){
                    id = participant.identifier;
                } else {
                    id = participant;
                }
                $("#participants-list").prepend(
                    '<li>' +
                        '<div style="margin:4" class="alert alert-info">' +
                            '<button type="button" class="close remove-participant" data-dismiss="alert">' +
                                '&times;'+
                            '</button>' +
                            '<span data-projectid="'+ projectID +'">' +
                                '' + id+ '' +
                            '</span>' +
                        '</div>' +
                    '</li>');
            }
        }
    });

    $(document).on("click", ".remove-participant", function () {

        var parentDiv = $(this).parent();
        var emailSpan = parentDiv.find('span');
        var projectID = emailSpan.data('projectid');
        ProjectsModelController.removeParticipant(projectID, emailSpan.text());
    });


    $(document).on("click", ".open-trigger-modal", function () {

        var surveyID = $(this).data('surveyid');
        var triggerID = $(this).data('triggerid');
        $('#triggerSelectorContainer').show();
        if(triggerCircle)
        {
            triggerCircle.setMap(null);
            triggerCircle = null;
            map.setZoom(3);      // This will trigger a zoom_changed on the map
            map.setCenter(new google.maps.LatLng(52.486243, -1.890401));
        }

        $('#trigger-type-title').html('Select Trigger Type');
        $("#addressInput").val('');

        $("#fullAddressName").empty();

        $("#radiusSlider").slider( "value", 50 );
        $('#radiusValue').text(50);

        $("#selected-location-trigger").hide();


        $("#spatial-times-list").empty();
        var spatialTimepicker = $('#datetimepicker').data('datetimepicker');
        spatialTimepicker.setLocalDate(null);
        $("#spatial-time-duration").val('');
        $("#spatialContainer").hide();

        var temporalTimepicker = $('#temporal-datetimepicker').data('datetimepicker');
        temporalTimepicker.setLocalDate(null);
        $("#temporal-time-duration").val('');
        $("#temporalContainer").hide();


        $('#trigger-modal-view').data('surveyid', surveyID);

        if (triggerID)
        {
            surveyID = ProjectsModelController.getTriggerSurveyID(triggerID);
            $('#trigger-modal-view').data('surveyid', surveyID);

            $('#triggerSelectorContainer').hide();

            $('#trigger-modal-view').data('triggerid', triggerID);

            var triggerObj = ProjectsModelController.getTrigger(surveyID, triggerID);

            if(triggerObj.type == 'SpatialTrigger')
            {
                $('#spatialContainer').show('slow',function()
                {
                    google.maps.event.trigger(document.getElementById("googleMap"), 'resize');
                    $('#temporalContainer').hide();
                    $('#trigger-type-title').html('Spatial');

                    var options = {
                        strokeColor: '#d0d022',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: ' #d0d022',
                        fillOpacity: 0.35,
                        map: map,
                        center: new google.maps.LatLng(triggerObj.latitude, triggerObj.longitude),
                        radius: parseInt(triggerObj.radius)
                    };

                    triggerCircle = new google.maps.Circle(options);
                    map.fitBounds(triggerCircle.getBounds());

                    $("#selected-location-trigger").show('slow',function()
                    {
                        $('#fullAddressName').html(triggerObj.placename);
                        $('#radiusSlider').slider('value', triggerCircle.radius);
                        $('#radiusValue').text(triggerCircle.radius);

                    });

                    if(triggerObj.children.length > 0)
                    {
                        for(var temporalIndex in triggerObj.children)
                        {
                            var temporalTrigger = triggerObj.children[temporalIndex];
                            var duration = temporalTrigger.duration;
                            var timeGUID = temporalTrigger.identifier;

                            var utcTimeString = temporalTrigger.activation_time;
                            var activationTimeObj = new Date(utcTimeString);
                            var localTime = activationTimeObj.toString();

                            $("#spatial-times-list").append('<li id="' + timeGUID + '" data-utcformat="' + utcTimeString +'" data-duration="' + duration +'"><div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button><div><span style="color:#a9a9a9">Start Time </span><span>' + localTime + '</span></div><div><span style="color:#a9a9a9">Active Duration (Seconds) </span><span>' + duration + '</span></div></div></li>');
                        }
                    }


                });
            }
            else
            {
                $('#temporalContainer').show('slow',function()
                {
                    var temporalTimepicker = $('#temporal-datetimepicker').data('datetimepicker');

                    var utcTimeString = triggerObj.activation_time;
                    var activationTimeObj = new Date(utcTimeString);

                    temporalTimepicker.setLocalDate(new Date(activationTimeObj));

                    $("#temporal-time-duration").val(triggerObj.duration);

                });
            }
        }
    });

    $('#trigger-modal-view').on('shown', function () {
        google.maps.event.trigger(document.getElementById("googleMap"), 'resize');
    });

</script>