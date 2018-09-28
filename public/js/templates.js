var hostname = '';

var ErrorHandler = {

    handle:function(error)
    {
        switch (parseInt(error.code))
        {
            case 401:
                alert('Your session has expired. You will be redirected to login.');
                window.location = "../";

                break;
            default:
                alert("Oops! something went wrong. Logout and try again.");
        }
    }
};

var ProjectsModelController = {

    projects: [],
    setCookie: function(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    },
    downloadProjects: function()
    {
        $.ajax({
            url: 'http://' + hostname + '/coordinator/surveys',
            cache: false,
            async: false,
            dataType: 'json'
           ,
            success: function(data)
            {
                ProjectsModelController.projects = data;
                ProjectsModelController.reloadProjects();
            },
            error: function(jqXHR, exception) {
                if (jqXHR.status === 0) {
                    console.log('Not connect.\n Verify Network.');
                } else if (jqXHR.status == 404) {
                    console.log('Requested page not found. [404]');
                } else if (jqXHR.status == 500) {
                    console.log('Internal Server Error [500].');
                } else if (exception === 'parsererror') {
                    console.log('Requested JSON parse failed.');
                } else if (exception === 'timeout') {
                    console.log('Time out error.');
                } else if (exception === 'abort') {
                    console.log('Ajax request aborted.');
                } else {
                    console.log('Uncaught Error.\n' + jqXHR.responseText);
                }
            }
        });
    },
    reloadProjects: function(){
        $('#projectsContainer').empty();
        for(var projectIndex in ProjectsModelController.projects)
        {
            var project = ProjectsModelController.projects[projectIndex];
            UIController.addProjectItem(project);


            for(var survey in project.surveys)
            {
                UIController.addSurveyItem(project.identifier, project.surveys[survey]);
            }
        }
    },
    addParticipant: function(projectID, emailIdentifier)
    {
        var participantObj =
        {
            identifier:emailIdentifier
        };

        for(var projectIndex in this.projects)
        {
            if(this.projects[projectIndex].identifier == projectID)
            {
                this.projects[projectIndex].participants.push(participantObj);

                this.saveProject(this.projects[projectIndex]);

                if(this.projects[projectIndex].type == "StimulusStudy")
                {
                    allocateWordListsToUser(this.projects[projectIndex],emailIdentifier);
                }
                break;
            }
        }
    },
    removeParticipant: function(projectID, emailIdentifier)
    {
        var participantObj =
        {
            identifier:emailIdentifier
        };

        for(var projectIndex in this.projects)
        {
            if(this.projects[projectIndex].identifier == projectID)
            {
                for(var participantIndex in this.projects[projectIndex].participants)
                {
                    if(this.projects[projectIndex].participants[participantIndex].identifier == emailIdentifier)
                    {
                        this.projects[projectIndex].participants.splice(participantIndex,1);

                        if("stimulus_alloc" in this.projects[projectIndex]) {

                            for (var stimIndex in this.projects[projectIndex].stimulus_alloc) {
                                if (this.projects[projectIndex].stimulus_alloc[stimIndex].identifier == emailIdentifier) {
                                    this.projects[projectIndex].stimulus_alloc.splice(stimIndex, 1);
                                    break;
                                }
                            }
                        }

                        this.saveProject(this.projects[projectIndex]);
                        break;
                    }
                }
            }
        }
    },
    createProject: function(title, type, surveyAmount, wordListRangeMin, wordListRangeMax, defaultQuestion, poolID, recallOption)
    {
        var projectID = generateGUID();
        var projectObj= null;

        if(type == 'Project')
        {
            projectObj = {
                identifier: projectID,
                name: title,
                participants: [],
                revision:generateGUID(),
                surveys:[],
                coordinator:$.cookie('xpr_username'),
                type:type
            };
        }
        else
        {
            projectObj = {
                identifier: projectID,
                name: title,
                participants: [],
                revision:generateGUID(),
                surveys:[],
                coordinator:$.cookie('xpr_username'),
                type:type,
                stimulus_alloc:[],
                stimulus_max:0,
                stimulus_min:0,
                stimulus_pool:poolID,
                default_question:'How much do you like this word?',
                ask_recall:parseInt(recallOption)
            };

            surveyAmount = surveyAmount || 10;
            projectObj.stimulus_min = wordListRangeMin || 1;
            projectObj.stimulus_max = wordListRangeMax || 16;
            projectObj.default_question = defaultQuestion || 'How much do you like this word?';

            this.projects.push(projectObj);
            UIController.addProjectItem(projectObj);

            for(var i = 0; i < surveyAmount; i++)
            {
                var surveyID = this.createSurvey(projectObj.identifier, "Trial "+i, "StimulusSurveyForm");

                for(var j = 0; j< projectObj.stimulus_max; j++)
                {
                    this.createQuestion(surveyID, "StimulusQuestion", projectObj.default_question, j, null, "","");
                }
                this.createQuestion(surveyID, "RecallQuestion", "Please enter as many words as you can remember", projectObj.stimulus_max, null, "","");
            }

        }

        updateProjectAPI(projectObj);
    },
    createSurvey: function(projectID, title, surveyType)
    {
        var surveyID = generateGUID();
        var surveyObj =
        {
            questions: [],
            type:surveyType,
            triggers:[],
            state:0,
            identifier:surveyID,
            title:title
        };

        for(var projectIndex in this.projects)
        {
            if(this.projects[projectIndex].identifier == projectID)
            {
                this.projects[projectIndex].surveys.push(surveyObj);
                this.saveProject(this.projects[projectIndex]);
                UIController.addSurveyItem(projectID,surveyObj);
                break;
            }
        }
        return surveyID;
    },

    updateSurveyTitle: function(surveyID, surveyText)
    {
        for(var projectIndex in this.projects)
        {
            for(var surveyIndex in this.projects[projectIndex].surveys)
            {
                if(this.projects[projectIndex].surveys[surveyIndex].identifier == surveyID)
                {
                    this.projects[projectIndex].surveys[surveyIndex].title = surveyText;
                    this.saveProject(this.projects[projectIndex]);
                }
            }
        }
    },
    createQuestion: function(surveyID, questionType, questionText, ordinal, options, lowDescriptor, highDescriptor)
    {
        var questionID = generateGUID();
        var questionObj = null;

        if(questionType == 'SurveyMultiOptionQuestion')
        {
            questionObj =
            {
                options: options,
                type:questionType,
                identifier:questionID,
                ordinal:ordinal,
                question:questionText
            };
        }
        else if(questionType == 'SurveyLikertQuestion')
        {
            var likertOptions = [];
            for (var i = 1; i < 8; i++) {
                var option = {
                    type:'LikertOption',
                    value:i
                };
                likertOptions.push(option);
            }

            questionObj =
            {
                options: likertOptions,
                type:questionType,
                low_end_descriptor:lowDescriptor,
                identifier:questionID,
                ordinal:ordinal,
                question:questionText,
                high_end_descriptor:highDescriptor
            };
        }
        else if(questionType == 'ImageExperienceCapture')
        {
            questionObj =
            {
                type:questionType,
                identifier:questionID,
                ordinal:ordinal,
                question:questionText
            };
        }
        else if(questionType == 'RecallQuestion')
        {
            questionObj =
            {
                type:questionType,
                identifier:questionID,
                ordinal:ordinal,
                question:questionText
            };
        }
        else if(questionType == 'StimulusQuestion')
        {
            var likertOptions = [];
            for (var i = 1; i < 8; i++) {
                var option = {
                    type:'LikertOption',
                    value:i
                };
                likertOptions.push(option);
            }

            questionObj =
            {
                options: likertOptions,
                type:questionType,
                identifier:questionID,
                ordinal:ordinal,
                question:questionText

            };
        }

        for(var projectIndex in this.projects)
        {
            for(var surveyIndex in this.projects[projectIndex].surveys)
            {
                if(this.projects[projectIndex].surveys[surveyIndex].identifier == surveyID)
                {
                    this.projects[projectIndex].surveys[surveyIndex].questions.push(questionObj);

                    this.saveProject(this.projects[projectIndex]);
                    UIController.addQuestionItem(surveyID, questionObj);
                    break;
                }
            }
        }
        return questionID;
    },
    updateQuestion: function(surveyID, questionID, questionType, questionText, options, lowDescriptor, highDescriptor)
    {
        var questionObj = this.getQuestion(surveyID, questionID);

        if(questionType == 'SurveyMultiOptionQuestion')
        {
            questionObj.options = options;
            questionObj.type = questionType;
            questionObj.question = questionText;
        }
        else if(questionType == 'SurveyLikertQuestion')
        {
            var likertOptions = [];
            for (var i = 1; i < 8; i++) {
                var option = {
                    type:'LikertOption',
                    value:i
                };
                likertOptions.push(option);
            }
            questionObj.options = likertOptions;
            questionObj.type = questionType;
            questionObj.low_end_descriptor = lowDescriptor;
            questionObj.question = questionText;
            questionObj.high_end_descriptor = highDescriptor;
        }
        else if(questionType == 'StimulusQuestion')
        {
            var likertOptions = [];
            for (var i = 1; i < 8; i++) {
                var option = {
                    type:'LikertOption',
                    value:i
                };
                likertOptions.push(option);
            }
            questionObj.options = likertOptions;
            questionObj.type = questionType;
            questionObj.low_end_descriptor = lowDescriptor;
            questionObj.question = questionText;
            questionObj.high_end_descriptor = highDescriptor;
        }
        else if(questionType == 'ImageExperienceCapture')
        {

            questionObj.type = questionType;
            questionObj.question = questionText;
        }
        else if(questionType == 'RecallQuestion')
        {

            questionObj.type = questionType;
            questionObj.question = questionText;
        }
        
        for(var projectIndex in this.projects)
        {
            for(var surveyIndex in this.projects[projectIndex].surveys)
            {
                if(this.projects[projectIndex].surveys[surveyIndex].identifier == surveyID)
                {
                    this.saveProject(this.projects[projectIndex]);
                    UIController.addQuestionItem(surveyID, questionObj);
                }
            }
        }
    },
    updateQuestionText: function(surveyID, questionID, questionText)
    {
        var questionObj = this.getQuestion(surveyID, questionID);
        questionObj.question = questionText;

        for(var projectIndex in this.projects)
        {
            for(var surveyIndex in this.projects[projectIndex].surveys)
            {
                if(this.projects[projectIndex].surveys[surveyIndex].identifier == surveyID)
                {
                   this.saveProject(this.projects[projectIndex]);
                }
            }
        }
    },
    getQuestion: function(surveyID, questionID)
    {

        for(var projectIndex in this.projects)
        {
            for(var surveyIndex in this.projects[projectIndex].surveys)
            {
                if(this.projects[projectIndex].surveys[surveyIndex].identifier == surveyID)
                {
                    var survey = this.projects[projectIndex].surveys[surveyIndex];
                    for(var questionIndex in survey.questions)
                    {
                        if(survey.questions[questionIndex].identifier == questionID)
                        {
                            var question = survey.questions[questionIndex];
                            return question;
                        }
                    }
                }
            }
        }
        return null;
    },
    removeQuestion: function(questionid)
    {
        for(var projectIndex in this.projects)
        {
            for(var surveyIndex in this.projects[projectIndex].surveys)
            {
                var reorder = false;

                for(var questionIndex in this.projects[projectIndex].surveys[surveyIndex].questions)
                {
                    if(this.projects[projectIndex].surveys[surveyIndex].questions[questionIndex].identifier == questionid)
                    {
                        this.projects[projectIndex].surveys[surveyIndex].questions.splice(questionIndex,1);
                        reorder = true;
                    }

                    if(reorder && this.projects[projectIndex].surveys[surveyIndex].questions.length > (questionIndex + 1))
                    {
                        var ordinal = this.projects[projectIndex].surveys[surveyIndex].questions[questionIndex].ordinal;
                        this.projects[projectIndex].surveys[surveyIndex].questions[questionIndex].ordinal = (parseInt(ordinal) - 1);

                    }
                }

                if(reorder)
                {
                    this.saveProject(this.projects[projectIndex]);
                    UIController.reloadSurveyQuestions(this.projects[projectIndex].surveys[surveyIndex].identifier,
                                                        this.projects[projectIndex].surveys[surveyIndex].questions);
                    break;
                }
            }
        }
    },
    removeTrigger: function(triggerid)
    {
        for(var projectIndex in this.projects){
            for(var surveyIndex in this.projects[projectIndex].surveys){
                var reorder = false;
                for(var triggerIndex in this.projects[projectIndex].surveys[surveyIndex].triggers){
                    if(this.projects[projectIndex].surveys[surveyIndex].triggers[triggerIndex].identifier == triggerid){
                        this.projects[projectIndex].surveys[surveyIndex].triggers.splice(triggerIndex,1);
                        reorder = true;
                    }
                    if(reorder && this.projects[projectIndex].surveys[surveyIndex].triggers.length > (triggerIndex + 1)){
                        var ordinal = this.projects[projectIndex].surveys[surveyIndex].triggers[questionIndex].ordinal;
                        this.projects[projectIndex].surveys[surveyIndex].triggers[triggerIndex].ordinal = (parseInt(ordinal) - 1);
                    }
                }

                if(reorder){
                    this.saveProject(this.projects[projectIndex]);
                    UIController.reloadSurveyTriggers(
                        this.projects[projectIndex].surveys[surveyIndex].identifier,
                        this.projects[projectIndex].surveys[surveyIndex].triggers
                    );
                    break;
                }
            }
        }
    },
    removeProject: function(projectid)
    {
        for(var projectIndex in this.projects)
        {
            if(this.projects[projectIndex].identifier == projectid)
            {
                this.deleteProject(this.projects[projectIndex]);
                this.projects.splice(projectIndex,1);
            }
        }
    },
    createQuestionOption: function(questionID, optionType, option)
    {
        var optionObj = null;

        if(optionType == 'MultiOption')
        {
            optionObj = {
                value:option,
                type:optionType,
                ordinal:0
            };
        }
        else if(optionType == 'LikertOption')
        {
            optionObj = {
                value:option,
                type:optionType
            };
        }

        for(var projectIndex in this.projects)
        {
            for(var surveyIndex in this.projects[projectIndex].surveys)
            {
                for(var questionIndex in this.projects[projectIndex].surveys[surveyIndex].questions)
                {
                    if(this.projects[projectIndex].surveys[surveyIndex].questions[questionIndex].identifier == questionID)
                    {
                        this.projects[projectIndex].surveys[surveyIndex].questions[questionIndex].options.push(optionObj);
                        this.saveProject(this.projects[projectIndex]);

                        return;
                    }
                }
            }
        }
    },
    createSpatialTrigger: function(surveyID, placename, radius, children, lat, lon, triggerID)
    {
        var spatial;

        if(!triggerID)
        {
            var identifier = generateGUID();
            spatial = {
                type:'SpatialTrigger',
                placename:placename,
                radius:radius,
                children:children,
                identifier:identifier,
                state:0,
                latitude:lat,
                longitude:lon
            };

            for(var projectIndex in this.projects)
            {
                for(var surveyIndex in this.projects[projectIndex].surveys)
                {
                    if(this.projects[projectIndex].surveys[surveyIndex].identifier == surveyID)
                    {
                        this.projects[projectIndex].surveys[surveyIndex].triggers.push(spatial);

                        this.saveProject(this.projects[projectIndex]);
                        UIController.addTriggerItem(surveyID, spatial);
                        break;
                    }
                }
            }
        }
        else
        {
            spatial = this.getTrigger(surveyID, triggerID);
            spatial.placename = placename;
            spatial.radius = radius;
            spatial.children = children,
            spatial.state = 0;
            spatial.latitude = lat;
            spatial.longitude = lon;

            this.saveProject(this.getProject(surveyID));

            UIController.addTriggerItem(surveyID, spatial);
        }
    },
    createTemporalTrigger: function(surveyID, activationTime, identifier, duration, isChild, temporalInterval)
    {
        var temporalTriggerObj = {
            activation_time:activationTime,
            type:'TemporalTrigger',
            children:[],
            state:0,
            identifier:identifier,
            duration:duration,
            interval:parseInt(temporalInterval)
        };

        if(!isChild)
        {
            for(var projectIndex in this.projects)
            {
                for(var surveyIndex in this.projects[projectIndex].surveys)
                {
                    if(this.projects[projectIndex].surveys[surveyIndex].identifier == surveyID)
                    {
                        this.projects[projectIndex].surveys[surveyIndex].triggers.push(temporalTriggerObj);
                        this.saveProject(this.projects[projectIndex]);
                        UIController.addTriggerItem(surveyID, temporalTriggerObj);
                        break;
                    }
                }
            }
        }
        return temporalTriggerObj;
    },
    getTrigger: function(surveyID, triggerID)
    {
        for(var projectIndex in this.projects)
        {
            for(var surveyIndex in this.projects[projectIndex].surveys)
            {
                if(this.projects[projectIndex].surveys[surveyIndex].identifier == surveyID)
                {
                    var survey = this.projects[projectIndex].surveys[surveyIndex];
                    for(var triggerIndex in survey.triggers)
                    {
                        if(survey.triggers[triggerIndex].identifier == triggerID)
                        {
                            var trigger = survey.triggers[triggerIndex];
                            return trigger;
                        }
                    }
                }
            }
        }
        return null;
    },
    getTriggerSurveyID: function(triggerID)
    {
        for(var projectIndex in this.projects)
        {
            for(var surveyIndex in this.projects[projectIndex].surveys)
            {
                for(var triggerIndex in this.projects[projectIndex].surveys[surveyIndex].triggers)
                {
                    if(this.projects[projectIndex].surveys[surveyIndex].triggers[triggerIndex].identifier == triggerID)
                    {
                        return this.projects[projectIndex].surveys[surveyIndex].identifier;
                    }
                }
            }
        }
        return null;
    },
    getQuestionSurveyID: function(questionID)
    {
        for(var projectIndex in this.projects)
        {
            for(var surveyIndex in this.projects[projectIndex].surveys)
            {
                for(var questionIndex in this.projects[projectIndex].surveys[surveyIndex].questions)
                {
                    if(this.projects[projectIndex].surveys[surveyIndex].questions[questionIndex].identifier == questionID)
                    {
                        return this.projects[projectIndex].surveys[surveyIndex].identifier;
                    }
                }
            }
        }
        return null;
    },
    getProject: function(surveyID)
    {
        for(var projectIndex in this.projects)
        {
            for(var surveyIndex in this.projects[projectIndex].surveys)
            {
                if(this.projects[projectIndex].surveys[surveyIndex].identifier == surveyID)
                {
                    return this.projects[projectIndex];
                }
            }
        }
        return null;
    },
    getProjectByID: function(projectID)
    {

        for(var projectIndex in this.projects)
        {
            if(this.projects[projectIndex].identifier == projectID)
            {
                return this.projects[projectIndex];
            }
        }
        return null;
    },
    saveProject: function(project)
    {
        $.ajax({
            url: 'http://'+ hostname +'/project/update',
            cache: true,
            async: false,
            type: "post",
            data: JSON.stringify(project),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data)
            {
                if(data.resp == 'error')
                {
                    ErrorHandler.handle(data);
                }
                project._rev = data.body.rev;
            }
        });
    },
    deleteProject: function(project)
    {
        $.ajax({
            url: 'http://'+ hostname +'/project/delete',
            cache: true,
            async: false,
            type: "post",
            data: JSON.stringify(project),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data)
            {
                if(data.resp == 'error')
                {
                    ErrorHandler.handle(data);
                }
            }
        });
    }
};



