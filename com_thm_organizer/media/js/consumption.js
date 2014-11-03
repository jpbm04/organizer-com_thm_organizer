/* global $*/

/**
 * Code i part from
 * http://www.kubilayerdogan.net/javascript-export-html-table-to-excel-with-custom-file-name/
 *
 **/

jQuery(document).ready(function ()
{
    "use strict";
    jQuery("#roomsExport").click(function(e)
    {
        downloadTable('rooms');
        //just in case, prevent default behaviour
        e.preventDefault();
    });

    jQuery("#teachersExport").click(function(e)
    {
        downloadTable('teachers');
        //just in case, prevent default behaviour
        e.preventDefault();
    });

    function downloadTable(type)
    {
        var tableToExcel;
        var dt = new Date(),
            day = dt.getDate(),
            month = dt.getMonth() + 1,
            year = dt.getFullYear(),
            hour = dt.getHours(),
            mins = dt.getMinutes(),
            created = day + "-" + month + "-" + year + "_" + hour + "-" + mins,
            divID = 'thm_organizer-' + type + '-consumption-table',
            sheetName = type + '-' + created;

        // From http://stackoverflow.com/questions/17126453/html-table-to-excel-javascript (changed slightly)
        tableToExcel = (function () {
            var uri = 'data:application/vnd.ms-excel;base64,',
                template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
                base64 = function (s) {
                    return window.btoa(unescape(encodeURIComponent(s)))
                },
                format = function (s, c) {
                    return s.replace(/{(\w+)}/g, function (m, p) {
                        return c[p];
                    })
                };
            return function (table, name, filename) {
                if (!table.nodeType) {
                    table = document.getElementById(table);
                }
                var ctx = { worksheet: name || 'Worksheet', table: table.innerHTML };

                if (jQuery.isFunction(window.navigator.msSaveOrOpenBlob)) {
                    // IE
                    var fileData = [format(template, ctx)];
                    var blobObject = new Blob(fileData);
                    window.navigator.msSaveOrOpenBlob(blobObject, filename);
                }
                else if (navigator.userAgent.indexOf("Safari") >= 0 && navigator.userAgent.indexOf("OPR") === -1 && navigator.userAgent.indexOf("Chrome") === -1) {
                    // Safari
                    // No possibility to define the file name :(
                    window.location.href = uri + base64(format(template, ctx));
                }
                else {
                    // Other Browsers
                    var downloadLink = document.getElementById('dlink');
                    downloadLink.href = uri + base64(format(template, ctx));
                    downloadLink.download = filename;
                    downloadLink.click();
                }
            };
        })();

        tableToExcel(divID, type, sheetName + '.xls');
    }

    jQuery('#consumption').keypress(function(e)
    {
        var form = jQuery('#statistic-form');
        if (e.keyCode === 13)
        {
            form.submit();
        }
    });
});

function toggleRooms()
{
    "use strict";
    var toggleSpan = jQuery("#filter-room-toggle-image");
    if (toggleSpan.hasClass('toggle-closed'))
    {
        toggleSpan.removeClass('toggle-closed');
        toggleSpan.addClass('toggle-open');
    }
    else
    {
        toggleSpan.removeClass('toggle-open');
        toggleSpan.addClass('toggle-closed');
    }
    jQuery("#filter-room").toggle();
}

function toggleTeachers()
{
    "use strict";
    var toggleSpan =jQuery("#filter-teacher-toggle-image");
    if (toggleSpan.hasClass('toggle-closed'))
    {
        toggleSpan.removeClass('toggle-closed');
        toggleSpan.addClass('toggle-open');
    }
    else
    {
        toggleSpan.removeClass('toggle-open');
        toggleSpan.addClass('toggle-closed');
    }
    jQuery("#filter-teacher").toggle();
}
