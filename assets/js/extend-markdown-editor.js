/**
 * Script to extend Markdown editor for Blog post form
 *  - add button to insert photos from albums
 *  - add visual dialog for this button
 */

+function ($) {

    $(document).ready(function () {
        var editor = $('[data-control="markdowneditor"]').data('oc.markdownEditor');

        // FIXME Localize label when it is supported
        var button = {
            label: 'Insert photo from Photoalbums',
            cssClass: 'oc-autumn-button oc-icon-camera-retro',
            insertAfter: 'mediaimage',
            action: 'showAlbumsDialog',
            template: '$1'
        };

        /**
         *
         * Markdown editor method to show photo selection dialog
         *
         * @param template
         */
        editor.showAlbumsDialog = function (template) {
            var editor = this.editor,
                pos = this.editor.getCursorPosition();

            new $.oc.photoselector.popup({
                alias: 'photoSelector',
                onInsert: function (code) {
                    editor.insert(template.replace('$1', code));
                    editor.moveCursorToPosition(pos);
                    editor.focus();
                    this.hide();
                }
            });
        };

        //add button to editor
        editor.addToolbarButton('photoalbums', button);
    });

}(window.jQuery);