function updateProjectAPI(projectObj){

    $.ajax({
        url: 'http://' + hostname + '/project/create',
        cache: false,
        async: false,
        type: "post",
        data: JSON.stringify(projectObj),
        contentType: 'application/json',
        dataType: 'json',
        success: function(data)
        {
            if(data.resp == 'error')
            {
                ErrorHandler.handle(data);
            }
            else
            {
                for(var projectIndex in ProjectsModelController.projects)
                {
                    if(ProjectsModelController.projects[projectIndex].identifier == projectObj.identifier)
                    {
                        ProjectsModelController.projects[projectIndex]["_id"] = data.body.id;
                        ProjectsModelController.projects[projectIndex]["_rev"] = data.body.rev;
                    }
                }
            }
        }
    });
}


var StimulusAllocator = {
    projectID : 0,
    surveyAmount:0,
    wordListRange:0,
    wordLists:{id:generateGUID(), lists:[]},
    surveyIDs:[],
    participantIDs:[],
    questionIDs:[],
    words:[]
};



function getWordsFromAPI(poolID){

    $.ajax({
        url: 'http://'+ hostname +'/words?id=' + poolID,
        cache: true,
        type: "GET",
        contentType: 'application/json',
        dataType: 'json',
        success: function(data)
        {
            if(data.resp == 'error'){
                ErrorHandler.handle(data);
            } else {
                StimulusAllocator.words = data;
            }
        }
    });
}





