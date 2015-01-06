/*global jq: false, categories: false, invalidFormText: false*/
window.addEvent('domready', function() {
    "use strict";
    document.formvalidator.setHandler('germandate',
        function (value) { return (/^[0-3][0-9].[0-1][0-9].[0-9]{4}$/).test(value); });
});

window.addEvent('domready', function() {
    "use strict";
    document.formvalidator.setHandler('time',
        function (value) { return (/^[0-2]?[0-9]:[0-5][0-9]$/).test(value); });
});

window.addEvent('domready', function() {
    "use strict";
    document.formvalidator.setHandler('title', function (value) { return value !== ''; });
});

window.addEvent('domready', function() {
    "use strict";
    document.formvalidator.setHandler('category', function (value) { return value > 0; });
});


var jq = jQuery.noConflict();

jq("body").on({
    ajaxStart: function() {
        jq(this).addClass("loading");
    },
    ajaxStop: function() {
        jq(this).removeClass("loading");
    }
});

function closePopup()
{
    var frame = document.getElementById('thm_organizer_ee_preview_event'),
        content = document.getElementById('thm_organizer_e_preview_div');
    jq('.Popup').fadeOut("slow");
    frame.removeChild(content);
}

function preview_content(response) {
    var json = jq.parseJSON(response);
    jq('#thm_organizer_ee_preview_event').append("<div id='thm_organizer_e_preview_div' class='thm_organizer_e_preview_div' >\
                                                        <div class='thm_organizer_e_title'>"           + json.title        + "</div>\
                                                        <div class='thm_organizer_e_publish_up'>"      + json.created_at   + "</div>\
                                                        <div class='thm_organizer_e_author'>"          + json.username     + "</div>\
                                                        "                                              + json.introtext    + "\
                                                        <div class='thm_organizer_e_description'>"     + json.description  + "</div>\
                                                      </div>");
}

function build_url() {
    var url = "<?php echo $this->baseurl; ?>";
    url = url + "/index.php?option=com_thm_organizer&view=event_ajax&format=raw&eventID=";
    url = url + jq('#jform_id').val() + "&title=";
    url = url + jq('#jform_title').val() + "&id=";
    url = url + jq('#jform_id').val() + "&startdate=";
    url = url + jq('#jform_startdate').val() + "&enddate=";
    url = url + jq('#jform_enddate').val() + "&starttime=";
    url = url + jq('#jform_starttime').val() + "&endtime=";
    url = url + jq('#jform_endtime').val() + "&category=";
    url = url + jq('#category').val() + "&rec_type=";
    url = url + getRecType() + "&teachers[]=";
    url = url + getResources('#teachers') + "&rooms[]=";
    url = url + getResources('#rooms') + "&groups[]=";
    url = url + getResources('#groups');
    return url;
}

/**
* Changes a dynamically generated list
*/
function changeCategoryInformation()
{
    "use strict";
    var index = document.getElementById('category').selectedIndex;
    var id = document.getElementById('category').options[index].value;
    document.getElementById('thm_organizer_ee_event_cat_desc_div').innerHTML = categories[id][0];
    document.getElementById('thm_organizer_ee_event_cat_disp_div').innerHTML = categories[id][1];
    document.getElementById('thm_organizer_ee_content_cat_name_div').innerHTML = categories[id][2];
    document.getElementById('thm_organizer_ee_content_cat_desc_div').innerHTML = categories[id][3];
    document.getElementById('thm_organizer_ee_content_cat_access_div').innerHTML = categories[id][4];
}

/**
 * returns the value of the recurrence type input
 */
function getRecType()
{
    "use strict";
    var rec_type = 0;
    for(var i=0; i < document.eventForm.rec_type.length; i++)
    {
        if(document.eventForm.rec_type[i].checked)
        {
            rec_type = document.eventForm.rec_type[i].value;
        }
    }
    return rec_type;
}

/**
 * returns a string containing the resource values selected
 */
function getResources(resourceID)
{
    "use strict";
    var selectedResources = jq(resourceID).val();
    if(typeof selectedResources !== 'undefined'){
        if(jq.isArray(selectedResources))
        {
            selectedResources = selectedResources.join(",");
            return selectedResources;
        }
        if(jq.isNumeric(selectedResources))
        {
            selectedResources = selectedResources.toString();
            return selectedResources;
        }
        else
        {
            return selectedResources;
        }
    }
    else
    {
        return '';
    }
}

/**
 * toggles the value of the checkbox since joomla didnt bother to create standardized
 * js for this form field type
 */
function toggleCheckValue(isitchecked)
{
    "use strict";
    if(isitchecked === true)
    {
        document.eventForm.jform_emailNotification.value++;
    }
    else
    {
        document.eventForm.jform_emailNotification.value--;
    }
}

Joomla.submitbutton =  function(task){
    if (task === '') { return false; }
    else
    {
        var isValid = true;
        var action = task.split('.');

        if (action[1] !== 'cancel' && action[1] !== 'close')
        {
            var forms = $$('form.form-validate');
            for (var i=0;i<forms.length;i++)
            {
                if (!document.formvalidator.isValid(forms[i]))
                {
                    isValid = false;
                    break;
                }
            }
        }

        var requrl = build_url();
        if (isValid && task === 'event.preview')
        {
            var description = document.getElementById("jform_description_ifr").contentWindow.document.getElementById("tinymce").innerHTML;
            var descriptionString = String(description);
            description = descriptionString.indexOf("data-mce-bogus") != -1? '' : description;
            requrl = requrl + "&description=" + description  + "&task=preview";
            jq.ajax( {
                type    : "GET",
                url     : requrl,
                success : function(response) {
                    preview_content(response);
                    jq('.Popup').fadeIn("slow");
                    return false;
                },
                failure : function() {
                    return false;
                }
            });
        }
        else if (isValid)
        {
            requrl = requrl + "&task=booking";
            jq.ajax( {
                type    : "GET",
                url     : requrl,
                success : function(response) {
                    var confirmed = true;
                    if (response){ confirmed = confirm(response); }
                    if (confirmed){Joomla.submitform(task, document.eventForm); }
                    return false;
                },
                failure : function() {
                    return false;
                }
            });
        }
        else
        {
            alert(invalidFormText);
            return false;
        }
    }
};