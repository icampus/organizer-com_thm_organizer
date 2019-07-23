/**
 * Gets programs, pools and rooms depending on selected department (or program) and inserts them as the only options
 */
jQuery(document).ready(function () {
    'use strict';

    var department = jQuery('#jform_params_departmentID'),
        category = jQuery('#jform_params_categoryIDs'),
        group = jQuery('#jform_params_groupIDs'),
        room = jQuery('#jform_params_roomIDs');

    // init loading
    if (department.val() !== '0')
    {
        update();
    }

    department.change(update);
    category.change(update);

    /**
     * All values get filtered by department/program through Ajax
     * When triggered by event - discard old value
     * @param {Event} [event]
     */
    function update(event)
    {
        var ajaxBaseUrl = '../index.php?option=com_thm_organizer&format=json',
            ajaxParams = '&departmentIDs=' + department.val() + '&categoryIDs=' + (category.val() || ''),
            keepValue = !event;

        // Update programs, when it is not its own trigger
        if (!event || (event && event.target.id !== category.attr('id')))
        {
            jQuery.ajax(ajaxBaseUrl + '&view=category_options' + ajaxParams)
                .done(function (request) {
                    insertOptions(category, request, keepValue);
                });
        }
        // Update pools
        jQuery.ajax(ajaxBaseUrl + '&view=group_options' + ajaxParams)
            .done(function (request) {
                insertOptions(group, request, keepValue);
            });
        // Update rooms
        jQuery.ajax(ajaxBaseUrl + '&view=room_options&roomtypeIDs=-1' + ajaxParams)
            .done(function (request) {
                insertOptions(room, request, keepValue);
            });
    }

    /**
     * Fills options of given field with an Ajax request
     * @params {object} field
     * @params {string} request
     * @params {boolean} keepValue
     */
    function insertOptions(field, request, keepValue)
    {
        var values = JSON.parse(request), oldValue = field.val();

        field.find('option:not(:first)').remove();
        jQuery.each(values, function (name, id) {
            var option = jQuery('<option></option>').val(id).html(name);
            if (keepValue && (jQuery.inArray(id, oldValue) !== -1 || id === oldValue))
            {
                option.attr('selected', 'selected');
            }
            field.append(option);
        });
        jQuery(field).chosen('destroy').chosen();
    }
});