function getUniqueWord(wordPool)
{
    var selected_word = JSON.parse(JSON.stringify(wordPool[0]));
    wordPool.splice(0,1);
    return selected_word;
}

function shuffle(a) {
    var j, x, i;
    for (i = a.length; i; i -= 1) {
        j = Math.floor(Math.random() * i);
        x = a[i - 1];
        a[i - 1] = a[j];
        a[j] = x;
    }
}

function allocateWordListsToUser(projectObj, participantID)
{
    if(StimulusAllocator.words.length > 0)
    {
        var participantObj =
        {
            "identifier":participantID,
            "trials":[]
        };

        var tempWords = StimulusAllocator.words.slice();

        for(var i = 0; i < projectObj.surveys.length; i++)
        {
            var range = projectObj.stimulus_max - projectObj.stimulus_min;
            var rand = Math.random();
            var offsetLength = Math.floor(rand * range);
            var wordlistLength = parseInt(projectObj.stimulus_min) + parseInt(offsetLength);

            var newList =
            {
                id: generateGUID(),
                type:"StimulusSet",
                words:[]
            };
            var allocationStim = [];

            var qIndex = 0;
            while(qIndex < wordlistLength)
            {

                shuffle(tempWords);
                var word = getUniqueWord(tempWords);

                var stimID = generateGUID();


                newList.words.push({
                    "identifier":stimID,
                    "value":word,
                    "type":"Stimulus"
                });

                allocationStim.push({
                    "identifier":stimID,
                    "value":word,
                    "stimulus_question_identifier":projectObj.surveys[i].questions[qIndex].identifier,
                    "type":"Stimulus"
                });

                qIndex++;
            }

            saveWordListtoAPI(newList);


            var trialObj = {
                "type": "StimulusAllocation",
                "trial_identifier":projectObj.surveys[i].identifier,
                "trial_name":projectObj.surveys[i].title,
                "stimulus_list_identifier":newList.id,
                "stimulus":allocationStim
            };

            participantObj.trials.push(trialObj);
        }


        if(projectObj.stimulus_alloc.length > 0)
        {
            var inde = 0;
            var found = false;
            for(var r = 0; r < projectObj.stimulus_alloc.length; r++)
            {
                if(projectObj.stimulus_alloc[r].identifier == participantID)
                {
                    inde = r;
                    found = true;
                    break;
                }
            }

            if(found) {
                projectObj.stimulus_alloc.splice(inde, 1);
            }
        }

        projectObj.stimulus_alloc.push(participantObj);
        ProjectsModelController.saveProject(projectObj);
    }
}



