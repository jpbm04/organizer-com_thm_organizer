/* globals jQuery, Joomla */
var jq = jQuery.noConflict();

window.onload = function(){
    var forms  = document.getElementsByTagName("form");
    for(var i =  0; i < forms.length; i++){
        forms[i].onsubmit = function() {return false};
    }
};

/**
 * Moves the values of the calling row up one row in the children table
 *
 * @param {type} oldOrder
 *
 * @returns {void}
 */
function moveUp(oldOrder)
{
    "use strict";
    var currentOrder = getCurrentOrder();
    if (oldOrder <= 1 || (currentOrder.length === Number(oldOrder) && currentOrder[oldOrder-2].name === ""))
    {
        return;
    }
    var reorderObjects = currentOrder.splice(oldOrder - 2, 2);

    // Set current to lower
    overrideElement((oldOrder - 1), reorderObjects[1]);

    // Set current with lower
    overrideElement(oldOrder, reorderObjects[0]);
    
}

/**
 * Moves the values of the calling row down one row in the children table
 *
 * @param {type} oldOrder
 *
 * @returns {void}
 */
function moveDown(oldOrder)
{
    "use strict";
    var currentOrder = getCurrentOrder();
    if (oldOrder >= currentOrder.length || (currentOrder.length === Number(oldOrder)+1 && currentOrder[oldOrder-1].name === ""))
    {
        return;
    }
    var reorderObjects = currentOrder.splice(oldOrder - 1, 2);

    // Set current to lower
    overrideElement(oldOrder, reorderObjects[1]);

    // Set current with lower
    var newOrder = parseInt(oldOrder, 10) + 1;
    overrideElement(newOrder, reorderObjects[0]);
    
}

/**
 * Add new empty level.
 * 
 * @param {int} position
 * @returns {void}
 */
function setEmptyElement(position)
{
    "use strict";

    var currentOrder = getCurrentOrder();
    var length = parseInt(currentOrder.length, 10);

    createNewRow(length, 'childList');

    while (position <= length)
    {
        var newOrder = length + 1;
        var oldIndex = length - 1;

        overrideElement(newOrder, currentOrder[oldIndex]);
        length--;
    }

    overrideElementWithDummy(position);
}

/**
 * Pushes all rows a step up. 
 * The first row 'position' is moved to the last postion.
 * 
 * @param {int} position
 * @returns {void}
 */
function setElementOnLastPosition(position)
{
    "use strict";

    var currentOrder = getCurrentOrder();
    var length = parseInt(currentOrder.length, 10);
    var tmpElement = currentOrder[position - 1];

    if(tmpElement.name !== "")
    {
        pushAllUp(position, length, currentOrder);

        overrideElement(length, tmpElement);
    }
}

/**
 * Sets the current values of a row to another row indicated by the value of the
 * order field
 *
 * @param   {int} firstPos
 *
 * @returns  {void}
 */
function orderWithNumber(firstPos)
{
    "use strict";

    var currentOrder = getCurrentOrder();
    var length = currentOrder.length;
    
    var tmpElement = currentOrder[firstPos - 1];
    var secondPos = jq('#child' + firstPos + 'order').val();
    secondPos = parseInt(secondPos);


    if (isNaN(secondPos) === true || secondPos > length || (Number(secondPos) === length && tmpElement.name === ""))
    {
        jq('#child' + firstPos + 'order').val(firstPos);
        return;
    }

    if (firstPos < secondPos) 
    {
        pushAllUp(firstPos, secondPos, currentOrder);   
    } 
    else 
    {
        pushAllDown(firstPos, secondPos, currentOrder);
    }

    overrideElement(secondPos, tmpElement);
}

/**
 * Removes a child row from the display
 *
 * @param   {int}  rowNumber  the number of the row to be deleted
 *
 * @returns  {void}
 */
function removeRow(rowNumber)
{
    "use strict";
    
    var currentOrder = getCurrentOrder();
    var length = currentOrder.length;
    
    pushAllUp(rowNumber, length, currentOrder);
    
    jq('#childRow' + length).remove();
}

/**
 * Push all Ements up.
 * 
 * @param {int} position
 * @param {int} length
 * @param {array} elementArray
 * @returns {void}
 */
