$( document ).ready(function() {
    $('#id_managecolorschemas').click( function() {
        var colorschema = $('#id_colorschema option:selected').val();
        var courseid = $("[name='id']").val();
        location.href = M.cfg.wwwroot + '/course/format/ned_tabs/colorschema_edit.php?courseid=' + courseid + '&edit=' + colorschema;
    });
});