function generateWordLists(words, surveyAmount, range){
    var howMany = 0;
    var index = 0;

    for(var i = 0; i < 50; i++)
    {
        var rand = Math.random();
        howMany = Math.floor(rand * range);


        var wordList = [];

        for(var x= 0;x < howMany;x++)
        {
            var uniqueWord = getUniqueWord(wordList, words);
            wordList.push(uniqueWord);
        }

        var generatedWordList = {identifier:generateGUID(), type:"StimulusSet", words:[]};
        var qIndex = 0;
        var max = wordList.length;

        while(qIndex < max)
        {
            var wordIndex = Math.floor(Math.random() * wordList.length);
            generatedWordList.words.push({
                "identifier":generateGUID(),
                "value":wordList[wordIndex],
                "stimulus_question_identifier":StimulusAllocator.questionIDs[qIndex],
                "type":"Stimulus"
            });

            wordList.splice(wordIndex, 1);
            qIndex++;
        }

        StimulusAllocator.wordLists.lists.push(generatedWordList);
    }
    saveWordListtoAPI(StimulusAllocator.wordLists);
    generateStimulusAllocation();
}

function saveWordListtoAPI(wordLists){
    $.ajax({
        url: 'http://'+ hostname+'/wordList/create',
        type: "post",
        data: JSON.stringify(wordLists),
        contentType: 'application/json',
        dataType: 'json'
    });
}