function pushAllUp(position, length, elementArray)
{
    "use strict";
    
    while (position < length)
    {
        overrideElement(position, elementArray[position]);
        position++;
    } 
}

/**
 * Push all Elements down.
 * 
 * @param {int} position
 * @param {int} length
 * @param {array} elementArray
 * @returns {void}
 */
function pushAllDown(position, length, elementArray)
{
    "use strict";
    
    while (position > length)
    {
        var newOrder = position;
        var oldIndex = position - 2;

        overrideElement(newOrder, elementArray[oldIndex]);
        position--;
    }
}

/**
 * Retreives an array of objects mapping form values
 *
 * @returns {Array}
 */
function getCurrentOrder()
{
    "use strict";
    var currentOrder = [];

    // The header row needs to be removed from the count
    var rowCount = jq('#childList tr').length - 1;
    for (var i = 0; i < rowCount; i++)
    {
        var order = i + 1;
        currentOrder[i] = {};
        currentOrder[i].name = jq('#child' + order + 'name').text().trim();
        currentOrder[i].id = jq('#child' + order).val();
        currentOrder[i].link = jq('#child' + order + 'link').attr('href');
        currentOrder[i].order = jq('#child' + order + 'order').val();
    }
    return currentOrder;
}

/**
 * Override a DOM-Element with the ID '#child'+newOrder.
 * 
 * @param {int} newOrder
 * @param {Object} oldElement
 * @returns {void}
 */
function overrideElement(newOrder, oldElement)
{
    "use strict";

    jq('#child' + newOrder + 'name').text(oldElement.name);
    jq('#child' + newOrder).val(oldElement.id);
    jq('#child' + newOrder + 'link').attr('href', oldElement.link);
    jq('#child' + newOrder + 'order').val(newOrder);
}

/**
 * Overrides a DOM-Element with a Dummy-Element.
 * 
 * @param {int} position
 * @returns {void}
 */
function overrideElementWithDummy(position)
{
    "use strict";
    
    jq('#child' + position + 'name').text('');
    jq('#child' + position).val('');
    jq('#child' + position + 'link').attr('href', "");
    jq('#child' + position + 'order').val(position); 
}

/**
 * Add a new row on the end of the table.
 * 
 * @param {int} lastPosition
 * @param {int} tableID
 * @returns {void}
 */
function createNewRow(lastPosition, tableID) 
{
    "use strict";

    var nextClassRow;
    var lastClassRow = document.getElementById('childRow' + lastPosition).className;
    
    if (lastClassRow === null) {
        nextClassRow = 'row1';
    }
    else if (lastClassRow === 'row0')
    {
        nextClassRow = 'row1';
    } 
    else 
    {
        nextClassRow = 'row0';
    }
    
    var pos = parseInt(lastPosition, 10) + 1;
    
    jq( '<tr id="childRow'+pos+'" class="'+nextClassRow+'">' +
        '<td class="child-name">' +
          '<a id="child'+pos+'link" href="#">' +
            '<span id="child'+pos+'name">TEST OBJEKT</span>' +
          '</a>' +
          '<input id="child'+pos+'" type="hidden" value="0" name="child'+pos+'">' +
        '</td>' +
        '<td class="child-order">' +
        '<button class="btn btn-small" onclick="moveUp(\''+pos+'\');" title="Move Up"><span class="icon-previous"></span></button>' +
        '<input type="text" title="Ordering" name="child'+pos+'order" id="child'+pos+'order" size="2" value="'+pos+'" class="text-area-order" onchange="orderWithNumber('+pos+');">' +
        '<button class="btn btn-small" onclick="setEmptyElement(\''+pos+'\');" title="Add Space"><span class="icon-download"></span></button>' +
        '<button class="btn btn-small" onclick="removeRow(\''+pos+'\');" title="Delete"><span class="icon-delete"></span></button>' +
        '<button class="btn btn-small" onclick="moveDown(\''+pos+'\');" title="Move Down"><span class="icon-next"></span></button>' +
        '<button class="btn btn-small" onclick="setElementOnLastPosition(\''+pos+'\');" title="Make Last"><span class="icon-last"></span></button>' +
        '</td>' +
        '</tr>' 
    ).appendTo(document.getElementById(tableID).tBodies[0]);
}
