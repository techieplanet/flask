function addAjaxSubmit(buttonId,formId,postUrl) {
//this is ajax submit min document
        
        
        
        
           
        console.log('button: ' + buttonId + ' form: ' + formId + ' ' + postUrl); 
        
	(function() {
		var _button = new YAHOO.widget.Button(buttonId);
		var handleSuccess = function(o) {
			try {
                               stopPageLoader(buttonId);
                               
                               if(buttonId == 'submitPersonTop' || buttonId == 'submitPerson')
                               {
                                 // disableRetiredField2(); //Tp
                               }
                               
                               
				var response = o.responseText;
                                //alert(response);
                               // alert(response);
				var responseObj = YAHOO.lang.JSON.parse(response);
                                
				displayStatus(responseObj.status);
                                //console.log(responseObj.status);
				var allGood = true;
                                console.log(JSON.stringify(responseObj) );
				if ( responseObj.messages ) {
                                    
					for (var key in responseObj.messages) {
						displayErrorMessage(key,responseObj.messages[key]);
						allGood = false;
					}
				}
				if(!allGood) {
                                    if(buttonId == 'submitPersonTop' || buttonId == 'submitPerson')
                                        clearDisabledField()
                                    
                                        console.log("Entered Not All Good");
					setStatusBoxError();
				}
                                

				
				if ( responseObj.obj_id ) {
					var new_obj_field =  YAHOO.util.Dom.get('obj_id');
					if ( new_obj_field ) {
						new_obj_field.value = responseObj.obj_id;
					}
				}
				
				if ( responseObj.redirect ) {
					window.location = responseObj.redirect;
				}
                                
                                
			}
			catch (x) {
                           // alert(responseObj);
                                stopPageLoader(buttonId);
				//alert("ajaxmin TP ITech script error: " + x);
                                console.log("Error - " + x);
				//alert(response);
				return;
			}

                        document.body.style.cursor = "auto";

	
		}
		var handleFailure = function(o) {
                        
                         stopPageLoader(buttonId)
                         
			console.log("Submission failed with code: " + o.status + ". The error text is: " + o.statusText);
			document.body.style.cursor = "auto";
		}

		var callback = {
	    success: handleSuccess,
	    failure: handleFailure
		};

	  _button.on('click', function(ev) {
              
              var docontinue = isHWRetired();
              var formEnabled = isFormDisabled();
              
              console.log("TP2: user clicked " + _button.id + " button");
              //TP: Display the loading icon for persons edit page save button click event
              
	    window.setTimeout(function() {
              
              startPageLoader(buttonId);
              
	      //document.body.style.cursor = "wait";
				//debugger;
	      //clear error text
				var els = YAHOO.util.Dom.getElementsByClassName('errorText');

				if ( els.length ){
                                        console.log("No Error");
					YAHOO.util.Dom.setStyle(els, 'display', 'none');
                                        //var div = document.getElementById("statusBox");
                                        //div.style.display = "none";
                                        $("#statusBox").removeClass("statusError");
                                        $("#statusBox").remove();
                                }

				var formObject = document.getElementById(formId);
                              // alert(postUrl);
                              //onreadystatechange
				YAHOO.util.Connect.setForm(formObject);
                                
                               
                                      if(docontinue == false && formEnabled == false)
                                    {
                                        stopPageLoader(buttonId);
                                        console.log(docontinue);
                                        //alert("User is InActive, activate user before you can make changes");
                                        console.log("User is inactive, Returning");
                                        return 0;
                                    } 
                                
                                console.log("Initializing Ajax request");
			 	var request = YAHOO.util.Connect.asyncRequest('POST', postUrl, callback);
			} 
      , 200);
	  });

	})();
}

function isHWRetired()
{
    if($("#isActive").length > 0)
    {
        var val = $("#isActive").val();
        console.log("isActive : " + val);
        
        if(val == "0" || val == 0)
        {
            return false;
        }
        return true;
    }
    
    return true;
}

function isFormDisabled()
{
    var fnameObj = $("#first_name").prop("disabled");
    
     if(fnameObj == true || fnameObj == "true")
     {
         return false;
     }
     else
     {
         return true;
     }
}


function startPageLoader(buttonId)
{
    
        startLoading();
    
  
}

function stopPageLoader(buttonId)
{
        console.log("Calling stop loading");
        stopLoading();
        console.log("Stop loader finished");
    
}