function retrieveWordList(wordListID, participantID){
    $.ajax({
        url: 'http://' +hostname + '/wordsList?wordsListID='+wordListID,
        cache: true,
        type: "GET",
        contentType: 'application/json',
        dataType: 'json',
        success: function(data)
        {
            if(data.resp == 'error'){
                ErrorHandler.handle(data);
            } else {
                StimulusAllocator.wordLists = data;
                addStimulusAllocation(participantID);
            }
        }
    });
}

function generateStimulusAllocation(){
    var stimulusAllocations = [];
    for(var i = 0; i<StimulusAllocator.participantIDs.length; i++){

        var participantObj = {"identifier":StimulusAllocator.participantIDs[i], trials:[]};

        for(var j = 0; j<StimulusAllocator.surveyIDs.length; j++){

            var index = Math.floor(Math.random() * StimulusAllocator.wordLists.lists.length);
            var trialObj = {
                "type": "StimulusAllocation",
                "trial_identifier":StimulusAllocator.surveyIDs[j],
                "stimulus_list_identifier":StimulusAllocator.wordLists.lists[index].id,
                "stimulus":[]
            };

            for(var k = 0; k < StimulusAllocator.wordLists.lists[index].words.length; k++)
            {
                trialObj.stimulus.push(StimulusAllocator.wordLists.lists[index].words[k]);
            }

            participantObj.trials.push(trialObj);
        }

        stimulusAllocations.push(participantObj);
    }

    addStimAllocToProject(stimulusAllocations);
}

function addStimulusAllocation(participantID){

    var participantObj = {"identifier":participantID, trials:[]};
    for(var projectIndex in ProjectsModelController.projects)
    {
        if(ProjectsModelController.projects[projectIndex].identifier == StimulusAllocator.projectID)
        {
            for(var j = 0; j < ProjectsModelController.projects[projectIndex].surveys.length; j++)
            {
                var index = Math.floor(Math.random() * StimulusAllocator.wordLists.lists.length);

                var trialObj = {
                    "type": "StimulusAllocation",
                    "trial_identifier":ProjectsModelController.projects[projectIndex].surveys[j].identifier,
                    "stimulus_list_identifier":StimulusAllocator.wordLists.lists[index].id,
                    "stimulus":[]
                };

                for(var k = 0; k < StimulusAllocator.wordLists.lists[index].words.length; k++)
                {
                    trialObj.stimulus.push(StimulusAllocator.wordLists.lists[index].words[k]);
                }

                participantObj.trials.push(trialObj);
            }
        }
    }
    appendStimAllocToProject(participantObj);
}


function addStimAllocToProject(stimulusAllocations){
    for(var projectIndex in ProjectsModelController.projects)
    {
        if(ProjectsModelController.projects[projectIndex].identifier == StimulusAllocator.projectID){
            ProjectsModelController.projects[projectIndex].stimulus_alloc = stimulusAllocations;
            ProjectsModelController.projects[projectIndex].participants = StimulusAllocator.participantIDs;
            ProjectsModelController.projects[projectIndex].stimulus_pool_identifier = StimulusAllocator.wordLists.id;

            ProjectsModelController.saveProject(ProjectsModelController.projects[projectIndex]);
            ProjectsModelController.reloadProjects();
            break;
        }
    }
}

function appendStimAllocToProject(participantObj){
    for(var projectIndex in ProjectsModelController.projects)
    {
        if(ProjectsModelController.projects[projectIndex].identifier == StimulusAllocator.projectID){
            ProjectsModelController.projects[projectIndex].stimulus_alloc.push(participantObj);
            ProjectsModelController.saveProject(ProjectsModelController.projects[projectIndex]);
            ProjectsModelController.reloadProjects();
            break;
        }
    }
}

function generatePins(participantAmount) {
    var pins = $.ajax({
        url: 'http://'+ hostname +'/pins?amountNeeded='+participantAmount,
        type:"GET",
        async: false
    }).responseText;

    return eval('(' + pins + ')');
}

function generateGUID()
{
    var guid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
        return v.toString(16);
    });
    return guid;
}

Handlebars.getTemplateAjax = function(name) {

    var source;
    var template;

    $.ajax({
        url: 'public/js/templates/' + name + '.handlebars',
        cache: true,
        async: false,
        success: function(data) {
            source    = data;
            template  = Handlebars.compile(source);
        }
    });

    return template;
};


function UIController()
{

}

UIController.addProjectItem = function(jsonProjectObj)
{
    var projectItemTtemplate = Handlebars.getTemplateAjax('projectlistitem');
    var projectItemHTML = projectItemTtemplate(jsonProjectObj);

    $("#projectsContainer").append(projectItemHTML);
};

UIController.addSurveyItem = function(projectID, jsonSurveyObj)
{
    $('#noSurveys').remove();

    var surveyItemTemplate = Handlebars.getTemplateAjax('surveylistitem');
    var surveyItemHTML = surveyItemTemplate(jsonSurveyObj);

    $('#' + projectID + '-content').append(surveyItemHTML);
};

UIController.addQuestionItem = function(surveyID, jsonQuestionObj)
{
    var questionItemTemplate = Handlebars.getTemplateAjax('questionlistitem');
    var questionItemHTML = questionItemTemplate(jsonQuestionObj);
    var questionsTabContainer = $('#' + surveyID + '-survey-questions-list');

    if(questionsTabContainer.children('div'))
    {
        //removing div to signal no questions
        questionsTabContainer.children('div').remove();
    }

    var existingQuestionHTML = questionsTabContainer.find('#' + jsonQuestionObj.identifier);

    if(existingQuestionHTML.html() !== undefined)
    {
        existingQuestionHTML.replaceWith(questionItemHTML);
    }
    else
    {
        questionsTabContainer.find('ul').append(questionItemHTML);
    }
};

UIController.reloadSurveyQuestions = function(surveyID, questions)
{
    var questionItemTemplate = Handlebars.getTemplateAjax('questionlistitem');
    var questionsTabContainer = $('#' + surveyID + '-survey-questions-list');

    if(questionsTabContainer.children('div'))
    {
        questionsTabContainer.children('div').remove();
    }

    for(var i in questions)
    {
        var questionItemHTML = questionItemTemplate(questions[i]);
        questionsTabContainer.find('ul').append(questionItemHTML);
    }
};

UIController.reloadSurveyTriggers = function(surveyID, triggers)
{
    var triggersTabContainer = $('#' + surveyID + '-survey-triggers-list');
    var triggerItemHTML = null;

    triggersTabContainer.find('ul').children().remove();

    var spatialItemTemplate = Handlebars.getTemplateAjax('spatialtriggeritem');
    var temporalItemTemplate = Handlebars.getTemplateAjax('temporaltriggeritem');

    for(var i in triggers){
        var trigger = triggers[i];
        if(trigger.type == 'SpatialTrigger') {
            triggerItemHTML = spatialItemTemplate(trigger);
        } else if(trigger.type == 'TemporalTrigger'){
            triggerItemHTML = temporalItemTemplate(trigger);
        } else {
            alert('Error: unrecognized trigger type');
        }
        triggersTabContainer.find('ul').append(triggerItemHTML);
    }
};

UIController.addTriggerItem = function(surveyID, triggerObj)
{
    var triggerTemplateID;

    if(triggerObj.type == 'SpatialTrigger')
    {
        triggerTemplateID = 'spatialtriggeritem';
    }
    else if(triggerObj.type == 'TemporalTrigger')
    {
        triggerTemplateID = 'temporaltriggeritem';
    }
    else
    {
        alert('Error: unrecognized trigger type');
    }

    var triggerItemTemplate = Handlebars.getTemplateAjax(triggerTemplateID);
    var triggerItemHTML = triggerItemTemplate(triggerObj);
    var triggersTabContainer = $('#' + surveyID + '-survey-triggers-list');

    if(triggersTabContainer.children('div'))
    {
        //removing div to signal no questions
        triggersTabContainer.children('div').remove();
    }


    var existingTriggerHTML = triggersTabContainer.find('#' + triggerObj.identifier);

    if(existingTriggerHTML.html() !== undefined)
    {
        existingTriggerHTML.replaceWith(triggerItemHTML);
    }
    else
    {
        triggersTabContainer.find('ul').append(triggerItemHTML);
    }
};

UIController.addQuestionOptionItem = function(questionID, jsonQuestionOptionObj)
{
    var questionOptionItemTemplate = Handlebars.getTemplateAjax('questionoptionlistitem');
    var questionOptionItemHTML = questionOptionItemTemplate(jsonQuestionOptionObj);
    $('#' + 'question-multiOptionList').append(questionOptionItemHTML);
};